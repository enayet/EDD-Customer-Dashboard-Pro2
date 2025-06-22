<?php
/**
 * Admin Menu Component
 * 
 * Handles all admin menu creation and page routing
 */

if (!defined('ABSPATH')) {
    exit;
}

class EDDCDP_Admin_Menu implements EDDCDP_Component_Interface {
    
    /**
     * Initialize component
     */
    public function init() {
        add_action('admin_menu', array($this, 'add_admin_menu'), 20);
        add_action('admin_init', array($this, 'handle_admin_actions'));
        add_filter('plugin_action_links_' . plugin_basename(EDDCDP_PLUGIN_FILE), array($this, 'add_plugin_action_links'));
    }
    
    /**
     * Get component dependencies
     */
    public function get_dependencies() {
        return array();
    }
    
    /**
     * Check if component should load
     */
    public function should_load() {
        return is_admin(); // Only load in admin
    }
    
    /**
     * Get component priority
     */
    public function get_priority() {
        return 5; // Load early
    }
    
    /**
     * Add admin menu pages
     */
    public function add_admin_menu() {
        // Only show if user has capability to manage EDD
        if (!current_user_can('manage_shop_settings')) {
            return;
        }
        
        // Add submenu under Downloads
        add_submenu_page(
            'edit.php?post_type=download',           // Parent slug
            __('Dashboard Pro', 'edd-customer-dashboard-pro'),    // Page title
            __('Dashboard Pro', 'edd-customer-dashboard-pro'),    // Menu title
            'manage_shop_settings',                  // Capability
            'eddcdp-settings',                      // Menu slug
            array($this, 'render_settings_page')    // Callback
        );
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        // Handle form submissions
        if (isset($_POST['eddcdp_save_settings'])) {
            $this->save_settings();
        }
        
        // Handle template activation
        if (isset($_GET['activate_template']) && wp_verify_nonce($_GET['_wpnonce'], 'activate_template_' . $_GET['activate_template'])) {
            $this->activate_template(sanitize_text_field($_GET['activate_template']));
        }
        
        // Include the settings page view
        $settings_page = EDDCDP_PLUGIN_DIR . 'includes/admin/views/settings-page.php';
        if (file_exists($settings_page)) {
            include $settings_page;
        } else {
            $this->render_fallback_settings_page();
        }
    }
    
    /**
     * Handle admin actions
     */
    public function handle_admin_actions() {
        // Handle AJAX and other admin actions
        if (isset($_GET['eddcdp_action'])) {
            $action = sanitize_text_field($_GET['eddcdp_action']);
            
            switch ($action) {
                case 'flush_cache':
                    if (wp_verify_nonce($_GET['_wpnonce'], 'eddcdp_flush_cache')) {
                        $this->flush_cache();
                    }
                    break;
            }
        }
    }
    
    /**
     * Save settings
     */
    private function save_settings() {
        // Verify nonce
        if (!isset($_POST['eddcdp_settings_nonce']) || 
            !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['eddcdp_settings_nonce'])), 'eddcdp_save_settings')) {
            wp_die(__('Security verification failed.', 'edd-customer-dashboard-pro'));
        }
        
        // Get current settings
        $settings = get_option('eddcdp_settings', array());
        
        // Update replace EDD pages setting
        $settings['replace_edd_pages'] = isset($_POST['replace_edd_pages']) ? true : false;
        
        // Update fullscreen mode setting
        $settings['fullscreen_mode'] = isset($_POST['fullscreen_mode']) ? true : false;
        
        // Update enabled sections
        $enabled_sections = array();
        if (isset($_POST['enabled_sections']) && is_array($_POST['enabled_sections'])) {
            foreach ($_POST['enabled_sections'] as $section => $value) {
                $section_key = sanitize_text_field($section);
                $enabled_sections[$section_key] = true;
            }
        }
        $settings['enabled_sections'] = $enabled_sections;
        
        // Save settings
        $updated = update_option('eddcdp_settings', $settings);
        
        if ($updated) {
            // Clear cache
            if (class_exists('EDDCDP_Cache_Helper')) {
                EDDCDP_Cache_Helper::flush_all();
            }
            
            // Show success message
            add_action('admin_notices', function() {
                echo '<div class="notice notice-success is-dismissible">';
                echo '<p>' . esc_html__('Settings saved successfully!', 'edd-customer-dashboard-pro') . '</p>';
                echo '</div>';
            });
        } else {
            // Show error message
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error is-dismissible">';
                echo '<p>' . esc_html__('Failed to save settings.', 'edd-customer-dashboard-pro') . '</p>';
                echo '</div>';
            });
        }
    }
    
    /**
     * Activate template
     */
    private function activate_template($template_name) {
        // Get template manager
        $template_manager = eddcdp()->component_manager->get_component('EDDCDP_Template_Manager');
        
        if ($template_manager && $template_manager->switch_template($template_name)) {
            add_action('admin_notices', function() use ($template_name) {
                echo '<div class="notice notice-success is-dismissible">';
                echo '<p>' . sprintf(esc_html__('Template "%s" activated successfully!', 'edd-customer-dashboard-pro'), esc_html($template_name)) . '</p>';
                echo '</div>';
            });
        } else {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error is-dismissible">';
                echo '<p>' . esc_html__('Failed to activate template.', 'edd-customer-dashboard-pro') . '</p>';
                echo '</div>';
            });
        }
    }
    
    /**
     * Flush cache
     */
    private function flush_cache() {
        if (class_exists('EDDCDP_Cache_Helper')) {
            EDDCDP_Cache_Helper::flush_all();
        }
        
        wp_cache_flush();
        
        add_action('admin_notices', function() {
            echo '<div class="notice notice-success is-dismissible">';
            echo '<p>' . esc_html__('Cache cleared successfully!', 'edd-customer-dashboard-pro') . '</p>';
            echo '</div>';
        });
    }
    
    /**
     * Add plugin action links
     */
    public function add_plugin_action_links($links) {
        $settings_link = '<a href="' . esc_url(admin_url('edit.php?post_type=download&page=eddcdp-settings')) . '">' . 
                        esc_html__('Settings', 'edd-customer-dashboard-pro') . '</a>';
        
        array_unshift($links, $settings_link);
        return $links;
    }
    
    /**
     * Fallback settings page if view file is missing
     */
    private function render_fallback_settings_page() {
        echo '<div class="wrap">';
        echo '<h1>' . esc_html__('EDD Customer Dashboard Pro Settings', 'edd-customer-dashboard-pro') . '</h1>';
        echo '<div class="notice notice-warning">';
        echo '<p>' . esc_html__('Settings page template not found. Please check your plugin installation.', 'edd-customer-dashboard-pro') . '</p>';
        echo '</div>';
        echo '</div>';
    }
}