<?php
/**
 * Header Section Template - All escaping issues fixed
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<!-- Dashboard Header -->
<div class="eddcdp-dashboard-header">
    <div class="eddcdp-welcome-section">
        <div class="eddcdp-welcome-text">
            <h1><?php 
            // translators: %s is the user's display name
            printf(esc_html__('Welcome back, %s!', 'edd-customer-dashboard-pro'), esc_html($user->display_name)); 
            ?></h1>
            <p><?php esc_html_e('Manage your purchases, downloads, and account settings', 'edd-customer-dashboard-pro'); ?></p>
        </div>
        <div class="eddcdp-user-avatar">
            <?php echo esc_html($dashboard_data->get_user_initials($user->display_name)); ?>
        </div>
    </div>
</div>