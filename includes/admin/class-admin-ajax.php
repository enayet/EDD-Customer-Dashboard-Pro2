<?php
/**
 * Admin AJAX Handler
 * 
 * Handles all AJAX requests for admin and frontend
 */

if (!defined('ABSPATH')) {
    exit;
}

class EDDCDP_Admin_Ajax implements EDDCDP_Component_Interface {
    
    /**
     * Initialize component
     */
    public function init() {
        // Admin AJAX handlers
        add_action('wp_ajax_eddcdp_update_billing', array($this, 'update_billing_info'));
        add_action('wp_ajax_eddcdp_generate_pdf', array($this, 'generate_pdf_invoice'));
        add_action('wp_ajax_eddcdp_test_template', array($this, 'test_template'));
        add_action('wp_ajax_eddcdp_get_analytics', array($this, 'get_analytics_data'));
        
        // Frontend AJAX handlers (for logged-in users)
        add_action('wp_ajax_eddcdp_update_billing', array($this, 'update_billing_info'));
        add_action('wp_ajax_eddcdp_generate_pdf', array($this, 'generate_pdf_invoice'));
        add_action('wp_ajax_eddcdp_get_analytics', array($this, 'get_analytics_data'));
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
        return true;
    }
    
    /**
     * Get component priority
     */
    public function get_priority() {
        return 35;
    }
    
    /**
     * Update billing information
     */
    public function update_billing_info() {
        if (!isset($_POST['eddcdp_billing_nonce']) || 
            !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['eddcdp_billing_nonce'])), 'eddcdp_update_billing')) {
            wp_send_json_error(__('Security verification failed.', 'edd-customer-dashboard-pro'));
        }
        
        if (!is_user_logged_in()) {
            wp_send_json_error(__('Please log in first.', 'edd-customer-dashboard-pro'));
        }
        
        if (!isset($_POST['payment_id'])) {
            wp_send_json_error(__('Payment ID parameter missing.', 'edd-customer-dashboard-pro'));
        }
        
        $payment_id = absint($_POST['payment_id']);
        $payment = edd_get_payment($payment_id);
        
        if (!$payment) {
            wp_send_json_error(__('Payment not found.', 'edd-customer-dashboard-pro'));
        }
        
        // Verify ownership
        $current_user = wp_get_current_user();
        $customer = edd_get_customer_by('user_id', $current_user->ID);
        
        if (!$customer || $payment->customer_id != $customer->id) {
            wp_send_json_error(__('Access denied.', 'edd-customer-dashboard-pro'));
        }
        
        // Sanitize and prepare update data
        $user_info = $this->sanitize_billing_data($_POST);
        
        // Update payment meta
        $current_user_info = edd_get_payment_meta_user_info($payment_id);
        $updated_user_info = array_merge($current_user_info, $user_info);
        
        edd_update_payment_meta($payment_id, '_edd_payment_user_info', $updated_user_info);
        
        // Update payment email if changed
        if (isset($user_info['email'])) {
            edd_update_payment_meta($payment_id, '_edd_payment_user_email', $user_info['email']);
            
            $update_data = array(
                'ID' => $payment_id,
                'post_title' => $user_info['email']
            );
            wp_update_post($update_data);
        }
        
        // Update customer record
        if ($customer && !empty($user_info)) {
            $this->update_customer_record($customer, $user_info);
        }
        
        // Log the update
        edd_insert_payment_note($payment_id, __('Billing information updated by customer via dashboard.', 'edd-customer-dashboard-pro'));
        
        wp_send_json_success(__('Billing information updated successfully!', 'edd-customer-dashboard-pro'));
    }
    
    /**
     * Generate PDF invoice
     */
    public function generate_pdf_invoice() {
        if (!isset($_POST['nonce']) || 
            !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'eddcdp_nonce')) {
            wp_send_json_error(__('Security verification failed.', 'edd-customer-dashboard-pro'));
        }
        
        if (!is_user_logged_in()) {
            wp_send_json_error(__('Please log in first.', 'edd-customer-dashboard-pro'));
        }
        
        if (!isset($_POST['payment_key'])) {
            wp_send_json_error(__('Payment key parameter missing.', 'edd-customer-dashboard-pro'));
        }
        
        $payment_key = sanitize_text_field(wp_unslash($_POST['payment_key']));
        
        if (empty($payment_key)) {
            wp_send_json_error(__('Invalid payment key.', 'edd-customer-dashboard-pro'));
        }
        
        $payment = edd_get_payment_by('key', $payment_key);
        
        if (!$payment || !edd_can_view_receipt($payment_key)) {
            wp_send_json_error(__('Access denied or payment not found.', 'edd-customer-dashboard-pro'));
        }
        
        // Check for PDF invoice plugins
        $pdf_url = $this->get_pdf_invoice_url($payment);
        
        if ($pdf_url) {
            wp_send_json_success(array(
                'pdf_url' => $pdf_url,
                'message' => __('PDF invoice generated successfully.', 'edd-customer-dashboard-pro')
            ));
        } else {
            wp_send_json_success(array(
                'message' => __('Please use the print function in your browser to generate a PDF.', 'edd-customer-dashboard-pro'),
                'print_url' => add_query_arg('print', '1')
            ));
        }
    }
    
    /**
     * Test template functionality
     */
    public function test_template() {
        if (!current_user_can('manage_shop_settings')) {
            wp_send_json_error(__('Permission denied.', 'edd-customer-dashboard-pro'));
        }
        
        if (!isset($_POST['nonce']) || 
            !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'eddcdp_admin_nonce')) {
            wp_send_json_error(__('Security verification failed.', 'edd-customer-dashboard-pro'));
        }
        
        if (!isset($_POST['template_name'])) {
            wp_send_json_error(__('Template name parameter missing.', 'edd-customer-dashboard-pro'));
        }
        
        $template_name = sanitize_text_field(wp_unslash($_POST['template_name']));
        
        // Get template loader
        $template_loader = eddcdp()->get_template_loader();
        
        if (!$template_loader) {
            wp_send_json_error(__('Template loader not available.', 'edd-customer-dashboard-pro'));
        }
        
        // Check if template exists
        if (!$template_loader->template_exists($template_name)) {
            wp_send_json_error(__('Template not found.', 'edd-customer-dashboard-pro'));
        }
        
        // Get template info
        $template_info = $template_loader->get_template_info($template_name);
        
        wp_send_json_success(array(
            'template_name' => $template_name,
            'template_info' => $template_info,
            'message' => sprintf(__('Template "%s" is working correctly.', 'edd-customer-dashboard-pro'), $template_name)
        ));
    }
    
    /**
     * Get analytics data via AJAX
     */
    public function get_analytics_data() {
        if (!isset($_POST['nonce']) || 
            !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'eddcdp_nonce')) {
            wp_send_json_error(__('Security verification failed.', 'edd-customer-dashboard-pro'));
        }
        
        if (!is_user_logged_in()) {
            wp_send_json_error(__('Please log in first.', 'edd-customer-dashboard-pro'));
        }
        
        $user = wp_get_current_user();
        $customer = edd_get_customer_by('user_id', $user->ID);
        
        if (!$customer) {
            wp_send_json_error(__('Customer not found.', 'edd-customer-dashboard-pro'));
        }
        
        // Get analytics component
        $analytics_data = eddcdp()->get_component('EDDCDP_Analytics_Data');
        
        if (!$analytics_data) {
            wp_send_json_error(__('Analytics not available.', 'edd-customer-dashboard-pro'));
        }
        
        $analytics = $analytics_data->get_customer_analytics($customer);
        
        wp_send_json_success(array(
            'analytics' => $analytics,
            'formatted' => array(
                'total_spent' => edd_currency_filter(edd_format_amount($analytics['total_spent'])),
                'avg_per_order' => edd_currency_filter(edd_format_amount($analytics['avg_per_order'])),
                'purchase_frequency' => sprintf(_n('%d day', '%d days', $analytics['purchase_frequency'], 'edd-customer-dashboard-pro'), $analytics['purchase_frequency'])
            )
        ));
    }
    
    /**
     * Sanitize billing data
     */
    private function sanitize_billing_data($post_data) {
        $user_info = array();
        
        if (isset($post_data['first_name'])) {
            $user_info['first_name'] = sanitize_text_field(wp_unslash($post_data['first_name']));
        }
        
        if (isset($post_data['last_name'])) {
            $user_info['last_name'] = sanitize_text_field(wp_unslash($post_data['last_name']));
        }
        
        if (isset($post_data['email'])) {
            $email = sanitize_email(wp_unslash($post_data['email']));
            if (is_email($email)) {
                $user_info['email'] = $email;
            }
        }
        
        // Handle address
        if (isset($post_data['address']) && is_array($post_data['address'])) {
            $address = array();
            $address_fields = array('line1', 'line2', 'city', 'state', 'zip', 'country');
            
            foreach ($address_fields as $field) {
                if (isset($post_data['address'][$field])) {
                    $address[$field] = sanitize_text_field(wp_unslash($post_data['address'][$field]));
                }
            }
            
            $user_info['address'] = $address;
        }
        
        return $user_info;
    }
    
    /**
     * Update customer record
     */
    private function update_customer_record($customer, $user_info) {
        $customer_update_data = array();
        
        if (isset($user_info['first_name'])) {
            $customer_update_data['name'] = trim($user_info['first_name'] . ' ' . ($user_info['last_name'] ?? ''));
        }
        
        if (isset($user_info['email'])) {
            $customer_update_data['email'] = $user_info['email'];
        }
        
        if (!empty($customer_update_data)) {
            $customer->update($customer_update_data);
        }
    }
    
    /**
     * Get PDF invoice URL
     */
    private function get_pdf_invoice_url($payment) {
        // Check for EDD PDF Invoices plugin
        if (function_exists('eddpdfi_get_pdf_invoice_url')) {
            return eddpdfi_get_pdf_invoice_url($payment->ID);
        }
        
        // Check for other PDF invoice plugins
        if (class_exists('EDD_PDF_Invoices')) {
            return add_query_arg(array(
                'edd_action' => 'generate_pdf_invoice',
                'payment_id' => $payment->ID,
                'payment_key' => $payment->key
            ), home_url());
        }
        
        return false;
    }
}