<?php
/**
 * Shortcodes Class for EDD Customer Dashboard Pro
 */

if (!defined('ABSPATH')) {
    exit;
}

class EDDCDP_Shortcodes {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
    }
    
    public function init() {
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
        $this->load_dashboard_template($atts);
        
        // Return the output
        return ob_get_clean();
    }
    
    /**
     * Load dashboard template
     */
    private function load_dashboard_template($atts) {
        $settings = get_option('eddcdp_settings', array());
        
        // Determine active template
        $active_template = !empty($atts['template']) ? $atts['template'] : 
                          (isset($settings['active_template']) ? $settings['active_template'] : 'default');
        
        // Load template
        $template_path = EDDCDP_PLUGIN_DIR . 'templates/' . $active_template . '/dashboard.php';
        
        if (file_exists($template_path)) {
            include $template_path;
        } else {
            echo '<div class="eddcdp-error">';
            echo '<p>' . __('Dashboard template not found.', 'eddcdp') . '</p>';
            echo '</div>';
        }
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