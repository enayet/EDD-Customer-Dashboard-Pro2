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
$fullscreen = !empty($settings['fullscreen_mode']);

// Include header
include 'header.php';
?>

<?php if (!$fullscreen) : ?>
<style>
/* Override WordPress layout constraints for embedded mode */
.eddcdp-dashboard-wrapper * {
    max-width: none !important;
}

.eddcdp-dashboard-wrapper .is-layout-constrained > :where(:not(.alignleft):not(.alignright):not(.alignfull)) {
    max-width: none !important;
    margin-left: 0 !important;
    margin-right: 0 !important;
}
</style>
<?php endif; ?>

<div class="eddcdp-dashboard-wrapper <?php echo $fullscreen ? 'eddcdp-fullscreen-wrapper' : 'eddcdp-embedded-wrapper'; ?>" style="<?php echo $fullscreen ? 'width: 100vw; height: 100vh; margin: 0; padding: 0; max-width: none;' : ''; ?>">

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
            // Get user purchase stats
            $customer = new EDD_Customer($current_user->user_email);
            $purchase_count = $customer->purchase_count;
            $total_spent = $customer->purchase_value;
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

        <!-- Navigation Tabs -->
        <div class="bg-white/80 backdrop-blur-xl rounded-2xl p-6 mb-8 shadow-lg border border-white/20">
            <div class="flex flex-wrap gap-3">
                <template x-for="tab in tabs" :key="tab.id">
                    <button 
                        @click="activeTab = tab.id"
                        :class="activeTab === tab.id ? 'bg-gradient-to-r from-indigo-500 to-purple-600 text-white shadow-lg' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                        class="px-6 py-3 rounded-xl font-medium transition-all duration-300 flex items-center gap-2 hover:-translate-y-0.5"
                        x-text="tab.label">
                    </button>
                </template>
            </div>
        </div>

        <!-- Content Area -->
        <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/20 min-h-[600px]">
            
            <!-- Purchases Tab -->
            <div x-show="activeTab === 'purchases'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0" class="p-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-3">
                    📦 <?php _e('Your Orders & Purchases', 'eddcdp'); ?>
                </h2>
                
                <?php 
                $purchases = edd_get_users_purchases($current_user->ID, 10, true, 'any');
                if ($purchases) :
                ?>
                <div class="space-y-6">
                    <?php foreach ($purchases as $purchase) : 
                        $payment = new EDD_Payment($purchase->ID);
                        $downloads = edd_get_payment_meta_downloads($purchase->ID);
                    ?>
                    <div class="bg-gray-50/80 rounded-2xl p-6 border border-gray-200/50 hover:shadow-md transition-all duration-300">
                        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-4">
                            <div>
                                <h3 class="text-xl font-semibold text-gray-800 mb-2"><?php printf(__('Order #%s', 'eddcdp'), $payment->number); ?></h3>
                                <div class="flex flex-wrap gap-4 text-sm text-gray-600">
                                    <span class="flex items-center gap-1">📋 <?php printf(__('Order #%s', 'eddcdp'), $payment->number); ?></span>
                                    <span class="flex items-center gap-1">📅 <?php echo date_i18n(get_option('date_format'), strtotime($payment->date)); ?></span>
                                    <span class="flex items-center gap-1 font-semibold">💰 <?php echo edd_currency_filter(edd_format_amount($payment->total)); ?></span>
                                </div>
                            </div>
                            <span class="bg-green-100 text-green-800 px-4 py-2 rounded-full text-sm font-medium w-fit">✅ <?php _e('Completed', 'eddcdp'); ?></span>
                        </div>
                        
                        <?php if ($downloads && $payment->status == 'publish') : ?>
                        <div class="bg-white/60 rounded-xl p-4 mb-4">
                            <?php foreach ($downloads as $download) : 
                                $download_id = isset($download['id']) ? $download['id'] : $download;
                                $download_name = get_the_title($download_id);
                            ?>
                            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
                                <div>
                                    <p class="font-medium text-gray-800"><?php echo $download_name; ?></p>
                                    <p class="text-sm text-gray-500 mt-1"><?php _e('File: product-file.zip', 'eddcdp'); ?></p>
                                </div>
                                <button class="bg-gradient-to-r from-green-500 to-emerald-600 text-white px-6 py-2 rounded-xl font-medium hover:shadow-lg transition-all duration-300 flex items-center gap-2">
                                    🔽 <?php _e('Download', 'eddcdp'); ?>
                                </button>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                        
                        <div class="flex flex-wrap gap-3 pt-4 border-t border-gray-200">
                            <button class="bg-white text-gray-600 border border-gray-300 px-4 py-2 rounded-xl hover:bg-gray-50 transition-colors flex items-center gap-2">📋 <?php _e('Details', 'eddcdp'); ?></button>
                            <button class="bg-white text-gray-600 border border-gray-300 px-4 py-2 rounded-xl hover:bg-gray-50 transition-colors flex items-center gap-2">📄 <?php _e('Invoice', 'eddcdp'); ?></button>
                            <button class="bg-white text-gray-600 border border-gray-300 px-4 py-2 rounded-xl hover:bg-gray-50 transition-colors flex items-center gap-2">🔑 <?php _e('Licenses', 'eddcdp'); ?></button>
                            <button class="bg-white text-gray-600 border border-gray-300 px-4 py-2 rounded-xl hover:bg-gray-50 transition-colors flex items-center gap-2">💬 <?php _e('Support', 'eddcdp'); ?></button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else : ?>
                <div class="bg-gray-50/80 rounded-2xl p-12 text-center border border-gray-200/50">
                    <div class="w-24 h-24 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center text-white text-4xl mx-auto mb-6">📦</div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-3"><?php _e('No Purchases Yet', 'eddcdp'); ?></h3>
                    <p class="text-gray-600 mb-6"><?php _e('You haven\'t made any purchases yet. Start exploring our products!', 'eddcdp'); ?></p>
                </div>
                <?php endif; ?>
            </div>

            <!-- Downloads Tab -->
            <div x-show="activeTab === 'downloads'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0" class="p-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-3">
                    ⬇️ <?php _e('Download History', 'eddcdp'); ?>
                </h2>
                <div class="bg-gray-50/80 rounded-2xl p-12 text-center border border-gray-200/50">
                    <div class="w-24 h-24 bg-gradient-to-br from-green-500 to-emerald-600 rounded-full flex items-center justify-center text-white text-4xl mx-auto mb-6">⬇️</div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-3"><?php _e('No Downloads Yet', 'eddcdp'); ?></h3>
                    <p class="text-gray-600"><?php _e('Download history will appear here.', 'eddcdp'); ?></p>
                </div>
            </div>

            <!-- Licenses Tab -->
            <div x-show="activeTab === 'licenses'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0" class="p-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-3">
                    🔑 <?php _e('License Management', 'eddcdp'); ?>
                </h2>
                <div class="bg-gray-50/80 rounded-2xl p-12 text-center border border-gray-200/50">
                    <div class="w-24 h-24 bg-gradient-to-br from-purple-500 to-purple-600 rounded-full flex items-center justify-center text-white text-4xl mx-auto mb-6">🔑</div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-3"><?php _e('No Licenses Found', 'eddcdp'); ?></h3>
                    <p class="text-gray-600"><?php _e('License management will appear here.', 'eddcdp'); ?></p>
                </div>
            </div>

            <!-- Wishlist Tab -->
            <div x-show="activeTab === 'wishlist'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0" class="p-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-3">
                    ❤️ <?php _e('Your Wishlist', 'eddcdp'); ?>
                </h2>
                <div class="bg-gray-50/80 rounded-2xl p-12 text-center border border-gray-200/50">
                    <div class="w-24 h-24 bg-gradient-to-br from-pink-500 to-rose-600 rounded-full flex items-center justify-center text-white text-4xl mx-auto mb-6">❤️</div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-3"><?php _e('Your Wishlist is Empty', 'eddcdp'); ?></h3>
                    <p class="text-gray-600"><?php _e('Wishlist items will appear here.', 'eddcdp'); ?></p>
                </div>
            </div>

            <!-- Analytics Tab -->
            <div x-show="activeTab === 'analytics'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0" class="p-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-3">
                    📊 <?php _e('Purchase Analytics', 'eddcdp'); ?>
                </h2>
                <div class="bg-gray-50/80 rounded-2xl p-12 text-center border border-gray-200/50">
                    <div class="w-24 h-24 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center text-white text-4xl mx-auto mb-6">📊</div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-3"><?php _e('Analytics Coming Soon', 'eddcdp'); ?></h3>
                    <p class="text-gray-600"><?php _e('Purchase analytics will appear here.', 'eddcdp'); ?></p>
                </div>
            </div>

            <!-- Support Tab -->
            <div x-show="activeTab === 'support'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0" class="p-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-3">
                    💬 <?php _e('Support Center', 'eddcdp'); ?>
                </h2>
                <div class="bg-gray-50/80 rounded-2xl p-12 text-center border border-gray-200/50">
                    <div class="w-24 h-24 bg-gradient-to-br from-purple-500 to-pink-600 rounded-full flex items-center justify-center text-white text-4xl mx-auto mb-6">💬</div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-3"><?php _e('Need Help?', 'eddcdp'); ?></h3>
                    <p class="text-gray-600"><?php _e('Support center will appear here.', 'eddcdp'); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function dashboard() {
    return {
        activeTab: 'purchases',
        downloading: null,
        newSiteUrl: '',
        
        tabs: [
            { id: 'purchases', label: '📦 <?php _e('Purchases', 'eddcdp'); ?>' },
            { id: 'downloads', label: '⬇️ <?php _e('Downloads', 'eddcdp'); ?>' },
            { id: 'licenses', label: '🔑 <?php _e('Licenses', 'eddcdp'); ?>' },
            { id: 'wishlist', label: '❤️ <?php _e('Wishlist', 'eddcdp'); ?>' },
            { id: 'analytics', label: '📊 <?php _e('Analytics', 'eddcdp'); ?>' },
            { id: 'support', label: '💬 <?php _e('Support', 'eddcdp'); ?>' }
        ],
        
        downloadFile(productId) {
            this.downloading = productId;
            setTimeout(() => {
                this.downloading = null;
            }, 2000);
        },
        
        copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                console.log('License key copied to clipboard');
            });
        },
        
        activateSite() {
            if (this.newSiteUrl) {
                console.log('Activating site:', this.newSiteUrl);
                this.newSiteUrl = '';
            }
        }
    }
}
</script>

</div> <!-- Close eddcdp-dashboard-wrapper -->

<?php
// Include footer
include 'footer.php';
?>