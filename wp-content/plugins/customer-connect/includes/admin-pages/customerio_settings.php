<?php
if ( isset( $_POST[ 'tc_customerio' ] ) ) {
	if ( wp_verify_nonce( $_POST[ 'save_customerio_settings_nonce' ], 'save_customerio_settings' ) ) {
		update_option( 'tc_customerio_settings', $_POST[ 'tc_customerio' ] );
	}
}

$tc_customerio_settings = get_option( 'tc_customerio_settings' );
?>
<div class="wrap tc_wrap">
    
        
        <div id="poststuff" class="metabox-holder tc-settings">
            <form action="" method="post" enctype="multipart/form-data">

            <div id="store_settings" class="postbox">
                <h3><span><?php _e( 'Integrate Customer.io', 'cc' ); ?></span></h3>

                <div class="inside">
    
                        <table class="form-table">
                            <tbody>
                                <tr>
                                    <th scope="row"><label for="site_id"><?php _e( 'Site ID', 'cc' ) ?></label></th>
                                    <td><input name="tc_customerio[site_id]" type="text" id="site_id" value="<?php echo isset( $tc_customerio_settings[ 'site_id' ] ) ? $tc_customerio_settings[ 'site_id' ] : ''; ?>" class="regular-text">
                                        <p class="description"><?php _e( 'Site ID can be found on the integrations page of the Customer.io dashboard.', 'cc' ) ?></p>
                                    </td>
                                </tr>

                                <tr>
                                    <th scope="row"><label for="api_key"><?php _e( 'API Key', 'cc' ) ?></label></th>
                                    <td><input name="tc_customerio[api_key]" type="text" id="api_key" value="<?php echo isset( $tc_customerio_settings[ 'api_key' ] ) ? $tc_customerio_settings[ 'api_key' ] : ''; ?>" class="regular-text">
                                        <p class="description"><?php _e( 'API Key can be found on the integrations page of the Customer.io dashboard.', 'cc' ) ?></p>
                                    </td>
                                </tr>
                                
                          

                            </tbody>
                        </table>
                    
</div>
                
                            </div>
            
                        <?php wp_nonce_field( 'save_customerio_settings', 'save_customerio_settings_nonce' ); ?>
                                <?php submit_button(); ?>
                    </form>
            
        </div><!-- #poststuff -->
</div><!-- .wrap -->