<?php
/**
 * Asset Manager Component
 * 
 * Handles CSS and JavaScript loading
 */

if (!defined('ABSPATH')) {
    exit;
}

class EDDCDP_Asset_Manager implements EDDCDP_Component_Interface {
    
    /**
     * Initialize component
     */
    public function init() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
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
        return true; // Always load asset manager
    }
    
    /**
     * Get component priority
     */
    public function get_priority() {
        return 15; // Load early
    }
    
    /**
     * Enqueue frontend assets
     */
    public function enqueue_frontend_assets() {
        if (!is_user_logged_in()) {
            return;
        }
        
        // Enqueue common frontend styles
        wp_enqueue_style(
            'eddcdp-frontend',
            EDDCDP_PLUGIN_URL . 'assets/frontend.css',
            array(),
            EDDCDP_VERSION
        );
        
        // Enqueue common frontend scripts
        wp_enqueue_script(
            'eddcdp-frontend',
            EDDCDP_PLUGIN_URL . 'assets/frontend.js',
            array('jquery'),
            EDDCDP_VERSION,
            true
        );
        
        // Localize script
        wp_localize_script('eddcdp-frontend', 'eddcdp_frontend', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('eddcdp_frontend_nonce'),
            'text_domain' => 'edd-customer-dashboard-pro'
        ));
    }
    

    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        // Only load on our admin pages
        if (strpos($hook, 'eddcdp') === false) {
            return;
        }
        
        wp_enqueue_style(
            'eddcdp-admin',
            EDDCDP_PLUGIN_URL . 'assets/admin.css',
            array(),
            EDDCDP_VERSION
        );
        
        wp_enqueue_script(
            'eddcdp-admin',
            EDDCDP_PLUGIN_URL . 'assets/admin.js',
            array('jquery'),
            EDDCDP_VERSION,
            true
        );
        
        // Localize admin script
        wp_localize_script('eddcdp-admin', 'eddcdp_admin', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('eddcdp_admin_nonce'),
            'text_domain' => 'edd-customer-dashboard-pro'
        ));
    }
    
    /**
     * Enqueue specific template assets
     */
    public function enqueue_template_assets($template_name = null) {
        $settings = get_option('eddcdp_settings', array());
        $active_template = isset($settings['active_template']) ? $settings['active_template'] : 'default';
        
        if ($template_name) {
            $active_template = $template_name;
        }
        
        $template_dir = EDDCDP_PLUGIN_DIR . 'templates/' . $active_template . '/';
        $template_url = EDDCDP_PLUGIN_URL . 'templates/' . $active_template . '/';
        
        // Enqueue template CSS
        $css_file = $template_dir . 'style.css';
        if (file_exists($css_file)) {
            wp_enqueue_style(
                'eddcdp-template-' . $active_template,
                $template_url . 'style.css',
                array(),
                filemtime($css_file)
            );
        }
        
        // Enqueue template JS
        $js_file = $template_dir . 'script.js';
        if (file_exists($js_file)) {
            wp_enqueue_script(
                'eddcdp-template-' . $active_template,
                $template_url . 'script.js',
                array('jquery'),
                filemtime($js_file),
                true
            );
            
            // Localize template script
            wp_localize_script('eddcdp-template-' . $active_template, 'eddcdp_template', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('eddcdp_template_nonce'),
                'template' => $active_template,
                'text_domain' => 'edd-customer-dashboard-pro'
            ));
        }
    }
    
    /**
     * Enqueue fullscreen assets
     */
    public function enqueue_fullscreen_assets() {
        wp_enqueue_style(
            'eddcdp-fullscreen',
            EDDCDP_PLUGIN_URL . 'assets/fullscreen.css',
            array(),
            EDDCDP_VERSION
        );
        
        wp_enqueue_script(
            'eddcdp-fullscreen',
            EDDCDP_PLUGIN_URL . 'assets/fullscreen.js',
            array('jquery'),
            EDDCDP_VERSION,
            true
        );
    }
}