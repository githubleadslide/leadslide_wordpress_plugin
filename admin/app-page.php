<?php
function leadslide_app_page() {
    $options = get_option('leadslide_options');
    $api_key = $options['leadslide_api_key'];

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