<?php
/**
 * Default Dashboard Template
 * Main dashboard template file
 */

if (!defined('ABSPATH')) {
    exit;
}

$template_loader = eddcdp()->get_template_loader();
$template_name = 'default';
?>

<div class="eddcdp-dashboard-container">
    <?php
    // Load header section
    echo $template_loader->load_section($template_name, 'header', compact('user', 'customer', 'dashboard_data'));
    
    // Load stats section
    echo $template_loader->load_section($template_name, 'stats', compact('user', 'customer', 'dashboard_data'));
    
    // Load navigation section
    echo $template_loader->load_section($template_name, 'navigation', compact('enabled_sections'));
    ?>
    
    <!-- Dashboard Content -->
    <div class="eddcdp-dashboard-content">
        <?php if (isset($enabled_sections['purchases']) && $enabled_sections['purchases']) : ?>
            <div class="eddcdp-content-section active" id="eddcdp-purchases">
                <?php echo $template_loader->load_section($template_name, 'purchases', compact('customer', 'dashboard_data')); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($enabled_sections['downloads']) && $enabled_sections['downloads']) : ?>
            <div class="eddcdp-content-section" id="eddcdp-downloads">
                <?php echo $template_loader->load_section($template_name, 'downloads', compact('customer', 'dashboard_data')); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($enabled_sections['licenses']) && $enabled_sections['licenses']) : ?>
            <div class="eddcdp-content-section" id="eddcdp-licenses">
                <?php echo $template_loader->load_section($template_name, 'licenses', compact('user', 'customer', 'dashboard_data')); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($enabled_sections['wishlist']) && $enabled_sections['wishlist']) : ?>
            <div class="eddcdp-content-section" id="eddcdp-wishlist">
                <?php echo $template_loader->load_section($template_name, 'wishlist', compact('user', 'dashboard_data')); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($enabled_sections['analytics']) && $enabled_sections['analytics']) : ?>
            <div class="eddcdp-content-section" id="eddcdp-analytics">
                <?php echo $template_loader->load_section($template_name, 'analytics', compact('customer', 'dashboard_data')); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($enabled_sections['support']) && $enabled_sections['support']) : ?>
            <div class="eddcdp-content-section" id="eddcdp-support">
                <?php echo $template_loader->load_section($template_name, 'support', array()); ?>
            </div>
        <?php endif; ?>
    </div>
</div>