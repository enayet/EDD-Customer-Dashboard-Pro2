<?php
/**
 * Purchases Section Template
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get current user and purchases
$current_user = wp_get_current_user();
$customer = new EDD_Customer($current_user->user_email);
$purchases = edd_get_users_purchases($current_user->ID, 20, true, 'any');
?>

<h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-3">
    📦 <?php _e('Your Orders & Purchases', 'eddcdp'); ?>
</h2>

<?php if ($purchases) : ?>
<div class="space-y-6">
    <?php foreach ($purchases as $purchase) : 
        $payment = new EDD_Payment($purchase->ID);
        $downloads = edd_get_payment_meta_downloads($purchase->ID);
    ?>
    
    <!-- Purchase Item -->
    <div class="bg-gray-50/80 rounded-2xl p-6 border border-gray-200/50 hover:shadow-md transition-all duration-300">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-4">
            <div>
                <h3 class="text-xl font-semibold text-gray-800 mb-2">
                    <?php echo $payment->get_meta('_edd_payment_purchase_key', true); ?>
                </h3>
                <div class="flex flex-wrap gap-4 text-sm text-gray-600">
                    <span class="flex items-center gap-1">📋 <?php printf(__('Order #%s', 'eddcdp'), $payment->number); ?></span>
                    <span class="flex items-center gap-1">📅 <?php echo date_i18n(get_option('date_format'), strtotime($payment->date)); ?></span>
                    <span class="flex items-center gap-1 font-semibold">💰 <?php echo edd_currency_filter(edd_format_amount($payment->total)); ?></span>
                </div>
            </div>
            <?php 
            $status_class = 'bg-green-100 text-green-800';
            $status_icon = '✅';
            $status_text = __('Completed', 'eddcdp');
            
            if ($payment->status == 'pending') {
                $status_class = 'bg-yellow-100 text-yellow-800';
                $status_icon = '⏳';
                $status_text = __('Pending', 'eddcdp');
            } elseif ($payment->status == 'failed') {
                $status_class = 'bg-red-100 text-red-800';
                $status_icon = '❌';
                $status_text = __('Failed', 'eddcdp');
            }
            ?>
            <span class="<?php echo $status_class; ?> px-4 py-2 rounded-full text-sm font-medium w-fit">
                <?php echo $status_icon . ' ' . $status_text; ?>
            </span>
        </div>
        
        <?php if ($downloads && $payment->status == 'publish') : ?>
        <div class="bg-white/60 rounded-xl p-4 mb-4 space-y-3">
            <?php foreach ($downloads as $download) : 
                $download_id = isset($download['id']) ? $download['id'] : $download;
                $download_files = edd_get_download_files($download_id);
                $download_name = get_the_title($download_id);
                $price_id = isset($download['options']['price_id']) ? $download['options']['price_id'] : 0;
            ?>
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 <?php echo count($downloads) > 1 ? 'pb-3 border-b border-gray-200 last:border-b-0 last:pb-0' : ''; ?>">
                <div>
                    <p class="font-medium text-gray-800"><?php echo $download_name; ?></p>
                    <?php if ($download_files) : ?>
                        <p class="text-sm text-gray-500 mt-1">
                            <?php 
                            $file_info = reset($download_files);
                            printf(__('File: %s', 'eddcdp'), basename($file_info['file']));
                            ?>
                        </p>
                    <?php endif; ?>
                </div>
                
                <?php if (edd_can_redownload_file($download_id, $payment->ID, $current_user->ID)) : ?>
                <button 
                    onclick="window.location.href='<?php echo edd_get_download_file_url($payment->key, $current_user->user_email, reset($download_files)['attachment_id'], $download_id); ?>'"
                    class="bg-gradient-to-r from-green-500 to-emerald-600 text-white px-6 py-2 rounded-xl font-medium hover:shadow-lg transition-all duration-300 flex items-center gap-2">
                    🔽 <?php _e('Download', 'eddcdp'); ?>
                </button>
                <?php else : ?>
                <span class="text-gray-400 px-6 py-2">
                    <?php _e('Download Expired', 'eddcdp'); ?>
                </span>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        
        <div class="flex flex-wrap gap-3 pt-4 border-t border-gray-200">
            <button class="bg-white text-gray-600 border border-gray-300 px-4 py-2 rounded-xl hover:bg-gray-50 transition-colors flex items-center gap-2">
                📋 <?php _e('Details', 'eddcdp'); ?>
            </button>
            <button onclick="window.open('<?php echo edd_get_success_page_uri(); ?>?payment_key=<?php echo $payment->key; ?>&edd_action=generate_pdf', '_blank')" class="bg-white text-gray-600 border border-gray-300 px-4 py-2 rounded-xl hover:bg-gray-50 transition-colors flex items-center gap-2">
                📄 <?php _e('Invoice', 'eddcdp'); ?>
            </button>
            <?php if (class_exists('EDD_Software_Licensing')) : ?>
            <button class="bg-white text-gray-600 border border-gray-300 px-4 py-2 rounded-xl hover:bg-gray-50 transition-colors flex items-center gap-2">
                🔑 <?php _e('Licenses', 'eddcdp'); ?>
            </button>
            <?php endif; ?>
            <button class="bg-white text-gray-600 border border-gray-300 px-4 py-2 rounded-xl hover:bg-gray-50 transition-colors flex items-center gap-2">
                💬 <?php _e('Support', 'eddcdp'); ?>
            </button>
        </div>
    </div>
    
    <?php endforeach; ?>
</div>

<?php else : ?>
<div class="bg-gray-50/80 rounded-2xl p-12 text-center border border-gray-200/50">
    <div class="w-24 h-24 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center text-white text-4xl mx-auto mb-6">
        📦
    </div>
    <h3 class="text-2xl font-bold text-gray-800 mb-3"><?php _e('No Purchases Yet', 'eddcdp'); ?></h3>
    <p class="text-gray-600 mb-6"><?php _e('You haven\'t made any purchases yet. Start exploring our products!', 'eddcdp'); ?></p>
    <button onclick="window.location.href='<?php echo home_url('/downloads/'); ?>'" class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white px-6 py-3 rounded-xl font-medium hover:shadow-lg transition-all duration-300">
        🛒 <?php _e('Browse Products', 'eddcdp'); ?>
    </button>
</div>
<?php endif; ?>