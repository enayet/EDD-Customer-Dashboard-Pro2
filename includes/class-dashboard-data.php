<?php
/**
 * Dashboard Data Class - Fixed superglobal array index issues
 * 
 * Handles data retrieval and processing for dashboard sections
 */

if (!defined('ABSPATH')) {
    exit;
}

class EDDCDP_Dashboard_Data {
    
    public function __construct() {
        // Add AJAX handlers
        add_action('wp_ajax_eddcdp_activate_license', array($this, 'ajax_activate_license'));
        add_action('wp_ajax_eddcdp_deactivate_license', array($this, 'ajax_deactivate_license'));
        add_action('wp_ajax_eddcdp_remove_wishlist', array($this, 'ajax_remove_wishlist'));
    }
    
    /**
     * Get user initials for avatar
     */
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
    
    /**
     * Get total download count for customer
     */
    public function get_download_count($customer_id) {
        $payments = edd_get_payments(array(
            'customer' => $customer_id,
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
     * Get active licenses count
     */
    public function get_active_licenses_count($customer_id) {
        if (!class_exists('EDD_Software_Licensing')) {
            return 0;
        }
        
        $licensing = edd_software_licensing();
        if (!$licensing) {
            return 0;
        }
        
        $customer = edd_get_customer($customer_id);
        if (!$customer || !$customer->user_id) {
            return 0;
        }
        
        $licenses = $licensing->get_license_keys_of_user($customer->user_id);
        $active = 0;
        
        if ($licenses) {
            foreach ($licenses as $license) {
                if ($license->is_expired() === false) {
                    $active++;
                }
            }
        }
        
        return $active;
    }
    
    /**
     * Get wishlist items count
     */
    public function get_wishlist_count($user_id) {
        if (!class_exists('EDD_Wish_Lists')) {
            return 0;
        }
        
        $wish_lists = edd_wl_get_wish_lists(array('user_id' => $user_id));
        $count = 0;
        
        if ($wish_lists) {
            foreach ($wish_lists as $list) {
                $items = edd_wl_get_list_items($list->ID);
                if ($items) {
                    $count += count($items);
                }
            }
        }
        
        return $count;
    }
    
    /**
     * Get customer purchases
     */
    public function get_customer_purchases($customer, $limit = 20) {
        $payments = edd_get_payments(array(
            'customer' => $customer->id,
            'status' => array('complete', 'revoked'),
            'number' => $limit
        ));
        
        return $payments ? $payments : array();
    }
    
    /**
     * Get customer downloads
     */
    public function get_customer_downloads($customer) {
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
                        $download_limit = edd_get_file_download_limit($download['id']);
                        $downloads_remaining = $this->get_downloads_remaining($download['id'], $payment->ID, $download_limit);
                        
                        $downloads[] = array(
                            'payment' => $payment,
                            'download' => $download,
                            'download_limit' => $download_limit,
                            'downloads_remaining' => $downloads_remaining
                        );
                    }
                }
            }
        }
        
        return $downloads;
    }
    
    /**
     * Get remaining downloads for a file
     */
    private function get_downloads_remaining($download_id, $payment_id, $download_limit) {
        if (empty($download_limit) || $download_limit == 0) {
            return __('Unlimited', 'edd-customer-dashboard-pro');
        }
        
        // Get download count from payment meta or logs
        $download_count = $this->get_file_download_count($download_id, $payment_id);
        $remaining = max(0, $download_limit - $download_count);
        
        return $remaining;
    }
    
    /**
     * Get file download count for a specific payment
     */
    private function get_file_download_count($download_id, $payment_id) {
        global $wpdb;
        
        // Try to get from EDD logs table if it exists
        $logs_table = $wpdb->prefix . 'edd_logs';
        
        // Check if table exists - FIXED: Use esc_like() for table name safety
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        $table_exists = $wpdb->get_var($wpdb->prepare(
            "SHOW TABLES LIKE %s",
            $wpdb->esc_like($logs_table)
        ));
        
        if ($table_exists === $logs_table) {
            // FIXED: Properly prepare the entire query including table name safety
            // Since table names can't be parameterized, we validate it's safe first
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
    
    /**
     * Get customer licenses
     */
    public function get_customer_licenses($user_id) {
        if (!class_exists('EDD_Software_Licensing')) {
            return array();
        }
        
        $licensing = edd_software_licensing();
        if (!$licensing) {
            return array();
        }
        
        $licenses = $licensing->get_license_keys_of_user($user_id);
        return $licenses ? $licenses : array();
    }
    
    /**
     * Get customer wishlist items
     */
    public function get_customer_wishlist($user_id) {
        if (!class_exists('EDD_Wish_Lists')) {
            return array();
        }
        
        $wish_lists = edd_wl_get_wish_lists(array('user_id' => $user_id));
        $items = array();
        
        if ($wish_lists) {
            foreach ($wish_lists as $list) {
                $list_items = edd_wl_get_list_items($list->ID);
                if ($list_items) {
                    $items = array_merge($items, $list_items);
                }
            }
        }
        
        return $items;
    }
    
    /**
     * Get customer analytics data
     */
    public function get_customer_analytics($customer) {
        $total_spent = $customer->purchase_value;
        $purchase_count = edd_count_purchases_of_customer($customer->id);
        $avg_per_order = $purchase_count > 0 ? $total_spent / $purchase_count : 0;
        
        return array(
            'total_spent' => $total_spent,
            'purchase_count' => $purchase_count,
            'avg_per_order' => $avg_per_order,
            'first_purchase' => $this->get_first_purchase_date($customer->id),
            'last_purchase' => $this->get_last_purchase_date($customer->id)
        );
    }
    
    /**
     * Get first purchase date
     */
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
    
    /**
     * Get last purchase date
     */
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
    
    /**
     * Format download file URL
     */
    public function get_download_url($payment_key, $email, $file_key, $download_id) {
        if (!edd_can_view_receipt($payment_key)) {
            return false;
        }
        
        return edd_get_download_file_url($payment_key, $email, $file_key, $download_id);
    }
    
    /**
     * Check if user can download file
     */
    public function can_download_file($payment, $download_id) {
        return edd_is_payment_complete($payment->ID) && edd_can_view_receipt($payment->key);
    }
    
    /**
     * Get download files for a product
     */
    public function get_download_files($download_id) {
        return edd_get_download_files($download_id);
    }
    
    /**
     * Format currency amount
     */
//    public function format_currency($amount) {
//        return edd_currency_filter(edd_format_amount($amount));
//    }
    
    
    public function format_currency($amount) {
        // Debug logging if WP_DEBUG is enabled
        if (defined('WP_DEBUG') && WP_DEBUG && !is_numeric($amount) && !is_null($amount)) {
            error_log('EDDCDP: Invalid amount passed to format_currency: ' . print_r($amount, true));
        }
        
        // Ensure we have a numeric value
        if (is_array($amount)) {
            // If it's an array, try to extract a numeric value
            if (isset($amount['amount'])) {
                $amount = $amount['amount'];
            } elseif (isset($amount['price'])) {
                $amount = $amount['price'];
            } elseif (isset($amount['item_price'])) {
                $amount = $amount['item_price'];
            } else {
                // If we can't find a numeric value, default to 0
                $amount = 0;
            }
        }
        
        // Handle null or non-numeric values
        if (is_null($amount) || $amount === '' || $amount === false) {
            $amount = 0;
        }
        
        // Convert to float and ensure it's numeric
        $amount = is_numeric($amount) ? floatval($amount) : 0;
        
        return edd_currency_filter(edd_format_amount($amount));
    }    
    
    /**
     * Format date
     */
    public function format_date($date) {
        return date_i18n(get_option('date_format'), strtotime($date));
    }
    
    /**
     * Get payment status label
     */
    public function get_payment_status_label($payment) {
        return edd_get_payment_status($payment, true);
    }
    
    /**
     * Get receipt URL
     */
    public function get_receipt_url($payment) {
        return edd_get_success_page_uri('?payment_key=' . $payment->key);
    }
    
    /**
     * Check if Software Licensing is active
     */
    public function is_licensing_active() {
        return class_exists('EDD_Software_Licensing');
    }
    
    /**
     * Check if Wish Lists is active
     */
    public function is_wishlist_active() {
        return class_exists('EDD_Wish_Lists');
    }
    
    /**
     * Get license sites
     */
    public function get_license_sites($license_key) {
        if (!$this->is_licensing_active()) {
            return array();
        }
        
        $license = edd_software_licensing()->get_license($license_key);
        if (!$license) {
            return array();
        }
        
        return $license->get_sites();
    }
    
    /**
     * AJAX: Activate license - FIXED superglobal array access
     */
    public function ajax_activate_license() {
        // FIXED: Check if nonce exists before accessing it
        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'eddcdp_nonce')) {
            wp_send_json_error(__('Security verification failed.', 'edd-customer-dashboard-pro'));
        }
        
        if (!is_user_logged_in()) {
            wp_send_json_error(__('Please log in first.', 'edd-customer-dashboard-pro'));
        }
        
        // FIXED: Check if required POST data exists before accessing
        if (!isset($_POST['license_key']) || !isset($_POST['site_url'])) {
            wp_send_json_error(__('Required parameters missing.', 'edd-customer-dashboard-pro'));
        }
        
        $license_key = sanitize_text_field(wp_unslash($_POST['license_key']));
        $site_url = esc_url_raw(wp_unslash($_POST['site_url']));
        
        if (empty($license_key) || empty($site_url)) {
            wp_send_json_error(__('License key and site URL are required.', 'edd-customer-dashboard-pro'));
        }
        
        if (!$this->is_licensing_active()) {
            wp_send_json_error(__('Software Licensing is not active.', 'edd-customer-dashboard-pro'));
        }
        
        // Get license
        $license = edd_software_licensing()->get_license($license_key);
        if (!$license) {
            wp_send_json_error(__('Invalid license key.', 'edd-customer-dashboard-pro'));
        }
        
        // Check if user owns this license
        if ($license->user_id != get_current_user_id()) {
            wp_send_json_error(__('You do not own this license.', 'edd-customer-dashboard-pro'));
        }
        
        // Check if license is expired
        if ($license->is_expired()) {
            wp_send_json_error(__('This license has expired.', 'edd-customer-dashboard-pro'));
        }
        
        // Check activation limit
        if ($license->activation_limit > 0 && $license->activation_count >= $license->activation_limit) {
            wp_send_json_error(__('License activation limit reached.', 'edd-customer-dashboard-pro'));
        }
        
        // Activate license
        $result = $license->add_site($site_url);
        
        if ($result) {
            wp_send_json_success(__('License activated successfully!', 'edd-customer-dashboard-pro'));
        } else {
            wp_send_json_error(__('Failed to activate license. The site may already be activated.', 'edd-customer-dashboard-pro'));
        }
    }
    
    /**
     * AJAX: Deactivate license - FIXED superglobal array access
     */
    public function ajax_deactivate_license() {
        // FIXED: Check if nonce exists before accessing it
        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'eddcdp_nonce')) {
            wp_send_json_error(__('Security verification failed.', 'edd-customer-dashboard-pro'));
        }
        
        if (!is_user_logged_in()) {
            wp_send_json_error(__('Please log in first.', 'edd-customer-dashboard-pro'));
        }
        
        // FIXED: Check if required POST data exists before accessing
        if (!isset($_POST['license_key']) || !isset($_POST['site_url'])) {
            wp_send_json_error(__('Required parameters missing.', 'edd-customer-dashboard-pro'));
        }
        
        $license_key = sanitize_text_field(wp_unslash($_POST['license_key']));
        $site_url = esc_url_raw(wp_unslash($_POST['site_url']));
        
        if (empty($license_key) || empty($site_url)) {
            wp_send_json_error(__('License key and site URL are required.', 'edd-customer-dashboard-pro'));
        }
        
        if (!$this->is_licensing_active()) {
            wp_send_json_error(__('Software Licensing is not active.', 'edd-customer-dashboard-pro'));
        }
        
        // Get license
        $license = edd_software_licensing()->get_license($license_key);
        if (!$license) {
            wp_send_json_error(__('Invalid license key.', 'edd-customer-dashboard-pro'));
        }
        
        // Check if user owns this license
        if ($license->user_id != get_current_user_id()) {
            wp_send_json_error(__('You do not own this license.', 'edd-customer-dashboard-pro'));
        }
        
        // Deactivate license
        $result = $license->remove_site($site_url);
        
        if ($result) {
            wp_send_json_success(__('License deactivated successfully!', 'edd-customer-dashboard-pro'));
        } else {
            wp_send_json_error(__('Failed to deactivate license.', 'edd-customer-dashboard-pro'));
        }
    }
    
    /**
     * AJAX: Remove wishlist item - FIXED superglobal array access
     */
    public function ajax_remove_wishlist() {
        // FIXED: Check if nonce exists before accessing it
        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'eddcdp_nonce')) {
            wp_send_json_error(__('Security verification failed.', 'edd-customer-dashboard-pro'));
        }
        
        if (!is_user_logged_in()) {
            wp_send_json_error(__('Please log in first.', 'edd-customer-dashboard-pro'));
        }
        
        // FIXED: Check if download_id exists before accessing
        if (!isset($_POST['download_id'])) {
            wp_send_json_error(__('Download ID parameter missing.', 'edd-customer-dashboard-pro'));
        }
        
        $download_id = absint($_POST['download_id']);
        
        if (empty($download_id)) {
            wp_send_json_error(__('Download ID is required.', 'edd-customer-dashboard-pro'));
        }
        
        if (!$this->is_wishlist_active()) {
            wp_send_json_error(__('Wish Lists is not active.', 'edd-customer-dashboard-pro'));
        }
        
        // Get user's wishlist
        $wish_lists = edd_wl_get_wish_lists(array('user_id' => get_current_user_id()));
        
        if (!$wish_lists) {
            wp_send_json_error(__('No wishlist found.', 'edd-customer-dashboard-pro'));
        }
        
        $removed = false;
        foreach ($wish_lists as $list) {
            $result = edd_wl_remove_from_list($download_id, $list->ID);
            if ($result) {
                $removed = true;
                break;
            }
        }
        
        if ($removed) {
            wp_send_json_success(__('Item removed from wishlist!', 'edd-customer-dashboard-pro'));
        } else {
            wp_send_json_error(__('Failed to remove item from wishlist.', 'edd-customer-dashboard-pro'));
        }
    }
    
    
    
/**
     * Format currency amount - Enhanced with error checking
     */


    
    /**
     * Safely get download price from various sources
     */
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
    
    
}