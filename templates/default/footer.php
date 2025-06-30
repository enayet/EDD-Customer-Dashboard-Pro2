<?php
/**
 * Template Footer - Updated Design
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
    </body>
    </html>
    <?php
} else {
    // Embedded mode - close wrappers
    ?>
        </div> <!-- Close eddcdp-embedded-wrapper -->
    </div> <!-- Close eddcdp-dashboard-wrapper -->
    <?php
}