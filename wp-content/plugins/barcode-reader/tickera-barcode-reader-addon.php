<?php

/*
 Plugin Name: Tickera - Barcode Reader Add-on
 Plugin URI: http://tickera.com/
 Description: Add Barcode Reader support to Tickera plugin
 Author: Tickera.com
 Author URI: http://tickera.com/
 Version: 1.2.2.3
 Text Domain: bcr
 Domain Path: /languages/

 Copyright 2019 Tickera (http://tickera.com/)
*/
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
// Exit if accessed directly
if ( !class_exists( 'TC_Barcode_Reader' ) ) {
    class TC_Barcode_Reader
    {
        var  $version = '1.2.2.3' ;
        var  $title = 'Barcode Reader' ;
        var  $name = 'tc_barcode_reader' ;
        var  $dir_name = 'barcode-reader' ;
        var  $location = 'plugins' ;
        var  $plugin_dir = '' ;
        var  $plugin_url = '' ;
        function __construct()
        {
            $this->init_vars();
            
            if ( class_exists( 'TC' ) ) {
                //Check if Tickera plugin is active / main Tickera class exists
                global  $tc ;
                add_filter( 'tc_admin_capabilities', array( &$this, 'append_capabilities' ) );
                add_filter( 'tc_staff_capabilities', array( &$this, 'append_capabilities' ) );
                add_action( $tc->name . '_add_menu_items_after_ticket_templates', array( &$this, 'add_admin_menu_item_to_tc' ) );
                add_action( 'admin_enqueue_scripts', array( &$this, 'admin_header' ) );
                add_action( 'wp_ajax_check_in_barcode', array( &$this, 'check_in_barcode' ) );
                add_action( 'wp_ajax_nopriv_check_in_barcode', array( &$this, 'check_in_barcode' ) );
                add_action( 'tc_load_ticket_template_elements', array( &$this, 'tc_load_ticket_template_elements' ) );
                add_action( 'init', array( &$this, 'localization' ), 10 );
            }
        
        }
        
        //Plugin localization function
        function localization()
        {
            // Load up the localization file if we're using WordPress in a different language
            // Place it in this plugin's "languages" folder and name it "tc-[value in wp-config].mo"
            
            if ( $this->location == 'mu-plugins' ) {
                load_muplugin_textdomain( 'bcr', 'languages/' );
            } else {
                
                if ( $this->location == 'subfolder-plugins' ) {
                    load_plugin_textdomain( 'bcr', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
                } else {
                    
                    if ( $this->location == 'plugins' ) {
                        load_plugin_textdomain( 'bcr', false, 'languages/' );
                    } else {
                    }
                
                }
            
            }
            
            $temp_locales = explode( '_', get_locale() );
            $this->language = ( $temp_locales[0] ? $temp_locales[0] : 'en' );
        }
        
        function tc_load_ticket_template_elements()
        {
            include $this->plugin_dir . 'includes/ticket-elements/ticket_barcode_element.php';
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
                        wp_die( sprintf( __( 'There was an issue determining where %s is installed. Please reinstall it.', 'bcr' ), $this->title ) );
                    }
                
                }
            
            }
        
        }
        
        function check_in_barcode()
        {
            //Waiting for ajax calls to check barcode
            
            if ( isset( $_POST['api_key'] ) && isset( $_POST['barcode'] ) && defined( 'DOING_AJAX' ) && DOING_AJAX ) {
                $api_key = new TC_API_Key( $_POST['api_key'] );
                $checkin = new TC_Checkin_API(
                    $api_key->details->api_key,
                    apply_filters( 'tc_checkin_request_name', 'tickera_scan' ),
                    'return',
                    $_POST['barcode'],
                    false
                );
                $checkin_result = $checkin->ticket_checkin( false );
                
                if ( is_numeric( $checkin_result ) && $checkin_result == 403 ) {
                    //permissions issue
                    echo  $checkin_result ;
                    exit;
                } else {
                    
                    if ( $checkin_result['status'] == 1 ) {
                        //success
                        echo  1 ;
                        exit;
                    } else {
                        //fail
                        echo  2 ;
                        exit;
                    }
                
                }
            
            }
        
        }
        
        function append_capabilities( $capabilities )
        {
            //Add additional capabilities to staff and admins
            $capabilities['manage_' . $this->name . '_cap'] = 1;
            return $capabilities;
        }
        
        function add_admin_menu_item_to_tc()
        {
            //Add additional menu item under Tickera admin menu
            global  $first_tc_menu_handler ;
            $handler = 'ticket_templates';
            add_submenu_page(
                $first_tc_menu_handler,
                __( $this->title, 'bcr' ),
                __( $this->title, 'bcr' ),
                'manage_' . $this->name . '_cap',
                $this->name,
                $this->name . '_admin'
            );
            eval("function " . $this->name . "_admin() {require_once( '" . $this->plugin_dir . "includes/admin-pages/" . $this->name . ".php');}");
            do_action( $this->name . '_add_menu_items_after_' . $handler );
        }
        
        function admin_header()
        {
            //Add scripts and CSS for the plugin
            wp_enqueue_script(
                $this->name . '-admin',
                $this->plugin_url . 'js/admin.js',
                array( 'jquery' ),
                false,
                false
            );
            wp_localize_script( $this->name . '-admin', 'tc_barcode_reader_vars', array(
                'admin_ajax_url'                       => admin_url( 'admin-ajax.php' ),
                'message_barcode_default'              => __( 'Select input field and scan a barcode located on the ticket.', 'bcr' ),
                'message_checking_in'                  => __( 'Checking in...', 'bcr' ),
                'message_insufficient_permissions'     => __( 'Insufficient permissions. This API key cannot check in this ticket.', 'bcr' ),
                'message_barcode_status_error'         => __( 'Ticket code is wrong or expired.', 'bcr' ),
                'message_barcode_status_success'       => __( 'Ticket has been successfully checked in.', 'bcr' ),
                'message_barcode_status_error_exists'  => __( 'Ticket does not exist.', 'bcr' ),
                'message_barcode_api_key_not_selected' => sprintf( __( 'Please create and select an %s in order to check in the ticket.', 'bcr' ), '<a href="' . admin_url( 'admin.php?page=tc_settings&tab=api' ) . '">' . __( 'API Key', 'bcr' ) . '</a>' ),
                'message_barcode_cannot_be_empty'      => __( 'Ticket code cannot be empty', 'bcr' ),
            ) );
            wp_enqueue_style(
                $this->name . '-admin',
                $this->plugin_url . 'css/admin.css',
                array(),
                $this->version
            );
        }
    
    }
}
if ( !function_exists( 'is_plugin_active_for_network' ) ) {
    require_once ABSPATH . '/wp-admin/includes/plugin.php';
}

if ( is_multisite() && is_plugin_active_for_network( plugin_basename( __FILE__ ) ) ) {
    function tc_barcode_reader_load()
    {
        global  $tc_barcode_reader ;
        $tc_barcode_reader = new TC_Barcode_Reader();
    }
    
    add_action( 'tets_fs_loaded', 'tc_barcode_reader_load' );
} else {
    $tc_barcode_reader = new TC_Barcode_Reader();
}


if ( !function_exists( 'barcode_reader_fs' ) ) {
    // Create a helper function for easy SDK access.
    function barcode_reader_fs()
    {
        global  $barcode_reader_fs ;
        
        if ( !isset( $barcode_reader_fs ) ) {
            // Activate multisite network integration.
            if ( !defined( 'WP_FS__PRODUCT_3170_MULTISITE' ) ) {
                define( 'WP_FS__PRODUCT_3170_MULTISITE', true );
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
            
            $barcode_reader_fs = fs_dynamic_init( array(
                'id'               => '3170',
                'slug'             => 'barcode-reader',
                'premium_slug'     => 'barcode-reader',
                'type'             => 'plugin',
                'public_key'       => 'pk_27912bc9aa4331a1f230f4f86cd7a',
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
                'override_exact' => true,
                'first-path'     => 'plugins.php',
                'support'        => false,
            ),
                'is_live'          => true,
            ) );
        }
        
        return $barcode_reader_fs;
    }
    
    function barcode_reader_fs_settings_url()
    {
        return admin_url( 'edit.php?post_type=tc_events&page=tc_barcode_reader' );
    }

}

function barcode_reader_fs_is_parent_active_and_loaded()
{
    // Check if the parent's init SDK method exists.
    return function_exists( 'tets_fs' );
}

function barcode_reader_fs_is_parent_active()
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

function barcode_reader_fs_init()
{
    
    if ( barcode_reader_fs_is_parent_active_and_loaded() ) {
        // Init Freemius.
        barcode_reader_fs();
        barcode_reader_fs()->add_filter( 'connect_url', 'barcode_reader_fs_settings_url' );
        barcode_reader_fs()->add_filter( 'after_skip_url', 'barcode_reader_fs_settings_url' );
        barcode_reader_fs()->add_filter( 'after_connect_url', 'barcode_reader_fs_settings_url' );
        barcode_reader_fs()->add_filter( 'after_pending_connect_url', 'barcode_reader_fs_settings_url' );
        // Parent is active, add your init code here.
    } else {
        // Parent is inactive, add your error handling here.
    }

}


if ( barcode_reader_fs_is_parent_active_and_loaded() ) {
    // If parent already included, init add-on.
    barcode_reader_fs_init();
} else {
    
    if ( barcode_reader_fs_is_parent_active() ) {
        // Init add-on only after the parent is loaded.
        add_action( 'tets_fs_loaded', 'barcode_reader_fs_init' );
    } else {
        // Even though the parent is not activated, execute add-on for activation / uninstall hooks.
        barcode_reader_fs_init();
    }

}
