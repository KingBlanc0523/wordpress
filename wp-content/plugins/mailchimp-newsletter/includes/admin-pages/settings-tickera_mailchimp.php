<?php

if (isset($_POST[ 'save_mailchimp_settings_nonce' ])){
    if ( wp_verify_nonce( $_POST[ 'save_mailchimp_settings_nonce' ], 'save_mailchimp_settings' ) ) {
     update_option( 'tc_mailchimp_settings', $_POST[ 'tc_mailchimp' ] );
    }
}

$tc_mailchimp_settings = get_option( 'tc_mailchimp_settings' );
$tc_general_settings = get_option('tc_general_setting', false);
?>
<div class="wrap tc_wrap" style="opacity: 1;">
	<div id="poststuff" class="metabox-holder tc-settings">
            <form action="" method="post" name="save_mailchimp_options" enctype="multipart/form-data">
                <div id="store_settings" class="postbox">
                    <h3>
                        <span>
                            <?php _e('Mailchimp Options','tc-mailchimp'); ?>
                        </span>
                    </h3>
                    <div class="inside">
                        <table class="form-table">
                            <tbody>

                                
                                
                                <tr>
                                    <th scope="row"><label for="list_id"><?php _e( 'Disable Mailchimp', 'tc-mailchimp' ) ?></label></th>
                                    <td><input name="tc_mailchimp[disable_mailchimp]" type="checkbox" id="disable_mailchimp" value="1" <?php if ( isset( $tc_mailchimp_settings[ 'disable_mailchimp' ] ) ) {
                        echo "checked"; } ?>>
                                        <p class="description"><?php _e( 'Check to disable Mailchimp submission', 'tc-mailchimp' ); ?></p></td>
                                </tr>


                                <tr>
                                    <th scope="row"><label for="api_key"><?php _e( 'API Key', 'tc-mailchimp' ) ?></label></th>
                                    <td><input name="tc_mailchimp[api_key]" type="text" id="api_key" value="<?php echo isset( $tc_mailchimp_settings[ 'api_key' ] ) ? $tc_mailchimp_settings[ 'api_key' ] : ''; ?>" class="regular-text">
                                        <p class="description"><?php _e( 'Set the MailChimp API key.', 'tc-mailchimp' ); ?></p>
                                    </td>
                                </tr>

                                <tr>
                                    <th scope="row"><label for="list_id"><?php _e( 'List ID', 'tc-mailchimp' ) ?></label></th>
                                    <td><input name="tc_mailchimp[list_id]" type="text" id="list_id" value="<?php echo isset( $tc_mailchimp_settings[ 'list_id' ] ) ? $tc_mailchimp_settings[ 'list_id' ] : ''; ?>" class="regular-text"> <a href="#" class="tc-test-submission"><?php _e('Test newsletter submission', 'tc-mailchimp'); ?></a><div class="tc-show-message"></div>
                                        <p class="description"><?php _e( 'Set the Mailchimp list ID', 'tc-mailchimp' ); ?></p></td>
                                </tr>
                                
                                <?php $tc_emails_to_collect = $tc_mailchimp_settings['tc_emails_to_collect'];
                                
                                if(!isset($tc_emails_to_collect)) {
                                    $tc_emails_to_collect = 'both_emails';
                                }
                                ?>
                                <tr>
                                    <th scope="row"><label for="show_owner_fields">E-Mails To Collect:</label></th>
                                    <td>
                                        <label>
                                            <input type="radio" class="tc_emails_to_collect" name="tc_mailchimp[tc_emails_to_collect]" value="buyer_emails" <?php if($tc_emails_to_collect == 'buyer_emails'){ ?> checked="checked" <?php } ?>>Buyer E-Mails    
                                        </label>
                                        <br/>
                                        <label>
                                            <input type="radio" class="tc_emails_to_collect"  name="tc_mailchimp[tc_emails_to_collect]" value="owner_emails" <?php if($tc_emails_to_collect == 'owner_emails'){ ?>checked="checked" <?php } ?>>Ticket Attendee E-Mails
                                        </label>
                                        <br/>
                                        <label>
                                            <input type="radio" class="tc_emails_to_collect" name="tc_mailchimp[tc_emails_to_collect]" value="both_emails" <?php if($tc_emails_to_collect == 'both_emails'){ ?>checked="checked" <?php } ?>>Ticket Attendee and Buyer E-mails.    
                                        </label>
                                        <br/>
                                        
                                        <span class="description">Attendee e-mails will not be subscribed if attendee fields and e-mail are not enabled in Tickera settings. </span>
                                    </td>
                                </tr>

                                
                                <?php if ( class_exists( 'TC_WooCommerce_Bridge' ) ) { ?>
                                    <tr>
                                        <th scope="row"><label for="users_buying_tickets"><?php _e( 'Subscribe ticket buying customers only', 'tc-mailchimp' ) ?></label></th>
                                        <td><input name="tc_mailchimp[users_buying_tickets]" type="checkbox" id="users_buying_tickets" value="1" <?php if ( isset( $tc_mailchimp_settings[ 'users_buying_tickets' ] ) ) { echo "checked"; } ?>>
                                            <p class="description"><?php _e( 'If checked only the customers who bought tickets will be subscribed.', 'tc-mailchimp' ); ?></p></td>
                                    </tr>
                                <?php } ?>

                                <tr>
                                    <th scope="row"><label for="send_welcome"><?php _e( 'Send Welcome E-mail', 'tc-mailchimp' ) ?></label></th>
                                    <td><input name="tc_mailchimp[send_welcome]" type="checkbox" id="send_welcome" value="1" <?php if ( isset( $tc_mailchimp_settings[ 'send_welcome' ] ) ) {
                        echo "checked"; } ?>>
                                        <p class="description"><?php _e( 'Check to enable Mailchimp to send welcome e-mail (only if you have paid subscription at Mailchimp).', 'tc-mailchimp' ); ?></p></td>
                                </tr>
                                
                                <tr>
                                    <th scope="row"><label for="double_optin"><?php _e( 'Enable Double Opt-In', 'tc-mailchimp' ) ?></label></th>
                                    <td><input name="tc_mailchimp[double_optin]" type="checkbox" id="double_optin" value="1" <?php if ( isset( $tc_mailchimp_settings[ 'double_optin' ] ) ) {
                        echo "checked"; } ?>>
                                        <p class="description"><?php _e( 'If enabled, customers will receive mail prompting them to confirm their subscription.', 'tc-mailchimp' ); ?></p></td>
                                </tr>
                                
                                <tr>
                                    <th scope="row"><label for="double_optin"><?php _e( 'Enable Confirmation Checkbox', 'tc-mailchimp' ) ?></label></th>
                                    <td><input name="tc_mailchimp[enable_confirmation]" type="checkbox" id="enable_confirmation" value="1" <?php if ( isset( $tc_mailchimp_settings[ 'enable_confirmation' ] ) ) {
                        echo "checked"; } ?>>
                                        <p class="description"><?php _e( 'If enabled, customers will see checkbox to confirm subscription, if not customers will be automatically subscribed.', 'tc-mailchimp' ); ?></p></td>
                                </tr>

                            </tbody>
                        </table>
                    </div><!-- inside -->
                </div><!-- store-settings -->
                <?php wp_nonce_field( 'save_mailchimp_settings', 'save_mailchimp_settings_nonce' ); ?>
                <?php submit_button('','primary','save_mailchimp_options'); ?>
            </form>
            
        </div><!-- poststuff -->
</div><!-- wrap -->
