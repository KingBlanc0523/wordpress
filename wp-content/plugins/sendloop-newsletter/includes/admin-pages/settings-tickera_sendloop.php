<?php

if ( wp_verify_nonce( $_POST[ 'save_sendloop_settings_nonce' ], 'save_sendloop_settings' ) ) {
	update_option( 'tc_sendloop_settings', $_POST[ 'tc_sendloop' ] );
        
            include(ABSPATH . 'wp-content/plugins/sendloop-newsletter/includes/sendloopapi3.php');
            
            $tc_sendloop_settings = get_option( 'tc_sendloop_settings' );
            
            $tc_api_key = $tc_sendloop_settings[ 'api_key' ];
            $tc_list_id = $tc_sendloop_settings[ 'list_id' ];
            $tc_subdomain = $tc_sendloop_settings[ 'subdomain' ];

            $API = new SendloopAPI3($tc_api_key, $tc_subdomain,'php');

            $API->run('List.Update', array('ListID' => $tc_list_id ));

}

$tc_sendloop_settings = get_option( 'tc_sendloop_settings' );
?>
<div class="wrap tc_wrap" style="opacity: 1;">
    <div id="poststuff" class="metabox-holder tc-settings">
        <form action="" method="post" name="save_sendloop_options" enctype="multipart/form-data">
            <div id="store_settings" class="postbox">
                <h3>
                    <span>
                        <?php _e('Sendloop Options','sl'); ?>
                    </span>
                </h3>
                    <div class="inside">
                    <table class="form-table">
                        <tbody>

                            <tr>
                                <th scope="row"><label for="list_id"><?php _e( 'Disable Sendloop', 'sl' ) ?></label></th>
                                <td><input name="tc_sendloop[disable_sendloop]" type="checkbox" id="disable_sendloop" value="1" <?php if ( isset( $tc_sendloop_settings[ 'disable_sendloop' ] ) ) {
                    echo "checked"; } ?>>
                                    <p class="description"><?php _e( 'Check to disable sendloop submission', 'sl' ); ?></p></td>
                            </tr>

                            <tr>
                                <th scope="row"><label for="subdomain"><?php _e( 'Sendloop Subdomain', 'sl' ) ?></label></th>
                                <td><input name="tc_sendloop[subdomain]" type="text" id="subdomain" value="<?php echo isset( $tc_sendloop_settings[ 'subdomain' ] ) ? $tc_sendloop_settings[ 'subdomain' ] : ''; ?>" class="regular-text">
                                    <p class="description"><?php _e( 'Set your subdomain - <strong>yoursubdomain</strong>.sendloop.com', 'sl' ); ?></p>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row"><label for="api_key"><?php _e( 'API Key', 'sl' ) ?></label></th>
                                <td><input name="tc_sendloop[api_key]" type="text" id="api_key" value="<?php echo isset( $tc_sendloop_settings[ 'api_key' ] ) ? $tc_sendloop_settings[ 'api_key' ] : ''; ?>" class="regular-text"><a href="#" class="tc-test-sendloop-submission"><?php _e('Test newsletter submission','sl'); ?></a><div class="tc-show-message"></div>
                                    <p class="description"><?php _e( 'Set the Sendloop API key. In order to generate an API key, simply login to your Sendloop account, click "Settings" on the top right menu. Then click "API Settings" tab. On top right, you will see "Create new API key" button.', 'sl' ); ?></p>
                                </td>
                            </tr>
                            

                            <tr>
                                <th scope="row"><label for="list_id"><?php _e( 'List ID', 'sl' ) ?></label></th>
                                <td><input name="tc_sendloop[list_id]" type="text" id="list_id" value="<?php echo isset( $tc_sendloop_settings[ 'list_id' ] ) ? $tc_sendloop_settings[ 'list_id' ] : ''; ?>" class="regular-text">
                                    <p class="description"><?php _e( 'Set the Sendloop list ID', 'sl' ); ?></p></td>
                            </tr>

                            <tr>
                                <th scope="row"><label for="list_id"><?php _e( 'Enable Confirmation Checkbox', 'sl' ) ?></label></th>
                                <td><input name="tc_sendloop[enable_confirmation_box]" type="checkbox" id="disable_sendloop" value="1" <?php if ( isset( $tc_sendloop_settings[ 'enable_confirmation_box' ] ) ) {
                    echo "checked"; } ?>>
                                    <p class="description"><?php _e( 'If enabled, customers will see checkbox to confirm subscription, if not customers will be automatically subscribed.', 'sl' ); ?></p></td>
                            </tr>

                            
                            <?php if ( class_exists( 'TC_WooCommerce_Bridge' ) ) { ?>
                                <tr>
                                    <th scope="row"><label for="users_buying_tickets"><?php _e( 'Subscribe ticket buying customers only', 'sl' ) ?></label></th>
                                    <td><input name="tc_sendloop[users_buying_tickets]" type="checkbox" id="users_buying_tickets" value="1" <?php if ( isset( $tc_sendloop_settings[ 'users_buying_tickets' ] ) ) { echo "checked"; } ?>>
                                        <p class="description"><?php _e( 'If checked only the customers who bought tickets will be subscribed.', 'sl' ); ?></p></td>
                                </tr>
                            <?php } ?>
                        
                        </tbody>
                    </table>
                    </div><!-- inside -->
                </div><!-- store-settings -->
            <?php wp_nonce_field( 'save_sendloop_settings', 'save_sendloop_settings_nonce' ); ?>
            <?php submit_button('','primary','save_sendloop_options'); ?>
        </form>
    </div><!-- poststuff -->
</div><!-- wrap -->