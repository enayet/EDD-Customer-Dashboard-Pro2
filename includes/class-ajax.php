<?php
/**
 * AJAX Handler Class - Simplified (No License Management)
 */

if (!defined('ABSPATH')) {
    exit;
}

class EDDCDP_Ajax {
    
    private static $instance = null;
    
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function __construct() {
        // Public AJAX actions (for logged-in users)
        add_action('wp_ajax_eddcdp_remove_from_wishlist', array($this, 'remove_from_wishlist'));
        add_action('wp_ajax_eddcdp_add_to_cart', array($this, 'add_to_cart'));
        
        // Admin-only AJAX actions
        add_action('wp_ajax_eddcdp_clear_template_cache', array($this, 'clear_template_cache'));
        
        // Enqueue AJAX script data
        add_action('wp_enqueue_scripts', array($this, 'localize_ajax_script'));
    }
    
    /**
     * Localize AJAX script with necessary data
     */
    public function localize_ajax_script() {
        if (is_user_logged_in()) {
            wp_localize_script('jquery', 'eddcdpAjax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('eddcdp_ajax_nonce'),
                'user_id' => get_current_user_id(),
                'strings' => array(
                    'processing' => __('Processing...', 'eddcdp'),
                    'success' => __('Success!', 'eddcdp'),
                    'error' => __('An error occurred. Please try again.', 'eddcdp'),
                    'confirm_action' => __('Are you sure you want to continue?', 'eddcdp')
                )
            ));
        }
    }
    
    /**
     * Validate AJAX request
     */
    private function validate_ajax_request($capability = 'read') {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'eddcdp_ajax_nonce')) {
            wp_send_json_error(__('Security check failed. Please refresh the page and try again.', 'eddcdp'));
        }
        
        // Check if user is logged in
        if (!is_user_logged_in()) {
            wp_send_json_error(__('You must be logged in to perform this action.', 'eddcdp'));
        }
        
        // Check user capability
        if (!current_user_can($capability)) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'eddcdp'));
        }
        
        return true;
    }
    
    /**
     * Remove from wishlist
     */
    public function remove_from_wishlist() {
        $this->validate_ajax_request();
        
        // Check if EDD Wish Lists is active
        if (!function_exists('edd_wl_remove_from_wish_list')) {
            wp_send_json_error(__('Wish Lists extension is not active.', 'eddcdp'));
        }
        
        $download_id = intval($_POST['download_id']);
        
        if (!$download_id) {
            wp_send_json_error(__('Invalid download ID.', 'eddcdp'));
        }
        
        // Verify download exists
        if (get_post_status($download_id) !== 'publish' || get_post_type($download_id) !== 'download') {
            wp_send_json_error(__('Invalid download.', 'eddcdp'));
        }
        
        $user_id = get_current_user_id();
        
        // Remove from wishlist
        $result = edd_wl_remove_from_wish_list($download_id, $user_id);
        
        if ($result) {
            wp_send_json_success(array(
                'message' => __('Item removed from wishlist.', 'eddcdp'),
                'download_id' => $download_id
            ));
        } else {
            wp_send_json_error(__('Failed to remove item from wishlist. Please try again.', 'eddcdp'));
        }
    }
    
    /**
     * Add to cart
     */
    public function add_to_cart() {
        $this->validate_ajax_request();
        
        $download_id = intval($_POST['download_id']);
        $price_id = isset($_POST['price_id']) ? intval($_POST['price_id']) : false;
        $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
        
        if (!$download_id) {
            wp_send_json_error(__('Invalid download ID.', 'eddcdp'));
        }
        
        // Verify download exists and is published
        if (get_post_status($download_id) !== 'publish' || get_post_type($download_id) !== 'download') {
            wp_send_json_error(__('This product is not available.', 'eddcdp'));
        }
        
        // Validate quantity
        if ($quantity < 1 || $quantity > 99) {
            wp_send_json_error(__('Invalid quantity.', 'eddcdp'));
        }
        
        // Validate price ID for variable pricing
        if (edd_has_variable_prices($download_id)) {
            $prices = edd_get_variable_prices($download_id);
            if ($price_id === false || !isset($prices[$price_id])) {
                wp_send_json_error(__('Please select a valid price option.', 'eddcdp'));
            }
        }
        
        // Prepare cart options
        $options = array();
        if ($price_id !== false) {
            $options['price_id'] = $price_id;
        }
        
        // Add to cart
        $cart_key = edd_add_to_cart($download_id, $options, $quantity);
        
        if ($cart_key !== false) {
            $cart_quantity = edd_get_cart_quantity();
            $cart_total = edd_get_cart_total();
            
            wp_send_json_success(array(
                'message' => __('Item added to cart successfully!', 'eddcdp'),
                'cart_key' => $cart_key,
                'cart_quantity' => $cart_quantity,
                'cart_total' => edd_currency_filter(edd_format_amount($cart_total)),
                'checkout_url' => edd_get_checkout_uri()
            ));
        } else {
            wp_send_json_error(__('Failed to add item to cart. Please try again.', 'eddcdp'));
        }
    }
    
    /**
     * Clear template cache (admin only)
     */
    public function clear_template_cache() {
        $this->validate_ajax_request('manage_shop_settings');
        
        // Clear template cache
        EDDCDP_Templates::instance()->clear_cache();
        
        wp_send_json_success(array(
            'message' => __('Template cache cleared successfully!', 'eddcdp')
        ));
    }
}