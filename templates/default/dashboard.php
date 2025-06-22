<?php
/**
 * Default Dashboard Template
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get current user and settings
$current_user = wp_get_current_user();
$settings = get_option('eddcdp_settings', array());
$enabled_sections = isset($settings['enabled_sections']) ? $settings['enabled_sections'] : array();

// Include header
include 'header.php';
?>

<div class="eddcdp-dashboard" x-data="dashboard()">
    <div class="container mx-auto px-4 py-8 max-w-7xl">
        
        <!-- Welcome Header -->
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

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <?php 
            // Get user purchase stats - using correct EDD functions
            $customer = new EDD_Customer($current_user->user_email);
            $purchase_count = $customer->purchase_count;
            
            // Get user's download history count
            $download_logs = edd_get_file_download_logs(array(
                'user_id' => $current_user->ID,
                'number' => -1
            ));
            $download_count = is_array($download_logs) ? count($download_logs) : 0;
            ?>
            
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
            
            <?php if (!empty($enabled_sections['downloads'])): ?>
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
            <?php endif; ?>
            
            <?php if (!empty($enabled_sections['licenses'])): ?>
            <div class="bg-white/80 backdrop-blur-xl rounded-2xl p-6 shadow-lg border border-white/20 hover:shadow-xl transition-all duration-300 group hover:-translate-y-1">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-3xl font-bold text-gray-800 mb-1">
                            <?php 
                            $license_count = 0;
                            if (function_exists('edd_software_licensing') && class_exists('EDD_SL_License')) {
                                $licenses = edd_software_licensing()->licenses_db->get_licenses(array('user_id' => $current_user->ID));
                                $license_count = is_array($licenses) ? count($licenses) : 0;
                            }
                            echo $license_count;
                            ?>
                        </p>
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wide"><?php _e('Active Licenses', 'eddcdp'); ?></p>
                    </div>
                    <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center text-white text-xl group-hover:scale-110 transition-transform">
                        🔑
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($enabled_sections['wishlist'])): ?>
            <div class="bg-white/80 backdrop-blur-xl rounded-2xl p-6 shadow-lg border border-white/20 hover:shadow-xl transition-all duration-300 group hover:-translate-y-1">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-3xl font-bold text-gray-800 mb-1">
                            <?php 
                            $wishlist_count = 0;
                            if (function_exists('edd_wl_get_wish_list')) {
                                $wishlist = edd_wl_get_wish_list($current_user->ID);
                                if ($wishlist) {
                                    $wishlist_items = edd_wl_get_wish_list_downloads($wishlist->ID);
                                    $wishlist_count = is_array($wishlist_items) ? count($wishlist_items) : 0;
                                }
                            }
                            echo $wishlist_count;
                            ?>
                        </p>
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wide"><?php _e('Wishlist Items', 'eddcdp'); ?></p>
                    </div>
                    <div class="w-12 h-12 bg-gradient-to-br from-pink-500 to-rose-600 rounded-xl flex items-center justify-center text-white text-xl group-hover:scale-110 transition-transform">
                        ❤️
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Navigation Tabs -->
        <div class="bg-white/80 backdrop-blur-xl rounded-2xl p-6 mb-8 shadow-lg border border-white/20">
            <div class="flex flex-wrap gap-3">
                <?php if (!empty($enabled_sections['purchases'])): ?>
                <button @click="activeTab = 'purchases'" :class="activeTab === 'purchases' ? 'bg-gradient-to-r from-indigo-500 to-purple-600 text-white shadow-lg' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'" class="px-6 py-3 rounded-xl font-medium transition-all duration-300 flex items-center gap-2 hover:-translate-y-0.5">
                    📦 <?php _e('Purchases', 'eddcdp'); ?>
                </button>
                <?php endif; ?>
                
                <?php if (!empty($enabled_sections['downloads'])): ?>
                <button @click="activeTab = 'downloads'" :class="activeTab === 'downloads' ? 'bg-gradient-to-r from-indigo-500 to-purple-600 text-white shadow-lg' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'" class="px-6 py-3 rounded-xl font-medium transition-all duration-300 flex items-center gap-2 hover:-translate-y-0.5">
                    ⬇️ <?php _e('Downloads', 'eddcdp'); ?>
                </button>
                <?php endif; ?>
                
                <?php if (!empty($enabled_sections['licenses'])): ?>
                <button @click="activeTab = 'licenses'" :class="activeTab === 'licenses' ? 'bg-gradient-to-r from-indigo-500 to-purple-600 text-white shadow-lg' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'" class="px-6 py-3 rounded-xl font-medium transition-all duration-300 flex items-center gap-2 hover:-translate-y-0.5">
                    🔑 <?php _e('Licenses', 'eddcdp'); ?>
                </button>
                <?php endif; ?>
                
                <?php if (!empty($enabled_sections['wishlist'])): ?>
                <button @click="activeTab = 'wishlist'" :class="activeTab === 'wishlist' ? 'bg-gradient-to-r from-indigo-500 to-purple-600 text-white shadow-lg' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'" class="px-6 py-3 rounded-xl font-medium transition-all duration-300 flex items-center gap-2 hover:-translate-y-0.5">
                    ❤️ <?php _e('Wishlist', 'eddcdp'); ?>
                </button>
                <?php endif; ?>
                
                <?php if (!empty($enabled_sections['analytics'])): ?>
                <button @click="activeTab = 'analytics'" :class="activeTab === 'analytics' ? 'bg-gradient-to-r from-indigo-500 to-purple-600 text-white shadow-lg' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'" class="px-6 py-3 rounded-xl font-medium transition-all duration-300 flex items-center gap-2 hover:-translate-y-0.5">
                    📊 <?php _e('Analytics', 'eddcdp'); ?>
                </button>
                <?php endif; ?>
                
                <?php if (!empty($enabled_sections['support'])): ?>
                <button @click="activeTab = 'support'" :class="activeTab === 'support' ? 'bg-gradient-to-r from-indigo-500 to-purple-600 text-white shadow-lg' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'" class="px-6 py-3 rounded-xl font-medium transition-all duration-300 flex items-center gap-2 hover:-translate-y-0.5">
                    💬 <?php _e('Support', 'eddcdp'); ?>
                </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Content Area -->
        <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/20 min-h-[600px]">
            
            <?php if (!empty($enabled_sections['purchases'])): ?>
            <div x-show="activeTab === 'purchases'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0" class="p-8">
                <?php include 'sections/purchases.php'; ?>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($enabled_sections['downloads'])): ?>
            <div x-show="activeTab === 'downloads'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0" class="p-8">
                <?php include 'sections/downloads.php'; ?>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($enabled_sections['licenses'])): ?>
            <div x-show="activeTab === 'licenses'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0" class="p-8">
                <?php include 'sections/licenses.php'; ?>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($enabled_sections['wishlist'])): ?>
            <div x-show="activeTab === 'wishlist'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0" class="p-8">
                <?php include 'sections/wishlist.php'; ?>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($enabled_sections['analytics'])): ?>
            <div x-show="activeTab === 'analytics'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0" class="p-8">
                <?php include 'sections/analytics.php'; ?>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($enabled_sections['support'])): ?>
            <div x-show="activeTab === 'support'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0" class="p-8">
                <?php include 'sections/support.php'; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
// Include footer
include 'footer.php';
?>