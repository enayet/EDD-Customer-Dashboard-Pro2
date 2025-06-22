<?php
/**
 * Template Footer
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Check if we're in fullscreen mode
$settings = get_option('eddcdp_settings', array());
$fullscreen = !empty($settings['fullscreen_mode']);

if ($fullscreen) {
    // Fullscreen mode - close HTML
    ?>
    <?php wp_footer(); ?>
    </body>
    </html>
    <?php
}
// If not fullscreen, we don't need to output anything as we're within WordPress content area