<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

function leadsldie_valid_api_key_format($api_key) {
    $pattern = '/^[a-zA-Z0-9]+\.[a-zA-Z0-9]+$/';
    return preg_match($pattern, $api_key) === 1;
}

function leadslide_app_page() {
    global $BASE_LS_API_URL, $BASE_LEADSLIDE_WP_URL;
    $options = get_option('leadslide_options');
    $api_key = $options['leadslide_api_key'];

    if (empty($api_key) || !leadsldie_valid_api_key_format($api_key)) {
        echo '<p>Please enter your API key. <a href="options-general.php?page=leadslide-api-key-iframe-loader">Go to settings page</a></p>';
    } else {
        $options = [
            'headers' => array('Content-Type' => 'application/json; charset=utf-8'),
            'body' => json_encode(['api_key' => $api_key, 'refer_bck' => $BASE_LEADSLIDE_WP_URL])
        ];

        // get current url
        $response = wp_remote_post($BASE_LS_API_URL.'auth/login/', $options);

        if (is_wp_error($response)) {
            echo '<p>Error: ' . esc_html($response->get_error_message()) . '</p>';
        } else {
            $data = json_decode($response['body'], true);
            if (isset($data['url'])) {
                $wp_url = get_site_url();
                echo '<iframe src="' . esc_attr($data['url']) . '&refer='.$wp_url.'" frameborder="0" scrolling="yes" width="100%" style="min-height: 90vh; overflow-x: auto;"></iframe>';
            } else {
                if(isset($data['message'])){
                    echo '<h2>Error</h2>';
                    echo '<p>'.esc_html($data['message']).'</p>';
                } else {
                    if (isset($data['detail']) && $data['detail'] === 'Invalid API Key Pre') {
                        echo '<p>Please enter your API key. <a href="options-general.php?page=leadslide-api-key-iframe-loader">Go to settings page</a></p>';
                    } else {
                        echo '<p>Error: Unexpected response from the API. Please contact support@leadslide.com</p>';
                    }
                }
            }
        }
    }
}