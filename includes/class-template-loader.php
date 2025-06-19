<?php
/**
 * Template Loader Class
 * 
 * Handles loading and management of dashboard templates
 */

if (!defined('ABSPATH')) {
    exit;
}

class EDDCDP_Template_Loader {
    
    private $templates_dir;
    private $available_templates = array();
    
    public function __construct() {
        $this->templates_dir = EDDCDP_PLUGIN_DIR . 'templates/';
        $this->load_available_templates();
    }
    
    /**
     * Load available templates from templates directory
     */
    private function load_available_templates() {
        if (!is_dir($this->templates_dir)) {
            return;
        }
        
        $template_dirs = glob($this->templates_dir . '*', GLOB_ONLYDIR);
        
        foreach ($template_dirs as $template_dir) {
            $template_name = basename($template_dir);
            $template_json = $template_dir . '/template.json';
            
            if (file_exists($template_json)) {
                $template_data = json_decode(file_get_contents($template_json), true);
                if ($template_data) {
                    $this->available_templates[$template_name] = $template_data;
                }
            } else {
                // Default template info if no JSON file
                $this->available_templates[$template_name] = array(
                    'name' => ucfirst($template_name),
                    'description' => sprintf(__('%s template for EDD Customer Dashboard Pro', EDDCDP_TEXT_DOMAIN), ucfirst($template_name)),
                    'version' => '1.0.0',
                    'author' => 'Unknown'
                );
            }
        }
    }
    
    /**
     * Get all available templates
     */
    public function get_available_templates() {
        return $this->available_templates;
    }
    
    /**
     * Check if template exists
     */
    public function template_exists($template_name) {
        return isset($this->available_templates[$template_name]);
    }
    
    /**
     * Get template directory path
     */
    public function get_template_dir($template_name) {
        if (!$this->template_exists($template_name)) {
            $template_name = 'default';
        }
        
        return $this->templates_dir . $template_name . '/';
    }
    
    /**
     * Get template URL
     */
    public function get_template_url($template_name) {
        if (!$this->template_exists($template_name)) {
            $template_name = 'default';
        }
        
        return EDDCDP_PLUGIN_URL . 'templates/' . $template_name . '/';
    }
    
    /**
     * Load main template
     */
    public function load_template($template_name, $data = array()) {
        if (!$this->template_exists($template_name)) {
            $template_name = 'default';
        }
        
        $template_file = $this->get_template_dir($template_name) . 'dashboard.php';
        
        if (!file_exists($template_file)) {
            return '<p>' . __('Template not found.', EDDCDP_TEXT_DOMAIN) . '</p>';
        }
        
        // Extract data for template
        extract($data);
        
        ob_start();
        include $template_file;
        return ob_get_clean();
    }
    
    /**
     * Load template section
     */
    public function load_section($template_name, $section_name, $data = array()) {
        if (!$this->template_exists($template_name)) {
            $template_name = 'default';
        }
        
        $section_file = $this->get_template_dir($template_name) . 'sections/' . $section_name . '.php';
        
        if (!file_exists($section_file)) {
            return '<p>' . sprintf(__('Section "%s" not found.', EDDCDP_TEXT_DOMAIN), $section_name) . '</p>';
        }
        
        // Extract data for template
        extract($data);
        
        ob_start();
        include $section_file;
        return ob_get_clean();
    }
    
    /**
     * Enqueue template assets
     */
    public function enqueue_template_assets($template_name) {
        if (!$this->template_exists($template_name)) {
            $template_name = 'default';
        }
        
        $template_url = $this->get_template_url($template_name);
        $template_dir = $this->get_template_dir($template_name);
        
        // Enqueue CSS
        $css_file = $template_dir . 'style.css';
        if (file_exists($css_file)) {
            wp_enqueue_style(
                'eddcdp-template-' . $template_name,
                $template_url . 'style.css',
                array(),
                filemtime($css_file)
            );
        }
        
        // Enqueue JS
        $js_file = $template_dir . 'script.js';
        if (file_exists($js_file)) {
            wp_enqueue_script(
                'eddcdp-template-' . $template_name,
                $template_url . 'script.js',
                array('jquery'),
                filemtime($js_file),
                true
            );
            
            // Localize script with AJAX URL and nonce
            wp_localize_script('eddcdp-template-' . $template_name, 'eddcdp_ajax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('eddcdp_nonce'),
                'text_domain' => EDDCDP_TEXT_DOMAIN
            ));
        }
    }
    
    /**
     * Get template metadata
     */
    public function get_template_info($template_name) {
        if (!$this->template_exists($template_name)) {
            return false;
        }
        
        return $this->available_templates[$template_name];
    }
    
    /**
     * Create template preview screenshot URL
     */
    public function get_template_screenshot($template_name) {
        if (!$this->template_exists($template_name)) {
            return false;
        }
        
        $screenshot_path = $this->get_template_dir($template_name) . 'screenshot.png';
        
        if (file_exists($screenshot_path)) {
            return $this->get_template_url($template_name) . 'screenshot.png';
        }
        
        return false;
    }
    
    /**
     * Validate template structure
     */
    public function validate_template($template_name) {
        if (!$this->template_exists($template_name)) {
            return false;
        }
        
        $template_dir = $this->get_template_dir($template_name);
        $required_files = array('dashboard.php');
        
        foreach ($required_files as $file) {
            if (!file_exists($template_dir . $file)) {
                return false;
            }
        }
        
        return true;
    }
}