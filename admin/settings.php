<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

add_action('admin_init', 'leadslide_delete_leadslide_template');
add_action('admin_init', 'leadslide_install_leadslide_template');

function leadslide_settings_page() {
    /**
     * The settings page will allow the user to enter their API key and save it.
     * The settings page will also allow the user to install the Leadslide page template.
     */
    $theme_dir = get_template_directory();
    $template_file = $theme_dir . '/leadslide-page-template.php';
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <p>
            This plugin integrates your WordPress site with Leadslide, allowing you to manage and publish campaigns
            directly from your dashboard. It connects to ai.leadslide.com, using your unique API key, to fetch campaign
            information. Enter your API key below to enable seamless synchronization between Leadslide and your site,
            ensuring efficient campaign management and publication.
        </p>

        <p>
            The API key is a crucial element of the Leadslide integration. It serves as a unique identifier that grants
            your WordPress site access to your Leadslide account. By entering your API key here, you enable the plugin
            to securely communicate with ai.leadslide.com. This communication allows the plugin to retrieve your
            campaign data and manage publications directly from your WordPress dashboard. Be sure to input your correct
            Leadslide API key to ensure seamless integration and functionality.
        </p>

        <?php settings_errors(); ?>

        <?php settings_errors(); ?>

        <form action="options.php" method="post">
            <?php
            settings_fields('leadslide_options');
            do_settings_sections('leadslide-settings');
            wp_nonce_field('leadslide-settings-save', 'leadslide-settings-nonce');
            submit_button('Save Changes');
            ?>
        </form>

        <?php if (!file_exists($template_file)) : ?>
            <p>
                The 'Install Leadslide Page Template' button allows you to add a custom page template to your theme. This template is designed specifically for displaying Leadslide campaigns on your site. Clicking this button will automatically copy the necessary template file into your current theme's directory, enabling you to select it when creating or editing pages. This ensures that your campaigns are displayed optimally, utilizing Leadslide's tailored layout and design.
            </p>
            <form action="" method="post">
                <?php wp_nonce_field('leadslide-install-template-action', 'leadslide-install-template-nonce'); ?>
                <input type="hidden" name="action" value="install_leadslide_template">
                <?php submit_button('Install Leadslide Page Template'); ?>
            </form>
        <?php else : ?>
            <form action="" method="post">
                <?php wp_nonce_field('leadslide-delete-template-action', 'leadslide-delete-template-nonce'); ?>
                <input type="hidden" name="action" value="delete_leadslide_template">
                <?php submit_button('Delete Leadslide Page Template'); ?>
            </form>
        <?php endif; ?>

    </div>
    <?php
}

function leadslide_auth_user($user_can='manage_options', $action, $nonce_field, $ajax=false) {
    /**
     * This function will check if the user is authorized to perform the action.
     */
    if (!current_user_can($user_can)) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    if($ajax)
    {
        if(!check_ajax_referer($action, $nonce_field, false))
        {
            wp_die(__('Nonce verification failed.'));
        }
    } else {
        if (!isset($_POST[$nonce_field]) || !check_admin_referer($action, $nonce_field)) {
            wp_die(__('Nonce verification failed.', 'leadslide-text-domain'));
        }
    }
}
function leadslide_delete_leadslide_template() {
    /**
     * This function will delete the Leadslide page template from the theme directory.
     */

    if (isset($_POST['action']) && $_POST['action'] === 'delete_leadslide_template') {
        leadslide_auth_user('manage_options', 'leadslide-delete-template-action', 'leadslide-delete-template-nonce');
    }

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
    /**
     * This function will install the Leadslide page template in the theme directory.
     */

    if (isset($_POST['action']) && $_POST['action'] === 'install_leadslide_template') {
        leadslide_auth_user('manage_options', 'leadslide-install-template-action', 'leadslide-install-template-nonce');
    }

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
    add_settings_section('leadslide_settings', 'API Settings', null, 'leadslide-settings');
    add_settings_field('leadslide_api_key', 'API Key', 'leadslide_api_key_field', 'leadslide-settings', 'leadslide_settings');
}

// The leadslide_sanitize_options() function will go here
function leadslide_sanitize_options($options) {
    /**
     * This function will sanitize the options entered by the user.
     */

    if (!isset($_POST['leadslide-settings-nonce']) || !wp_verify_nonce($_POST['leadslide-settings-nonce'], 'leadslide-settings-save')) {
        add_settings_error('leadslide_options', 'invalid_nonce', 'Security check failed.', 'error');
        return get_option('leadslide_options');
    }

    $sanitized_options = array();
    $sanitized_options['leadslide_api_key'] = sanitize_text_field($options['leadslide_api_key']);

    add_settings_error('leadslide_options', 'settings_updated', 'Settings saved successfully.', 'updated');


    return $sanitized_options;
}

function leadslide_api_key_field() {
    /**
     * This function will display the API key field on the settings page.
     */
    $options = get_option('leadslide_options');
    echo '<input type="text" id="leadslide_api_key" name="leadslide_options[leadslide_api_key]" value="' . esc_attr($options['leadslide_api_key']) . '">';
}