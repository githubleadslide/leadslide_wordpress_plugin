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
    echo 'Campaign not installed properly, please contact leadslide support.';
    return;
}

?>

<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
            margin: 0;
            padding: 0;
        }
    </style>
    <title>
        <?php echo get_the_title(); ?>
    </title>
</head>
</html>
<body>
<iframe
        id="leadslidePageFrame"
        src="<?php echo $BASE_LEADSLIDE_VIEW_URL; ?>?cid=<?php echo $leadslide_campaign_id; ?>&pak=<?php echo $leadslide_publish_api_key; ?>" scrolling="yes" frameborder="0" width="100%" style="min-height:100vh;"></iframe>
</body>
</html>



