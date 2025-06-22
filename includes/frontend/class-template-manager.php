<?php
/**
 * Template Manager Component
 * 
 * Handles template loading, rendering, and management
 */

if (!defined('ABSPATH')) {
    exit;
}

class EDDCDP_Template_Manager implements EDDCDP_Component_Interface {
    
    private $template_loader;
    
    /**
     * Initialize component
     */
    public function init() {
        // Template manager initialization
    }
    
    /**
     * Get component dependencies
     */
    public function get_dependencies() {
        return array(); // No dependencies
    }
    
    /**
     * Check if component should load
     */
    public function should_load() {
        return !is_admin(); // Only load on frontend
    }
    
    /**
     * Get component priority
     */
    public function get_priority() {
        return 25; // Load before shortcode handler
    }
    
    /**
     * Set template loader (legacy compatibility)
     */
    public function set_template_loader($template_loader) {
        $this->template_loader = $template_loader;
    }
    
    /**
     * Render template with data
     */
    public function render_template($template_name, $data = array()) {
        if (!$this->template_loader) {
            return $this->render_error(__('Template loader not available.', 'edd-customer-dashboard-pro'));
        }
        
        // Apply filters to template data
        $data = apply_filters('eddcdp_template_data', $data, $template_name);
        
        // Render template
        $output = $this->template_loader->load_template(null, $data);
        
        // Apply filters to output
        return apply_filters('eddcdp_template_output', $output, $template_name, $data);
    }
    
    /**
     * Render template section
     */
    public function render_section($section_name, $data = array()) {
        if (!$this->template_loader) {
            return $this->render_error(__('Template loader not available.', 'edd-customer-dashboard-pro'));
        }
        
        // Apply filters to section data
        $data = apply_filters('eddcdp_section_data', $data, $section_name);
        
        // Render section
        $output = $this->template_loader->load_section($section_name, null, $data);
        
        // Apply filters to section output
        return apply_filters('eddcdp_section_output', $output, $section_name, $data);
    }
    
    /**
     * Get available templates
     */
    public function get_available_templates() {
        if (!$this->template_loader) {
            return array();
        }
        
        return $this->template_loader->get_available_templates();
    }
    
    /**
     * Get active template
     */
    public function get_active_template() {
        if (!$this->template_loader) {
            return 'default';
        }
        
        return $this->template_loader->get_active_template();
    }
    
    /**
     * Get template directory path
     */
    public function get_template_dir($template_name = null) {
        if (!$this->template_loader) {
            return '';
        }
        
        return $this->template_loader->get_template_dir($template_name);
    }
    
    /**
     * Get template URL
     */
    public function get_template_url($template_name = null) {
        if (!$this->template_loader) {
            return '';
        }
        
        return $this->template_loader->get_template_url($template_name);
    }
    
    /**
     * Enqueue template assets
     */
    public function enqueue_template_assets($template_name = null) {
        if (!$this->template_loader) {
            return;
        }
        
        $this->template_loader->enqueue_template_assets($template_name);
    }
    
    /**
     * Check if template exists
     */
    public function template_exists($template_name) {
        if (!$this->template_loader) {
            return false;
        }
        
        return $this->template_loader->template_exists($template_name);
    }
    
    /**
     * Get template info
     */
    public function get_template_info($template_name) {
        if (!$this->template_loader) {
            return false;
        }
        
        return $this->template_loader->get_template_info($template_name);
    }
    
    /**
     * Render error message
     */
    private function render_error($message) {
        return sprintf(
            '<div class="eddcdp-error">
                <p>%s</p>
            </div>',
            esc_html($message)
        );
    }
}