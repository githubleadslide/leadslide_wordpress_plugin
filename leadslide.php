<?php
/**
 * Plugin Name: Leadslide API Key
 * Description: Loads an external site in an iframe after validating the user's Leadslide API key.
 * Version: 1.0
 * Author: Leadslide.com
 * License: GPLv2 or later
 * Text Domain: Leadslide.com
 */

global $BASE_LS_API_URL;
global $BASE_VIEW_URL;
$BASE_LS_API_URL = 'http://localhost:8080/api/basic/wp/';
$BASE_VIEW_URL  = 'http://localhost:8080/campaign/cp/';



add_action('admin_menu', 'lakil_add_settings_page');
add_action('admin_menu', 'lakil_add_app_page');

function lakil_add_settings_page() {
    add_options_page(
        'Leadslide Settings',
        'Leadslide Settings',
        'manage_options',
        'leadslide-api-key-iframe-loader',
        'lakil_settings_page'
    );
}

function lakil_add_app_page() {
    add_menu_page(
        'Leadslide Application',
        'Leadslide Application',
        'manage_options',
        'leadslide-application',
        'lakil_app_page',
        'dashicons-admin-site-alt3'
    );
    add_submenu_page(
        'leadslide-application',
        'Publish Campaigns',
        'Publish Campaigns',
        'manage_options',
        'leadslide-publish-page',
        'lakil_publish_page'
    );
}

function lakil_publish_page() {
    global $BASE_LS_API_URL;
    $options = get_option('lakil_options');
    $api_key = $options['lakil_api_key'];

    if (empty($api_key)) {
        echo '<p>Please enter your API key. <a href="options-general.php?page=leadslide-api-key-iframe-loader">Go to settings page</a></p>';
    } else {
        $options = [
            'headers' => array('Content-Type' => 'application/json; charset=utf-8'),
            'body' => json_encode(['api_key' => $api_key])
        ];

        $response = wp_remote_post($BASE_LS_API_URL.'published-wp-campaigns', $options);

        if (is_wp_error($response)) {
            echo '<p>Error: ' . $response->get_error_message() . '</p>';
        } else {
            $data = json_decode($response['body'], true);
            if (isset($data['data'])) {
                echo '<table>';
                echo '<tr><th>ID</th><th>Name</th><th>Actions</th></tr>';
                foreach ($data['data'] as $item) {
                    echo '<tr>';
                    echo '<td>' . esc_html($item['id']) . '</td>';
                    echo '<td>' . esc_html($item['name']) . '</td>';
                    echo '<td>';
                    $page = get_page_by_path($item['name'], OBJECT, 'page');
                    if ($page) {
                        echo '<button disabled>Campaign already exists</button>';
                    } else {
                        echo '<button class="add-campaign-button" data-campaign-name="'.esc_attr($item['name']).'" data-campaign-id="'.esc_attr($item['id']).'" data-publish-api-key="'.esc_attr($item['publish_api_key']).'">Add Campaign</button>';
                    }
                    echo '</td>';
                    echo '</tr>';
                }
                echo '</table>';
            } else {
                echo '<p>Error: Unexpected response from the API.</p>';
            }
        }
    }
}

function lakil_settings_page() {
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
        <?php if (!file_exists(get_template_directory() . '/leadslide-page-template.php')) : ?>
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
add_action('admin_init', 'lakil_delete_leadslide_template');
function lakil_delete_leadslide_template() {
    if (isset($_POST['action']) && $_POST['action'] === 'delete_leadslide_template') {
        $template_file = get_template_directory() . '/leadslide-page-template.php';

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
add_action('admin_init', 'lakil_install_leadslide_template');
function lakil_install_leadslide_template() {
    if (isset($_POST['action']) && $_POST['action'] === 'install_leadslide_template') {
        $plugin_dir = plugin_dir_path(__FILE__);
        $template_file = $plugin_dir . 'leadslide-page-template.php';

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

function lakil_app_page() {
    $options = get_option('lakil_options');
    $api_key = $options['lakil_api_key'];
    
    if (empty($api_key)) {
        echo '<p>Please enter your API key. <a href="options-general.php?page=leadslide-api-key-iframe-loader">Go to settings page</a></p>';
    } else {
        $options = [
            'headers' => array('Content-Type' => 'application/json; charset=utf-8'),
            'body' => json_encode(['api_key' => $api_key])
        ];

        // get current url
        $response = wp_remote_post('http://localhost:8080/api/basic/wp/auth/login/', $options);

        if (is_wp_error($response)) {
            echo '<p>Error: ' . $response->get_error_message() . '</p>';
        } else {
            $data = json_decode($response['body'], true);
            if (isset($data['url'])) {
                $wp_url = get_site_url();
                echo '<iframe src="' . esc_attr($data['url']) . '&refer='.$wp_url.'" frameborder="0" scrolling="yes" width="100%" style="min-height: 90vh; overflow-x: auto;"></iframe>';
            } else {
                echo '<p>Error: Unexpected response from the API.</p>';
            }
        }
    }
}

add_action('admin_init', 'lakil_register_settings');

function lakil_register_settings() {
    register_setting('lakil_options', 'lakil_options', 'lakil_sanitize_options');
    add_settings_section('lakil_settings', 'Settings', null, 'leadslide-api-key-iframe-loader');
    add_settings_field('lakil_api_key', 'API Key', 'lakil_api_key_field', 'leadslide-api-key-iframe-loader', 'lakil_settings');
}

function lakil_sanitize_options($options) {
    $sanitized_options = array();
    $sanitized_options['lakil_api_key'] = sanitize_text_field($options['lakil_api_key']);
    return $sanitized_options;
}

function lakil_api_key_field() {
    $options = get_option('lakil_options');
    echo '<input type="text" id="lakil_api_key" name="lakil_options[lakil_api_key]" value="' . esc_attr($options['lakil_api_key']) . '">';
}

add_action('admin_footer', 'lakil_add_campaign_javascript');

function lakil_add_campaign_javascript() {
    ?>
    <script type="text/javascript" >
        jQuery(document).ready(function($) {
            $('.add-campaign-button').click(function() {
                var data = {
                    'action': 'lakil_add_campaign',
                    'campaign_name': $(this).data('campaign-name'),
                    'campaign_id': $(this).data('campaign-id'),
                    'publish_api_key': $(this).data('publish-api-key')
                };

                $.post(ajaxurl, data, function(response) {
                    alert('Got this from the server: ' + response);
                });
            });
        });
    </script>
    <?php
}

add_action('wp_ajax_lakil_add_campaign', 'lakil_add_campaign');

function lakil_add_campaign() {
    $campaign_name = sanitize_text_field($_POST['campaign_name']);
    $campaign_id = sanitize_text_field($_POST['campaign_id']);
    $publish_api_key = sanitize_text_field($_POST['publish_api_key']);

    $page = array(
        'post_title'    => $campaign_name,
        'post_status'   => 'publish',
        'post_type'     => 'page',
        'page_template' => 'leadslide-page-template.php'
    );

    $page_id = wp_insert_post($page);

    if ($page_id != 0) {
        update_post_meta($page_id, 'campaign_id', $campaign_id);
        update_post_meta($page_id, 'publish_api_key', $publish_api_key);
        echo 'Page added successfully.';
    } else {
        echo 'An error occurred while adding the page.';
    }

    wp_die();
}

