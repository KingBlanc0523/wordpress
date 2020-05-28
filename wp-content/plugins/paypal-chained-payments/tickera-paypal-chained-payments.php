<?php

/*
 Plugin Name: PayPal Chained Payment for Tickera
 Plugin URI: http://tickera.com/
 Description: Collect fees from every sale on your multisite network with PayPal chained payments
 Author: Tickera.com
 Author URI: http://tickera.com/
 Version: 1.1.3.6
 TextDomain: tc
 Domain Path: /languages/
 Network: true
 Copyright 2019 Tickera (http://tickera.com/)
*/
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
// Exit if accessed directly
if ( !is_multisite() ) {
    add_action( 'admin_notices', 'tc_paypal_chained_admin_notice' );
}
function tc_paypal_chained_admin_notice()
{
    ?>
    <div class="notice notice-error is-dismissible">
        <p><?php 
    _e( 'PayPal Chained Payment add-on must be installed on a WordPress multisite and should be network activated, otherwise it won\'t work as intended.', 'tc' );
    ?></p>
    </div>
    <?php 
}

add_action( 'init', 'tc_paypal_chained_payments_gateway_init' );
add_filter(
    'tc_gateway_plugins',
    'tc_deregister_paypal_gateway_plugins',
    10,
    2
);
function tc_deregister_paypal_gateway_plugins( $gateways, $gateways_originals )
{
    $gateway_index = 0;
    foreach ( $gateways as $gateway ) {
        if ( preg_match( '/paypal_standard/', $gateway ) ) {
            unset( $gateways[$gateway_index] );
        }
        $gateway_index++;
    }
    return $gateways;
}

function tc_paypal_chained_payments_gateway_init()
{
    load_plugin_textdomain( 'tc', false, basename( dirname( __FILE__ ) ) . '/languages' );
}

add_action( 'tc_load_gateway_plugins', 'register_paypal_chained_payments_gateway' );
function register_paypal_chained_payments_gateway()
{
    class TC_Gateway_PayPal_Chained_Payments extends TC_Gateway_API
    {
        var  $plugin_name = 'paypal-chained-payments' ;
        var  $admin_name = '' ;
        var  $public_name = '' ;
        var  $method_img_url = '' ;
        var  $admin_img_url = '' ;
        var  $force_ssl ;
        var  $ipn_url ;
        var  $publishable_key, $private_key, $currency ;
        var  $currencies = array() ;
        var  $automatically_activated = false ;
        var  $skip_payment_screen = false ;
        var  $version = '1.1.3.6' ;
        var  $title = 'PayPal Chained Payments' ;
        var  $name = 'tc_paypal_chained_payments' ;
        var  $dir_name = 'paypal-chained-payments' ;
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
            $settings = get_option( 'tc_settings' );
            $network_settings = get_site_option( 'tc_network_settings' );
            $this->admin_name = __( 'PayPal', 'tc' );
            $this->public_name = __( 'PayPal', 'tc' );
            $this->method_img_url = apply_filters( 'tc_gateway_method_img_url', $tc->plugin_url . 'images/gateways/paypal-standard.png', $this->plugin_name );
            
            if ( is_multisite() && is_network_admin() ) {
                $this->admin_img_url = apply_filters( 'tc_gateway_network_admin_img_url', $this->plugin_url . 'images/small-paypal-chained.png', $this->plugin_name );
            } else {
                $this->admin_img_url = apply_filters( 'tc_gateway_admin_img_url', $tc->plugin_url . 'images/gateways/small-paypal-standard.png', $this->plugin_name );
            }
            
            $this->currency = $this->get_option( 'currency', 'USD' );
            $this->locale = $this->get_option( 'locale', 'US' );
            $this->locales = array(
                'AR' => __( 'Argentina', 'tc' ),
                'AU' => __( 'Australia', 'tc' ),
                'AT' => __( 'Austria', 'tc' ),
                'BE' => __( 'Belgium', 'tc' ),
                'BR' => __( 'Brazil', 'tc' ),
                'CA' => __( 'Canada', 'tc' ),
                'CN' => __( 'China', 'tc' ),
                'FI' => __( 'Finland', 'tc' ),
                'FR' => __( 'France', 'tc' ),
                'DE' => __( 'Germany', 'tc' ),
                'HK' => __( 'Hong Kong', 'tc' ),
                'IE' => __( 'Ireland', 'tc' ),
                'IL' => __( 'Israel', 'tc' ),
                'IT' => __( 'Italy', 'tc' ),
                'JP' => __( 'Japan', 'tc' ),
                'MX' => __( 'Mexico', 'tc' ),
                'NL' => __( 'Netherlands', 'tc' ),
                'NZ' => __( 'New Zealand', 'tc' ),
                'PL' => __( 'Poland', 'tc' ),
                'RU' => __( 'Russia', 'tc' ),
                'SG' => __( 'Singapore', 'tc' ),
                'ES' => __( 'Spain', 'tc' ),
                'SE' => __( 'Sweden', 'tc' ),
                'CH' => __( 'Switzerland', 'tc' ),
                'TR' => __( 'Turkey', 'tc' ),
                'GB' => __( 'United Kingdom', 'tc' ),
                'US' => __( 'United States', 'tc' ),
            );
            $this->currencies = array(
                "AUD" => __( 'AUD - Australian Dollar', 'tc' ),
                "BRL" => __( 'BRL - Brazilian Real', 'tc' ),
                "CAD" => __( 'CAD - Canadian Dollar', 'tc' ),
                "CZK" => __( 'CZK - Czech Koruna', 'tc' ),
                "DKK" => __( 'DKK - Danish Krone', 'tc' ),
                "EUR" => __( 'EUR - Euro', 'tc' ),
                "HKD" => __( 'HKD - Hong Kong Dollar', 'tc' ),
                "HUF" => __( 'HUF - Hungarian Forint', 'tc' ),
                "ILS" => __( 'ILS - Israeli New Shekel', 'tc' ),
                "JPY" => __( 'JPY - Japanese Yen', 'tc' ),
                "MYR" => __( 'MYR - Malaysian Ringgit', 'tc' ),
                "MXN" => __( 'MXN - Mexican Peso', 'tc' ),
                "NOK" => __( 'NOK - Norwegian Krone', 'tc' ),
                "NZD" => __( 'NZD - New Zealand Dollar', 'tc' ),
                "PHP" => __( 'PHP - Philippine Peso', 'tc' ),
                "PLN" => __( 'PLN - Polish Zloty', 'tc' ),
                "GBP" => __( 'GBP - Pound Sterling', 'tc' ),
                "RUB" => __( 'RUB - Russian Ruble', 'tc' ),
                "SGD" => __( 'SGD - Singapore Dollar', 'tc' ),
                "SEK" => __( 'SEK - Swedish Krona', 'tc' ),
                "CHF" => __( 'CHF - Swiss Franc', 'tc' ),
                "TWD" => __( 'TWD - Taiwan New Dollar', 'tc' ),
                "TRY" => __( 'TRY - Turkish Lira', 'tc' ),
                "USD" => __( 'USD - U.S. Dollar', 'tc' ),
                "THB" => __( 'THB - Thai Baht', 'tc' ),
            );
            $mode = $this->get_option( 'mode', 'sandbox' );
            
            if ( $mode == 'sandbox' ) {
                $this->API_Endpoint = "https://svcs.sandbox.paypal.com/AdaptivePayments/";
                $this->paypalURL = "https://www.sandbox.paypal.com/webscr?cmd=_ap-payment&paykey=";
                $this->API_Username = ( isset( $network_settings['gateways'][$this->plugin_name]['api_user_sandbox'] ) ? $network_settings['gateways'][$this->plugin_name]['api_user_sandbox'] : '' );
                $this->API_Password = ( isset( $network_settings['gateways'][$this->plugin_name]['api_pass_sandbox'] ) ? $network_settings['gateways'][$this->plugin_name]['api_pass_sandbox'] : '' );
                $this->API_Signature = ( isset( $network_settings['gateways'][$this->plugin_name]['api_sig_sandbox'] ) ? $network_settings['gateways'][$this->plugin_name]['api_sig_sandbox'] : '' );
                $this->appId = 'APP-80W284485P519543T';
                //test application id for sandbox
            } else {
                $this->API_Endpoint = "https://svcs.paypal.com/AdaptivePayments/";
                $this->paypalURL = "https://www.paypal.com/webscr?cmd=_ap-payment&paykey=";
                $this->API_Username = ( isset( $network_settings['gateways'][$this->plugin_name]['api_user'] ) ? $network_settings['gateways'][$this->plugin_name]['api_user'] : '' );
                $this->API_Password = ( isset( $network_settings['gateways'][$this->plugin_name]['api_pass'] ) ? $network_settings['gateways'][$this->plugin_name]['api_pass'] : '' );
                $this->API_Signature = ( isset( $network_settings['gateways'][$this->plugin_name]['api_sig'] ) ? $network_settings['gateways'][$this->plugin_name]['api_sig'] : '' );
                $this->appId = ( isset( $network_settings['gateways'][$this->plugin_name]['app_id'] ) ? $network_settings['gateways'][$this->plugin_name]['app_id'] : '' );
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
                        wp_die( sprintf( __( 'There was an issue determining where %s is installed. Please reinstall it.', 'tc' ), $this->title ) );
                    }
                
                }
            
            }
        
        }
        
        function payment_form( $cart )
        {
            global  $tc ;
            
            if ( isset( $_GET[$this->cancel_slug] ) ) {
                $_SESSION['tc_gateway_error'] = __( 'Your transaction has been canceled.', 'tc' );
                wp_redirect( $tc->get_payment_slug( true ) );
                tc_js_redirect( $tc->get_payment_slug( true ) );
                exit;
            }
        
        }
        
        function order_confirmation( $order, $payment_info = '', $cart_info = '' )
        {
            global  $tc ;
            $this->ipn();
        }
        
        function gateway_network_admin_settings( $settings, $visible )
        {
            global  $tc ;
            ?>
            <div id="<?php 
            echo  $this->plugin_name ;
            ?>" class="postbox" <?php 
            echo  ( !$visible ? 'style="display:none;"' : '' ) ;
            ?>>
                <h3><span><?php 
            _e( 'PayPal Chained Payments', 'tc' );
            ?></span></h3>
                <div class="inside">
                    <p class="description"><?php 
            _e( "PayPal Chained Payments allows you to charge commision per sale for each subsite in your network. NOTE: you should disable other PayPal payment gateways when use PayPal Chained Payments.", 'tc' );
            ?></p>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php 
            _e( 'Percentage', 'tc' );
            ?></th>
                            <td>
                                <span class="description"><?php 
            _e( 'Enter a percentage of sales you want to collect. Decimals allowed.', 'tc' );
            ?></span>
                                <p>
                                    <label><?php 
            _e( 'Percentage', 'tc' );
            ?><br />
                                        <input value="<?php 
            echo  esc_attr( ( isset( $settings['gateways'][$this->plugin_name]['percentage'] ) ? $settings['gateways'][$this->plugin_name]['percentage'] : '0' ) ) ;
            ?>" size="70" name="tc[gateways][<?php 
            echo  $this->plugin_name ;
            ?>][percentage]" type="text" />
                                    </label>
                                </p>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row"><?php 
            _e( 'E-mail', 'tc' );
            ?></th>
                            <td>
                                <span class="description"><?php 
            _e( 'Your PayPal email address or business ID you want to recieve fees at.', 'tc' );
            ?></span>
                                <p>
                                    <label><?php 
            _e( 'PayPal E-mail', 'tc' );
            ?><br />
                                        <input value="<?php 
            echo  esc_attr( ( isset( $settings['gateways'][$this->plugin_name]['network_email'] ) ? $settings['gateways'][$this->plugin_name]['network_email'] : '' ) ) ;
            ?>" size="70" name="tc[gateways][<?php 
            echo  $this->plugin_name ;
            ?>][network_email]" type="text" />
                                    </label>
                                </p>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row"><?php 
            _e( 'Sandbox Credentials', 'tc' );
            ?></th>
                            <td>
                                <p>
                                    <label><?php 
            _e( 'API Username', 'tc' );
            ?><br />
                                        <input value="<?php 
            echo  esc_attr( ( isset( $settings['gateways'][$this->plugin_name]['api_user_sandbox'] ) ? $settings['gateways'][$this->plugin_name]['api_user_sandbox'] : '' ) ) ;
            ?>" size="70" name="tc[gateways][<?php 
            echo  $this->plugin_name ;
            ?>][api_user_sandbox]" type="text" />
                                    </label>
                                </p>

                                <p>
                                    <label><?php 
            _e( 'API Password', 'tc' );
            ?><br />
                                        <input value="<?php 
            echo  esc_attr( ( isset( $settings['gateways'][$this->plugin_name]['api_pass_sandbox'] ) ? $settings['gateways'][$this->plugin_name]['api_pass_sandbox'] : '' ) ) ;
            ?>" size="70" name="tc[gateways][<?php 
            echo  $this->plugin_name ;
            ?>][api_pass_sandbox]" type="text" />
                                    </label>
                                </p>

                                <p>
                                    <label><?php 
            _e( 'API Signature', 'tc' );
            ?><br />
                                        <input value="<?php 
            echo  esc_attr( ( isset( $settings['gateways'][$this->plugin_name]['api_sig_sandbox'] ) ? $settings['gateways'][$this->plugin_name]['api_sig_sandbox'] : '' ) ) ;
            ?>" size="70" name="tc[gateways][<?php 
            echo  $this->plugin_name ;
            ?>][api_sig_sandbox]" type="text" />
                                    </label>
                                </p>

                            </td>
                        </tr>

                        <tr>
                            <th scope="row"><?php 
            _e( 'Live Credentials', 'tc' );
            ?></th>
                            <td>
                                <p>
                                    <label><?php 
            _e( 'API Username', 'tc' );
            ?><br />
                                        <input value="<?php 
            echo  esc_attr( ( isset( $settings['gateways'][$this->plugin_name]['api_user'] ) ? $settings['gateways'][$this->plugin_name]['api_user'] : '' ) ) ;
            ?>" size="70" name="tc[gateways][<?php 
            echo  $this->plugin_name ;
            ?>][api_user]" type="text" />
                                    </label>
                                </p>

                                <p>
                                    <label><?php 
            _e( 'API Password', 'tc' );
            ?><br />
                                        <input value="<?php 
            echo  esc_attr( ( isset( $settings['gateways'][$this->plugin_name]['api_pass'] ) ? $settings['gateways'][$this->plugin_name]['api_pass'] : '' ) ) ;
            ?>" size="70" name="tc[gateways][<?php 
            echo  $this->plugin_name ;
            ?>][api_pass]" type="text" />
                                    </label>
                                </p>

                                <p>
                                    <label><?php 
            _e( 'API Signature', 'tc' );
            ?><br />
                                        <input value="<?php 
            echo  esc_attr( ( isset( $settings['gateways'][$this->plugin_name]['api_sig'] ) ? $settings['gateways'][$this->plugin_name]['api_sig'] : '' ) ) ;
            ?>" size="70" name="tc[gateways][<?php 
            echo  $this->plugin_name ;
            ?>][api_sig]" type="text" />
                                    </label>
                                </p>

                                <p>
                                    <span class="description">
                                        <?php 
            _e( 'You must register this application with PayPal using your business account login to get an Application ID that will work with your API credentials. <a target="_blank" href="https://apps.paypal.com/user/my-account/applications">Register then submit your application</a> while logged in to the developer portal.</a>  <a target="_blank" href="https://developer.paypal.com/docs/classic/lifecycle/goingLive/#register">More Information &raquo;</a>', 'tc' );
            ?>
                                    </span>
                                    <label><?php 
            _e( 'Application ID', 'tc' );
            ?><br />
                                        <input value="<?php 
            echo  esc_attr( ( isset( $settings['gateways'][$this->plugin_name]['app_id'] ) ? $settings['gateways'][$this->plugin_name]['app_id'] : '' ) ) ;
            ?>" size="70" name="tc[gateways][<?php 
            echo  $this->plugin_name ;
            ?>][app_id]" type="text" />
                                    </label>
                                </p>

                            </td>
                        </tr>

                    </table>    
                </div>
            </div>      
            <?php 
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
                <h3><span><?php 
            _e( 'PayPal', 'tc' );
            ?></span></h3>
                <div class="inside">
                    <table class="form-table">
                        <tr valign="top">
                            <th scope="row"><?php 
            _e( 'PayPal Mode', 'tc' );
            ?></th>
                            <td>
                                <select name="tc[gateways][<?php 
            echo  $this->plugin_name ;
            ?>][mode]">
                                    <option value="live"<?php 
            selected( ( isset( $settings['gateways'][$this->plugin_name]['mode'] ) ? $settings['gateways'][$this->plugin_name]['mode'] : 'sandbox' ), 'live' );
            ?>><?php 
            _e( 'Live', 'tc' );
            ?></option>
                                    <option value="sandbox"<?php 
            selected( ( isset( $settings['gateways'][$this->plugin_name]['mode'] ) ? $settings['gateways'][$this->plugin_name]['mode'] : 'sandbox' ), 'sandbox' );
            ?>><?php 
            _e( 'Sandbox', 'tc' );
            ?></option>
                                </select>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><?php 
            _e( 'Currency', 'tc' );
            ?></th>
                            <td>
                                <span class="description"><?php 
            _e( 'Selecting a currency other than that used for your store may cause problems at checkout.', 'tc' );
            ?></span><br />
                                <select name="tc[gateways][<?php 
            echo  $this->plugin_name ;
            ?>][currency]">
                                    <?php 
            $sel_currency = ( isset( $settings['gateways'][$this->plugin_name]['currency'] ) ? $settings['gateways'][$this->plugin_name]['currency'] : 'USD' );
            $currencies = $this->currencies;
            foreach ( $currencies as $k => $v ) {
                echo  '<option value="' . $k . '"' . (( $k == $sel_currency ? ' selected' : '' )) . '>' . esc_html( $v, true ) . '</option>' . "\n" ;
            }
            ?>
                                </select>
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row"><?php 
            _e( 'Locale', 'tc' );
            ?></th>
                            <td>
                                <select name="tc[gateways][<?php 
            echo  $this->plugin_name ;
            ?>][locale]">
                                    <?php 
            $sel_locale = ( isset( $settings['gateways'][$this->plugin_name]['locale'] ) ? $settings['gateways'][$this->plugin_name]['locale'] : 'US' );
            //en_US
            foreach ( $this->locales as $k => $v ) {
                echo  '<option value="' . $k . '"' . (( $k == $sel_locale ? ' selected' : '' )) . '>' . esc_html( $v, true ) . '</option>' . "\n" ;
            }
            ?>
                                </select>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row"><?php 
            _e( 'PayPal Credentials', 'tc' );
            ?></th>
                            <td>
                                <p>
                                    <label><?php 
            _e( 'PayPal E-mail', 'tc' );
            ?><br />
                                        <input value="<?php 
            echo  esc_attr( ( isset( $settings['gateways'][$this->plugin_name]['email'] ) ? $settings['gateways'][$this->plugin_name]['email'] : '' ) ) ;
            ?>" size="70" name="tc[gateways][<?php 
            echo  $this->plugin_name ;
            ?>][email]" type="text" />
                                    </label>
                                </p>
                            </td>
                        </tr>
                    </table>    
                </div>
            </div>      
            <?php 
        }
        
        function process_payment( $cart )
        {
            global  $tc ;
            $this->maybe_start_session();
            $this->save_cart_info();
            $order_id = $tc->generate_order_id();
            $this->cancelURL = $this->cancel_url;
            $this->returnURL = $tc->get_confirmation_slug( true, $order_id );
            //set it up with PayPal
            $result = $this->Pay( $cart, $order_id, $this->total() );
            //check response
            
            if ( $result["responseEnvelope_ack"] == "Success" || $result["responseEnvelope_ack"] == "SuccessWithWarning" ) {
                $paykey = urldecode( $result["payKey"] );
                $_SESSION['PAYKEY'] = $paykey;
                $payment_info = $this->save_payment_info();
                $tc->create_order(
                    $order_id,
                    $this->cart_contents(),
                    $this->cart_info(),
                    $payment_info,
                    $paid
                );
                //go to paypal for final payment confirmation
                $this->RedirectToPayPal( $paykey );
            } else {
                //whoops, error
                for ( $i = 0 ;  $i <= 10 ;  $i++ ) {
                    if ( isset( $result["error({$i})_message"] ) ) {
                        $error .= "<li>{$result["error({$i})_errorId"]} - {$result["error({$i})_message"]}</li>";
                    }
                }
                $error = '<br /><ul>' . $error . '</ul>';
                $_SESSION['tc_gateway_error'] = $error;
                wp_redirect( $tc->get_payment_slug( true ) );
                tc_js_redirect( $tc->get_payment_slug( true ) );
                exit;
            }
        
        }
        
        function Pay( $cart, $order_id, $total )
        {
            global  $tc ;
            $network_settings = get_site_option( 'tc_network_settings', array() );
            $settings = get_option( 'tc_settings', array() );
            $nvpstr = "actionType=PAY";
            $nvpstr .= "&returnUrl=" . $this->returnURL;
            $nvpstr .= "&cancelUrl=" . $this->cancelURL;
            $nvpstr .= "&ipnNotificationUrl=" . $this->ipn_url;
            $nvpstr .= "&currencyCode=" . $this->currency;
            $nvpstr .= "&feesPayer=PRIMARYRECEIVER";
            $nvpstr .= "&trackingId=" . $order_id;
            $nvpstr .= "&memo=" . urlencode( sprintf( __( '%s Store Purchase - Order ID: %s', 'tc' ), get_bloginfo( 'name' ), $order_id ) );
            //cart name
            //loop through cart items
            //calculate fees / get fees only for base price (excluding taxes and shipping)
            $percentage = ( isset( $network_settings['gateways'][$this->plugin_name]['percentage'] ) ? $network_settings['gateways'][$this->plugin_name]['percentage'] : 0 );
            $fee = round( $total / 100 * $percentage, 2 );
            $nvpstr .= "&receiverList.receiver(0).email=" . urlencode( $settings['gateways'][$this->plugin_name]['email'] );
            $nvpstr .= "&receiverList.receiver(0).amount=" . round( $total, 2 );
            $nvpstr .= "&receiverList.receiver(0).invoiceId=" . $order_id;
            $nvpstr .= "&receiverList.receiver(0).paymentType=GOODS";
            $nvpstr .= "&receiverList.receiver(0).primary=true";
            $nvpstr .= "&receiverList.receiver(1).email=" . urlencode( $network_settings['gateways'][$this->plugin_name]['network_email'] );
            $nvpstr .= "&receiverList.receiver(1).amount=" . $fee;
            $nvpstr .= "&receiverList.receiver(1).paymentType=SERVICE";
            $nvpstr .= "&receiverList.receiver(1).primary=false";
            //make the call
            return $this->api_call( "Pay", $nvpstr );
        }
        
        function decodePayPalIPN( $raw_post )
        {
            if ( empty($raw_post) ) {
                return array();
            }
            $post = array();
            $pairs = explode( '&', $raw_post );
            foreach ( $pairs as $pair ) {
                list( $key, $value ) = explode( '=', $pair, 2 );
                $key = urldecode( $key );
                $value = urldecode( $value );
                # This is look for a key as simple as 'return_url' or as complex as 'somekey[x].property'
                preg_match( '/(\\w+)(?:\\[(\\d+)\\])?(?:\\.(\\w+))?/', $key, $key_parts );
                switch ( count( $key_parts ) ) {
                    case 4:
                        # Original key format: somekey[x].property
                        # Converting to $post[somekey][x][property]
                        
                        if ( !isset( $post[$key_parts[1]] ) ) {
                            $post[$key_parts[1]] = array(
                                $key_parts[2] => array(
                                $key_parts[3] => $value,
                            ),
                            );
                        } else {
                            
                            if ( !isset( $post[$key_parts[1]][$key_parts[2]] ) ) {
                                $post[$key_parts[1]][$key_parts[2]] = array(
                                    $key_parts[3] => $value,
                                );
                            } else {
                                $post[$key_parts[1]][$key_parts[2]][$key_parts[3]] = $value;
                            }
                        
                        }
                        
                        break;
                    case 3:
                        # Original key format: somekey[x]
                        # Converting to $post[somkey][x]
                        if ( !isset( $post[$key_parts[1]] ) ) {
                            $post[$key_parts[1]] = array();
                        }
                        $post[$key_parts[1]][$key_parts[2]] = $value;
                        break;
                    default:
                        # No special format
                        $post[$key] = $value;
                        break;
                }
                #switch
            }
            #foreach
            return $post;
        }
        
        function RedirectToPayPal( $token )
        {
            // Redirect to paypal.com here
            $payPalURL = $this->paypalURL . $token;
            wp_redirect( $payPalURL );
            tc_js_redirect( $payPalURL );
            exit;
        }
        
        function PaymentDetails( $paykey )
        {
            $nvpstr = "payKey=" . urlencode( $paykey ) . "&senderOptions.referrerCode=Tickera_SP";
            //make the call
            return $this->api_call( "PaymentDetails", $nvpstr );
        }
        
        //This function will take NVPString and convert it to an Associative Array and it will decode the response.
        function deformatNVP( $nvpstr )
        {
            parse_str( $nvpstr, $nvpArray );
            return $nvpArray;
        }
        
        function api_call( $methodName, $nvpStr )
        {
            global  $tc ;
            //build args
            $args['headers'] = array(
                'X-PAYPAL-SECURITY-USERID'         => $this->API_Username,
                'X-PAYPAL-SECURITY-PASSWORD'       => $this->API_Password,
                'X-PAYPAL-SECURITY-SIGNATURE'      => $this->API_Signature,
                'X-PAYPAL-DEVICE-IPADDRESS'        => $_SERVER['REMOTE_ADDR'],
                'X-PAYPAL-REQUEST-DATA-FORMAT'     => 'NV',
                'X-PAYPAL-REQUEST-RESPONSE-FORMAT' => 'NV',
                'X-PAYPAL-APPLICATION-ID'          => $this->appId,
            );
            $args['user-agent'] = $tc->title . "/" . $tc->version . ": " . get_site_url();
            $args['body'] = $nvpStr . '&requestEnvelope.errorLanguage=en_US';
            $args['sslverify'] = false;
            $args['timeout'] = 60;
            //use built in WP http class to work with most server setups
            $response = wp_remote_post( $this->API_Endpoint . $methodName, $args );
            
            if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) != 200 ) {
                $_SESSION['tc_gateway_error'] = __( 'There was a problem connecting to PayPal. Please try again.', 'tc' );
                wp_redirect( $tc->get_payment_slug( true ) );
                tc_js_redirect( $tc->get_payment_slug( true ) );
                return false;
            } else {
                //convert NVPResponse to an Associative Array
                $nvpResArray = $this->deformatNVP( $response['body'] );
                return $nvpResArray;
            }
        
        }
        
        function ipn()
        {
            global  $tc ;
            $settings = get_option( 'tc_settings' );
            $message = '';
            foreach ( $_POST as $key => $value ) {
                $message .= $key . ' = ' . $value . '<br />';
            }
            
            if ( isset( $_POST['transaction_type'] ) ) {
                $txn_type = $_POST['transaction_type'];
                $tracking_id = $_POST['tracking_id'];
                $order = tc_get_order_id_by_name( $tracking_id );
                $order_id = $order->ID;
                
                if ( empty($txn_type) || empty($tracking_id) ) {
                    header( 'Status: 404 Not Found' );
                    echo  'Error: Missing POST variables. Identification is not possible.' ;
                    exit;
                }
                
                $raw_post_data = file_get_contents( 'php://input' );
                $raw_post_array = explode( '&', $raw_post_data );
                $myPost = array();
                foreach ( $raw_post_array as $keyval ) {
                    $keyval = explode( '=', $keyval );
                    if ( count( $keyval ) == 2 ) {
                        $myPost[$keyval[0]] = urldecode( $keyval[1] );
                    }
                }
                $req = 'cmd=_notify-validate';
                if ( function_exists( 'get_magic_quotes_gpc' ) ) {
                    $get_magic_quotes_exists = true;
                }
                foreach ( $myPost as $key => $value ) {
                    
                    if ( $get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1 ) {
                        $value = urlencode( stripslashes( $value ) );
                    } else {
                        $value = urlencode( $value );
                    }
                    
                    $req .= "&{$key}={$value}";
                }
                
                if ( $settings['gateways'][$this->plugin_name]['mode'] == 'sandbox' ) {
                    $url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
                } else {
                    $url = 'https://www.paypal.com/cgi-bin/webscr';
                }
                
                $args['user-agent'] = $tc->title;
                $args['body'] = $req;
                $args['sslverify'] = false;
                $args['timeout'] = 60;
                $response = wp_remote_post( $url, $args );
                //check results
                
                if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) != 200 || $response['body'] != 'VERIFIED' ) {
                    header( "HTTP/1.1 503 Service Unavailable" );
                } else {
                    switch ( $_POST['status'] ) {
                        case 'COMPLETED':
                            $tc->update_order_payment_status( $order_id, true );
                            break;
                        case 'SUCCESS':
                            $tc->update_order_payment_status( $order_id, true );
                            break;
                        case 'PROCESSING':
                            $tc->update_order_payment_status( $order_id, true );
                            break;
                        default:
                            //do nothing, wait for IPN message
                    }
                    $tc->remove_order_session_data();
                }
            
            }
        
        }
    
    }
    tc_register_gateway_plugin( 'TC_Gateway_PayPal_Chained_Payments', 'paypal-chained-payments', __( 'PayPal Chained Payments', 'tc' ) );
}

if ( !function_exists( 'tcpcp_fs' ) ) {
    // Create a helper function for easy SDK access.
    function tcpcp_fs()
    {
        global  $tcpcp_fs ;
        
        if ( !isset( $tcpcp_fs ) ) {
            // Activate multisite network integration.
            if ( !defined( 'WP_FS__PRODUCT_3184_MULTISITE' ) ) {
                define( 'WP_FS__PRODUCT_3184_MULTISITE', true );
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
            
            $tcpcp_fs = fs_dynamic_init( array(
                'id'               => '3184',
                'slug'             => 'paypal-chained-payments',
                'premium_slug'     => 'paypal-chained-payments',
                'type'             => 'plugin',
                'public_key'       => 'pk_41e2998b057b5ee70e14b772a4967',
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
        
        return $tcpcp_fs;
    }

}
function tcpcp_fs_is_parent_active_and_loaded()
{
    // Check if the parent's init SDK method exists.
    return function_exists( 'tets_fs' );
}

function tcpcp_fs_is_parent_active()
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

function tcpcp_fs_init()
{
    
    if ( tcpcp_fs_is_parent_active_and_loaded() ) {
        // Init Freemius.
        tcpcp_fs();
        // Parent is active, add your init code here.
    } else {
        // Parent is inactive, add your error handling here.
    }

}


if ( tcpcp_fs_is_parent_active_and_loaded() ) {
    // If parent already included, init add-on.
    tcpcp_fs_init();
} else {
    
    if ( tcpcp_fs_is_parent_active() ) {
        // Init add-on only after the parent is loaded.
        add_action( 'tets_fs_loaded', 'tcpcp_fs_init' );
    } else {
        // Even though the parent is not activated, execute add-on for activation / uninstall hooks.
        tcpcp_fs_init();
    }

}
