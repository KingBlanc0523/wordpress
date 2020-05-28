<?php

/*
 Plugin Name: Tickera Check-in Notifications
 Plugin URI: http://tickera.com/
 Description: Send notification e-mail when user has checked in the event
 Author: Tickera.com
 Author URI: http://tickera.com/
 Version: 1.1.5
 Text Domain: chin
 Domain Path: /languages/
 Copyright 2019 Tickera (http://tickera.com/)
*/
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
// Exit if accessed directly

if ( !class_exists( 'TC_Checkin_Notifications' ) ) {
    class TC_Checkin_Notifications
    {
        var  $version = '1.1.5' ;
        var  $title = 'Check-in Notifications' ;
        var  $name = 'chin' ;
        var  $dir_name = 'check-in-notifications' ;
        var  $location = 'plugins' ;
        var  $plugin_dir = '' ;
        var  $plugin_url = '' ;
        function __construct()
        {
            add_action( 'tc_check_in_notification', array( &$this, 'tc_check_in_notification_email' ) );
            add_filter( 'tc_settings_email_sections', array( &$this, 'tc_email_notifications_section' ) );
            add_filter( 'tc_settings_email_fields', array( &$this, 'tc_email_notifications_fields' ) );
            add_filter( 'wp_mail_content_type', array( &$this, 'set_content_type' ) );
            add_action( 'init', array( &$this, 'localization' ), 10 );
        }
        
        //Plugin localization function
        function localization()
        {
            // Load up the localization file if we're using WordPress in a different language
            // Place it in this plugin's "languages" folder and name it "tc-[value in wp-config].mo"
            
            if ( $this->location == 'mu-plugins' ) {
                load_muplugin_textdomain( 'chin', 'languages/' );
            } else {
                
                if ( $this->location == 'subfolder-plugins' ) {
                    load_plugin_textdomain( 'chin', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
                } else {
                    
                    if ( $this->location == 'plugins' ) {
                        load_plugin_textdomain( 'chin', false, 'languages/' );
                    } else {
                    }
                
                }
            
            }
            
            $temp_locales = explode( '_', get_locale() );
            $this->language = ( $temp_locales[0] ? $temp_locales[0] : 'en' );
        }
        
        //ading section in the e-mail tab
        function tc_email_notifications_section( $sections )
        {
            $sections[] = array(
                'name'        => 'email_notifications',
                'title'       => __( 'Check-in Notifications', 'chin' ),
                'description' => '',
            );
            return $sections;
        }
        
        //ading fields in the e-mail notifications section
        function tc_email_notifications_fields( $fields )
        {
            $fields[] = array(
                'field_name'        => 'subject',
                'field_title'       => __( 'Set the mail subject', 'chin' ),
                'field_type'        => 'option',
                'default_value'     => __( 'Ticket Check-in', 'chin' ),
                'field_description' => __( 'Set the mail subject', 'chin' ),
                'section'           => 'email_notifications',
            );
            $fields[] = array(
                'field_name'        => 'from_name',
                'field_title'       => __( 'From Name', 'chin' ),
                'field_type'        => 'option',
                'default_value'     => get_option( 'blogname' ),
                'field_description' => __( 'This name will appear as sent from name in the e-mail.', 'chin' ),
                'section'           => 'email_notifications',
            );
            $fields[] = array(
                'field_name'        => 'from_email',
                'field_title'       => __( 'From E-Mail Address', 'chin' ),
                'field_type'        => 'option',
                'default_value'     => get_option( 'admin_email' ),
                'field_description' => __( 'This e-mail will appear as sender address.', 'chin' ),
                'section'           => 'email_notifications',
            );
            $fields[] = array(
                'field_name'        => 'checkin_notifications_text',
                'field_title'       => __( 'Check-in Notifications Text', 'chin' ),
                'field_type'        => 'function',
                'function'          => 'tc_get_notification_message',
                'default_value'     => __( 'Hello (OWNER_NAME), your ticket has been checked at (EVENT)', 'chin' ),
                'field_description' => __( 'Set the text that will be sent to ticket owner e-mail like information about the event, map, program etc...' . 'You can use following placeholders (OWNER_NAME), (EVENT), (TICKET TYPE)', 'chin' ),
                'section'           => 'email_notifications',
            );
            $fields[] = array(
                'field_name'        => 'checkin_notifications',
                'field_title'       => __( 'Send Check-in Notification To Owner', 'chin' ),
                'field_type'        => 'function',
                'function'          => 'tc_yes_no_checkins',
                'default_value'     => 'yes',
                'field_description' => __( 'Check the field to send notifications to owner e-mail when they are checked-in.', 'chin' ),
                'section'           => 'email_notifications',
            );
            $fields[] = array(
                'field_name'        => 'checkin_notifications_buyer',
                'field_title'       => __( 'Send Check-in Notification To Buyer', 'chin' ),
                'field_type'        => 'function',
                'function'          => 'tc_yes_no_checkins',
                'default_value'     => 'no',
                'field_description' => __( 'Check the field to send notifications to buyer e-mail when they are checked-in.', 'chin' ),
                'section'           => 'email_notifications',
            );
            return $fields;
        }
        
        function set_content_type( $content_type )
        {
            return 'text/html';
        }
        
        //function responsible for sending e-mails
        function tc_check_in_notification_email( $ticket_id )
        {
            $order_id = wp_get_post_parent_id( $ticket_id );
            $order = new TC_Order( $order_id );
            $tc_email_settings = get_option( 'tc_email_setting', false );
            $tc_get_owner_mail = get_post_meta( $ticket_id, 'owner_email', true );
            $tc_general_settngs = get_option( 'tc_general_setting' );
            $tc_owner_fields = $tc_general_settngs['show_owner_fields'];
            $tc_get_buyer_mail = ( isset( $order->details->tc_cart_info['buyer_data']['email_post_meta'] ) ? $order->details->tc_cart_info['buyer_data']['email_post_meta'] : '' );
            $tc_get_buyer_mail = apply_filters( 'tc_ticket_checkin_buyer_email', $tc_get_buyer_mail, $order->details->ID );
            
            if ( $tc_email_settings['checkin_notifications'] == 'yes' || $tc_email_settings['checkin_notifications_buyer'] == 'yes' ) {
                if ( $tc_email_settings['checkin_notifications'] == 'no' ) {
                    $tc_get_owner_mail = '';
                }
                if ( $tc_email_settings['checkin_notifications_buyer'] == 'no' ) {
                    $tc_get_buyer_mail = '';
                }
                $tc_checkin_mail = array( $tc_get_owner_mail, $tc_get_buyer_mail );
                
                if ( $tc_owner_fields == 'no' ) {
                    $tc_get_owner_first_name = get_post_meta( $order_id, '_billing_first_name', true );
                    $tc_get_owner_last_name = get_post_meta( $order_id, '_billing_last_name', true );
                    
                    if ( empty($tc_get_owner_first_name) && empty($tc_get_owner_last_name) ) {
                        $tc_get_owner_first_name = $order->details->tc_cart_info['buyer_data']['first_name_post_meta'];
                        $tc_get_owner_last_name = $order->details->tc_cart_info['buyer_data']['last_name_post_meta'];
                    }
                
                } else {
                    $tc_get_owner_first_name = get_post_meta( $ticket_id, 'first_name', true );
                    $tc_get_owner_last_name = get_post_meta( $ticket_id, 'last_name', true );
                }
                
                $tc_get_event_name = get_post_meta( $ticket_id, 'event_id', true );
                $tc_get_event_name = get_the_title( $tc_get_event_name );
                $check_text = $tc_email_settings['checkin_notifications_text'];
                $ticket_type_id = get_post_meta( $ticket_id, apply_filters( 'tc_ticket_type_id', 'ticket_type_id' ), true );
                $ticket_type_name = apply_filters( 'tc_checkout_owner_info_ticket_title', get_the_title( $ticket_type_id ), $ticket_type_id );
                $tc_placeholders = array( 'OWNER_NAME', 'EVENT', 'TICKET_TYPE' );
                $tc_placeholder_values = array( $tc_get_owner_first_name . ' ' . $tc_get_owner_last_name, $tc_get_event_name, $ticket_type_name );
                $tc_message = str_replace( $tc_placeholders, $tc_placeholder_values, $tc_email_settings['checkin_notifications_text'] );
                $tc_headers = 'From: ' . $tc_email_settings['from_name'] . ' <' . $tc_email_settings['from_email'] . '>' . "\r\n";
                wp_mail(
                    $tc_checkin_mail,
                    $tc_email_settings['subject'],
                    stripcslashes( apply_filters( 'tc_checkin_notification', $tc_message ) ),
                    $tc_headers
                );
                remove_filter( 'wp_mail_content_type', 'set_content_type' );
            }
            
            // if($tc_email_settings['checkin_notifications'] == 'yes' || $tc_email_settings['checkin_notifications_buyer'] == 'yes' )
        }
    
    }
    //class TC_Checkin_Notifications
}

//if (!class_exists('TC_Checkin_Notifications'))
// checking notifiation function for yes and no
if ( !function_exists( 'is_plugin_active_for_network' ) ) {
    require_once ABSPATH . '/wp-admin/includes/plugin.php';
}

if ( is_multisite() && is_plugin_active_for_network( plugin_basename( __FILE__ ) ) ) {
    function tc_checkin_notifications_load()
    {
        global  $tc_checkin_notifications ;
        $tc_checkin_notifications = new TC_Checkin_Notifications();
    }
    
    add_action( 'tets_fs_loaded', 'tc_checkin_notifications_load' );
} else {
    $tc_checkin_notifications = new TC_Checkin_Notifications();
}

function tc_yes_no_checkins( $field_name, $default_value = '' )
{
    $tc_email_settings = get_option( 'tc_email_setting', false );
    
    if ( isset( $tc_email_settings[$field_name] ) ) {
        $checked = $tc_email_settings[$field_name];
    } else {
        
        if ( $default_value !== '' ) {
            $checked = $default_value;
        } else {
            $checked = 'no';
        }
    
    }
    
    ?>
    <label>
        <input type="radio" name="tc_email_setting[<?php 
    echo  esc_attr( $field_name ) ;
    ?>]" value="yes" <?php 
    checked( $checked, 'yes', true );
    ?>  /><?php 
    _e( 'Yes', 'chin' );
    ?>
    </label>
    <label>
        <input type="radio" name="tc_email_setting[<?php 
    echo  esc_attr( $field_name ) ;
    ?>]" value="no" <?php 
    checked( $checked, 'no', true );
    ?> /><?php 
    _e( 'No', 'chin' );
    ?>
    </label>
    <?php 
}

//function tc_yes_no_checkins($field_name, $default_value = '')
//wp editor for notification message
function tc_get_notification_message( $field_name, $default_value = '' )
{
    global  $tc_email_settings ;
    
    if ( isset( $tc_email_settings[$field_name] ) ) {
        $value = $tc_email_settings[$field_name];
    } else {
        
        if ( $default_value !== '' ) {
            $value = $default_value;
        } else {
            $value = '';
        }
    
    }
    
    wp_editor( html_entity_decode( stripcslashes( $value ) ), $field_name, array(
        'textarea_name' => 'tc_email_setting[' . $field_name . ']',
        'textarea_rows' => 2,
    ) );
}

if ( !function_exists( 'tcchin_fs' ) ) {
    // Create a helper function for easy SDK access.
    function tcchin_fs()
    {
        global  $tcchin_fs ;
        
        if ( !isset( $tcchin_fs ) ) {
            // Activate multisite network integration.
            if ( !defined( 'WP_FS__PRODUCT_3182_MULTISITE' ) ) {
                define( 'WP_FS__PRODUCT_3182_MULTISITE', true );
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
            
            $tcchin_fs = fs_dynamic_init( array(
                'id'               => '3182',
                'slug'             => 'check-in-notifications',
                'premium_slug'     => 'check-in-notifications',
                'type'             => 'plugin',
                'public_key'       => 'pk_9a3d0abd6c056523178b5c2b7701d',
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
        
        return $tcchin_fs;
    }

}
function tcchin_fs_is_parent_active_and_loaded()
{
    // Check if the parent's init SDK method exists.
    return function_exists( 'tets_fs' );
}

function tcchin_fs_is_parent_active()
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

function tcchin_fs_init()
{
    
    if ( tcchin_fs_is_parent_active_and_loaded() ) {
        // Init Freemius.
        tcchin_fs();
        // Parent is active, add your init code here.
    } else {
        // Parent is inactive, add your error handling here.
    }

}


if ( tcchin_fs_is_parent_active_and_loaded() ) {
    // If parent already included, init add-on.
    tcchin_fs_init();
} else {
    
    if ( tcchin_fs_is_parent_active() ) {
        // Init add-on only after the parent is loaded.
        add_action( 'tets_fs_loaded', 'tcchin_fs_init' );
    } else {
        // Even though the parent is not activated, execute add-on for activation / uninstall hooks.
        tcchin_fs_init();
    }

}
