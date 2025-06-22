<?php
/**
 * Shortcodes Class for EDD Customer Dashboard Pro
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class EDDCDP_Shortcodes {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array($this, 'init'));
    }
    
    /**
     * Initialize shortcodes
     */
    public function init() {
        add_shortcode('edd_customer_dashboard_pro', array($this, 'dashboard_shortcode'));
        add_shortcode('eddcdp_dashboard', array($this, 'dashboard_shortcode')); // Alternative shortcode
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
            'template' => '', // Override template
            'sections' => '', // Override sections (comma-separated)
            'fullscreen' => '', // Override fullscreen mode
        ), $atts, 'edd_customer_dashboard_pro');
        
        // Start output buffering
        ob_start();
        
        // Load the dashboard template
        $this->load_dashboard($atts);
        
        // Return the output
        return ob_get_clean();
    }
    
    /**
     * Load dashboard template
     */
    private function load_dashboard($atts) {
        $settings = get_option('eddcdp_settings', array());
        
        // Determine active template
        $active_template = !empty($atts['template']) ? $atts['template'] : 
                          (isset($settings['active_template']) ? $settings['active_template'] : 'default');
        
        // Override fullscreen mode if specified
        if (!empty($atts['fullscreen'])) {
            $settings['fullscreen_mode'] = ($atts['fullscreen'] === 'true' || $atts['fullscreen'] === '1');
        }
        
        // Override sections if specified
        if (!empty($atts['sections'])) {
            $custom_sections = array_map('trim', explode(',', $atts['sections']));
            $settings['enabled_sections'] = array();
            foreach ($custom_sections as $section) {
                $settings['enabled_sections'][$section] = true;
            }
        }
        
        // Temporarily update settings for this render
        $original_settings = get_option('eddcdp_settings', array());
        update_option('eddcdp_settings', $settings);
        
        // Load template
        $templates = new EDDCDP_Templates();
        $template_path = $templates->get_template_path($active_template);
        
        if ($template_path && file_exists($template_path . '/dashboard.php')) {
            // Enqueue assets for this template
            $this->enqueue_dashboard_assets($active_template);
            
            // Include the dashboard template
            include $template_path . '/dashboard.php';
        } else {
            echo '<div class="eddcdp-error">';
            echo '<p>' . __('Dashboard template not found.', 'eddcdp') . '</p>';
            echo '</div>';
        }
        
        // Restore original settings
        update_option('eddcdp_settings', $original_settings);
    }
    
    /**
     * Display login form for non-logged-in users
     */
    private function login_form() {
        ob_start();
        ?>
        <div class="eddcdp-login-required">
            <div style="text-align: center; padding: 40px; background: #f9f9f9; border-radius: 8px; margin: 20px 0;">
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
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Enqueue dashboard assets
     */
    private function enqueue_dashboard_assets($template_name) {
        $template_url = EDDCDP_PLUGIN_URL . 'templates/' . $template_name . '/';
        $template_path = EDDCDP_PLUGIN_DIR . 'templates/' . $template_name . '/';
        
        // Enqueue CSS if exists
        if (file_exists($template_path . 'style.css')) {
            wp_enqueue_style(
                'eddcdp-template-' . $template_name,
                $template_url . 'style.css',
                array(),
                EDDCDP_VERSION
            );
        }
        
        // Enqueue JS if exists
        if (file_exists($template_path . 'script.js')) {
            wp_enqueue_script(
                'eddcdp-template-' . $template_name,
                $template_url . 'script.js',
                array('jquery'),
                EDDCDP_VERSION,
                true
            );
            
            // Localize script with AJAX data
            wp_localize_script(
                'eddcdp-template-' . $template_name,
                'eddcdp_ajax',
                array(
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce('eddcdp_ajax_nonce'),
                    'user_id' => get_current_user_id(),
                )
            );
        }
        
        // Always enqueue default dashboard script for functionality
        wp_enqueue_script(
            'eddcdp-dashboard',
            EDDCDP_PLUGIN_URL . 'assets/dashboard.js',
            array('jquery'),
            EDDCDP_VERSION,
            true
        );
        
        // Localize dashboard script
        wp_localize_script(
            'eddcdp-dashboard',
            'eddcdp',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('eddcdp_ajax_nonce'),
                'user_id' => get_current_user_id(),
                'strings' => array(
                    'license_copied' => __('License key copied to clipboard!', 'eddcdp'),
                    'copy_failed' => __('Failed to copy license key.', 'eddcdp'),
                    'confirm_deactivate' => __('Are you sure you want to deactivate this site?', 'eddcdp'),
                    'deactivate_error' => __('Error deactivating site. Please try again.', 'eddcdp'),
                    'site_url_required' => __('Please enter a site URL.', 'eddcdp'),
                    'activation_success' => __('Site activated successfully!', 'eddcdp'),
                    'activation_error' => __('Error activating site. Please try again.', 'eddcdp'),
                )
            )
        );
    }
    
    /**
     * Check if current user can access dashboard
     */
    private function user_can_access_dashboard() {
        if (!is_user_logged_in()) {
            return false;
        }
        
        // Check if user has any purchases (optional)
        $current_user = wp_get_current_user();
        $customer = new EDD_Customer($current_user->user_email);
        
        // Allow access if user has purchases or if they're an admin
        return ($customer->purchase_count > 0 || current_user_can('manage_options'));
    }
}