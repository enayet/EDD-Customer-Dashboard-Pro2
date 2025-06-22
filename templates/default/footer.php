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
?>

</div> <!-- .eddcdp-wrapper -->

<?php if ($fullscreen) : ?>
    <?php wp_footer(); ?>
</body>
</html>
<?php else : ?>
    <?php get_footer(); ?>
<?php endif; ?>