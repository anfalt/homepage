<?php

add_action('admin_init', 'customizeSettingsPage');
add_action('wp_ajax_custom_score_team_sync', 'custom_score_team_sync');



function customizeSettingsPage()
{
    add_settings_field(
        'custom_score_team_sync',
        'Start Sync of teams',
        'custom_score_team_sync_callback',
        'general'
    );

    function custom_score_team_sync_callback()
    { // Section Callback
?>
        <input type="text" name="custom_score_team_sync_url" id="custom_score_team_sync_url" value="https://btv.liga.nu/cgi-bin/WebObjects/nuLigaTENDE.woa/wa/clubTeams?club=22994" />
        <input type="button" name="custom_score_team_sync" id="custom_score_team_sync" value="Start team sync" />
        <script>
            jQuery("#custom_score_team_sync").click(function() {
                var url = jQuery("#custom_score_team_sync_url").val();
                var data = {
                    'action': 'custom_score_team_sync',
                    'sync_url': url
                };
                jQuery.post(ajaxurl, data, function(response) {});
            })
        </script>
<?php
    }
}
