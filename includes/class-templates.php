<?php
/**
 * Template System for EDD Customer Dashboard Pro
 */

if (!defined('ABSPATH')) {
    exit;
}

class EDDCDP_Templates {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
    }
    
    /**
     * Load the active dashboard template
     */
    public function load_dashboard_template() {
        $settings = get_option('eddcdp_settings', array());
        
        // Debug the settings
        error_log('EDDCDP Debug - Settings: ' . print_r($settings, true));
        
        $active_template = isset($settings['active_template']) ? $settings['active_template'] : 'default';
        
        // Make sure we have a valid template name
        if (empty($active_template)) {
            $active_template = 'default';
        }
        
        $template_path = $this->get_template_path($active_template);
        $dashboard_file = $template_path . '/dashboard.php';
        
        // Debug output
        error_log('EDDCDP Debug - Active template: "' . $active_template . '"');
        error_log('EDDCDP Debug - Template path: ' . $template_path);
        error_log('EDDCDP Debug - Dashboard file: ' . $dashboard_file);
        error_log('EDDCDP Debug - File exists: ' . (file_exists($dashboard_file) ? 'YES' : 'NO'));
        
        if ($template_path && file_exists($dashboard_file)) {
            include $dashboard_file;
            return true;
        }
        
        echo '<div class="notice notice-error">';
        echo '<p>Dashboard template not found: "' . $active_template . '"</p>';
        echo '<p>Looking for: ' . $dashboard_file . '</p>';
        echo '<p>Template path: ' . $template_path . '</p>';
        echo '</div>';
        return false;
    }
    
    /**
     * Get template path
     */
    public function get_template_path($template_name) {
        // Ensure we have a template name
        if (empty($template_name)) {
            $template_name = 'default';
        }
        
        $template_dir = EDDCDP_PLUGIN_DIR . 'templates/' . $template_name;
        
        error_log('EDDCDP Debug - get_template_path called with: ' . $template_name);
        error_log('EDDCDP Debug - Full template dir: ' . $template_dir);
        error_log('EDDCDP Debug - Directory exists: ' . (is_dir($template_dir) ? 'YES' : 'NO'));
        
        if (is_dir($template_dir)) {
            return $template_dir;
        }
        
        // Fallback to default if template not found
        $default_dir = EDDCDP_PLUGIN_DIR . 'templates/default';
        if (is_dir($default_dir)) {
            error_log('EDDCDP Debug - Falling back to default template');
            return $default_dir;
        }
        
        return false;
    }
    
    /**
     * Get available templates
     */
    public function get_available_templates() {
        $templates = array();
        $templates_dir = EDDCDP_PLUGIN_DIR . 'templates/';
        
        if (!is_dir($templates_dir)) {
            return $templates;
        }
        
        $dirs = scandir($templates_dir);
        foreach ($dirs as $dir) {
            if ($dir === '.' || $dir === '..' || !is_dir($templates_dir . $dir)) {
                continue;
            }
            
            $config_file = $templates_dir . $dir . '/template.json';
            if (file_exists($config_file)) {
                $config = json_decode(file_get_contents($config_file), true);
                if ($config) {
                    $templates[$dir] = $config;
                }
            } else {
                // Default config if no JSON file
                $templates[$dir] = array(
                    'name' => ucfirst($dir) . ' Template',
                    'description' => 'Custom dashboard template',
                    'version' => '1.0.0',
                    'author' => 'EDD Customer Dashboard Pro'
                );
            }
        }
        
        return $templates;
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
        
        // Enqueue CSS
        $css_file = $template_path . '/style.css';
        if (file_exists($css_file)) {
            wp_enqueue_style(
                'eddcdp-template-' . $active_template,
                $template_url . 'style.css',
                array(),
                EDDCDP_VERSION
            );
        }
        
        // Enqueue JS
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
    }
    
    /**
     * Check if we should enqueue assets
     */
    private function should_enqueue_assets() {
        global $post;
        
        if (is_a($post, 'WP_Post')) {
            $content = $post->post_content;
            
            // Check for our shortcode or EDD shortcodes
            if (has_shortcode($content, 'edd_customer_dashboard_pro') ||
                has_shortcode($content, 'purchase_history') ||
                has_shortcode($content, 'download_history') ||
                has_shortcode($content, 'edd_purchase_history') ||
                has_shortcode($content, 'edd_download_history')) {
                return true;
            }
        }
        
        return false;
    }
}