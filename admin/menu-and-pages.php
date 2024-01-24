<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action('admin_menu', 'leadslide_add_app_page');

function leadslide_add_app_page() {
    add_menu_page(
        'Publish Campaigns',
        'Leadslide',
        'manage_options',
        'leadslide-publish-campaign',
        'leadslide_publish_campaign',
        'dashicons-admin-site-alt3'
    );

    add_submenu_page(
        'leadslide-publish-campaign',
        'Popups',
        'Popups',
        'manage_options',
        'leadslide-popups',
        'leadslide_popups_page'
    );

    add_submenu_page(
        'leadslide-publish-campaign',
        'Leadslide',
        'Settings',
        'manage_options',
        'leadslide-settings',
        'leadslide_settings_page'
    );
}



