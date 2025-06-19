<?php
/**
 * Downloads Section Template
 */

if (!defined('ABSPATH')) {
    exit;
}

$downloads = $dashboard_data->get_customer_downloads($customer);
?>

<h2 class="eddcdp-section-title"><?php _e('Download History', 'edd-customer-dashboard-pro'); ?></h2>

<div class="eddcdp-purchase-list">
    <?php if ($downloads) : ?>
        <?php foreach ($downloads as $download_item) : ?>
            <?php 
            $payment = $download_item['payment'];
            $download = $download_item['download'];
            $download_limit = $download_item['download_limit'];
            $downloads_remaining = $download_item['downloads_remaining'];
            ?>
            <div class="eddcdp-purchase-item">
                <div class="eddcdp-purchase-header">
                    <div class="eddcdp-product-name"><?php echo get_the_title($download['id']); ?></div>
                    <div class="eddcdp-purchase-date">
                        <?php printf(__('Purchased: %s', 'edd-customer-dashboard-pro'), $dashboard_data->format_date($payment->date)); ?>
                    </div>
                </div>
                <div style="margin-top: 10px; color: #666;">
                    <?php
                    if (is_numeric($downloads_remaining) && $download_limit > 0) {
                        printf(
                            __('<strong>Downloads remaining:</strong> %d of %d', 'edd-customer-dashboard-pro'),
                            $downloads_remaining,
                            $download_limit
                        );
                    } elseif ($downloads_remaining === __('Unlimited', 'edd-customer-dashboard-pro') || $download_limit == 0) {
                        _e('<strong>Downloads remaining:</strong> Unlimited', 'edd-customer-dashboard-pro');
                    } else {
                        printf(
                            __('<strong>Downloads remaining:</strong> %s', 'edd-customer-dashboard-pro'),
                            $downloads_remaining
                        );
                    }
                    ?>
                </div>
                
                <?php if ($dashboard_data->can_download_file($payment, $download['id'])) : ?>
                    <div class="eddcdp-order-actions" style="margin-top: 15px;">
                        <?php 
                        $download_files = $dashboard_data->get_download_files($download['id']);
                        if ($download_files) : ?>
                            <a href="<?php echo $dashboard_data->get_download_url($payment->key, $payment->email, 0, $download['id']); ?>" class="eddcdp-btn eddcdp-btn-download">
                                🔽 <?php _e('Download', 'edd-customer-dashboard-pro'); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php else : ?>
        <div class="eddcdp-empty-state">
            <div class="eddcdp-empty-icon">⬇️</div>
            <h3><?php _e('No downloads yet', 'edd-customer-dashboard-pro'); ?></h3>
            <p><?php _e('Your download history will appear here.', 'edd-customer-dashboard-pro'); ?></p>
        </div>
    <?php endif; ?>
</div>