<?php
/**
 * Shortcodes Class - Optimized
 */

if (!defined('ABSPATH')) {
    exit;
}

class EDDCDP_Shortcodes {
    
    private static $instance = null;
    private $templates = null;
    private $rendered = false;
    
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('init', array($this, 'init'), 15); // After EDD init
        add_action('template_redirect', array($this, 'handle_fullscreen'), 5);
    }
    
    public function init() {
        $this->templates = EDDCDP_Templates::instance();
        
        // Register our main shortcode
        add_shortcode('edd_customer_dashboard_pro', array($this, 'dashboard_shortcode'));
        
        // Hook to override EDD shortcodes if setting is enabled
        add_action('wp', array($this, 'maybe_override_edd_shortcodes'), 5);
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
        
        // Store original shortcodes for potential restoration
        global $shortcode_tags;
        $original_shortcodes = array();
        
        $edd_shortcodes = array(
            'purchase_history',
            'download_history', 
            'edd_purchase_history',
            'edd_download_history'
        );
        
        foreach ($edd_shortcodes as $shortcode) {
            if (isset($shortcode_tags[$shortcode])) {
                $original_shortcodes[$shortcode] = $shortcode_tags[$shortcode];
                remove_shortcode($shortcode);
            }
            add_shortcode($shortcode, array($this, 'dashboard_shortcode'));
        }
        
        // Store original shortcodes for potential use
        if (!empty($original_shortcodes)) {
            wp_cache_set('eddcdp_original_shortcodes', $original_shortcodes, 'edd-customer-dashboard-pro', HOUR_IN_SECONDS);
        }
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
        if ($this->has_dashboard_shortcode($post->post_content)) {
            $this->render_fullscreen_dashboard();
            exit;
        }
    }
    
    /**
     * Check if content has dashboard shortcode
     */
    private function has_dashboard_shortcode($content) {
        if (has_shortcode($content, 'edd_customer_dashboard_pro')) {
            return true;
        }
        
        // Check EDD shortcodes if replacement is enabled
        $settings = get_option('eddcdp_settings', array());
        if (!empty($settings['replace_edd_pages'])) {
            $edd_shortcodes = array(
                'purchase_history',
                'download_history',
                'edd_purchase_history', 
                'edd_download_history'
            );
            
            foreach ($edd_shortcodes as $shortcode) {
                if (has_shortcode($content, $shortcode)) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Main dashboard shortcode
     */
    public function dashboard_shortcode($atts, $content = '', $tag = '') {
        // Prevent multiple renders on same page
        if ($this->rendered) {
            return '<div class="eddcdp-notice">' . esc_html__('Dashboard can only be displayed once per page.', 'edd-customer-dashboard-pro') . '</div>';
        }
        
        // Check if user is logged in
        if (!is_user_logged_in()) {
            return $this->render_login_form();
        }
        
        // Parse attributes
        $atts = shortcode_atts(array(
            'template' => '',
            'sections' => '',
            'user_id' => get_current_user_id()
        ), $atts, 'edd_customer_dashboard_pro');
        
        // Validate user access
        if (!$this->can_user_access_dashboard($atts['user_id'])) {
            return $this->render_access_denied();
        }
        
        // Mark as rendered
        $this->rendered = true;
        
        // Start output buffering
        ob_start();
        
        // Set shortcode context
        $this->set_shortcode_context($atts, $tag);
        
        // Load the dashboard template
        $loaded = $this->templates->load_dashboard_template();
        
        if (!$loaded) {
            echo $this->render_template_error();
        }
        
        // Return the output
        return ob_get_clean();
    }
    
    /**
     * Render fullscreen dashboard
     */
    private function render_fullscreen_dashboard() {
        // Set fullscreen flag for template to use
        define('EDDCDP_IS_FULLSCREEN', true);
        
        // Prevent caching on fullscreen pages
        if (!defined('DONOTCACHEPAGE')) {
            define('DONOTCACHEPAGE', true);
        }
        
        // Set headers to prevent caching
        nocache_headers();
        
        // Load template
        $this->templates->load_dashboard_template();
    }
    
    /**
     * Set shortcode context for template use
     */
    private function set_shortcode_context($atts, $tag) {
        global $eddcdp_shortcode_atts, $eddcdp_shortcode_tag;
        
        $eddcdp_shortcode_atts = $atts;
        $eddcdp_shortcode_tag = $tag;
        
        // Set context-specific template variables
        if (!empty($atts['template'])) {
            add_filter('eddcdp_active_template', function() use ($atts) {
                return sanitize_file_name($atts['template']);
            });
        }
        
        if (!empty($atts['sections'])) {
            $sections = array_map('trim', explode(',', $atts['sections']));
            add_filter('eddcdp_enabled_sections', function($enabled_sections) use ($sections) {
                $filtered = array();
                foreach ($sections as $section) {
                    if (isset($enabled_sections[$section])) {
                        $filtered[$section] = true;
                    }
                }
                return $filtered;
            });
        }
    }
    
    /**
     * Check if user can access dashboard
     */
    private function can_user_access_dashboard($user_id) {
        // Basic capability check
        if (!user_can($user_id, 'read')) {
            return false;
        }
        
        // Check if user has any EDD activity
        $customer = edd_get_customer_by('user_id', $user_id);
        
        // Allow access even without purchases for potential customers
        return true;
    }
    
    /**
     * Display login form for non-logged-in users
     */
    private function render_login_form() {
        ob_start();
        ?>
        <div class="eddcdp-login-required">
            <div class="eddcdp-login-box">
                <h3><?php esc_html_e('Customer Dashboard', 'edd-customer-dashboard-pro'); ?></h3>
                <p><?php esc_html_e('Please log in to access your customer dashboard.', 'edd-customer-dashboard-pro'); ?></p>
                
                <?php
                // Use WordPress native login form
                $login_args = array(
                    'redirect' => get_permalink(),
                    'form_id' => 'eddcdp-loginform',
                    'label_username' => esc_html__('Username or Email', 'edd-customer-dashboard-pro'),
                    'label_password' => esc_html__('Password', 'edd-customer-dashboard-pro'),
                    'label_remember' => esc_html__('Remember Me', 'edd-customer-dashboard-pro'),
                    'label_log_in' => esc_html__('Log In', 'edd-customer-dashboard-pro'),
                    'remember' => true
                );
                
                if (function_exists('wp_login_form')) {
                    wp_login_form($login_args);
                } else {
                    $login_url = wp_login_url(get_permalink());
                    echo '<p><a href="' . esc_url($login_url) . '" class="eddcdp-login-link">' . esc_html__('Log In', 'edd-customer-dashboard-pro') . '</a></p>';
                }
                ?>
                
                <div class="eddcdp-login-links">
                    <?php if (get_option('users_can_register')) : ?>
                        <p>
                            <a href="<?php echo esc_url(wp_registration_url()); ?>">
                                <?php esc_html_e('Create an account', 'edd-customer-dashboard-pro'); ?>
                            </a>
                        </p>
                    <?php endif; ?>
                    
                    <p>
                        <a href="<?php echo esc_url(wp_lostpassword_url(get_permalink())); ?>">
                            <?php esc_html_e('Lost your password?', 'edd-customer-dashboard-pro'); ?>
                        </a>
                    </p>
                </div>
            </div>
        </div>
        
        <style>
        .eddcdp-login-required {
            max-width: 400px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .eddcdp-login-box {
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        .eddcdp-login-box h3 {
            margin-top: 0;
            color: #333;
        }
        .eddcdp-login-box form {
            text-align: left;
            margin: 20px 0;
        }
        .eddcdp-login-box input[type="text"],
        .eddcdp-login-box input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .eddcdp-login-box input[type="submit"] {
            background: linear-gradient(to right, #6366f1, #8b5cf6);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            width: 100%;
            font-weight: 600;
        }
        .eddcdp-login-links {
            margin-top: 20px;
            font-size: 14px;
        }
        .eddcdp-login-links a {
            color: #6366f1;
            text-decoration: none;
        }
        </style>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render access denied message
     */
    private function render_access_denied() {
        return '<div class="eddcdp-access-denied">' . 
               '<p>' . esc_html__('You do not have permission to access this dashboard.', 'edd-customer-dashboard-pro') . '</p>' .
               '</div>';
    }
    
    /**
     * Render template error
     */
    private function render_template_error() {
        if (current_user_can('manage_options')) {
            return '<div class="eddcdp-error">' .
                   '<p>' . esc_html__('Dashboard template could not be loaded. Please check your template settings.', 'edd-customer-dashboard-pro') . '</p>' .
                   '<p><a href="' . esc_url(admin_url('edit.php?post_type=download&page=eddcdp-settings')) . '">' . esc_html__('Go to Settings', 'edd-customer-dashboard-pro') . '</a></p>' .
                   '</div>';
        }
        
        return '<div class="eddcdp-error">' .
               '<p>' . esc_html__('Dashboard is temporarily unavailable. Please try again later.', 'edd-customer-dashboard-pro') . '</p>' .
               '</div>';
    }
}