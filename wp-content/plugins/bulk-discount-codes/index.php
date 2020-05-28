<?php

/*
 Plugin Name: Tickera Bulk Discount Codes
 Plugin URI: http://tickera.com/
 Description: Add bulk discount codes
 Author: Tickera.com
 Author URI: http://tickera.com/
 Version: 1.0.7
 Text Domain: tc-bdc
 Domain Path: /languages/
 Copyright 2019 Tickera (http://tickera.com/)
*/
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
// Exit if accessed directly
if ( !class_exists( 'TC_Bulk_Discount_Codes' ) ) {
    class TC_Bulk_Discount_Codes
    {
        var  $version = '1.0.7' ;
        var  $title = 'Tickera Bulk Discount Codes' ;
        var  $name = 'tc-bdc' ;
        var  $dir_name = 'bulk-discount-codes' ;
        var  $location = 'plugins' ;
        var  $plugin_dir = '' ;
        var  $plugin_url = '' ;
        function __construct()
        {
            $this->init_vars();
            add_action( 'init', array( &$this, 'localization' ), 10 );
            add_filter( 'tc_settings_new_menus', array( &$this, 'tc_settings_new_menus_additional' ) );
            add_action( 'tc_settings_menu_tickera_bulk_discount_codes', array( &$this, 'tc_settings_menu_tickera_bulk_discount_codes_show_page' ) );
        }
        
        function tc_settings_new_menus_additional( $settings_tabs )
        {
            $settings_tabs['tickera_bulk_discount_codes'] = __( 'Bulk Discounts', 'tc-bdc' );
            return $settings_tabs;
        }
        
        function tc_settings_menu_tickera_bulk_discount_codes_show_page()
        {
            
            if ( apply_filters( 'tc_bridge_for_woocommerce_is_active', false ) == true ) {
                require_once $this->plugin_dir . 'includes/admin-pages/settings-tickera_bulk_discount_codes-woo.php';
            } else {
                require_once $this->plugin_dir . 'includes/admin-pages/settings-tickera_bulk_discount_codes.php';
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
                        wp_die( sprintf( __( 'There was an issue determining where %s is installed. Please reinstall it.', 'tc-bdc' ), $this->title ) );
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
                load_muplugin_textdomain( 'tc-bdc', 'languages/' );
            } else {
                
                if ( $this->location == 'subfolder-plugins' ) {
                    load_plugin_textdomain( 'tc-bdc', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
                } else {
                    
                    if ( $this->location == 'plugins' ) {
                        load_plugin_textdomain( 'tc-bdc', false, 'languages/' );
                    } else {
                    }
                
                }
            
            }
            
            $temp_locales = explode( '_', get_locale() );
            $this->language = ( $temp_locales[0] ? $temp_locales[0] : 'en' );
        }
    
    }
}
if ( !function_exists( 'is_plugin_active_for_network' ) ) {
    require_once ABSPATH . '/wp-admin/includes/plugin.php';
}

if ( is_multisite() && is_plugin_active_for_network( plugin_basename( __FILE__ ) ) ) {
    function tc_bulk_discount_codes_load()
    {
        global  $tc_bulk_discount_codes ;
        $tc_bulk_discount_codes = new TC_Bulk_Discount_Codes();
    }
    
    add_action( 'tets_fs_loaded', 'tc_bulk_discount_codes_load' );
} else {
    $tc_bulk_discount_codes = new TC_Bulk_Discount_Codes();
}

if ( !function_exists( 'tcbdc_fs' ) ) {
    // Create a helper function for easy SDK access.
    function tcbdc_fs()
    {
        global  $tcbdc_fs ;
        
        if ( !isset( $tcbdc_fs ) ) {
            // Activate multisite network integration.
            if ( !defined( 'WP_FS__PRODUCT_3187_MULTISITE' ) ) {
                define( 'WP_FS__PRODUCT_3187_MULTISITE', true );
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
            
            $tcbdc_fs = fs_dynamic_init( array(
                'id'               => '3187',
                'slug'             => 'bulk-discount-codes',
                'premium_slug'     => 'bulk-discount-codes',
                'type'             => 'plugin',
                'public_key'       => 'pk_b37539bb642c6b5d990f6d84ac249',
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
        
        return $tcbdc_fs;
    }

}
function tcbdc_fs_is_parent_active_and_loaded()
{
    // Check if the parent's init SDK method exists.
    return function_exists( 'tets_fs' );
}

function tcbdc_fs_is_parent_active()
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

function tcbdc_fs_init()
{
    
    if ( tcbdc_fs_is_parent_active_and_loaded() ) {
        // Init Freemius.
        tcbdc_fs();
        // Parent is active, add your init code here.
    } else {
        // Parent is inactive, add your error handling here.
    }

}


if ( tcbdc_fs_is_parent_active_and_loaded() ) {
    // If parent already included, init add-on.
    tcbdc_fs_init();
} else {
    
    if ( tcbdc_fs_is_parent_active() ) {
        // Init add-on only after the parent is loaded.
        add_action( 'tets_fs_loaded', 'tcbdc_fs_init' );
    } else {
        // Even though the parent is not activated, execute add-on for activation / uninstall hooks.
        tcbdc_fs_init();
    }

}
