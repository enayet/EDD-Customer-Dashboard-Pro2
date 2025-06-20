<?php
/**
 * Aurora Dashboard Template - Main Template File
 */

if (!defined('ABSPATH')) {
    exit;
}

$template_loader = eddcdp()->get_template_loader();
$active_template = $template_loader->get_active_template();

?>

<div class="eddcdp-dashboard-container">
    <?php
    // Load header section
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Template loader output is already escaped
    echo $template_loader->load_section('header', null, compact('user', 'customer', 'dashboard_data'));
    ?>
    
    <!-- Sidebar Navigation -->
    <aside class="eddcdp-dashboard-sidebar">
        <?php
        // Load sidebar section
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Template loader output is already escaped
        echo $template_loader->load_section('sidebar', null, compact('user', 'customer', 'dashboard_data', 'enabled_sections'));
        ?>
    </aside>
    
    <?php
    // Load stats section
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Template loader output is already escaped
    echo $template_loader->load_section('stats', null, compact('user', 'customer', 'dashboard_data'));
    ?>
    
    <!-- Dashboard Content -->
    <div class="eddcdp-dashboard-content">
        <?php 
        $first_section = true;
        
        // Load enabled sections using active template
        if (isset($enabled_sections['purchases']) && $enabled_sections['purchases']) : ?>
            <div class="eddcdp-content-section <?php echo $first_section ? 'active' : ''; ?>" id="eddcdp-purchases">
                <?php 
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Template loader output is already escaped
                echo $template_loader->load_section('purchases', null, compact('customer', 'dashboard_data')); 
                ?>
            </div>
            <?php $first_section = false; ?>
        <?php endif; ?>

        <?php if (isset($enabled_sections['downloads']) && $enabled_sections['downloads']) : ?>
            <div class="eddcdp-content-section <?php echo $first_section ? 'active' : ''; ?>" id="eddcdp-downloads">
                <?php 
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Template loader output is already escaped
                echo $template_loader->load_section('downloads', null, compact('customer', 'dashboard_data')); 
                ?>
            </div>
            <?php $first_section = false; ?>
        <?php endif; ?>

        <?php if (isset($enabled_sections['licenses']) && $enabled_sections['licenses']) : ?>
            <div class="eddcdp-content-section <?php echo $first_section ? 'active' : ''; ?>" id="eddcdp-licenses">
                <?php 
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Template loader output is already escaped
                echo $template_loader->load_section('licenses', null, compact('user', 'customer', 'dashboard_data')); 
                ?>
            </div>
            <?php $first_section = false; ?>
        <?php endif; ?>

        <?php if (isset($enabled_sections['wishlist']) && $enabled_sections['wishlist']) : ?>
            <div class="eddcdp-content-section <?php echo $first_section ? 'active' : ''; ?>" id="eddcdp-wishlist">
                <?php 
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Template loader output is already escaped
                echo $template_loader->load_section('wishlist', null, compact('user', 'dashboard_data')); 
                ?>
            </div>
            <?php $first_section = false; ?>
        <?php endif; ?>

        <?php if (isset($enabled_sections['analytics']) && $enabled_sections['analytics']) : ?>
            <div class="eddcdp-content-section <?php echo $first_section ? 'active' : ''; ?>" id="eddcdp-analytics">
                <?php 
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Template loader output is already escaped
                echo $template_loader->load_section('analytics', null, compact('customer', 'dashboard_data')); 
                ?>
            </div>
            <?php $first_section = false; ?>
        <?php endif; ?>

        <?php if (isset($enabled_sections['support']) && $enabled_sections['support']) : ?>
            <div class="eddcdp-content-section <?php echo $first_section ? 'active' : ''; ?>" id="eddcdp-support">
                <?php 
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Template loader output is already escaped
                echo $template_loader->load_section('support', null, array()); 
                ?>
            </div>
            <?php $first_section = false; ?>
        <?php endif; ?>
        
        <?php if ($first_section) : ?>
            <!-- No sections enabled fallback -->
            <div class="eddcdp-empty-state">
                <div class="eddcdp-empty-icon">⚙️</div>
                <h3><?php esc_html_e('No sections enabled', 'edd-customer-dashboard-pro'); ?></h3>
                <p><?php esc_html_e('Please enable at least one dashboard section in the settings.', 'edd-customer-dashboard-pro'); ?></p>
            </div>
        <?php endif; ?>
    </div>
</div>