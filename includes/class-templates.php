<?php

class EDDCDP_Template_Loader {    
    
    public static function load_dashboard() {
        // Get the current user and pass any data needed to the template
        $user = wp_get_current_user();
        $dashboard_data = array(); // Add purchase/download/license data here
        
        // Load the template
        include plugin_dir_path(__FILE__) . '../templates/edd_dashboard.html';
    }    

    public static function render() {
        $active_template = get_option('eddcdp_active_template', 'default');
        $template_path = EDDCDP_PATH . 'templates/' . $active_template . '/edd_dashboard.html';

        if (!file_exists($template_path)) {
            echo '<p>Dashboard template not found.</p>';
            return;
        }

        $current_user = wp_get_current_user();
        $purchases = edd_get_users_purchases($current_user->ID, -1, false, 'any');

        // Open output buffer to inject purchases later
        ob_start();
        include $template_path;
        $html = ob_get_clean();

        // Inject purchases where `<!--PURCHASES-->` placeholder exists
        $html = str_replace('<!--PURCHASES-->', self::render_purchases_html($purchases), $html);
        $html = str_replace('<!--LICENSES-->', self::render_licenses_html(), $html);        

        echo $html;
    }    
    
    
    public static function render_purchases_html($purchases) {
        if (empty($purchases)) {
            return '<p class="text-gray-500">No purchases found.</p>';
        }

        $output = '';

        foreach ($purchases as $payment_id) {
            $payment = edd_get_payment($payment_id);
            if (!$payment) continue;

            $cart_items = edd_get_payment_meta_cart_details($payment_id, true);
            if (empty($cart_items)) continue;

            // FIX: Get the status as a string, not passing the payment object
            $status = edd_get_payment_status($payment_id, true); // Pass payment ID, not object
            $total = edd_currency_filter(edd_format_amount($payment->total));
            $date = date_i18n(get_option('date_format'), strtotime($payment->date));
            
            // FIX: Safely get meta data
            $first_name = '';
            $last_name = '';
            if (is_object($payment)) {
                // Use proper method to get meta or fallback to legacy
                if (method_exists($payment, 'get_meta')) {
                    $first_name = $payment->get_meta('_edd_payment_meta')['user_info']['first_name'] ?? '';
                    $last_name = $payment->get_meta('_edd_payment_meta')['user_info']['last_name'] ?? '';
                } else {
                    // Fallback for older EDD versions
                    $user_info = edd_get_payment_meta_user_info($payment_id);
                    $first_name = $user_info['first_name'] ?? '';
                    $last_name = $user_info['last_name'] ?? '';
                }
            }

            $output .= '<div class="bg-gray-50/80 rounded-2xl p-6 border border-gray-200/50 hover:shadow-md transition-all duration-300 mb-6">';
            $output .= '<div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-4">';
            $output .= '<div>';
            $output .= '<h3 class="text-xl font-semibold text-gray-800 mb-2">' . esc_html($first_name . ' ' . $last_name) . '</h3>';
            $output .= '<div class="flex flex-wrap gap-4 text-sm text-gray-600">';
            //$output .= '<span class="flex items-center gap-1">📋 Order #' . esc_html($payment_id) . '</span>';
            //$output .= '<span class="flex items-center gap-1">📅 ' . esc_html($date) . '</span>';
            $output .= '<span class="flex items-center gap-1 font-semibold">💰 ' . esc_html($total) . '</span>';
            $output .= '</div></div>';
            $output .= '<span class="bg-green-100 text-green-800 px-4 py-2 rounded-full text-sm font-medium w-fit">✅ ' . esc_html($status) . '</span>';
            $output .= '</div>';

            foreach ($cart_items as $item) {
                $file_name = $item['name'];
                $price     = edd_currency_filter($item['price']);
                $output .= '<div class="bg-white/60 rounded-xl p-4 mb-4">';
                $output .= '<div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">';
                $output .= '<div><p class="font-medium text-gray-800">' . esc_html($file_name) . '</p>';
                $output .= '<p class="text-sm text-gray-500 mt-1">Price: ' . esc_html($price) . '</p></div>';
                
                // FIX: Get email from payment object properly
                $payment_email = '';
                if (is_object($payment)) {
                    if (property_exists($payment, 'email')) {
                        $payment_email = $payment->email;
                    } else {
                        // Fallback method
                        $user_info = edd_get_payment_meta_user_info($payment_id);
                        $payment_email = $user_info['email'] ?? '';
                    }
                }
                
                $output .= '<a href="' . esc_url(edd_get_download_file_url($payment_email, $payment_id, $item['id'], $item['options']['price_id'] ?? null)) . '" class="bg-gradient-to-r from-green-500 to-emerald-600 text-white px-6 py-2 rounded-xl font-medium hover:shadow-lg transition-all duration-300 flex items-center gap-2">🔽 Download</a>';
                $output .= '</div></div>';
            }

            $output .= '<div class="flex flex-wrap gap-3 pt-4 border-t border-gray-200">';
            $output .= '<a href="' . esc_url(edd_get_payment_receipt_url($payment_id, false)) . '" class="bg-white text-gray-600 border border-gray-300 px-4 py-2 rounded-xl hover:bg-gray-50 transition-colors flex items-center gap-2">📄 Receipt</a>';
            $output .= '</div>';
            $output .= '</div>';
        }

        return $output;
    }
    
    
    public static function render_licenses_html() {
        if (!function_exists('edd_software_licensing')) {
            return '<p class="text-red-600">Software Licensing is not enabled.</p>';
        }

        $user_id = get_current_user_id();
        $licenses = edd_software_licensing()->get_licenses_of_customer($user_id);

        if (empty($licenses)) {
            return '<p class="text-gray-500">No licenses found.</p>';
        }

        $output = '';

        foreach ($licenses as $license) {
            $license = edd_software_licensing()->get_license($license->ID);
            $status = $license->get_status();
            $status_label = ($status === 'active') ? '✅ Active' : '❌ ' . ucfirst($status);
            $status_class = ($status === 'active') ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';

            $output .= '<div class="bg-white/80 rounded-2xl p-6 border border-gray-200/50 mb-6">';
            $output .= '<div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 mb-6">';
            $output .= '<h3 class="text-xl font-semibold text-gray-800">' . esc_html($license->get_download()->post_title) . '</h3>';
            $output .= '<span class="' . $status_class . ' px-4 py-2 rounded-full text-sm font-medium">' . esc_html($status_label) . '</span>';
            $output .= '</div>';

            $output .= '<div class="bg-white/80 rounded-xl p-6">';
            $output .= '<div class="mb-4">';
            $output .= '<label class="block text-sm font-medium text-gray-700 mb-2">License Key:</label>';
            $output .= '<div class="bg-gray-100 p-3 rounded-lg font-mono text-sm cursor-pointer border">' . esc_html($license->key) . '</div>';
            $output .= '</div>';

            $output .= '<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">';
            $output .= '<div>';
            $output .= '<p class="text-sm text-gray-600"><strong>Purchase Date:</strong> ' . esc_html($license->get_purchase_date()) . '</p>';
            $output .= '<p class="text-sm text-gray-600"><strong>Expires:</strong> ' . esc_html($license->get_expiration()) . '</p>';
            $output .= '</div><div>';
            $output .= '<p class="text-sm text-gray-600"><strong>Activations:</strong> ' . esc_html($license->activation_count()) . ' of ' . esc_html($license->activation_limit()) . '</p>';
            $output .= '<p class="text-sm text-gray-600"><strong>License Type:</strong> ' . esc_html(ucfirst($license->get_price_name())) . '</p>';
            $output .= '</div></div>';

            // Optional buttons
            $output .= '<div class="flex flex-wrap gap-3 mt-6 pt-4 border-t">';
            $output .= '<a href="#" class="bg-white text-gray-600 border border-gray-300 px-4 py-2 rounded-xl hover:bg-gray-50">📄 Invoice</a>';
            $output .= '<a href="#" class="bg-white text-gray-600 border border-gray-300 px-4 py-2 rounded-xl hover:bg-gray-50">⬆️ Upgrade</a>';
            if ($license->is_expired()) {
                $output .= '<a href="#" class="bg-gradient-to-r from-orange-500 to-amber-500 text-white px-4 py-2 rounded-xl hover:shadow-lg">🔄 Renew License</a>';
            }
            $output .= '</div></div></div>';
        }
        
        $license_id = $license->ID;
        // Replace {{LICENSE_ID}} in Alpine block
        $output .= str_replace('{{LICENSE_ID}}', $license_id, <<<HTML
        <!-- Render Alpine.js block from step 1 here -->
        HTML);        
        

        return $output;
    }
}