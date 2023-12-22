<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

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

        <form action="options.php" method="post">
            <?php
            settings_fields('leadslide_options');
            do_settings_sections('leadslide-settings');
            wp_nonce_field('leadslide-settings-save', 'leadslide-settings-nonce');
            submit_button('Save Changes');
            ?>
        </form>

        <p>
            Use the new "Leadslide Campaigns" custom post type to manage your campaigns. You can add new campaigns, edit existing ones, and manage all your content directly through this custom post type.
        </p>

        <h2>
            Warning**
        </h2>

        <p>
            Leadslide is set to work with the permalink set to postname. If the structure is not set to postname the page
            campaign will not load correctly.
        </p>

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

    if (!isset($_POST['leadslide-settings-nonce']) || !wp_verify_nonce(sanitize_text_field($_POST['leadslide-settings-nonce']), 'leadslide-settings-save')) {
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