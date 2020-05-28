<?php
$discounts	 = new TC_Discounts();
$fields		 = $discounts->get_discount_fields( true );
$columns	 = $discounts->get_columns();

function tc_add_bulk_discounts() {
	global $user_id, $post;

	if ( isset( $_POST[ 'add_new_discounts' ] ) ) {
		set_time_limit( 0 );//we have to increase since it might take some time

		$discount_codes_raw		 = trim( $_POST[ 'post_titles_post_title' ] );
		$discount_codes_array	 = explode( "\n", $discount_codes_raw );
		$discount_codes			 = array_filter( $discount_codes_array, 'trim' ); // remove any extra \r characters left behind

		foreach ( $discount_codes as $discount_code ) {
			$title = $discount_code;

			$metas = array();

			foreach ( $_POST as $field_name => $field_value ) {
				if ( preg_match( '/_post_excerpt/', $field_name ) ) {
					$excerpt = $field_value;
				}

				if ( preg_match( '/_post_content/', $field_name ) ) {
					$content = $field_value;
				}

				if ( preg_match( '/_post_meta/', $field_name ) ) {
					$metas[ str_replace( '_post_meta', '', $field_name ) ] = $field_value;
				}

				do_action( 'tc_after_discount_post_field_type_check' );
			}

			$metas = apply_filters( 'discount_code_metas', $metas );

			$arg = array(
				'post_author'	 => $user_id,
				'post_excerpt'	 => (isset( $excerpt ) ? $excerpt : ''),
				'post_content'	 => (isset( $content ) ? $content : ''),
				'post_status'	 => 'publish',
				'post_title'	 => (isset( $title ) ? $title : ''),
				'post_type'		 => 'tc_discounts',
			);

			if ( isset( $_POST[ 'post_id' ] ) ) {
				$arg[ 'ID' ] = $_POST[ 'post_id' ]; //for edit
			}

			$post_id = @wp_insert_post( $arg, true );

			//Update post meta
			if ( $post_id !== 0 ) {
				if ( isset( $metas ) ) {
					foreach ( $metas as $key => $value ) {
                                            if($key == 'discount_availability'){
                                                $value = implode(',', $value );
                                            }
						update_post_meta( $post_id, $key, $value );
					}
				}
			}
		}
	}
}

if ( isset( $_POST[ 'add_new_discounts' ] ) ) {
	if ( check_admin_referer( 'save_discounts' ) ) {
		if ( current_user_can( 'manage_options' ) || current_user_can( 'add_discount_cap' ) ) {
			tc_add_bulk_discounts();
			$message = __( 'Discount Codes data has been saved successfully.', 'tc-bdc' );
		} else {
			$message = __( 'You do not have required permissions for this action.', 'tc-bdc' );
		}
	}
}
?>
<div class="wrap tc_wrap">
    <div id="poststuff" class="metabox-holder tc-settings">

	<?php
	if ( isset( $message ) ) {
		?>
		<div id="message" class="updated fade"><p><?php echo esc_attr( $message ); ?></p></div>
		<?php
	}
	?>

	<form action="" method="post" enctype = "multipart/form-data">
            <div id="store_settings" class="postbox">
                <h3><?php echo $discounts->form_title; ?></h3>
                <div class="inside">
		<?php wp_nonce_field( 'save_discounts' ); ?>
		<table class="discount-table">
			<tbody>
				<?php foreach ( $fields as $field ) { ?>
					<?php if ( $discounts->is_valid_discount_field_type( $field[ 'field_type' ] ) ) { ?>    
						<tr valign="top">

							<th scope="row"><label for="<?php echo $field[ 'field_name' ]; ?>"><?php echo $field[ 'field_title' ]; ?></label></th>

							<td>
								<?php do_action( 'tc_before_discounts_field_type_check' ); ?>
								<?php
								if ( $field[ 'field_type' ] == 'function' ) {
									eval( $field[ 'function' ] . '("' . $field[ 'field_name' ] . '"' . (isset( $post_id ) ? ',' . $post_id : '') . ');' );
									?>
									<span class="description"><?php echo $field[ 'field_description' ]; ?></span>
								<?php } ?>
								<?php if ( $field[ 'field_type' ] == 'text' ) { ?>
									<input type="text" <?php
									if ( isset( $field[ 'placeholder' ] ) ) {
										echo 'placeholder="' . esc_attr( $field[ 'placeholder' ] ) . '"';
									}
									?> class="regular-<?php echo $field[ 'field_type' ]; ?>" value="<?php
										   if ( isset( $discount ) ) {
											   if ( $field[ 'post_field_type' ] == 'post_meta' ) {
												   echo esc_attr( isset( $discount->details->{$field[ 'field_name' ]} ) ? $discount->details->{$field[ 'field_name' ]} : ''  );
											   } else {
												   echo esc_attr( $discount->details->{$field[ 'post_field_type' ]} );
											   }
										   }
										   ?>" id="<?php echo $field[ 'field_name' ]; ?>" name="<?php echo $field[ 'field_name' ] . '_' . $field[ 'post_field_type' ]; ?>">
									<span class="description"><?php echo $field[ 'field_description' ]; ?></span>
								<?php } ?>
								<?php if ( $field[ 'field_type' ] == 'textarea' ) { ?>
									<textarea class="regular-<?php echo $field[ 'field_type' ]; ?>" id="<?php echo $field[ 'field_name' ]; ?>" name="<?php echo $field[ 'field_name' ] . '_' . $field[ 'post_field_type' ]; ?>"><?php
										if ( isset( $discount ) ) {
											if ( $field[ 'post_field_type' ] == 'post_meta' ) {
												echo esc_textarea( isset( $discount->details->{$field[ 'field_name' ]} ) ? $discount->details->{$field[ 'field_name' ]} : ''  );
											} else {
												echo esc_textarea( $discount->details->{$field[ 'post_field_type' ]} );
											}
										}
										?></textarea>
									<br /><?php echo $field[ 'field_description' ]; ?>
								<?php } ?>
								<?php
								if ( $field[ 'field_type' ] == 'image' ) {
									?>
									<div class="file_url_holder">
										<label>
											<input class="file_url" type="text" size="36" name="<?php echo $field[ 'field_name' ] . '_file_url_' . $field[ 'post_field_type' ]; ?>" value="<?php
											if ( isset( $discount ) ) {
												echo esc_attr( isset( $discount->details->{$field[ 'field_name' ] . '_file_url'} ) ? $discount->details->{$field[ 'field_name' ] . '_file_url'} : ''  );
											}
											?>" />
											<input class="file_url_button button-secondary" type="button" value="<?php _e( 'Browse', 'tc-bdc' ); ?>" />
											<?php echo $field[ 'field_description' ]; ?>
										</label>
									</div>
								<?php } ?>
								<?php do_action( 'tc_after_discounts_field_type_check' ); ?>
							</td>
						</tr>
						<?php
					}
				}
				?>
			</tbody>
		</table>
                </div>
            </div>
            		<?php submit_button( __( 'Add Discount Codes', 'tc-bdc' ), 'primary', 'add_new_discounts', true ); ?>

    </form>
    </div>
</div><!-- .wrap -->