<?php

/*
 Plugin Name: Tickera Sendloop
 Plugin URI: http://tickera.com/
 Description: Tickera Sendloop Newsletter add-on allows you to send marketing emails, automated messages, and targeted campaigns to your customers.
 Author: Tickera.com
 Author URI: http://tickera.com/
 Version: 1.0.9
 Text Domain: sl
 Domain Path: /languages/
 Copyright 2019 Tickera (http://tickera.com/)
*/
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
// Exit if accessed directly

if ( !class_exists( 'TC_Sendloop' ) ) {
    class TC_Sendloop
    {
        var  $version = '1.0.9' ;
        var  $title = 'Tickera Sendloop' ;
        var  $name = 'sl' ;
        var  $dir_name = 'sendloop-newsletter' ;
        var  $location = 'plugins' ;
        var  $plugin_dir = '' ;
        var  $plugin_url = '' ;
        function __construct()
        {
            add_filter( 'tc_settings_new_menus', array( &$this, 'tc_settings_new_menus_additional' ) );
            add_action( 'tc_settings_menu_tickera_sendloop', array( &$this, 'tc_settings_menu_tickera_sendloop_show_page' ) );
            add_action(
                'tc_order_created',
                array( &$this, 'tc_order_created' ),
                10,
                5
            );
            add_action( 'tc_before_cart_submit', array( &$this, 'tc_add_sendloop_field' ) );
            add_action(
                'tc_cart_passed_successfully',
                array( &$this, 'tc_sendloop_check_confirmation' ),
                10,
                0
            );
            add_action(
                'woocommerce_checkout_process',
                array( &$this, 'tc_sendloop_check_confirmation' ),
                10,
                1
            );
            add_action(
                'woocommerce_new_order',
                array( &$this, 'tc_subscribe_to_sendloop' ),
                20,
                1
            );
            add_action(
                'woocommerce_resume_order',
                array( &$this, 'tc_subscribe_to_sendloop' ),
                20,
                1
            );
            add_action(
                'woocommerce_api_create_order',
                array( &$this, 'tc_subscribe_to_sendloop' ),
                20,
                1
            );
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
                load_muplugin_textdomain( 'sl', 'languages/' );
            } else {
                
                if ( $this->location == 'subfolder-plugins' ) {
                    load_plugin_textdomain( 'sl', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
                } else {
                    
                    if ( $this->location == 'plugins' ) {
                        load_plugin_textdomain( 'sl', false, 'languages/' );
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
                $options = array( 'tc_sendloop_settings' );
                foreach ( $options as $option ) {
                    delete_option( $option );
                }
            }
        
        }
        
        function tc_settings_new_menus_additional( $settings_tabs )
        {
            $settings_tabs['tickera_sendloop'] = __( 'Sendloop', 'sl' );
            return $settings_tabs;
        }
        
        //set sendlooop options
        function tc_settings_menu_tickera_sendloop_show_page()
        {
            require_once $this->plugin_dir . 'includes/admin-pages/settings-tickera_sendloop.php';
        }
        
        function tc_add_sendloop_field()
        {
            $tc_sendloop_settings = get_option( 'tc_sendloop_settings' );
            
            if ( isset( $tc_sendloop_settings['enable_confirmation_box'] ) ) {
                $tc_sendloop_confirmation = $tc_sendloop_settings['enable_confirmation_box'];
            } else {
                $tc_sendloop_confirmation = '';
            }
            
            
            if ( $tc_sendloop_confirmation && !isset( $tc_sendloop_settings['disable_sendloop'] ) ) {
                ?>
                <label>
                    <input type="checkbox" name="tc-sendloop-subscribe" value="1" <?php 
                echo  apply_filters( 'tc_sendloop_checked', 'checked' ) ;
                ?> /><?php 
                _e( 'Sign-up to our newsletter.', 'tc-sendloop' );
                ?>
                </label>
                <?php 
            }
            
            //if ( $tc_sendloop_confirmation )
        }
        
        //send data to sendloop
        function tc_order_created(
            $order_id,
            $status,
            $cart_contents,
            $cart_info,
            $payment_info
        )
        {
            $tc_sendloop_settings = get_option( 'tc_sendloop_settings' );
            if ( isset( $_SESSION['tc_sendloop_confirmed_subscription'] ) && $_SESSION['tc_sendloop_confirmed_subscription'] == false && $tc_sendloop_settings['enable_confirmation_box'] == true ) {
                return;
            }
            
            if ( !empty($tc_sendloop_settings['api_key']) && !empty($tc_sendloop_settings['list_id']) && !isset( $tc_sendloop_settings['disable_sendloop'] ) ) {
                $tc_buyer_first_name = $cart_info['buyer_data']['first_name_post_meta'];
                $tc_buyer_last_name = $cart_info['buyer_data']['last_name_post_meta'];
                $tc_buyer_email = $cart_info['buyer_data']['email_post_meta'];
                $tc_api_key = $tc_sendloop_settings['api_key'];
                $tc_list_id = $tc_sendloop_settings['list_id'];
                $tc_subdomain = $tc_sendloop_settings['subdomain'];
                include $this->plugin_dir . 'includes/sendloopapi3.php';
                $API = new SendloopAPI3( $tc_api_key, $tc_subdomain, 'php' );
                $API->run( 'Subscriber.Subscribe', array(
                    'ListID'         => $tc_list_id,
                    'EmailAddress'   => $tc_buyer_email,
                    'SubscriptionIP' => $_SERVER['REMOTE_ADDR'],
                ) );
            }
            
            //if ( !empty( $tc_sendloop_settings[ 'api_key' ] ) && !empty( $tc_sendloop_settings[ 'list_id' ] ) && !isset( $tc_sendloop_settings[ 'disable_sendloop' ] ) ) {
        }
        
        //function tc_order_created( $order_id, $status, $cart_contents, $cart_info, $payment_info )
        function tc_subscribe_to_sendloop( $order_id )
        {
            global  $woocommerce ;
            $tc_sendloop_settings = get_option( 'tc_sendloop_settings' );
            if ( isset( $_SESSION['tc_sendloop_confirmed_subscription'] ) && $_SESSION['tc_sendloop_confirmed_subscription'] == false && $tc_sendloop_settings['enable_confirmation_box'] == true ) {
                return;
            }
            $buyer_email = $_POST['billing_email'];
            $buyer_first_name = $_POST['billing_first_name'];
            $buyer_last_name = $_POST['billing_last_name'];
            $tc_tickets_instances_arg = array(
                'post_parent'    => $order_id,
                'post_type'      => 'tc_tickets_instances',
                'posts_per_page' => -1,
            );
            $tc_tickets_instances = get_posts( $tc_tickets_instances_arg );
            $ticket_buying_subscribe = $tc_sendloop_settings['users_buying_tickets'];
            $tc_ticket_instance = $tc_tickets_instances[0]->post_type;
            
            if ( $ticket_buying_subscribe == 1 && $tc_ticket_instance == 'tc_tickets_instances' ) {
                $this->tc_woo_bridge_sendloop( $buyer_first_name, $buyer_last_name, $buyer_email );
            } else {
                if ( !isset( $ticket_buying_subscribe ) ) {
                    $this->tc_woo_bridge_sendloop( $buyer_first_name, $buyer_last_name, $buyer_email );
                }
            }
        
        }
        
        //function tc_subscribe_to_sendloop($order_id)
        function tc_woo_bridge_sendloop()
        {
            $tc_sendloop_settings = get_option( 'tc_sendloop_settings' );
            
            if ( !empty($tc_sendloop_settings['api_key']) && !empty($tc_sendloop_settings['list_id']) && !isset( $tc_sendloop_settings['disable_sendloop'] ) ) {
                $tc_buyer_email = $_POST['billing_email'];
                $tc_buyer_first_name = $_POST['billing_first_name'];
                $tc_buyer_last_name = $_POST['billing_last_name'];
                $tc_api_key = $tc_sendloop_settings['api_key'];
                $tc_list_id = $tc_sendloop_settings['list_id'];
                $tc_subdomain = $tc_sendloop_settings['subdomain'];
                include $this->plugin_dir . 'includes/sendloopapi3.php';
                $API = new SendloopAPI3( $tc_api_key, $tc_subdomain, 'php' );
                $API->run( 'Subscriber.Subscribe', array(
                    'ListID'         => $tc_list_id,
                    'EmailAddress'   => $tc_buyer_email,
                    'SubscriptionIP' => $_SERVER['REMOTE_ADDR'],
                ) );
            }
            
            //if ( !empty( $tc_sendloop_settings[ 'api_key' ] ) && !empty( $tc_sendloop_settings[ 'list_id' ] ) && !isset( $tc_sendloop_settings[ 'disable_sendloop' ] ) ) {
        }
        
        function tc_sendloop_check_confirmation()
        {
            global  $tc ;
            $tc->start_session();
            $tc_sendloop_settings = get_option( 'tc_sendloop_settings' );
            
            if ( isset( $tc_sendloop_settings['enable_confirmation_box'] ) ) {
                $tc_sendloop_confirmation = $tc_sendloop_settings['enable_confirmation_box'];
            } else {
                $tc_sendloop_confirmation = '';
            }
            
            //Check if confirmation is needed get_option etc.
            
            if ( $tc_sendloop_confirmation ) {
                //NEED CONFIRMATION
                
                if ( isset( $_POST['tc-sendloop-subscribe'] ) ) {
                    $_SESSION['tc_sendloop_confirmed_subscription'] = true;
                } else {
                    $_SESSION['tc_sendloop_confirmed_subscription'] = false;
                }
            
            } else {
            }
        
        }
    
    }
    //class TC_Sendloop
    //class TC_Sendloop
}

//if ( !class_exists( 'TC_Sendloop' ) ) {
if ( !function_exists( 'is_plugin_active_for_network' ) ) {
    require_once ABSPATH . '/wp-admin/includes/plugin.php';
}

if ( is_multisite() && is_plugin_active_for_network( plugin_basename( __FILE__ ) ) ) {
    function tc_sendloop_load()
    {
        global  $tc_sendloop ;
        $tc_sendloop = new TC_Sendloop();
    }
    
    add_action( 'tets_fs_loaded', 'tc_sendloop_load' );
} else {
    $tc_sendloop = new TC_Sendloop();
}

//CHECKING SENDLOOP API
//ajax part
add_action( 'admin_enqueue_scripts', 'tc_check_sendloop' );
function tc_check_sendloop()
{
    wp_enqueue_script( 'sendloop-js', plugin_dir_url( __FILE__ ) . '/includes/javascript.js' );
}

//tc_check_sendloop() {
//php part called by the ajax for checking sendloop
add_action( 'wp_ajax_ajax_sendloop_check', 'check_sendloop_check' );
function check_sendloop_check()
{
    $tc_api_key = $_POST['tc_api_key'];
    $tc_list_id = $_POST['tc_list_id'];
    $tc_subdomain = $_POST['tc_subdomain'];
    include plugin_dir_path( __FILE__ ) . 'includes/sendloopapi3.php';
    $API = new SendloopAPI3( $tc_api_key, $tc_subdomain, 'php' );
    $API->run( 'List.Get', array(
        'ListID' => $tc_list_id,
    ) );
    
    if ( $API->Result['Success'] == '' && isset( $API->Result['Success'] ) ) {
        echo  '<span style="color:red;">' . $API->Result['ErrorMessage'] . '</span>' ;
    } else {
        
        if ( !isset( $API->Result['Success'] ) ) {
            echo  '<span style="color:red;">' . __( 'Please check that all fields are filled in correctly.', 'tk' ) . '</span>' ;
        } else {
            echo  '<span style="color:green;">' . __( 'Everything works fine!', 'tk' ) . '</span>' ;
        }
    
    }
    
    wp_die();
    // this is required to terminate immediately and return a proper response
}

if ( !function_exists( 'tcsln_fs' ) ) {
    // Create a helper function for easy SDK access.
    function tcsln_fs()
    {
        global  $tcsln_fs ;
        
        if ( !isset( $tcsln_fs ) ) {
            // Activate multisite network integration.
            if ( !defined( 'WP_FS__PRODUCT_3176_MULTISITE' ) ) {
                define( 'WP_FS__PRODUCT_3176_MULTISITE', true );
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
            
            $tcsln_fs = fs_dynamic_init( array(
                'id'               => '3176',
                'slug'             => 'sendloop-newsletter',
                'premium_slug'     => 'sendloop-newsletter',
                'type'             => 'plugin',
                'public_key'       => 'pk_baabd7c2d9921eb624ef1fe3da744',
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
        
        return $tcsln_fs;
    }

}
function tcsln_fs_is_parent_active_and_loaded()
{
    // Check if the parent's init SDK method exists.
    return function_exists( 'tets_fs' );
}

function tcsln_fs_is_parent_active()
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

function tcsln_fs_init()
{
    
    if ( tcsln_fs_is_parent_active_and_loaded() ) {
        // Init Freemius.
        tcsln_fs();
        // Parent is active, add your init code here.
    } else {
        // Parent is inactive, add your error handling here.
    }

}


if ( tcsln_fs_is_parent_active_and_loaded() ) {
    // If parent already included, init add-on.
    tcsln_fs_init();
} else {
    
    if ( tcsln_fs_is_parent_active() ) {
        // Init add-on only after the parent is loaded.
        add_action( 'tets_fs_loaded', 'tcsln_fs_init' );
    } else {
        // Even though the parent is not activated, execute add-on for activation / uninstall hooks.
        tcsln_fs_init();
    }

}
