<?php
/**
 * Fullscreen Handler for EDD Customer Dashboard Pro
 */

if (!defined('ABSPATH')) {
    exit;
}

class EDDCDP_Fullscreen {
    
    public function __construct() {
        add_action('template_redirect', array($this, 'handle_fullscreen_request'));
    }
    
    /**
     * Check if current page should be displayed in fullscreen
     */
    public function handle_fullscreen_request() {
        // Only proceed if user is logged in
        if (!is_user_logged_in()) {
            return;
        }
        
        // Get settings
        $settings = get_option('eddcdp_settings', array());
        
        // Only proceed if fullscreen mode is enabled
        if (empty($settings['fullscreen_mode'])) {
            return;
        }
        
        // Check if current page contains our shortcode
        global $post;
        if (!is_a($post, 'WP_Post')) {
            return;
        }
        
        // Check for our shortcode or EDD shortcodes (if replace is enabled)
        $has_dashboard_shortcode = has_shortcode($post->post_content, 'edd_customer_dashboard_pro');
        
        $has_edd_shortcode = false;
        if (!empty($settings['replace_edd_pages'])) {
            $has_edd_shortcode = (
                has_shortcode($post->post_content, 'purchase_history') ||
                has_shortcode($post->post_content, 'download_history') ||
                has_shortcode($post->post_content, 'edd_purchase_history') ||
                has_shortcode($post->post_content, 'edd_download_history')
            );
        }
        
        // If this page has dashboard shortcode, render fullscreen
        if ($has_dashboard_shortcode || $has_edd_shortcode) {
            $this->render_fullscreen_dashboard();
            exit; // Important: Stop WordPress from rendering normally
        }
    }
    
    /**
     * Render the fullscreen dashboard using existing template system
     */
    private function render_fullscreen_dashboard() {
        // Get settings and template
        $settings = get_option('eddcdp_settings', array());
        $active_template = isset($settings['active_template']) ? $settings['active_template'] : 'default';
        
        // Load template using existing template loader
        $templates = new EDDCDP_Templates();
        $template_path = $templates->get_template_path($active_template);
        
        if ($template_path && file_exists($template_path . '/dashboard.php')) {
            // Set fullscreen flag for template to use
            define('EDDCDP_IS_FULLSCREEN', true);
            
            include $template_path . '/dashboard.php';
        } else {
            echo '<div class="notice notice-error"><p>Dashboard template not found.</p></div>';
        }
    }
}