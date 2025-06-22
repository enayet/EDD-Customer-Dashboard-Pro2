<?php
/**
 * EDD Software Licensing Integration
 * 
 * Handles all EDD Software Licensing functionality
 */

if (!defined('ABSPATH')) {
    exit;
}

class EDDCDP_Licensing_Integration {
    
    private $licensing_api;
    
    public function __construct() {
        if (class_exists('EDD_Software_Licensing')) {
            $this->licensing_api = edd_software_licensing();
        }
    }
    
    /**
     * Get active licenses count for user
     */
    public function get_active_licenses_count($user_id) {
        if (!$this->licensing_api) {
            return 0;
        }
        
        try {
            $licenses = $this->get_customer_licenses($user_id);
            $active_count = 0;
            
            foreach ($licenses as $license) {
                if ($this->is_license_active($license)) {
                    $active_count++;
                }
            }
            
            return $active_count;
            
        } catch (Exception $e) {
            error_log('EDDCDP: Error getting active licenses count: ' . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get customer licenses
     */
    public function get_customer_licenses($user_id) {
        if (!$this->licensing_api) {
            return array();
        }
        
        try {
            // Try modern method first
            if (method_exists($this->licensing_api, 'get_licenses_of_user')) {
                return $this->licensing_api->get_licenses_of_user($user_id);
            }
            
            // Fallback to database query
            return $this->get_licenses_by_user_fallback($user_id);
            
        } catch (Exception $e) {
            error_log('EDDCDP: Error getting customer licenses: ' . $e->getMessage());
            return array();
        }
    }
    
    /**
     * Fallback method to get licenses by user
     */
    private function get_licenses_by_user_fallback($user_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'edd_licenses';
        
        // Check if table exists
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        $table_exists = $wpdb->get_var($wpdb->prepare(
            "SHOW TABLES LIKE %s",
            $wpdb->esc_like($table_name)
        ));
        
        if ($table_exists === $table_name) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
            return $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM {$table_name} WHERE user_id = %d ORDER BY date_created DESC",
                $user_id
            ));
        }
        
        return array();
    }
    
    /**
     * Check if license is active
     */
    private function is_license_active($license) {
        if (!$license) {
            return false;
        }
        
        // Use EDD's built-in license status methods
        if (method_exists($license, 'is_expired')) {
            return !$license->is_expired();
        }
        
        // Fallback: check status directly
        if (isset($license->status)) {
            return $license->status === 'active';
        }
        
        // Check expiration manually
        if (isset($license->expiration)) {
            if ($license->expiration === '0000-00-00 00:00:00' || empty($license->expiration)) {
                return true; // Lifetime license
            }
            
            return strtotime($license->expiration) > time();
        }
        
        return false;
    }
    
    /**
     * Get license status info
     */
    public function get_license_status_info($license) {
        if (!$license) {
            return array(
                'status' => 'unknown',
                'label' => __('Unknown', 'edd-customer-dashboard-pro'),
                'days_remaining' => 0,
                'is_expired' => true
            );
        }
        
        $is_expired = method_exists($license, 'is_expired') ? $license->is_expired() : false;
        $expiration = isset($license->expiration) ? $license->expiration : null;
        
        if (!$expiration || $expiration === '0000-00-00 00:00:00') {
            return array(
                'status' => 'lifetime',
                'label' => __('Lifetime', 'edd-customer-dashboard-pro'),
                'days_remaining' => -1,
                'is_expired' => false
            );
        }
        
        $expiration_timestamp = strtotime($expiration);
        $current_timestamp = time();
        $days_remaining = ceil(($expiration_timestamp - $current_timestamp) / DAY_IN_SECONDS);
        
        if ($is_expired || $days_remaining <= 0) {
            return array(
                'status' => 'expired',
                'label' => __('Expired', 'edd-customer-dashboard-pro'),
                'days_remaining' => $days_remaining,
                'is_expired' => true
            );
        } elseif ($days_remaining <= 30) {
            return array(
                'status' => 'expiring_soon',
                'label' => sprintf(__('Expires in %d days', 'edd-customer-dashboard-pro'), $days_remaining),
                'days_remaining' => $days_remaining,
                'is_expired' => false
            );
        } else {
            return array(
                'status' => 'active',
                'label' => sprintf(__('Active (expires %s)', 'edd-customer-dashboard-pro'), date_i18n(get_option('date_format'), $expiration_timestamp)),
                'days_remaining' => $days_remaining,
                'is_expired' => false
            );
        }
    }
    
    /**
     * Get license sites
     */
    public function get_license_sites($license_key) {
        if (!$this->licensing_api) {
            return array();
        }
        
        $license_id = $this->get_license_id_by_key($license_key);
        if (!$license_id) {
            return array();
        }
        
        return $this->get_license_activations($license_id);
    }
    
    /**
     * Get license ID by key
     */
    private function get_license_id_by_key($license_key) {
        // Try modern licenses database first
        if (class_exists('EDD_SL_License_DB') && isset($this->licensing_api->licenses_db)) {
            try {
                $licenses_db = $this->licensing_api->licenses_db;
                
                if (method_exists($licenses_db, 'get_licenses')) {
                    $licenses = $licenses_db->get_licenses(array(
                        'license_key' => $license_key,
                        'number' => 1
                    ));
                    
                    if ($licenses && is_array($licenses) && !empty($licenses[0])) {
                        return $licenses[0]->id;
                    }
                }
            } catch (Exception $e) {
                error_log('EDDCDP: Error getting license ID: ' . $e->getMessage());
            }
        }
        
        // Fallback: Direct database query
        global $wpdb;
        $table_name = $wpdb->prefix . 'edd_licenses';
        
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        $table_exists = $wpdb->get_var($wpdb->prepare(
            "SHOW TABLES LIKE %s",
            $wpdb->esc_like($table_name)
        ));
        
        if ($table_exists === $table_name) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
            $license_id = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM {$table_name} WHERE license_key = %s LIMIT 1",
                $license_key
            ));
            
            return $license_id ? intval($license_id) : false;
        }
        
        return false;
    }
    
    /**
     * Get license activations
     */
    private function get_license_activations($license_id) {
        $sites = array();
        
        // Try modern activations database
        if (class_exists('EDD_SL_Activations_DB') && isset($this->licensing_api->activations_db)) {
            try {
                $activations_db = $this->licensing_api->activations_db;
                
                if (method_exists($activations_db, 'get_activations')) {
                    $activations = $activations_db->get_activations(array(
                        'license_id' => $license_id,
                        'activated' => 1
                    ));
                    
                    if ($activations && is_array($activations)) {
                        foreach ($activations as $activation) {
                            if (is_object($activation) && isset($activation->site_name) && !empty($activation->site_name)) {
                                $sites[] = $activation;
                            }
                        }
                    }
                }
            } catch (Exception $e) {
                error_log('EDDCDP: Error getting activations: ' . $e->getMessage());
            }
        }
        
        // Fallback: Direct database query
        if (empty($sites)) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'edd_license_activations';
            
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
            $table_exists = $wpdb->get_var($wpdb->prepare(
                "SHOW TABLES LIKE %s",
                $wpdb->esc_like($table_name)
            ));
            
            if ($table_exists === $table_name) {
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                $activations = $wpdb->get_results($wpdb->prepare(
                    "SELECT site_name FROM {$table_name} WHERE license_id = %d AND activated = 1",
                    $license_id
                ));
                
                if ($activations) {
                    foreach ($activations as $activation) {
                        if (!empty($activation->site_name)) {
                            $sites[] = $activation;
                        }
                    }
                }
            }
        }
        
        return $sites;
    }
}