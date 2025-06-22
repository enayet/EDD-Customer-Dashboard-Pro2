<?php
/**
 * AJAX Handler Class for EDD Customer Dashboard Pro
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class EDDCDP_Ajax {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('wp_ajax_eddcdp_deactivate_license_site', array($this, 'deactivate_license_site'));
        add_action('wp_ajax_eddcdp_activate_license_site', array($this, 'activate_license_site'));
        add_action('wp_ajax_eddcdp_remove_from_wishlist', array($this, 'remove_from_wishlist'));
    }
    
    /**
     * Deactivate license site
     */
    public function deactivate_license_site() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'eddcdp_ajax_nonce')) {
            wp_die(__('Security check failed', 'eddcdp'));
        }
        
        // Check if user is logged in
        if (!is_user_logged_in()) {
            wp_send_json_error(__('You must be logged in', 'eddcdp'));
        }
        
        // Check if EDD Software Licensing is active
        if (!class_exists('EDD_Software_Licensing')) {
            wp_send_json_error(__('Software Licensing extension is not active', 'eddcdp'));
        }
        
        $license_id = intval($_POST['license_id']);
        $site_url = sanitize_url($_POST['site_url']);
        
        // Verify license belongs to current user
        $license = edd_software_licensing()->get_license($license_id);
        if (!$license || $license->user_id != get_current_user_id()) {
            wp_send_json_error(__('Invalid license', 'eddcdp'));
        }
        
        // Deactivate the site
        $result = $license->deactivate($site_url);
        
        if ($result) {
            wp_send_json_success(__('Site deactivated successfully', 'eddcdp'));
        } else {
            wp_send_json_error(__('Failed to deactivate site', 'eddcdp'));
        }
    }
    
    /**
     * Activate license site
     */
    public function activate_license_site() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'eddcdp_ajax_nonce')) {
            wp_die(__('Security check failed', 'eddcdp'));
        }
        
        // Check if user is logged in
        if (!is_user_logged_in()) {
            wp_send_json_error(__('You must be logged in', 'eddcdp'));
        }
        
        // Check if EDD Software Licensing is active
        if (!class_exists('EDD_Software_Licensing')) {
            wp_send_json_error(__('Software Licensing extension is not active', 'eddcdp'));
        }
        
        $license_id = intval($_POST['license_id']);
        $site_url = sanitize_url($_POST['site_url']);
        
        // Verify license belongs to current user
        $license = edd_software_licensing()->get_license($license_id);
        if (!$license || $license->user_id != get_current_user_id()) {
            wp_send_json_error(__('Invalid license', 'eddcdp'));
        }
        
        // Activate the site
        $result = $license->activate($site_url);
        
        if ($result) {
            wp_send_json_success(__('Site activated successfully', 'eddcdp'));
        } else {
            wp_send_json_error(__('Failed to activate site', 'eddcdp'));
        }
    }
    
    /**
     * Remove from wishlist
     */
    public function remove_from_wishlist() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'eddcdp_ajax_nonce')) {
            wp_die(__('Security check failed', 'eddcdp'));
        }
        
        // Check if user is logged in
        if (!is_user_logged_in()) {
            wp_send_json_error(__('You must be logged in', 'eddcdp'));
        }
        
        // Check if EDD Wish Lists is active
        if (!function_exists('edd_wl_remove_from_wish_list')) {
            wp_send_json_error(__('Wish Lists extension is not active', 'eddcdp'));
        }
        
        $download_id = intval($_POST['download_id']);
        $user_id = get_current_user_id();
        
        // Remove from wishlist
        $result = edd_wl_remove_from_wish_list($download_id, $user_id);
        
        if ($result) {
            wp_send_json_success(__('Removed from wishlist', 'eddcdp'));
        } else {
            wp_send_json_error(__('Failed to remove from wishlist', 'eddcdp'));
        }
    }
}