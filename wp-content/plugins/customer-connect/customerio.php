<?php

/**
 * Plugin Name:     Customer.io Connect for Tickera
 * Plugin URI:      https://tickera.com/
 * Description:     Track your customers with Customer.io
 * Version:         1.0.7
 * Author:          Tickera
 * Author URI:      https://tickera.com/
 * Text Domain:     cc
 * Domain Path: /languages/
 */
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
if ( !class_exists( 'TC_Customerio_Connect' ) ) {
    class TC_Customerio_Connect
    {
        /**
         * @access      public
         * @var         $api The Customer.io API object
         * @since       1.0.1
         */
        public  $api ;
        /**
         * @access public
         * @var $location, $plugin_dir, $plugin_url
         */
        public 
            $location,
            $plugin_dir,
            $plugin_url,
            $name
        ;
        public  $dir_name = 'customer-connect' ;
        /**
         * Get active instance
         *
         * @access      public
         * @since       1.0.0
         * @return      self::$instance The one true TC_Customerio_Connect
         */
        function __construct()
        {
            $this->init_vars();
            $this->includes();
            $this->load_plugin_textdomain();
            $tc_customerio_settings = get_option( 'tc_customerio_settings' );
            $site_id = $tc_customerio_settings['site_id'];
            $api_key = $tc_customerio_settings['api_key'];
            if ( $site_id && $api_key ) {
                $this->api = new TC_Customerio( $site_id, $api_key );
            }
            add_filter(
                'tc_settings_new_menus',
                array( &$this, 'tc_settings_new_menus' ),
                10,
                1
            );
            add_action( 'tc_settings_menu_customerio', array( &$this, 'tc_settings_menu_customerio' ) );
            add_filter( 'tc_admin_capabilities', array( &$this, 'append_capabilities' ) );
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
                load_muplugin_textdomain( 'cc', 'languages/' );
            } else {
                
                if ( $this->location == 'subfolder-plugins' ) {
                    load_plugin_textdomain( 'cc', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
                } else {
                    
                    if ( $this->location == 'plugins' ) {
                        load_plugin_textdomain( 'cc', false, 'languages/' );
                    } else {
                    }
                
                }
            
            }
            
            $temp_locales = explode( '_', get_locale() );
            $this->language = ( $temp_locales[0] ? $temp_locales[0] : 'en' );
        }
        
        function tc_delete_info_plugins_list( $plugins )
        {
            $plugins['customer-connect'] = __( 'Customer.io Connect' );
            return $plugins;
        }
        
        function tc_delete_plugins_data( $submitted_data )
        {
            
            if ( array_key_exists( 'customer-connect', $submitted_data ) ) {
                global  $wpdb ;
                //Delete options
                $options = array( 'tc_customerio_settings' );
                foreach ( $options as $option ) {
                    delete_option( $option );
                }
            }
        
        }
        
        function format( $amount )
        {
            
            if ( (int) $amount == (double) $amount ) {
                $int_decimals = 0;
            } else {
                $int_decimals = 2;
            }
            
            $decimals = apply_filters( 'tc_cart_amount_decimals', $int_decimals );
            return round( $amount, $decimals );
        }
        
        function tc_settings_new_menus( $menus )
        {
            $menus['customerio'] = __( 'Customer.io', 'tc' );
            return $menus;
        }
        
        function tc_settings_menu_customerio()
        {
            include $this->plugin_dir . 'includes/admin-pages/customerio_settings.php';
        }
        
        function append_capabilities( $capabilities )
        {
            //Add additional capabilities to admins
            $capabilities['manage_' . $this->name . '_cap'] = 1;
            return $capabilities;
        }
        
        /**
         * Initialize plugin variables
         */
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
                        wp_die( sprintf( __( 'There was an issue determining where %s is installed. Please reinstall it.', 'woocommerce-tickera-bridge' ), $this->title ) );
                    }
                
                }
            
            }
        
        }
        
        public function load_plugin_textdomain()
        {
            $locale = apply_filters( 'plugin_locale', get_locale(), 'tc' );
            load_textdomain( 'tc', WP_LANG_DIR . '/tickera-customerio-' . $locale . '.mo' );
            load_textdomain( 'tc', WP_LANG_DIR . '/tickera-customerio/tickera-customerio-' . $locale . '.mo' );
            load_plugin_textdomain( 'tc', false, plugin_basename( dirname( __FILE__ ) ) . "/languages" );
        }
        
        /**
         * Include necessary files
         *
         * @access      private
         * @since       1.0.0
         * @return      void
         */
        private function includes()
        {
            require_once $this->plugin_dir . 'includes/libraries/class.customerio.php';
            require_once $this->plugin_dir . 'includes/actions.php';
        }
    
    }
}
if ( !function_exists( 'is_plugin_active_for_network' ) ) {
    require_once ABSPATH . '/wp-admin/includes/plugin.php';
}

if ( is_multisite() && is_plugin_active_for_network( plugin_basename( __FILE__ ) ) ) {
    function tc_customerio_connect_load()
    {
        global  $TC_Customerio_Connect ;
        $TC_Customerio_Connect = new TC_Customerio_Connect();
    }
    
    add_action( 'tets_fs_loaded', 'tc_customerio_connect_load' );
} else {
    $TC_Customerio_Connect = new TC_Customerio_Connect();
}

if ( !function_exists( 'customer_connect_fs' ) ) {
    // Create a helper function for easy SDK access.
    function customer_connect_fs()
    {
        global  $customer_connect_fs ;
        
        if ( !isset( $customer_connect_fs ) ) {
            // Activate multisite network integration.
            if ( !defined( 'WP_FS__PRODUCT_3171_MULTISITE' ) ) {
                define( 'WP_FS__PRODUCT_3171_MULTISITE', true );
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
            
            $customer_connect_fs = fs_dynamic_init( array(
                'id'               => '3171',
                'slug'             => 'customer-connect',
                'premium_slug'     => 'customer-connect',
                'type'             => 'plugin',
                'public_key'       => 'pk_a61e0cfd59e4562b7fd7e06c4c87b',
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
        
        return $customer_connect_fs;
    }

}
function customer_connect_fs_is_parent_active_and_loaded()
{
    // Check if the parent's init SDK method exists.
    return function_exists( 'tets_fs' );
}

function customer_connect_fs_is_parent_active()
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

function customer_connect_fs_init()
{
    
    if ( customer_connect_fs_is_parent_active_and_loaded() ) {
        // Init Freemius.
        customer_connect_fs();
        // Parent is active, add your init code here.
    } else {
        // Parent is inactive, add your error handling here.
    }

}


if ( customer_connect_fs_is_parent_active_and_loaded() ) {
    // If parent already included, init add-on.
    customer_connect_fs_init();
} else {
    
    if ( customer_connect_fs_is_parent_active() ) {
        // Init add-on only after the parent is loaded.
        add_action( 'tets_fs_loaded', 'customer_connect_fs_init' );
    } else {
        // Even though the parent is not activated, execute add-on for activation / uninstall hooks.
        customer_connect_fs_init();
    }

}
