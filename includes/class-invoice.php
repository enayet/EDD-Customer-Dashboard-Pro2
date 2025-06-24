<?php
/**
 * Invoice URL Redirect Handler - Simple Approach
 */

if (!defined('ABSPATH')) {
    exit;
}

class EDDCDP_Invoice_Redirect {
    
    private static $instance = null;
    
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function __construct() {
        // Hook very early to catch the invoice URL
        add_action('parse_request', array($this, 'intercept_invoice_url'), 1);
        //add_action('init', array($this, 'check_invoice_request'), 1);
    }
    
    /**
     * Check for invoice URL pattern and redirect
     */
    public function check_invoice_request() {
        
        // Get the current request URI
        $request_uri = $_SERVER['REQUEST_URI'] ?? '';
        
        // Check if URL contains 'invoice/' pattern
        if (strpos($request_uri, 'invoice/') === false && strpos($request_uri, '/invoice') === false) {
            return;
        }
        
        // Check if we have the required parameters
        if (!isset($_GET['payment_id']) || !isset($_GET['invoice'])) {
            return;
        }
        
        // Validate user is logged in
        if (!is_user_logged_in()) {
            return;
        }
        
        $payment_id = intval($_GET['payment_id']);
        $invoice_hash = sanitize_text_field($_GET['invoice']);

        
        // Validate the order belongs to current user
        if (!$this->validate_user_access($payment_id, $invoice_hash)) {
            return;
        }
        
        // Find dashboard URL
        $dashboard_url = $this->find_dashboard_page();
        if (!$dashboard_url) {
            // No dashboard found, let it continue normally
            return;
        }
        
        // Build redirect URL to our dashboard
        $redirect_url = add_query_arg(array(
            'eddcdp_invoice_form' => 1,
            'payment_id' => $payment_id,
            'invoice' => $invoice_hash
        ), $dashboard_url);
        

        // Perform redirect
        wp_redirect($redirect_url);
        exit;
    }
    
    /**
     * Validate user can access this invoice
     */
    private function validate_user_access($payment_id, $invoice_hash) {
        // Get the order
        $order = edd_get_order($payment_id);
        if (!$order) {
            return false;
        }
        
        // Check if user owns this order
        $customer = edd_get_customer_by('email', wp_get_current_user()->user_email);
        if (!$customer || $order->customer_id != $customer->id) {
            return false;
        }
        
        if ($order->customer_id != $customer->id) {
            error_log("EDDCDP Debug: Customer ID mismatch - Order: {$order->customer_id}, Customer: {$customer->id}");
            return false;
        }
        
        return true;
    }
    
    /**
     * Find dashboard page URL
     */
    public function find_dashboard_page() {
        // Check cache first
        $cached_url = get_transient('eddcdp_dashboard_url');
        if ($cached_url) {
            return $cached_url;
        }
        
        global $wpdb;
        
        // Look for our main shortcode
        $pages = $wpdb->get_results($wpdb->prepare(
            "SELECT ID FROM {$wpdb->posts} 
            WHERE post_type = 'page' 
            AND post_status = 'publish' 
            AND post_content LIKE %s 
            ORDER BY menu_order ASC, ID ASC 
            LIMIT 1",
            '%[edd_customer_dashboard_pro%'
        ));
        
        if (!empty($pages)) {
            $url = get_permalink($pages[0]->ID);
            set_transient('eddcdp_dashboard_url', $url, HOUR_IN_SECONDS);
            return $url;
        }
        
        // Check if replace EDD pages is enabled
        $settings = get_option('eddcdp_settings', array());
        if (!empty($settings['replace_edd_pages'])) {
            // Check EDD's purchase history page
            $purchase_history_page = edd_get_option('purchase_history_page');
            if ($purchase_history_page) {
                $url = get_permalink($purchase_history_page);
                set_transient('eddcdp_dashboard_url', $url, HOUR_IN_SECONDS);
                return $url;
            }
        }
        
        return false;
    }
    
    /**
     * Alternative method using parse_request hook
     */
    public function intercept_invoice_url($wp) {

        // Check if this is an invoice request
        if (!isset($wp->request)) {
            return;
        }
        
        // Look for 'invoice' in the request path
        if (strpos($wp->request, 'invoice') !== false) {
            // Let our check_invoice_request method handle it
            add_action('template_redirect', array($this, 'check_invoice_request'), 1);
        }
    }
}

// Don't initialize here - let main plugin file handle it