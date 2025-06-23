<?php
/**
 * Template System for EDD Customer Dashboard Pro - Optimized
 */

if (!defined('ABSPATH')) {
    exit;
}

class EDDCDP_Templates {
    
    private static $instance = null;
    private $active_template = null;
    private $template_cache = array();
    
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('wp_head', array($this, 'add_template_styles'), 5);
    }
    
    /**
     * Get active template name
     */
    public function get_active_template() {
        if (is_null($this->active_template)) {
            $settings = get_option('eddcdp_settings', array());
            $this->active_template = isset($settings['active_template']) ? $settings['active_template'] : 'default';
        }
        return $this->active_template;
    }
    
    /**
     * Load the active dashboard template
     */
    public function load_dashboard_template() {
        $template_name = $this->get_active_template();
        $template_path = $this->get_template_path($template_name);
        
        if (!$template_path) {
            $this->render_error(__('Dashboard template not found.', 'eddcdp'));
            return false;
        }
        
        $dashboard_file = $template_path . '/dashboard.php';
        
        if (!file_exists($dashboard_file)) {
            $this->render_error(__('Dashboard template file is missing.', 'eddcdp'));
            return false;
        }
        
        // Set template globals
        $this->set_template_globals($template_name);
        
        include $dashboard_file;
        return true;
    }
    
    /**
     * Load a specific template section
     */
    public function load_section($section_name) {
        $template_name = $this->get_active_template();
        $template_path = $this->get_template_path($template_name);
        
        if (!$template_path) {
            return false;
        }
        
        $section_file = $template_path . '/sections/' . sanitize_file_name($section_name) . '.php';
        
        if (file_exists($section_file)) {
            include $section_file;
            return true;
        }
        
        return false;
    }
    
    /**
     * Get template path with caching
     */
    public function get_template_path($template_name) {
        // Check cache first
        if (isset($this->template_cache[$template_name])) {
            return $this->template_cache[$template_name];
        }
        
        $template_name = sanitize_file_name($template_name);
        
        if (empty($template_name)) {
            $template_name = 'default';
        }
        
        // Check for custom template in theme first
        $theme_template_dir = get_stylesheet_directory() . '/eddcdp/templates/' . $template_name;
        if (is_dir($theme_template_dir)) {
            $this->template_cache[$template_name] = $theme_template_dir;
            return $theme_template_dir;
        }
        
        // Check plugin templates
        $plugin_template_dir = EDDCDP_PLUGIN_DIR . 'templates/' . $template_name;
        if (is_dir($plugin_template_dir)) {
            $this->template_cache[$template_name] = $plugin_template_dir;
            return $plugin_template_dir;
        }
        
        // Fallback to default if template not found
        $default_dir = EDDCDP_PLUGIN_DIR . 'templates/default';
        if (is_dir($default_dir)) {
            $this->template_cache[$template_name] = $default_dir;
            return $default_dir;
        }
        
        return false;
    }
    
    /**
     * Get available templates with caching
     */
    public function get_available_templates() {
        $cache_key = 'eddcdp_available_templates';
        $templates = get_transient($cache_key);
        
        if (false === $templates) {
            $templates = array();
            
            // Scan plugin templates
            $plugin_templates_dir = EDDCDP_PLUGIN_DIR . 'templates/';
            $templates = array_merge($templates, $this->scan_template_directory($plugin_templates_dir));
            
            // Scan theme templates
            $theme_templates_dir = get_stylesheet_directory() . '/eddcdp/templates/';
            if (is_dir($theme_templates_dir)) {
                $theme_templates = $this->scan_template_directory($theme_templates_dir, true);
                $templates = array_merge($templates, $theme_templates);
            }
            
            // Cache for 12 hours
            set_transient($cache_key, $templates, 12 * HOUR_IN_SECONDS);
        }
        
        return $templates;
    }
    
    /**
     * Scan directory for templates
     */
    private function scan_template_directory($dir, $is_theme = false) {
        $templates = array();
        
        if (!is_dir($dir)) {
            return $templates;
        }
        
        $dirs = scandir($dir);
        foreach ($dirs as $template_dir) {
            if ($template_dir === '.' || $template_dir === '..' || !is_dir($dir . $template_dir)) {
                continue;
            }
            
            $config = $this->get_template_config($dir . $template_dir);
            if ($config) {
                if ($is_theme) {
                    $config['source'] = 'theme';
                    $config['name'] .= ' (Theme)';
                }
                $templates[$template_dir] = $config;
            }
        }
        
        return $templates;
    }
    
    /**
     * Get template configuration
     */
    private function get_template_config($template_dir) {
        $config_file = $template_dir . '/template.json';
        
        if (file_exists($config_file)) {
            $config = json_decode(file_get_contents($config_file), true);
            if ($config && is_array($config)) {
                return wp_parse_args($config, $this->get_default_template_config());
            }
        }
        
        // Default config if no JSON file
        return $this->get_default_template_config(basename($template_dir));
    }
    
    /**
     * Get default template configuration
     */
    private function get_default_template_config($template_name = '') {
        return array(
            'name' => !empty($template_name) ? ucfirst($template_name) . ' Template' : 'Unknown Template',
            'description' => 'Custom dashboard template',
            'version' => '1.0.0',
            'author' => 'EDD Customer Dashboard Pro',
            'supports' => array('purchases', 'downloads', 'licenses', 'wishlist', 'analytics', 'support'),
            'requirements' => array(
                'edd_version' => '3.0.0',
                'php_version' => '7.4'
            )
        );
    }
    
    /**
     * Set template globals for use in template files
     */
    private function set_template_globals($template_name) {
        global $eddcdp_template_name, $eddcdp_template_path, $eddcdp_current_user, $eddcdp_customer;
        
        $eddcdp_template_name = $template_name;
        $eddcdp_template_path = $this->get_template_path($template_name);
        $eddcdp_current_user = wp_get_current_user();
        $eddcdp_customer = edd_get_customer_by('email', $eddcdp_current_user->user_email);
    }
    
    /**
     * Enqueue template assets
     */
    public function enqueue_assets() {
        if (!$this->should_enqueue_assets()) {
            return;
        }
        
        $template_name = $this->get_active_template();
        $template_url = $this->get_template_url($template_name);
        
        // Enqueue CSS
        $css_file = $this->get_template_path($template_name) . '/style.css';
        if (file_exists($css_file)) {
            wp_enqueue_style(
                'eddcdp-template-' . $template_name,
                $template_url . 'style.css',
                array(),
                $this->get_asset_version($css_file)
            );
        }
        
        // Enqueue JS
        $js_file = $this->get_template_path($template_name) . '/script.js';
        if (file_exists($js_file)) {
            wp_enqueue_script(
                'eddcdp-template-' . $template_name,
                $template_url . 'script.js',
                array('jquery'),
                $this->get_asset_version($js_file),
                true
            );
            
            // Localize script
            wp_localize_script('eddcdp-template-' . $template_name, 'eddcdp', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('eddcdp_ajax_nonce'),
                'strings' => $this->get_js_strings()
            ));
        }
    }
    
    /**
     * Add template styles to head
     */
    public function add_template_styles() {
        if (!$this->should_enqueue_assets()) {
            return;
        }
        
        echo '<style id="eddcdp-template-vars">';
        echo ':root {';
        echo '--eddcdp-primary-color: ' . apply_filters('eddcdp_primary_color', '#6366f1') . ';';
        echo '--eddcdp-secondary-color: ' . apply_filters('eddcdp_secondary_color', '#8b5cf6') . ';';
        echo '}';
        echo '</style>';
    }
    
    /**
     * Get template URL
     */
    private function get_template_url($template_name) {
        $template_path = $this->get_template_path($template_name);
        
        // Check if it's a theme template
        $theme_dir = get_stylesheet_directory() . '/eddcdp/templates/' . $template_name;
        if ($template_path === $theme_dir) {
            return get_stylesheet_directory_uri() . '/eddcdp/templates/' . $template_name . '/';
        }
        
        // Plugin template
        return EDDCDP_PLUGIN_URL . 'templates/' . $template_name . '/';
    }
    
    /**
     * Get asset version for cache busting
     */
    private function get_asset_version($file_path) {
        if (file_exists($file_path)) {
            return filemtime($file_path);
        }
        return EDDCDP_VERSION;
    }
    
    /**
     * Check if we should enqueue assets
     */
    private function should_enqueue_assets() {
        global $post;
        
        if (is_a($post, 'WP_Post')) {
            $content = $post->post_content;
            
            if (has_shortcode($content, 'edd_customer_dashboard_pro')) {
                return true;
            }
            
            // Check if EDD pages replacement is enabled
            $settings = get_option('eddcdp_settings', array());
            if (!empty($settings['replace_edd_pages'])) {
                $edd_shortcodes = array('purchase_history', 'download_history', 'edd_purchase_history', 'edd_download_history');
                foreach ($edd_shortcodes as $shortcode) {
                    if (has_shortcode($content, $shortcode)) {
                        return true;
                    }
                }
            }
        }
        
        return false;
    }
    
    /**
     * Get JavaScript strings for localization
     */
    private function get_js_strings() {
        return array(
            'download_success' => __('Download started successfully!', 'eddcdp'),
            'license_copied' => __('License key copied to clipboard!', 'eddcdp'),
            'copy_failed' => __('Failed to copy to clipboard.', 'eddcdp'),
            'site_url_required' => __('Please enter a site URL.', 'eddcdp'),
            'activation_success' => __('Site activated successfully!', 'eddcdp'),
            'deactivation_success' => __('Site deactivated successfully!', 'eddcdp'),
            'confirm_deactivate' => __('Are you sure you want to deactivate this site?', 'eddcdp'),
            'confirm_remove_wishlist' => __('Are you sure you want to remove this item from your wishlist?', 'eddcdp'),
            'cart_success' => __('Added to cart successfully!', 'eddcdp'),
            'general_error' => __('An error occurred. Please try again.', 'eddcdp')
        );
    }
    
    /**
     * Render error message
     */
    private function render_error($message) {
        echo '<div class="eddcdp-error bg-red-50 border border-red-200 rounded-lg p-4 text-red-800">';
        echo '<p>' . esc_html($message) . '</p>';
        echo '</div>';
    }
    
    /**
     * Clear template cache
     */
    public function clear_cache() {
        delete_transient('eddcdp_available_templates');
        $this->template_cache = array();
    }
    
    /**
     * Validate template requirements
     */
    public function validate_template($template_name) {
        $config = $this->get_template_config($this->get_template_path($template_name));
        
        if (!$config) {
            return false;
        }
        
        // Check EDD version
        if (isset($config['requirements']['edd_version'])) {
            if (version_compare(EDD_VERSION, $config['requirements']['edd_version'], '<')) {
                return false;
            }
        }
        
        // Check PHP version
        if (isset($config['requirements']['php_version'])) {
            if (version_compare(PHP_VERSION, $config['requirements']['php_version'], '<')) {
                return false;
            }
        }
        
        return true;
    }
}