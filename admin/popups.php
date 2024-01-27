<?php
function leadslide_register_shortcode() {
    add_shortcode('leadslide_popup', 'leadslide_popup_shortcode_handler');
}
add_action('init', 'leadslide_register_shortcode');

function leadslide_popups_page() {
    global $BASE_LEADSLIDE_VIEW_URL;
    /**
     * This is the admin page popups.
     * The API will be sent and a list of available popup campaigns
     * will be returned. Please make sure these have been published
     * using the leadslide publishing tool on ai.leadslide.com
     */
    if(!current_user_can('manage_options'))
    {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    global $BASE_LS_API_URL;
    $options = get_option('leadslide_options'); // Fetch plugin options
    $api_key = sanitize_text_field($options['leadslide_api_key']);
    if (empty($api_key)) {
        echo '<p>Please enter your API key. <a href="' . esc_url(admin_url('admin.php?page=leadslide-settings')) . '">Go to settings page</a></p>';
        return;
    }

    $popup_api_url = $BASE_LS_API_URL . 'short-codes'; // Construct API URL
    $api_request_options = [
        'headers' => array('Content-Type' => 'application/json; charset=utf-8'),
        'body' => json_encode(['api_key' => $api_key])
    ];
    $response = wp_remote_post($popup_api_url, $api_request_options);
    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        // Display an error message in the admin notices
        add_action('admin_notices', function() use ($error_message) {
            echo '<div class="notice notice-error is-dismissible"><p>Error: ' . esc_html($error_message) . '</p></div>';
        });
        // log the error in debug.log if debugging is enabled
        error_log('Leadslide API Error: ' . $error_message);
        return;
    }

    // Decode the response body
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (!$data || !is_array($data)) {
        echo '<div class="notice notice-error is-dismissible"><p>Error: Invalid API response</p></div>';
        return;
    }

    echo '<h2>LeadSlide Popup Campaigns:</h2>';
    echo '<p>LeadSlide popup campaigns can be added by using a shortcode from the table below.</p>';
    echo '<p>A list of attributes can be given to the shortcode to allow you to customise it.</p>';
    echo '<table class="leadslide-table">';
    echo '<thead><tr><th>Campaign Name</th><th style="width:300px;">Shortcode</th><th>Embed Code</th><th>Actions</th></tr></thead>';
    echo '<tbody>';

    foreach ($data as $item) {

        $shortcode_tag = 'leadslide_popup id="' . $item[0]['public_id'] . '" key="' . $item[0]['publish_api_key'] . '"';
        $embed_code = htmlentities('<iframe scrolling="no" src="' . $BASE_LEADSLIDE_VIEW_URL . 'popup/' . $item[0]['public_id'] . '/' . $item[0]['publish_api_key'] . '" width="560" height="315"></iframe>');
        $embed_code_to_copy = $embed_code;
        // Display the campaign name and shortcode in a table row
        echo '<tr>';
        echo '<td>' . esc_html($item[0]['campaign_name']) . '</td>';
        echo '<td>[' . esc_html($shortcode_tag) . ']</td>';
        echo '<td><textarea rows="12" readonly>' . $embed_code . '</textarea></td>';
        echo '<td><button onclick="leadslidecopyToClipboard(this, \'Copied embed code\')" data-clipboard-text="' . esc_attr($embed_code_to_copy) . '">Copy Embed Code</button>';
        echo '<button onclick="leadslidecopyToClipboard(this, \'Copied shortcode\')" data-clipboard-text="' . esc_attr($shortcode_tag) . '">Copy Shortcode</button></td>';
        echo '</tr>';
    }
    // list of available attrbiutes
    $attributes = [
        'id' => [
            'description' => 'The ID of the popup campaign. This is required.',
            'required' => true
        ],
        'key' => [
            'description' => 'The publish API key of the popup campaign. This is required.',
            'required' => true
        ],
        'text' => [
            'description' => 'The text to display in the link. Default: Click Here',
            'required' => false
        ],
        'class' => [
            'description' => 'The class to apply to the link. Default: leadslide-popup-trigger',
            'required' => false
        ],
        'style' => [
            'description' => 'The style to apply to the link. We recommend using class instead of styles. Default: none',
            'required' => false
        ],
    ];
    echo '</tbody></table>';
    echo '<h2>Custom Attributes</h2>';
    echo '<p>Required attributes will be displayed with an astrix (*)</p>';
    echo '<table class="leadslide-table">';
    echo '<thead><tr><th>Attrbiute</th><th>Description</th></tr></thead>';
    echo '<tbody>';
    foreach ($attributes as $key => $value) {
        echo '<tr>';
        echo '<td>' . esc_html($key) . ($value['required'] ? ' <span style="color: red;">*</span>' : '') . '</td>';
        echo '<td>' . esc_html($value['description']) . '</td>';
        echo '</tr>';
    }
    echo '</tbody></table>';

    echo '<script type="text/javascript">
        const leadslidecopyToClipboard = (button, message) => {
            let textToCopy = button.getAttribute("data-clipboard-text");
    
            navigator.clipboard.writeText(textToCopy).then(function () {
                alert(message);
            })
            .catch(function (err) {
                console.error("Error in copying text: ", err);
            });
        }
    </script>';


}

function leadslide_popup_shortcode_handler($atts = [], $content = null, $tag = '') {
    wp_enqueue_style('leadslide-popup-style', plugin_dir_url(__FILE__) . 'assets/css/popup.css');
    wp_enqueue_script('leadslide-popup-script', plugin_dir_url(__FILE__) . 'assets/js/popup.js', array(), '1.0', true);

    $available_attributes = [
        'id' => [
            'required' => true,
            'default' => ''
        ],
        'key' => [
            'required' => true,
            'default' => ''
        ],
        'text' => [
            'required' => false,
            'default' => 'Click Here'
        ],
        'class' => [
            'required' => false,
            'default' => 'leadslide-popup-trigger'
        ],
        'style' => [
            'required' => false,
            'default' => ''
        ]
    ];

    $default_attributes = [];
    $errors = false;
    foreach ($available_attributes as $key => $value) {
        if($value['default'])
        {
            $default_attributes[$key] = $value['default'];
        }
        if($value['required'] && !isset($atts[$key]))
        {
            $errors = true;
            echo '<div class="notice notice-error is-dismissible"><p>Error: The attribute ' . esc_html($key) . ' is required.</p></div>';
        } else {
            if(isset($atts[$key]))
            {
                $default_attributes[$key] = $atts[$key];
            } else {
                $default_attributes[$key] = $value['default'];
            }
        }
    }

    if($errors)
    {
        return "";
    }

    $atts = shortcode_atts($default_attributes, $atts, $tag);
    // get popup id and key from attr
    $popup_id = sanitize_text_field($atts['id']);
    $popup_key = sanitize_text_field($atts['key']);
    // get popup data from api
    global $BASE_LEADSLIDE_VIEW_URL;
    // get content from a get request to the api $BASE_LS_API_URL + 'short-code/' + $popup_id + '/' + $popup_key
    $popup_iframe_url = $BASE_LEADSLIDE_VIEW_URL . 'popup/' . $popup_id . '/' . $popup_key;
    $popup_iframe_url = esc_url($popup_iframe_url);
    $popup_iframe = '<a href="#" class="' . esc_attr($atts['class']) . '" style="' . esc_attr($atts['style']) . '">' . esc_html($atts['text']) . '</a>';
    $popup_iframe .= '<script type="text/javascript">
    document.addEventListener("DOMContentLoaded", function() {
        let popupWrapper = document.createElement("div");
        popupWrapper.id = "leadslide-popup-wrapper";
        let popup = document.createElement("div");
        popup.id = "leadslide-popup";
        popupWrapper.appendChild(popup);
        let iframe = document.createElement("iframe");
        iframe.src = "' . esc_url($popup_iframe_url) . '";
        iframe.scrolling = "no";
        popup.appendChild(iframe);
        document.body.appendChild(popupWrapper);

        let popupTrigger = document.querySelector(".' . esc_attr($atts['class']) . '");
        popupTrigger.addEventListener("click", function(e) {
            e.preventDefault();
            popupWrapper.style.display = "block";
        });
        popupWrapper.addEventListener("click", function(e) {
            popupWrapper.style.display = "none";
        });
        
        
    });
</script>';




    return $popup_iframe;
}








