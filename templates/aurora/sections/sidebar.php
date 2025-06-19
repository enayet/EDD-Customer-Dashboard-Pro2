<?php
/**
 * Aurora Template - Sidebar Section
 */

if (!defined('ABSPATH')) {
    exit;
}

// Build navigation items based on enabled sections
$nav_items = array();

if (isset($enabled_sections['purchases']) && $enabled_sections['purchases']) {
    $nav_items['purchases'] = array(
        'icon' => 'fas fa-box-open',
        'label' => esc_html__('My Products', 'edd-customer-dashboard-pro'),
        'badge' => edd_count_purchases_of_customer($customer->id)
    );
}

if (isset($enabled_sections['downloads']) && $enabled_sections['downloads']) {
    $nav_items['downloads'] = array(
        'icon' => 'fas fa-download',
        'label' => esc_html__('Downloads', 'edd-customer-dashboard-pro'),
        'badge' => null
    );
}

if (isset($enabled_sections['licenses']) && $enabled_sections['licenses']) {
    $active_licenses = $dashboard_data->get_active_licenses_count($customer->id);
    $nav_items['licenses'] = array(
        'icon' => 'fas fa-key',
        'label' => esc_html__('Licenses', 'edd-customer-dashboard-pro'),
        'badge' => $active_licenses > 0 ? $active_licenses . ' active' : null
    );
}

if (isset($enabled_sections['wishlist']) && $enabled_sections['wishlist']) {
    $wishlist_count = $dashboard_data->get_wishlist_count($user->ID);
    $nav_items['wishlist'] = array(
        'icon' => 'fas fa-heart',
        'label' => esc_html__('Wishlist', 'edd-customer-dashboard-pro'),
        'badge' => $wishlist_count > 0 ? $wishlist_count : null
    );
}

if (isset($enabled_sections['analytics']) && $enabled_sections['analytics']) {
    $nav_items['analytics'] = array(
        'icon' => 'fas fa-chart-line',
        'label' => esc_html__('Analytics', 'edd-customer-dashboard-pro'),
        'badge' => null
    );
}

if (isset($enabled_sections['support']) && $enabled_sections['support']) {
    $nav_items['support'] = array(
        'icon' => 'fas fa-question-circle',
        'label' => esc_html__('Support', 'edd-customer-dashboard-pro'),
        'badge' => null
    );
}
?>

<div class="eddcdp-user-profile">
    <div class="eddcdp-sidebar-avatar">
        <?php echo esc_html($dashboard_data->get_user_initials($user->display_name)); ?>
    </div>
    <h3 class="eddcdp-user-name"><?php echo esc_html($user->display_name); ?></h3>
    <p class="eddcdp-user-email"><?php echo esc_html($user->user_email); ?></p>
</div>

<ul class="eddcdp-sidebar-nav">
    <?php 
    $is_first = true;
    foreach ($nav_items as $section_key => $nav_item) : 
    ?>
        <li>
            <a href="#" class="eddcdp-nav-tab <?php echo $is_first ? 'active' : ''; ?>" data-section="<?php echo esc_attr($section_key); ?>">
                <i class="<?php echo esc_attr($nav_item['icon']); ?>"></i>
                <?php echo esc_html($nav_item['label']); ?>
                <?php if ($nav_item['badge']) : ?>
                    <span class="eddcdp-nav-badge"><?php echo esc_html($nav_item['badge']); ?></span>
                <?php endif; ?>
            </a>
        </li>
    <?php 
        $is_first = false;
    endforeach; 
    ?>
</ul>