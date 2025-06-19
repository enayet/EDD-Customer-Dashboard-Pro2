<?php
/**
 * Header Section Template
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<!-- Dashboard Header -->
<div class="eddcdp-dashboard-header">
    <div class="eddcdp-welcome-section">
        <div class="eddcdp-welcome-text">
            <h1><?php printf(__('Welcome back, %s!', EDDCDP_TEXT_DOMAIN), esc_html($user->display_name)); ?></h1>
            <p><?php _e('Manage your purchases, downloads, and account settings', EDDCDP_TEXT_DOMAIN); ?></p>
        </div>
        <div class="eddcdp-user-avatar">
            <?php echo esc_html($dashboard_data->get_user_initials($user->display_name)); ?>
        </div>
    </div>
</div>