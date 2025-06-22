<?php
/**
 * Admin Settings Page View - Clean Modern Version
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get current settings
$settings = get_option('eddcdp_settings', array());
$active_template = isset($settings['active_template']) ? $settings['active_template'] : 'default';
$replace_edd_pages = isset($settings['replace_edd_pages']) ? $settings['replace_edd_pages'] : false;
$fullscreen_mode = isset($settings['fullscreen_mode']) ? $settings['fullscreen_mode'] : false;
$enabled_sections = isset($settings['enabled_sections']) ? $settings['enabled_sections'] : array();

// Get template manager and available templates
$template_manager = eddcdp()->get_template_manager();
$available_templates = array();

if ($template_manager) {
    $available_templates = $template_manager->get_available_templates();
}

// Fallback if no templates found
if (empty($available_templates)) {
    $available_templates = array(
        'default' => array(
            'name' => 'Default Dashboard',
            'description' => 'Modern, clean dashboard interface',
            'version' => '1.0.0',
            'author' => 'EDD Customer Dashboard Pro'
        )
    );
}

// Available sections
$available_sections = array(
    'purchases' => __('Purchases', 'edd-customer-dashboard-pro'),
    'downloads' => __('Downloads', 'edd-customer-dashboard-pro'),
    'licenses' => __('Licenses', 'edd-customer-dashboard-pro'),
    'wishlist' => __('Wishlist', 'edd-customer-dashboard-pro'),
    'analytics' => __('Analytics', 'edd-customer-dashboard-pro'),
    'support' => __('Support', 'edd-customer-dashboard-pro')
);
?>

<div class="wrap eddcdp-admin">
    <h1><?php esc_html_e('EDD Customer Dashboard Pro Settings', 'edd-customer-dashboard-pro'); ?></h1>
    
    <div class="eddcdp-admin-wrapper">
        <div class="eddcdp-admin-main">
            <!-- General Settings -->
            <div class="eddcdp-settings-section">
                <h2><?php esc_html_e('General Settings', 'edd-customer-dashboard-pro'); ?></h2>
                <form method="post" action="">
                    <?php wp_nonce_field('eddcdp_save_settings', 'eddcdp_settings_nonce'); ?>
                    <input type="hidden" name="eddcdp_save_settings" value="1" />
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php esc_html_e('Replace EDD Pages', 'edd-customer-dashboard-pro'); ?></th>
                            <td>
                                <label class="eddcdp-toggle">
                                    <input type="checkbox" name="replace_edd_pages" value="1" <?php checked($replace_edd_pages, true); ?> />
                                    <span class="eddcdp-toggle-slider"></span>
                                </label>
                                <p class="description"><?php esc_html_e('Replace default EDD customer pages with Dashboard Pro', 'edd-customer-dashboard-pro'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('Fullscreen Mode', 'edd-customer-dashboard-pro'); ?></th>
                            <td>
                                <label class="eddcdp-toggle">
                                    <input type="checkbox" name="fullscreen_mode" value="1" <?php checked($fullscreen_mode, true); ?> />
                                    <span class="eddcdp-toggle-slider"></span>
                                </label>
                                <p class="description"><?php esc_html_e('Enable automatic fullscreen dashboard mode', 'edd-customer-dashboard-pro'); ?></p>
                                
                                <?php if ($fullscreen_mode) : ?>
                                    <div class="eddcdp-fullscreen-preview" style="margin-top: 15px; padding: 15px; background: #f0f8ff; border-left: 4px solid #667eea; border-radius: 4px;">
                                        <h4 style="margin: 0 0 10px 0; color: #333;">🚀 <?php esc_html_e('Auto Full Screen Mode Active', 'edd-customer-dashboard-pro'); ?></h4>
                                        <ul style="margin: 0; padding-left: 20px;">
                                            <li><?php esc_html_e('All dashboard pages automatically open in full screen', 'edd-customer-dashboard-pro'); ?></li>
                                            <li><?php esc_html_e('Clean URLs - no parameters needed', 'edd-customer-dashboard-pro'); ?></li>
                                            <li><?php esc_html_e('"Back to Site" button returns to homepage or referrer', 'edd-customer-dashboard-pro'); ?></li>
                                            <li><?php esc_html_e('ESC key exits to main site', 'edd-customer-dashboard-pro'); ?></li>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                    
                    <h3><?php esc_html_e('Dashboard Sections', 'edd-customer-dashboard-pro'); ?></h3>
                    <p class="description"><?php esc_html_e('Enable or disable specific dashboard sections.', 'edd-customer-dashboard-pro'); ?></p>
                    
                    <table class="form-table">
                        <?php foreach ($available_sections as $section_key => $section_name) : ?>
                            <?php $is_enabled = isset($enabled_sections[$section_key]) ? $enabled_sections[$section_key] : true; ?>
                            <tr>
                                <th scope="row"><?php echo esc_html($section_name); ?></th>
                                <td>
                                    <label class="eddcdp-toggle">
                                        <input type="checkbox" name="enabled_sections[<?php echo esc_attr($section_key); ?>]" value="1" <?php checked($is_enabled, true); ?> />
                                        <span class="eddcdp-toggle-slider"></span>
                                    </label>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                    
                    <?php submit_button(__('Save Settings', 'edd-customer-dashboard-pro')); ?>
                </form>
            </div>
            
            <!-- Template Selection -->
            <div class="eddcdp-templates-section">
                <h2><?php esc_html_e('Template Settings', 'edd-customer-dashboard-pro'); ?></h2>
                <p class="description"><?php esc_html_e('Choose and configure your dashboard template.', 'edd-customer-dashboard-pro'); ?></p>
                
                <div class="eddcdp-template-grid">
                    <?php foreach ($available_templates as $template_key => $template_info) : ?>
                        <?php $is_active = ($active_template === $template_key); ?>
                        <div class="eddcdp-template-card <?php echo $is_active ? 'active' : ''; ?>">
                            <div class="eddcdp-template-preview">
                                <?php
                                $screenshot = false;
                                if ($template_manager && method_exists($template_manager, 'get_template_screenshot')) {
                                    $screenshot = $template_manager->get_template_screenshot($template_key);
                                }
                                
                                if ($screenshot) :
                                ?>
                                    <img src="<?php echo esc_url($screenshot); ?>" 
                                         alt="<?php echo esc_attr(sprintf(__('Screenshot of %s template', 'edd-customer-dashboard-pro'), $template_info['name'])); ?>" 
                                         class="eddcdp-template-screenshot" 
                                         loading="lazy" />
                                <?php else : ?>
                                    <div class="eddcdp-no-screenshot">
                                        <span class="dashicons dashicons-admin-appearance"></span>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($is_active) : ?>
                                    <div class="eddcdp-active-badge">
                                        <span class="dashicons dashicons-yes-alt"></span>
                                        <?php esc_html_e('Active', 'edd-customer-dashboard-pro'); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="eddcdp-template-info">
                                <h4 class="eddcdp-template-name"><?php echo esc_html($template_info['name']); ?></h4>
                                <p class="eddcdp-template-description"><?php echo esc_html($template_info['description']); ?></p>
                                
                                <div class="eddcdp-template-meta">
                                    <span class="eddcdp-template-version">
                                        <?php printf(__('Version: %s', 'edd-customer-dashboard-pro'), esc_html($template_info['version'])); ?>
                                    </span>
                                    <?php if (isset($template_info['author'])) : ?>
                                        <span class="eddcdp-template-author">
                                            <?php printf(__('by %s', 'edd-customer-dashboard-pro'), esc_html($template_info['author'])); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="eddcdp-template-actions">
                                    <?php if ($is_active) : ?>
                                        <button type="button" class="button button-secondary" disabled>
                                            <span class="dashicons dashicons-yes-alt"></span>
                                            <?php esc_html_e('Active', 'edd-customer-dashboard-pro'); ?>
                                        </button>
                                    <?php else : ?>
                                        <a href="<?php echo esc_url(wp_nonce_url(
                                            add_query_arg(array(
                                                'page' => 'eddcdp-settings',
                                                'activate_template' => $template_key
                                            ), admin_url('edit.php?post_type=download')),
                                            'activate_template_' . $template_key
                                        )); ?>" class="button button-primary eddcdp-activate-btn">
                                            <?php esc_html_e('Activate', 'edd-customer-dashboard-pro'); ?>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <div class="eddcdp-admin-sidebar">
            <div class="eddcdp-info-box">
                <h3><?php esc_html_e('How to Use', 'edd-customer-dashboard-pro'); ?></h3>
                <p><?php esc_html_e('Use this shortcode to display the dashboard:', 'edd-customer-dashboard-pro'); ?></p>
                <code>[edd_customer_dashboard_pro]</code>

                <h4><?php esc_html_e('Plugin Info', 'edd-customer-dashboard-pro'); ?></h4>
                <p><strong><?php esc_html_e('Version:', 'edd-customer-dashboard-pro'); ?></strong> <?php echo esc_html(EDDCDP_VERSION); ?></p>
                <p><strong><?php esc_html_e('Active Template:', 'edd-customer-dashboard-pro'); ?></strong> <?php echo esc_html(ucfirst($active_template)); ?></p>
                
                <?php if ($fullscreen_mode) : ?>
                    <div style="margin-top: 20px; padding: 15px; background: #e8f5e8; border-radius: 8px; border: 1px solid #4caf50;">
                        <h4 style="margin: 0 0 10px 0; color: #2e7d32;">🔍 <?php esc_html_e('Full Screen Mode Active', 'edd-customer-dashboard-pro'); ?></h4>
                        <p style="margin: 0; font-size: 13px; color: #333;">
                            <?php esc_html_e('Customers will see a clean, immersive dashboard experience.', 'edd-customer-dashboard-pro'); ?>
                        </p>
                    </div>
                <?php endif; ?>

                <h4><?php esc_html_e('Quick Actions', 'edd-customer-dashboard-pro'); ?></h4>
                <p>
                    <a href="<?php echo esc_url(add_query_arg(array('eddcdp_action' => 'flush_cache', '_wpnonce' => wp_create_nonce('eddcdp_flush_cache')))); ?>" class="button button-secondary">
                        <?php esc_html_e('Clear Cache', 'edd-customer-dashboard-pro'); ?>
                    </a>
                </p>

                <h4><?php esc_html_e('System Status', 'edd-customer-dashboard-pro'); ?></h4>
                <ul style="margin: 0; padding-left: 0; list-style: none;">
                    <li style="margin-bottom: 8px;">
                        <?php if (class_exists('Easy_Digital_Downloads')) : ?>
                            <span style="color: #4caf50;">✅</span> <?php esc_html_e('EDD Active', 'edd-customer-dashboard-pro'); ?>
                        <?php else : ?>
                            <span style="color: #f44336;">❌</span> <?php esc_html_e('EDD Required', 'edd-customer-dashboard-pro'); ?>
                        <?php endif; ?>
                    </li>
                    <li style="margin-bottom: 8px;">
                        <?php if ($template_manager) : ?>
                            <span style="color: #4caf50;">✅</span> <?php esc_html_e('Templates Loaded', 'edd-customer-dashboard-pro'); ?>
                        <?php else : ?>
                            <span style="color: #ff9800;">⚠️</span> <?php esc_html_e('Template Issues', 'edd-customer-dashboard-pro'); ?>
                        <?php endif; ?>
                    </li>
                    <li style="margin-bottom: 8px;">
                        <?php if (class_exists('EDD_Software_Licensing')) : ?>
                            <span style="color: #4caf50;">✅</span> <?php esc_html_e('Licensing Support', 'edd-customer-dashboard-pro'); ?>
                        <?php else : ?>
                            <span style="color: #9e9e9e;">⚪</span> <?php esc_html_e('Licensing Optional', 'edd-customer-dashboard-pro'); ?>
                        <?php endif; ?>
                    </li>
                    <li style="margin-bottom: 8px;">
                        <?php if (class_exists('EDD_Wish_Lists')) : ?>
                            <span style="color: #4caf50;">✅</span> <?php esc_html_e('Wishlist Support', 'edd-customer-dashboard-pro'); ?>
                        <?php else : ?>
                            <span style="color: #9e9e9e;">⚪</span> <?php esc_html_e('Wishlist Optional', 'edd-customer-dashboard-pro'); ?>
                        <?php endif; ?>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>