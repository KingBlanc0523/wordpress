<?php

/*
 Plugin Name: Tickera Terms & Conditions
 Plugin URI: http://tickera.com/
 Description: Set Terms and Conditions that ticket buyer needs to check in order to purchase ticket.
 Author: Tickera.com
 Author URI: http://tickera.com/
 Version: 1.2
 Text Domain: tac
 Domain Path: /languages/

 Copyright 2019 Tickera (http://tickera.com/)
*/
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
// Exit if accessed directly

if ( !class_exists( 'TC_Terms' ) ) {
    class TC_Terms
    {
        var  $version = '1.2' ;
        var  $title = 'Tickera Terms & Conditions' ;
        var  $name = 'tac' ;
        var  $dir_name = 'terms-and-conditions' ;
        var  $location = 'plugins' ;
        var  $plugin_dir = '' ;
        var  $plugin_url = '' ;
        function __construct()
        {
            add_filter( 'tc_settings_new_menus', array( &$this, 'tc_settings_new_menus_additional' ) );
            add_action( 'tc_settings_menu_tickera_terms', array( &$this, 'tc_settings_menu_tickera_terms_show_page' ) );
            add_action( 'tc_before_cart_submit', array( &$this, 'add_terms_and_conditions_checkbox' ) );
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
                load_muplugin_textdomain( 'tac', 'languages/' );
            } else {
                
                if ( $this->location == 'subfolder-plugins' ) {
                    load_plugin_textdomain( 'tac', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
                } else {
                    
                    if ( $this->location == 'plugins' ) {
                        load_plugin_textdomain( 'tac', false, 'languages/' );
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
                $options = array( 'tc_terms_settings' );
                foreach ( $options as $option ) {
                    delete_option( $option );
                }
            }
        
        }
        
        function tc_settings_new_menus_additional( $settings_tabs )
        {
            $settings_tabs['tickera_terms'] = __( 'Terms & Conditions', 'tac' );
            return $settings_tabs;
        }
        
        //set sendlooop options
        function tc_settings_menu_tickera_terms_show_page()
        {
            require_once $this->plugin_dir . 'includes/admin-pages/settings-tickera_terms.php';
        }
        
        function add_terms_and_conditions_checkbox()
        {
            $tc_terms_settings = get_option( 'tc_terms_settings' );
            $tc_link_title = $tc_terms_settings['link_title'];
            $tc_terms_content = $tc_terms_settings['terms'];
            $tc_terms_error = $tc_terms_settings['error_text'];
            $tc_term_display = $tc_terms_settings['term_display'];
            $tc_term_page = $tc_terms_settings['terms_page'];
            
            if ( !isset( $tc_terms_settings['disable_terms'] ) ) {
                //fill variables if they are empty
                if ( empty($tc_terms_error) ) {
                    $tc_terms_error = __( 'You must agree to the terms and conditions before proceeding to the checkout', 'tac' );
                }
                if ( empty($tc_link_title) ) {
                    $tc_link_title = __( 'I agree to the Terms and Conditions', 'tac' );
                }
                // hook the js files
                if ( !function_exists( 'terms_js' ) ) {
                    function terms_js()
                    {
                        wp_enqueue_script( 'tc-terms-js', plugin_dir_url( __FILE__ ) . '/includes/javascript.js' );
                    }
                
                }
                add_action( 'wp_footer', 'terms_js' );
                
                if ( $tc_term_display == 'p' ) {
                    //calling thickbox
                    add_thickbox();
                    ?>

                    <label>
                        <input type="checkbox" name="check_terms" id="tc_terms_and_conditions" /> <a href="#TB_inline?width=600&height=550&inlineId=tc_terms_content" class="thickbox"><?php 
                    echo  $tc_link_title ;
                    ?></a>
                        <div class="tc_term_error" style="display: none; color: red;">
                            <?php 
                    echo  $tc_terms_error ;
                    ?>
                        </div><!-- .tc_term_error -->
                    </label>


                    <div id="tc_terms_content" style="display:none;">
                        <?php 
                    if ( !empty($tc_terms_content) ) {
                        echo  '<p>' . $tc_terms_content . '</p>' ;
                    }
                    ?>
                    </div><!-- #tc_terms_content -->


                <?php 
                } else {
                    ?>
                    <!-- display link to a page with terms and conditions -->
                    <label>
                        <input type="checkbox" name="check_terms" id="tc_terms_and_conditions" /><a target="_blank" href="<?php 
                    echo  get_permalink( $tc_term_page ) ;
                    ?>"><?php 
                    echo  $tc_link_title ;
                    ?></a>
                        <div class="tc_term_error" style="display: none; color: red;">
                            <?php 
                    echo  $tc_terms_error ;
                    ?>
                        </div><!-- .tc_term_error -->
                    </label>
                    <?php 
                }
                
                //if($tc_term_display == 'p'){
            }
            
            //add_terms_and_conditions_checkbox
        }
    
    }
    //class TC_Terms
}

if ( !function_exists( 'is_plugin_active_for_network' ) ) {
    require_once ABSPATH . '/wp-admin/includes/plugin.php';
}

if ( is_multisite() && is_plugin_active_for_network( plugin_basename( __FILE__ ) ) ) {
    function tc_terms_load()
    {
        global  $tc_terms ;
        $tc_terms = new TC_Terms();
    }
    
    add_action( 'tets_fs_loaded', 'tc_terms_load' );
} else {
    $tc_terms = new TC_Terms();
}

//HOOK ADMIN JS FILE
function terms_js_script()
{
    wp_enqueue_script( 'tc_admin_js', plugin_dir_url( __FILE__ ) . 'includes/admin-js.js' );
}

add_action( 'admin_enqueue_scripts', 'terms_js_script' );
if ( !function_exists( 'tctac_fs' ) ) {
    // Create a helper function for easy SDK access.
    function tctac_fs()
    {
        global  $tctac_fs ;
        
        if ( !isset( $tctac_fs ) ) {
            // Activate multisite network integration.
            if ( !defined( 'WP_FS__PRODUCT_3179_MULTISITE' ) ) {
                define( 'WP_FS__PRODUCT_3179_MULTISITE', true );
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
            
            $tctac_fs = fs_dynamic_init( array(
                'id'               => '3179',
                'slug'             => 'terms-and-conditions',
                'premium_slug'     => 'terms-and-conditions',
                'type'             => 'plugin',
                'public_key'       => 'pk_57c7bdc60c123bcb114116d277969',
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
        
        return $tctac_fs;
    }

}
function tctac_fs_is_parent_active_and_loaded()
{
    // Check if the parent's init SDK method exists.
    return function_exists( 'tets_fs' );
}

function tctac_fs_is_parent_active()
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

function tctac_fs_init()
{
    
    if ( tctac_fs_is_parent_active_and_loaded() ) {
        // Init Freemius.
        tctac_fs();
        // Parent is active, add your init code here.
    } else {
        // Parent is inactive, add your error handling here.
    }

}


if ( tctac_fs_is_parent_active_and_loaded() ) {
    // If parent already included, init add-on.
    tctac_fs_init();
} else {
    
    if ( tctac_fs_is_parent_active() ) {
        // Init add-on only after the parent is loaded.
        add_action( 'tets_fs_loaded', 'tctac_fs_init' );
    } else {
        // Even though the parent is not activated, execute add-on for activation / uninstall hooks.
        tctac_fs_init();
    }

}
