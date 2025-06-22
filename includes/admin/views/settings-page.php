<?php
/**
 * Admin Settings Page View
 * 
 * Clean, separated view for admin settings
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

// Get available templates
$template_loader = eddcdp()->get_template_loader();
$available_templates = $template_loader ? $template_loader->get_available_templates() : array();

// Fallback if no templates found
if (empty($available_templates)) {
    $available_templates = array(
        'default' => array(
            'name' => esc_html__('Default Dashboard', 'edd-customer-dashboard-pro'),
            'description' => esc_html__('Modern, clean dashboard interface', 'edd-customer-dashboard-pro'),
            'version' => '1.0.0',
            'author' => esc_html__('EDD Customer Dashboard Pro', 'edd-customer-dashboard-pro')
        )
    );
}

// Available sections
$available_sections = array(
    'purchases' => esc_html__('Purchases', 'edd-customer-dashboard-pro'),
    'downloads' => esc_html__('Downloads', 'edd-customer-dashboard-pro'),
    'licenses' => esc_html__('Licenses', 'edd-customer-dashboard-pro'),
    'wishlist' => esc_html__('Wishlist', 'edd-customer-dashboard-pro'),
    'analytics' => esc_html__('Analytics', 'edd-customer-dashboard-pro'),
    'support' => esc_html__('Support', 'edd-customer-dashboard-pro')
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
                                <label>
                                    <input type="checkbox" name="replace_edd_pages" value="1" <?php checked($replace_edd_pages, true); ?> />
                                    <?php esc_html_e('Replace default EDD customer pages with Dashboard Pro', 'edd-customer-dashboard-pro'); ?>
                                </label>
                                <p class="description"><?php esc_html_e('When enabled, all EDD customer dashboard pages will automatically open in full screen mode by default.', 'edd-customer-dashboard-pro'); ?></p>

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
                    
                    <?php submit_button(esc_html__('Save Settings', 'edd-customer-dashboard-pro')); ?>
                </form>
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
            </div>
        </div>
    </div>
    
    <!-- Template Selection -->
    <div class="eddcdp-admin-wrapper">
        <div class="eddcdp-admin-main">
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
                                if ($template_loader) {
                                    $screenshot = $template_loader->get_template_screenshot($template_key);
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
                                        <?php printf(esc_html__('Version: %s', 'edd-customer-dashboard-pro'), esc_html($template_info['version'])); ?>
                                    </span>
                                    <?php if (isset($template_info['author'])) : ?>
                                        <span class="eddcdp-template-author">
                                            <?php printf(esc_html__('by %s', 'edd-customer-dashboard-pro'), esc_html($template_info['author'])); ?>
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
    </div>
</div>

<style>
.eddcdp-admin-wrapper {
    display: flex;
    gap: 30px;
    margin-bottom: 30px;
}

.eddcdp-admin-main {
    flex: 1;
}

.eddcdp-admin-sidebar {
    width: 300px;
}

.eddcdp-settings-section {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
}

.eddcdp-info-box {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 8px;
    padding: 20px;
}

.eddcdp-info-box h3 {
    margin-top: 0;
}

.eddcdp-info-box code {
    display: block;
    margin: 10px 0;
    padding: 10px;
    background: #f6f7f7;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.eddcdp-toggle {
    position: relative;
    display: inline-block;
    width: 60px;
    height: 34px;
}

.eddcdp-toggle input {
    opacity: 0;
    width: 0;
    height: 0;
}

.eddcdp-toggle-slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: .4s;
    border-radius: 34px;
}

.eddcdp-toggle-slider:before {
    position: absolute;
    content: "";
    height: 26px;
    width: 26px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    transition: .4s;
    border-radius: 50%;
}

.eddcdp-toggle input:checked + .eddcdp-toggle-slider {
    background-color: #2196F3;
}

.eddcdp-toggle input:checked + .eddcdp-toggle-slider:before {
    transform: translateX(26px);
}

.eddcdp-template-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.eddcdp-template-card {
    border: 1px solid #ddd;
    border-radius: 8px;
    overflow: hidden;
    background: #fff;
    transition: all 0.3s ease;
}

.eddcdp-template-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.eddcdp-template-card.active {
    border-color: #2196F3;
    box-shadow: 0 0 0 2px rgba(33, 150, 243, 0.2);
}

.eddcdp-template-preview {
    position: relative;
    height: 200px;
    background: #f6f7f7;
    display: flex;
    align-items: center;
    justify-content: center;
}

.eddcdp-template-screenshot {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.eddcdp-no-screenshot .dashicons {
    font-size: 48px;
    color: #999;
}

.eddcdp-active-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    background: #2196F3;
    color: white;
    padding: 5px 10px;
    border-radius: 4px;
    font-size: 12px;
    display: flex;
    align-items: center;
    gap: 5px;
}

.eddcdp-template-info {
    padding: 20px;
}

.eddcdp-template-name {
    margin: 0 0 10px 0;
    font-size: 18px;
}

.eddcdp-template-description {
    color: #666;
    margin: 0 0 15px 0;
}

.eddcdp-template-meta {
    font-size: 12px;
    color: #999;
    margin-bottom: 15px;
}

.eddcdp-template-meta span {
    display: block;
    margin-bottom: 5px;
}

.eddcdp-template-actions {
    text-align: right;
}

@media (max-width: 1200px) {
    .eddcdp-admin-wrapper {
        flex-direction: column;
    }
    
    .eddcdp-admin-sidebar {
        width: 100%;
    }
}
</style>