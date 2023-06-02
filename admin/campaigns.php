<?php
add_action('admin_footer', 'lakil_add_campaign_javascript');
add_action('wp_ajax_lakil_add_campaign', 'lakil_add_campaign');

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