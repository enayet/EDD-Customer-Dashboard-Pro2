<?php
/**
 * EDD Integration Component
 * 
 * Central hub for all EDD-related functionality and third-party add-on integrations
 */

if (!defined('ABSPATH')) {
    exit;
}

class EDDCDP_Edd_Integration implements EDDCDP_Component_Interface {
    
    private $licensing_integration;
    private $wishlist_integration;
    
    /**
     * Initialize component
     */
    public function init() {
        // Initialize sub-integrations with proper file loading
        if ($this->is_licensing_active()) {
            $licensing_file = EDDCDP_PLUGIN_DIR . 'includes/integrations/class-licensing-integration.php';
            if (file_exists($licensing_file)) {
                require_once $licensing_file;
                $this->licensing_integration = new EDDCDP_Licensing_Integration();
            }
        }
        
        if ($this->is_wishlist_active()) {
            $wishlist_file = EDDCDP_PLUGIN_DIR . 'includes/integrations/class-wishlist-integration.php';
            if (file_exists($wishlist_file)) {
                require_once $wishlist_file;
                $this->wishlist_integration = new EDDCDP_Wishlist_Integration();
            }
        }
    }
    
    /**
     * Get component dependencies
     */
    public function get_dependencies() {
        return array(); // Core integration, no dependencies
    }
    
    /**
     * Check if component should load
     */
    public function should_load() {
        return class_exists('Easy_Digital_Downloads');
    }
    
    /**
     * Get component priority
     */
    public function get_priority() {
        return 10; // Load early as other components depend on this
    }
    
    /**
     * Check if EDD Software Licensing is active
     */
    public function is_licensing_active() {
        return class_exists('EDD_Software_Licensing');
    }
    
    /**
     * Check if EDD Wish Lists is active
     */
    public function is_wishlist_active() {
        return class_exists('EDD_Wish_Lists');
    }
    
    /**
     * Get active licenses count for user
     */
    public function get_active_licenses_count($user_id) {
        if (!$this->licensing_integration) {
            return 0;
        }
        
        return $this->licensing_integration->get_active_licenses_count($user_id);
    }
    
    /**
     * Get customer licenses
     */
    public function get_customer_licenses($user_id) {
        if (!$this->licensing_integration) {
            return array();
        }
        
        return $this->licensing_integration->get_customer_licenses($user_id);
    }
    
    /**
     * Get license status info
     */
    public function get_license_status_info($license) {
        if (!$this->licensing_integration) {
            return array(
                'status' => 'unknown',
                'label' => 'Unknown',
                'days_remaining' => 0,
                'is_expired' => true
            );
        }
        
        return $this->licensing_integration->get_license_status_info($license);
    }
    
    /**
     * Get license sites
     */
    public function get_license_sites($license_key) {
        if (!$this->licensing_integration) {
            return array();
        }
        
        return $this->licensing_integration->get_license_sites($license_key);
    }
    
    /**
     * Get wishlist count for user
     */
    public function get_wishlist_count($user_id) {
        if (!$this->wishlist_integration) {
            return 0;
        }
        
        return $this->wishlist_integration->get_wishlist_count($user_id);
    }
    
    /**
     * Get customer wishlist
     */
    public function get_customer_wishlist($user_id) {
        if (!$this->wishlist_integration) {
            return array();
        }
        
        return $this->wishlist_integration->get_customer_wishlist($user_id);
    }
    
    /**
     * Get EDD version info
     */
    public function get_edd_version() {
        return defined('EDD_VERSION') ? EDD_VERSION : '0.0.0';
    }
    
    /**
     * Check if EDD version meets minimum requirement
     */
    public function meets_minimum_version($required_version) {
        return version_compare($this->get_edd_version(), $required_version, '>=');
    }
    
    /**
     * Get available EDD add-ons
     */
    public function get_available_addons() {
        return array(
            'licensing' => array(
                'name' => 'Software Licensing',
                'active' => $this->is_licensing_active(),
                'class' => 'EDD_Software_Licensing'
            ),
            'wishlist' => array(
                'name' => 'Wish Lists',
                'active' => $this->is_wishlist_active(),
                'class' => 'EDD_Wish_Lists'
            ),
            'reviews' => array(
                'name' => 'Product Reviews',
                'active' => class_exists('EDD_Reviews'),
                'class' => 'EDD_Reviews'
            ),
            'recurring' => array(
                'name' => 'Recurring Payments',
                'active' => class_exists('EDD_Recurring'),
                'class' => 'EDD_Recurring'
            ),
            'commissions' => array(
                'name' => 'Commissions',
                'active' => class_exists('EDDC'),
                'class' => 'EDDC'
            )
        );
    }
    
    /**
     * Get EDD settings
     */
    public function get_edd_setting($key, $default = false) {
        return edd_get_option($key, $default);
    }
    
    /**
     * Get currency settings
     */
    public function get_currency_settings() {
        return array(
            'currency' => edd_get_currency(),
            'currency_position' => edd_get_option('currency_position', 'before'),
            'thousands_separator' => edd_get_option('thousands_separator', ','),
            'decimal_separator' => edd_get_option('decimal_separator', '.'),
            'decimals' => edd_get_option('decimals', 2)
        );
    }
    
    /**
     * Format currency using EDD settings
     */
    public function format_currency($amount) {
        return edd_currency_filter(edd_format_amount($amount));
    }
    
    /**
     * Get EDD pages URLs
     */
    public function get_edd_pages() {
        return array(
            'checkout' => edd_get_checkout_uri(),
            'success' => edd_get_success_page_uri(),
            'failure' => edd_get_failed_transaction_uri(),
            'purchase_history' => edd_get_option('purchase_history_page'),
            'login_redirect' => edd_get_option('login_redirect_page')
        );
    }
    
    /**
     * Get payment gateways
     */
    public function get_payment_gateways() {
        return edd_get_payment_gateways();
    }
    
    /**
     * Get download categories
     */
    public function get_download_categories() {
        return get_terms(array(
            'taxonomy' => 'download_category',
            'hide_empty' => false
        ));
    }
    
    /**
     * Get download tags
     */
    public function get_download_tags() {
        return get_terms(array(
            'taxonomy' => 'download_tag',
            'hide_empty' => false
        ));
    }
    
    /**
     * Check if user can view receipt
     */
    public function can_view_receipt($payment_key) {
        return edd_can_view_receipt($payment_key);
    }
    
    /**
     * Get receipt URL
     */
    public function get_receipt_url($payment_key) {
        return edd_get_success_page_uri('?payment_key=' . $payment_key);
    }
    
    /**
     * Get login form
     */
    public function get_login_form($args = array()) {
        if (function_exists('edd_login_form')) {
            return edd_login_form($args);
        }
        
        return '<p>' . __('Please log in to view your account.', 'edd-customer-dashboard-pro') . '</p>';
    }
    
    /**
     * Get registration form
     */
    public function get_registration_form($args = array()) {
        if (function_exists('edd_register_form')) {
            return edd_register_form($args);
        }
        
        return '<p>' . __('Registration is not available.', 'edd-customer-dashboard-pro') . '</p>';
    }
    
    /**
     * Check if downloads are taxable
     */
    public function is_tax_enabled() {
        return edd_use_taxes();
    }
    
    /**
     * Get tax settings
     */
    public function get_tax_settings() {
        return array(
            'enabled' => edd_use_taxes(),
            'rate' => edd_get_tax_rate(),
            'included' => edd_prices_include_tax(),
            'display_on_checkout' => edd_get_option('checkout_include_tax', false)
        );
    }
}