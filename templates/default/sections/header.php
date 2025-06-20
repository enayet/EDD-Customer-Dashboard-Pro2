<?php
/**
 * Enhanced Header Section Template with Receipt Mode Support
 */

if (!defined('ABSPATH')) {
    exit;
}

$current_view_mode = isset($view_mode) ? $view_mode : 'dashboard';
?>

<!-- Dashboard Header -->
<div class="eddcdp-dashboard-header">
    <div class="eddcdp-welcome-section">
        <div class="eddcdp-welcome-text">
            <?php if ($current_view_mode === 'receipt' && isset($payment)) : ?>
                <h1><?php esc_html_e('Order Receipt', 'edd-customer-dashboard-pro'); ?></h1>
                <p>
                    <?php 
                    // translators: %1$s is the order number, %2$s is the customer name
                    printf(
                        esc_html__('Order #%1$s for %2$s', 'edd-customer-dashboard-pro'), 
                        esc_html($payment->number),
                        esc_html($user->display_name)
                    ); 
                    ?>
                </p>
            <?php else : ?>
                <h1><?php 
                // translators: %s is the user's display name
                printf(esc_html__('Welcome back, %s!', 'edd-customer-dashboard-pro'), esc_html($user->display_name)); 
                ?></h1>
                <p><?php esc_html_e('Manage your purchases, downloads, and account settings', 'edd-customer-dashboard-pro'); ?></p>
            <?php endif; ?>
        </div>
        <div class="eddcdp-user-avatar">
            <?php echo esc_html($dashboard_data->get_user_initials($user->display_name)); ?>
        </div>
    </div>
</div>