<?php

add_action('admin_init', 'lakil_delete_leadslide_template');
add_action('admin_init', 'lakil_install_leadslide_template');

// The lakil_settings_page() function will go here
function lakil_settings_page() {
    $theme_dir = get_template_directory();
    $template_file = $theme_dir . '/leadslide-page-template.php';
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

        <form action="options.php" method="post">
            <?php
            settings_fields('lakil_options');
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
function lakil_delete_leadslide_template() {
    global $LS_PAGE_TEMPLATE_PATH;
    if (isset($_POST['action']) && $_POST['action'] === 'delete_leadslide_template') {
        $theme_dir = get_template_directory();
        $template_file = $theme_dir . '/leadslide-page-template.php';

        if (file_exists($template_file)) {
            if (unlink($template_file)) {
                add_settings_error('lakil_options', 'template_deleted', 'Leadslide page template deleted successfully.', 'updated');
            } else {
                add_settings_error('lakil_options', 'delete_failed', 'Could not delete the Leadslide page template. Please check the permissions of your theme directory.');
            }
        } else {
            add_settings_error('lakil_options', 'template_not_found', 'Leadslide page template not found in the theme directory.', 'updated');
        }

        set_transient('settings_errors', get_settings_errors(), 30);

        $goback = add_query_arg('settings-updated', 'true', wp_get_referer());
        wp_redirect($goback);
        exit;
    }
}

function lakil_install_leadslide_template() {
    global $LS_PAGE_TEMPLATE_PATH;
    if (isset($_POST['action']) && $_POST['action'] === 'install_leadslide_template') {
        $template_file = $LS_PAGE_TEMPLATE_PATH;
        if (file_exists($template_file)) {
            $theme_dir = get_template_directory();
            $destination_file = $theme_dir . '/leadslide-page-template.php';

            if (!file_exists($destination_file)) {
                if (copy($template_file, $destination_file)) {
                    add_settings_error('lakil_options', 'template_installed', 'Leadslide page template installed successfully.', 'updated');
                } else {
                    add_settings_error('lakil_options', 'install_failed', 'Could not install the Leadslide page template. Please check the permissions of your theme directory.');
                }
            } else {
                add_settings_error('lakil_options', 'template_exists', 'Leadslide page template already exists in the theme directory.', 'updated');
            }
        } else {
            add_settings_error('lakil_options', 'template_not_found', 'Leadslide page template not found in the plugin directory.');
        }

        set_transient('settings_errors', get_settings_errors(), 30);

        $goback = add_query_arg('settings-updated', 'true', wp_get_referer());
        wp_redirect($goback);
        exit;
    }
}

// The lakil_register_settings() function will go here
add_action('admin_init', 'lakil_register_settings');
function lakil_register_settings() {
    register_setting('lakil_options', 'lakil_options', 'lakil_sanitize_options');
    add_settings_section('lakil_settings', 'Settings', null, 'leadslide-api-key-iframe-loader');
    add_settings_field('lakil_api_key', 'API Key', 'lakil_api_key_field', 'leadslide-api-key-iframe-loader', 'lakil_settings');
}

// The lakil_sanitize_options() function will go here
function lakil_sanitize_options($options) {
    $sanitized_options = array();
    $sanitized_options['lakil_api_key'] = sanitize_text_field($options['lakil_api_key']);
    return $sanitized_options;
}

// The lakil_api_key_field() function will go here
function lakil_api_key_field() {
    $options = get_option('lakil_options');
    echo '<input type="text" id="lakil_api_key" name="lakil_options[lakil_api_key]" value="' . esc_attr($options['lakil_api_key']) . '">';
}