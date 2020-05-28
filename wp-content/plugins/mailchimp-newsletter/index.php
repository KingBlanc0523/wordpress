<?php

/*
 Plugin Name: Tickera Mailchimp
 Plugin URI: http://tickera.com/
 Description: Tickera MailChimp Newsletter add-on allows you to send marketing emails, automated messages, and targeted campaigns to your customers.
 Author: Tickera.com
 Author URI: http://tickera.com/
 Version: 1.1.8
 Text Domain: tc-mailchimp
 Domain Path: /languages/
 Copyright 2019 Tickera (http://tickera.com/)
*/
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
// Exit if accessed directly
if ( !class_exists( 'TC_Mailchimp' ) ) {
    class TC_Mailchimp
    {
        var  $plugin_name = 'mailchimp' ;
        var  $admin_name = 'Mailchimp' ;
        var  $version = '1.1.8' ;
        var  $title = 'Tickera Mailchimp' ;
        var  $name = 'tc' ;
        var  $dir_name = 'tickera-mailchimp' ;
        var  $location = 'plugins' ;
        var  $plugin_dir = '' ;
        var  $plugin_url = '' ;
        function init()
        {
            global  $tc ;
            //localize the plugin
            add_action( 'init', array( &$this, 'localization' ), 10 );
            $this->admin_name = __( 'Mailchimp', 'tc-mailchimp' );
            $this->public_name = __( 'Mailchimp', 'tc-mailchimp' );
        }
        
        //Plugin localization function
        function localization()
        {
            // Load up the localization file if we're using WordPress in a different language
            // Place it in this plugin's "languages" folder and name it "tc-[value in wp-config].mo"
            
            if ( $this->location == 'mu-plugins' ) {
                load_muplugin_textdomain( 'tc-mailchimp', 'languages/' );
            } else {
                
                if ( $this->location == 'subfolder-plugins' ) {
                    //load_plugin_textdomain( 'tc-mailchimp', false, $this->plugin_dir . '/languages/' );
                    load_plugin_textdomain( 'tc-mailchimp', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
                } else {
                    
                    if ( $this->location == 'plugins' ) {
                        load_plugin_textdomain( 'tc-mailchimp', false, 'languages/' );
                    } else {
                    }
                
                }
            
            }
            
            $temp_locales = explode( '_', get_locale() );
            $this->language = ( $temp_locales[0] ? $temp_locales[0] : 'en' );
        }
        
        function __construct()
        {
            $this->init();
            add_filter( 'tc_settings_new_menus', array( &$this, 'tc_settings_new_menus_additional' ) );
            add_action( 'tc_settings_menu_tickera_mailchimp', array( &$this, 'tc_settings_menu_tickera_mailchimp_show_page' ) );
            add_action(
                'tc_order_created',
                array( &$this, 'tc_order_created' ),
                10,
                5
            );
            add_action( 'tc_before_cart_submit', array( &$this, 'tc_add_mailchimp_field' ) );
            add_action( 'tc_cart_passed_successfully', array( &$this, 'tc_check_confirmation' ), 0 );
            add_action(
                'woocommerce_checkout_process',
                array( &$this, 'tc_check_confirmation' ),
                10,
                1
            );
            add_action(
                'woocommerce_new_order',
                array( &$this, 'tc_subscribe_to_mailchimp' ),
                20,
                1
            );
            add_action(
                'woocommerce_resume_order',
                array( &$this, 'tc_subscribe_to_mailchimp' ),
                20,
                1
            );
            add_action(
                'woocommerce_api_create_order',
                array( &$this, 'tc_subscribe_to_mailchimp' ),
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
                $options = array( 'tc_mailchimp_settings' );
                foreach ( $options as $option ) {
                    delete_option( $option );
                }
            }
        
        }
        
        function tc_check_confirmation()
        {
            global  $tc ;
            $tc->start_session();
            $tc_mailchimp_settings = get_option( 'tc_mailchimp_settings' );
            
            if ( isset( $tc_mailchimp_settings['enable_confirmation'] ) ) {
                $tc_mailchimp_confirmation = $tc_mailchimp_settings['enable_confirmation'];
            } else {
                $tc_mailchimp_confirmation = '';
            }
            
            //Check if confirmation is needed get_option etc.
            
            if ( $tc_mailchimp_confirmation ) {
                //NEED CONFIRMATION
                
                if ( isset( $_POST['tc-mailchimp-subscribe'] ) ) {
                    $_SESSION['tc_mailchimp_confirmed_subscription'] = true;
                } else {
                    $_SESSION['tc_mailchimp_confirmed_subscription'] = false;
                }
            
            } else {
            }
        
        }
        
        //tc_check_confirmation()
        function tc_settings_new_menus_additional( $settings_tabs )
        {
            $settings_tabs['tickera_mailchimp'] = __( 'Mailchimp', 'tc' );
            return $settings_tabs;
        }
        
        function tc_add_mailchimp_field()
        {
            $tc_mailchimp_settings = get_option( 'tc_mailchimp_settings' );
            
            if ( isset( $tc_mailchimp_settings['enable_confirmation'] ) ) {
                $tc_mailchimp_confirmation = $tc_mailchimp_settings['enable_confirmation'];
            } else {
                $tc_mailchimp_confirmation = '';
            }
            
            
            if ( $tc_mailchimp_confirmation && !isset( $tc_mailchimp_settings['disable_mailchimp'] ) ) {
                ?>
                <label>
                    <input type="checkbox" name="tc-mailchimp-subscribe" value="1" <?php 
                echo  apply_filters( 'tc_mailchimp_checked', '' ) ;
                ?> /><?php 
                _e( 'Sign-up to our newsletter.', 'tc-mailchimp' );
                ?>
                </label>
                <?php 
            }
            
            //if ( $tc_mailchimp_confirmation )
        }
        
        //function tc_add_mailchimp_field()
        //set mailchimp options
        function tc_settings_menu_tickera_mailchimp_show_page()
        {
            require_once $this->plugin_dir . 'includes/admin-pages/settings-tickera_mailchimp.php';
        }
        
        //function tc_settings_menu_tickera_mailchimp_show_page()
        //send data to mailchimp
        function tc_order_created(
            $order_id,
            $status,
            $cart_contents,
            $cart_info,
            $payment_info
        )
        {
            $tc_mailchimp_settings = get_option( 'tc_mailchimp_settings' );
            $tc_general_settings = get_option( 'tc_general_setting', false );
            if ( isset( $_SESSION['tc_mailchimp_confirmed_subscription'] ) && $_SESSION['tc_mailchimp_confirmed_subscription'] == false && $tc_mailchimp_settings['enable_confirmation'] == true ) {
                return;
            }
            
            if ( !empty($tc_mailchimp_settings['api_key']) && !empty($tc_mailchimp_settings['list_id']) && !isset( $tc_mailchimp_settings['disable_mailchimp'] ) ) {
                $buyer_first_name = $cart_info['buyer_data']['first_name_post_meta'];
                $buyer_last_name = $cart_info['buyer_data']['last_name_post_meta'];
                $buyer_email = $cart_info['buyer_data']['email_post_meta'];
                $api_key = $tc_mailchimp_settings['api_key'];
                $list_id = $tc_mailchimp_settings['list_id'];
                
                if ( isset( $tc_mailchimp_settings['double_optin'] ) ) {
                    $double_optin = TRUE;
                } else {
                    $double_optin = FALSE;
                }
                
                //check to see if mailchimp should send welcome e-mail
                
                if ( isset( $tc_mailchimp_settings['send_welcome'] ) ) {
                    $tc_send_welcome = TRUE;
                } else {
                    $tc_send_welcome = FALSE;
                }
                
                if ( !class_exists( '\\src\\Mailchimp' ) ) {
                    require_once $this->plugin_dir . 'includes/scripts/mailchimp-api/src/Mailchimp.php';
                }
                try {
                    $Mailchimp = new \src\Mailchimp( $api_key );
                    $tc_emails_to_collect = $tc_mailchimp_settings['tc_emails_to_collect'];
                    $tc_array_key = key( $cart_info['owner_data']['owner_email_post_meta'] );
                    
                    if ( $tc_general_settings['show_owner_email_field'] == 'yes' && ($tc_emails_to_collect == NULL || $tc_emails_to_collect == 'owner_emails' || $tc_emails_to_collect == 'both_emails') ) {
                        $i = 0;
                        foreach ( $cart_info['owner_data']['owner_email_post_meta'][$tc_array_key] as $tc_get_array_key => $tc_owner_mail ) {
                            $tc_ticket_id = $cart_info['owner_data']['ticket_type_id_post_meta'][$tc_array_key][$tc_get_array_key];
                            $tc_event_id = get_post_meta( $tc_ticket_id, 'event_name', true );
                            $tc_owner_first_name = $cart_info['owner_data']['first_name_post_meta'][$tc_array_key][$tc_get_array_key];
                            $tc_owner_last_name = $cart_info['owner_data']['last_name_post_meta'][$tc_array_key][$tc_get_array_key];
                            $merge_vars = apply_filters( 'tc_owner_merge_fields', array(
                                "FNAME"  => $tc_owner_first_name,
                                "LNAME"  => $tc_owner_last_name,
                                "TICKET" => get_the_title( $tc_event_id ) . ' - ' . get_the_title( $tc_ticket_id ),
                            ), $cart_info['owner_data'] );
                            $subscriber = $Mailchimp->lists->subscribe(
                                $list_id,
                                array(
                                'email' => $tc_owner_mail,
                            ),
                                $merge_vars,
                                'html',
                                $double_optin,
                                true,
                                true,
                                $tc_send_welcome
                            );
                            $i++;
                        }
                    }
                    
                    
                    if ( $tc_emails_to_collect == NULL || $tc_emails_to_collect == 'buyer_emails' || $tc_emails_to_collect == 'both_emails' ) {
                        $merge_vars = apply_filters( 'tc_buyer_merge_fields', array(
                            "FNAME" => $buyer_first_name,
                            "LNAME" => $buyer_last_name,
                        ), $cart_info['owner_data'] );
                        $subscriber = $Mailchimp->lists->subscribe(
                            $list_id,
                            array(
                            'email' => $buyer_email,
                        ),
                            $merge_vars,
                            'html',
                            $double_optin,
                            true,
                            true,
                            $tc_send_welcome
                        );
                    }
                    
                    if ( !empty($subscriber['leid']) ) {
                        // Success
                    }
                } catch ( Exception $e ) {
                }
            }
        
        }
        
        //function tc_order_created( $order_id, $status, $cart_contents, $cart_info, $payment_info )
        function tc_subscribe_to_mailchimp( $order_id )
        {
            global  $woocommerce ;
            $tc_mailchimp_settings = get_option( 'tc_mailchimp_settings' );
            if ( isset( $_SESSION['tc_mailchimp_confirmed_subscription'] ) && $_SESSION['tc_mailchimp_confirmed_subscription'] == false && $tc_mailchimp_settings['enable_confirmation'] == true ) {
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
            $ticket_buying_subscribe = $tc_mailchimp_settings['users_buying_tickets'];
            $tc_ticket_instance = $tc_tickets_instances[0]->post_type;
            if ( !empty($tc_tickets_instances) ) {
                
                if ( $ticket_buying_subscribe == 1 && $tc_ticket_instance == 'tc_tickets_instances' ) {
                    $this->tc_woo_bridge_mailchimp( $buyer_first_name, $buyer_last_name, $buyer_email );
                } else {
                    if ( !isset( $ticket_buying_subscribe ) ) {
                        $this->tc_woo_bridge_mailchimp( $buyer_first_name, $buyer_last_name, $buyer_email );
                    }
                }
            
            }
        }
        
        //function tc_subscribe_to_mailchimp($order_id)
        function tc_woo_bridge_mailchimp( $buyer_first_name, $buyer_last_name, $buyer_email )
        {
            $tc_mailchimp_settings = get_option( 'tc_mailchimp_settings' );
            $tc_general_settings = get_option( 'tc_general_setting', false );
            
            if ( !empty($tc_mailchimp_settings['api_key']) && !empty($tc_mailchimp_settings['list_id']) && !isset( $tc_mailchimp_settings['disable_mailchimp'] ) ) {
                $api_key = $tc_mailchimp_settings['api_key'];
                $list_id = $tc_mailchimp_settings['list_id'];
                
                if ( isset( $tc_mailchimp_settings['double_optin'] ) ) {
                    $double_optin = TRUE;
                } else {
                    $double_optin = FALSE;
                }
                
                //check to see if mailchimp should send welcome e-mail
                
                if ( isset( $tc_mailchimp_settings['send_welcome'] ) ) {
                    $tc_send_welcome = TRUE;
                } else {
                    $tc_send_welcome = FALSE;
                }
                
                $tc_array_key = key( $_POST['owner_data_owner_email_post_meta'] );
                $save_post = $_POST;
                if ( !class_exists( '\\src\\Mailchimp' ) ) {
                    include $this->plugin_dir . 'includes/scripts/mailchimp-api/src/Mailchimp.php';
                }
                try {
                    $Mailchimp = new \src\Mailchimp( $api_key );
                    $tc_emails_to_collect = $tc_mailchimp_settings['tc_emails_to_collect'];
                    
                    if ( $tc_general_settings['show_owner_email_field'] == 'yes' && ($tc_emails_to_collect == NULL || $tc_emails_to_collect == 'owner_emails' || $tc_emails_to_collect == 'both_emails') ) {
                        $i = 0;
                        foreach ( $save_post['owner_data_owner_email_post_meta'][$tc_array_key] as $tc_get_array_key => $tc_owner_mail ) {
                            $tc_ticket_id = $save_post['owner_data_ticket_type_id_post_meta'][$tc_array_key][$tc_get_array_key];
                            $tc_event_id = get_post_meta( $tc_ticket_id, '_event_name', true );
                            $tc_owner_first_name = $save_post['owner_data_first_name_post_meta'][$tc_array_key][$tc_get_array_key];
                            $tc_owner_last_name = $save_post['owner_data_last_name_post_meta'][$tc_array_key][$tc_get_array_key];
                            $merge_vars = apply_filters( 'tc_owner_merge_fields', array(
                                "FNAME"  => $tc_owner_first_name,
                                "LNAME"  => $tc_owner_last_name,
                                "TICKET" => get_the_title( $tc_event_id ) . ' - ' . get_the_title( $tc_ticket_id ),
                            ), $cart_info['owner_data'] );
                            $subscriber = $Mailchimp->lists->subscribe(
                                $list_id,
                                array(
                                'email' => $tc_owner_mail,
                            ),
                                $merge_vars,
                                'html',
                                $double_optin,
                                true,
                                true,
                                $tc_send_welcome
                            );
                            $i++;
                        }
                    }
                    
                    
                    if ( $tc_emails_to_collect == NULL || $tc_emails_to_collect == 'buyer_emails' || $tc_emails_to_collect == 'both_emails' ) {
                        $merge_vars = apply_filters( 'tc_buyer_merge_fields', array(
                            "FNAME" => $buyer_first_name,
                            "LNAME" => $buyer_last_name,
                        ), $cart_info['owner_data'] );
                        $subscriber = $Mailchimp->lists->subscribe(
                            $list_id,
                            array(
                            'email' => $buyer_email,
                        ),
                            $merge_vars,
                            'html',
                            $double_optin,
                            true,
                            true,
                            $tc_send_welcome
                        );
                    }
                    
                    if ( !empty($subscriber['leid']) ) {
                        // Success
                    }
                } catch ( Exception $e ) {
                }
            }
        
        }
    
    }
}
if ( !function_exists( 'is_plugin_active_for_network' ) ) {
    require_once ABSPATH . '/wp-admin/includes/plugin.php';
}

if ( is_multisite() && is_plugin_active_for_network( plugin_basename( __FILE__ ) ) ) {
    function tc_mailchimp_load()
    {
        global  $tc_mailchimp ;
        $tc_mailchimp = new TC_Mailchimp();
    }
    
    add_action( 'tets_fs_loaded', 'tc_mailchimp_load' );
} else {
    $tc_mailchimp = new TC_Mailchimp();
}

//CHECKING MAILCHIMP API
//ajax part
add_action( 'admin_enqueue_scripts', 'tc_check_mailchimp' );
function tc_check_mailchimp()
{
    wp_enqueue_script( 'mailchimp-js', plugin_dir_url( __FILE__ ) . '/includes/scripts/javascript.js' );
}

//tc_check_mailchimp
//php part called by the ajax for checking mailchimp
add_action( 'wp_ajax_ajax_mailchimp_check', 'check_mailchimp_check' );
function check_mailchimp_check()
{
    $tc_api_key = $_POST['tc_api_key'];
    $tc_list_id = $_POST['tc_list_id'];
    include plugin_dir_path( __FILE__ ) . 'includes/scripts/mailchimp-api/src/Mailchimp.php';
    try {
        $Mailchimp = new \src\Mailchimp( $tc_api_key );
        $subscriber = $Mailchimp->lists->subscribe( $tc_list_id, array(
            'email' => get_option( 'admin_email' ),
        ) );
        echo  "Works Fine!" ;
    } catch ( Exception $e ) {
        echo  '<span style="color:red;">' . $e->getMessage() . '</span>' ;
    }
    wp_die();
    // this is required to terminate immediately and return a proper response
}

if ( !function_exists( 'tcmn_fs' ) ) {
    // Create a helper function for easy SDK access.
    function tcmn_fs()
    {
        global  $tcmn_fs ;
        
        if ( !isset( $tcmn_fs ) ) {
            // Activate multisite network integration.
            if ( !defined( 'WP_FS__PRODUCT_3175_MULTISITE' ) ) {
                define( 'WP_FS__PRODUCT_3175_MULTISITE', true );
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
            
            $tcmn_fs = fs_dynamic_init( array(
                'id'               => '3175',
                'slug'             => 'mailchimp-newsletter',
                'premium_slug'     => 'mailchimp-newsletter',
                'type'             => 'plugin',
                'public_key'       => 'pk_eff989814cc25fcd6cafabe8c455b',
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
        
        return $tcmn_fs;
    }

}
function tcmn_fs_is_parent_active_and_loaded()
{
    // Check if the parent's init SDK method exists.
    return function_exists( 'tets_fs' );
}

function tcmn_fs_is_parent_active()
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

function tcmn_fs_init()
{
    
    if ( tcmn_fs_is_parent_active_and_loaded() ) {
        // Init Freemius.
        tcmn_fs();
        // Parent is active, add your init code here.
    } else {
        // Parent is inactive, add your error handling here.
    }

}


if ( tcmn_fs_is_parent_active_and_loaded() ) {
    // If parent already included, init add-on.
    tcmn_fs_init();
} else {
    
    if ( tcmn_fs_is_parent_active() ) {
        // Init add-on only after the parent is loaded.
        add_action( 'tets_fs_loaded', 'tcmn_fs_init' );
    } else {
        // Even though the parent is not activated, execute add-on for activation / uninstall hooks.
        tcmn_fs_init();
    }

}
