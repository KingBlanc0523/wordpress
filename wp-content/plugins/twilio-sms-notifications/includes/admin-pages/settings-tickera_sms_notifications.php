<?php
$settings = get_option( 'tc_settings' );

if ( isset( $_POST[ 'save_tc_sms_options' ] ) ) {
	if ( current_user_can( 'manage_options' ) ) {

		if ( isset( $_POST[ 'tc' ] ) ) {
			$tc_twilio			 = stripslashes_deep_twilio( $_POST[ 'tc' ] );
			$filtered_settings	 = apply_filters( 'tc_sms_settings_filter', $tc_twilio );
			$settings			 = array_merge( $settings, $filtered_settings );
			update_option( 'tc_settings', $settings );
		}
	}
}

//function for stripping slashes on fields
function stripslashes_deep_twilio( $value ) {
	$value = is_array( $value ) ?
	array_map( 'stripslashes_deep_twilio', $value ) :
	stripslashes( $value );
	return $value;
}

$settings								 = get_option( 'tc_settings' );

?>
<div class="wrap tc_wrap">
	<div id="poststuff" class="metabox-holder tc-settings">
		<form action="" method="post" enctype = "multipart/form-data">

			<div id="store_settings" class="postbox">
				<h3 class="hndle"><span><?php _e( 'Twillio Account', 'tw' ); ?></span></h3>
				<div class="inside">
					<table class="form-table">

						<tbody>

							<tr valign="top">
								<th scope="row"><label><?php _e( 'Account SID', 'tw' ); ?></label></th>
								<td>
									<input type="text" name='tc[sms_options][account_sid]' value='<?php echo isset( $settings[ 'sms_options' ][ 'account_sid' ] ) ? esc_attr( $settings[ 'sms_options' ][ 'account_sid' ] ) : ''; ?>' />
									<span class="description"><?php printf( __( "If you don't have Twilio account already, open an account %shere%s", 'tw' ), '<a target="_blank" href="https://www.twilio.com/">', '</a>' ); ?></span>
								</td>
							</tr>

							<tr valign="top">
								<th scope="row"><label><?php _e( 'Auth Token', 'tw' ); ?></label></th>
								<td>
									<input type="password" name='tc[sms_options][auth_token]' value='<?php echo isset( $settings[ 'sms_options' ][ 'auth_token' ] ) ? esc_attr( $settings[ 'sms_options' ][ 'auth_token' ] ) : ''; ?>' />
									<span class="description"><?php printf( __( 'You can find both Account SID and the Auth Token under your %sTwillio Settings%s > API Credentials' ), '<a target="_blank" href="https://www.twilio.com/user/account/settings">', '</a>' ) ?></span>
								</td>
							</tr>

							<tr valign="top">
								<th scope="row"><label><?php _e( 'From Phone Number', 'tw' ); ?></label></th>
								<td>
									<input type="text" name='tc[sms_options][from_phone]' value='<?php echo isset( $settings[ 'sms_options' ][ 'from_phone' ] ) ? esc_attr( $settings[ 'sms_options' ][ 'from_phone' ] ) : ''; ?>' />
									<span class="description"><?php printf( __( '<strong>IMPORTANT:</strong> here you must add %syour number purchased from Twillio%s. For example: +12016453123. You can also use <a target="_blank" href="https://support.twilio.com/hc/en-us/articles/223181348-Getting-started-with-Alphanumeric-Sender-ID">Alphanumeric Sender ID</a>.', 'tw' ), '<a target="_blank" href="https://www.twilio.com/user/account/phone-numbers/incoming">', '</a>' ); ?></span>
								</td>
							</tr>
                                                        
                                                        <?php do_action( 'tc_after_twilio_settings' ); ?>

						</tbody>
					</table>
				</div>
			</div>

			<div id="store_settings" class="postbox">
				<h3 class="hndle"><span><?php _e( 'Admin SMS Notification', 'tw' ); ?></span></h3>
				<div class="inside">
					<table class="form-table">

						<tbody>

							<tr valign="top">
								<th scope="row"><label><?php _e( 'Admin Purchase Notification', 'tw' ); ?></label></th>
								<td>
									<label>
										<?php
										$send_purchase_notifications_to_admin	 = isset( $settings[ 'sms_options' ][ 'send_purchase_notifications_to_admin' ] ) && $settings[ 'sms_options' ][ 'send_purchase_notifications_to_admin' ] == '1' ? 1 : 0;
										?>
										<input type="radio" name="tc[sms_options][send_purchase_notifications_to_admin]" value="1" <?php checked( $send_purchase_notifications_to_admin, '1', true ); ?>><?php _e( 'Yes', 'tw' ) ?></label>
									<label>
										<input type="radio" name="tc[sms_options][send_purchase_notifications_to_admin]" value="0" <?php checked( $send_purchase_notifications_to_admin, '0', true ); ?>><?php _e( 'No', 'tw' ) ?>	</label>
									<span class="description"><?php _e( 'A text message will be sent to the admin for each successful purchase.', '' ); ?></span>
								</td>
							</tr>

							<tr valign="top">
								<th scope="row"><label><?php _e( 'Admin Phone Number', 'tw' ); ?></label></th>
								<td>
									<input type="text" name='tc[sms_options][admin_phone]' value='<?php echo isset( $settings[ 'sms_options' ][ 'admin_phone' ] ) ? esc_attr( $settings[ 'sms_options' ][ 'admin_phone' ] ) : ''; ?>' />
									<span class="description"><?php _e( 'Mobile phone number with "+" and country code', 'tw' ); ?></span>
								</td>
							</tr>

							<tr valign="top">
								<th scope="row"><label><?php _e( 'Message Content', 'tw' ); ?></label></th>
								<td>
									<input type="text" name='tc[sms_options][admin_sms_content]' value='<?php echo isset( $settings[ 'sms_options' ][ 'admin_sms_content' ] ) ? esc_attr( $settings[ 'sms_options' ][ 'admin_sms_content' ] ) : ''; ?>' />
									<span class="description"><?php _e( "You can use following placeholders (ORDER_ID, ORDER_TOTAL, ORDER_ADMIN_URL)", 'tw' ); ?></span>
								</td>
							</tr>
                                                        
                                                        <?php do_action( 'tc_after_admin_notification_settings' ); ?>

						</tbody>
					</table>
				</div>
			</div>

			<div id="store_settings" class="postbox">
				<h3 class="hndle"><span><?php _e( 'Collect Mobile Numbers', 'tw' ); ?></span></h3>
				<div class="inside">
					<table class="form-table">

						<tbody>

							<tr valign="top">
								<th scope="row"><label><?php _e( 'For Ticket Buyers', 'tw' ); ?></label></th>
								<td>
									<label>
										<?php
										$collect_mobile_buyers					 = isset( $settings[ 'sms_options' ][ 'collect_mobile_buyers' ] ) && $settings[ 'sms_options' ][ 'collect_mobile_buyers' ] == '1' ? 1 : 0;
										?>
										<input type="radio" name="tc[sms_options][collect_mobile_buyers]" value="1" <?php checked( $collect_mobile_buyers, '1', true ); ?>><?php _e( 'Yes', 'tw' ) ?></label>
									<label>
										<input type="radio" name="tc[sms_options][collect_mobile_buyers]" value="0" <?php checked( $collect_mobile_buyers, '0', true ); ?>><?php _e( 'No', 'tw' ) ?>	</label>
								</td>
							</tr>

							<tr valign="top">
								<th scope="row"><label><?php _e( 'Buyer Phone Field Title', 'tw' ); ?></label></th>
								<td>
									<input type="text" name='tc[sms_options][buyer_mobile_phone_field_title]' value='<?php echo isset( $settings[ 'sms_options' ][ 'buyer_mobile_phone_field_title' ] ) ? esc_attr( $settings[ 'sms_options' ][ 'buyer_mobile_phone_field_title' ] ) : 'Mobile Phone'; ?>' />
								</td>
							</tr>

							<tr valign="top">
								<th scope="row"><label><?php _e( 'Buyer Phone Field Description', 'tw' ); ?></label></th>
								<td>
									<input type="text" name='tc[sms_options][buyer_mobile_phone_field_description]' value='<?php echo isset( $settings[ 'sms_options' ][ 'buyer_mobile_phone_field_description' ] ) ? esc_attr( $settings[ 'sms_options' ][ 'buyer_mobile_phone_field_description' ] ) : 'Mobile phone number with "+" and country code'; ?>' />
								</td>
							</tr>

                                                         <?php do_action( 'tc_after_collect_mobile_buyers' ); ?>
                                                        
                                                        
							<tr valign="top">
								<td colspan="2">
									<hr />
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><label><?php _e( 'For Ticket Owners', 'tw' ); ?></label></th>
								<td>
									<label>
										<?php
										$collect_mobile_owners					 = isset( $settings[ 'sms_options' ][ 'collect_mobile_owners' ] ) && $settings[ 'sms_options' ][ 'collect_mobile_owners' ] == '1' ? 1 : 0;
										?>
										<input type="radio" name="tc[sms_options][collect_mobile_owners]" value="1" <?php checked( $collect_mobile_owners, '1', true ); ?>><?php _e( 'Yes', 'tw' ) ?></label>
									<label>
										<input type="radio" name="tc[sms_options][collect_mobile_owners]" value="0" <?php checked( $collect_mobile_owners, '0', true ); ?>><?php _e( 'No', 'tw' ) ?>	</label>
									<span class="description"><?php _e( 'An additional input field will be visible on the cart page.', 'tw' ); ?></span>
								</td>
							</tr>

							<tr valign="top">
								<th scope="row"><label><?php _e( 'Owner Phone Field Title', 'tw' ); ?></label></th>
								<td>
									<input type="text" name='tc[sms_options][owner_mobile_phone_field_title]' value='<?php echo isset( $settings[ 'sms_options' ][ 'owner_mobile_phone_field_title' ] ) ? esc_attr( $settings[ 'sms_options' ][ 'owner_mobile_phone_field_title' ] ) : 'Mobile Phone'; ?>' />
								</td>
							</tr>

							<tr valign="top">
								<th scope="row"><label><?php _e( 'Owner Phone Field Description', 'tw' ); ?></label></th>
								<td>
									<input type="text" name='tc[sms_options][owner_mobile_phone_field_description]' value='<?php echo isset( $settings[ 'sms_options' ][ 'owner_mobile_phone_field_description' ] ) ? esc_attr( $settings[ 'sms_options' ][ 'owner_mobile_phone_field_description' ] ) : 'Mobile phone number with "+" and country code'; ?>' />
								</td>
							</tr>
                                                        
                                                        <?php do_action( 'tc_after_collect_mobile_owners' ); ?>

						</tbody>
					</table>
				</div>
			</div>

			<div id="store_settings" class="postbox">
				<h3 class="hndle"><span><?php _e( 'Buyer SMS Notification', 'tw' ); ?></span></h3>
				<div class="inside">
					<table class="form-table">

						<tbody>

							<tr valign="top">
								<th scope="row"><label><?php _e( 'Buyer Purchase Notification', 'tw' ); ?></label></th>
								<td>
									<label>
										<?php
										$send_purchase_notifications_to_buyer	 = isset( $settings[ 'sms_options' ][ 'send_purchase_notifications_to_buyer' ] ) && $settings[ 'sms_options' ][ 'send_purchase_notifications_to_buyer' ] == '1' ? 1 : 0;
										?>
										<input type="radio" name="tc[sms_options][send_purchase_notifications_to_buyer]" value="1" <?php checked( $send_purchase_notifications_to_buyer, '1', true ); ?>><?php _e( 'Yes', 'tw' ) ?></label>
									<label>
										<input type="radio" name="tc[sms_options][send_purchase_notifications_to_buyer]" value="0" <?php checked( $send_purchase_notifications_to_buyer, '0', true ); ?>><?php _e( 'No', 'tw' ) ?>	</label>
									<span class="description"><?php _e( 'A text message will be sent to a buyer after successful purchase.', '' ); ?></span>
								</td>
							</tr>

							<tr valign="top">
								<th scope="row"><label><?php _e( 'Message Content', 'tw' ); ?></label></th>
								<td>
									<input type="text" name='tc[sms_options][buyer_sms_content]' value='<?php echo isset( $settings[ 'sms_options' ][ 'buyer_sms_content' ] ) ? esc_attr( $settings[ 'sms_options' ][ 'buyer_sms_content' ] ) : ''; ?>' />
									<span class="description"><?php _e( "You can use following placeholders (ORDER_ID, ORDER_TOTAL, ORDER_URL)", 'tw' ); ?>.</span>
                                                                        <span class="description"><?php _e( "You can also let know your customers about <a href='https://support.twilio.com/hc/en-us/articles/223134027-Twilio-support-for-opt-out-keywords-SMS-STOP-filtering-'>Twilio's default responses</a> so they can control the messages they receive from you.", 'tw' ); ?></span>
								</td>
							</tr>
                                                        
                                                        <?php do_action( 'tc_after_buyer_sms_notifications' ); ?>

						</tbody>
					</table>
				</div>
			</div>

			<div id="store_settings" class="postbox">
				<h3 class="hndle"><span><?php _e( 'Ticket Owner SMS Notification', 'tw' ); ?></span></h3>
				<div class="inside">
					<table class="form-table">

						<tbody>

							<tr valign="top">
								<th scope="row"><label><?php _e( 'Ticket Owner Purchase Notification', 'tw' ); ?></label></th>
								<td>
									<label>
										<?php
										$send_purchase_notifications_to_owner	 = isset( $settings[ 'sms_options' ][ 'send_purchase_notifications_to_owner' ] ) && $settings[ 'sms_options' ][ 'send_purchase_notifications_to_owner' ] == '1' ? 1 : 0;
										?>
										<input type="radio" name="tc[sms_options][send_purchase_notifications_to_owner]" value="1" <?php checked( $send_purchase_notifications_to_owner, '1', true ); ?>><?php _e( 'Yes', 'tw' ) ?></label>
									<label>
										<input type="radio" name="tc[sms_options][send_purchase_notifications_to_owner]" value="0" <?php checked( $send_purchase_notifications_to_owner, '0', true ); ?>><?php _e( 'No', 'tw' ) ?>	</label>
									<span class="description"><?php _e( 'A text message will be sent to ticket owner(s) after successful purchase.', '' ); ?></span>
								</td>
							</tr>

							<tr valign="top">
								<th scope="row"><label><?php _e( 'Message Content', 'tw' ); ?></label></th>
								<td>
									<input type="text" name='tc[sms_options][owner_sms_content]' value='<?php echo isset( $settings[ 'sms_options' ][ 'owner_sms_content' ] ) ? esc_attr( $settings[ 'sms_options' ][ 'owner_sms_content' ] ) : ''; ?>' />
									<span class="description"><?php _e( "You can use following placeholders (ORDER_ID, ORDER_TOTAL, ORDER_URL, TICKET_URL)", 'tw' ); ?>.</span>
                                                                        <span class="description"><?php _e( "You can also let know your customers about <a href='https://support.twilio.com/hc/en-us/articles/223134027-Twilio-support-for-opt-out-keywords-SMS-STOP-filtering-'>Twilio's default responses</a> so they can control the messages they receive from you.", 'tw' ); ?></span>
								</td>
							</tr>
                                                        
                                                        <?php do_action( 'tc_after_owner_sms_notifications' ); ?>

						</tbody>
					</table>
				</div>
			</div>
              

			<!--SEND TEST MESSAGE-->
			<!--SEND BY EVENT / TICKET TYPE / ANY -->

			<p class="submit">
				<input type="submit" name="save_tc_sms_options" id="save_tc_sms_options" class="button button-primary" value="<?php _e( 'Save SMS Settings', 'tw' ); ?>">
			</p>
		</form>
	</div>
</div>