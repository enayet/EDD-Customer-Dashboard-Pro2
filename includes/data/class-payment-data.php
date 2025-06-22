<?php
/**
 * Payment Data Component
 * 
 * Handles all payment and receipt related data operations
 */

if (!defined('ABSPATH')) {
    exit;
}

class EDDCDP_Payment_Data implements EDDCDP_Component_Interface {
    
    /**
     * Initialize component
     */
    public function init() {
        add_action('wp_ajax_eddcdp_update_billing', array($this, 'ajax_update_billing'));
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
        return 25;
    }
    
    /**
     * Get payment by key
     */
    public function get_payment_by_key($payment_key) {
        if (empty($payment_key)) {
            return false;
        }
        
        return new EDD_Payment($payment_key, true);
    }
    
    /**
     * Get payment downloads with enhanced data
     */
    public function get_payment_downloads($payment) {
        if (!$payment instanceof EDD_Payment) {
            return array();
        }
        
        $downloads = $payment->get_downloads();
        $enhanced_downloads = array();
        
        foreach ($downloads as $download) {
            $download_obj = new EDD_Download($download['id']);
            
            $enhanced_downloads[] = array(
                'id' => $download['id'],
                'name' => $download_obj->get_name(),
                'price' => $this->get_download_price_from_payment($download, $payment->ID),
                'files' => $download_obj->get_files(),
                'can_download' => $this->can_download_file($payment, $download['id']),
                'download_url' => $this->get_download_url($payment->key, $payment->email, 0, $download['id']),
                'download_limit' => $download_obj->get_download_limit(),
                'downloads_remaining' => $this->get_downloads_remaining($download['id'], $payment->ID),
                'sku' => edd_get_download_sku($download['id']),
                'version' => get_post_meta($download['id'], '_edd_sl_version', true)
            );
        }
        
        return $enhanced_downloads;
    }
    
    /**
     * Get payment customer info
     */
    public function get_payment_customer_info($payment) {
        if (!$payment instanceof EDD_Payment) {
            return array();
        }
        
        $customer = $payment->get_customer();
        $user_info = $payment->get_user_info();
        
        return array(
            'customer' => $customer,
            'user_info' => $user_info,
            'email' => $payment->email,
            'address' => isset($user_info['address']) ? $user_info['address'] : array(),
            'first_name' => isset($user_info['first_name']) ? $user_info['first_name'] : '',
            'last_name' => isset($user_info['last_name']) ? $user_info['last_name'] : ''
        );
    }
    
    /**
     * Get payment totals breakdown
     */
    public function get_payment_totals($payment) {
        if (!$payment instanceof EDD_Payment) {
            return array();
        }
        
        $subtotal = edd_get_payment_subtotal($payment->ID);
        $tax = edd_get_payment_tax($payment->ID);
        $fees = edd_get_payment_fees($payment->ID);
        $discounts = $this->get_payment_discounts($payment);
        
        return array(
            'subtotal' => $subtotal,
            'tax' => $tax,
            'fees' => $fees,
            'discounts' => $discounts,
            'total' => $payment->total,
            'formatted_subtotal' => EDDCDP_Formatter::currency($subtotal),
            'formatted_tax' => EDDCDP_Formatter::currency($tax),
            'formatted_total' => EDDCDP_Formatter::currency($payment->total)
        );
    }
    
    /**
     * Get payment discounts
     */
    private function get_payment_discounts($payment) {
        $cart_discounts = edd_get_payment_meta($payment->ID, '_edd_payment_discount', true);
        $discount_amount = 0;
        
        if (!empty($cart_discounts)) {
            $subtotal = edd_get_payment_subtotal($payment->ID);
            $total_before_tax = $payment->total - edd_get_payment_tax($payment->ID);
            $discount_amount = $subtotal - $total_before_tax;
        }
        
        return array(
            'codes' => $cart_discounts,
            'amount' => max(0, $discount_amount),
            'formatted_amount' => EDDCDP_Formatter::currency($discount_amount)
        );
    }
    
    /**
     * Check if user can download file
     */
    private function can_download_file($payment, $download_id) {
        return $payment->is_complete() && edd_can_view_receipt($payment->key);
    }
    
    /**
     * Get download URL
     */
    private function get_download_url($payment_key, $email, $file_key, $download_id) {
        if (!edd_can_view_receipt($payment_key)) {
            return false;
        }
        
        return edd_get_download_file_url($payment_key, $email, $file_key, $download_id);
    }
    
    /**
     * Get downloads remaining
     */
    private function get_downloads_remaining($download_id, $payment_id) {
        if (function_exists('edd_get_download_limit_remaining')) {
            $remaining = edd_get_download_limit_remaining($download_id, $payment_id);
            return $remaining === false ? __('Unlimited', 'edd-customer-dashboard-pro') : $remaining;
        }
        
        $download = new EDD_Download($download_id);
        $download_limit = $download->get_download_limit();
        
        if (empty($download_limit) || $download_limit == 0) {
            return __('Unlimited', 'edd-customer-dashboard-pro');
        }
        
        $download_count = $this->get_file_download_count($download_id, $payment_id);
        return max(0, $download_limit - $download_count);
    }
    
    /**
     * Get file download count
     */
    private function get_file_download_count($download_id, $payment_id) {
        if (function_exists('edd_get_download_logs')) {
            $logs = edd_get_download_logs(array(
                'post_id' => $download_id,
                'meta_query' => array(
                    array(
                        'key' => '_edd_log_payment_id',
                        'value' => $payment_id,
                        'compare' => '='
                    )
                )
            ));
            
            return count($logs);
        }
        
        $download_count = get_post_meta($payment_id, '_edd_download_count_' . $download_id, true);
        return intval($download_count);
    }
    
    /**
     * Get download price from payment
     */
    private function get_download_price_from_payment($download, $payment_id) {
        $payment = new EDD_Payment($payment_id);
        $cart_details = $payment->get_cart_details();
        
        foreach ($cart_details as $item) {
            if ($item['id'] == $download['id']) {
                return isset($item['item_price']) ? floatval($item['item_price']) : 0;
            }
        }
        
        $download_obj = new EDD_Download($download['id']);
        return $download_obj->get_price();
    }
    
    /**
     * Get company address for invoices
     */
    public function get_company_address() {
        $address_parts = array();
        
        $company_name = edd_get_option('company_name', get_bloginfo('name'));
        $company_address = edd_get_option('company_address', '');
        $company_city = edd_get_option('company_city', '');
        $company_state = edd_get_option('company_state', '');
        $company_zip = edd_get_option('company_zip', '');
        $company_country = edd_get_option('company_country', '');
        
        if (!empty($company_address)) {
            $address_parts[] = $company_address;
        }
        
        $city_state_zip = array();
        if (!empty($company_city)) $city_state_zip[] = $company_city;
        if (!empty($company_state)) $city_state_zip[] = $company_state;
        if (!empty($company_zip)) $city_state_zip[] = $company_zip;
        
        if (!empty($city_state_zip)) {
            $address_parts[] = implode(', ', $city_state_zip);
        }
        
        if (!empty($company_country)) {
            $countries = edd_get_country_list();
            $country_name = isset($countries[$company_country]) ? $countries[$company_country] : $company_country;
            $address_parts[] = $country_name;
        }
        
        return implode('<br>', $address_parts);
    }
    
    /**
     * Get PDF invoice URL
     */
    public function get_pdf_invoice_url($payment) {
        if (function_exists('eddpdfi_get_pdf_invoice_url')) {
            return eddpdfi_get_pdf_invoice_url($payment->ID);
        }
        
        if (class_exists('EDD_PDF_Invoices')) {
            return add_query_arg(array(
                'edd_action' => 'generate_pdf_invoice',
                'payment_id' => $payment->ID,
                'payment_key' => $payment->key
            ), home_url());
        }
        
        return add_query_arg('print', '1');
    }
    
    /**
     * AJAX: Update billing information
     */
    public function ajax_update_billing() {
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
        
        // Update user info
        $user_info = array();
        
        if (isset($_POST['first_name'])) {
            $user_info['first_name'] = sanitize_text_field(wp_unslash($_POST['first_name']));
        }
        
        if (isset($_POST['last_name'])) {
            $user_info['last_name'] = sanitize_text_field(wp_unslash($_POST['last_name']));
        }
        
        if (isset($_POST['email'])) {
            $email = sanitize_email(wp_unslash($_POST['email']));
            if (is_email($email)) {
                $user_info['email'] = $email;
            }
        }
        
        if (isset($_POST['address']) && is_array($_POST['address'])) {
            $address = array();
            $address_fields = array('line1', 'line2', 'city', 'state', 'zip', 'country');
            
            foreach ($address_fields as $field) {
                if (isset($_POST['address'][$field])) {
                    $address[$field] = sanitize_text_field(wp_unslash($_POST['address'][$field]));
                }
            }
            
            $user_info['address'] = $address;
        }
        
        // Update payment meta
        $current_user_info = edd_get_payment_meta_user_info($payment_id);
        $updated_user_info = array_merge($current_user_info, $user_info);
        
        edd_update_payment_meta($payment_id, '_edd_payment_user_info', $updated_user_info);
        
        if (isset($user_info['email'])) {
            edd_update_payment_meta($payment_id, '_edd_payment_user_email', $user_info['email']);
            
            $update_data = array(
                'ID' => $payment_id,
                'post_title' => $user_info['email']
            );
            wp_update_post($update_data);
        }
        
        // Update customer record
        if ($customer) {
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
        
        edd_insert_payment_note($payment_id, __('Billing information updated by customer via dashboard.', 'edd-customer-dashboard-pro'));
        
        wp_send_json_success(__('Billing information updated successfully!', 'edd-customer-dashboard-pro'));
    }
}