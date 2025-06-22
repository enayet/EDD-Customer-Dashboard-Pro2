<?php
/**
 * Modern Template Loader - No Backward Compatibility
 * 
 * Clean, efficient template loading with modern PHP practices
 */

if (!defined('ABSPATH')) {
    exit;
}

class EDDCDP_Template_Loader {
    
    private string $templates_dir;
    private array $available_templates = [];
    private string $active_template;
    
    public function __construct() {
        $this->templates_dir = EDDCDP_PLUGIN_DIR . 'templates/';
        $this->load_available_templates();
        $this->set_active_template();
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
     * Get current active template
     */
    public function get_active_template(): string {
        return $this->active_template;
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
     * Get all available templates
     */
    public function get_available_templates(): array {
        return $this->available_templates;
    }
    
    /**
     * Check if template exists
     */
    public function template_exists(string $template_name): bool {
        return isset($this->available_templates[$template_name]);
    }
    
    /**
     * Get template directory path
     */
    public function get_template_dir(?string $template_name = null): string {
        $template_name = $template_name ?? $this->active_template;
        
        if (!$this->template_exists($template_name)) {
            $template_name = 'default';
        }
        
        return $this->templates_dir . $template_name . '/';
    }
    
    /**
     * Get template URL
     */
    public function get_template_url(?string $template_name = null): string {
        $template_name = $template_name ?? $this->active_template;
        
        if (!$this->template_exists($template_name)) {
            $template_name = 'default';
        }
        
        return EDDCDP_PLUGIN_URL . 'templates/' . $template_name . '/';
    }
    
    /**
     * Load main template
     */
    public function load_template(?string $template_name = null, array $data = []): string {
        $template_name = $template_name ?? $this->active_template;
        
        if (!$this->template_exists($template_name)) {
            $template_name = 'default';
        }
        
        $template_file = $this->get_template_dir($template_name) . 'dashboard.php';
        
        if (!file_exists($template_file)) {
            return '<div class="eddcdp-error"><p>' . __('Template not found.', 'edd-customer-dashboard-pro') . '</p></div>';
        }
        
        // Ensure complete data
        $data = $this->ensure_complete_data($data);
        
        // Apply filters
        $data = apply_filters('eddcdp_template_data', $data, $template_name);
        
        // Extract data for template
        extract($data, EXTR_SKIP);
        
        ob_start();
        include $template_file;
        $output = ob_get_clean();
        
        return apply_filters('eddcdp_template_output', $output, $template_name, $data);
    }
    
    /**
     * Load template section
     */
    public function load_section(string $section_name, ?string $template_name = null, array $data = []): string {
        $template_name = $template_name ?? $this->active_template;
        
        if (!$this->template_exists($template_name)) {
            $template_name = 'default';
        }
        
        $section_file = $this->get_template_dir($template_name) . 'sections/' . $section_name . '.php';
        
        if (!file_exists($section_file)) {
            return '<div class="eddcdp-error"><p>' . 
                   sprintf(__('Section "%s" not found.', 'edd-customer-dashboard-pro'), esc_html($section_name)) . 
                   '</p></div>';
        }
        
        // Ensure complete data
        $data = $this->ensure_complete_data($data);
        
        // Apply filters
        $data = apply_filters('eddcdp_section_data', $data, $section_name, $template_name);
        
        // Extract data for template
        extract($data, EXTR_SKIP);
        
        ob_start();
        include $section_file;
        $output = ob_get_clean();
        
        return apply_filters('eddcdp_section_output', $output, $section_name, $template_name, $data);
    }
    
    /**
     * Ensure template data has all required properties
     */
    private function ensure_complete_data(array $data): array {
        // Ensure user is set
        if (!isset($data['user']) || !$data['user']) {
            $data['user'] = wp_get_current_user();
        }
        
        // Ensure customer is set
        if (!isset($data['customer']) || !$data['customer']) {
            $user = $data['user'];
            if ($user && $user->ID) {
                $data['customer'] = edd_get_customer_by('user_id', $user->ID);
            }
        }
        
        // Ensure settings are set
        if (!isset($data['settings'])) {
            $data['settings'] = get_option('eddcdp_settings', []);
        }
        
        // Ensure enabled_sections are set
        if (!isset($data['enabled_sections'])) {
            $settings = $data['settings'];
            $data['enabled_sections'] = is_array($settings['enabled_sections'] ?? null) 
                ? $settings['enabled_sections'] 
                : [];
        }
        
        // Ensure view_mode is set
        if (!isset($data['view_mode'])) {
            $data['view_mode'] = 'dashboard';
        }
        
        // Ensure payment data is set
        if (!isset($data['payment'])) {
            $data['payment'] = null;
        }
        
        if (!isset($data['payment_key'])) {
            $data['payment_key'] = '';
        }
        
        // Ensure customer stats
        if (!isset($data['customer_stats']) && $data['customer']) {
            $data['customer_stats'] = [
                'total_purchases' => $data['customer']->purchase_count ?? 0,
                'total_spent' => $data['customer']->purchase_value ?? 0,
                'download_count' => 0,
                'active_licenses' => 0,
                'wishlist_count' => 0
            ];
        }
        
        return $data;
    }
    
    /**
     * Enqueue template assets
     */
    public function enqueue_template_assets(?string $template_name = null): void {
        $template_name = $template_name ?? $this->active_template;
        
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
                [],
                filemtime($css_file)
            );
        }
        
        // Enqueue JS
        $js_file = $template_dir . 'script.js';
        if (file_exists($js_file)) {
            wp_enqueue_script(
                'eddcdp-template-' . $template_name,
                $template_url . 'script.js',
                ['jquery'],
                filemtime($js_file),
                true
            );
            
            // Localize script with AJAX data
            wp_localize_script('eddcdp-template-' . $template_name, 'eddcdp_ajax', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('eddcdp_nonce'),
                'text_domain' => 'edd-customer-dashboard-pro',
                'template' => $template_name,
                'user_id' => get_current_user_id(),
                'is_user_logged_in' => is_user_logged_in()
            ]);
        }
        
        // Load Alpine.js if template uses it
        $template_info = $this->get_template_info($template_name);
        if (isset($template_info['assets']['dependencies']['alpinejs'])) {
            wp_enqueue_script(
                'alpinejs',
                'https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js',
                [],
                '3.x.x',
                true
            );
            wp_script_add_data('alpinejs', 'defer', true);
        }
    }
    
    /**
     * Get template metadata
     */
    public function get_template_info(string $template_name): array|false {
        if (!$this->template_exists($template_name)) {
            return false;
        }
        
        return $this->available_templates[$template_name];
    }
    
    /**
     * Get template screenshot URL
     */
    public function get_template_screenshot(string $template_name): string|false {
        if (!$this->template_exists($template_name)) {
            return false;
        }
        
        $template_info = $this->get_template_info($template_name);
        $screenshot_file = $template_info['screenshot'] ?? 'screenshot.png';
        $screenshot_path = $this->get_template_dir($template_name) . $screenshot_file;
        
        if (file_exists($screenshot_path)) {
            return $this->get_template_url($template_name) . $screenshot_file . '?v=' . filemtime($screenshot_path);
        }
        
        return false;
    }
    
    /**
     * Switch active template
     */
    public function switch_template(string $template_name): bool {
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
     * Get template requirements
     */
    public function get_template_requirements(string $template_name): array {
        $template_info = $this->get_template_info($template_name);
        
        if (!$template_info || !isset($template_info['supports'])) {
            return [];
        }
        
        $requirements = [];
        $supports = $template_info['supports'];
        
        if ($supports['licenses'] ?? false) {
            $requirements['licensing'] = [
                'name' => __('EDD Software Licensing', 'edd-customer-dashboard-pro'),
                'met' => class_exists('EDD_Software_Licensing')
            ];
        }
        
        if ($supports['wishlist'] ?? false) {
            $requirements['wishlist'] = [
                'name' => __('EDD Wish Lists', 'edd-customer-dashboard-pro'),
                'met' => class_exists('EDD_Wish_Lists')
            ];
        }
        
        if ($supports['analytics'] ?? false) {
            $requirements['analytics'] = [
                'name' => __('Analytics Support', 'edd-customer-dashboard-pro'),
                'met' => true // Built-in feature
            ];
        }
        
        return $requirements;
    }
    
    /**
     * Check if template has all requirements met
     */
    public function template_requirements_met(string $template_name): bool {
        $requirements = $this->get_template_requirements($template_name);
        
        foreach ($requirements as $requirement) {
            if (!$requirement['met']) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Validate template structure
     */
    public function validate_template(string $template_name): array {
        $errors = [];
        $warnings = [];
        
        if (!$this->template_exists($template_name)) {
            $errors[] = __('Template does not exist', 'edd-customer-dashboard-pro');
            return ['errors' => $errors, 'warnings' => $warnings];
        }
        
        $template_dir = $this->get_template_dir($template_name);
        
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
        
        // Validate template.json if it exists
        $json_file = $template_dir . 'template.json';
        if (file_exists($json_file)) {
            $json_content = file_get_contents($json_file);
            $json_data = json_decode($json_content, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                $errors[] = __('Invalid template.json format', 'edd-customer-dashboard-pro');
            } elseif (!isset($json_data['name']) || !isset($json_data['version'])) {
                $warnings[] = __('template.json missing required fields (name, version)', 'edd-customer-dashboard-pro');
            }
        }
        
        return ['errors' => $errors, 'warnings' => $warnings];
    }
    
    /**
     * Get template performance metrics
     */
    public function get_template_metrics(string $template_name): array {
        if (!$this->template_exists($template_name)) {
            return [];
        }
        
        $template_dir = $this->get_template_dir($template_name);
        $metrics = [
            'files' => [],
            'total_size' => 0,
            'asset_count' => 0
        ];
        
        $files = ['dashboard.php', 'style.css', 'script.js'];
        
        foreach ($files as $file) {
            $file_path = $template_dir . $file;
            if (file_exists($file_path)) {
                $size = filesize($file_path);
                $metrics['files'][$file] = [
                    'size' => $size,
                    'size_formatted' => size_format($size),
                    'modified' => filemtime($file_path)
                ];
                $metrics['total_size'] += $size;
                $metrics['asset_count']++;
            }
        }
        
        $metrics['total_size_formatted'] = size_format($metrics['total_size']);
        
        return $metrics;
    }
    
    /**
     * Clear template cache
     */
    public function clear_template_cache(?string $template_name = null): void {
        if (class_exists('EDDCDP_Cache_Helper')) {
            EDDCDP_Cache_Helper::invalidate_template_cache($template_name);
        }
        
        // Clear WordPress object cache
        wp_cache_flush();
    }
    
    /**
     * Get template compatibility info
     */
    public function get_compatibility_info(string $template_name): array {
        $template_info = $this->get_template_info($template_name);
        
        if (!$template_info) {
            return [];
        }
        
        $compatibility = [];
        
        // Check PHP version
        $required_php = $template_info['requires']['php'] ?? '7.4';
        $compatibility['php'] = [
            'required' => $required_php,
            'current' => PHP_VERSION,
            'compatible' => version_compare(PHP_VERSION, $required_php, '>=')
        ];
        
        // Check WordPress version
        $required_wp = $template_info['requires']['wordpress'] ?? '5.0';
        $compatibility['wordpress'] = [
            'required' => $required_wp,
            'current' => get_bloginfo('version'),
            'compatible' => version_compare(get_bloginfo('version'), $required_wp, '>=')
        ];
        
        // Check EDD version
        $required_edd = $template_info['requires']['edd'] ?? '2.9';
        $current_edd = defined('EDD_VERSION') ? EDD_VERSION : '0.0.0';
        $compatibility['edd'] = [
            'required' => $required_edd,
            'current' => $current_edd,
            'compatible' => version_compare($current_edd, $required_edd, '>=')
        ];
        
        return $compatibility;
    }
}