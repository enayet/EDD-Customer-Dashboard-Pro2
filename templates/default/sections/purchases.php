<?php
/**
 * Purchases Section Template - Using only core EDD functions
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get current user
$current_user = wp_get_current_user();

// Get user's purchases using basic query
$purchases = get_posts(array(
    'post_type' => 'edd_payment',
    'posts_per_page' => 20,
    'post_status' => array('publish', 'edd_subscription'),
    'meta_query' => array(
        array(
            'key' => '_edd_payment_user_email',
            'value' => $current_user->user_email,
            'compare' => '='
        )
    ),
    'orderby' => 'date',
    'order' => 'DESC'
));
?>

<h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-3">
    📦 <?php _e('Your Orders & Purchases', 'eddcdp'); ?>
</h2>

<?php if ($purchases) : ?>
<div class="space-y-6">
    <?php foreach ($purchases as $purchase) : 
        $payment_meta = get_post_meta($purchase->ID);
        $total = isset($payment_meta['_edd_payment_total'][0]) ? $payment_meta['_edd_payment_total'][0] : '0.00';
        $status = $purchase->post_status;
        $payment_key = isset($payment_meta['_edd_payment_purchase_key'][0]) ? $payment_meta['_edd_payment_purchase_key'][0] : '';
        
        // Get cart details
        $cart_details = isset($payment_meta['_edd_payment_meta'][0]) ? maybe_unserialize($payment_meta['_edd_payment_meta'][0]) : array();
        $downloads = isset($cart_details['cart_details']) ? $cart_details['cart_details'] : array();
    ?>
    
    <!-- Purchase Item -->
    <div class="bg-gray-50/80 rounded-2xl p-6 border border-gray-200/50 hover:shadow-md transition-all duration-300">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-4">
            <div>
                <h3 class="text-xl font-semibold text-gray-800 mb-2">
                    <?php printf(__('Purchase #%s', 'eddcdp'), $purchase->ID); ?>
                </h3>
                <div class="flex flex-wrap gap-4 text-sm text-gray-600">
                    <span class="flex items-center gap-1">📋 <?php printf(__('Order #%s', 'eddcdp'), $purchase->ID); ?></span>
                    <span class="flex items-center gap-1">📅 <?php echo date_i18n(get_option('date_format'), strtotime($purchase->post_date)); ?></span>
                    <span class="flex items-center gap-1 font-semibold">💰 <?php echo function_exists('edd_currency_filter') ? edd_currency_filter($total) : '$' . $total; ?></span>
                </div>
            </div>
            <?php 
            $status_class = 'bg-green-100 text-green-800';
            $status_icon = '✅';
            $status_text = __('Completed', 'eddcdp');
            
            if ($status == 'pending') {
                $status_class = 'bg-yellow-100 text-yellow-800';
                $status_icon = '⏳';
                $status_text = __('Pending', 'eddcdp');
            } elseif ($status == 'failed') {
                $status_class = 'bg-red-100 text-red-800';
                $status_icon = '❌';
                $status_text = __('Failed', 'eddcdp');
            }
            ?>
            <span class="<?php echo $status_class; ?> px-4 py-2 rounded-full text-sm font-medium w-fit">
                <?php echo $status_icon . ' ' . $status_text; ?>
            </span>
        </div>
        
        <?php if ($downloads && $status == 'publish') : ?>
        <div class="bg-white/60 rounded-xl p-4 mb-4 space-y-3">
            <?php foreach ($downloads as $download_item) : 
                $download_id = isset($download_item['id']) ? $download_item['id'] : 0;
                $download_name = get_the_title($download_id);
                $download_files = function_exists('edd_get_download_files') ? edd_get_download_files($download_id) : array();
            ?>
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 <?php echo count($downloads) > 1 ? 'pb-3 border-b border-gray-200 last:border-b-0 last:pb-0' : ''; ?>">
                <div>
                    <p class="font-medium text-gray-800"><?php echo $download_name; ?></p>
                    <?php if ($download_files) : ?>
                        <p class="text-sm text-gray-500 mt-1">
                            <?php 
                            $file_info = reset($download_files);
                            printf(__('File: %s', 'eddcdp'), isset($file_info['name']) ? $file_info['name'] : __('Download File', 'eddcdp'));
                            ?>
                        </p>
                    <?php endif; ?>
                </div>
                
                <button class="bg-gradient-to-r from-green-500 to-emerald-600 text-white px-6 py-2 rounded-xl font-medium hover:shadow-lg transition-all duration-300 flex items-center gap-2">
                    🔽 <?php _e('Download', 'eddcdp'); ?>
                </button>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        
        <div class="flex flex-wrap gap-3 pt-4 border-t border-gray-200">
            <button class="bg-white text-gray-600 border border-gray-300 px-4 py-2 rounded-xl hover:bg-gray-50 transition-colors flex items-center gap-2">
                📋 <?php _e('Details', 'eddcdp'); ?>
            </button>
            <button class="bg-white text-gray-600 border border-gray-300 px-4 py-2 rounded-xl hover:bg-gray-50 transition-colors flex items-center gap-2">
                📄 <?php _e('Invoice', 'eddcdp'); ?>
            </button>
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