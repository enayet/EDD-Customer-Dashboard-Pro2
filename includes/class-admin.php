<?php

class EDDCDP_Admin_Settings {

    public static function register_menu() {
        add_submenu_page(
            'download', // Under EDD menu
            'Dashboard Template',
            'Customer Dashboard',
            'manage_options',
            'edd-cdp-settings',
            [self::class, 'render_settings_page']
        );
    }

    public static function render_settings_page() {
        if (isset($_POST['eddcdp_template'])) {
            update_option('eddcdp_active_template', sanitize_text_field($_POST['eddcdp_template']));
            echo '<div class="updated"><p>Template updated successfully.</p></div>';
        }

        $active = get_option('eddcdp_active_template', 'default');
        $templates = array_filter(glob(EDDCDP_PATH . 'templates/*'), 'is_dir');

        echo '<div class="wrap"><h1>Customer Dashboard Template</h1><form method="post">';
        echo '<table class="form-table"><tr><th>Select Template</th><td><select name="eddcdp_template">';

        foreach ($templates as $dir) {
            $slug = basename($dir);
            echo '<option value="' . esc_attr($slug) . '"' . selected($active, $slug, false) . '>' . esc_html(ucfirst($slug)) . '</option>';
        }

        echo '</select></td></tr></table>';
        submit_button('Save Changes');
        echo '</form></div>';
    }
}
