<?php
/**
 * License Data Component
 * 
 * Handles all license-related data operations
 */

if (!defined('ABSPATH')) {
    exit;
}

class EDDCDP_License_Data implements EDDCDP_Component_Interface {
    
    private $edd_integration;
    
    /**
     * Initialize component
     */
    public function init() {
        add_action('init', array($this, 'process_license_actions'));
    }
    
    /**
     * Get component dependencies
     */
    public function get_dependencies() {
        return array('EDDCDP_Edd_Integration');
    }
    
    /**
     * Check if component should load
     */
    public function should_load() {
        return class_exists('EDD_Software_Licensing');
    }
    
    /**
     * Get component priority
     */
    public function get_priority() {
        return 25;
    }
    
    /**
     * Set EDD integration dependency
     */
    public function set_edd_integration($edd_integration) {
        $this->edd_integration = $edd_integration;
    }
    
    /**
     * Get customer licenses
     */
    public function get_customer_licenses($user_id) {
        if (!$this->edd_integration || !$this->edd_integration->is_licensing_active()) {
            return array();
        }
        
        return $this->edd_integration->get_customer_licenses($user_id);
    }
    
    /**
     * Get active licenses count
     */
    public function get_active_licenses_count($user_id) {
        if (!$this->edd_integration || !$this->edd_integration->is_licensing_active()) {
            return 0;
        }
        
        return $this->edd_integration->get_active_licenses_count($user_id);
    }
    
    /**
     * Get license status info
     */
    public function get_license_status_info($license) {
        if (!$this->edd_integration) {
            return array(
                'status' => 'unknown',
                'label' => 'Unknown',
                'days_remaining' => 0,
                'is_expired' => true
            );
        }
        
        return $this->edd_integration->get_license_status_info($license);
    }
    
    /**
     * Get license sites
     */
    public function get_license_sites($license_key) {
        if (!$this->edd_integration) {
            return array();
        }
        
        return $this->edd_integration->get_license_sites($license_key);
    }
    
    /**
     * Get license renewal URL
     */
    public function get_license_renewal_url($license) {
        if (!$license || !isset($license->key)) {
            return '';
        }
        
        if (function_exists('edd_sl_get_license_renewal_url')) {
            return edd_sl_get_license_renewal_url($license->key);
        }
        
        return edd_get_checkout_uri(array(
            'edd_license_key' => $license->key,
            'download_id' => $license->download_id,
            'edd_action' => 'license_renewal'
        ));
    }
    
    /**
     * Get license upgrade URL
     */
    public function get_license_upgrade_url($license) {
        if (!$license || !isset($license->download_id)) {
            return '';
        }
        
        if (edd_has_variable_prices($license->download_id)) {
            if (function_exists('edd_sl_get_license_upgrade_url')) {
                return edd_sl_get_license_upgrade_url($license->key);
            }
            
            return edd_get_checkout_uri(array(
                'edd_action' => 'license_upgrade',
                'license_key' => $license->key,
                'download_id' => $license->download_id
            ));
        }
        
        return get_permalink($license->download_id);
    }
    
    /**
     * Get license invoice URL
     */
    public function get_license_invoice_url($license) {
        if (!$license || !isset($license->payment_id)) {
            return '';
        }
        
        $payment = edd_get_payment($license->payment_id);
        if (!$payment) {
            return '';
        }
        
        return add_query_arg(array(
            'payment_key' => $payment->key,
            'view' => 'invoice'
        ));
    }
    
    /**
     * Check if license has upgrades
     */
    public function license_has_upgrades($license) {
        if (!$license || !isset($license->download_id)) {
            return false;
        }
        
        if (edd_has_variable_prices($license->download_id)) {
            $prices = edd_get_variable_prices($license->download_id);
            $current_price_id = isset($license->price_id) ? $license->price_id : null;
            
            if ($current_price_id !== null && isset($prices[$current_price_id])) {
                $current_price = $prices[$current_price_id]['amount'];
                
                foreach ($prices as $price_id => $price_data) {
                    if ($price_data['amount'] > $current_price) {
                        return true;
                    }
                }
            }
        }
        
        return false;
    }
    
    /**
     * Process license actions (activation/deactivation)
     */
    public function process_license_actions() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['eddcdp_action'])) {
            return;
        }
        
        $action = sanitize_text_field(wp_unslash($_POST['eddcdp_action']));
        
        if ($action === 'activate_license') {
            $this->process_license_activation();
        } elseif ($action === 'deactivate_license') {
            $this->process_license_deactivation();
        }
    }
    
    /**
     * Process license activation
     */
    private function process_license_activation() {
        if (!isset($_POST['eddcdp_license_nonce']) || 
            !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['eddcdp_license_nonce'])), 'eddcdp_activate_license')) {
            wp_die(__('Security verification failed.', 'edd-customer-dashboard-pro'));
        }
        
        if (!is_user_logged_in()) {
            wp_die(__('Please log in first.', 'edd-customer-dashboard-pro'));
        }
        
        $license_key = isset($_POST['license_key']) ? sanitize_text_field(wp_unslash($_POST['license_key'])) : '';
        $site_url = isset($_POST['site_url']) ? esc_url_raw(wp_unslash($_POST['site_url'])) : '';
        
        if (empty($license_key) || empty($site_url)) {
            wp_die(__('License key and site URL are required.', 'edd-customer-dashboard-pro'));
        }
        
        $site_url = untrailingslashit($site_url);
        $license = edd_software_licensing()->get_license($license_key);
        
        if (!$license) {
            wp_die(__('Invalid license key.', 'edd-customer-dashboard-pro'));
        }
        
        $license_id = is_object($license) && isset($license->ID) ? $license->ID : $license;
        
        if (get_post_field('post_author', $license_id) != get_current_user_id()) {
            wp_die(__('You do not own this license.', 'edd-customer-dashboard-pro'));
        }
        
        // Check if already activated
        $existing_sites = $this->get_license_sites($license_key);
        foreach ($existing_sites as $site) {
            if (isset($site->site_name) && $site->site_name === $site_url) {
                wp_die(__('This site is already activated for this license.', 'edd-customer-dashboard-pro'));
            }
        }
        
        // Activate license
        $result = false;
        if (method_exists(edd_software_licensing(), 'insert_site')) {
            $result = edd_software_licensing()->insert_site($license_id, $site_url);
        }
        
        if ($result) {
            $redirect_url = add_query_arg(array(
                'eddcdp_message' => 'license_activated',
                'eddcdp_site' => urlencode($site_url)
            ), wp_get_referer());
            
            wp_redirect($redirect_url);
            exit;
        } else {
            wp_die(__('Failed to activate license.', 'edd-customer-dashboard-pro'));
        }
    }
    
    /**
     * Process license deactivation
     */
    private function process_license_deactivation() {
        if (!isset($_POST['eddcdp_license_nonce']) || 
            !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['eddcdp_license_nonce'])), 'eddcdp_deactivate_license')) {
            wp_die(__('Security verification failed.', 'edd-customer-dashboard-pro'));
        }
        
        if (!is_user_logged_in()) {
            wp_die(__('Please log in first.', 'edd-customer-dashboard-pro'));
        }
        
        $license_key = isset($_POST['license_key']) ? sanitize_text_field(wp_unslash($_POST['license_key'])) : '';
        $site_url = isset($_POST['site_url']) ? esc_url_raw(wp_unslash($_POST['site_url'])) : '';
        
        if (empty($license_key) || empty($site_url)) {
            wp_die(__('License key and site URL are required.', 'edd-customer-dashboard-pro'));
        }
        
        $site_url = untrailingslashit($site_url);
        $license = edd_software_licensing()->get_license($license_key);
        
        if (!$license) {
            wp_die(__('Invalid license key.', 'edd-customer-dashboard-pro'));
        }
        
        $license_id = is_object($license) && isset($license->ID) ? $license->ID : $license;
        
        if (get_post_field('post_author', $license_id) != get_current_user_id()) {
            wp_die(__('You do not own this license.', 'edd-customer-dashboard-pro'));
        }
        
        // Deactivate license
        $result = false;
        if (method_exists(edd_software_licensing(), 'delete_site')) {
            $result = edd_software_licensing()->delete_site($license_id, $site_url);
        }
        
        if ($result) {
            $redirect_url = add_query_arg(array(
                'eddcdp_message' => 'license_deactivated',
                'eddcdp_site' => urlencode($site_url)
            ), wp_get_referer());
            
            wp_redirect($redirect_url);
            exit;
        } else {
            wp_die(__('Failed to deactivate license.', 'edd-customer-dashboard-pro'));
        }
    }
}