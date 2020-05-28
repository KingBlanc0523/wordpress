<?php

/*
 Plugin Name: Twilio SMS Notifications for Tickera
 Plugin URI: http://tickera.com/
 Description: Send SMS notifications to yourself, your buyers and ticket owners automatically via Twilio.com service
 Author: Tickera.com
 Author URI: http://tickera.com/
 Version: 1.2.2
 Text Domain: tw
 Domain Path: /languages/
 Copyright 2019 Tickera (http://tickera.com/)
*/
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
// Exit if accessed directly
if ( !class_exists( 'TC_Twilio_SMS_Notifications' ) ) {
    class TC_Twilio_SMS_Notifications
    {
        var  $version = '1.2.2' ;
        var  $title = 'Tickera Twilio SMS Notifications' ;
        var  $name = 'tw' ;
        var  $dir_name = 'twilio-sms-notifications' ;
        var  $location = 'plugins' ;
        var  $plugin_dir = '' ;
        var  $plugin_url = '' ;
        function __construct()
        {
            
            if ( class_exists( 'TC' ) ) {
                add_filter( 'tc_settings_new_menus', array( &$this, 'tc_settings_new_menus_additional' ) );
                add_filter( 'tc_owner_info_fields', array( &$this, 'custom_owner_phone_field' ) );
                add_filter( 'tc_owner_info_orders_table_fields', array( &$this, 'custom_owner_phone_table_field' ) );
                add_filter( 'tc_buyer_info_fields', array( &$this, 'custom_buyer_phone_field' ) );
                add_filter( 'tc_order_fields', array( &$this, 'custom_buyer_phone_table_field' ) );
                add_action( 'tc_settings_menu_tickera_twilio_sms_notifications', array( &$this, 'tc_settings_menu_tickera_twilio_sms_data_show_page' ) );
                add_action(
                    'tc_order_created',
                    array( &$this, 'send_twilio_notification' ),
                    10,
                    5
                );
                add_action(
                    'tc_order_paid_change',
                    array( &$this, 'send_twilio_notification' ),
                    10,
                    5
                );
                add_action(
                    'tc_order_updated_status_to_paid',
                    array( &$this, 'send_twilio_notification' ),
                    10,
                    5
                );
                add_action(
                    'woocommerce_order_status_changed',
                    array( &$this, 'send_twilio_notification_woo' ),
                    10,
                    3
                );
                add_action( 'init', array( &$this, 'localization' ), 10 );
                //WooCommerce
                //add_action('woocommerce_api_create_order', array(&$this, 'send_twilio_notification_woo' ), 10, 2);
                //add_action('woocommerce_new_order', array(&$this, 'send_twilio_notification_woo'), 10, 1);
                add_filter(
                    'tc_csv_admin_fields',
                    array( &$this, 'tc_twilio_modify_tc_csv_admin_fields' ),
                    10,
                    2
                );
                add_filter(
                    'tc_csv_array',
                    array( &$this, 'tc_twilio_csv_array_additional' ),
                    10,
                    4
                );
            }
        
        }
        
        //Plugin localization function
        function localization()
        {
            // Load up the localization file if we're using WordPress in a different language
            // Place it in this plugin's "languages" folder and name it "tc-[value in wp-config].mo"
            
            if ( $this->location == 'mu-plugins' ) {
                load_muplugin_textdomain( 'tw', 'languages/' );
            } else {
                
                if ( $this->location == 'subfolder-plugins' ) {
                    load_plugin_textdomain( 'tw', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
                } else {
                    
                    if ( $this->location == 'plugins' ) {
                        load_plugin_textdomain( 'tw', false, 'languages/' );
                    } else {
                    }
                
                }
            
            }
            
            $temp_locales = explode( '_', get_locale() );
            $this->language = ( $temp_locales[0] ? $temp_locales[0] : 'en' );
        }
        
        /*
         * Custom Owner Phone Field
         */
        function custom_owner_phone_field( $fields )
        {
            $settings = get_option( 'tc_settings' );
            $collect_mobile_owners = ( isset( $settings['sms_options']['collect_mobile_owners'] ) && $settings['sms_options']['collect_mobile_owners'] == '1' ? 1 : 0 );
            $field_title = ( isset( $settings['sms_options']['owner_mobile_phone_field_title'] ) ? $settings['sms_options']['owner_mobile_phone_field_title'] : __( 'Mobile Phone', 'tw' ) );
            $field_description = ( isset( $settings['sms_options']['owner_mobile_phone_field_description'] ) ? $settings['sms_options']['owner_mobile_phone_field_description'] : __( 'Mobile phone number with leading "+" and the country code. For example: +12016453123', 'tw' ) );
            if ( $collect_mobile_owners == 1 ) {
                $fields[] = array(
                    'field_name'        => 'owner_mobile_phone',
                    'field_title'       => $field_title,
                    'field_type'        => 'text',
                    'post_field_type'   => 'post_meta',
                    'required'          => false,
                    'field_description' => $field_description,
                );
            }
            apply_filters( 'TC_Twilio_SMS_Notifications_custom_owner_fields', $fields );
            return $fields;
        }
        
        function custom_owner_phone_table_field( $fields )
        {
            $settings = get_option( 'tc_settings' );
            $collect_mobile_owners = ( isset( $settings['sms_options']['collect_mobile_owners'] ) && $settings['sms_options']['collect_mobile_owners'] == '1' ? 1 : 0 );
            $field_title = ( isset( $settings['sms_options']['owner_mobile_phone_field_title'] ) ? $settings['sms_options']['owner_mobile_phone_field_title'] : __( 'Mobile Phone', 'tw' ) );
            $field_description = ( isset( $settings['sms_options']['owner_mobile_phone_field_description'] ) ? $settings['sms_options']['owner_mobile_phone_field_description'] : __( 'Mobile phone number with leading "+" and the country code. Example: For example: +12016453123', 'tw' ) );
            if ( $collect_mobile_owners == 1 ) {
                $fields[] = array(
                    'id'                => 'owner_mobile_phone',
                    'field_name'        => 'owner_mobile_phone',
                    'field_title'       => $field_title,
                    'field_type'        => 'text',
                    'post_field_type'   => 'post_meta',
                    'required'          => false,
                    'field_description' => '',
                );
            }
            return $fields;
        }
        
        /*
         * Buyer Custom Phone Field
         */
        function custom_buyer_phone_field( $fields )
        {
            $settings = get_option( 'tc_settings' );
            $collect_mobile_buyers = ( isset( $settings['sms_options']['collect_mobile_buyers'] ) && $settings['sms_options']['collect_mobile_buyers'] == '1' ? 1 : 0 );
            $field_title = ( isset( $settings['sms_options']['buyer_mobile_phone_field_title'] ) ? $settings['sms_options']['buyer_mobile_phone_field_title'] : __( 'Mobile Phone', 'tw' ) );
            $field_description = ( isset( $settings['sms_options']['buyer_mobile_phone_field_description'] ) ? $settings['sms_options']['buyer_mobile_phone_field_description'] : __( 'Mobile phone number with leading "+" and the country code. For example: +12016453123', 'tw' ) );
            $bridge_active = is_plugin_active( 'bridge-for-woocommerce/bridge-for-woocommerce.php' );
            if ( $collect_mobile_buyers == 1 && $bridge_active !== true ) {
                $fields[] = array(
                    'field_name'        => 'buyer_mobile_phone',
                    'field_title'       => $field_title,
                    'field_type'        => 'text',
                    'post_field_type'   => 'post_meta',
                    'required'          => false,
                    'field_description' => $field_description,
                );
            }
            return $fields;
        }
        
        function custom_buyer_phone_table_field( $fields )
        {
            $settings = get_option( 'tc_settings' );
            $collect_mobile_buyers = ( isset( $settings['sms_options']['collect_mobile_buyers'] ) && $settings['sms_options']['collect_mobile_buyers'] == '1' ? 1 : 0 );
            $field_title = ( isset( $settings['sms_options']['buyer_mobile_phone_field_title'] ) ? $settings['sms_options']['buyer_mobile_phone_field_title'] : __( 'Mobile Phone', 'tw' ) );
            $field_description = ( isset( $settings['sms_options']['buyer_mobile_phone_field_description'] ) ? $settings['sms_options']['buyer_mobile_phone_field_description'] : __( 'Mobile phone number with leading "+" and the country code. For example: +12016453123', 'tw' ) );
            if ( $collect_mobile_buyers == 1 ) {
                $fields[] = array(
                    'id'                => 'buyer_mobile_phone',
                    'field_name'        => 'tc_cart_info',
                    'field_title'       => $field_title,
                    'field_type'        => 'function',
                    'function'          => 'TC_Twilio_SMS_Notifications_get_buyer_phone_number',
                    'field_description' => '',
                    'table_visibility'  => false,
                    'post_field_type'   => 'post_meta',
                );
            }
            return $fields;
        }
        
        function tc_settings_new_menus_additional( $settings_tabs )
        {
            $settings_tabs['tickera_twilio_sms_notifications'] = __( 'Twilio SMS', 'tw' );
            return $settings_tabs;
        }
        
        function tc_settings_menu_tickera_twilio_sms_data_show_page()
        {
            require_once $this->plugin_dir . 'includes/admin-pages/settings-tickera_sms_notifications.php';
        }
        
        function send_twilio_notification_woo( $order, $old_status, $new_status )
        {
            $settings = get_option( 'tc_settings' );
            
            if ( $new_status == "completed" ) {
                global  $tc, $woocommerce ;
                $order = wc_get_order( $order );
                $order_id = $order->get_id();
                $order_items = $order->get_items();
                $order_data = $order->get_data();
                $woo_order_has_physical_tickets = false;
                foreach ( $order_items as $item ) {
                    $product_id = $item->get_product_id();
                    $is_ticket_meta = get_post_meta( $product_id, '_tc_is_ticket', true );
                    $is_ticket = ( $is_ticket_meta == 'yes' ? true : false );
                    
                    if ( $is_ticket ) {
                        $tc_order_has_ticket = true;
                    } else {
                        $tc_order_has_ticket = false;
                    }
                
                }
                
                if ( $tc_order_has_ticket == true ) {
                    $send_purchase_notifications_to_buyer = ( isset( $settings['sms_options']['send_purchase_notifications_to_buyer'] ) && $settings['sms_options']['send_purchase_notifications_to_buyer'] == '1' ? 1 : 0 );
                    //twilio tokens
                    $account_sid = ( isset( $settings['sms_options']['account_sid'] ) ? $settings['sms_options']['account_sid'] : '' );
                    $auth_token = ( isset( $settings['sms_options']['auth_token'] ) ? $settings['sms_options']['auth_token'] : '' );
                    do_action( 'tc_before_sms_send' );
                    //get buyer and twilio phone
                    $from_phone = ( isset( $settings['sms_options']['from_phone'] ) ? $settings['sms_options']['from_phone'] : '' );
                    $from_phone = str_replace( ' ', '', $from_phone );
                    $from_phone = str_replace( '-', '', $from_phone );
                    $buyer_phone = get_post_meta( $order_id, '_billing_phone', true );
                    $buyer_phone = apply_filters( 'tc_buyer_phone_number', $buyer_phone );
                    try {
                        require_once $this->plugin_dir . 'includes/lib/twilio-php/Services/Twilio.php';
                        $client = new Services_Twilio( $account_sid, $auth_token );
                    } catch ( Services_Twilio_RestException $e ) {
                        
                        if ( defined( 'TC_DEBUG' ) ) {
                            echo  $e->getMessage() ;
                            die;
                        }
                    
                    }
                    //SEND NOTIFICATIONS TO BUYER
                    
                    if ( $send_purchase_notifications_to_buyer == 1 ) {
                        $buyer_phone = get_post_meta( $order_id, '_billing_phone', true );
                        $buyer_phone = apply_filters( 'tc_buyer_phone_number', $buyer_phone );
                        //message and replacing message with content
                        $message = ( isset( $settings['sms_options']['buyer_sms_content'] ) ? $settings['sms_options']['buyer_sms_content'] : '' );
                        $tags = array( 'ORDER_ID', 'ORDER_TOTAL', 'ORDER_URL' );
                        $tags_replaces = array( $order_id, $order_data['total'], $order->get_view_order_url() );
                        $message = str_replace( $tags, $tags_replaces, $message );
                        //send message
                        try {
                            $message = $client->account->messages->sendMessage( $from_phone, $buyer_phone, $message );
                        } catch ( Services_Twilio_RestException $e ) {
                            
                            if ( defined( 'TC_DEBUG' ) ) {
                                echo  $e->getMessage() ;
                                //die;
                            }
                        
                        }
                    }
                    
                    $send_purchase_notifications_to_owner = ( isset( $settings['sms_options']['send_purchase_notifications_to_owner'] ) && $settings['sms_options']['send_purchase_notifications_to_owner'] == '1' ? 1 : 0 );
                    
                    if ( $send_purchase_notifications_to_owner == 1 ) {
                        $args = array(
                            'posts_per_page' => -1,
                            'orderby'        => 'post_date',
                            'order'          => 'ASC',
                            'post_type'      => 'tc_tickets_instances',
                            'post_parent'    => $order_id,
                        );
                        $tickets = get_posts( $args );
                        foreach ( $tickets as $ticket ) {
                            $message = ( isset( $settings['sms_options']['owner_sms_content'] ) ? $settings['sms_options']['owner_sms_content'] : '' );
                            $ticket_instance = new TC_Ticket_Instance( (int) $ticket->ID );
                            $ticket_type = new TC_Ticket( $ticket_instance->details->ticket_type_id );
                            
                            if ( get_post_type( $ticket_type->id ) == 'product_variation' ) {
                                $is_ticket_meta = get_post_meta( wp_get_post_parent_id( $ticket_type->id ), '_tc_is_ticket', true );
                            } else {
                                $is_ticket_meta = get_post_meta( $ticket_type->id, '_tc_is_ticket', true );
                            }
                            
                            $is_ticket = ( $is_ticket_meta == 'yes' ? true : false );
                            
                            if ( $is_ticket ) {
                                $tags = array(
                                    'ORDER_ID',
                                    'ORDER_TOTAL',
                                    'ORDER_URL',
                                    'TICKET_URL'
                                );
                                $tc_ticket_link = tc_get_raw_ticket_download_link(
                                    '',
                                    '',
                                    $ticket->ID,
                                    true
                                );
                                $tc_ticket_link = htmlspecialchars_decode( $tc_ticket_link );
                                $tc_ticket_link = str_replace( "&amp;", "&", $tc_ticket_link );
                                $tags_replaces = array(
                                    $order_id,
                                    $order_data['total'],
                                    $order->get_view_order_url(),
                                    $tc_ticket_link
                                );
                                $message = str_replace( $tags, $tags_replaces, $message );
                                $owner_phone_value = get_post_meta( $ticket->ID, 'owner_mobile_phone', true );
                                $owner_phone_value = apply_filters( 'tc_owner_phone_number', $owner_phone_value );
                                
                                if ( trim( $owner_phone_value ) !== '' ) {
                                    $to = $owner_phone_value;
                                    try {
                                        $sms = $client->account->messages->sendMessage( $from_phone, $to, $message );
                                    } catch ( Services_Twilio_RestException $e ) {
                                        
                                        if ( defined( 'TC_DEBUG' ) ) {
                                            echo  $e->getMessage() ;
                                            die;
                                        }
                                    
                                    }
                                }
                            
                            }
                        
                        }
                        //foreach ( $tickets as $ticket )
                    }
                    
                    //if ( $send_purchase_notifications_to_owner == 1 )
                    //SEND NOTIFICATIONS TO OWNER
                    /* SEND NOTIFICATION TO ADMIN */
                    $send_purchase_notifications_to_admin = ( isset( $settings['sms_options']['send_purchase_notifications_to_admin'] ) && $settings['sms_options']['send_purchase_notifications_to_admin'] == '1' ? 1 : 0 );
                    
                    if ( $send_purchase_notifications_to_admin == 1 ) {
                        $admin_phone = ( isset( $settings['sms_options']['admin_phone'] ) ? $settings['sms_options']['admin_phone'] : '' );
                        $to = $admin_phone;
                        $message = ( isset( $settings['sms_options']['admin_sms_content'] ) ? $settings['sms_options']['admin_sms_content'] : '' );
                        $tags = array( 'ORDER_ID', 'ORDER_TOTAL', 'ORDER_ADMIN_URL' );
                        $tags_replaces = array( $order_id, $order_data['total'], admin_url( 'post.php?post=' . absint( $order_id ) . '&action=edit' ) );
                        $message = str_replace( $tags, $tags_replaces, $message );
                        try {
                            $sms = $client->account->messages->sendMessage( $from_phone, $to, $message );
                        } catch ( Services_Twilio_RestException $e ) {
                            
                            if ( defined( 'TC_DEBUG' ) ) {
                                echo  $e->getMessage() ;
                                die;
                            }
                        
                        }
                    }
                    
                    do_action( 'tc_sms_order_created' );
                }
            
            }
        
        }
        
        //send_twilio_notification_woo
        function send_twilio_notification(
            $order_id,
            $status,
            $cart_contents,
            $cart_info,
            $payment_info
        )
        {
            global  $tc ;
            if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
                return;
            }
            if ( $status !== 'order_paid' || empty($order_id) ) {
                return;
            }
            $settings = get_option( 'tc_settings' );
            
            if ( !is_int( $order_id ) ) {
                $order = tc_get_order_id_by_name( $order_id );
                $order = new TC_Order( $order->ID );
            } else {
                $order = new TC_Order( $order_id );
            }
            
            if ( !isset( $payment_info['total'] ) ) {
                $payment_info = $order->details->tc_payment_info;
            }
            $cart_contents = $order->details->tc_cart_contents;
            $settings = get_option( 'tc_settings' );
            //twilio tokens
            $account_sid = ( isset( $settings['sms_options']['account_sid'] ) ? $settings['sms_options']['account_sid'] : '' );
            $auth_token = ( isset( $settings['sms_options']['auth_token'] ) ? $settings['sms_options']['auth_token'] : '' );
            do_action( 'tc_before_sms_send' );
            //get buyer and twilio phone
            $from_phone = ( isset( $settings['sms_options']['from_phone'] ) ? $settings['sms_options']['from_phone'] : '' );
            $from_phone = str_replace( ' ', '', $from_phone );
            $from_phone = str_replace( '-', '', $from_phone );
            $buyer_phone = ( isset( $cart_info['buyer_data']['buyer_mobile_phone_post_meta'] ) ? $cart_info['buyer_data']['buyer_mobile_phone_post_meta'] : '' );
            try {
                require_once $this->plugin_dir . 'includes/lib/twilio-php/Services/Twilio.php';
                $client = new Services_Twilio( $account_sid, $auth_token );
            } catch ( Services_Twilio_RestException $e ) {
                
                if ( defined( 'TC_DEBUG' ) ) {
                    echo  $e->getMessage() ;
                    die;
                }
            
            }
            //SEND NOTIFICATIONS TO BUYER
            $send_purchase_notifications_to_buyer = ( isset( $settings['sms_options']['send_purchase_notifications_to_buyer'] ) && $settings['sms_options']['send_purchase_notifications_to_buyer'] == '1' ? 1 : 0 );
            
            if ( $send_purchase_notifications_to_buyer == 1 ) {
                $buyer_phone_value = get_post_meta( $order->details->ID, 'tc_cart_info', true );
                $buyer_phone = ( isset( $buyer_phone_value['buyer_data']['buyer_mobile_phone_post_meta'] ) ? $buyer_phone_value['buyer_data']['buyer_mobile_phone_post_meta'] : '' );
                $buyer_phone = apply_filters( 'tc_buyer_phone_number', $buyer_phone );
                //message and replacing message with content
                $message = ( isset( $settings['sms_options']['buyer_sms_content'] ) ? $settings['sms_options']['buyer_sms_content'] : '' );
                $tags = array( 'ORDER_ID', 'ORDER_TOTAL', 'ORDER_URL' );
                $tags_replaces = array( $order->details->post_title, $tc->get_cart_currency_and_format( ( isset( $payment_info['total'] ) ? $payment_info['total'] : 0 ) ), $tc->tc_order_status_url(
                    $order,
                    $order->details->tc_order_date,
                    false,
                    false
                ) );
                $message = str_replace( $tags, $tags_replaces, $message );
                //send message
                try {
                    $message = $client->account->messages->sendMessage( $from_phone, $buyer_phone, $message );
                } catch ( Services_Twilio_RestException $e ) {
                    
                    if ( defined( 'TC_DEBUG' ) ) {
                        echo  $e->getMessage() ;
                        die;
                    }
                
                }
            }
            
            $send_purchase_notifications_to_owner = ( isset( $settings['sms_options']['send_purchase_notifications_to_owner'] ) && $settings['sms_options']['send_purchase_notifications_to_owner'] == '1' ? 1 : 0 );
            
            if ( $send_purchase_notifications_to_owner == 1 ) {
                $args = array(
                    'posts_per_page' => -1,
                    'orderby'        => 'post_date',
                    'order'          => 'ASC',
                    'post_type'      => 'tc_tickets_instances',
                    'post_parent'    => $order->details->ID,
                );
                $tickets = get_posts( $args );
                foreach ( $tickets as $ticket ) {
                    $tc_ticket_link = tc_get_raw_ticket_download_link(
                        '',
                        '',
                        $ticket->ID,
                        true
                    );
                    $tc_ticket_link = htmlspecialchars_decode( $tc_ticket_link );
                    $tc_ticket_link = str_replace( "&amp;", "&", $tc_ticket_link );
                    $message = ( isset( $settings['sms_options']['owner_sms_content'] ) ? $settings['sms_options']['owner_sms_content'] : '' );
                    $tags = array(
                        'ORDER_ID',
                        'ORDER_TOTAL',
                        'ORDER_URL',
                        'TICKET_URL'
                    );
                    $tags_replaces = array(
                        $order->details->post_title,
                        $tc->get_cart_currency_and_format( ( isset( $payment_info['total'] ) ? $payment_info['total'] : 0 ) ),
                        $tc->tc_order_status_url(
                        $order,
                        $order->details->tc_order_date,
                        false,
                        false
                    ),
                        $tc_ticket_link
                    );
                    $message = str_replace( $tags, $tags_replaces, $message );
                    $owner_phone_value = get_post_meta( $ticket->ID, 'owner_mobile_phone', true );
                    $owner_phone_value = apply_filters( 'tc_owner_phone_number', $owner_phone_value );
                    
                    if ( trim( $owner_phone_value ) !== '' ) {
                        $to = $owner_phone_value;
                        try {
                            $sms = $client->account->messages->sendMessage( $from_phone, $to, $message );
                        } catch ( Services_Twilio_RestException $e ) {
                            
                            if ( defined( 'TC_DEBUG' ) ) {
                                echo  $e->getMessage() ;
                                die;
                            }
                        
                        }
                    }
                
                }
                //foreach ( $tickets as $ticket )
            }
            
            //if ( $send_purchase_notifications_to_owner == 1 )
            //SEND NOTIFICATIONS TO ADMIN
            $send_purchase_notifications_to_admin = ( isset( $settings['sms_options']['send_purchase_notifications_to_admin'] ) && $settings['sms_options']['send_purchase_notifications_to_admin'] == '1' ? 1 : 0 );
            
            if ( $send_purchase_notifications_to_admin == 1 ) {
                $admin_phone = ( isset( $settings['sms_options']['admin_phone'] ) ? $settings['sms_options']['admin_phone'] : '' );
                $to = $admin_phone;
                $message = ( isset( $settings['sms_options']['admin_sms_content'] ) ? $settings['sms_options']['admin_sms_content'] : '' );
                $tags = array( 'ORDER_ID', 'ORDER_TOTAL', 'ORDER_ADMIN_URL' );
                $tags_replaces = array( $order->details->post_title, $tc->get_cart_currency_and_format( ( isset( $payment_info['total'] ) ? $payment_info['total'] : 0 ) ), admin_url( 'admin.php?page=tc_orders&action=details&ID=' . $order_id ) );
                $message = str_replace( $tags, $tags_replaces, $message );
                try {
                    $sms = $client->account->messages->sendMessage( $from_phone, $to, $message );
                } catch ( Services_Twilio_RestException $e ) {
                    
                    if ( defined( 'TC_DEBUG' ) ) {
                        echo  $e->getMessage() ;
                        die;
                    }
                
                }
            }
            
            do_action( 'tc_sms_order_created' );
        }
        
        function sms_admin_email_from_email( $email )
        {
            $settings = get_option( 'tc_settings' );
            $email = ( isset( $settings['sms_options']['account_email'] ) ? $settings['sms_options']['account_email'] : '' );
            return $email;
        }
        
        function tc_twilio_modify_tc_csv_admin_fields( $fields )
        {
            $fields['col_owner_phone_number'] = __( 'Owner Phone Number', 'tw' );
            $fields['col_buyer_phone_number'] = __( 'Buyer Phone Number', 'tw' );
            return $fields;
        }
        
        function tc_twilio_csv_array_additional(
            $export_array,
            $order,
            $instance,
            $post
        )
        {
            global  $tc ;
            
            if ( isset( $post['col_owner_phone_number'] ) ) {
                $tc_phone_number = get_post_meta( $instance->details->ID, 'owner_mobile_phone', true );
                if ( empty($tc_phone_number) || !isset( $tc_phone_number ) ) {
                    $tc_phone_number = '';
                }
                $new_export_array = array(
                    __( 'Owner Phone Number', 'tw' ) => $tc_phone_number,
                );
                $export_array = array_merge( $export_array, $new_export_array );
            }
            
            
            if ( isset( $post['col_buyer_phone_number'] ) ) {
                $tc_buyer_phone_number = $order->details->tc_cart_info['buyer_data']['buyer_mobile_phone_post_meta'];
                if ( empty($tc_buyer_phone_number) || !isset( $tc_buyer_phone_number ) ) {
                    $tc_buyer_phone_number = '';
                }
                $new_export_array = array(
                    __( 'Buyer Phone Number', 'tw' ) => $tc_buyer_phone_number,
                );
                $export_array = array_merge( $export_array, $new_export_array );
            }
            
            return $export_array;
        }
    
    }
}
function TC_Twilio_SMS_Notifications_get_buyer_phone_number( $field_name = '', $post_id = '' )
{
    $value = get_post_meta( $post_id, $field_name, true );
    echo  ( isset( $value['buyer_data']['buyer_mobile_phone_post_meta'] ) ? $value['buyer_data']['buyer_mobile_phone_post_meta'] : '-' ) ;
}

if ( !function_exists( 'is_plugin_active_for_network' ) ) {
    require_once ABSPATH . '/wp-admin/includes/plugin.php';
}
$TC_Twilio_SMS_Notifications = new TC_Twilio_SMS_Notifications();
/*
if (is_multisite() && is_plugin_active_for_network(plugin_basename(__FILE__))) {

    function tc_twilio_sms_notifications_load() {
        global $TC_Twilio_SMS_Notifications;
        $TC_Twilio_SMS_Notifications = new TC_Twilio_SMS_Notifications();
    }

    add_action('tets_fs_loaded', 'tc_twilio_sms_notifications_load');
} else {
    $TC_Twilio_SMS_Notifications = new TC_Twilio_SMS_Notifications();
}*/
if ( !function_exists( 'tctsn_fs' ) ) {
    // Create a helper function for easy SDK access.
    function tctsn_fs()
    {
        global  $tctsn_fs ;
        
        if ( !isset( $tctsn_fs ) ) {
            // Activate multisite network integration.
            if ( !defined( 'WP_FS__PRODUCT_3185_MULTISITE' ) ) {
                define( 'WP_FS__PRODUCT_3185_MULTISITE', true );
            }
            // Include Freemius SDK.
            
            if ( file_exists( dirname( dirname( __FILE__ ) ) . '/tickera-event-ticketing-system/freemius/start.php' ) ) {
                // Try to load SDK from parent plugin folder.
                require_once dirname( dirname( __FILE__ ) ) . '/tickera-event-ticketing-system/freemius/start.php';
            } else {
                
                if ( file_exists( dirname( dirname( __FILE__ ) ) . '/tickera/freemius/start.php' ) ) {
                    // Try to load SDK from premium parent plugin folder.
                    require_once dirname( dirname( __FILE__ ) ) . '/tickera/freemius/start.php';
                } else {
                    require_once dirname( __FILE__ ) . '/freemius/start.php';
                }
            
            }
            
            $tctsn_fs = fs_dynamic_init( array(
                'id'               => '3185',
                'slug'             => 'twilio-sms-notifications',
                'premium_slug'     => 'twilio-sms-notifications',
                'type'             => 'plugin',
                'public_key'       => 'pk_bcf16d230c8e8bbcaab5ebf6d65dd',
                'is_premium'       => true,
                'is_premium_only'  => true,
                'has_paid_plans'   => true,
                'is_org_compliant' => false,
                'parent'           => array(
                'id'         => '3102',
                'slug'       => 'tickera-event-ticketing-system',
                'public_key' => 'pk_7a38a2a075ec34d6221fe925bdc65',
                'name'       => 'Tickera',
            ),
                'menu'             => array(
                'first-path' => 'plugins.php',
                'support'    => false,
            ),
                'is_live'          => true,
            ) );
        }
        
        return $tctsn_fs;
    }

}
function tctsn_fs_is_parent_active_and_loaded()
{
    // Check if the parent's init SDK method exists.
    return function_exists( 'tets_fs' );
}

function tctsn_fs_is_parent_active()
{
    $active_plugins = get_option( 'active_plugins', array() );
    
    if ( is_multisite() ) {
        $network_active_plugins = get_site_option( 'active_sitewide_plugins', array() );
        $active_plugins = array_merge( $active_plugins, array_keys( $network_active_plugins ) );
    }
    
    foreach ( $active_plugins as $basename ) {
        if ( 0 === strpos( $basename, 'tickera-event-ticketing-system/' ) || 0 === strpos( $basename, 'tickera/' ) ) {
            return true;
        }
    }
    return false;
}

function tctsn_fs_init()
{
    
    if ( tctsn_fs_is_parent_active_and_loaded() ) {
        // Init Freemius.
        tctsn_fs();
        // Parent is active, add your init code here.
    } else {
        // Parent is inactive, add your error handling here.
    }

}


if ( tctsn_fs_is_parent_active_and_loaded() ) {
    // If parent already included, init add-on.
    tctsn_fs_init();
} else {
    
    if ( tctsn_fs_is_parent_active() ) {
        // Init add-on only after the parent is loaded.
        add_action( 'tets_fs_loaded', 'tctsn_fs_init' );
    } else {
        // Even though the parent is not activated, execute add-on for activation / uninstall hooks.
        tctsn_fs_init();
    }

}
