<?php
/**
 * Admin Settings Class
 * 
 * Handles admin settings page and configuration
 */

if (!defined('ABSPATH')) {
    exit;
}

class EDDCDP_Admin_Settings {
    
    private $settings_slug = 'eddcdp-settings';
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }
    
    /**
     * Add admin menu page
     */
    public function add_admin_menu() {
        add_submenu_page(
            'edit.php?post_type=download',
            __('Customer Dashboard Pro', EDDCDP_TEXT_DOMAIN),
            __('Dashboard Pro', EDDCDP_TEXT_DOMAIN),
            'manage_shop_settings',
            $this->settings_slug,
            array($this, 'render_settings_page')
        );
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('eddcdp_settings_group', 'eddcdp_settings', array($this, 'sanitize_settings'));
        
        // General Settings Section
        add_settings_section(
            'eddcdp_general_section',
            __('General Settings', EDDCDP_TEXT_DOMAIN),
            array($this, 'general_section_callback'),
            $this->settings_slug
        );
        
        // Template Settings Section
        add_settings_section(
            'eddcdp_template_section',
            __('Template Settings', EDDCDP_TEXT_DOMAIN),
            array($this, 'template_section_callback'),
            $this->settings_slug
        );
        
        // Sections Settings Section
        add_settings_section(
            'eddcdp_sections_section',
            __('Dashboard Sections', EDDCDP_TEXT_DOMAIN),
            array($this, 'sections_section_callback'),
            $this->settings_slug
        );
        
        // Add settings fields
        $this->add_settings_fields();
    }
    
    /**
     * Add settings fields
     */
    private function add_settings_fields() {
        // Replace EDD Pages
        add_settings_field(
            'replace_edd_pages',
            __('Replace EDD Pages', EDDCDP_TEXT_DOMAIN),
            array($this, 'checkbox_field_callback'),
            $this->settings_slug,
            'eddcdp_general_section',
            array(
                'field' => 'replace_edd_pages',
                'description' => __('Replace default EDD customer pages with Dashboard Pro', EDDCDP_TEXT_DOMAIN)
            )
        );
        
        // Active Template
        add_settings_field(
            'active_template',
            __('Active Template', EDDCDP_TEXT_DOMAIN),
            array($this, 'template_field_callback'),
            $this->settings_slug,
            'eddcdp_template_section'
        );
        
        // Section toggles
        $sections = $this->get_available_sections();
        foreach ($sections as $section_key => $section_name) {
            add_settings_field(
                'enabled_sections_' . $section_key,
                $section_name,
                array($this, 'section_toggle_callback'),
                $this->settings_slug,
                'eddcdp_sections_section',
                array('section' => $section_key)
            );
        }
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
        ?>
        <div class="wrap eddcdp-admin">
            <h1><?php esc_html_e('EDD Customer Dashboard Pro Settings', EDDCDP_TEXT_DOMAIN); ?></h1>
            
            <div class="eddcdp-admin-content">
                <form method="post" action="options.php">
                    <?php
                    settings_fields('eddcdp_settings_group');
                    do_settings_sections($this->settings_slug);
                    submit_button();
                    ?>
                </form>
                
<!--
                <div class="eddcdp-sidebar">                    
                    <div class="eddcdp-info-box">
                        <h3><?php esc_html_e('Shortcode', EDDCDP_TEXT_DOMAIN); ?></h3>
                        <p><?php esc_html_e('Use this shortcode to display the dashboard:', EDDCDP_TEXT_DOMAIN); ?></p>
                        <code>[edd_customer_dashboard_pro]</code>
                    </div>
                </div>
-->
            </div>
        </div>
        <?php
    }
    
    /**
     * Section callbacks
     */
    public function general_section_callback() {
        echo '<p>' . esc_html__('Configure general dashboard settings.', EDDCDP_TEXT_DOMAIN) . '</p>';
    }
    
    public function template_section_callback() {
        echo '<p>' . esc_html__('Choose and configure your dashboard template.', EDDCDP_TEXT_DOMAIN) . '</p>';
    }
    
    public function sections_section_callback() {
        echo '<p>' . esc_html__('Enable or disable specific dashboard sections.', EDDCDP_TEXT_DOMAIN) . '</p>';
    }
    
    /**
     * Field callbacks
     */
    public function checkbox_field_callback($args) {
        $settings = get_option('eddcdp_settings', array());
        $field = $args['field'];
        $value = isset($settings[$field]) ? $settings[$field] : false;
        $description = isset($args['description']) ? $args['description'] : '';
        
        ?>
        <label>
            <input type="checkbox" name="eddcdp_settings[<?php echo esc_attr($field); ?>]" value="1" <?php checked($value, true); ?> />
            <?php echo esc_html($description); ?>
        </label>
        <?php
    }
    
    public function template_field_callback() {
        $settings = get_option('eddcdp_settings', array());
        $active_template = isset($settings['active_template']) ? $settings['active_template'] : 'default';
        
        $template_loader = eddcdp()->get_template_loader();
        $available_templates = $template_loader->get_available_templates();
        
        ?>
        <div class="eddcdp-template-selector">
            <?php foreach ($available_templates as $template_key => $template_info) : ?>
                <div class="eddcdp-template-option">
                    <label>
                        <input type="radio" name="eddcdp_settings[active_template]" value="<?php echo esc_attr($template_key); ?>" <?php checked($active_template, $template_key); ?> />
                        <div class="template-preview">
                            <?php
                            $screenshot = $template_loader->get_template_screenshot($template_key);
                            if ($screenshot) :
                            ?>
                                <img src="<?php echo esc_url($screenshot); ?>" alt="<?php echo esc_attr($template_info['name']); ?>" />
                            <?php else : ?>
                                <div class="no-screenshot">
                                    <span class="dashicons dashicons-admin-appearance"></span>
                                </div>
                            <?php endif; ?>
                            
                            <div class="template-info">
                                <h4><?php echo esc_html($template_info['name']); ?></h4>
                                <p><?php echo esc_html($template_info['description']); ?></p>
                                <div class="template-meta">
                                    <span class="version"><?php printf(esc_html__('Version: %s', EDDCDP_TEXT_DOMAIN), esc_html($template_info['version'])); ?></span>
                                    <?php if (isset($template_info['author'])) : ?>
                                        <span class="author"><?php printf(esc_html__('by %s', EDDCDP_TEXT_DOMAIN), esc_html($template_info['author'])); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </label>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
    }
    
    public function section_toggle_callback($args) {
        $settings = get_option('eddcdp_settings', array());
        $section = $args['section'];
        $enabled_sections = isset($settings['enabled_sections']) ? $settings['enabled_sections'] : array();
        $is_enabled = isset($enabled_sections[$section]) ? $enabled_sections[$section] : true;
        
        ?>
        <label class="eddcdp-toggle">
            <input type="checkbox" name="eddcdp_settings[enabled_sections][<?php echo esc_attr($section); ?>]" value="1" <?php checked($is_enabled, true); ?> />
            <span class="eddcdp-toggle-slider"></span>
        </label>
        <?php
    }
    
    /**
     * Sanitize settings
     */
    public function sanitize_settings($input) {
        $sanitized = array();
        
        // Replace EDD Pages
        $sanitized['replace_edd_pages'] = isset($input['replace_edd_pages']) ? true : false;
        
        // Active Template
        if (isset($input['active_template'])) {
            $template_loader = eddcdp()->get_template_loader();
            if ($template_loader && $template_loader->template_exists($input['active_template'])) {
                $sanitized['active_template'] = sanitize_text_field($input['active_template']);
            } else {
                $sanitized['active_template'] = 'default';
            }
        }
        
        // Enabled Sections
        if (isset($input['enabled_sections']) && is_array($input['enabled_sections'])) {
            $sanitized['enabled_sections'] = array();
            $available_sections = $this->get_available_sections();
            
            foreach ($available_sections as $section_key => $section_name) {
                $sanitized['enabled_sections'][$section_key] = isset($input['enabled_sections'][$section_key]) ? true : false;
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Get available dashboard sections
     */
    private function get_available_sections() {
        return array(
            'purchases' => __('Purchases', EDDCDP_TEXT_DOMAIN),
            'downloads' => __('Downloads', EDDCDP_TEXT_DOMAIN),
            'licenses' => __('Licenses', EDDCDP_TEXT_DOMAIN),
            'wishlist' => __('Wishlist', EDDCDP_TEXT_DOMAIN),
            'analytics' => __('Analytics', EDDCDP_TEXT_DOMAIN),
            'support' => __('Support', EDDCDP_TEXT_DOMAIN)
        );
    }
}