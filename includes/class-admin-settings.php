<?php
/**
 * Enhanced Admin Settings Class with Full Screen Mode Support
 */

if (!defined('ABSPATH')) {
    exit;
}

class EDDCDP_Admin_Settings {
    
    private $settings_slug = 'eddcdp-settings';
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_init', array($this, 'handle_form_submissions'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }
    
    /**
     * Register settings (for WordPress compatibility)
     */
    
    public function register_settings() {
        register_setting(
            'eddcdp_settings_group', 
            'eddcdp_settings',
            array(
                'type' => 'array',
                'sanitize_callback' => array($this, 'sanitize_settings'),
                'default' => array()
            )
        );
    }

    /**
     * Sanitize settings before saving
     */    

    public function sanitize_settings($settings) {
        if (!is_array($settings)) {
            return array();
        }

        $sanitized = array();

        // Sanitize active template
        if (isset($settings['active_template'])) {
            $sanitized['active_template'] = sanitize_text_field($settings['active_template']);
        }

        // Sanitize replace_edd_pages boolean
        $sanitized['replace_edd_pages'] = isset($settings['replace_edd_pages']) ? 
            (bool) $settings['replace_edd_pages'] : false;

        // ENHANCED: Sanitize fullscreen_mode boolean
        $sanitized['fullscreen_mode'] = isset($settings['fullscreen_mode']) ? 
            (bool) $settings['fullscreen_mode'] : false;

        // Sanitize enabled sections
        if (isset($settings['enabled_sections']) && is_array($settings['enabled_sections'])) {
            $sanitized['enabled_sections'] = array();
            $allowed_sections = array_keys($this->get_available_sections());

            foreach ($settings['enabled_sections'] as $section_key => $enabled) {
                // Only allow valid section keys
                if (in_array($section_key, $allowed_sections, true)) {
                    $sanitized['enabled_sections'][sanitize_key($section_key)] = (bool) $enabled;
                }
            }
        } else {
            $sanitized['enabled_sections'] = array();
        }

        return $sanitized;
    }    
    
    
    /**
     * Handle all form submissions manually
     */
    public function handle_form_submissions() {
        // Only process on our settings page
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if (!isset($_GET['page']) || $_GET['page'] !== $this->settings_slug) {
            return;
        }
        
        // Handle template activation
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if (isset($_GET['activate_template']) && isset($_GET['_wpnonce'])) {
            $this->handle_template_activation();
            return;
        }
        
        // Handle settings save
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Missing
        if (isset($_POST['eddcdp_save_settings']) && isset($_POST['eddcdp_settings_nonce'])) {
            $this->handle_settings_save();
            return;
        }
    }
    
    /**
     * Handle template activation
     */
    private function handle_template_activation() {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended,WordPress.Security.ValidatedSanitizedInput.InputNotValidated
        $template_name = sanitize_text_field(wp_unslash($_GET['activate_template']));
        
        // Verify nonce
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended,WordPress.Security.ValidatedSanitizedInput.InputNotValidated
        if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'activate_template_' . $template_name)) {
            wp_die(esc_html__('Security check failed', 'edd-customer-dashboard-pro'));
        }
        
        // Get current settings
        $settings = get_option('eddcdp_settings', array());
        
        // Update active template
        $settings['active_template'] = $template_name;
        
        // Save settings
        $result = update_option('eddcdp_settings', $settings);
        
        // Redirect with success message
        if ($result !== false) {
            $redirect_url = add_query_arg(array(
                'page' => $this->settings_slug,
                'template_activated' => '1',
                'activated_template' => urlencode($template_name)
            ), admin_url('edit.php?post_type=download'));
        } else {
            $redirect_url = add_query_arg(array(
                'page' => $this->settings_slug,
                'template_error' => '1'
            ), admin_url('edit.php?post_type=download'));
        }
        
        wp_redirect($redirect_url);
        exit;
    }
    
    /**
     * Handle settings save
     */
    private function handle_settings_save() {
        // Verify nonce
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended,WordPress.Security.ValidatedSanitizedInput.InputNotValidated
        if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['eddcdp_settings_nonce'])), 'eddcdp_save_settings')) {
            wp_die(esc_html__('Security check failed', 'edd-customer-dashboard-pro'));
        }
        
        // Get current settings to preserve active_template
        $current_settings = get_option('eddcdp_settings', array());
        $settings = array();
        
        // Preserve active template
        $settings['active_template'] = isset($current_settings['active_template']) ? $current_settings['active_template'] : 'default';
        
        // Replace EDD Pages
        $settings['replace_edd_pages'] = isset($_POST['replace_edd_pages']) ? true : false;
        
        // ENHANCED: Full Screen Mode
        $settings['fullscreen_mode'] = isset($_POST['fullscreen_mode']) ? true : false;
        
        // Enabled Sections
        $settings['enabled_sections'] = array();
        $available_sections = $this->get_available_sections();
        
        foreach ($available_sections as $section_key => $section_name) {
            $settings['enabled_sections'][$section_key] = isset($_POST['enabled_sections'][$section_key]) ? true : false;
        }
        
        // Save settings
        $result = update_option('eddcdp_settings', $settings);
        
        // Redirect with success message
        if ($result !== false) {
            $redirect_url = add_query_arg(array(
                'page' => $this->settings_slug,
                'settings_saved' => '1'
            ), admin_url('edit.php?post_type=download'));
        } else {
            $redirect_url = add_query_arg(array(
                'page' => $this->settings_slug,
                'settings_error' => '1'
            ), admin_url('edit.php?post_type=download'));
        }
        
        wp_redirect($redirect_url);
        exit;
    }
    
    /**
     * Add admin menu page
     */
    public function add_admin_menu() {
        add_submenu_page(
            'edit.php?post_type=download',
            esc_html__('Customer Dashboard Pro', 'edd-customer-dashboard-pro'),
            esc_html__('Dashboard Pro', 'edd-customer-dashboard-pro'),
            'manage_shop_settings',
            $this->settings_slug,
            array($this, 'render_settings_page')
        );
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        if (strpos($hook, $this->settings_slug) === false) {
            return;
        }
        
        wp_enqueue_style('eddcdp-admin', EDDCDP_PLUGIN_URL . 'assets/admin.css', array(), EDDCDP_VERSION);
        wp_enqueue_script('eddcdp-admin', EDDCDP_PLUGIN_URL . 'assets/admin.js', array('jquery'), EDDCDP_VERSION, true);
    }
    
    /**
     * Render settings page
     */
    public function render_settings_page() {
        // Get current settings
        $settings = get_option('eddcdp_settings', array());
        $active_template = isset($settings['active_template']) ? $settings['active_template'] : 'default';
        $replace_edd_pages = isset($settings['replace_edd_pages']) ? $settings['replace_edd_pages'] : false;
        $fullscreen_mode = isset($settings['fullscreen_mode']) ? $settings['fullscreen_mode'] : false; // ENHANCED
        $enabled_sections = isset($settings['enabled_sections']) ? $settings['enabled_sections'] : array();
        
        // Show messages
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if (isset($_GET['template_activated'])) {
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $activated_template = isset($_GET['activated_template']) ? sanitize_text_field(wp_unslash($_GET['activated_template'])) : '';
            echo '<div class="notice notice-success is-dismissible"><p>' . 
                 // translators: %s is the template name that was activated
                 sprintf(esc_html__('Template "%s" activated successfully!', 'edd-customer-dashboard-pro'), esc_html($activated_template)) . 
                 '</p></div>';
        }
        
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if (isset($_GET['template_error'])) {
            echo '<div class="notice notice-error is-dismissible"><p>' . 
                 esc_html__('Failed to activate template. Please try again.', 'edd-customer-dashboard-pro') . 
                 '</p></div>';
        }
        
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if (isset($_GET['settings_saved'])) {
            echo '<div class="notice notice-success is-dismissible"><p>' . 
                 esc_html__('Settings saved successfully!', 'edd-customer-dashboard-pro') . 
                 '</p></div>';
        }
        
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if (isset($_GET['settings_error'])) {
            echo '<div class="notice notice-error is-dismissible"><p>' . 
                 esc_html__('Failed to save settings. Please try again.', 'edd-customer-dashboard-pro') . 
                 '</p></div>';
        }
        
        ?>
        <div class="wrap eddcdp-admin">
            <h1><?php esc_html_e('EDD Customer Dashboard Pro Settings', 'edd-customer-dashboard-pro'); ?></h1>
            
            <div class="eddcdp-admin-wrapper">
                <div class="eddcdp-admin-main">
                    <!-- Settings Form -->
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
                                        <p class="description"><?php esc_html_e('When enabled, the default EDD shortcodes will be replaced with the modern dashboard interface.', 'edd-customer-dashboard-pro'); ?></p>
                                    </td>
                                </tr>
                                
                                <!-- ENHANCED: Full Screen Mode Setting -->
                                <tr>
                                    <th scope="row"><?php esc_html_e('Full Screen Mode', 'edd-customer-dashboard-pro'); ?></th>
                                    <td>
                                        <label class="eddcdp-toggle">
                                            <input type="checkbox" name="fullscreen_mode" value="1" <?php checked($fullscreen_mode, true); ?> />
                                            <span class="eddcdp-toggle-slider"></span>
                                        </label>
                                        <p class="description"><?php esc_html_e('When enabled, all EDD customer dashboard pages will automatically open in full screen mode by default. Provides a clean, distraction-free experience without WordPress header/footer.', 'edd-customer-dashboard-pro'); ?></p>

                                        <?php if ($fullscreen_mode) : ?>
                                            <div class="eddcdp-fullscreen-preview" style="margin-top: 15px; padding: 15px; background: #f0f8ff; border-left: 4px solid #667eea; border-radius: 4px;">
                                                <h4 style="margin: 0 0 10px 0; color: #333;">🚀 <?php esc_html_e('Auto Full Screen Mode Active:', 'edd-customer-dashboard-pro'); ?></h4>
                                                <ul style="margin: 0; padding-left: 20px;">
                                                    <li><?php esc_html_e('All dashboard pages automatically open in full screen', 'edd-customer-dashboard-pro'); ?></li>
                                                    <li><?php esc_html_e('Clean URLs - no parameters needed', 'edd-customer-dashboard-pro'); ?></li>
                                                    <li><?php esc_html_e('"Back to Site" button returns to homepage or referrer', 'edd-customer-dashboard-pro'); ?></li>
                                                    <li><?php esc_html_e('Works with order history, receipts, and invoices', 'edd-customer-dashboard-pro'); ?></li>
                                                    <li><?php esc_html_e('ESC key exits to main site', 'edd-customer-dashboard-pro'); ?></li>
                                                    <li><?php esc_html_e('Mobile responsive design', 'edd-customer-dashboard-pro'); ?></li>
                                                </ul>

                                                <?php 
                                                // Show example URLs that will auto-open in full screen
                                                $base_url = home_url();
                                                ?>
                                                <div style="margin-top: 15px; padding: 10px; background: #fff; border-radius: 4px;">
                                                    <p style="margin: 0 0 8px 0;"><strong><?php esc_html_e('Pages that auto-open in full screen:', 'edd-customer-dashboard-pro'); ?></strong></p>
                                                    <ul style="margin: 0; padding-left: 20px; font-family: monospace; font-size: 12px;">
                                                        <li><code><?php echo esc_html($base_url); ?>/checkout-2/order-history/</code></li>
                                                        <li><code><?php echo esc_html($base_url); ?>/order-history/?payment_key=abc123</code></li>
                                                        <li><code><?php echo esc_html($base_url); ?>/my-account/orders/</code></li>
                                                        <li><?php esc_html_e('+ Any page with EDD dashboard shortcodes', 'edd-customer-dashboard-pro'); ?></li>
                                                    </ul>
                                                </div>

                                                <div style="margin-top: 15px; padding: 10px; background: #fff3cd; border-radius: 4px;">
                                                    <p style="margin: 0; font-size: 13px;"><strong><?php esc_html_e('Override for testing:', 'edd-customer-dashboard-pro'); ?></strong><br>
                                                    <?php esc_html_e('Add', 'edd-customer-dashboard-pro'); ?> <code>?eddcdp_exit_fullscreen=1</code> <?php esc_html_e('to any URL to view in normal mode.', 'edd-customer-dashboard-pro'); ?></p>
                                                </div>
                                            </div>
                                        <?php else : ?>
                                            <div style="margin-top: 15px; padding: 15px; background: #f9f9f9; border-left: 4px solid #ccc; border-radius: 4px;">
                                                <p style="margin: 0; color: #666; font-size: 13px;">
                                                    <?php esc_html_e('When disabled, customers will see the normal WordPress page layout with theme header/footer. You can still test full screen mode by adding', 'edd-customer-dashboard-pro'); ?> 
                                                    <code>?eddcdp_fullscreen=1</code> <?php esc_html_e('to any dashboard URL.', 'edd-customer-dashboard-pro'); ?>
                                                </p>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </table>
                            
                            <h3><?php esc_html_e('Dashboard Sections', 'edd-customer-dashboard-pro'); ?></h3>
                            <p class="description"><?php esc_html_e('Enable or disable specific dashboard sections.', 'edd-customer-dashboard-pro'); ?></p>
                            
                            <table class="form-table">
                                <?php foreach ($this->get_available_sections() as $section_key => $section_name) : ?>
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
                        <ul>
                            <li><?php esc_html_e('Use the shortcode to display the dashboard:', 'edd-customer-dashboard-pro'); ?></li>
                        </ul>
                        <code>[edd_customer_dashboard_pro]</code>

                        <h4><?php esc_html_e('Plugin Info', 'edd-customer-dashboard-pro'); ?></h4>
                        <p><strong><?php esc_html_e('Version:', 'edd-customer-dashboard-pro'); ?></strong> <?php echo esc_html(EDDCDP_VERSION); ?></p>
                        <p><strong><?php esc_html_e('Active Template:', 'edd-customer-dashboard-pro'); ?></strong> <?php echo esc_html(ucfirst($active_template)); ?></p>
                        
                        <!-- ENHANCED: Full Screen Mode Info -->
                        <?php if ($fullscreen_mode) : ?>
                            <div style="margin-top: 20px; padding: 15px; background: #e8f5e8; border-radius: 8px; border: 1px solid #4caf50;">
                                <h4 style="margin: 0 0 10px 0; color: #2e7d32;">🔍 <?php esc_html_e('Full Screen Mode Active', 'edd-customer-dashboard-pro'); ?></h4>
                                <p style="margin: 0; font-size: 13px; color: #333;">
                                    <?php esc_html_e('Customers will see a "Full Screen" button on their dashboard for an immersive experience.', 'edd-customer-dashboard-pro'); ?>
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>  
                
            </div>
                   
            <div class="eddcdp-admin-wrapper">
                <div class="eddcdp-admin-main">                                                                               
                    
                    
                    <!-- Template Selection -->
                    <div class="eddcdp-templates-section">
                        <h2><?php esc_html_e('Template Settings', 'edd-customer-dashboard-pro'); ?></h2>
                        <p class="description"><?php esc_html_e('Choose and configure your dashboard template.', 'edd-customer-dashboard-pro'); ?></p>
                        
                        <div class="eddcdp-template-grid">
                            <?php $this->render_template_options($active_template); ?>
                        </div>
                    </div>
                </div>
            </div>
            
            
        </div>
        <?php
    }
    
    /**
     * Render template options - IMPROVED LAYOUT
     */
    private function render_template_options($active_template) {
        // Use template loader to get available templates
        $template_loader = eddcdp()->get_template_loader();
        $available_templates = $template_loader ? $template_loader->get_available_templates() : array();
        
        // Fallback if template loader not available
        if (empty($available_templates)) {
            $available_templates = $this->get_available_templates_fallback();
        }
        
        foreach ($available_templates as $template_key => $template_info) {
            $is_active = ($active_template === $template_key);
            ?>
            <div class="eddcdp-template-card <?php echo $is_active ? 'active' : ''; ?>">
                <div class="eddcdp-template-preview">
                    <?php
                    // Try to get screenshot from template loader
                    $screenshot = false;
                    if ($template_loader) {
                        $screenshot = $template_loader->get_template_screenshot($template_key);
                    }
                    
                    if ($screenshot) :
                        $this->render_template_screenshot($screenshot, $template_info['name']);
                    ?>
                        
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
                            <?php 
                            // translators: %s is the version number of the template
                            printf(esc_html__('Version: %s', 'edd-customer-dashboard-pro'), esc_html($template_info['version'])); 
                            ?>
                        </span>
                        <?php if (isset($template_info['author'])) : ?>
                            <span class="eddcdp-template-author">
                                <?php 
                                    // translators: %s is the name of the template author
                                    printf(esc_html__('by %s', 'edd-customer-dashboard-pro'), esc_html($template_info['author']));      
                                ?>
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
                                    'page' => $this->settings_slug,
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
            <?php
        }
    }
    
    
    /**
     * Render template screenshot using WordPress-compliant method
     * FIXED: Added proper image rendering function
     */
    private function render_template_screenshot($screenshot_url, $template_name) {
        // Validate the screenshot URL is from our plugin directory
        $plugin_url = EDDCDP_PLUGIN_URL;
        if (strpos($screenshot_url, $plugin_url) !== 0) {
            // Security: Only allow screenshots from our plugin directory
            return;
        }
        
        // Get the file path for validation
        $screenshot_path = str_replace($plugin_url, EDDCDP_PLUGIN_DIR, $screenshot_url);
        $screenshot_path = remove_query_arg('v', $screenshot_path); // Remove version parameter
        
        // Validate file exists and is an image
        if (!file_exists($screenshot_path) || !$this->is_valid_image_file($screenshot_path)) {
            return;
        }
        
        // Get image dimensions for better accessibility
        $image_size = @getimagesize($screenshot_path);
        $width = $image_size ? $image_size[0] : '';
        $height = $image_size ? $image_size[1] : '';
        
        // Create WordPress-compliant image attributes
        $image_attrs = array(
            'src' => esc_url($screenshot_url),
            'alt' => esc_attr(sprintf(
                /* translators: %s is the template name */
                __('Screenshot of %s template', 'edd-customer-dashboard-pro'), 
                $template_name
            )),
            'class' => 'eddcdp-template-screenshot',
            'loading' => 'lazy', // Modern loading attribute
        );
        
        // Add dimensions if available
        if ($width && $height) {
            $image_attrs['width'] = $width;
            $image_attrs['height'] = $height;
        }
        
        // Build the image tag using WordPress best practices
        $image_html = '<img';
        foreach ($image_attrs as $attr => $value) {
            if (!empty($value)) {
                $image_html .= ' ' . esc_attr($attr) . '="' . esc_attr($value) . '"';
            }
        }
        $image_html .= ' />';
        
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Already escaped above
        echo $image_html;
    }
    
    /**
     * Validate if file is a valid image
     * FIXED: Added image validation helper
     */
    private function is_valid_image_file($file_path) {
        // Check file extension
        $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif', 'webp');
        $file_extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
        
        if (!in_array($file_extension, $allowed_extensions, true)) {
            return false;
        }
        
        // Check if it's actually an image using WordPress function
        $image_info = wp_getimagesize($file_path);
        return $image_info !== false;
    }    
    
    
    /**
     * Fallback for available templates if template loader fails
     */
    private function get_available_templates_fallback() {
        return array(
            'default' => array(
                'name' => esc_html__('Default Dashboard', 'edd-customer-dashboard-pro'),
                'description' => esc_html__('Modern, clean dashboard interface with glassmorphism design', 'edd-customer-dashboard-pro'),
                'version' => '1.0.0',
                'author' => esc_html__('EDD Customer Dashboard Pro', 'edd-customer-dashboard-pro')
            )
        );
    }
    
    /**
     * Get available dashboard sections
     */
    private function get_available_sections() {
        return array(
            'purchases' => esc_html__('Purchases', 'edd-customer-dashboard-pro'),
            'downloads' => esc_html__('Downloads', 'edd-customer-dashboard-pro'),
            'licenses' => esc_html__('Licenses', 'edd-customer-dashboard-pro'),
            'wishlist' => esc_html__('Wishlist', 'edd-customer-dashboard-pro'),
            'analytics' => esc_html__('Analytics', 'edd-customer-dashboard-pro'),
            'support' => esc_html__('Support', 'edd-customer-dashboard-pro')
        );
    }
}