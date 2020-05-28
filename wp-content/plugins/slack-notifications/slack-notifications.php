<?php

/*
 Plugin Name: Tickera Slack Notifications
 Plugin URI: https://tickera.com/
 Description: Receive notifications to your Slack channel whenever a sale occurs in your Tickera store.
 Author: Tickera.com
 Author URI: https://tickera.com/
 Version: 1.1.5
 TextDomain: slack
 Domain Path: /languages/
 Copyright 2019 Tickera (https://tickera.com/)
*/
class TC_Slack_Notifications_Addon
{
    var  $version = '1.1.5' ;
    var  $title = 'Slack' ;
    var  $name = 'tc_slack' ;
    var  $dir_name = 'slack-notifications' ;
    var  $location = 'plugins' ;
    var  $plugin_dir = '' ;
    var  $plugin_url = '' ;
    /**
     * Refers to a single instance of the class
     *
     * @since 3.0
     * @access private
     * @var object
     */
    private static  $_instance = null ;
    /**
     * Gets the single instance of the class
     *
     * @since 3.0
     * @access public
     * @return object
     */
    public static function get_instance()
    {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new TC_Slack_Notifications_Addon();
        }
        return self::$_instance;
    }
    
    /**
     * Constructor function
     *
     * @access private
     */
    private function __construct()
    {
        $this->init_vars();
        add_action( 'init', array( &$this, 'load_plugin_textdomain' ), 11 );
        //if (class_exists('TC')) {//Check if Tickera plugin is active / main Ticekra class exists
        global  $tc ;
        add_filter(
            'tc_settings_new_menus',
            array( &$this, 'tc_settings_new_menus' ),
            10,
            1
        );
        add_action( 'tc_settings_menu_slack', array( &$this, 'tc_settings_menu_slack' ) );
        add_filter( 'tc_admin_capabilities', array( &$this, 'append_capabilities' ) );
        add_action(
            'tc_order_created',
            array( &$this, 'send_slack_notification' ),
            999,
            5
        );
        add_action(
            'tc_order_updated_status_to_paid',
            array( &$this, 'send_slack_notification' ),
            999,
            5
        );
        add_action(
            'tc_order_paid_change',
            array( &$this, 'send_slack_notification' ),
            999,
            5
        );
        //}
        add_filter(
            'tc_delete_info_plugins_list',
            array( $this, 'tc_delete_info_plugins_list' ),
            10,
            1
        );
        add_action(
            'tc_delete_plugins_data',
            array( $this, 'tc_delete_plugins_data' ),
            10,
            1
        );
        add_action( 'init', array( &$this, 'localization' ), 10 );
    }
    
    //Plugin localization function
    function localization()
    {
        // Load up the localization file if we're using WordPress in a different language
        // Place it in this plugin's "languages" folder and name it "tc-[value in wp-config].mo"
        
        if ( $this->location == 'mu-plugins' ) {
            load_muplugin_textdomain( 'slack', 'languages/' );
        } else {
            
            if ( $this->location == 'subfolder-plugins' ) {
                load_plugin_textdomain( 'slack', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
            } else {
                
                if ( $this->location == 'plugins' ) {
                    load_plugin_textdomain( 'slack', false, 'languages/' );
                } else {
                }
            
            }
        
        }
        
        $temp_locales = explode( '_', get_locale() );
        $this->language = ( $temp_locales[0] ? $temp_locales[0] : 'en' );
    }
    
    function tc_delete_info_plugins_list( $plugins )
    {
        $plugins[$this->name] = $this->title;
        return $plugins;
    }
    
    function tc_delete_plugins_data( $submitted_data )
    {
        
        if ( array_key_exists( $this->name, $submitted_data ) ) {
            global  $wpdb ;
            //Delete options
            $options = array( 'tc_slack_settings' );
            foreach ( $options as $option ) {
                delete_option( $option );
            }
        }
    
    }
    
    function init_vars()
    {
        //setup proper directories
        
        if ( defined( 'WP_PLUGIN_URL' ) && defined( 'WP_PLUGIN_DIR' ) && file_exists( WP_PLUGIN_DIR . '/' . $this->dir_name . '/' . basename( __FILE__ ) ) ) {
            $this->location = 'subfolder-plugins';
            $this->plugin_dir = WP_PLUGIN_DIR . '/' . $this->dir_name . '/';
            $this->plugin_url = plugins_url( '/', __FILE__ );
        } else {
            
            if ( defined( 'WP_PLUGIN_URL' ) && defined( 'WP_PLUGIN_DIR' ) && file_exists( WP_PLUGIN_DIR . '/' . basename( __FILE__ ) ) ) {
                $this->location = 'plugins';
                $this->plugin_dir = WP_PLUGIN_DIR . '/';
                $this->plugin_url = plugins_url( '/', __FILE__ );
            } else {
                
                if ( is_multisite() && defined( 'WPMU_PLUGIN_URL' ) && defined( 'WPMU_PLUGIN_DIR' ) && file_exists( WPMU_PLUGIN_DIR . '/' . basename( __FILE__ ) ) ) {
                    $this->location = 'mu-plugins';
                    $this->plugin_dir = WPMU_PLUGIN_DIR;
                    $this->plugin_url = WPMU_PLUGIN_URL;
                } else {
                    wp_die( sprintf( __( 'There was an issue determining where %s is installed. Please reinstall it.', 'slack' ), $this->title ) );
                }
            
            }
        
        }
    
    }
    
    function tc_settings_new_menus( $menus )
    {
        $menus['slack'] = __( 'Slack', 'slack' );
        return $menus;
    }
    
    function tc_settings_menu_slack()
    {
        include $this->plugin_dir . 'includes/admin-pages/slack_settings.php';
    }
    
    function append_capabilities( $capabilities )
    {
        //Add additional capabilities to admins
        $capabilities['manage_' . $this->name . '_cap'] = 1;
        return $capabilities;
    }
    
    public function send_slack_notification(
        $order_id,
        $status,
        $cart_contents,
        $cart_info,
        $payment_info
    )
    {
        global  $tc ;
        $slack_notifications_settings = get_option( 'tc_slack_settings' );
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        if ( $status !== 'order_paid' ) {
            return;
        }
        
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
        //Make sure it's not already sent
        $sent_slack_notification = get_post_meta( $order_id, 'sent_slack_notification', true );
        
        if ( isset( $sent_slack_notification ) && $sent_slack_notification == '1' ) {
            return;
        } else {
            update_post_meta( $order_id, 'sent_slack_notification', '1' );
        }
        
        $tc_general_settings = get_option( 'tc_general_setting', false );
        $tax_label = ( isset( $tc_general_settings['tax_label'] ) ? $tc_general_settings['tax_label'] : __( 'Tax', 'slack' ) );
        $fees_label = ( isset( $tc_general_settings['fees_label'] ) ? $tc_general_settings['fees_label'] : __( 'Fees', 'slack' ) );
        $slack_channel = ( isset( $slack_notifications_settings['channel_name'] ) ? $slack_notifications_settings['channel_name'] : '#ticketsales' );
        $webhook_url = ( isset( $slack_notifications_settings['webhook_url'] ) ? $slack_notifications_settings['webhook_url'] : '' );
        $title = ( isset( $slack_notifications_settings['title'] ) && !empty($slack_notifications_settings['title']) ? $slack_notifications_settings['title'] : __( 'New Sale!', 'slack' ) );
        if ( !($slack_channel && $webhook_url) ) {
            return;
        }
        $emoji = ( !empty($slack_notifications_settings['bot_icon']) ? $slack_notifications_settings['bot_icon'] : ':moneybag:' );
        $bot_name = ( !empty($slack_notifications_settings['bot_name']) ? $slack_notifications_settings['bot_name'] : __( 'Ticket Sales', 'slack' ) );
        $order_amount = $tc->get_cart_currency_and_format( $payment_info['total'] );
        $items_sold = "";
        foreach ( $cart_contents as $ticket_type_id => $ordered_count ) {
            $ticket = new TC_Ticket( $ticket_type_id );
            $name = $ticket->details->post_title;
            $items_sold .= $name . " x " . $ordered_count . " \n";
        }
        $subtotal = $tc->get_cart_currency_and_format( $payment_info['subtotal'] );
        $tax_total = $payment_info['tax_total'];
        $fees_total = $payment_info['fees_total'];
        $discounts = new TC_Discounts();
        $discount_total = $discounts->get_discount_total_by_order( $order_id );
        
        if ( $discount_total > 0 ) {
            $discount_total = $tc->get_cart_currency_and_format( $discount_total );
        } else {
            $discount_total = 0;
        }
        
        $fees_total_formatted = $tc->get_cart_currency_and_format( $fees_total );
        $tax_total_formatted = $tc->get_cart_currency_and_format( $tax_total );
        $payment_method = $payment_info['gateway_public_name'];
        $message = __( 'A new order ', 'slack' ) . '<' . admin_url( 'edit.php?post_type=tc_events&page=tc_orders&action=details&ID=' . $order_id ) . '|' . strtoupper( $order->details->post_title ) . '>' . "\n\n";
        $message .= "*" . __( 'TICKET(S):', 'slack' ) . "* \n";
        $message .= $items_sold;
        if ( $subtotal !== $order_amount ) {
            $message .= "\n *" . __( 'Subtotal:', 'slack' ) . "* {$subtotal} \n";
        }
        if ( $tax_total > 0 ) {
            $message .= "\n *" . $tax_label . ":* {$tax_total_formatted} \n";
        }
        if ( $fees_total > 0 ) {
            $message .= "\n *" . $fees_label . ":* {$fees_total_formatted} \n";
        }
        if ( $discount_total !== 0 ) {
            $message .= "\n *" . __( 'Discount Value' ) . ":* {$discount_total} \n";
        }
        $message .= "\n *" . __( 'Order Total:', 'slack' ) . "* {$order_amount} \n";
        $message .= "*" . __( 'Payment Method:', 'slack' ) . "* {$payment_method} \n";
        $attachment = array();
        $attachment[] = array(
            'fallback'  => $title . " " . $order_amount,
            'title'     => $title,
            'text'      => $message,
            'color'     => 'good',
            'mrkdwn_in' => array( 'text' ),
        );
        $payload = array(
            'username'    => $bot_name,
            'attachments' => $attachment,
            'icon_emoji'  => $emoji,
            'channel'     => $slack_channel,
        );
        $args = array(
            'body'    => json_encode( $payload ),
            'timeout' => 30,
        );
        $response = wp_remote_post( $webhook_url, $args );
        return;
    }
    
    //Plugin localization function
    public function load_plugin_textdomain()
    {
        $locale = apply_filters( 'plugin_locale', get_locale(), 'slack' );
        load_textdomain( 'slack', WP_LANG_DIR . '/slack-notifications-' . $locale . '.mo' );
        load_textdomain( 'slack', WP_LANG_DIR . 'slack-notifications/slack-notifications-' . $locale . '.mo' );
        load_plugin_textdomain( 'slack', false, plugin_basename( dirname( __FILE__ ) ) . "/languages" );
    }

}
TC_Slack_Notifications_Addon::get_instance();
if ( !function_exists( 'tc_slack_notifications_addon' ) ) {
    function tc_slack_notifications_addon()
    {
        return TC_Slack_Notifications_Addon::get_instance();
    }

}
if ( !function_exists( 'tcsn_fs' ) ) {
    // Create a helper function for easy SDK access.
    function tcsn_fs()
    {
        global  $tcsn_fs ;
        
        if ( !isset( $tcsn_fs ) ) {
            // Activate multisite network integration.
            if ( !defined( 'WP_FS__PRODUCT_3173_MULTISITE' ) ) {
                define( 'WP_FS__PRODUCT_3173_MULTISITE', true );
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
            
            $tcsn_fs = fs_dynamic_init( array(
                'id'               => '3173',
                'slug'             => 'slack-notifications',
                'premium_slug'     => 'slack-notifications',
                'type'             => 'plugin',
                'public_key'       => 'pk_214a30f54f9e59857de3b57dc915e',
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
        
        return $tcsn_fs;
    }

}
function tcsn_fs_is_parent_active_and_loaded()
{
    // Check if the parent's init SDK method exists.
    return function_exists( 'tets_fs' );
}

function tcsn_fs_is_parent_active()
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

function tcsn_fs_init()
{
    
    if ( tcsn_fs_is_parent_active_and_loaded() ) {
        // Init Freemius.
        tcsn_fs();
        // Parent is active, add your init code here.
    } else {
        // Parent is inactive, add your error handling here.
    }

}


if ( tcsn_fs_is_parent_active_and_loaded() ) {
    // If parent already included, init add-on.
    tcsn_fs_init();
} else {
    
    if ( tcsn_fs_is_parent_active() ) {
        // Init add-on only after the parent is loaded.
        add_action( 'tets_fs_loaded', 'tcsn_fs_init' );
    } else {
        // Even though the parent is not activated, execute add-on for activation / uninstall hooks.
        tcsn_fs_init();
    }

}
