<?php
/**
 * Simple Order Details Handler
 * Uses page reload instead of modal
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
        add_action('init', array($this, 'handle_order_details_request'));
    }
    
    /**
     * Handle order details request via URL parameter
     */
    public function handle_order_details_request() {
        // Check if we have an order details request
        if (!isset($_GET['eddcdp_order']) || !is_user_logged_in()) {
            return;
        }
        
        $order_id = intval($_GET['eddcdp_order']);
        if (!$order_id) {
            return;
        }
        
        // Verify user can access this order
        $order = edd_get_order($order_id);
        if (!$order) {
            return;
        }
        
        $customer = edd_get_customer_by('email', wp_get_current_user()->user_email);
        if (!$customer || $order->customer_id != $customer->id) {
            wp_die(__('You do not have permission to view this order.', 'eddcdp'));
        }
        
        // Store order data in global for template use
        global $eddcdp_current_order;
        $eddcdp_current_order = $order;
    }
    
    /**
     * Check if we're viewing order details
     */
    public function is_viewing_order_details() {
        return isset($_GET['eddcdp_order']) && is_user_logged_in();
    }
    
    /**
     * Get current order being viewed
     */
    public function get_current_order() {
        global $eddcdp_current_order;
        return $eddcdp_current_order;
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
     * Get return URL
     */
    public function get_return_url() {
        if (isset($_GET['return_url'])) {
            return urldecode($_GET['return_url']);
        }
        
        // Remove order parameter to go back to main dashboard
        return remove_query_arg('eddcdp_order', get_permalink());
    }
}