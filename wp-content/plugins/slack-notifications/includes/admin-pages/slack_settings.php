<?php
if (isset($_POST['tc_slack'])) {
    if (wp_verify_nonce($_POST['save_slack_settings_nonce'], 'save_slack_settings')) {
        update_option('tc_slack_settings', $_POST['tc_slack']);
    }
}

$tc_slack_settings = get_option('tc_slack_settings');
?>



<div class="wrap tc_wrap">

    <div id="poststuff" class="metabox-holder tc-settings">
        <div id="store_settings" class="postbox">
            <form action="" method="post" enctype="multipart/form-data">

                <h3><span><?php _e('Slack Notifications', 'slack'); ?></span></h3>
                <div class="inside">

                    <table class="form-table">
                        <tbody>
                            <tr>
                                <th scope="row"><label for="title"><?php _e('Notification Title', 'slack') ?></label></th>
                                <td><input name="tc_slack[title]" type="text" id="title" value="<?php echo isset($tc_slack_settings['title']) ? $tc_slack_settings['title'] : 'New Sale!'; ?>" class="regular-text">
                                    <p class="description"><?php _e('Title of the Slack notification', 'slack') ?></p>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row"><label for="bot_name"><?php _e('Bot Name', 'slack') ?></label></th>
                                <td><input name="tc_slack[bot_name]" type="text" id="bot_name" value="<?php echo isset($tc_slack_settings['bot_name']) ? $tc_slack_settings['bot_name'] : 'Ticket Sales'; ?>" class="regular-text">
                                    <p class="description"><?php _e('Enter the name of your Bot, the default is: Ticket Sales', 'slack'); ?></p></td>
                            </tr>

                            <tr>
                                <th scope="row"><label for="bot_icon"><?php _e('Bot Icon', 'slack') ?></label></th>
                                <td><input name="tc_slack[bot_icon]" type="text" id="bot_icon" value="<?php echo isset($tc_slack_settings['bot_icon']) ? $tc_slack_settings['bot_icon'] : ':moneybag:'; ?>" class="regular-text">
                                    <p class="description"><?php _e('Enter the emoji icon for your bot. Click <a href="http://emoji-cheat-sheet.com" target="_blank">here</a> to view the list of available emoji icon. You are to enter only a single emoji icon. The default is :moneybag:', 'slack'); ?></p></td>
                            </tr>

                            <tr>
                                <th scope="row"><label for="channel_name"><?php _e('Channel Name', 'slack') ?></label></th>
                                <td><input name="tc_slack[channel_name]" type="text" id="channel_name" value="<?php echo isset($tc_slack_settings['channel_name']) ? $tc_slack_settings['channel_name'] : '#ticketsales'; ?>" class="regular-text">
                                    <p class="description"><?php _e("Enter the name of the existing Slack channel notifications should be sent to e.g. #ticketsales", 'slack') ?></p></td>
                            </tr>

                            <tr>
                                <th scope="row"><label for="webhook_url"><?php _e('Webhook URL', 'slack') ?></label></th>
                                <td><input name="tc_slack[webhook_url]" type="text" id="webhook_url" value="<?php echo isset($tc_slack_settings['webhook_url']) ? $tc_slack_settings['webhook_url'] : ''; ?>" class="regular-text">
                                    <p class="description"><?php _e('Enter the URL of the webhook created for the channel above. This can be created <a href="https://my.slack.com/services/new/incoming-webhook/" target="_blank">here</a>', 'slack'); ?></p></td>
                            </tr>

                        </tbody>
                    </table>
                    <?php wp_nonce_field('save_slack_settings', 'save_slack_settings_nonce'); ?>

                </div>

        </div>

        <?php submit_button(); ?>
        </form>
    </div><!-- #poststuff -->
</div><!-- .wrap -->