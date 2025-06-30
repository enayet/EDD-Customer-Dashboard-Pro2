<?php
/**
 * Template Footer - Updated
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Check if we're in fullscreen mode
$is_fullscreen = defined('EDDCDP_IS_FULLSCREEN') && EDDCDP_IS_FULLSCREEN;

if ($is_fullscreen) {
    // Fullscreen mode - close HTML
    ?>
    
    <?php
    // Allow other plugins to add content before body close
    do_action('eddcdp_body_end');
    ?>
    
    </body>
    </html>
    <?php
} else {
    // Embedded mode - close wrappers
    ?>
    
    <?php
    // Allow other plugins to add content before wrapper close
    do_action('eddcdp_embedded_body_end');
    ?>
    
        </div> <!-- Close eddcdp-embedded-wrapper -->
    </div> <!-- Close eddcdp-dashboard-wrapper -->
    
    <?php
}