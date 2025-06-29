<?php
/**
 * Plugin Name: EDD Customer Dashboard Pro
 * Plugin URI: https://yoursite.com/edd-dashboard-pro
 * Description: Custom dashboard templates for Easy Digital Downloads customer area
 * Version: 1.0.0
 * Author: Your Name
 * Text Domain: edd-customer-dashboard-pro
 * Requires at least: 5.0
 * Requires PHP: 7.4
 * License: GPL v2 or later
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('EDDCDP_VERSION', '1.0.7');
define('EDDCDP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('EDDCDP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('EDDCDP_PLUGIN_FILE', __FILE__);

/**
 * Main EDD Dashboard Pro Class - Optimized
 */
final class EDD_Customer_Dashboard_Pro {
    
    private static $instance = null;
    
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('plugins_loaded', array($this, 'init'), 10);
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    public function init() {
        // Check if EDD is active
        if (!$this->is_edd_active()) {
            add_action('admin_notices', array($this, 'edd_missing_notice'));
            return;
        }
        
        // Check minimum requirements
        if (!$this->check_requirements()) {
            return;
        }
        
        $this->load_textdomain();
        $this->includes();
        $this->init_hooks();
        
        // Plugin is fully loaded
        do_action('eddcdp_loaded');
    }
    
    private function is_edd_active() {
        return class_exists('Easy_Digital_Downloads');
    }
    
    private function check_requirements() {
        $errors = array();
        
        // Check PHP version
        if (version_compare(PHP_VERSION, '7.4', '<')) {
            /* translators: %s: Current PHP version */
            $errors[] = sprintf(esc_html__('EDD Customer Dashboard Pro requires PHP 7.4 or higher. You are running version %s.', 'edd-customer-dashboard-pro'), PHP_VERSION);
        }
        
        // Check WordPress version
        global $wp_version;
        if (version_compare($wp_version, '5.0', '<')) {
            /* translators: %s: Current WordPress version */
            $errors[] = sprintf(esc_html__('EDD Customer Dashboard Pro requires WordPress 5.0 or higher. You are running version %s.', 'edd-customer-dashboard-pro'), $wp_version);
        }
        
        // Check EDD version
        if (defined('EDD_VERSION') && version_compare(EDD_VERSION, '2.8', '<')) {
            /* translators: %s: Current EDD version */
            $errors[] = sprintf(esc_html__('EDD Customer Dashboard Pro requires Easy Digital Downloads 2.8 or higher. You are running version %s.', 'edd-customer-dashboard-pro'), EDD_VERSION);
        }
        
        if (!empty($errors)) {
            add_action('admin_notices', function() use ($errors) {
                foreach ($errors as $error) {
                    echo '<div class="notice notice-error"><p>' . wp_kses_post($error) . '</p></div>';
                }
            });
            return false;
        }
        
        return true;
    }
    
    private function load_textdomain() {
        load_plugin_textdomain('edd-customer-dashboard-pro', false, dirname(plugin_basename(__FILE__)) . '/languages/');
    }
    
    private function includes() {
        $includes = array(
            'includes/functions.php',
            'includes/class-templates.php',
            'includes/class-admin.php', 
            'includes/class-shortcodes.php',
            'includes/class-invoice.php',
            'includes/class-order-details.php',
            'includes/class-wishlist-handler.php',
            'includes/class-fullscreen-helper.php'
        );
        
        foreach ($includes as $file) {
            $file_path = EDDCDP_PLUGIN_DIR . $file;
            if (file_exists($file_path)) {
                require_once $file_path;
            } else {
                /* translators: %s: Missing file path */
                wp_die(sprintf(esc_html__('Required file missing: %s', 'edd-customer-dashboard-pro'), esc_html($file)));
            }
        }
    }
    
    private function init_hooks() {
        // Initialize components based on context
        if (is_admin()) {
            EDDCDP_Admin::instance();
        }
        
        // Always initialize these
        EDDCDP_Templates::instance();
        EDDCDP_Shortcodes::instance();
        EDDCDP_Order_Details::instance();
        EDDCDP_Invoice_Redirect::instance();
        EDDCDP_Wishlist_Handler::instance();
        EDDCDP_Fullscreen_Helper::instance();
        
        // Setup hooks
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
        add_filter('body_class', array($this, 'add_body_classes'));
        
        // Add plugin action links
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'plugin_action_links'));
    }
    
    public function enqueue_frontend_assets() {
        // Only enqueue on pages that need it
        if (!$this->should_enqueue_assets()) {
            return;
        }
        
        // Enqueue common styles
        wp_enqueue_style(
            'eddcdp-common',
            EDDCDP_PLUGIN_URL . 'assets/css/common.css',
            array(),
            EDDCDP_VERSION
        );
        
        // Enqueue common scripts
        wp_enqueue_script(
            'eddcdp-common',
            EDDCDP_PLUGIN_URL . 'assets/js/common.js',
            array('jquery'),
            EDDCDP_VERSION,
            true
        );
        
        // Localize script
        wp_localize_script('eddcdp-common', 'edd-customer-dashboard-pro', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('eddcdp_ajax_nonce'),
            'is_user_logged_in' => is_user_logged_in(),
            'checkout_url' => edd_get_checkout_uri(),
            'strings' => array(
                'loading' => esc_html__('Loading...', 'edd-customer-dashboard-pro'),
                'error' => esc_html__('An error occurred. Please try again.', 'edd-customer-dashboard-pro'),
                'success' => esc_html__('Success!', 'edd-customer-dashboard-pro')
            )
        ));
    }
    
    private function should_enqueue_assets() {
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
    
    public function add_body_classes($classes) {
        if ($this->should_enqueue_assets()) {
            $classes[] = 'eddcdp-dashboard-page';
            
            // Add fullscreen class if enabled
            $settings = get_option('eddcdp_settings', array());
            if (!empty($settings['fullscreen_mode'])) {
                $classes[] = 'eddcdp-fullscreen-mode';
            }
            
            // Add template class
            $active_template = isset($settings['active_template']) ? $settings['active_template'] : 'default';
            $classes[] = 'eddcdp-template-' . sanitize_html_class($active_template);
        }
        
        return $classes;
    }
    
    public function plugin_action_links($links) {
        $settings_link = '<a href="' . esc_url(admin_url('edit.php?post_type=download&page=eddcdp-settings')) . '">' . esc_html__('Settings', 'edd-customer-dashboard-pro') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }
    
    public function activate() {
        // Check requirements on activation
        if (!$this->is_edd_active()) {
            wp_die(esc_html__('EDD Customer Dashboard Pro requires Easy Digital Downloads to be installed and active.', 'edd-customer-dashboard-pro'));
        }
        
        // Create default settings
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
        
        add_option('eddcdp_settings', $default_settings);
        
        // Create initial logs table (if needed)
        add_option('eddcdp_license_logs', array());
        
        // Set activation flag
        add_option('eddcdp_activation_redirect', true);
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Schedule any recurring events here if needed
        do_action('eddcdp_activated');
    }
    
    public function deactivate() {
        // Clear scheduled events
        wp_clear_scheduled_hook('eddcdp_cleanup_logs');
        
        // Clear template cache
        if (class_exists('EDDCDP_Templates')) {
            EDDCDP_Templates::instance()->clear_cache();
        }
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
        do_action('eddcdp_deactivated');
    }
    
    public function edd_missing_notice() {
        echo '<div class="notice notice-error">';
        echo '<p><strong>' . esc_html__('EDD Customer Dashboard Pro', 'edd-customer-dashboard-pro') . '</strong></p>';
        echo '<p>' . esc_html__('This plugin requires Easy Digital Downloads to be installed and active.', 'edd-customer-dashboard-pro') . '</p>';
        if (current_user_can('install_plugins')) {
            echo '<p><a href="' . esc_url(admin_url('plugin-install.php?s=easy+digital+downloads&tab=search&type=term')) . '" class="button button-primary">' . esc_html__('Install Easy Digital Downloads', 'edd-customer-dashboard-pro') . '</a></p>';
        }
        echo '</div>';
    }
    
    /**
     * Get plugin version
     */
    public function get_version() {
        return EDDCDP_VERSION;
    }
    
    /**
     * Get plugin settings
     */
    public function get_settings() {
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
        
        return wp_parse_args(get_option('eddcdp_settings', array()), $defaults);
    }
}

// Initialize the plugin
function eddcdp() {
    return EDD_Customer_Dashboard_Pro::instance();
}

// Start the plugin
eddcdp();

// Activation redirect
add_action('admin_init', function() {
    if (get_option('eddcdp_activation_redirect', false)) {
        delete_option('eddcdp_activation_redirect');
        if (!isset($_GET['activate-multi'])) {
            wp_redirect(esc_url_raw(admin_url('edit.php?post_type=download&page=eddcdp-settings')));
            exit;
        }
    }
});