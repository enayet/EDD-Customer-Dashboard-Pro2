<?php
/**
 * Template System for EDD Customer Dashboard Pro
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class EDDCDP_Templates {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array($this, 'init'));
    }
    
    /**
     * Initialize
     */
    public function init() {
        // Hook into EDD customer dashboard
        add_filter('edd_get_template_part', array($this, 'override_template'), 10, 3);
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
    }
    
    /**
     * Override EDD templates if our dashboard is active
     */
    public function override_template($templates, $slug, $name) {
        $settings = get_option('eddcdp_settings', array());
        
        // Only override if replacement is enabled
        if (empty($settings['replace_edd_pages'])) {
            return $templates;
        }
        
        // Check if this is a customer dashboard page
        if ($slug === 'history' || $slug === 'downloads' || is_page('customer-dashboard')) {
            return $this->load_dashboard_template();
        }
        
        return $templates;
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
     * Get available templates
     */
    public function get_available_templates() {
        $templates = array();
        $template_dir = EDDCDP_PLUGIN_DIR . 'templates/';
        
        if (is_dir($template_dir)) {
            $dirs = scandir($template_dir);
            
            foreach ($dirs as $dir) {
                if ($dir === '.' || $dir === '..') {
                    continue;
                }
                
                $template_path = $template_dir . $dir;
                $config_file = $template_path . '/template.json';
                
                if (is_dir($template_path) && file_exists($config_file)) {
                    $config = json_decode(file_get_contents($config_file), true);
                    
                    if ($config) {
                        $templates[$dir] = $config;
                    }
                }
            }
        }
        
        return $templates;
    }
    
    /**
     * Get template config
     */
    public function get_template_config($template_name) {
        $template_path = $this->get_template_path($template_name);
        $config_file = $template_path . '/template.json';
        
        if (file_exists($config_file)) {
            return json_decode(file_get_contents($config_file), true);
        }
        
        return false;
    }
    
    /**
     * Include template section
     */
    public function include_section($section, $template_name = null) {
        if (!$template_name) {
            $settings = get_option('eddcdp_settings', array());
            $template_name = isset($settings['active_template']) ? $settings['active_template'] : 'default';
        }
        
        $template_path = $this->get_template_path($template_name);
        $section_file = $template_path . '/sections/' . $section . '.php';
        
        if (file_exists($section_file)) {
            include $section_file;
            return true;
        }
        
        return false;
    }
    
    /**
     * Enqueue template assets
     */
    public function enqueue_assets() {
        if (!$this->is_dashboard_page()) {
            return;
        }
        
        $settings = get_option('eddcdp_settings', array());
        $active_template = isset($settings['active_template']) ? $settings['active_template'] : 'default';
        
        $template_path = $this->get_template_path($active_template);
        $template_url = EDDCDP_PLUGIN_URL . 'templates/' . $active_template . '/';
        
        // Enqueue CSS if exists
        if (file_exists($template_path . '/style.css')) {
            wp_enqueue_style(
                'eddcdp-template-' . $active_template,
                $template_url . 'style.css',
                array(),
                EDDCDP_VERSION
            );
        }
        
        // Enqueue JS if exists
        if (file_exists($template_path . '/script.js')) {
            wp_enqueue_script(
                'eddcdp-template-' . $active_template,
                $template_url . 'script.js',
                array('jquery'),
                EDDCDP_VERSION,
                true
            );
        }
    }
    
    /**
     * Check if current page is dashboard page
     */
    private function is_dashboard_page() {
        return is_page('customer-dashboard') || edd_is_checkout() || edd_is_purchase_history_page();
    }
}