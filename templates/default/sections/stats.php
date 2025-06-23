<?php
/**
 * Stats Grid Section - Simple & Clean
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$current_user = wp_get_current_user();

// Get customer data using EDD 3.0+ methods
$customer = edd_get_customer_by('email', $current_user->user_email);
$purchase_count = $customer ? $customer->purchase_count : 0;

// Get download count using utility function
$download_count = eddcdp_get_customer_download_count($current_user->user_email);

// Get active license count using utility function
$license_count = eddcdp_get_customer_active_license_count($current_user->ID);

// Get wishlist count using utility function
$wishlist_count = eddcdp_get_customer_wishlist_count($current_user->ID);
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