<?php

/**
 * Customer.io actions
 *
 * @since       1.0.0
 */
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Track new users
 */
function tc_customerio_connect_register_user( $order_id, $status, $cart_contents, $cart_info, $payment_info ) {
	// Bail if API isn't setup
	global $TC_Customerio_Connect;
        $tc_customerio_settings = get_option('tc_customerio_settings');
                    
	if ( !$TC_Customerio_Connect->api ) {
		return;
	}

	global $tc;

	$order_name	 = $order_id;
	$order		 = tc_get_order_id_by_name( $order_id );
	$order_id	 = $order->ID;
	$order		 = new TC_Order( $order_id );

	$total = $payment_info[ 'total' ];

	// Setup the request body
	$cart_items	 = isset( $payment_meta[ 'cart_details' ] ) ? maybe_unserialize( $payment_meta[ 'cart_details' ] ) : false;
	$user_name	 = false;


	$buyer_full_name = isset( $order->details->tc_cart_info[ 'buyer_data' ][ 'first_name_post_meta' ] ) ? ($order->details->tc_cart_info[ 'buyer_data' ][ 'first_name_post_meta' ] . ' ' . $order->details->tc_cart_info[ 'buyer_data' ][ 'last_name_post_meta' ]) : '';
	$buyer_email	 = isset( $order->details->tc_cart_info[ 'buyer_data' ][ 'email_post_meta' ] ) ? $order->details->tc_cart_info[ 'buyer_data' ][ 'email_post_meta' ] : '';

	$user_name = $buyer_full_name;

	$body = array(
		'email'		 => $buyer_email,
		'created_at' => strtotime( $order->details->post_date )
	);

	if ( $user_name ) {
		$body[ 'name' ] = $user_name;
	}

	$user_id = $order->details->post_author;

	if ( user_can( $user_id, 'manage_options' ) ) {
		$user_id = sanitize_key( $buyer_email );
	}

	$response = $TC_Customerio_Connect->api->call( $user_id, apply_filters( 'tc_customerio_body_user_id', $body, $user_id, $order_id ) );

	// Track the purchases
	if ( $cart_contents ) {

		$discounts		 = new TC_Discounts();
		$discount_total	 = $discounts->get_discount_total_by_order( $order_id );

		if ( $discount_total > 0 ) {
			$discount_total = $TC_Customerio_Connect->format( $discount_total );
		} else {
			$discount_total = 0;
		}

		$payment_method = $payment_info[ 'gateway_public_name' ];

		$body = array(
			'name'	 => 'purchased',
			'data'	 => array(
				'order_id'		 => $order_name,
				'order_status'	 => $status,
				'total'			 => (float) $TC_Customerio_Connect->format( $payment_info[ 'total' ] ),
				'subtotal'		 => (float) $TC_Customerio_Connect->format( $payment_info[ 'subtotal' ] ),
				'tax_total'		 => (float) $TC_Customerio_Connect->format( $payment_info[ 'tax_total' ] ),
				'fees_total'	 => (float) $TC_Customerio_Connect->format( $payment_info[ 'fees_total' ] ),
				'discount_total' => (float) $TC_Customerio_Connect->format( $discount_total ),
				'payment_name'	 => $payment_method,
			)
		);

		foreach ( $cart_contents as $id => $qty ) {

			$ticket = new TC_Ticket( $id );

			$price = $cart_item[ 'price' ];

			$body[ 'data' ][ 'items' ][ $id ] = array(
				'price'			 => (float) $ticket->details->price_per_ticket,
				'quantity'		 => (int) $qty,
				'product_name'	 => $ticket->details->post_title,
				'product_id'	 => (int) $id,
			);
		}

		$response = $TC_Customerio_Connect->api->call( $user_id, apply_filters( 'tc_customerio_body_events', $body, $user_id, $order_id ), 'POST', 'events' );
	}
}

add_action( 'tc_order_created', 'tc_customerio_connect_register_user', 100, 5 );
