<?php
/**
 * Stats Grid Section - EDD 3.0+ Compatible
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$current_user = wp_get_current_user();

// Get customer data using EDD 3.0+ methods
$customer = edd_get_customer_by('email', $current_user->user_email);
$purchase_count = $customer ? $customer->purchase_count : 0;

// Get download count using EDD 3.0+ method
$download_logs = edd_get_file_download_logs(array(
    'customer' => $current_user->user_email,
    'number' => 999999,
    'fields' => 'ids'
));
$download_count = is_array($download_logs) ? count($download_logs) : 0;

// Get active license count if Software Licensing is active
$license_count = 0;
if (class_exists('EDD_Software_Licensing') && function_exists('edd_software_licensing')) {
    $licenses = edd_software_licensing()->licenses_db->get_licenses(array(
        'user_id' => $current_user->ID,
        'status' => 'active',
        'number' => 999999
    ));
    $license_count = is_array($licenses) ? count($licenses) : 0;
}

// Get wishlist count if Wish Lists is active
$wishlist_count = 0;
if (function_exists('edd_wl_get_wish_list') && function_exists('edd_wl_get_wish_list_downloads')) {
    $wishlist = edd_wl_get_wish_list($current_user->ID);
    if ($wishlist) {
        $wishlist_items = edd_wl_get_wish_list_downloads($wishlist->ID);
        $wishlist_count = is_array($wishlist_items) ? count($wishlist_items) : 0;
    }
}
?>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white/80 backdrop-blur-xl rounded-2xl p-6 shadow-lg border border-white/20 hover:shadow-xl transition-all duration-300 group hover:-translate-y-1">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-3xl font-bold text-gray-800 mb-1"><?php echo $purchase_count; ?></p>
                <p class="text-sm font-medium text-gray-500 uppercase tracking-wide"><?php _e('Total Purchases', 'eddcdp'); ?></p>
            </div>
            <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center text-white text-xl group-hover:scale-110 transition-transform">
                📦
            </div>
        </div>
    </div>
    
    <div class="bg-white/80 backdrop-blur-xl rounded-2xl p-6 shadow-lg border border-white/20 hover:shadow-xl transition-all duration-300 group hover:-translate-y-1">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-3xl font-bold text-gray-800 mb-1"><?php echo $download_count; ?></p>
                <p class="text-sm font-medium text-gray-500 uppercase tracking-wide"><?php _e('Downloads', 'eddcdp'); ?></p>
            </div>
            <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center text-white text-xl group-hover:scale-110 transition-transform">
                ⬇️
            </div>
        </div>
    </div>
    
    <div class="bg-white/80 backdrop-blur-xl rounded-2xl p-6 shadow-lg border border-white/20 hover:shadow-xl transition-all duration-300 group hover:-translate-y-1">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-3xl font-bold text-gray-800 mb-1"><?php echo $license_count; ?></p>
                <p class="text-sm font-medium text-gray-500 uppercase tracking-wide"><?php _e('Active Licenses', 'eddcdp'); ?></p>
            </div>
            <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center text-white text-xl group-hover:scale-110 transition-transform">
                🔑
            </div>
        </div>
    </div>
    
    <div class="bg-white/80 backdrop-blur-xl rounded-2xl p-6 shadow-lg border border-white/20 hover:shadow-xl transition-all duration-300 group hover:-translate-y-1">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-3xl font-bold text-gray-800 mb-1"><?php echo $wishlist_count; ?></p>
                <p class="text-sm font-medium text-gray-500 uppercase tracking-wide"><?php _e('Wishlist Items', 'eddcdp'); ?></p>
            </div>
            <div class="w-12 h-12 bg-gradient-to-br from-pink-500 to-rose-600 rounded-xl flex items-center justify-center text-white text-xl group-hover:scale-110 transition-transform">
                ❤️
            </div>
        </div>
    </div>
</div>