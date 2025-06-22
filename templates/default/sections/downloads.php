<?php
/**
 * Downloads Section Template
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get current user and download history
$current_user = wp_get_current_user();
$downloads = edd_get_users_downloaded_files($current_user->ID, 20);
?>

<h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-3">
    ⬇️ <?php _e('Download History', 'eddcdp'); ?>
</h2>

<?php if ($downloads) : ?>
<div class="space-y-4">
    <?php foreach ($downloads as $download) : 
        $download_id = $download->download_id;
        $payment = new EDD_Payment($download->payment_id);
        $download_name = get_the_title($download_id);
        $download_date = $download->date_created;
        $remaining_downloads = edd_get_file_download_limit_override($download_id, $payment->ID);
        $total_downloads = edd_get_download_sales_stats($download_id);
    ?>
    
    <div class="bg-gray-50/80 rounded-2xl p-6 border border-gray-200/50">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-800"><?php echo $download_name; ?></h3>
                <p class="text-gray-600 mt-1">
                    <?php printf(__('Downloaded: %s', 'eddcdp'), date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($download_date))); ?>
                </p>
                <p class="text-sm text-gray-500 mt-2">
                    <strong><?php _e('Downloads remaining:', 'eddcdp'); ?></strong> 
                    <?php 
                    $limit = edd_get_file_download_limit($download_id);
                    if ($limit) {
                        $used = edd_count_file_downloads($download_id, $payment->ID, $current_user->ID);
                        echo ($limit - $used) . ' ' . __('of', 'eddcdp') . ' ' . $limit;
                    } else {
                        _e('Unlimited', 'eddcdp');
                    }
                    ?>
                </p>
            </div>
            
            <?php 
            $is_recent = (strtotime($download_date) > strtotime('-7 days'));
            $badge_class = $is_recent ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-600';
            $badge_text = $is_recent ? __('Recent', 'eddcdp') : __('Previous', 'eddcdp');
            ?>
            <span class="<?php echo $badge_class; ?> px-4 py-2 rounded-full text-sm font-medium">
                <?php echo $badge_text; ?>
            </span>
        </div>
    </div>
    
    <?php endforeach; ?>
</div>

<?php else : ?>
<div class="bg-gray-50/80 rounded-2xl p-12 text-center border border-gray-200/50">
    <div class="w-24 h-24 bg-gradient-to-br from-green-500 to-emerald-600 rounded-full flex items-center justify-center text-white text-4xl mx-auto mb-6">
        ⬇️
    </div>
    <h3 class="text-2xl font-bold text-gray-800 mb-3"><?php _e('No Downloads Yet', 'eddcdp'); ?></h3>
    <p class="text-gray-600 mb-6"><?php _e('You haven\'t downloaded any files yet. Make a purchase to get started!', 'eddcdp'); ?></p>
    <button onclick="window.location.href='<?php echo home_url('/downloads/'); ?>'" class="bg-gradient-to-r from-green-500 to-emerald-600 text-white px-6 py-3 rounded-xl font-medium hover:shadow-lg transition-all duration-300">
        🛒 <?php _e('Browse Products', 'eddcdp'); ?>
    </button>
</div>
<?php endif; ?>