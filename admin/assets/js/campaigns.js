jQuery(document).ready(function($) {
    $('.manage-campaign-button').click(function() {
        if (!confirm('Are you sure you want to ' + $(this).data('action') + ' this page?')) {
            return;
        }

        var data = {
            'is_new': false,
            'action': 'leadslide_manage_campaign',
            'page_id': $(this).data('page-id'),
            'campaign_action': $(this).data('action')
        };
        console.log(data);

        $.post(leadslide_ajax.ajax_url, data, function(response) {
            location.reload();
        });
    });

    $('.add-campaign-button').click(function() {
        var data = {
            'is_new': true,
            'action': 'leadslide_manage_campaign',
            'campaign_name': $(this).data('campaign-name'),
            'campaign_id': $(this).data('campaign-id'),
            'publish_api_key': $(this).data('publish-api-key')
        };

        $.post(leadslide_ajax.ajax_url, data, function(response) {
            location.reload();
        });
    });
});

