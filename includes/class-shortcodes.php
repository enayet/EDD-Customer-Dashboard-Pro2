<?php


add_action('wp_ajax_eddcdp_activate_site', 'eddcdp_activate_site');
add_action('wp_ajax_eddcdp_deactivate_site', 'eddcdp_deactivate_site');
add_action('wp_ajax_eddcdp_get_sites', 'eddcdp_get_sites');

function eddcdp_activate_site() {
    $license_id = absint($_POST['license_id']);
    $url = esc_url_raw($_POST['url']);

    $license = edd_software_licensing()->get_license($license_id);
    if (!$license || get_current_user_id() !== $license->customer_id) {
        wp_send_json_error(['message' => 'Permission denied']);
    }

    $license->add_site($url);
    wp_send_json_success(['url' => $url]);
}

function eddcdp_deactivate_site() {
    $license_id = absint($_POST['license_id']);
    $url = esc_url_raw($_POST['url']);

    $license = edd_software_licensing()->get_license($license_id);
    if (!$license || get_current_user_id() !== $license->customer_id) {
        wp_send_json_error(['message' => 'Permission denied']);
    }

    $license->remove_site($url);
    wp_send_json_success();
}

function eddcdp_get_sites() {
    $license_id = absint($_GET['license_id']);
    $license = edd_software_licensing()->get_license($license_id);
    if (!$license || get_current_user_id() !== $license->customer_id) {
        wp_send_json_error(['message' => 'Permission denied']);
    }

    wp_send_json_success(['sites' => array_values($license->get_sites())]);
}



?>