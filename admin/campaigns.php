<?php
//add_action('admin_footer', 'leadslide_add_campaign_javascript');
add_action('wp_ajax_leadslide_add_campaign', 'leadslide_add_campaign');
add_action('admin_enqueue_scripts', 'leadslide_campaign_scripts');
add_action('wp_ajax_leadslide_manage_campaign', 'leadslide_manage_campaign');
add_action('admin_notices', 'leadslide_admin_notice');

function leadslide_campaign_scripts() {
    wp_enqueue_script('leadslide-admin', plugins_url('assets/js/campaigns.js', __FILE__), array('jquery'), '1.0.0', true);

    // pass Ajax Url to script.js
    wp_localize_script('leadslide-admin', 'leadslide_ajax', array( 'ajax_url' => admin_url( 'admin-ajax.php' )));
}

function leadslide_admin_notice() {
    if ($message = get_transient('leadslide_admin_notice')) {
        ?>
        <div class="notice notice-success is-dismissible">
            <p><?php echo $message; ?></p>
        </div>
        <?php
    }
}

function leadslide_manage_campaign() {
    $is_new = true;
    if(isset($_POST['is_new']))
    {
        $is_new = $_POST['is_new'];
    }


    if($is_new === true || $is_new === 'true')
    {
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
            set_transient('leadslide_admin_notice', 'Campaign published successfully.', 60);
        } else {
            set_transient('leadslide_admin_notice', 'An error occurred.', 60);
        }

        echo $page_id ? 'success' : 'error';
        wp_die();
    }
    $page_id = sanitize_text_field($_POST['page_id']);
    $action = sanitize_text_field($_POST['campaign_action']);

    $page = array(
        'ID' => $page_id,
        'post_status' => $action == 'publish' ? 'publish' : 'draft'
    );

    $updated = wp_update_post($page);
    if($updated) {
        set_transient('leadslide_admin_notice', 'Campaign status changed successfully.', 60);
    } else {
        set_transient('leadslide_admin_notice', 'An error occurred.', 60);
    }

    echo $updated ? 'success' : 'error';
    wp_die();
}

function leadslide_publish_campaign() {
    global $BASE_LS_API_URL;
    $options = get_option('leadslide_options');
    $api_key = $options['leadslide_api_key'];

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
                        $buttonText = '';
                        $buttonAction = '';
                        if ($page->post_status == 'publish') {
                            $buttonText = 'Disable Campaign';
                            $buttonAction = 'disable';
                        } else if ($page->post_status == 'draft') {
                            $buttonText = 'Enable Campaign';
                            $buttonAction = 'publish';
                        }

                        // check page contains page meta campaign_id
                        $campaign_id = get_post_meta($page->ID, 'campaign_id', true);
                        // if not return error
                        if(empty($campaign_id)) {
                            echo '<button disabled>Page Exists</button>';
                        } else {
                            echo '<button class="manage-campaign-button" data-page-id="'.esc_attr($page->ID).'" data-action="'.esc_attr($buttonAction).'">'. $buttonText .'</button>';
                        }
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