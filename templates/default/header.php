<?php
/**
 * Template Header - Updated Design
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
        
        <!-- Alpine.js -->
        <script defer src="<?php echo esc_url($template_url . 'assets/alpine.min.js'); ?>?v=<?php echo esc_attr(EDDCDP_VERSION); ?>"></script>
    </head>
    <body>
        
        <!-- Fullscreen Exit Button -->
        <?php EDDCDP_Fullscreen_Helper::render_exit_button(); ?>
        
    <?php
} else {
    // Embedded mode - inject styles and scripts
    ?>
    
    <!-- Template CSS -->
    <link rel="stylesheet" href="<?php echo esc_url($template_url . 'style.css'); ?>?v=<?php echo esc_attr(EDDCDP_VERSION); ?>">
    
    <!-- Alpine.js -->
    <script defer src="<?php echo esc_url($template_url . 'assets/alpine.min.js'); ?>?v=<?php echo esc_attr(EDDCDP_VERSION); ?>"></script>
    
    <div class="eddcdp-dashboard-wrapper">
        <div class="eddcdp-embedded-wrapper">
    <?php
}