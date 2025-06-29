<?php
/**
 * Template Header - Proper Asset Loading
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
    // Fullscreen mode - complete HTML with direct asset loading
    ?>
    <!DOCTYPE html>
    <html <?php language_attributes(); ?>>
    <head>
        <meta charset="<?php bloginfo('charset'); ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php esc_html_e('Customer Dashboard', 'edd-customer-dashboard-pro'); ?> - <?php bloginfo('name'); ?></title>
        
        <!-- Framework CSS (Tailwind) -->
        <link rel="stylesheet" href="<?php echo esc_url($template_url . 'assets/framework.css'); ?>?v=<?php echo esc_attr(EDDCDP_VERSION); ?>">
        
        <!-- Template CSS -->
        <link rel="stylesheet" href="<?php echo esc_url($template_url . 'style.css'); ?>?v=<?php echo esc_attr(EDDCDP_VERSION); ?>">
        
        <!-- Alpine.js -->
        <script defer src="<?php echo esc_url($template_url . 'assets/alpine.min.js'); ?>?v=<?php echo esc_attr(EDDCDP_VERSION); ?>"></script>
        
        <!-- Tailwind Config (after framework loads) -->
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof tailwind !== 'undefined') {
                tailwind.config = {
                    theme: {
                        extend: {
                            animation: {
                                'fade-in': 'fadeIn 0.5s ease-in-out',
                                'slide-up': 'slideUp 0.4s ease-out'
                            },
                            keyframes: {
                                fadeIn: {
                                    '0%': { opacity: '0', transform: 'translateY(10px)' },
                                    '100%': { opacity: '1', transform: 'translateY(0)' }
                                },
                                slideUp: {
                                    '0%': { opacity: '0', transform: 'translateY(20px)' },
                                    '100%': { opacity: '1', transform: 'translateY(0)' }
                                }
                            }
                        }
                    }
                }
            }
        });
        </script>
    </head>
    <body class="bg-gradient-to-br from-indigo-50 via-white to-purple-50 min-h-screen eddcdp-fullscreen-mode">
        
        <!-- Fullscreen Exit Button -->
        <a href="<?php echo esc_url(wp_get_referer() ?: home_url('/')); ?>" 
           class="eddcdp-fullscreen-exit">
            <?php esc_html_e('← Exit Dashboard', 'edd-customer-dashboard-pro'); ?>
        </a>
        
    <?php
} else {
    // Embedded mode - Output assets directly since wp_enqueue_scripts already fired
    ?>
    
    <!-- Framework CSS (Tailwind) -->
<!--    <link rel="stylesheet" href="<?php echo esc_url($template_url . 'assets/framework.css'); ?>?v=<?php echo esc_attr(EDDCDP_VERSION); ?>">-->
        <script src="https://cdn.tailwindcss.com/"></script>
    <!-- Template CSS -->
    <link rel="stylesheet" href="<?php echo esc_url($template_url . 'style.css'); ?>?v=<?php echo esc_attr(EDDCDP_VERSION); ?>">
    
    <!-- Alpine.js -->
    <script defer src="<?php echo esc_url($template_url . 'assets/alpine.min.js'); ?>?v=<?php echo esc_attr(EDDCDP_VERSION); ?>"></script>
    
    <!-- Tailwind Config (wait for framework to load) -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Wait a bit for Tailwind to load
        setTimeout(function() {
            if (typeof tailwind !== 'undefined') {
                tailwind.config = {
                    theme: {
                        extend: {
                            animation: {
                                'fade-in': 'fadeIn 0.5s ease-in-out',
                                'slide-up': 'slideUp 0.4s ease-out'
                            },
                            keyframes: {
                                fadeIn: {
                                    '0%': { opacity: '0', transform: 'translateY(10px)' },
                                    '100%': { opacity: '1', transform: 'translateY(0)' }
                                },
                                slideUp: {
                                    '0%': { opacity: '0', transform: 'translateY(20px)' },
                                    '100%': { opacity: '1', transform: 'translateY(0)' }
                                }
                            }
                        }
                    }
                }
            }
        }, 100);
    });
    </script>
    
    <div class="eddcdp-dashboard-wrapper">
        <div class="eddcdp-embedded-wrapper">
    <?php
}