<?php
/**
 * Navigation Section Template
 */

if (!defined('ABSPATH')) {
    exit;
}

$nav_items = array();

if (isset($enabled_sections['purchases']) && $enabled_sections['purchases']) {
    $nav_items['purchases'] = array(
        'icon' => '📦',
        'label' => __('Purchases', 'edd-customer-dashboard-pro')
    );
}

if (isset($enabled_sections['downloads']) && $enabled_sections['downloads']) {
    $nav_items['downloads'] = array(
        'icon' => '⬇️',
        'label' => __('Downloads', 'edd-customer-dashboard-pro')
    );
}

if (isset($enabled_sections['licenses']) && $enabled_sections['licenses']) {
    $nav_items['licenses'] = array(
        'icon' => '🔑',
        'label' => __('Licenses', 'edd-customer-dashboard-pro')
    );
}

if (isset($enabled_sections['wishlist']) && $enabled_sections['wishlist']) {
    $nav_items['wishlist'] = array(
        'icon' => '❤️',
        'label' => __('Wishlist', 'edd-customer-dashboard-pro')
    );
}

if (isset($enabled_sections['analytics']) && $enabled_sections['analytics']) {
    $nav_items['analytics'] = array(
        'icon' => '📊',
        'label' => __('Analytics', 'edd-customer-dashboard-pro')
    );
}

if (isset($enabled_sections['support']) && $enabled_sections['support']) {
    $nav_items['support'] = array(
        'icon' => '💬',
        'label' => __('Support', 'edd-customer-dashboard-pro')
    );
}
?>

<!-- Navigation Tabs -->
<div class="eddcdp-dashboard-nav">
    <div class="eddcdp-nav-tabs">
        <?php 
        $is_first = true;
        foreach ($nav_items as $section_key => $nav_item) : 
        ?>
            <a href="#" class="eddcdp-nav-tab <?php echo $is_first ? 'active' : ''; ?>" data-section="<?php echo esc_attr($section_key); ?>">
                <?php echo $nav_item['icon']; ?> <?php echo esc_html($nav_item['label']); ?>
            </a>
        <?php 
            $is_first = false;
        endforeach; 
        ?>
    </div>
</div>