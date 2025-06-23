<?php
/**
 * AJAX Handler Class - Optimized
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
        // Public AJAX actions (for logged-in and non-logged-in users)
        add_action('wp_ajax_eddcdp_deactivate_license_site', array($this, 'deactivate_license_site'));
        add_action('wp_ajax_eddcdp_activate_license_site', array($this, 'activate_license_site'));
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
     * Deactivate license site
     */
    public function deactivate_license_site() {
        $this->validate_ajax_request();
        
        // Check if EDD Software Licensing is active
        if (!class_exists('EDD_Software_Licensing') || !function_exists('edd_software_licensing')) {
            wp_send_json_error(__('Software Licensing extension is not active.', 'eddcdp'));
        }
        
        $license_id = intval($_POST['license_id']);
        $site_url = sanitize_url($_POST['site_url']);
        
        if (!$license_id || !$site_url) {
            wp_send_json_error(__('Invalid license ID or site URL.', 'eddcdp'));
        }
        
        // Get and verify license
        $license = edd_software_licensing()->get_license($license_id);
        if (!$license || $license->user_id != get_current_user_id()) {
            wp_send_json_error(__('Invalid license or insufficient permissions.', 'eddcdp'));
        }
        
        // Deactivate the site
        $result = $license->deactivate($site_url);
        
        if ($result) {
            // Log the action
            $this->log_license_action('deactivate', $license_id, $site_url);
            
            wp_send_json_success(array(
                'message' => __('Site deactivated successfully.', 'eddcdp'),
                'license_id' => $license_id,
                'site_url' => $site_url
            ));
        } else {
            wp_send_json_error(__('Failed to deactivate site. Please try again or contact support.', 'eddcdp'));
        }
    }
    
    /**
     * Activate license site
     */
    public function activate_license_site() {
        $this->validate_ajax_request();
        
        // Check if EDD Software Licensing is active
        if (!class_exists('EDD_Software_Licensing') || !function_exists('edd_software_licensing')) {
            wp_send_json_error(__('Software Licensing extension is not active.', 'eddcdp'));
        }
        
        $license_id = intval($_POST['license_id']);
        $site_url = sanitize_url($_POST['site_url']);
        
        if (!$license_id || !$site_url) {
            wp_send_json_error(__('Invalid license ID or site URL.', 'eddcdp'));
        }
        
        // Validate URL format
        if (!filter_var($site_url, FILTER_VALIDATE_URL)) {
            wp_send_json_error(__('Please enter a valid URL (e.g., https://example.com).', 'eddcdp'));
        }
        
        // Get and verify license
        $license = edd_software_licensing()->get_license($license_id);
        if (!$license || $license->user_id != get_current_user_id()) {
            wp_send_json_error(__('Invalid license or insufficient permissions.', 'eddcdp'));
        }
        
        // Check if license is active and not expired
        if ($license->status !== 'active') {
            wp_send_json_error(__('License must be active to activate sites.', 'eddcdp'));
        }
        
        if (!empty($license->expiration)) {
            $expiration_date = strtotime($license->expiration);
            if ($expiration_date < time()) {
                wp_send_json_error(__('License has expired. Please renew to activate sites.', 'eddcdp'));
            }
        }
        
        // Check activation limits
        if ($license->activation_limit > 0 && $license->activation_count >= $license->activation_limit) {
            wp_send_json_error(__('Activation limit reached. Please deactivate a site first or upgrade your license.', 'eddcdp'));
        }
        
        // Activate the site
        $result = $license->activate($site_url);
        
        if ($result) {
            // Log the action
            $this->log_license_action('activate', $license_id, $site_url);
            
            wp_send_json_success(array(
                'message' => __('Site activated successfully.', 'eddcdp'),
                'license_id' => $license_id,
                'site_url' => $site_url
            ));
        } else {
            wp_send_json_error(__('Failed to activate site. The site may already be activated or there was an error. Please try again.', 'eddcdp'));
        }
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
        
        // Clear any other related caches
        do_action('eddcdp_clear_cache');
        
        wp_send_json_success(array(
            'message' => __('Template cache cleared successfully!', 'eddcdp')
        ));
    }
    
    /**
     * Log license actions for debugging/audit purposes
     */
    private function log_license_action($action, $license_id, $site_url) {
        $user_id = get_current_user_id();
        $user = get_userdata($user_id);
        
        $log_entry = array(
            'timestamp' => current_time('mysql'),
            'user_id' => $user_id,
            'user_email' => $user->user_email,
            'action' => $action,
            'license_id' => $license_id,
            'site_url' => $site_url,
            'ip_address' => $this->get_client_ip()
        );
        
        // Store in option (could be extended to use custom table)
        $logs = get_option('eddcdp_license_logs', array());
        $logs[] = $log_entry;
        
        // Keep only last 100 entries
        if (count($logs) > 100) {
            $logs = array_slice($logs, -100);
        }
        
        update_option('eddcdp_license_logs', $logs);
        
        // Also log to WordPress debug.log if WP_DEBUG_LOG is enabled
        if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
            error_log(sprintf(
                'EDDCDP License Action: %s performed %s on license %d for site %s',
                $user->user_email,
                $action,
                $license_id,
                $site_url
            ));
        }
    }
    
    /**
     * Get client IP address
     */
    private function get_client_ip() {
        $ip_keys = array(
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        );
        
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
    }
    
    /**
     * Sanitize and validate license site URL
     */
    private function sanitize_license_url($url) {
        // Remove www. prefix for consistency
        $url = preg_replace('/^https?:\/\/www\./', 'https://', $url);
        
        // Ensure URL has protocol
        if (!preg_match('/^https?:\/\//', $url)) {
            $url = 'https://' . $url;
        }
        
        // Remove trailing slash
        $url = rtrim($url, '/');
        
        // Validate URL
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }
        
        return $url;
    }
    
    /**
     * Rate limiting for AJAX requests
     */
    private function check_rate_limit($action, $user_id, $limit = 10, $window = 300) {
        $transient_key = 'eddcdp_rate_limit_' . $action . '_' . $user_id;
        $requests = get_transient($transient_key);
        
        if ($requests === false) {
            $requests = 1;
            set_transient($transient_key, $requests, $window);
            return true;
        }
        
        if ($requests >= $limit) {
            wp_send_json_error(__('Too many requests. Please wait a moment and try again.', 'eddcdp'));
        }
        
        set_transient($transient_key, $requests + 1, $window);
        return true;
    }
    
    /**
     * Get license action logs (admin only)
     */
    public function get_license_logs() {
        $this->validate_ajax_request('manage_shop_settings');
        
        $logs = get_option('eddcdp_license_logs', array());
        $logs = array_reverse($logs); // Most recent first
        
        wp_send_json_success(array(
            'logs' => $logs,
            'total' => count($logs)
        ));
    }
}