<?php
/**
 * Template Header - Updated with Fullscreen Exit
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Check if we're in fullscreen mode
$is_fullscreen = defined('EDDCDP_IS_FULLSCREEN') && EDDCDP_IS_FULLSCREEN;


add_action('wp_enqueue_scripts', 'enqueue_tailwind_cdn', 100);

function enqueue_tailwind_cdn() {
    wp_enqueue_script('tailwindcdn', 'https://cdn.tailwindcss.com', [], null, false);
    wp_enqueue_script('alpinecdn', 'https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js', [], null, true);
}

if ($is_fullscreen) {
    // Fullscreen mode - complete HTML
    ?>
    <!DOCTYPE html>
    <html <?php language_attributes(); ?>>
    <head>
        <meta charset="<?php bloginfo('charset'); ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php _e('Customer Dashboard', 'edd-customer-dashboard-pro'); ?> - <?php bloginfo('name'); ?></title>
        <script src="https://cdn.tailwindcss.com"></script>
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        animation: {
                            'fade-in': 'fadeIn 0.5s ease-in-out',
                            'slide-up': 'slideUp 0.4s ease-out',
                            'pulse-slow': 'pulse 3s infinite',
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
        </script>
        <?php 
        // Enqueue template CSS
        $settings = get_option('eddcdp_settings', array());
        $active_template = isset($settings['active_template']) ? $settings['active_template'] : 'default';
        $template_css = EDDCDP_PLUGIN_URL . 'templates/' . $active_template . '/style.css';
        if (file_exists(EDDCDP_PLUGIN_DIR . 'templates/' . $active_template . '/style.css')) :
        ?>
        <link rel="stylesheet" href="<?php echo $template_css; ?>?v=<?php echo EDDCDP_VERSION; ?>">
        <?php endif; ?>
    </head>
    <body class="bg-gradient-to-br from-indigo-50 via-white to-purple-50 min-h-screen eddcdp-fullscreen-mode">
        
        <!-- Fullscreen Exit Button -->
        <?php EDDCDP_Fullscreen_Helper::render_exit_button(); ?>
        
    <?php
} else {
    // Embedded mode - just scripts, styles, and wrapper
    ?>

    
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    animation: {
                        'fade-in': 'fadeIn 0.5s ease-in-out',
                        'slide-up': 'slideUp 0.4s ease-out',
                        'pulse-slow': 'pulse 3s infinite',
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
    </script>
    
    <div class="eddcdp-dashboard-wrapper">
        <div class="eddcdp-embedded-wrapper">
    <?php
}