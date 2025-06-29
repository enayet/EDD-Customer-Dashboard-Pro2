<?php
/**
 * Utility Functions for EDD Customer Dashboard Pro
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get license count for an order
 */
function eddcdp_get_order_license_count($order_id) {
    if (!class_exists('EDD_Software_Licensing') || !function_exists('edd_software_licensing')) {
        return 0;
    }
    
    $order = edd_get_order($order_id);
    if (!$order) {
        return 0;
    }
    
    $license_count = 0;
    $order_items = $order->get_items();
    
    foreach ($order_items as $item) {
        $licenses = edd_software_licensing()->get_licenses_of_purchase($order_id, $item->product_id);
        if ($licenses) {
            $license_count += count($licenses);
        }
    }
    
    return $license_count;
}

/**
 * Check if order has any licenses
 */
function eddcdp_order_has_licenses($order_id) {
    return eddcdp_get_order_license_count($order_id) > 0;
}

/**
 * Get customer's total license count
 */
function eddcdp_get_customer_license_count($customer_id = null) {
    if (!class_exists('EDD_Software_Licensing') || !function_exists('edd_software_licensing')) {
        return 0;
    }
    
    if (!$customer_id) {
        $customer_id = get_current_user_id();
    }
    
    $licenses = edd_software_licensing()->licenses_db->get_licenses(array(
        'user_id' => $customer_id,
        'number' => 999999,
        'fields' => 'ids'
    ));
    
    return is_array($licenses) ? count($licenses) : 0;
}

/**
 * Get active license count for customer
 */
function eddcdp_get_customer_active_license_count($customer_id = null) {
    if (!class_exists('EDD_Software_Licensing') || !function_exists('edd_software_licensing')) {
        return 0;
    }
    
    if (!$customer_id) {
        $customer_id = get_current_user_id();
    }
    
    $licenses = edd_software_licensing()->licenses_db->get_licenses(array(
        'user_id' => $customer_id,
        'status' => 'active',
        'number' => 999999,
        'fields' => 'ids'
    ));
    
    return is_array($licenses) ? count($licenses) : 0;
}

/**
 * Check if EDD Invoices is active and get invoice URL
 */
function eddcdp_get_invoice_url($order_id) {
    if (!function_exists('edd_invoices_get_invoice_url')) {
        return false;
    }
    
    return edd_invoices_get_invoice_url($order_id);
}

/**
 * Get download URL for order item
 */
function eddcdp_get_order_download_url($order_id, $download_id, $file_id = 0) {
    $order = edd_get_order($order_id);
    if (!$order || $order->status !== 'complete') {
        return false;
    }
    
    $customer = edd_get_customer($order->customer_id);
    if (!$customer) {
        return false;
    }
    
    return edd_get_download_file_url($order->payment_key, $customer->email, $file_id, $download_id);
}

/**
 * Format license status for display
 */
function eddcdp_format_license_status($license) {
    $status = $license->status;
    $is_expired = false;
    
    if (!empty($license->expiration) && $license->expiration !== '0000-00-00 00:00:00') {
        $expiration_date = strtotime($license->expiration);
        $is_expired = ($expiration_date < time());
    }
    
    if ($status === 'active' && !$is_expired) {
        return array(
            'status' => 'active',
            'text' => __('Active', 'edd-customer-dashboard-pro'),
            'icon' => '✅',
            'class' => 'text-green-600'
        );
    } elseif ($is_expired) {
        return array(
            'status' => 'expired',
            'text' => __('Expired', 'edd-customer-dashboard-pro'),
            'icon' => '⏰',
            'class' => 'text-red-600'
        );
    } else {
        return array(
            'status' => 'inactive',
            'text' => __('Inactive', 'edd-customer-dashboard-pro'),
            'icon' => '❌',
            'class' => 'text-gray-600'
        );
    }
}

/**
 * Get license renewal URL
 */
function eddcdp_get_license_renewal_url($license_id) {
    $checkout_url = edd_get_checkout_uri();
    return add_query_arg(array(
        'edd_action' => 'purchase_renewal',
        'license_id' => $license_id
    ), $checkout_url);
}

/**
 * Sanitize and validate license site URL
 */
function eddcdp_sanitize_license_url($url) {
    // Remove www. prefix for consistency
    $url = preg_replace('/^https?:\/\/www\./', 'https://', $url);
    
    // Ensure URL has protocol
    if (!preg_match('/^https?:\/\//', $url)) {
        $url = 'https://' . $url;
    }
    
    // Remove trailing slash
    $url = rtrim($url, '/');
    
    // Validate URL
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return false;
    }
    
    return esc_url_raw($url);
}

/**
 * Get formatted activation count display
 */
function eddcdp_format_activation_count($activation_count, $activation_limit) {
    if ($activation_limit > 0) {
        return sprintf(
            __('%d of %d sites', 'edd-customer-dashboard-pro'),
            $activation_count,
            $activation_limit
        );
    } else {
        return sprintf(
            __('%d of unlimited sites', 'edd-customer-dashboard-pro'),
            $activation_count
        );
    }
}

/**
 * Check if user can manage license
 */
function eddcdp_user_can_manage_license($license_id, $user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    
    if (!class_exists('EDD_Software_Licensing')) {
        return false;
    }
    
    $license = edd_software_licensing()->get_license($license_id);
    
    return $license && $license->user_id == $user_id;
}

/**
 * Get license expiration status
 */
function eddcdp_get_license_expiration_status($license) {
    if (empty($license->expiration) || $license->expiration === '0000-00-00 00:00:00') {
        return array(
            'is_expired' => false,
            'expires_soon' => false,
            'days_until_expiry' => null,
            'status' => 'lifetime'
        );
    }
    
    $expiration_date = strtotime($license->expiration);
    $current_time = time();
    $days_until_expiry = ceil(($expiration_date - $current_time) / (60 * 60 * 24));
    
    return array(
        'is_expired' => $expiration_date < $current_time,
        'expires_soon' => $days_until_expiry <= 30 && $days_until_expiry > 0,
        'days_until_expiry' => $days_until_expiry,
        'status' => $expiration_date < $current_time ? 'expired' : ($days_until_expiry <= 30 ? 'expires_soon' : 'valid')
    );
}

/**
 * Get order summary for license management
 */
function eddcdp_get_order_license_summary($order_id) {
    if (!class_exists('EDD_Software_Licensing') || !function_exists('edd_software_licensing')) {
        return false;
    }
    
    $order = edd_get_order($order_id);
    if (!$order) {
        return false;
    }
    
    $order_items = $order->get_items();
    $summary = array(
        'total_licenses' => 0,
        'active_licenses' => 0,
        'expired_licenses' => 0,
        'products' => array()
    );
    
    foreach ($order_items as $item) {
        $licenses = edd_software_licensing()->get_licenses_of_purchase($order_id, $item->product_id);
        if ($licenses) {
            $product_summary = array(
                'product_name' => $item->product_name,
                'license_count' => count($licenses),
                'active_count' => 0,
                'expired_count' => 0
            );
            
            foreach ($licenses as $license) {
                $summary['total_licenses']++;
                $expiration_status = eddcdp_get_license_expiration_status($license);
                
                if ($license->status === 'active' && !$expiration_status['is_expired']) {
                    $summary['active_licenses']++;
                    $product_summary['active_count']++;
                } else {
                    $summary['expired_licenses']++;
                    $product_summary['expired_count']++;
                }
            }
            
            $summary['products'][$item->product_id] = $product_summary;
        }
    }
    
    return $summary;
}

/**
 * Check if license can be activated on more sites
 */
function eddcdp_can_activate_license_site($license) {
    if ($license->status !== 'active') {
        return false;
    }
    
    $expiration_status = eddcdp_get_license_expiration_status($license);
    if ($expiration_status['is_expired']) {
        return false;
    }
    
    if ($license->activation_limit > 0 && $license->activation_count >= $license->activation_limit) {
        return false;
    }
    
    return true;
}

/**
 * Get customer order statistics
 */
function eddcdp_get_customer_order_stats($customer_id = null) {
    if (!$customer_id) {
        $customer_id = get_current_user_id();
    }
    
    $customer = edd_get_customer_by('user_id', $customer_id);
    if (!$customer) {
        return false;
    }
    
    $orders = edd_get_orders(array(
        'customer' => $customer->id,
        'number' => 999999,
        'status' => 'complete'
    ));
    
    $stats = array(
        'total_orders' => count($orders),
        'total_spent' => $customer->purchase_value,
        'average_order_value' => count($orders) > 0 ? $customer->purchase_value / count($orders) : 0,
        'first_purchase' => null,
        'last_purchase' => null
    );
    
    if (!empty($orders)) {
        // Sort orders by date
        usort($orders, function($a, $b) {
            return strtotime($a->date_created) - strtotime($b->date_created);
        });
        
        $stats['first_purchase'] = reset($orders)->date_created;
        $stats['last_purchase'] = end($orders)->date_created;
    }
    
    return $stats;
}

/**
 * Get download count for customer
 */
function eddcdp_get_customer_download_count($customer_email = null) {
    if (!$customer_email) {
        $current_user = wp_get_current_user();
        $customer_email = $current_user->user_email;
    }
    
    $download_logs = edd_get_file_download_logs(array(
        'customer' => $customer_email,
        'number' => 999999,
        'fields' => 'ids'
    ));
    
    return is_array($download_logs) ? count($download_logs) : 0;
}

/**
 * Format price for display
 */
function eddcdp_format_price($amount) {
    return edd_currency_filter(edd_format_amount($amount));
}

/**
 * Get wishlist count for customer
 */
function eddcdp_get_customer_wishlist_count($customer_id = null) {
    if (!function_exists('edd_wl_get_wish_list') || !function_exists('edd_wl_get_wish_list_downloads')) {
        return 0;
    }
    
    if (!$customer_id) {
        $customer_id = get_current_user_id();
    }
    
    $wishlist = edd_wl_get_wish_list($customer_id);
    if (!$wishlist) {
        return 0;
    }
    
    $wishlist_items = edd_wl_get_wish_list_downloads($wishlist->ID);
    return is_array($wishlist_items) ? count($wishlist_items) : 0;
}

/**
 * Get license status information for display
 */
function eddcdp_get_license_status_info($license) {
    $license_status = strtolower($license->status);
    $is_expired = false;
    $is_disabled = ($license_status === 'disabled');
    
    // Check expiration
    if (!empty($license->expiration) && $license->expiration !== '0000-00-00 00:00:00') {
        $expiration_date = strtotime($license->expiration);
        $is_expired = ($expiration_date < time());
    }
    
    // Determine display status and styling
    if ($is_disabled) {
        return array(
            'container_class' => 'bg-gray-50/50 border-gray-200/50',
            'badge_class' => 'bg-gray-100 text-gray-800',
            'text' => __('Disabled', 'edd-customer-dashboard-pro'),
            'icon' => '🚫',
            'can_activate' => false
        );
    } elseif ($is_expired) {
        return array(
            'container_class' => 'bg-red-50/50 border-red-200/50',
            'badge_class' => 'bg-red-100 text-red-800',
            'text' => __('Expired', 'edd-customer-dashboard-pro'),
            'icon' => '⏰',
            'can_activate' => false
        );
    } elseif ($license_status === 'active') {
        return array(
            'container_class' => 'bg-green-50/50 border-green-200/50',
            'badge_class' => 'bg-green-100 text-green-800',
            'text' => __('Active', 'edd-customer-dashboard-pro'),
            'icon' => '✅',
            'can_activate' => true
        );
    } elseif ($license_status === 'inactive') {
        return array(
            'container_class' => 'bg-gray-50/50 border-gray-200/50',
            'badge_class' => 'bg-gray-100 text-red-600',
            'text' => __('Inactive', 'edd-customer-dashboard-pro'),
            'icon' => '⚪',
            'can_activate' => true
        );
    } else {
        return array(
            'container_class' => 'bg-yellow-50/50 border-yellow-200/50',
            'badge_class' => 'bg-yellow-100 text-yellow-800',
            'text' => ucfirst($license_status),
            'icon' => '⚠️',
            'can_activate' => false
        );
    }
}

/**
 * Get active sites for a license
 */
function eddcdp_get_license_active_sites($license_id) {
    global $wpdb;
    
    return $wpdb->get_results($wpdb->prepare(
        "SELECT site_id, site_name FROM {$wpdb->prefix}edd_license_activations WHERE license_id = %d AND activated = 1",
        $license_id
    ));
}

/**
 * Check if license can activate more sites
 */
function eddcdp_can_license_activate_sites($license, $active_sites_count) {
    $license_info = eddcdp_get_license_status_info($license);
    
    if (!$license_info['can_activate']) {
        return false;
    }
    
    $activation_limit = (int) $license->activation_limit;
    if ($activation_limit > 0 && $active_sites_count >= $activation_limit) {
        return false;
    }
    
    return true;
}