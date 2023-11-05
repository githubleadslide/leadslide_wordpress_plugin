<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

add_action('admin_menu', 'leadslide_add_settings_page');
add_action('admin_menu', 'leadslide_add_app_page');

function leadslide_add_settings_page() {
    add_options_page(
        'Leadslide Settings',
        'Leadslide Settings',
        'manage_options',
        'leadslide-api-key-iframe-loader',
        'leadslide_settings_page'
    );
}
function leadslide_add_app_page() {
    add_menu_page(
        'Leadslide Application',
        'Leadslide Application',
        'manage_options',
        'leadslide-application',
        'leadslide_app_page',
        'dashicons-admin-site-alt3'
    );

    add_submenu_page(
        'leadslide-application',
        'Publish Campaigns',
        'Publish Campaigns',
        'manage_options',
        'leadslide-publish-campaign',
        'leadslide_publish_campaign'
    );
}



