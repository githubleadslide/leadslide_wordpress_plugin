<?php
/*
Template Name: Leadslide Page
*/
global $BASE_VIEW_URL;
$campaign_id = get_post_meta(get_the_ID(), 'campaign_id', true);
$publish_api_key = get_post_meta(get_the_ID(), 'publish_api_key', true);

if (empty($campaign_id) || empty($publish_api_key)) {
    echo 'The custom fields are not set properly. Please set them.';
    return;
}

?>

<iframe src="<?php echo $BASE_VIEW_URL; ?>?cid=<?php echo $campaign_id; ?>&pak=<?php echo $publish_api_key; ?>" frameborder="0" width="100%" style="height:100vh;"></iframe>