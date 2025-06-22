<?php
/**
 * Default Dashboard Template - Layout Only
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get current user and settings
$current_user = wp_get_current_user();
$settings = get_option('eddcdp_settings', array());
$enabled_sections = isset($settings['enabled_sections']) ? $settings['enabled_sections'] : array();

// Include header
include 'header.php';
?>

<div class="eddcdp-dashboard" x-data="dashboard()">
    <div class="container mx-auto px-4 py-8 max-w-7xl">
        
        <!-- Welcome Header Section -->
        <?php include 'sections/welcome.php'; ?>

        <!-- Stats Grid Section -->
        <?php include 'sections/stats.php'; ?>

        <!-- Navigation Tabs Section -->
        <?php include 'sections/navigation.php'; ?>

        <!-- Content Area -->
        <div class="bg-white/80 backdrop-blur-xl rounded-2xl shadow-lg border border-white/20 min-h-[600px]">
            
            <!-- Tab Content Sections -->
            <?php if (!empty($enabled_sections['purchases'])) : ?>
            <div x-show="activeTab === 'purchases'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0" class="p-8">
                <?php include 'sections/purchases.php'; ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($enabled_sections['downloads'])) : ?>
            <div x-show="activeTab === 'downloads'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0" class="p-8">
                <?php include 'sections/downloads.php'; ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($enabled_sections['licenses'])) : ?>
            <div x-show="activeTab === 'licenses'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0" class="p-8">
                <?php include 'sections/licenses.php'; ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($enabled_sections['wishlist'])) : ?>
            <div x-show="activeTab === 'wishlist'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0" class="p-8">
                <?php include 'sections/wishlist.php'; ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($enabled_sections['analytics'])) : ?>
            <div x-show="activeTab === 'analytics'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0" class="p-8">
                <?php include 'sections/analytics.php'; ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($enabled_sections['support'])) : ?>
            <div x-show="activeTab === 'support'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0" class="p-8">
                <?php include 'sections/support.php'; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Dashboard JavaScript -->
<?php include 'sections/script.php'; ?>

<?php
// Include footer
include 'footer.php';
?>