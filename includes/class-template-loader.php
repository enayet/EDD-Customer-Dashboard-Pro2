<?php
/**
 * Fixed Template Loader Class - Parameter order corrected
 */

if (!defined('ABSPATH')) {
    exit;
}

class EDDCDP_Template_Loader {
    
    private $templates_dir;
    private $available_templates = array();
    private $active_template = null;
    
    public function __construct() {
        $this->templates_dir = EDDCDP_PLUGIN_DIR . 'templates/';
        $this->load_available_templates();
        $this->set_active_template();
    }
    
    /**
     * Set active template from settings
     */
    private function set_active_template() {
        $settings = get_option('eddcdp_settings', array());
        $this->active_template = isset($settings['active_template']) ? sanitize_text_field($settings['active_template']) : 'default';
        
        // Fallback to default if active template doesn't exist
        if (!$this->template_exists($this->active_template)) {
            $this->active_template = 'default';
        }
    }
    
    /**
     * Get current active template
     */
    public function get_active_template() {
        return $this->active_template;
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
                    'description' => sprintf(__('%s template for EDD Customer Dashboard Pro', 'edd-customer-dashboard-pro'), ucfirst($template_name)),
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
    public function get_template_dir($template_name = null) {
        if ($template_name === null) {
            $template_name = $this->active_template;
        }
        
        if (!$this->template_exists($template_name)) {
            $template_name = 'default';
        }
        
        return $this->templates_dir . $template_name . '/';
    }
    
    /**
     * Get template URL
     */
    public function get_template_url($template_name = null) {
        if ($template_name === null) {
            $template_name = $this->active_template;
        }
        
        if (!$this->template_exists($template_name)) {
            $template_name = 'default';
        }
        
        return EDDCDP_PLUGIN_URL . 'templates/' . $template_name . '/';
    }
    
    /**
     * Load main template
     */
    public function load_template($template_name = null, $data = array()) {
        if ($template_name === null) {
            $template_name = $this->active_template;
        }
        
        if (!$this->template_exists($template_name)) {
            $template_name = 'default';
        }
        
        $template_file = $this->get_template_dir($template_name) . 'dashboard.php';
        
        if (!file_exists($template_file)) {
            return '<p>' . __('Template not found.', 'edd-customer-dashboard-pro') . '</p>';
        }
        
        // Extract data for template
        extract($data);
        
        ob_start();
        include $template_file;
        return ob_get_clean();
    }
    
    /**
     * Load template section - FIXED PARAMETER ORDER
     */
    public function load_section($section_name, $template_name = null, $data = array()) {

        if ($template_name == null) {
            $template_name = $this->active_template;
        }
        
        if (!$this->template_exists($template_name)) {
            $template_name = 'default';
        }
        
        $section_file = $this->get_template_dir($template_name) . 'sections/' . $section_name . '.php';
        
        
        
        if (!file_exists($section_file)) {
            return '<p>' . sprintf(__('Section "%s" not found.', 'edd-customer-dashboard-pro'), $section_name) . '</p>';
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
    public function enqueue_template_assets($template_name = null) {
        if ($template_name === null) {
            $template_name = $this->active_template;
        }
        
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
                'text_domain' => 'edd-customer-dashboard-pro',
                'template' => $template_name
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
            return $this->get_template_url($template_name) . 'screenshot.png?v=' . filemtime($screenshot_path);
        }
        
        return false;
    }
    
    /**
     * Switch active template - Simplified
     */
    public function switch_template($template_name) {
        // Get current settings
        $settings = get_option('eddcdp_settings', array());
        
        // Update active template
        $settings['active_template'] = sanitize_text_field($template_name);
        
        // Save to database
        $result = update_option('eddcdp_settings', $settings);
        
        // Update local property
        if ($result || get_option('eddcdp_settings')['active_template'] === $template_name) {
            $this->active_template = $template_name;
            return true;
        }
        
        return false;
    }
    
    /**
     * Get template requirements
     */
    public function get_template_requirements($template_name) {
        $template_info = $this->get_template_info($template_name);
        
        if (!$template_info || !isset($template_info['supports'])) {
            return array();
        }
        
        $requirements = array();
        $supports = $template_info['supports'];
        
        if (isset($supports['licenses']) && $supports['licenses']) {
            $requirements['licensing'] = array(
                'name' => __('EDD Software Licensing', 'edd-customer-dashboard-pro'),
                'met' => class_exists('EDD_Software_Licensing')
            );
        }
        
        if (isset($supports['wishlist']) && $supports['wishlist']) {
            $requirements['wishlist'] = array(
                'name' => __('EDD Wish Lists', 'edd-customer-dashboard-pro'),
                'met' => class_exists('EDD_Wish_Lists')
            );
        }
        
        return $requirements;
    }
    
    /**
     * Check if template has all requirements met
     */
    public function template_requirements_met($template_name) {
        $requirements = $this->get_template_requirements($template_name);
        
        foreach ($requirements as $requirement) {
            if (!$requirement['met']) {
                return false;
            }
        }
        
        return true;
    }
}