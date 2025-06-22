<?php
/**
 * Stats Grid Section
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$current_user = wp_get_current_user();
$customer = new EDD_Customer($current_user->user_email);
$purchase_count = $customer->purchase_count;
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
                <p class="text-3xl font-bold text-gray-800 mb-1">156</p>
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
                <p class="text-3xl font-bold text-gray-800 mb-1">18</p>
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
                <p class="text-3xl font-bold text-gray-800 mb-1">7</p>
                <p class="text-sm font-medium text-gray-500 uppercase tracking-wide"><?php _e('Wishlist Items', 'eddcdp'); ?></p>
            </div>
            <div class="w-12 h-12 bg-gradient-to-br from-pink-500 to-rose-600 rounded-xl flex items-center justify-center text-white text-xl group-hover:scale-110 transition-transform">
                ❤️
            </div>
        </div>
    </div>
</div>