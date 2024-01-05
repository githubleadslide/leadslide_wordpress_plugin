<?php
/*
Template Name: Leadslide Page
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

global $BASE_LEADSLIDE_VIEW_URL;
$leadslide_campaign_id = get_post_meta(get_the_ID(), 'campaign_id', true);
$leadslide_publish_api_key = get_post_meta(get_the_ID(), 'publish_api_key', true);

if (empty($leadslide_campaign_id) || empty($leadslide_publish_api_key)) {
    echo esc_html('Campaign not installed properly, please contact leadslide support.');
    return;
}

?>

<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }

        #leadslidePageFrame {
            width: 100%;
            height: 100%;
            border: none;
        }
    </style>
    <title>
        <?php echo esc_html(get_the_title()); ?>
    </title>
</head>
</html>
<body>
<iframe
        id="leadslidePageFrame"
        src="<?php echo esc_url($BASE_LEADSLIDE_VIEW_URL . 'cp/?cid=' . $leadslide_campaign_id . '&pak=' . $leadslide_publish_api_key); ?>"
        style="width:100%; height:100%; border:none;"
></iframe>

</body>
</html>



