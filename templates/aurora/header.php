<?php
/**
 * Aurora Template Header
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Check if we're in fullscreen mode
$is_fullscreen = defined('EDDCDP_IS_FULLSCREEN') && EDDCDP_IS_FULLSCREEN;

// Get template info
$template_name = 'aurora';
$template_url = EDDCDP_PLUGIN_URL . 'templates/' . $template_name . '/';

if ($is_fullscreen) {
    // Fullscreen mode - complete HTML document
    ?>
    <!DOCTYPE html>
    <html <?php language_attributes(); ?>>
    <head>
        <meta charset="<?php bloginfo('charset'); ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php esc_html_e('Customer Dashboard', 'edd-customer-dashboard-pro'); ?> - <?php bloginfo('name'); ?></title>
        
        <!-- Font Awesome Icons -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous">
        
        <!-- Aurora Template CSS -->
        <link rel="stylesheet" href="<?php echo esc_url($template_url . 'style.css'); ?>?v=<?php echo esc_attr(EDDCDP_VERSION); ?>">
        
        <!-- WordPress Head -->
        <?php wp_head(); ?>
    </head>
    <body class="eddcdp-aurora-fullscreen">
        
        <!-- Fullscreen Exit Button -->
        <?php EDDCDP_Fullscreen_Helper::render_exit_button(); ?>
        
    <?php
} else {
    // Embedded mode - just load styles and scripts
    ?>
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous">
    
    <!-- Aurora Template CSS -->
    <link rel="stylesheet" href="<?php echo esc_url($template_url . 'style.css'); ?>?v=<?php echo esc_attr(EDDCDP_VERSION); ?>">
    
    <div class="eddcdp-dashboard-wrapper eddcdp-aurora-wrapper">
        <div class="eddcdp-embedded-wrapper">
    <?php
}

// Add custom CSS variables for theme customization
?>
<style>
:root {
    --aurora-primary: <?php echo esc_attr(apply_filters('eddcdp_aurora_primary_color', '#6c5ce7')); ?>;
    --aurora-primary-light: <?php echo esc_attr(apply_filters('eddcdp_aurora_primary_light', '#a29bfe')); ?>;
    --aurora-secondary: <?php echo esc_attr(apply_filters('eddcdp_aurora_secondary_color', '#00b894')); ?>;
    --aurora-danger: <?php echo esc_attr(apply_filters('eddcdp_aurora_danger_color', '#d63031')); ?>;
    --aurora-warning: <?php echo esc_attr(apply_filters('eddcdp_aurora_warning_color', '#fdcb6e')); ?>;
    --aurora-dark: <?php echo esc_attr(apply_filters('eddcdp_aurora_dark_color', '#2d3436')); ?>;
    --aurora-light: <?php echo esc_attr(apply_filters('eddcdp_aurora_light_color', '#f5f6fa')); ?>;
    --aurora-gray: <?php echo esc_attr(apply_filters('eddcdp_aurora_gray_color', '#636e72')); ?>;
    --aurora-gray-light: <?php echo esc_attr(apply_filters('eddcdp_aurora_gray_light', '#dfe6e9')); ?>;
}
</style>