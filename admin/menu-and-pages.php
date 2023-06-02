<?php
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


