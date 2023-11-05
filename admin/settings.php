<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

add_action('admin_init', 'leadslide_delete_leadslide_template');
add_action('admin_init', 'leadslide_install_leadslide_template');

// The leadslide_settings_page() function will go here
function leadslide_settings_page() {
    $theme_dir = get_template_directory();
    $template_file = $theme_dir . '/leadslide-page-template.php';
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

        <form action="options.php" method="post">
            <?php
            settings_fields('leadslide_options');
            do_settings_sections('leadslide-api-key-iframe-loader');
            submit_button('Save Changes');
            ?>
        </form>

        <!-- Adding a new section -->
        <?php if (!file_exists($template_file)) : ?>
            <form action="" method="post">
                <input type="hidden" name="action" value="install_leadslide_template">
                <?php submit_button('Install Leadslide Page Template'); ?>
            </form>
        <?php else : ?>
            <form action="" method="post">
                <input type="hidden" name="action" value="delete_leadslide_template">
                <?php submit_button('Delete Leadslide Page Template'); ?>
            </form>
        <?php endif; ?>

    </div>
    <?php
}
function leadslide_delete_leadslide_template() {
    global $LS_PAGE_TEMPLATE_PATH;
    $action_posted = isset($_POST['action']) ? sanitize_text_field($_POST['action']) : '';

    if ($action_posted === 'delete_leadslide_template') {
        $theme_dir = get_template_directory();
        $template_file = $theme_dir . '/leadslide-page-template.php';

        if (file_exists($template_file)) {
            if (unlink($template_file)) {
                add_settings_error('leadslide_options', 'template_deleted', 'Leadslide page template deleted successfully.', 'updated');
            } else {
                add_settings_error('leadslide_options', 'delete_failed', 'Could not delete the Leadslide page template. Please check the permissions of your theme directory.');
            }
        } else {
            add_settings_error('leadslide_options', 'template_not_found', 'Leadslide page template not found in the theme directory.', 'updated');
        }
        /**
         * set_transient is used to store the settings errors in a transient, this is  a std WP function
         * get_settings_errors is used to retrieve the errors from the transient and display them
         * Both of these functions are WP functions.
         */
        set_transient('settings_errors', get_settings_errors(), 30);

        $goback = add_query_arg('settings-updated', 'true', wp_get_referer());
        wp_redirect($goback);
        exit;
    }
}

function leadslide_install_leadslide_template() {
    global $LS_PAGE_TEMPLATE_PATH;
    $action_posted = isset($_POST['action']) ? sanitize_text_field($_POST['action']) : '';

    if ($action_posted === 'install_leadslide_template') {
        $template_file = $LS_PAGE_TEMPLATE_PATH;
        if (file_exists($template_file)) {
            $theme_dir = get_template_directory();
            $destination_file = $theme_dir . '/leadslide-page-template.php';

            if (!file_exists($destination_file)) {
                if (copy($template_file, $destination_file)) {
                    add_settings_error('leadslide_options', 'template_installed', 'Leadslide page template installed successfully.', 'updated');
                } else {
                    add_settings_error('leadslide_options', 'install_failed', 'Could not install the Leadslide page template. Please check the permissions of your theme directory.');
                }
            } else {
                add_settings_error('leadslide_options', 'template_exists', 'Leadslide page template already exists in the theme directory.', 'updated');
            }
        } else {
            add_settings_error('leadslide_options', 'template_not_found', 'Leadslide page template not found in the plugin directory.');
        }
        /**
         * set_transient is used to store the settings errors in a transient, this is  a std WP function
         * get_settings_errors is used to retrieve the errors from the transient and display them
         * Both of these functions are WP functions.
         */
        set_transient('settings_errors', get_settings_errors(), 30);

        $goback = add_query_arg('settings-updated', 'true', wp_get_referer());
        wp_redirect($goback);
        exit;
    }
}

// The leadslide_register_settings() function will go here
add_action('admin_init', 'leadslide_register_settings');
function leadslide_register_settings() {
    register_setting('leadslide_options', 'leadslide_options', 'leadslide_sanitize_options');
    add_settings_section('leadslide_settings', 'Settings', null, 'leadslide-api-key-iframe-loader');
    add_settings_field('leadslide_api_key', 'API Key', 'leadslide_api_key_field', 'leadslide-api-key-iframe-loader', 'leadslide_settings');
}

// The leadslide_sanitize_options() function will go here
function leadslide_sanitize_options($options) {
    $sanitized_options = array();
    $sanitized_options['leadslide_api_key'] = sanitize_text_field($options['leadslide_api_key']);
    return $sanitized_options;
}

// The leadslide_api_key_field() function will go here
function leadslide_api_key_field() {
    $options = get_option('leadslide_options');
    echo '<input type="text" id="leadslide_api_key" name="leadslide_options[leadslide_api_key]" value="' . esc_attr($options['leadslide_api_key']) . '">';
}