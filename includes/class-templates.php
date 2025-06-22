<?php
/**
 * Template System for EDD Customer Dashboard Pro
 */

if (!defined('ABSPATH')) {
    exit;
}

class EDDCDP_Templates {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
    }
    
    public function init() {
        // Override EDD shortcodes if enabled
        add_action('wp_loaded', array($this, 'override_edd_shortcodes'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
    }
    
    /**
     * Override EDD shortcodes with our dashboard
     */
    public function override_edd_shortcodes() {
        $settings = get_option('eddcdp_settings', array());
        
        // Only override if replacement is enabled
        if (empty($settings['replace_edd_pages'])) {
            return;
        }
        
        // Remove default EDD shortcodes
        remove_shortcode('purchase_history');
        remove_shortcode('download_history');
        remove_shortcode('edd_purchase_history');
        remove_shortcode('edd_download_history');
        
        // Add our dashboard shortcodes
        add_shortcode('purchase_history', array($this, 'dashboard_shortcode'));
        add_shortcode('download_history', array($this, 'dashboard_shortcode'));
        add_shortcode('edd_purchase_history', array($this, 'dashboard_shortcode'));
        add_shortcode('edd_download_history', array($this, 'dashboard_shortcode'));
    }
    
    /**
     * Dashboard shortcode handler
     */
    public function dashboard_shortcode($atts) {
        // Check if user is logged in
        if (!is_user_logged_in()) {
            return $this->login_form();
        }
        
        ob_start();
        $this->load_dashboard_template();
        return ob_get_clean();
    }
    
    /**
     * Load the active dashboard template
     */
    public function load_dashboard_template() {
        $settings = get_option('eddcdp_settings', array());
        $active_template = isset($settings['active_template']) ? $settings['active_template'] : 'default';
        
        $template_path = $this->get_template_path($active_template);
        
        if ($template_path && file_exists($template_path . '/dashboard.php')) {
            include $template_path . '/dashboard.php';
            return true;
        }
        
        echo '<div class="notice notice-error"><p>Dashboard template not found.</p></div>';
        return false;
    }
    
    /**
     * Get template path
     */
    public function get_template_path($template_name) {
        $template_dir = EDDCDP_PLUGIN_DIR . 'templates/' . $template_name;
        
        if (is_dir($template_dir)) {
            return $template_dir;
        }
        
        return false;
    }
    
    /**
     * Enqueue template assets
     */
    public function enqueue_assets() {
        // Only enqueue on pages that might use our dashboard
        if (!$this->should_enqueue_assets()) {
            return;
        }
        
        $settings = get_option('eddcdp_settings', array());
        $active_template = isset($settings['active_template']) ? $settings['active_template'] : 'default';
        
        $template_path = $this->get_template_path($active_template);
        $template_url = EDDCDP_PLUGIN_URL . 'templates/' . $active_template . '/';
        
        // Always enqueue the template CSS to handle layout overrides
        $css_file = $template_path . '/style.css';
        if (file_exists($css_file)) {
            wp_enqueue_style(
                'eddcdp-template-' . $active_template,
                $template_url . 'style.css',
                array(),
                EDDCDP_VERSION
            );
        }
        
        // Enqueue JS if exists
        $js_file = $template_path . '/script.js';
        if (file_exists($js_file)) {
            wp_enqueue_script(
                'eddcdp-template-' . $active_template,
                $template_url . 'script.js',
                array('jquery'),
                EDDCDP_VERSION,
                true
            );
        }
        
        // Add inline CSS for critical layout fixes
        $this->add_critical_css();
    }
    
    /**
     * Add critical CSS to override WordPress layout constraints
     */
    private function add_critical_css() {
        $css = "
        .eddcdp-dashboard-wrapper,
        .eddcdp-dashboard-wrapper *,
        .eddcdp-dashboard-wrapper .is-layout-constrained > :where(:not(.alignleft):not(.alignright):not(.alignfull)) {
            max-width: none !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
        }
        ";
        
        wp_add_inline_style('eddcdp-template-default', $css);
    }
    
    /**
     * Check if we should enqueue assets
     */
    private function should_enqueue_assets() {
        global $post;
        
        // Check if post contains our shortcode or EDD shortcodes
        if (is_a($post, 'WP_Post')) {
            $content = $post->post_content;
            
            if (has_shortcode($content, 'edd_customer_dashboard_pro') ||
                has_shortcode($content, 'purchase_history') ||
                has_shortcode($content, 'download_history') ||
                has_shortcode($content, 'edd_purchase_history') ||
                has_shortcode($content, 'edd_download_history')) {
                return true;
            }
        }
        
        // Also check if we're on EDD pages
        if (function_exists('edd_is_checkout') && (edd_is_checkout() || edd_is_purchase_history_page())) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Login form for non-logged-in users
     */
    private function login_form() {
        ob_start();
        ?>
        <div class="eddcdp-login-required" style="text-align: center; padding: 40px; background: #f9f9f9; border-radius: 8px; margin: 20px 0;">
            <h3><?php _e('Login Required', 'eddcdp'); ?></h3>
            <p><?php _e('Please log in to access your customer dashboard.', 'eddcdp'); ?></p>
            <p>
                <a href="<?php echo wp_login_url(get_permalink()); ?>" class="button">
                    <?php _e('Log In', 'eddcdp'); ?>
                </a>
            </p>
        </div>
        <?php
        return ob_get_clean();
    }
}