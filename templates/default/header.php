<?php
/**
 * Template Header - Updated to remove Alpine.js
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Check if we're in fullscreen mode
$is_fullscreen = defined('EDDCDP_IS_FULLSCREEN') && EDDCDP_IS_FULLSCREEN;

// Get template info
$template_name = 'default';
$template_url = EDDCDP_PLUGIN_URL . 'templates/' . $template_name . '/';

if ($is_fullscreen) {
    // Fullscreen mode - complete HTML
    ?>
    <!DOCTYPE html>
    <html <?php language_attributes(); ?>>
    <head>
        <meta charset="<?php bloginfo('charset'); ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php esc_html_e('Customer Dashboard', 'edd-customer-dashboard-pro'); ?> - <?php bloginfo('name'); ?></title>
        
        <!-- Template CSS -->
        <link rel="stylesheet" href="<?php echo esc_url($template_url . 'style.css'); ?>?v=<?php echo esc_attr(EDDCDP_VERSION); ?>">
        
        <!-- Template JavaScript -->
<!--        <script src="<?php echo esc_url($template_url . 'assets/dashboard.js'); ?>?v=<?php echo esc_attr(EDDCDP_VERSION); ?>"></script>-->
        
        <?php
        // Allow other plugins to add head content
        do_action('eddcdp_head');
        ?>
    </head>
    <body class="eddcdp-fullscreen-mode">
        
        <!-- Fullscreen Exit Button -->
        <?php EDDCDP_Fullscreen_Helper::render_exit_button(); ?>
        
        <?php
        // Allow other plugins to add body content
        do_action('eddcdp_body_start');
        ?>
        
    <?php
} else {
    // Embedded mode - inject styles and scripts into existing page
    ?>
    
    <!-- Template CSS -->
    <link rel="stylesheet" href="<?php echo esc_url($template_url . 'style.css'); ?>?v=<?php echo esc_attr(EDDCDP_VERSION); ?>">
    
    <!-- Template JavaScript -->
    <script src="<?php echo esc_url($template_url . 'assets/dashboard.js'); ?>?v=<?php echo esc_attr(EDDCDP_VERSION); ?>"></script>
    
    <?php
    // Allow other plugins to add content
    do_action('eddcdp_embedded_head');
    ?>
    
    <div class="eddcdp-dashboard-wrapper">
        <div class="eddcdp-embedded-wrapper">
    <?php
    
    // Allow other plugins to add body content for embedded mode
    //do_action('eddcdp_embedded_body_start');
}