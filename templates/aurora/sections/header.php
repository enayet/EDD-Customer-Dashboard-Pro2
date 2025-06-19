<?php
/**
 * Aurora Template - Header Section
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
            printf(esc_html__('My Digital Products', 'edd-customer-dashboard-pro')); 
            ?></h1>
            <p><?php esc_html_e('Manage your purchases, downloads, and licenses', 'edd-customer-dashboard-pro'); ?></p>
        </div>
        <div class="eddcdp-search-bar">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="<?php esc_attr_e('Search products...', 'edd-customer-dashboard-pro'); ?>" id="eddcdp-search-input">
        </div>
    </div>
</div>