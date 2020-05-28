<?php

/*
  Plugin Name: Role based prices for Tickera
  Plugin URI: http://tickera.com/
  Description: Show different prices for different user roles
  Author: Tickera.com
  Author URI: http://tickera.com/
  Version: 1.1
  Copyright 2019 Tickera (http://tickera.com/)
  Text Domain: role
  Domain Path: /languages/
*/
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
// Exit if accessed directly

if ( !class_exists( 'TC_Role_Based_Prices' ) ) {
    class TC_Role_Based_Prices
    {
        var  $version = '1.1' ;
        var  $title = 'Role Based Prices' ;
        var  $name = 'role' ;
        var  $dir_name = 'role-based-prices' ;
        var  $location = 'plugins' ;
        var  $plugin_dir = '' ;
        var  $plugin_url = '' ;
        function __construct()
        {
            add_filter(
                'tc_ticket_fields',
                array( &$this, 'add_ticket_type_fields' ),
                10,
                1
            );
            add_filter(
                'tc_price_per_ticket',
                array( &$this, 'tc_modify_price_per_ticket' ),
                10,
                2
            );
            add_action( 'init', array( &$this, 'localization' ), 10 );
        }
        
        //Plugin localization function
        function localization()
        {
            // Load up the localization file if we're using WordPress in a different language
            // Place it in this plugin's "languages" folder and name it "tc-[value in wp-config].mo"
            
            if ( $this->location == 'mu-plugins' ) {
                load_muplugin_textdomain( 'role', 'languages/' );
            } else {
                
                if ( $this->location == 'subfolder-plugins' ) {
                    load_plugin_textdomain( 'role', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
                } else {
                    
                    if ( $this->location == 'plugins' ) {
                        load_plugin_textdomain( 'role', false, 'languages/' );
                    } else {
                    }
                
                }
            
            }
            
            $temp_locales = explode( '_', get_locale() );
            $this->language = ( $temp_locales[0] ? $temp_locales[0] : 'en' );
        }
        
        function array_insert( $array, $values, $offset )
        {
            return array_slice(
                $array,
                0,
                $offset,
                true
            ) + $values + array_slice(
                $array,
                $offset,
                NULL,
                true
            );
        }
        
        public static function user_has_role( $role )
        {
            $user = wp_get_current_user();
            
            if ( in_array( $role, (array) $user->roles ) ) {
                return true;
            } else {
                return false;
            }
        
        }
        
        public static function get_user_role()
        {
            $user = wp_get_current_user();
            return strtolower( ( isset( $user->roles[0] ) ? str_replace( ' ', '-', $user->roles[0] ) : '' ) );
        }
        
        function tc_modify_price_per_ticket( $price, $id )
        {
            $current_user_role = $this->get_user_role();
            $user_role_price = get_post_meta( $id, 'price_per_ticket_' . $current_user_role, true );
            if ( is_numeric( $user_role_price ) ) {
                $price = $user_role_price;
            }
            return $price;
        }
        
        function add_ticket_type_fields( $fields )
        {
            if ( !is_admin() ) {
                return;
            }
            $index = 0;
            $position = false;
            $fields_before = array();
            $fields_middle = array();
            $fields_after = array();
            if ( !function_exists( 'get_editable_roles' ) ) {
                require_once ABSPATH . '/wp-admin/includes/user.php';
            }
            $user_roles = get_editable_roles();
            foreach ( $user_roles as $user_role => $user_role_array ) {
                $fields_middle[] = array(
                    'field_name'       => 'price_per_ticket_' . strtolower( str_replace( '-', '_', str_replace( ' ', '_', $user_role ) ) ),
                    'field_title'      => sprintf( __( 'Price for %s', 'role' ), $user_role_array['name'] ),
                    'placeholder'      => __( 'Same as Default Price', 'role' ),
                    'field_type'       => 'text',
                    'tooltip'          => sprintf( __( 'Set a price for the ticket shown to %s', 'role' ), $user_role_array['name'] ),
                    'table_visibility' => false,
                    'post_field_type'  => 'post_meta',
                    'number'           => true,
                );
            }
            foreach ( $fields as $field ) {
                
                if ( !$position ) {
                    
                    if ( $field['field_name'] == 'price_per_ticket' ) {
                        $field['field_title'] = __( 'Default Price', 'role' );
                        $fields[$index] = $field;
                        $position = $index;
                    }
                    
                    $fields_before[] = $field;
                } else {
                    $fields_after[] = $field;
                }
                
                $index++;
            }
            $fields = array_merge( $fields_before, $fields_middle, $fields_after );
            return $fields;
        }
    
    }
    if ( !function_exists( 'is_plugin_active_for_network' ) ) {
        require_once ABSPATH . '/wp-admin/includes/plugin.php';
    }
    
    if ( is_multisite() && is_plugin_active_for_network( plugin_basename( __FILE__ ) ) ) {
        function tc_role_based_prices_load()
        {
            global  $TC_Role_Based_Prices ;
            $TC_Role_Based_Prices = new TC_Role_Based_Prices();
        }
        
        add_action( 'tets_fs_loaded', 'tc_role_based_prices_load' );
    } else {
        $TC_Role_Based_Prices = new TC_Role_Based_Prices();
    }

}

if ( !function_exists( 'tcrbp_fs' ) ) {
    // Create a helper function for easy SDK access.
    function tcrbp_fs()
    {
        global  $tcrbp_fs ;
        
        if ( !isset( $tcrbp_fs ) ) {
            // Activate multisite network integration.
            if ( !defined( 'WP_FS__PRODUCT_3188_MULTISITE' ) ) {
                define( 'WP_FS__PRODUCT_3188_MULTISITE', true );
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
            
            $tcrbp_fs = fs_dynamic_init( array(
                'id'               => '3188',
                'slug'             => 'role-based-prices',
                'premium_slug'     => 'role-based-prices',
                'type'             => 'plugin',
                'public_key'       => 'pk_974e1329248468c0babe7ad5ffa1f',
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
        
        return $tcrbp_fs;
    }

}
function tcrbp_fs_is_parent_active_and_loaded()
{
    // Check if the parent's init SDK method exists.
    return function_exists( 'tets_fs' );
}

function tcrbp_fs_is_parent_active()
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

function tcrbp_fs_init()
{
    
    if ( tcrbp_fs_is_parent_active_and_loaded() ) {
        // Init Freemius.
        tcrbp_fs();
        // Parent is active, add your init code here.
    } else {
        // Parent is inactive, add your error handling here.
    }

}


if ( tcrbp_fs_is_parent_active_and_loaded() ) {
    // If parent already included, init add-on.
    tcrbp_fs_init();
} else {
    
    if ( tcrbp_fs_is_parent_active() ) {
        // Init add-on only after the parent is loaded.
        add_action( 'tets_fs_loaded', 'tcrbp_fs_init' );
    } else {
        // Even though the parent is not activated, execute add-on for activation / uninstall hooks.
        tcrbp_fs_init();
    }

}
