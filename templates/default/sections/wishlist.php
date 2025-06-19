<?php
/**
 * Wishlist Section Template
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!$dashboard_data->is_wishlist_active()) {
    ?>
    <div class="eddcdp-empty-state">
        <div class="eddcdp-empty-icon">❤️</div>
        <h3><?php _e('Wishlist not available', 'edd-customer-dashboard-pro'); ?></h3>
        <p><?php _e('Wishlist functionality requires the Wish Lists add-on.', 'edd-customer-dashboard-pro'); ?></p>
    </div>
    <?php
    return;
}

$wishlist_items = $dashboard_data->get_customer_wishlist($user->ID);
?>

<h2 class="eddcdp-section-title"><?php _e('Your Wishlist', 'edd-customer-dashboard-pro'); ?></h2>

<?php if ($wishlist_items) : ?>
    <div class="eddcdp-wishlist-grid">
        <?php foreach ($wishlist_items as $item) : ?>
            <?php $download = get_post($item->download_id); ?>
            <div class="eddcdp-wishlist-item">
                <div class="eddcdp-product-image">
                    <?php 
                    if (has_post_thumbnail($download->ID)) {
                        echo get_the_post_thumbnail($download->ID, 'thumbnail');
                    } else {
                        echo '🎁';
                    }
                    ?>
                </div>
                <h3><?php echo esc_html($download->post_title); ?></h3>
                <?php if (edd_has_variable_prices($download->ID)) : ?>
                    <p><?php _e('Variable pricing', 'edd-customer-dashboard-pro'); ?></p>
                <?php else : ?>
                    <p><?php echo $dashboard_data->format_currency(edd_get_download_price($download->ID)); ?></p>
                <?php endif; ?>
                
                <div style="margin-top: 15px;">
                    <a href="<?php echo get_permalink($download->ID); ?>" class="eddcdp-btn">
                        🛒 <?php _e('View Product', 'edd-customer-dashboard-pro'); ?>
                    </a>
                    <button class="eddcdp-btn eddcdp-btn-secondary eddcdp-remove-wishlist" data-download-id="<?php echo $download->ID; ?>">
                        ❌ <?php _e('Remove', 'edd-customer-dashboard-pro'); ?>
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else : ?>
    <div class="eddcdp-empty-state">
        <div class="eddcdp-empty-icon">❤️</div>
        <h3><?php _e('Your wishlist is empty', 'edd-customer-dashboard-pro'); ?></h3>
        <p><?php _e('Save items for later purchase by adding them to your wishlist.', 'edd-customer-dashboard-pro'); ?></p>
        <a href="<?php echo home_url(); ?>" class="eddcdp-btn"><?php _e('Browse Products', 'edd-customer-dashboard-pro'); ?></a>
    </div>
<?php endif; ?>