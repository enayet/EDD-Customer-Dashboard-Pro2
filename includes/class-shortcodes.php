<?php
/**
 * Shortcodes Class for EDD Customer Dashboard Pro
 */

if (!defined('ABSPATH')) {
    exit;
}

class EDDCDP_Shortcodes {
    
    private $templates;
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('template_redirect', array($this, 'handle_fullscreen'));
    }
    
    public function init() {
        // Get templates instance
        $this->templates = new EDDCDP_Templates();
        
        // Register our main shortcode
        add_shortcode('edd_customer_dashboard_pro', array($this, 'dashboard_shortcode'));
        
        // Hook to override EDD shortcodes if setting is enabled
        add_action('wp', array($this, 'maybe_override_edd_shortcodes'));
    }
    
    /**
     * Override EDD shortcodes if setting is enabled
     */
    public function maybe_override_edd_shortcodes() {
        $settings = get_option('eddcdp_settings', array());
        
        // Only override if replacement is enabled
        if (empty($settings['replace_edd_pages'])) {
            return;
        }
        
        // Remove default EDD shortcodes and replace with ours
        remove_shortcode('purchase_history');
        remove_shortcode('download_history');
        remove_shortcode('edd_purchase_history');
        remove_shortcode('edd_download_history');
        
        add_shortcode('purchase_history', array($this, 'dashboard_shortcode'));
        add_shortcode('download_history', array($this, 'dashboard_shortcode'));
        add_shortcode('edd_purchase_history', array($this, 'dashboard_shortcode'));
        add_shortcode('edd_download_history', array($this, 'dashboard_shortcode'));
    }
    
    /**
     * Handle fullscreen mode
     */
    public function handle_fullscreen() {
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
            exit;
        }
    }
    
    /**
     * Main dashboard shortcode
     */
    public function dashboard_shortcode($atts) {
        // Check if user is logged in
        if (!is_user_logged_in()) {
            return $this->login_form();
        }
        
        // Parse attributes
        $atts = shortcode_atts(array(
            'template' => '',
        ), $atts, 'edd_customer_dashboard_pro');
        
        // Start output buffering
        ob_start();
        
        // Load the dashboard template
        $this->templates->load_dashboard_template();
        
        // Return the output
        return ob_get_clean();
    }
    
    /**
     * Render fullscreen dashboard
     */
    private function render_fullscreen_dashboard() {
        // Set fullscreen flag for template to use
        define('EDDCDP_IS_FULLSCREEN', true);
        
        // Load template
        $this->templates->load_dashboard_template();
    }
    
    /**
     * Display login form for non-logged-in users
     */
    private function login_form() {
        ob_start();
        ?>
        <div class="eddcdp-login-required" style="text-align: center; padding: 40px; background: #f9f9f9; border-radius: 8px; margin: 20px 0;">
            <h3><?php _e('Login Required', 'eddcdp'); ?></h3>
            <p><?php _e('Please log in to access your customer dashboard.', 'eddcdp'); ?></p>
            
            <?php if (function_exists('wp_login_form')) : ?>
                <div style="max-width: 300px; margin: 20px auto;">
                    <?php 
                    wp_login_form(array(
                        'redirect' => get_permalink(),
                        'form_id' => 'eddcdp-loginform',
                        'label_username' => __('Username or Email', 'eddcdp'),
                        'label_password' => __('Password', 'eddcdp'),
                        'label_remember' => __('Remember Me', 'eddcdp'),
                        'label_log_in' => __('Log In', 'eddcdp'),
                    )); 
                    ?>
                </div>
            <?php else : ?>
                <p>
                    <a href="<?php echo wp_login_url(get_permalink()); ?>" class="button">
                        <?php _e('Log In', 'eddcdp'); ?>
                    </a>
                </p>
            <?php endif; ?>
            
            <?php if (get_option('users_can_register')) : ?>
                <p style="margin-top: 15px;">
                    <?php _e('Don\'t have an account?', 'eddcdp'); ?>
                    <a href="<?php echo wp_registration_url(); ?>">
                        <?php _e('Register here', 'eddcdp'); ?>
                    </a>
                </p>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}