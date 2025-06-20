<?php
/**
 * Enhanced Default Dashboard Template with Receipt/Invoice Support
 */

if (!defined('ABSPATH')) {
    exit;
}

$template_loader = eddcdp()->get_template_loader();
$active_template = $template_loader->get_active_template();

// Determine what to display based on view mode and parameters
$view_mode = isset($view_mode) ? $view_mode : 'dashboard';

// Check if we should show invoice instead of receipt
$show_invoice = false;
if ($view_mode === 'receipt' && isset($_GET['view']) && $_GET['view'] === 'invoice') {
    $show_invoice = true;
}

?>

<div class="eddcdp-dashboard-container">
    <?php if ($view_mode === 'receipt' && isset($payment)) : ?>
        
        <!-- Receipt/Invoice View Mode -->
        <?php
        // Load header section with back to dashboard link
        echo $template_loader->load_section('header', null, compact('user', 'customer', 'dashboard_data', 'view_mode', 'payment'));
        ?>
        
        <!-- Dashboard Content -->
        <div class="eddcdp-dashboard-content">
            <?php if ($show_invoice) : ?>
                <!-- Invoice View -->
                <div class="eddcdp-content-section active" id="eddcdp-invoice">
                    <?php
                    // Load invoice section
                    echo $template_loader->load_section('invoice', null, compact('payment', 'payment_key', 'dashboard_data', 'user'));
                    ?>
                </div>
            <?php else : ?>
                <!-- Receipt/Order Details View -->
                <div class="eddcdp-content-section active" id="eddcdp-receipt">
                    <?php
                    // Load receipt section
                    echo $template_loader->load_section('receipt', null, compact('payment', 'payment_key', 'dashboard_data', 'user'));
                    ?>
                </div>
            <?php endif; ?>
        </div>
        
    <?php else : ?>
        
        <!-- Normal Dashboard Mode -->
        <?php
        // Load header section using active template
        echo $template_loader->load_section('header', null, compact('user', 'customer', 'dashboard_data', 'view_mode'));
        
        // Load stats section using active template  
        echo $template_loader->load_section('stats', null, compact('user', 'customer', 'dashboard_data'));
        
        // Load navigation section using active template
        echo $template_loader->load_section('navigation', null, compact('enabled_sections'));
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
            
        <?php endif; ?>
    </div>
    
</div>