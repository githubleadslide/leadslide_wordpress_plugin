<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

//add_action('admin_footer', 'leadslide_add_campaign_javascript');
add_action('wp_ajax_leadslide_add_campaign', 'leadslide_add_campaign');
add_action('admin_enqueue_scripts', 'leadslide_campaign_scripts');
add_action('wp_ajax_leadslide_manage_campaign', 'leadslide_manage_campaign');
add_action('admin_notices', 'leadslide_admin_notice');
add_action('init', 'leadslide_create_custom_post_type');

function leadslide_campaign_scripts() {
    /**
     * Add the leadslide ajax script which adds click events to the add campaigns activate and disable buttons
     * The javascript can be found in assets/js/campaigns.js
     */
    wp_enqueue_script('leadslide-admin', plugins_url('assets/js/campaigns.js', __FILE__), array('jquery'), '1.0.2', true);

    // pass Ajax Url to script.js
    wp_localize_script('leadslide-admin', 'leadslide_ajax', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'leadslide_ajax_nonce' )
    ));
}

function leadslide_admin_notice() {
    /**
     * Check transient, if available display notice
     */
    if ($message = get_transient('leadslide_admin_notice')) {
        ?>
        <div class="notice notice-success is-dismissible">
            <p><?php echo esc_html($message); ?></p>
        </div>
        <?php
    }
}

function leadslide_manage_campaign() {
    /**
     * Allows the user to manage their campaigns.
     * They can publish, unpublish and delete campaigns.
     * To edit a campaign the user must login to https://ai.leadslide.com
     */

    // Check for nonce and user permissions
    if (!check_ajax_referer('leadslide_ajax_nonce', 'nonce') || !current_user_can('manage_options')) {
        wp_die(__('Nonce verification failed.'));
    }

    // Force a boolean
    $is_new = filter_var(isset($_POST['is_new']) ? sanitize_text_field(wp_unslash($_POST['is_new'])) : false, FILTER_VALIDATE_BOOLEAN);

    if ($is_new === true || $is_new === 'true') {
        $campaign_name = sanitize_text_field(wp_unslash($_POST['campaign_name']));
        $campaign_id = sanitize_text_field(wp_unslash($_POST['campaign_id']));
        $publish_api_key = sanitize_text_field(wp_unslash($_POST['publish_api_key']));
        $campaign_url = sanitize_text_field(wp_unslash($_POST['campaign_url']));

        // Use custom post type 'leadslide_campaign'
        $post_name = sanitize_title($campaign_url);
        if (get_page_by_path($post_name, OBJECT, 'leadslide_campaign')) {
            set_transient('leadslide_admin_notice', 'An error occurred.', 60);
            echo "Error";
            wp_die();
        }

        $post = array(
            'post_title'    => $campaign_name,
            'post_name'     => $post_name,
            'post_status'   => 'publish',
            'post_type'     => 'leadslide_campaign',
        );

        $post_id = wp_insert_post($post);

        if ($post_id != 0) {
            update_post_meta($post_id, 'campaign_id', $campaign_id);
            update_post_meta($post_id, 'publish_api_key', $publish_api_key);
            set_transient('leadslide_admin_notice', 'Campaign published successfully.', 60);
        } else {
            set_transient('leadslide_admin_notice', 'An error occurred.', 60);
        }

        echo $post_id ? 'success' : 'error';
        wp_die();
    }

    $post_id = sanitize_text_field(wp_unslash($_POST['page_id']));
    $action = sanitize_text_field(wp_unslash($_POST['campaign_action']));
    if ($action === 'delete') {
        // delete the custom post type post
        $deleted = wp_delete_post($post_id, true);
        echo $deleted ? 'success' : 'error';
    } else {
        $post_status = $action == 'publish' ? 'publish' : 'draft';
        $post = array(
            'ID' => $post_id,
            'post_status' => $post_status
        );

        $updated = wp_update_post($post);
        if ($updated) {
            set_transient('leadslide_admin_notice', 'Campaign status changed successfully.', 60);
        } else {
            set_transient('leadslide_admin_notice', 'An error occurred.', 60);
        }

        echo $updated ? 'success' : 'error';
    }
    wp_die();
}


function leadslide_publish_campaign() {
    /**
     * Publishes a campaign by creating a new page in WordPress.
     * The page template is set to the LeadSlide page template.
     * The campaign_id and publish_api_key are stored as page meta.
     */
    global $BASE_LS_API_URL;
    $options = get_option('leadslide_options');
    $api_key = $options['leadslide_api_key'];
    // check current user has permissions
    if(!current_user_can('manage_options'))
    {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    if (empty($api_key)) {
        echo '<p>Please enter your API key. <a href="' . esc_url(admin_url('admin.php?page=leadslide-settings')) . '">Go to settings page</a></p>';
        return;
    }

    $options = [
        'headers' => array('Content-Type' => 'application/json; charset=utf-8'),
        'body' => json_encode(['api_key' => $api_key])
    ];

    $response = wp_remote_post($BASE_LS_API_URL.'published-wp-campaigns', $options);

    if (is_wp_error($response)) {
        echo '<p>Error: ' . esc_html($response->get_error_message()) . '</p>';
    } else {
        $data = json_decode($response['body'], true);
        if (isset($data['data'])) {
            echo '<h1>Campaigns</h1>';
            echo '<p>This is the campaigns page where you can activate published campaigns.</p>';

            echo '<p>To manage or edit pages and campaigns, please do so through <a href="https://www.leadslide.com" target="_blank">leadslide.com</a>.</p>';

            // Generate the list
            echo '<table class="leadslide-table">';
            echo '<tr><th>ID</th><th>Name</th><th>URL</th><th>Actions</th></tr>';
            foreach ($data['data'] as $item) {
                echo '<tr>';
                echo '<td style="text-align: center;">' . esc_html($item['id']) . '</td>';
                echo '<td>' . esc_html($item['campaign_name']) . '</td>';

                echo '<td><a target="_blank" href="/campaign/' . esc_html($item['url']) . '">' . esc_html($item['url']) . '</a></td>';

                echo '<td>';

                $post = get_page_by_path($item['url'], OBJECT, 'leadslide_campaign');

                if ($post) {
                    $buttonText = '';
                    $buttonAction = '';
                    if ($post->post_status == 'publish') {
                        $buttonText = 'Disable Campaign';
                        $buttonAction = 'disable';
                    } else if ($post->post_status == 'draft') {
                        $buttonText = 'Enable Campaign';
                        $buttonAction = 'publish';
                    }

                    // check page contains page meta campaign_id
                    $campaign_id = get_post_meta($post->ID, 'campaign_id', true);
                    // if not return error
                    if (empty($campaign_id)) {
                        echo '<button disabled>Page Exists</button>';
                    } else {
                        $page_id = esc_attr($post->ID);
                        $edit_link = get_edit_post_link($page_id);
                        echo '<button class="manage-campaign-button" data-page-id="'.esc_attr($post->ID).'" data-action="'.esc_attr($buttonAction).'">'. esc_html($buttonText) .'</button>';
                        echo '<button style="margin-left:15px;" class="manage-campaign-button" data-edit-link="'.esc_url($edit_link).'" data-page-id="'.esc_attr($post->ID).'" data-action="'.esc_attr('edit').'">Edit Page</button>';
                        echo '<button style="margin-left:15px;" class="manage-campaign-button" data-page-id="'.esc_attr($post->ID).'" data-action="'.esc_attr('delete').'">Delete Page</button>';

                    }
                } else {
                    echo '<button class="add-campaign-button" data-campaign-url="'.esc_attr($item['url']).'" data-campaign-name="'.esc_attr($item['campaign_name']).'" data-campaign-id="'.esc_attr($item['public_id']).'" data-publish-api-key="'.esc_attr($item['publish_api_key']).'">Add Campaign</button>';
                }
                echo '</td>';
                echo '</tr>';
            }
            echo '</table>';
        } else {
            if (isset($data['detail']) && $data['detail'] === 'Invalid API Key Pre') {
                echo '<p>The API Key you have entered is invalid. Please login to <a target="_blank" href="https://ai.leadslide.com/login">LeadSlide</a> click settings and generate a new api key.</p>';
            } else {
                echo '<p>Error: Unexpected response from the API. Please contact support@leadslide.com</p>';
            }
        }
    }
}

function leadslide_custom_post_type_template($single_template) {
    global $post;

    if ($post->post_type == 'leadslide_campaign') { // Make sure this matches your post type name
        $single_template = plugin_dir_path(__FILE__) . 'templates/leadslide-page-template.php';
    }

    return $single_template;
}
add_filter('single_template', 'leadslide_custom_post_type_template');


function leadslide_create_custom_post_type() {
    register_post_type('leadslide_campaign',
        array(
            'labels'      => array(
                'name'          => __('Leadslide Campaigns'),
                'singular_name' => __('Leadslide Campaign')
            ),
            'public'      => true,
            'has_archive' => true,
            'supports'    => array('title', 'editor', 'thumbnail'),
            'rewrite'     => array('slug' => 'campaign')
        )
    );
}

function leadslide_add_meta_boxes() {
    add_meta_box(
        'leadslide_campaign_details',
        'Leadslide Campaign Details',
        'leadslide_campaign_meta_box_callback',
        'leadslide_campaign'
    );
}
add_action('add_meta_boxes', 'leadslide_add_meta_boxes');

function leadslide_campaign_meta_box_callback($post) {
    // Add nonce for security and authentication.
    wp_nonce_field('leadslide_campaign_nonce_action', 'leadslide_campaign_nonce');

    // Retrieve existing values from the database.
    $campaign_id = get_post_meta($post->ID, 'campaign_id', true);
    $publish_api_key = get_post_meta($post->ID, 'publish_api_key', true);

    // Form fields.
    echo '<label for="campaign_id">Campaign ID:</label>';
    echo '<input type="text" id="campaign_id" name="campaign_id" value="' . esc_attr($campaign_id) . '" />';

    echo '<label for="publish_api_key">Publish API Key:</label>';
    echo '<input type="text" id="publish_api_key" name="publish_api_key" value="' . esc_attr($publish_api_key) . '" />';
}

function leadslide_save_postdata($post_id) {
    if (!isset($_POST['leadslide_campaign_nonce']) ||
        !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['leadslide_campaign_nonce'])), 'leadslide_campaign_nonce_action')) {
        return;
    }

    if (array_key_exists('campaign_id', $_POST)) {
        update_post_meta(
            $post_id,
            'campaign_id',
            sanitize_text_field(wp_unslash($_POST['campaign_id']))
        );
    }
    if (array_key_exists('publish_api_key', $_POST)) {
        update_post_meta(
            $post_id,
            'publish_api_key',
            sanitize_text_field(wp_unslash($_POST['publish_api_key']))
        );
    }
}
add_action('save_post', 'leadslide_save_postdata');
