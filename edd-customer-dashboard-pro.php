<?php
/**
 * Plugin Name: EDD Customer Dashboard Pro
 * Plugin URI: https://yoursite.com/edd-dashboard-pro
 * Description: Enhanced customer dashboard templates for Easy Digital Downloads
 * Version: 1.2.0
 * Author: Your Name
 * Text Domain: edd-customer-dashboard-pro
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('EDDCDP_VERSION', '1.2.0');
define('EDDCDP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('EDDCDP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('EDDCDP_PLUGIN_FILE', __FILE__);
define('EDDCDP_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main EDD Customer Dashboard Pro Class
 */
final class EDD_Customer_Dashboard_Pro {
    
    /**
     * Single instance of the class
     */
    private static $instance = null;
    
    /**
     * Plugin settings
     */
    private $settings = null;
    
    /**
     * Get single instance
     */
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        add_action('plugins_loaded', array($this, 'init'), 10);
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        // Check dependencies first
        //if (!$this->check_dependencies()) {
        //    return;
        //}
        
        // Load essentials
        
        $this->includes();
        $this->init_hooks();
        
        // Load textdomain at init (WordPress 6.7+ requirement)
        add_action('init', array($this, 'load_textdomain'));        
        
        // Plugin fully loaded
        do_action('eddcdp_loaded');
    }
    
    /**
     * Check plugin dependencies
     */
    private function check_dependencies() {
        // Check if EDD is active
        if (!class_exists('Easy_Digital_Downloads')) {
            add_action('admin_notices', array($this, 'edd_missing_notice'));
            return false;
        }
        
        // Check minimum requirements
        $requirements = array(
            'php' => '7.4',
            'wp' => '5.0',
            'edd' => '3.8'
        );
        
        $errors = array();
        
        // PHP Version
        if (version_compare(PHP_VERSION, $requirements['php'], '<')) {
            $errors[] = sprintf(
                /* translators: %1$s: Required PHP version, %2$s: Current PHP version */
                __('PHP %1$s or higher required. You are running %2$s.', 'edd-customer-dashboard-pro'),
                $requirements['php'],
                PHP_VERSION
            );
        }
        
        // WordPress Version
        global $wp_version;
        if (version_compare($wp_version, $requirements['wp'], '<')) {
            $errors[] = sprintf(
                /* translators: %1$s: Required WordPress version, %2$s: Current WordPress version */
                __('WordPress %1$s or higher required. You are running %2$s.', 'edd-customer-dashboard-pro'),
                $requirements['wp'],
                $wp_version
            );
        }
        
        // EDD Version
        if (defined('EDD_VERSION') && version_compare(EDD_VERSION, $requirements['edd'], '<')) {
            $errors[] = sprintf(
                /* translators: %1$s: Required EDD version, %2$s: Current EDD version */
                __('Easy Digital Downloads %1$s or higher required. You are running %2$s.', 'edd-customer-dashboard-pro'),
                $requirements['edd'],
                EDD_VERSION
            );
        }
        
        // Show errors if any
        if (!empty($errors)) {
            add_action('admin_notices', function() use ($errors) {
                foreach ($errors as $error) {
                    echo '<div class="notice notice-error"><p><strong>EDD Customer Dashboard Pro:</strong> ' . esc_html($error) . '</p></div>';
                }
            });
            return false;
        }
        
        return true;
    }
    
    /**
     * Load plugin textdomain
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'edd-customer-dashboard-pro',
            false,
            dirname(EDDCDP_PLUGIN_BASENAME) . '/languages/'
        );
    }
    
    /**
     * Include required files
     */
    private function includes() {
        $includes = array(
            'includes/functions.php',
            'includes/class-templates.php',
            'includes/class-shortcodes.php',
            'includes/class-order-details.php',
            'includes/class-invoice.php',
            'includes/class-wishlist-handler.php',
            'includes/class-fullscreen-helper.php',
        );
        
        // Add admin files only in admin
        if (is_admin()) {
            $includes[] = 'includes/class-admin.php';
        }
        
        foreach ($includes as $file) {
            $file_path = EDDCDP_PLUGIN_DIR . $file;
            if (file_exists($file_path)) {
                require_once $file_path;
            }
        }
    }
    
    
    
    /**
     * Initialize hooks and components
     */
    private function init_hooks() {
        // Initialize core components
        EDDCDP_Templates::instance();
        EDDCDP_Shortcodes::instance();
        EDDCDP_Order_Details::instance();
        EDDCDP_Invoice_Redirect::instance();
        EDDCDP_Wishlist_Handler::instance();
        EDDCDP_Fullscreen_Helper::instance();
        
        // Initialize admin only in admin
        if (is_admin()) {
            EDDCDP_Admin::instance();
        }
        
        // Frontend hooks
        add_filter('body_class', array($this, 'add_body_classes'));
        add_filter('plugin_action_links_' . EDDCDP_PLUGIN_BASENAME, array($this, 'plugin_action_links'));
        
        // Admin hooks
        if (is_admin()) {
            add_action('admin_init', array($this, 'handle_activation_redirect'));
        }
    }
    
    /**
     * Add body classes for dashboard pages
     */
    public function add_body_classes($classes) {
        if (!$this->is_dashboard_page()) {
            return $classes;
        }
        
        $classes[] = 'eddcdp-dashboard-page';
        
        $settings = $this->get_settings();
        
        // Add fullscreen class
        if (!empty($settings['fullscreen_mode'])) {
            $classes[] = 'eddcdp-fullscreen-enabled';
        }
        
        // Add template class
        $template = !empty($settings['active_template']) ? $settings['active_template'] : 'default';
        $classes[] = 'eddcdp-template-' . sanitize_html_class($template);
        
        return $classes;
    }
    
    /**
     * Check if current page is a dashboard page
     */
    private function is_dashboard_page() {
        global $post;
        
        if (!is_a($post, 'WP_Post')) {
            return false;
        }
        
        $content = $post->post_content;
        
        // Check for our shortcode
        if (has_shortcode($content, 'edd_customer_dashboard_pro')) {
            return true;
        }
        
        // Check for EDD shortcodes if replacement is enabled
        $settings = $this->get_settings();
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
     * Get plugin settings with defaults
     */
    public function get_settings() {
        if (is_null($this->settings)) {
            $defaults = array(
                'replace_edd_pages' => false,
                'fullscreen_mode' => false,
                'active_template' => 'default',
                'enabled_sections' => array(
                    'purchases' => true,
                    'downloads' => true,
                    'licenses' => true,
                    'wishlist' => true,
                    'analytics' => true,
                    'support' => true
                )
            );
            
            $this->settings = wp_parse_args(get_option('eddcdp_settings', array()), $defaults);
        }
        
        return $this->settings;
    }
    
    /**
     * Add plugin action links
     */
    public function plugin_action_links($links) {
        $settings_link = sprintf(
            '<a href="%s">%s</a>',
            esc_url(admin_url('edit.php?post_type=download&page=eddcdp-settings')),
            esc_html__('Settings', 'edd-customer-dashboard-pro')
        );
        
        array_unshift($links, $settings_link);
        return $links;
    }
    
    /**
     * Handle activation redirect
     */
    public function handle_activation_redirect() {
        if (get_option('eddcdp_activation_redirect', false)) {
            delete_option('eddcdp_activation_redirect');
            
            // Only redirect if not bulk activating
            if (!isset($_GET['activate-multi'])) {
                wp_safe_redirect(admin_url('edit.php?post_type=download&page=eddcdp-settings'));
                exit;
            }
        }
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Check dependencies on activation
        if (!class_exists('Easy_Digital_Downloads')) {
            wp_die(
                esc_html__('EDD Customer Dashboard Pro requires Easy Digital Downloads to be installed and active.', 'edd-customer-dashboard-pro'),
                esc_html__('Plugin Activation Error', 'edd-customer-dashboard-pro'),
                array('back_link' => true)
            );
        }
        
        // Set default settings
        $default_settings = array(
            'replace_edd_pages' => false,
            'fullscreen_mode' => false,
            'active_template' => 'default',
            'enabled_sections' => array(
                'purchases' => true,
                'downloads' => true,
                'licenses' => true,
                'wishlist' => true,
                'analytics' => true,
                'support' => true
            )
        );
        
        // Only add if not exists
        if (!get_option('eddcdp_settings')) {
            add_option('eddcdp_settings', $default_settings);
        }
        
        // Set activation redirect flag
        add_option('eddcdp_activation_redirect', true);
        
        // Clear any caches
        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
        }
        
        do_action('eddcdp_activated');
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Clear scheduled events
        wp_clear_scheduled_hook('eddcdp_cleanup_logs');
        
        // Clear template cache
        delete_transient('eddcdp_available_templates');
        delete_transient('eddcdp_dashboard_url');
        
        // Clear any caches
        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
        }
        
        do_action('eddcdp_deactivated');
    }
    
    /**
     * Show EDD missing notice
     */
    public function edd_missing_notice() {
        $class = 'notice notice-error';
        $message = __('EDD Customer Dashboard Pro requires Easy Digital Downloads to be installed and active.', 'edd-customer-dashboard-pro');
        
        printf('<div class="%1$s"><p><strong>%2$s</strong> %3$s</p></div>', 
            esc_attr($class), 
            esc_html__('EDD Customer Dashboard Pro:', 'edd-customer-dashboard-pro'),
            esc_html($message)
        );
        
        // Show install button if user can install plugins
        if (current_user_can('install_plugins')) {
            $install_url = wp_nonce_url(
                self_admin_url('update.php?action=install-plugin&plugin=easy-digital-downloads'),
                'install-plugin_easy-digital-downloads'
            );
            
            printf('<div class="%1$s"><p><a href="%2$s" class="button button-primary">%3$s</a></p></div>',
                esc_attr($class),
                esc_url($install_url),
                esc_html__('Install Easy Digital Downloads', 'edd-customer-dashboard-pro')
            );
        }
    }
    
    /**
     * Get plugin version
     */
    public function get_version() {
        return EDDCDP_VERSION;
    }
}

/**
 * Initialize the plugin
 */
function eddcdp() {
    return EDD_Customer_Dashboard_Pro::instance();
}

// Start the plugin
eddcdp();