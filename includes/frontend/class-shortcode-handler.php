<?php
/**
 * Shortcode Handler Component
 * 
 * Handles all shortcode registration and rendering logic
 */

if (!defined('ABSPATH')) {
    exit;
}

class EDDCDP_Shortcode_Handler implements EDDCDP_Component_Interface {
    
    private $template_manager;
    private $customer_data;
    private $edd_integration;
    
    /**
     * Initialize component
     */
    public function init() {
        $this->register_shortcodes();
        add_action('wp', array($this, 'maybe_replace_edd_shortcodes'), 15);
    }
    
    /**
     * Get component dependencies
     */
    public function get_dependencies() {
        return array(
            'EDDCDP_Template_Manager',
            'EDDCDP_Customer_Data',
            'EDDCDP_Edd_Integration'
        );
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
        return 30; // Load after data components
    }
    
    /**
     * Set dependencies
     */
    public function set_template_manager($template_manager) {
        $this->template_manager = $template_manager;
    }
    
    public function set_customer_data($customer_data) {
        $this->customer_data = $customer_data;
    }
    
    public function set_edd_integration($edd_integration) {
        $this->edd_integration = $edd_integration;
    }
    
    /**
     * Register all shortcodes
     */
    private function register_shortcodes() {
        add_shortcode('edd_customer_dashboard_pro', array($this, 'render_dashboard'));
        add_shortcode('eddcdp_dashboard', array($this, 'render_dashboard')); // Alternative
    }
    
    /**
     * Maybe replace EDD's default shortcodes
     */
    public function maybe_replace_edd_shortcodes() {
        $settings = get_option('eddcdp_settings', array());
        
        if (!isset($settings['replace_edd_pages']) || !$settings['replace_edd_pages']) {
            return;
        }
        
        $shortcodes_to_replace = array(
            'purchase_history' => array($this, 'render_dashboard'),
            'edd_profile_editor' => array($this, 'render_dashboard'),
            'download_history' => array($this, 'render_dashboard'),
            'edd_receipt' => array($this, 'render_receipt')
        );
        
        foreach ($shortcodes_to_replace as $shortcode => $callback) {
            remove_all_shortcodes($shortcode);
            add_shortcode($shortcode, $callback);
        }
    }
    
    /**
     * Render dashboard shortcode
     */
    public function render_dashboard($atts = array()) {
        // Parse attributes
        $atts = shortcode_atts(array(
            'template' => '',
            'sections' => '',
            'user_id' => 0,
            'show_stats' => 'yes',
            'show_navigation' => 'yes'
        ), $atts, 'edd_customer_dashboard_pro');
        
        // Check authentication
        if (!$this->is_user_authenticated($atts['user_id'])) {
            return $this->get_login_form();
        }
        
        // Get current user and customer
        $user = wp_get_current_user();
        $customer = $this->customer_data->get_customer($user->ID);
        
        if (!$customer || !$customer->id) {
            return $this->render_error(__('No customer data found.', 'edd-customer-dashboard-pro'));
        }
        
        // Prepare template data
        $template_data = $this->prepare_dashboard_data($user, $customer, $atts);
        
        // Add fullscreen button if enabled
        $output = $this->get_fullscreen_button();
        
        // Render template
        if ($this->template_manager) {
            do_action('eddcdp_dashboard_loaded', $template_data);
            $output .= $this->template_manager->render_template('dashboard', $template_data);
        } else {
            $output .= $this->render_error(__('Dashboard template not available.', 'edd-customer-dashboard-pro'));
        }
        
        return $output;
    }
    
    /**
     * Render receipt shortcode
     */
    public function render_receipt($atts = array()) {
        // Parse attributes
        $atts = shortcode_atts(array(
            'payment_key' => '',
            'template' => 'receipt'
        ), $atts, 'edd_receipt');
        
        // Check authentication
        if (!is_user_logged_in()) {
            return $this->get_login_form();
        }
        
        // Get payment key
        $payment_key = $this->get_payment_key($atts['payment_key']);
        if (empty($payment_key)) {
            return $this->render_error(__('Invalid payment key.', 'edd-customer-dashboard-pro'));
        }
        
        // Get payment
        $payment = new EDD_Payment($payment_key, true);
        if (!$payment->ID || !$this->edd_integration->can_view_receipt($payment_key)) {
            return $this->render_error(__('Access denied or payment not found.', 'edd-customer-dashboard-pro'));
        }
        
        // Prepare template data
        $user = wp_get_current_user();
        $customer = $this->customer_data->get_customer($user->ID);
        
        $template_data = array(
            'user' => $user,
            'customer' => $customer,
            'payment' => $payment,
            'payment_key' => $payment_key,
            'settings' => get_option('eddcdp_settings', array()),
            'enabled_sections' => $this->get_enabled_sections(),
            'view_mode' => 'receipt'
        );
        
        // Render template
        if ($this->template_manager) {
            do_action('eddcdp_receipt_loaded', $template_data);
            return $this->template_manager->render_template('receipt', $template_data);
        }
        
        return $this->render_error(__('Receipt template not available.', 'edd-customer-dashboard-pro'));
    }
    
    /**
     * Check if user is authenticated
     */
    private function is_user_authenticated($user_id = 0) {
        if (!is_user_logged_in()) {
            return false;
        }
        
        // If specific user ID is requested, check permission
        if ($user_id > 0) {
            $current_user = wp_get_current_user();
            
            // Allow if current user or admin
            return ($current_user->ID == $user_id) || current_user_can('manage_shop_settings');
        }
        
        return true;
    }
    
    /**
     * Prepare dashboard data
     */
    private function prepare_dashboard_data($user, $customer, $atts) {
        // Get payment data if in receipt mode
        $payment_data = $this->get_payment_data();
        
        // Get enabled sections
        $enabled_sections = $this->get_enabled_sections($atts['sections']);
        
        return array(
            'user' => $user,
            'customer' => $customer,
            'payment' => $payment_data['payment'],
            'payment_key' => $payment_data['payment_key'],
            'settings' => get_option('eddcdp_settings', array()),
            'enabled_sections' => $enabled_sections,
            'view_mode' => $payment_data['view_mode'],
            'show_stats' => $atts['show_stats'] === 'yes',
            'show_navigation' => $atts['show_navigation'] === 'yes',
            'customer_stats' => $this->customer_data->get_customer_stats($user->ID),
            'template_atts' => $atts
        );
    }
    
    /**
     * Get payment data for receipt mode
     */
    private function get_payment_data() {
        $payment_key = $this->get_payment_key();
        $payment = null;
        $view_mode = 'dashboard';
        
        if (!empty($payment_key)) {
            $payment = new EDD_Payment($payment_key, true);
            
            if ($payment->ID && $this->edd_integration->can_view_receipt($payment_key)) {
                $view_mode = 'receipt';
            }
        }
        
        return array(
            'payment_key' => $payment_key,
            'payment' => $payment,
            'view_mode' => $view_mode
        );
    }
    
    /**
     * Get payment key from various sources
     */
    private function get_payment_key($override_key = '') {
        if (!empty($override_key)) {
            return sanitize_text_field($override_key);
        }
        
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if (isset($_GET['payment_key'])) {
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            return sanitize_text_field(wp_unslash($_GET['payment_key']));
        }
        
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if (isset($_GET['purchase_key'])) {
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            return sanitize_text_field(wp_unslash($_GET['purchase_key']));
        }
        
        return '';
    }
    
    /**
     * Get enabled sections
     */
    private function get_enabled_sections($sections_override = '') {
        // If sections are specified in shortcode, use those
        if (!empty($sections_override)) {
            $sections = array_map('trim', explode(',', $sections_override));
            $enabled = array();
            
            foreach ($sections as $section) {
                $enabled[$section] = true;
            }
            
            return $enabled;
        }
        
        // Otherwise use settings
        $settings = get_option('eddcdp_settings', array());
        return isset($settings['enabled_sections']) && is_array($settings['enabled_sections']) 
            ? $settings['enabled_sections'] 
            : array();
    }
    
    /**
     * Get login form
     */
    private function get_login_form() {
        return $this->edd_integration->get_login_form();
    }
    
    /**
     * Get fullscreen button if enabled
     */
    private function get_fullscreen_button() {
        $settings = get_option('eddcdp_settings', array());
        
        if (!isset($settings['fullscreen_mode']) || !$settings['fullscreen_mode']) {
            return '';
        }
        
        $fullscreen_url = add_query_arg('eddcdp_fullscreen', '1');
        
        // Preserve current parameters
        $payment_key = $this->get_payment_key();
        if (!empty($payment_key)) {
            $fullscreen_url = add_query_arg('payment_key', $payment_key, $fullscreen_url);
        }
        
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if (isset($_GET['view']) && $_GET['view'] === 'invoice') {
            $fullscreen_url = add_query_arg('view', 'invoice', $fullscreen_url);
        }
        
        return sprintf(
            '<div class="eddcdp-fullscreen-toggle" style="position: fixed; top: 20px; right: 20px; z-index: 999;">
                <a href="%s" class="eddcdp-btn eddcdp-btn-primary">
                    🔍 %s
                </a>
            </div>',
            esc_url($fullscreen_url),
            esc_html__('Full Screen', 'edd-customer-dashboard-pro')
        );
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