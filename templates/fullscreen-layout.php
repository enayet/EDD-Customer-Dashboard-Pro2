<?php
/**
 * Fullscreen Layout Template
 * 
 * Template variables available:
 * - $template_data (array)
 * - $template_url (string)
 * - $back_url (string)
 * - $view_mode (string)
 * - $payment (object|null)
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php 
    if ($view_mode === 'receipt' && isset($payment)) {
        // translators: %s is the order number
        printf(esc_html__('Order #%s - %s', 'edd-customer-dashboard-pro'), esc_html($payment->number), get_bloginfo('name'));
    } else {
        // translators: %s is the site name
        printf(esc_html__('Customer Dashboard - %s', 'edd-customer-dashboard-pro'), get_bloginfo('name'));
    }
    ?></title>
    
    <?php if ($template_url) : ?>
        <link rel="stylesheet" href="<?php echo esc_url($template_url . 'style.css?v=' . EDDCDP_VERSION); ?>">
    <?php endif; ?>
    
    <link rel="stylesheet" href="<?php echo esc_url(EDDCDP_PLUGIN_URL . 'assets/fullscreen.css?v=' . EDDCDP_VERSION); ?>">
    
    <?php wp_head(); ?>
</head>
<body <?php body_class('eddcdp-fullscreen-mode'); ?>>
    
    <div class="eddcdp-fullscreen-wrapper">
        <!-- Full Screen Header -->
        <div class="eddcdp-fullscreen-header">
            <h1 class="eddcdp-fullscreen-title">
                <?php 
                if ($view_mode === 'receipt' && isset($payment)) {
                    // translators: %s is the order number
                    printf(esc_html__('Order #%s', 'edd-customer-dashboard-pro'), esc_html($payment->number));
                } else {
                    esc_html_e('Customer Dashboard', 'edd-customer-dashboard-pro');
                }
                ?>
            </h1>
            
            <div class="eddcdp-fullscreen-actions">
                <a href="<?php echo esc_url($back_url); ?>" class="eddcdp-back-to-site">
                    ← <?php esc_html_e('Back to Site', 'edd-customer-dashboard-pro'); ?>
                </a>
            </div>
        </div>
        
        <!-- Full Screen Content -->
        <div class="eddcdp-fullscreen-content">
            <?php
            // Load the dashboard template
            if ($this->template_loader) {
                do_action('eddcdp_fullscreen_dashboard_loaded', $template_data);
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Template loader output is already escaped
                echo $this->template_loader->load_template(null, $template_data);
            } else {
                echo '<p>' . esc_html__('Dashboard template not available.', 'edd-customer-dashboard-pro') . '</p>';
            }
            ?>
        </div>
    </div>
    
    <?php if ($template_url) : ?>
        <script src="<?php echo esc_url($template_url . 'script.js?v=' . EDDCDP_VERSION); ?>"></script>
    <?php endif; ?>
    
    <script src="<?php echo esc_url(EDDCDP_PLUGIN_URL . 'assets/fullscreen.js?v=' . EDDCDP_VERSION); ?>"></script>
    
    <?php wp_footer(); ?>
</body>
</html>