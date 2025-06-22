<?php
/**
 * Template Data Helper
 * 
 * Ensures templates always have the data they need, with fallbacks
 */

if (!defined('ABSPATH')) {
    exit;
}

class EDDCDP_Template_Data_Helper {
    
    /**
     * Ensure template data has all required properties
     */
    public static function ensure_complete_data($template_data) {
        // Ensure we have basic structure
        $template_data = is_array($template_data) ? $template_data : array();
        
        // Ensure user is set
        if (!isset($template_data['user']) || !$template_data['user']) {
            $template_data['user'] = wp_get_current_user();
        }
        
        // Ensure customer is set
        if (!isset($template_data['customer']) || !$template_data['customer']) {
            $user = $template_data['user'];
            if ($user && $user->ID) {
                $template_data['customer'] = edd_get_customer_by('user_id', $user->ID);
            } else {
                $template_data['customer'] = null;
            }
        }
        
        // Ensure dashboard_data is set (for backward compatibility)
        if (!isset($template_data['dashboard_data']) || !$template_data['dashboard_data']) {
            $template_data['dashboard_data'] = self::create_fallback_dashboard_data();
        }
        
        // Ensure settings are set
        if (!isset($template_data['settings'])) {
            $template_data['settings'] = get_option('eddcdp_settings', array());
        }
        
        // Ensure enabled_sections are set
        if (!isset($template_data['enabled_sections'])) {
            $settings = $template_data['settings'];
            $template_data['enabled_sections'] = isset($settings['enabled_sections']) && is_array($settings['enabled_sections']) 
                ? $settings['enabled_sections'] 
                : array();
        }
        
        // Ensure view_mode is set
        if (!isset($template_data['view_mode'])) {
            $template_data['view_mode'] = 'dashboard';
        }
        
        // Ensure payment data is set if in receipt mode
        if (!isset($template_data['payment'])) {
            $template_data['payment'] = null;
        }
        
        if (!isset($template_data['payment_key'])) {
            $template_data['payment_key'] = '';
        }
        
        return $template_data;
    }
    
    /**
     * Create fallback dashboard data object for backward compatibility
     */
    private static function create_fallback_dashboard_data() {
        return new class {
            public function get_user_initials($name) {
                $names = explode(' ', trim($name));
                $initials = '';
                
                foreach ($names as $n) {
                    if (!empty($n)) {
                        $initials .= strtoupper(substr($n, 0, 1));
                    }
                }
                
                return substr($initials, 0, 2);
            }
            
            public function get_download_count($customer) {
                if (!$customer || !isset($customer->id)) {
                    return 0;
                }
                
                $payments = edd_get_payments(array(
                    'customer' => $customer->id,
                    'status' => 'complete',
                    'number' => 9999
                ));
                
                $count = 0;
                if ($payments) {
                    foreach ($payments as $payment) {
                        $downloads = edd_get_payment_meta_downloads($payment->ID);
                        if ($downloads) {
                            $count += count($downloads);
                        }
                    }
                }
                
                return $count;
            }
            
            public function get_active_licenses_count($customer_id) {
                if (!class_exists('EDD_Software_Licensing')) {
                    return 0;
                }
                
                $customer = edd_get_customer($customer_id);
                if (!$customer || !$customer->user_id) {
                    return 0;
                }
                
                // Simple count - can be enhanced later
                return 0;
            }
            
            public function get_wishlist_count($user_id) {
                if (!class_exists('EDD_Wish_Lists')) {
                    return 0;
                }
                
                // Simple count - can be enhanced later
                return 0;
            }
            
            public function get_customer_purchases($customer, $limit = 20) {
                if (!$customer || !isset($customer->id)) {
                    return array();
                }
                
                return edd_get_payments(array(
                    'customer' => $customer->id,
                    'status' => array('complete', 'revoked'),
                    'number' => $limit
                )) ?: array();
            }
            
            public function get_customer_downloads($customer) {
                if (!$customer || !isset($customer->id)) {
                    return array();
                }
                
                $payments = edd_get_payments(array(
                    'customer' => $customer->id,
                    'status' => 'complete',
                    'number' => 9999
                ));
                
                $downloads = array();
                
                if ($payments) {
                    foreach ($payments as $payment) {
                        $payment_downloads = edd_get_payment_meta_downloads($payment->ID);
                        if ($payment_downloads) {
                            foreach ($payment_downloads as $download) {
                                $downloads[] = array(
                                    'payment' => $payment,
                                    'download' => $download,
                                    'download_limit' => edd_get_file_download_limit($download['id']),
                                    'downloads_remaining' => __('Unlimited', 'edd-customer-dashboard-pro')
                                );
                            }
                        }
                    }
                }
                
                return $downloads;
            }
            
            public function get_customer_licenses($user_id) {
                if (!class_exists('EDD_Software_Licensing')) {
                    return array();
                }
                
                // Simple implementation - can be enhanced later
                return array();
            }
            
            public function get_customer_wishlist($user_id) {
                if (!class_exists('EDD_Wish_Lists')) {
                    return array();
                }
                
                // Simple implementation - can be enhanced later
                return array();
            }
            
            public function get_customer_analytics($customer) {
                if (!$customer || !isset($customer->id)) {
                    return array(
                        'total_spent' => 0,
                        'purchase_count' => 0,
                        'avg_per_order' => 0,
                        'first_purchase' => null,
                        'last_purchase' => null
                    );
                }
                
                $total_spent = $customer->purchase_value;
                $purchase_count = $customer->purchase_count;
                $avg_per_order = $purchase_count > 0 ? $total_spent / $purchase_count : 0;
                
                return array(
                    'total_spent' => $total_spent,
                    'purchase_count' => $purchase_count,
                    'avg_per_order' => $avg_per_order,
                    'first_purchase' => $this->get_first_purchase_date($customer->id),
                    'last_purchase' => $this->get_last_purchase_date($customer->id)
                );
            }
            
            public function format_currency($amount) {
                if (is_array($amount)) {
                    if (isset($amount['amount'])) {
                        $amount = $amount['amount'];
                    } elseif (isset($amount['price'])) {
                        $amount = $amount['price'];
                    } elseif (isset($amount['item_price'])) {
                        $amount = $amount['item_price'];
                    } else {
                        $amount = 0;
                    }
                }
                
                if (is_null($amount) || $amount === '' || $amount === false) {
                    $amount = 0;
                }
                
                $amount = is_numeric($amount) ? floatval($amount) : 0;
                
                return edd_currency_filter(edd_format_amount($amount));
            }
            
            public function format_date($date) {
                return date_i18n(get_option('date_format'), strtotime($date));
            }
            
            public function get_payment_status_label($payment) {
                return edd_get_payment_status($payment, true);
            }
            
            public function get_receipt_url($payment) {
                return edd_get_success_page_uri('?payment_key=' . $payment->key);
            }
            
            public function is_licensing_active() {
                return class_exists('EDD_Software_Licensing');
            }
            
            public function can_download_file($payment, $download_id) {
                if (!$payment || !isset($payment->ID)) {
                    return false;
                }
                
                return edd_is_payment_complete($payment->ID) && edd_can_view_receipt($payment->key);
            }
            
            public function get_download_url($payment_key, $email, $file_key, $download_id) {
                if (!edd_can_view_receipt($payment_key)) {
                    return false;
                }
                
                return edd_get_download_file_url($payment_key, $email, $file_key, $download_id);
            }
            
            public function get_download_files($download_id) {
                return edd_get_download_files($download_id);
            }
            
            public function get_download_price_from_payment($download, $payment_id) {
                $download_price = 0;
                
                // Method 1: Direct price from download array
                if (isset($download['price']) && is_numeric($download['price'])) {
                    return floatval($download['price']);
                }
                
                // Method 2: Item price from download array
                if (isset($download['item_price']) && is_numeric($download['item_price'])) {
                    return floatval($download['item_price']);
                }
                
                // Method 3: Get from payment cart details
                $cart_details = edd_get_payment_meta_cart_details($payment_id);
                if (!empty($cart_details) && is_array($cart_details)) {
                    foreach ($cart_details as $cart_item) {
                        if (isset($cart_item['id']) && $cart_item['id'] == $download['id']) {
                            if (isset($cart_item['item_price']) && is_numeric($cart_item['item_price'])) {
                                return floatval($cart_item['item_price']);
                            } elseif (isset($cart_item['price']) && is_numeric($cart_item['price'])) {
                                return floatval($cart_item['price']);
                            }
                        }
                    }
                }
                
                // Method 4: Current download price (fallback)
                if (isset($download['options']['price_id'])) {
                    $fallback_price = edd_get_price_option_amount($download['id'], $download['options']['price_id']);
                } else {
                    $fallback_price = edd_get_download_price($download['id']);
                }
                
                return is_numeric($fallback_price) ? floatval($fallback_price) : 0;
            }
            
            public function get_downloads_remaining($download_id, $payment_id) {
                $download_limit = edd_get_file_download_limit($download_id);
                
                if (empty($download_limit) || $download_limit == 0) {
                    return __('Unlimited', 'edd-customer-dashboard-pro');
                }
                
                // Get download count from payment meta or logs
                $download_count = $this->get_file_download_count($download_id, $payment_id);
                $remaining = max(0, $download_limit - $download_count);
                
                return $remaining;
            }
            
            public function get_file_download_count($download_id, $payment_id) {
                global $wpdb;
                
                // Try to get from EDD logs table if it exists
                $logs_table = $wpdb->prefix . 'edd_logs';
                
                // Check if table exists
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
                $table_exists = $wpdb->get_var($wpdb->prepare(
                    "SHOW TABLES LIKE %s",
                    $wpdb->esc_like($logs_table)
                ));
                
                if ($table_exists === $logs_table) {
                    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                    $safe_table_name = esc_sql($logs_table);
                    
                    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                    $count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM `{$safe_table_name}` 
                        WHERE post_id = %d 
                        AND meta_value LIKE %s 
                        AND log_type = %s",
                        $download_id,
                        '%' . $wpdb->esc_like((string) $payment_id) . '%',
                        'file_download'
                    ));
                    
                    return intval($count);
                }
                
                // Fallback: check payment meta for download count
                $download_count = get_post_meta($payment_id, '_edd_download_count_' . $download_id, true);
                return intval($download_count);
            }
            
            public function get_license_sites($license_key) {
                if (!class_exists('EDD_Software_Licensing')) {
                    return array();
                }
                
                // Basic implementation - can be enhanced later
                return array();
            }
            
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
                        'label' => sprintf(__('Active (expires %s)', 'edd-customer-dashboard-pro'), $this->format_date($expiration)),
                        'days_remaining' => $days_remaining,
                        'is_expired' => false
                    );
                }
            }
            
            public function get_license_renewal_url($license) {
                if (!$this->is_licensing_active() || !$license) {
                    return '';
                }
                
                // Check if EDD Software Licensing has a renewal URL function
                if (function_exists('edd_sl_get_license_renewal_url')) {
                    return edd_sl_get_license_renewal_url($license->key);
                }
                
                // Fallback: Create a checkout URL with renewal parameters
                return edd_get_checkout_uri(array(
                    'edd_license_key' => $license->key,
                    'download_id' => $license->download_id,
                    'edd_action' => 'license_renewal'
                ));
            }
            
            public function get_license_upgrade_url($license) {
                if (!$this->is_licensing_active() || !$license) {
                    return '';
                }
                
                $download_id = $license->download_id;
                
                // Check if the download has variable pricing (potential upgrades)
                if (edd_has_variable_prices($download_id)) {
                    // Try to create an upgrade URL using EDD Software Licensing
                    if (function_exists('edd_sl_get_license_upgrade_url')) {
                        return edd_sl_get_license_upgrade_url($license->key);
                    }
                    
                    // Fallback: Create a checkout URL with license upgrade parameters
                    return edd_get_checkout_uri(array(
                        'edd_action' => 'license_upgrade',
                        'license_key' => $license->key,
                        'download_id' => $download_id
                    ));
                }
                
                // If no variable pricing, just link to the product page
                return get_permalink($download_id);
            }
            
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
            
            public function license_has_upgrades($license) {
                if (!$this->is_licensing_active() || !$license) {
                    return false;
                }
                
                $download_id = $license->download_id;
                
                // If the download has variable pricing, there might be upgrades available
                if (edd_has_variable_prices($download_id)) {
                    $prices = edd_get_variable_prices($download_id);
                    $current_price_id = isset($license->price_id) ? $license->price_id : null;
                    
                    // Check if there are price options with higher prices than current
                    if ($current_price_id !== null && isset($prices[$current_price_id])) {
                        $current_price = $prices[$current_price_id]['amount'];
                        
                        foreach ($prices as $price_id => $price_data) {
                            if ($price_data['amount'] > $current_price) {
                                return true; // Found a higher-priced option
                            }
                        }
                    }
                }
                
                return false;
            }
            
            public function get_company_address() {
                $address_parts = array();
                
                // Try to get from EDD settings first
                $company_name = edd_get_option('company_name', get_bloginfo('name'));
                $company_address = edd_get_option('company_address', '');
                $company_city = edd_get_option('company_city', '');
                $company_state = edd_get_option('company_state', '');
                $company_zip = edd_get_option('company_zip', '');
                $company_country = edd_get_option('company_country', '');
                
                // Build address
                if (!empty($company_address)) {
                    $address_parts[] = $company_address;
                }
                
                $city_state_zip = array();
                if (!empty($company_city)) {
                    $city_state_zip[] = $company_city;
                }
                if (!empty($company_state)) {
                    $city_state_zip[] = $company_state;
                }
                if (!empty($company_zip)) {
                    $city_state_zip[] = $company_zip;
                }
                
                if (!empty($city_state_zip)) {
                    $address_parts[] = implode(', ', $city_state_zip);
                }
                
                if (!empty($company_country)) {
                    $countries = edd_get_country_list();
                    $country_name = isset($countries[$company_country]) ? $countries[$company_country] : $company_country;
                    $address_parts[] = $country_name;
                }
                
                return implode('<br>', $address_parts);
            }
            
            public function get_pdf_invoice_url($payment) {
                // Check for EDD PDF Invoices plugin
                if (function_exists('eddpdfi_get_pdf_invoice_url')) {
                    return eddpdfi_get_pdf_invoice_url($payment->ID);
                }
                
                // Check for other PDF invoice plugins
                if (class_exists('EDD_PDF_Invoices')) {
                    return add_query_arg(array(
                        'edd_action' => 'generate_pdf_invoice',
                        'payment_id' => $payment->ID,
                        'payment_key' => $payment->key
                    ), home_url());
                }
                
                // Fallback - return current page with print parameter
                return add_query_arg('print', '1');
            }
            
            private function get_first_purchase_date($customer_id) {
                $payments = edd_get_payments(array(
                    'customer' => $customer_id,
                    'status' => 'complete',
                    'number' => 1,
                    'orderby' => 'date',
                    'order' => 'ASC'
                ));
                
                return $payments ? $payments[0]->date : null;
            }
            
            private function get_last_purchase_date($customer_id) {
                $payments = edd_get_payments(array(
                    'customer' => $customer_id,
                    'status' => 'complete',
                    'number' => 1,
                    'orderby' => 'date',
                    'order' => 'DESC'
                ));
                
                return $payments ? $payments[0]->date : null;
            }
        };
    }
    
    /**
     * Get customer stats with caching
     */
    public static function get_customer_stats($user_id) {
        // Try to get from cache first
        $cached_stats = EDDCDP_Cache_Helper::get_customer_analytics($user_id);
        if ($cached_stats !== false) {
            return $cached_stats;
        }
        
        // Get customer
        $customer = edd_get_customer_by('user_id', $user_id);
        if (!$customer) {
            return array(
                'total_purchases' => 0,
                'total_spent' => 0,
                'download_count' => 0,
                'active_licenses' => 0,
                'wishlist_count' => 0
            );
        }
        
        // Calculate stats
        $stats = array(
            'total_purchases' => $customer->purchase_count,
            'total_spent' => $customer->purchase_value,
            'download_count' => self::get_download_count($customer),
            'active_licenses' => self::get_active_licenses_count($customer->user_id),
            'wishlist_count' => self::get_wishlist_count($customer->user_id)
        );
        
        // Cache for 30 minutes
        EDDCDP_Cache_Helper::cache_customer_analytics($user_id, $stats);
        
        return $stats;
    }
    
    /**
     * Simple download count
     */
    private static function get_download_count($customer) {
        $payments = edd_get_payments(array(
            'customer' => $customer->id,
            'status' => 'complete',
            'number' => 9999
        ));
        
        $count = 0;
        if ($payments) {
            foreach ($payments as $payment) {
                $downloads = edd_get_payment_meta_downloads($payment->ID);
                if ($downloads) {
                    $count += count($downloads);
                }
            }
        }
        
        return $count;
    }
    
    /**
     * Simple active licenses count
     */
    private static function get_active_licenses_count($user_id) {
        if (!class_exists('EDD_Software_Licensing')) {
            return 0;
        }
        
        // Basic implementation - can be enhanced with the actual license integration
        return 0;
    }
    
    /**
     * Simple wishlist count
     */
    private static function get_wishlist_count($user_id) {
        if (!class_exists('EDD_Wish_Lists')) {
            return 0;
        }
        
        // Basic implementation - can be enhanced with the actual wishlist integration
        return 0;
    }
}