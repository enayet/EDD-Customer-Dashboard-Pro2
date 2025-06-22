<?php
/**
 * Welcome Header Section
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$current_user = wp_get_current_user();
?>

<div class="bg-white/80 backdrop-blur-xl rounded-3xl p-8 mb-8 shadow-xl border border-white/20 animate-fade-in">
    <div class="flex flex-col lg:flex-row items-center justify-between gap-6">
        <div class="text-center lg:text-left">
            <h1 class="text-4xl lg:text-5xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent mb-3">
                <?php printf(__('Welcome back, %s! 👋', 'eddcdp'), $current_user->display_name); ?>
            </h1>
            <p class="text-gray-600 text-lg"><?php _e('Manage your digital products, licenses, and downloads', 'eddcdp'); ?></p>
        </div>
        <div class="flex-shrink-0">
            <div class="w-20 h-20 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center text-white text-2xl font-bold shadow-lg">
                <?php echo strtoupper(substr($current_user->display_name, 0, 2)); ?>
            </div>
        </div>
    </div>
</div>