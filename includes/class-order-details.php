<?php
/**
 * Enhanced Order Details Handler
 * Handles both order details and order-specific license management
 */

if (!defined('ABSPATH')) {
    exit;
}

class EDDCDP_Order_Details {
    
    private static $instance = null;
    
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('init', array($this, 'handle_requests'));
    }
    
    /**
     * Handle both order details and license requests
     */
    public function handle_requests() {
        if (!is_user_logged_in()) {
            return;
        }
        
        // Handle order details request
        if (isset($_GET['eddcdp_order'])) {
            $this->handle_order_details_request();
        }
        
        // Handle order licenses request
        if (isset($_GET['eddcdp_order_licenses'])) {
            $this->handle_order_licenses_request();
        }
    }
    
    /**
     * Handle order details request via URL parameter
     */
    private function handle_order_details_request() {
        $order_id = intval($_GET['eddcdp_order']);
        if (!$order_id) {
            return;
        }
        
        $order = $this->validate_user_order_access($order_id);
        if (!$order) {
            return;
        }
        
        // Store order data in global for template use
        global $eddcdp_current_order;
        $eddcdp_current_order = $order;
    }
    
    /**
     * Handle order licenses request
     */
    private function handle_order_licenses_request() {
        $order_id = intval($_GET['eddcdp_order_licenses']);
        if (!$order_id) {
            return;
        }
        
        $order = $this->validate_user_order_access($order_id);
        if (!$order) {
            return;
        }
        
        // Store order data for license template
        global $eddcdp_current_order_licenses;
        $eddcdp_current_order_licenses = $order;
    }
    
    /**
     * Validate user can access this order
     */
    private function validate_user_order_access($order_id) {
        $order = edd_get_order($order_id);
        if (!$order) {
            return false;
        }
        
        $customer = edd_get_customer_by('email', wp_get_current_user()->user_email);
        if (!$customer || $order->customer_id != $customer->id) {
            wp_die(__('You do not have permission to view this order.', 'eddcdp'));
        }
        
        return $order;
    }
    
    /**
     * Check if we're viewing order details
     */
    public function is_viewing_order_details() {
        return isset($_GET['eddcdp_order']) && is_user_logged_in();
    }
    
    /**
     * Check if we're viewing order licenses
     */
    public function is_viewing_order_licenses() {
        return isset($_GET['eddcdp_order_licenses']) && is_user_logged_in();
    }
    
    /**
     * Check if we're viewing any order-specific page
     */
    public function is_viewing_order_page() {
        return $this->is_viewing_order_details() || $this->is_viewing_order_licenses();
    }
    
    /**
     * Get current order being viewed
     */
    public function get_current_order() {
        global $eddcdp_current_order;
        return $eddcdp_current_order;
    }
    
    /**
     * Get current order for licenses
     */
    public function get_current_order_licenses() {
        global $eddcdp_current_order_licenses;
        return $eddcdp_current_order_licenses;
    }
    
    /**
     * Generate order details URL
     */
    public function get_order_details_url($order_id, $return_url = '') {
        $url = add_query_arg('eddcdp_order', $order_id, get_permalink());
        
        if ($return_url) {
            $url = add_query_arg('return_url', urlencode($return_url), $url);
        }
        
        return $url;
    }
    
    /**
     * Generate order licenses URL
     */
    public function get_order_licenses_url($order_id, $return_url = '') {
        $url = add_query_arg('eddcdp_order_licenses', $order_id, get_permalink());
        
        if ($return_url) {
            $url = add_query_arg('return_url', urlencode($return_url), $url);
        }
        
        return $url;
    }
    
    /**
     * Get return URL
     */
    public function get_return_url() {
        if (isset($_GET['return_url'])) {
            return urldecode($_GET['return_url']);
        }
        
        // Remove all order parameters to go back to main dashboard
        $current_url = get_permalink();
        $current_url = remove_query_arg('eddcdp_order', $current_url);
        $current_url = remove_query_arg('eddcdp_order_licenses', $current_url);
        
        return $current_url;
    }
    
    /**
     * Get order license summary for display
     */
    public function get_order_license_summary($order_id) {
        return eddcdp_get_order_license_summary($order_id);
    }
    
    /**
     * Check if order has downloadable files
     */
    public function order_has_downloads($order_id) {
        $order = edd_get_order($order_id);
        if (!$order || $order->status !== 'complete') {
            return false;
        }
        
        $order_items = $order->get_items();
        foreach ($order_items as $item) {
            $download_files = edd_get_download_files($item->product_id);
            if (!empty($download_files)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get formatted order status
     */
    public function get_formatted_order_status($order) {
        $status = $order->status;
        
        $status_config = array(
            'complete' => array(
                'label' => __('Completed', 'eddcdp'),
                'icon' => '✅',
                'class' => 'bg-green-100 text-green-800'
            ),
            'pending' => array(
                'label' => __('Pending', 'eddcdp'),
                'icon' => '⏳',
                'class' => 'bg-yellow-100 text-yellow-800'
            ),
            'processing' => array(
                'label' => __('Processing', 'eddcdp'),
                'icon' => '⚙️',
                'class' => 'bg-blue-100 text-blue-800'
            ),
            'failed' => array(
                'label' => __('Failed', 'eddcdp'),
                'icon' => '❌',
                'class' => 'bg-red-100 text-red-800'
            )
        );
        
        if (isset($status_config[$status])) {
            return $status_config[$status];
        }
        
        // Default fallback
        return array(
            'label' => ucfirst($status),
            'icon' => '📋',
            'class' => 'bg-gray-100 text-gray-800'
        );
    }
    
    /**
     * Get download files for order item
     */
    public function get_order_item_download_files($order_id, $download_id, $price_id = null) {
        $order = edd_get_order($order_id);
        if (!$order || $order->status !== 'complete') {
            return array();
        }
        
        $files = edd_get_download_files($download_id, $price_id);
        $download_files = array();
        
        if ($files) {
            $customer = edd_get_customer($order->customer_id);
            
            foreach ($files as $file_key => $file) {
                $download_url = edd_get_download_file_url($order->payment_key, $customer->email, $file_key, $download_id);
                
                $download_files[] = array(
                    'name' => $file['name'],
                    'file' => $file['file'],
                    'url' => $download_url,
                    'key' => $file_key
                );
            }
        }
        
        return $download_files;
    }
    
    /**
     * Check if user can download order files
     */
    public function can_download_order_files($order_id) {
        $order = edd_get_order($order_id);
        if (!$order) {
            return false;
        }
        
        // Order must be complete
        if ($order->status !== 'complete') {
            return false;
        }
        
        // Check if user owns this order
        $customer = edd_get_customer_by('email', wp_get_current_user()->user_email);
        if (!$customer || $order->customer_id != $customer->id) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Get order invoice URL if EDD Invoices is active
     */
    public function get_order_invoice_url($order_id) {
        return eddcdp_get_invoice_url($order_id);
    }
    
    /**
     * Check if order has invoice capability
     */
    public function order_has_invoice($order_id) {
        return $this->get_order_invoice_url($order_id) !== false;
    }
    
    /**
     * Get order receipt URL
     */
    public function get_order_receipt_url($order_id) {
        if (function_exists('edd_get_order_receipt_url')) {
            return edd_get_order_receipt_url($order_id);
        }
        
        // Fallback for older EDD versions
        if (function_exists('edd_get_success_page_uri')) {
            return add_query_arg('payment_key', edd_get_order($order_id)->payment_key, edd_get_success_page_uri());
        }
        
        return false;
    }
    
    /**
     * Format order date for display
     */
    public function format_order_date($date_string, $format = '') {
        if (empty($format)) {
            $format = get_option('date_format') . ' ' . get_option('time_format');
        }
        
        return date_i18n($format, strtotime($date_string));
    }
    
    /**
     * Get order payment method display name
     */
    public function get_order_payment_method($order) {
        $gateways = edd_get_payment_gateways();
        
        if (isset($gateways[$order->gateway])) {
            return $gateways[$order->gateway]['admin_label'];
        }
        
        return ucfirst($order->gateway);
    }
    
    /**
     * Check if order has billing address
     */
    public function order_has_billing_address($order) {
        $billing_address = $order->get_address();
        
        if (!$billing_address) {
            return false;
        }
        
        // Check if any address fields are populated
        $address_fields = array('line1', 'line2', 'city', 'region', 'postal_code', 'country');
        foreach ($address_fields as $field) {
            if (!empty($billing_address->$field)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Format billing address for display
     */
    public function format_billing_address($order) {
        $billing_address = $order->get_address();
        if (!$billing_address || !$this->order_has_billing_address($order)) {
            return '';
        }
        
        $address_parts = array();
        
        if (!empty($billing_address->line1)) {
            $address_parts[] = esc_html($billing_address->line1);
        }
        
        if (!empty($billing_address->line2)) {
            $address_parts[] = esc_html($billing_address->line2);
        }
        
        $city_line = array();
        if (!empty($billing_address->city)) {
            $city_line[] = esc_html($billing_address->city);
        }
        if (!empty($billing_address->region)) {
            $city_line[] = esc_html($billing_address->region);
        }
        if (!empty($billing_address->postal_code)) {
            $city_line[] = esc_html($billing_address->postal_code);
        }
        
        if (!empty($city_line)) {
            $address_parts[] = implode(', ', $city_line);
        }
        
        if (!empty($billing_address->country)) {
            $address_parts[] = esc_html($billing_address->country);
        }
        
        return implode('<br>', $address_parts);
    }
}