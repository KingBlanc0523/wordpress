<?php

/*
 Plugin Name: Mollie for Tickera
 Plugin URI: http://tickera.com/
 Description: Accept iDeal, Credit Card, Bancontact / Mister Cash, SOFORT Banking, Overbooking, Bitcoin, PayPal, paysafecard and AcceptEmail payment via Mollie.
 Author: Tickera.com
 Author URI: http://tickera.com/
 Version: 1.3
 Text Domain: tc-mollie
 Domain Path: /languages/
 Copyright 2019 Tickera (http://tickera.com/)
*/
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
// Exit if accessed directly
add_action( 'tc_load_gateway_plugins', 'register_mollie_gateway' );
function register_mollie_gateway()
{
    class TC_Gateway_Mollie extends TC_Gateway_API
    {
        var  $plugin_name = 'mollie' ;
        var  $admin_name = 'Mollie' ;
        var  $public_name = '' ;
        var  $method_img_url = '' ;
        var  $method_button_img_url = '' ;
        var  $ipn_url ;
        var  $currencies = array() ;
        var  $skip_payment_screen = true ;
        var  $dir_name = 'mollie-payment-gateway' ;
        var  $location = 'plugins' ;
        var  $plugin_dir = '' ;
        var  $plugin_url = '' ;
        //Support for older payment gateway API
        function on_creation()
        {
            $this->init();
        }
        
        function init()
        {
            global  $tc ;
            $this->init_vars();
            //localize the plugin
            add_action( 'init', array( &$this, 'localization' ), 10 );
            $this->admin_name = __( 'Mollie', 'tc-mollie' );
            $this->public_name = __( 'Mollie', 'tc-mollie' );
            $this->method_img_url = apply_filters( 'tc_gateway_method_img_url', plugin_dir_url( __FILE__ ) . 'assets/images/mollie.png', $this->plugin_name );
            $this->admin_img_url = apply_filters( 'tc_gateway_admin_img_url', plugin_dir_url( __FILE__ ) . 'assets/images/small-mollie.png', $this->plugin_name );
            $this->api_key = $this->get_option( 'api_key' );
            $this->public_name = $this->get_option( 'public_name', $this->public_name );
            $this->currency = $this->get_option( 'currency', 'EUR' );
            $currencies = array(
                "EUR" => __( 'EUR (All payment methods)', 'tc' ),
                "AUD" => __( 'AUD - Australian Dollar (PayPal, credit card)', 'tc' ),
                "BGN" => __( 'BGN - Bulgarian lev (Credit card)', 'tc' ),
                "BRL" => __( 'BRL - Brazilian real (PayPal)', 'tc' ),
                "CAD" => __( 'CAD - Canadian dollar (PayPal, credit card)', 'tc' ),
                "CHF" => __( 'CHF - Swiss franc (PayPal, credit card)', 'tc' ),
                "CZK" => __( 'CZK - Czech koruna (PayPal, credit card)', 'tc' ),
                "DKK" => __( 'DKK - Danish krone (PayPal, credit card)', 'tc' ),
                "GBP" => __( 'GBP - British pound (PayPal, credit card)', 'tc' ),
                "HKD" => __( 'HKD - Hong Kong dollar (PayPal, credit card)', 'tc' ),
                "HRK" => __( 'HRK - Croatian kuna (Credit card)', 'tc' ),
                "HUF" => __( 'HUF - Hungarian forint (PayPal, credit card)', 'tc' ),
                "ILS" => __( 'ILS - Israeli shekel (PayPal, credit card)', 'tc' ),
                "ISK" => __( 'ISK - Icelandic krona (Credit card)', 'tc' ),
                "JPY" => __( 'JPY - Japanese yen (PayPal, credit card)', 'tc' ),
                "MXN" => __( 'MXN - Mexican peso (PayPal)', 'tc' ),
                "MYR" => __( 'MYR - Malaysian ringgit (PayPal)', 'tc' ),
                "NOK" => __( 'NOK - Norwegian krone (PayPal, credit card)', 'tc' ),
                "NZD" => __( 'NZD - New Zealand dollar (PayPal)', 'tc' ),
                "PHP" => __( 'PHP - Philippine piso (PayPal)', 'tc' ),
                "PLN" => __( 'PLN - Polish zloty (PayPal, credit card)', 'tc' ),
                "RON" => __( 'RON - Romanian leu (Credit card)', 'tc' ),
                "RUB" => __( 'RUB - Russian ruble (PayPal)', 'tc' ),
                "SEK" => __( 'SEK - Swedish krona (PayPal, credit card)', 'tc' ),
                "SGD" => __( 'SGD - Singapore dollar (PayPal)', 'tc' ),
                "THB" => __( 'THB - Thai baht (PayPal)', 'tc' ),
                "TWD" => __( 'TWD - New Taiwan dollar (PayPal)', 'tc' ),
                "USD" => __( 'USD - US dollar (PayPal, credit card)', 'tc' ),
            );
            $this->currencies = apply_filters( 'tc_mollie_currencies', $currencies );
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
                        wp_die( sprintf( __( 'There was an issue determining where %s is installed. Please reinstall it.', 'tc-mollie' ), $this->title ) );
                    }
                
                }
            
            }
        
        }
        
        //Plugin localization function
        function localization()
        {
            // Load up the localization file if we're using WordPress in a different language
            // Place it in this plugin's "languages" folder and name it "tc-[value in wp-config].mo"
            
            if ( $this->location == 'mu-plugins' ) {
                load_muplugin_textdomain( 'tc-mollie', 'languages/' );
            } else {
                
                if ( $this->location == 'subfolder-plugins' ) {
                    //load_plugin_textdomain( 'tc-mollie', false, $this->plugin_dir . '/languages/' );
                    load_plugin_textdomain( 'tc-mollie', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
                } else {
                    
                    if ( $this->location == 'plugins' ) {
                        load_plugin_textdomain( 'tc-mollie', false, 'languages/' );
                    } else {
                    }
                
                }
            
            }
            
            $temp_locales = explode( '_', get_locale() );
            $this->language = ( $temp_locales[0] ? $temp_locales[0] : 'en' );
        }
        
        function init_mollie()
        {
            require_once 'mollie-api-php/vendor/autoload.php';
            $this->mollie = new \Mollie\Api\MollieApiClient();
            $this->mollie->setApiKey( $this->api_key );
        }
        
        function process_payment( $cart )
        {
            global  $tc ;
            $this->maybe_start_session();
            $this->save_cart_info();
            $this->init_mollie();
            $order_id = $tc->generate_order_id();
            $duedate = strtotime( apply_filters( 'tc_mollie_duedate_days', '+30 days' ), time() );
            try {
                $payment = $this->mollie->payments->create( [
                    "amount"      => [
                    "currency" => $this->currency,
                    "value"    => number_format(
                    (double) $this->total(),
                    2,
                    '.',
                    ''
                ),
                ],
                    "description" => $this->cart_items(),
                    "redirectUrl" => apply_filters( 'tc_mollie_redirect_url', $tc->get_confirmation_slug( true, $order_id ), $order_id ),
                    "webhookUrl"  => $tc->get_confirmation_slug( true, $order_id ),
                    "metadata"    => array(
                    "order_id" => $order_id,
                ),
                    "dueDate"     => date( 'Y-m-d', $duedate ),
                ] );
                $payment_url = $payment->_links->checkout->href;
                $payment_info = array();
                $payment_info['transaction_id'] = $payment->id;
                $payment_info = $this->save_payment_info( $payment_info );
                $tc->create_order(
                    $order_id,
                    $this->cart_contents(),
                    $this->cart_info(),
                    $payment_info,
                    false
                );
                wp_redirect( $payment_url );
                tc_js_redirect( $payment_url );
                exit;
            } catch ( \Mollie\Api\Exceptions\ApiException $e ) {
                $_SESSION['tc_gateway_error'] = __( 'API call failed: ', 'tc-mollie' ) . htmlspecialchars( $e->getMessage() );
                wp_redirect( $tc->get_payment_slug( true ) );
                tc_js_redirect( $tc->get_payment_slug( true ) );
                exit;
            }
        }
        
        function order_confirmation( $order, $payment_info = '', $cart_info = '' )
        {
            global  $tc ;
            $received_order = $order;
            $order = tc_get_order_id_by_name( $order );
            $order_object = new TC_Order( $order->ID );
            
            if ( isset( $_REQUEST['id'] ) ) {
                $transaction_id = $_REQUEST['id'];
            } else {
                $transaction_id = $order_object->details->tc_payment_info['transaction_id'];
            }
            
            
            if ( isset( $transaction_id ) ) {
                $this->init_mollie();
                $payment = $this->mollie->payments->get( $transaction_id );
                $order_id = $payment->metadata->order_id;
                
                if ( $payment->isPaid() == TRUE ) {
                    $tc->update_order_payment_status( $order->ID, true );
                } elseif ( $payment->isOpen() == FALSE ) {
                    //do nothing, it's not paid yet
                }
            
            }
        
        }
        
        function gateway_admin_settings( $settings, $visible )
        {
            global  $tc ;
            ?>
            <div id="<?php 
            echo  $this->plugin_name ;
            ?>" class="postbox" <?php 
            echo  ( !$visible ? 'style="display:none;"' : '' ) ;
            ?>>
                <h3 class='handle'><span><?php 
            printf( __( '%s Settings', 'tc-mollie' ), $this->admin_name );
            ?></span></h3>
                <div class="inside">
                    <span class="description">
                        <?php 
            _e( 'Mollie provides a fully PCI Compliant and secure way to collect payments via iDeal, Credit Card, Bancontact / Mister Cash, SOFORT Banking, Overbooking, Bitcoin, PayPal, paysafecard and AcceptEmail.', 'tc-mollie' );
            if ( in_array( $_SERVER['REMOTE_ADDR'], array( '127.0.0.1', '::1' ) ) ) {
                printf( '<div style="border-left: 4px solid #ffb900;; margin: 5px 0 15px; background: #fff; left: 4px solid #fff; box-shadow: 0 1px 1px 0 rgba(0,0,0,.1); padding: 5px 12px;">%s</div>', __( 'NOTE: Mollie cannot be tested in the localhost environment because it won\'t unable to reach webhook URL.', 'tc' ) );
            }
            ?>
                    </span>

                    <?php 
            $fields = array(
                'api_key'     => array(
                'title' => __( 'Mollie API Key', 'tc-mollie' ),
                'type'  => 'text',
            ),
                'public_name' => array(
                'title' => __( 'Payment Method Name (shown on front)', 'tc-mollie' ),
                'type'  => 'text',
            ),
                'currency'    => array(
                'title'       => __( 'Currency', 'tc' ),
                'type'        => 'select',
                'options'     => $this->currencies,
                'default'     => 'EUR',
                'description' => __( 'Make sure that currency selected is the same as store currency selected in the General Settings', 'tc' ),
            ),
            );
            $form = new TC_Form_Fields_API(
                $fields,
                'tc',
                'gateways',
                $this->plugin_name
            );
            ?>
                    <table class="form-table">
                        <?php 
            $form->admin_options();
            ?>
                    </table>
                </div>
            </div>
            <?php 
        }
        
        function process_gateway_settings( $settings )
        {
            return $settings;
        }
        
        function ipn()
        {
            global  $tc ;
            $settings = get_option( 'tc_settings' );
        }
    
    }
    tc_register_gateway_plugin( 'TC_Gateway_Mollie', 'mollie', __( 'Mollie', 'tc-mollie' ) );
}

if ( !function_exists( 'mpg_fs' ) ) {
    // Create a helper function for easy SDK access.
    function mpg_fs()
    {
        global  $mpg_fs ;
        
        if ( !isset( $mpg_fs ) ) {
            // Activate multisite network integration.
            if ( !defined( 'WP_FS__PRODUCT_3172_MULTISITE' ) ) {
                define( 'WP_FS__PRODUCT_3172_MULTISITE', true );
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
            
            $mpg_fs = fs_dynamic_init( array(
                'id'               => '3172',
                'slug'             => 'mollie-payment-gateway',
                'premium_slug'     => 'mollie-payment-gateway',
                'type'             => 'plugin',
                'public_key'       => 'pk_e3581fda159a13888e4269b0621b6',
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
        
        return $mpg_fs;
    }

}
function mpg_fs_is_parent_active_and_loaded()
{
    // Check if the parent's init SDK method exists.
    return function_exists( 'tets_fs' );
}

function mpg_fs_is_parent_active()
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

function mpg_fs_init()
{
    
    if ( mpg_fs_is_parent_active_and_loaded() ) {
        // Init Freemius.
        mpg_fs();
        // Parent is active, add your init code here.
    } else {
        // Parent is inactive, add your error handling here.
    }

}


if ( mpg_fs_is_parent_active_and_loaded() ) {
    // If parent already included, init add-on.
    mpg_fs_init();
} else {
    
    if ( mpg_fs_is_parent_active() ) {
        // Init add-on only after the parent is loaded.
        add_action( 'tets_fs_loaded', 'mpg_fs_init' );
    } else {
        // Even though the parent is not activated, execute add-on for activation / uninstall hooks.
        mpg_fs_init();
    }

}
