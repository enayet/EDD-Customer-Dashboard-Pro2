<?php
/**
 * Template Manager Component
 * 
 * Modern template loading, rendering, and management without legacy dependencies
 */

if (!defined('ABSPATH')) {
    exit;
}

class EDDCDP_Template_Manager implements EDDCDP_Component_Interface {
    
    private string $templates_dir;
    private array $available_templates = [];
    private string $active_template;
    private bool $assets_enqueued = false;
    
    /**
     * Initialize component
     */
    public function init() {
        $this->templates_dir = EDDCDP_PLUGIN_DIR . 'templates/';
        $this->load_available_templates();
        $this->set_active_template();
        
        // Hook into WordPress
        add_action('wp_enqueue_scripts', array($this, 'maybe_enqueue_template_assets'));
        add_action('wp_head', array($this, 'output_template_styles'), 20);
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
     * Set active template from settings
     */
    private function set_active_template(): void {
        $settings = get_option('eddcdp_settings', []);
        $this->active_template = sanitize_text_field($settings['active_template'] ?? 'default');
        
        // Fallback to default if active template doesn't exist
        if (!$this->template_exists($this->active_template)) {
            $this->active_template = 'default';
        }
    }
    
    /**
     * Load available templates from templates directory
     */
    private function load_available_templates(): void {
        if (!is_dir($this->templates_dir)) {
            return;
        }
        
        $template_dirs = glob($this->templates_dir . '*', GLOB_ONLYDIR);
        
        foreach ($template_dirs as $template_dir) {
            $template_name = basename($template_dir);
            $template_json = $template_dir . '/template.json';
            
            if (file_exists($template_json)) {
                $template_data = json_decode(file_get_contents($template_json), true);
                if ($template_data && json_last_error() === JSON_ERROR_NONE) {
                    $this->available_templates[$template_name] = $template_data;
                }
            } else {
                // Default template info if no JSON file
                $this->available_templates[$template_name] = [
                    'name' => ucfirst($template_name),
                    'description' => sprintf(__('%s template for EDD Customer Dashboard Pro', 'edd-customer-dashboard-pro'), ucfirst($template_name)),
                    'version' => '1.0.0',
                    'author' => 'Unknown'
                ];
            }
        }
    }
    
    /**
     * Render template with data
     */
    public function render_template($template_name, $data = array()) {
        // Ensure we have the template name
        if (empty($template_name)) {
            $template_name = 'dashboard';
        }
        
        // Ensure complete data
        $data = $this->ensure_complete_data($data);
        
        // Apply filters to template data
        $data = apply_filters('eddcdp_template_data', $data, $template_name);
        
        // Get the template file path
        $template_file = $this->get_template_file($template_name);
        
        if (!$template_file) {
            return $this->render_error(__('Template not found.', 'edd-customer-dashboard-pro'));
        }
        
        // Enqueue assets for this template
        $this->enqueue_template_assets();
        
        // Extract data for template
        extract($data, EXTR_SKIP);
        
        // Start output buffering
        ob_start();
        include $template_file;
        $output = ob_get_clean();
        
        // Apply filters to output
        return apply_filters('eddcdp_template_output', $output, $template_name, $data);
    }
    
    /**
     * Render template section
     */
    public function render_section($section_name, $data = array()) {
        // Ensure complete data
        $data = $this->ensure_complete_data($data);
        
        // Apply filters to section data
        $data = apply_filters('eddcdp_section_data', $data, $section_name);
        
        // Get the section file path
        $section_file = $this->get_section_file($section_name);
        
        if (!$section_file) {
            return $this->render_error(
                sprintf(__('Section "%s" not found.', 'edd-customer-dashboard-pro'), esc_html($section_name))
            );
        }
        
        // Extract data for section
        extract($data, EXTR_SKIP);
        
        // Start output buffering
        ob_start();
        include $section_file;
        $output = ob_get_clean();
        
        // Apply filters to section output
        return apply_filters('eddcdp_section_output', $output, $section_name, $data);
    }
    
    /**
     * Get template file path
     */
    private function get_template_file($template_name) {
        $active_template_dir = $this->get_template_dir();
        $template_file = $active_template_dir . $template_name . '.php';
        
        // Check if template exists in active template
        if (file_exists($template_file)) {
            return $template_file;
        }
        
        // Fallback to dashboard.php if specific template not found
        $dashboard_file = $active_template_dir . 'dashboard.php';
        if (file_exists($dashboard_file)) {
            return $dashboard_file;
        }
        
        // Fallback to default template
        $default_template_dir = $this->templates_dir . 'default/';
        $default_file = $default_template_dir . $template_name . '.php';
        
        if (file_exists($default_file)) {
            return $default_file;
        }
        
        // Final fallback to default dashboard
        $default_dashboard = $default_template_dir . 'dashboard.php';
        if (file_exists($default_dashboard)) {
            return $default_dashboard;
        }
        
        return false;
    }
    
    /**
     * Get section file path
     */
    private function get_section_file($section_name) {
        $active_template_dir = $this->get_template_dir();
        $section_file = $active_template_dir . 'sections/' . $section_name . '.php';
        
        // Check if section exists in active template
        if (file_exists($section_file)) {
            return $section_file;
        }
        
        // Fallback to default template
        $default_template_dir = $this->templates_dir . 'default/';
        $default_section = $default_template_dir . 'sections/' . $section_name . '.php';
        
        if (file_exists($default_section)) {
            return $default_section;
        }
        
        return false;
    }
    
    /**
     * Get available templates
     */
    public function get_available_templates() {
        return $this->available_templates;
    }
    
    /**
     * Get active template
     */
    public function get_active_template() {
        return $this->active_template;
    }
    
    /**
     * Get template directory path
     */
    public function get_template_dir($template_name = null) {
        $template_name = $template_name ?? $this->active_template;
        
        if (!$this->template_exists($template_name)) {
            $template_name = 'default';
        }
        
        return $this->templates_dir . $template_name . '/';
    }
    
    /**
     * Get template URL
     */
    public function get_template_url($template_name = null) {
        $template_name = $template_name ?? $this->active_template;
        
        if (!$this->template_exists($template_name)) {
            $template_name = 'default';
        }
        
        return EDDCDP_PLUGIN_URL . 'templates/' . $template_name . '/';
    }
    
    /**
     * Check if template exists
     */
    public function template_exists($template_name) {
        return isset($this->available_templates[$template_name]);
    }
    
    /**
     * Get template info
     */
    public function get_template_info($template_name) {
        return $this->available_templates[$template_name] ?? false;
    }
    
    /**
     * Switch active template
     */
    public function switch_template($template_name) {
        if (!$this->template_exists($template_name)) {
            return false;
        }
        
        // Get current settings
        $settings = get_option('eddcdp_settings', []);
        
        // Update active template
        $settings['active_template'] = sanitize_text_field($template_name);
        
        // Save to database
        $result = update_option('eddcdp_settings', $settings);
        
        // Update local property
        if ($result) {
            $this->active_template = $template_name;
            
            // Clear any template-related cache
            if (class_exists('EDDCDP_Cache_Helper')) {
                EDDCDP_Cache_Helper::invalidate_template_cache();
            }
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Enqueue template assets
     */
    public function enqueue_template_assets($template_name = null) {
        if ($this->assets_enqueued) {
            return; // Prevent duplicate enqueueing
        }
        
        $template_name = $template_name ?? $this->active_template;
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
        
        // Enqueue JavaScript
        $js_file = $template_dir . 'script.js';
        if (file_exists($js_file)) {
            wp_enqueue_script(
                'eddcdp-template-' . $template_name,
                $template_url . 'script.js',
                array('jquery'),
                filemtime($js_file),
                true
            );
            
            // Localize script with data
            wp_localize_script('eddcdp-template-' . $template_name, 'eddcdp_template', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('eddcdp_template_nonce'),
                'template_name' => $template_name,
                'template_url' => $template_url
            ));
        }
        
        $this->assets_enqueued = true;
    }
    
    /**
     * Maybe enqueue template assets (WordPress hook)
     */
    public function maybe_enqueue_template_assets() {
        // Only enqueue if we're on a page that might use the dashboard
        if (is_page() || is_single() || has_shortcode(get_post()->post_content ?? '', 'edd_customer_dashboard_pro')) {
            $this->enqueue_template_assets();
        }
    }
    
    /**
     * Output template-specific styles in head
     */
    public function output_template_styles() {
        if (!$this->assets_enqueued) {
            return;
        }
        
        $template_info = $this->get_template_info($this->active_template);
        
        if ($template_info && isset($template_info['custom_css'])) {
            echo '<style id="eddcdp-template-custom-css">' . wp_strip_all_tags($template_info['custom_css']) . '</style>';
        }
    }
    
    /**
     * Get template screenshot URL
     */
    public function get_template_screenshot($template_name) {
        $template_dir = $this->get_template_dir($template_name);
        $template_url = $this->get_template_url($template_name);
        
        $screenshot_files = ['screenshot.png', 'screenshot.jpg', 'screenshot.jpeg'];
        
        foreach ($screenshot_files as $screenshot_file) {
            $screenshot_path = $template_dir . $screenshot_file;
            if (file_exists($screenshot_path)) {
                return $template_url . $screenshot_file . '?v=' . filemtime($screenshot_path);
            }
        }
        
        return false;
    }
    
    /**
     * Validate template structure
     */
    public function validate_template($template_name) {
        if (!$this->template_exists($template_name)) {
            return ['errors' => [__('Template does not exist.', 'edd-customer-dashboard-pro')]];
        }
        
        $template_dir = $this->get_template_dir($template_name);
        $errors = [];
        $warnings = [];
        
        // Check required files
        $required_files = ['dashboard.php'];
        foreach ($required_files as $file) {
            if (!file_exists($template_dir . $file)) {
                $errors[] = sprintf(__('Required file missing: %s', 'edd-customer-dashboard-pro'), $file);
            }
        }
        
        // Check optional files
        $optional_files = ['style.css', 'script.js', 'template.json', 'screenshot.png'];
        foreach ($optional_files as $file) {
            if (!file_exists($template_dir . $file)) {
                $warnings[] = sprintf(__('Optional file missing: %s', 'edd-customer-dashboard-pro'), $file);
            }
        }
        
        return ['errors' => $errors, 'warnings' => $warnings];
    }
    
    /**
     * Ensure template data has all required properties
     */
    private function ensure_complete_data($template_data) {
        // Use the template data helper if available
        if (class_exists('EDDCDP_Template_Data_Helper')) {
            return EDDCDP_Template_Data_Helper::ensure_complete_data($template_data);
        }
        
        // Basic fallback
        $template_data = is_array($template_data) ? $template_data : [];
        
        // Ensure user is set
        if (!isset($template_data['user']) || !$template_data['user']) {
            $template_data['user'] = wp_get_current_user();
        }
        
        // Ensure settings are set
        if (!isset($template_data['settings'])) {
            $template_data['settings'] = get_option('eddcdp_settings', []);
        }
        
        // Ensure view_mode is set
        if (!isset($template_data['view_mode'])) {
            $template_data['view_mode'] = 'dashboard';
        }
        
        return $template_data;
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