<?php
/**
 * Downloads Section Template - Fixed translator comments
 */

if (!defined('ABSPATH')) {
    exit;
}

$downloads = $dashboard_data->get_customer_downloads($customer);
?>

<h2 class="eddcdp-section-title"><?php esc_html_e('Download History', 'edd-customer-dashboard-pro'); ?></h2>

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
                    <div class="eddcdp-product-name"><?php echo esc_html(get_the_title($download['id'])); ?></div>
                    <div class="eddcdp-purchase-date">
                        <?php 
                        // translators: %s is the purchase date
                        printf(esc_html__('Purchased: %s', 'edd-customer-dashboard-pro'), esc_html($dashboard_data->format_date($payment->date))); 
                        ?>
                    </div>
                </div>
                <div style="margin-top: 10px; color: #666;">
                    <?php
                    if (is_numeric($downloads_remaining) && $download_limit > 0) {
                        // translators: %1$d is the number of downloads remaining, %2$d is the total download limit
                        printf(
                            esc_html__('Downloads remaining: %1$d of %2$d', 'edd-customer-dashboard-pro'),
                            esc_html($downloads_remaining),
                            esc_html($download_limit)
                        );
                    } elseif ($downloads_remaining === esc_html__('Unlimited', 'edd-customer-dashboard-pro') || $download_limit == 0) {
                        esc_html_e('Downloads remaining: Unlimited', 'edd-customer-dashboard-pro');
                    } else {
                        // translators: %s is the number or status of downloads remaining
                        printf(
                            esc_html__('Downloads remaining: %s', 'edd-customer-dashboard-pro'),
                            esc_html($downloads_remaining)
                        );
                    }
                    ?>
                </div>
                
                <?php if ($dashboard_data->can_download_file($payment, $download['id'])) : ?>
                    <div class="eddcdp-order-actions" style="margin-top: 15px;">
                        <?php 
                        $download_files = $dashboard_data->get_download_files($download['id']);
                        if ($download_files) : ?>
                            <a href="<?php echo esc_url($dashboard_data->get_download_url($payment->key, $payment->email, 0, $download['id'])); ?>" class="eddcdp-btn eddcdp-btn-download">
                                🔽 <?php esc_html_e('Download', 'edd-customer-dashboard-pro'); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php else : ?>
        <div class="eddcdp-empty-state">
            <div class="eddcdp-empty-icon">⬇️</div>
            <h3><?php esc_html_e('No downloads yet', 'edd-customer-dashboard-pro'); ?></h3>
            <p><?php esc_html_e('Your download history will appear here.', 'edd-customer-dashboard-pro'); ?></p>
        </div>
    <?php endif; ?>
</div>