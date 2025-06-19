<?php
/**
 * Aurora Template - Downloads Section
 */

if (!defined('ABSPATH')) {
    exit;
}

$downloads = $dashboard_data->get_customer_downloads($customer);
?>

<h2 class="eddcdp-section-title"><?php esc_html_e('Download History', 'edd-customer-dashboard-pro'); ?></h2>

<?php if ($downloads) : ?>
    <div class="eddcdp-purchase-list">
        <?php foreach ($downloads as $download_item) : ?>
            <?php 
            $payment = $download_item['payment'];
            $download = $download_item['download'];
            $download_limit = $download_item['download_limit'];
            $downloads_remaining = $download_item['downloads_remaining'];
            ?>
            <div class="eddcdp-purchase-item">
                <div class="eddcdp-purchase-header">
                    <div class="eddcdp-order-info">
                        <div class="eddcdp-product-name"><?php echo esc_html(get_the_title($download['id'])); ?></div>
                        <div class="eddcdp-purchase-date">
                            <?php 
                            // translators: %s is the purchase date
                            printf(esc_html__('Downloaded: %s', 'edd-customer-dashboard-pro'), esc_html($dashboard_data->format_date($payment->date))); 
                            ?>
                        </div>
                    </div>
                </div>
                
                <div style="margin-top: 10px; color: var(--gray); display: flex; align-items: center;">
                    <i class="fas fa-download" style="margin-right: 8px; color: var(--primary);"></i>
                    <strong><?php esc_html_e('Downloads remaining:', 'edd-customer-dashboard-pro'); ?></strong>&nbsp;
                    <?php
                    if (is_numeric($downloads_remaining) && $download_limit > 0) {
                        // translators: %1$d is the number of downloads remaining, %2$d is the total download limit
                        printf( esc_html__('%1$d of %2$d', 'edd-customer-dashboard-pro'),
                            esc_html($downloads_remaining),
                            esc_html($download_limit)
                        );
                    } elseif ($downloads_remaining === esc_html__('Unlimited', 'edd-customer-dashboard-pro') || $download_limit == 0) {
                        esc_html_e('Unlimited', 'edd-customer-dashboard-pro');
                    } else {
                        echo esc_html($downloads_remaining);
                    }
                    ?>
                </div>
                
                <?php if ($dashboard_data->can_download_file($payment, $download['id'])) : ?>
                    <div style="margin-top: 15px;">
                        <?php 
                        $download_files = $dashboard_data->get_download_files($download['id']);
                        if ($download_files) : ?>
                            <a href="<?php echo esc_url($dashboard_data->get_download_url($payment->key, $payment->email, 0, $download['id'])); ?>" class="eddcdp-btn eddcdp-btn-download">
                                <i class="fas fa-download"></i> <?php esc_html_e('Download Again', 'edd-customer-dashboard-pro'); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php else : ?>
    <div class="eddcdp-empty-state">
        <div class="eddcdp-empty-icon">
            <i class="fas fa-download"></i>
        </div>
        <h3><?php esc_html_e('No Recent Downloads', 'edd-customer-dashboard-pro'); ?></h3>
        <p><?php esc_html_e('Your download history will appear here when you download your purchased products.', 'edd-customer-dashboard-pro'); ?></p>
        <button class="eddcdp-btn eddcdp-btn-primary" onclick="document.querySelector('[data-section=purchases]').click(); return false;">
            <i class="fas fa-box-open"></i> <?php esc_html_e('View My Products', 'edd-customer-dashboard-pro'); ?>
        </button>
    </div>
<?php endif; ?>