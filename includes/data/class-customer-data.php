<?php
/**
 * Customer Data Component
 * 
 * Handles all customer-related data operations using EDD native functions
 */

if (!defined('ABSPATH')) {
    exit;
}

class EDDCDP_Customer_Data implements EDDCDP_Component_Interface {
    
    private $edd_integration;
    
    /**
     * Initialize component
     */
    public function init() {
        // Component initialization
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
        return true; // Always load for customer data
    }
    
    /**
     * Get component priority
     */
    public function get_priority() {
        return 20; // Load after EDD integration
    }
    
    /**
     * Set EDD integration dependency
     */
    public function set_edd_integration($edd_integration) {
        $this->edd_integration = $edd_integration;
    }
    
    /**
     * Get comprehensive customer stats
     */
    public function get_customer_stats($user_id) {
        $customer = $this->get_customer($user_id);
        
        if (!$customer || !$customer->id) {
            return $this->get_empty_stats();
        }
        
        return array(
            'total_purchases' => $customer->purchase_count,
            'total_spent' => $customer->purchase_value,
            'download_count' => $this->get_download_count($customer),
            'active_licenses' => $this->get_active_licenses_count($customer),
            'wishlist_count' => $this->get_wishlist_count($user_id),
            'first_purchase' => $this->get_first_purchase_date($customer),
            'last_purchase' => $this->get_last_purchase_date($customer)
        );
    }
    
    /**
     * Get EDD customer object
     */
    public function get_customer($user_id) {
        return new EDD_Customer($user_id, true);
    }
    
    /**
     * Get customer purchases with enhanced data
     */
    public function get_customer_purchases($customer, $args = array()) {
        $defaults = array(
            'status' => array('complete', 'revoked'),
            'number' => 20,
            'orderby' => 'date',
            'order' => 'DESC'
        );
        
        $args = wp_parse_args($args, $defaults);
        $payments = $customer->get_payments($args);
        
        // Convert to EDD_Payment objects with additional data
        $enhanced_payments = array();
        foreach ($payments as $payment) {
            $payment_obj = new EDD_Payment($payment->ID);
            $enhanced_payments[] = $this->enhance_payment_data($payment_obj);
        }
        
        return $enhanced_payments;
    }
    
    /**
     * Enhance payment data with additional information
     */
    private function enhance_payment_data($payment) {
        $payment->downloads_data = $this->get_payment_downloads_data($payment);
        $payment->customer_data = $this->get_payment_customer_data($payment);
        $payment->formatted_total = $this->format_currency($payment->total);
        $payment->formatted_date = $this->format_date($payment->date);
        $payment->status_label = $payment->get_status_label();
        
        return $payment;
    }
    
    /**
     * Get payment downloads with enhanced data
     */
    private function get_payment_downloads_data($payment) {
        $downloads = $payment->get_downloads();
        $enhanced_downloads = array();
        
        foreach ($downloads as $download) {
            $download_obj = new EDD_Download($download['id']);
            
            $enhanced_downloads[] = array(
                'id' => $download['id'],
                'name' => $download_obj->get_name(),
                'price' => $this->get_download_price_from_payment($download, $payment->ID),
                'files' => $download_obj->get_files(),
                'can_download' => $this->can_download_file($payment, $download['id']),
                'download_url' => $this->get_download_url($payment->key, $payment->email, 0, $download['id']),
                'download_limit' => $download_obj->get_download_limit(),
                'downloads_remaining' => $this->get_downloads_remaining($download['id'], $payment->ID)
            );
        }
        
        return $enhanced_downloads;
    }
    
    /**
     * Get payment customer data
     */
    private function get_payment_customer_data($payment) {
        $customer = $payment->get_customer();
        $user_info = $payment->get_user_info();
        
        return array(
            'customer' => $customer,
            'user_info' => $user_info,
            'email' => $payment->email,
            'address' => isset($user_info['address']) ? $user_info['address'] : array()
        );
    }
    
    /**
     * Get download count for customer
     */
    private function get_download_count($customer) {
        $payments = $customer->get_payments(array('status' => 'complete'));
        $count = 0;
        
        foreach ($payments as $payment) {
            $payment_obj = new EDD_Payment($payment->ID);
            $downloads = $payment_obj->get_downloads();
            $count += count($downloads);
        }
        
        return $count;
    }
    
    /**
     * Get active licenses count
     */
    private function get_active_licenses_count($customer) {
        if (!$this->edd_integration || !$this->edd_integration->is_licensing_active()) {
            return 0;
        }
        
        return $this->edd_integration->get_active_licenses_count($customer->user_id);
    }
    
    /**
     * Get wishlist count
     */
    private function get_wishlist_count($user_id) {
        if (!$this->edd_integration || !$this->edd_integration->is_wishlist_active()) {
            return 0;
        }
        
        return $this->edd_integration->get_wishlist_count($user_id);
    }
    
    /**
     * Get customer analytics data
     */
    public function get_customer_analytics($customer) {
        $stats = $this->get_customer_stats($customer->user_id);
        
        return array(
            'total_spent' => $stats['total_spent'],
            'purchase_count' => $stats['total_purchases'],
            'avg_per_order' => $stats['total_purchases'] > 0 ? ($stats['total_spent'] / $stats['total_purchases']) : 0,
            'first_purchase' => $stats['first_purchase'],
            'last_purchase' => $stats['last_purchase'],
            'download_count' => $stats['download_count'],
            'active_licenses' => $stats['active_licenses'],
            'customer_lifetime_days' => $this->get_customer_lifetime_days($stats['first_purchase'])
        );
    }
    
    /**
     * Get customer lifetime in days
     */
    private function get_customer_lifetime_days($first_purchase) {
        if (!$first_purchase) {
            return 0;
        }
        
        return round((time() - strtotime($first_purchase)) / DAY_IN_SECONDS);
    }
    
    /**
     * Get first purchase date
     */
    private function get_first_purchase_date($customer) {
        $payments = $customer->get_payments(array(
            'status' => 'complete',
            'number' => 1,
            'orderby' => 'date',
            'order' => 'ASC'
        ));
        
        return !empty($payments) ? $payments[0]->date : null;
    }
    
    /**
     * Get last purchase date
     */
    private function get_last_purchase_date($customer) {
        $payments = $customer->get_payments(array(
            'status' => 'complete',
            'number' => 1,
            'orderby' => 'date',
            'order' => 'DESC'
        ));
        
        return !empty($payments) ? $payments[0]->date : null;
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
     * Check if user can download file
     */
    private function can_download_file($payment, $download_id) {
        return $payment->is_complete() && edd_can_view_receipt($payment->key);
    }
    
    /**
     * Get download URL
     */
    private function get_download_url($payment_key, $email, $file_key, $download_id) {
        if (!edd_can_view_receipt($payment_key)) {
            return false;
        }
        
        return edd_get_download_file_url($payment_key, $email, $file_key, $download_id);
    }
    
    /**
     * Get downloads remaining
     */
    private function get_downloads_remaining($download_id, $payment_id) {
        if (function_exists('edd_get_download_limit_remaining')) {
            $remaining = edd_get_download_limit_remaining($download_id, $payment_id);
            return $remaining === false ? __('Unlimited', 'edd-customer-dashboard-pro') : $remaining;
        }
        
        $download = new EDD_Download($download_id);
        $download_limit = $download->get_download_limit();
        
        if (empty($download_limit) || $download_limit == 0) {
            return __('Unlimited', 'edd-customer-dashboard-pro');
        }
        
        $download_count = $this->get_file_download_count($download_id, $payment_id);
        return max(0, $download_limit - $download_count);
    }
    
    /**
     * Get file download count
     */
    private function get_file_download_count($download_id, $payment_id) {
        if (function_exists('edd_get_download_logs')) {
            $logs = edd_get_download_logs(array(
                'post_id' => $download_id,
                'meta_query' => array(
                    array(
                        'key' => '_edd_log_payment_id',
                        'value' => $payment_id,
                        'compare' => '='
                    )
                )
            ));
            
            return count($logs);
        }
        
        $download_count = get_post_meta($payment_id, '_edd_download_count_' . $download_id, true);
        return intval($download_count);
    }
    
    /**
     * Get download price from payment
     */
    private function get_download_price_from_payment($download, $payment_id) {
        $payment = new EDD_Payment($payment_id);
        $cart_details = $payment->get_cart_details();
        
        foreach ($cart_details as $item) {
            if ($item['id'] == $download['id']) {
                return isset($item['item_price']) ? floatval($item['item_price']) : 0;
            }
        }
        
        $download_obj = new EDD_Download($download['id']);
        return $download_obj->get_price();
    }
    
    /**
     * Format currency
     */
    private function format_currency($amount) {
        return edd_currency_filter(edd_format_amount($amount));
    }
    
    /**
     * Format date
     */
    private function format_date($date) {
        return date_i18n(get_option('date_format'), strtotime($date));
    }
    
    /**
     * Get empty stats array
     */
    private function get_empty_stats() {
        return array(
            'total_purchases' => 0,
            'total_spent' => 0,
            'download_count' => 0,
            'active_licenses' => 0,
            'wishlist_count' => 0,
            'first_purchase' => null,
            'last_purchase' => null
        );
    }
}