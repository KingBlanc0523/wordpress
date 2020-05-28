<?php

/*
 Plugin Name: Event Calendar for Tickera
 Plugin URI: http://tickera.com/
 Description: Add calendar view for all your Tickera events
 Author: Tickera.com
 Author URI: http://tickera.com/
 Version: 1.1.8
 Text Domain: ec
 Domain Path: /languages/

 Copyright 2019 Tickera (http://tickera.com/)
*/
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
// Exit if accessed directly
if ( !class_exists( 'TC_Event_Calendar' ) ) {
    class TC_Event_Calendar
    {
        var  $version = '1.1.8' ;
        var  $title = 'Calendar' ;
        var  $name = 'tc_calendar' ;
        var  $dir_name = 'tickera-event-calendar' ;
        var  $location = 'plugins' ;
        var  $plugin_dir = '' ;
        var  $plugin_url = '' ;
        function __construct()
        {
            $this->init_vars();
            $this->init();
            add_action( 'init', array( &$this, 'localization' ), 10 );
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
                        wp_die( sprintf( __( 'There was an issue determining where %s is installed. Please reinstall it.', 'ec' ), $this->title ) );
                    }
                
                }
            
            }
        
        }
        
        function init()
        {
            global  $tc ;
            require_once $this->plugin_dir . 'includes/functions.php';
            //add_action( 'wp_enqueue_scripts', array( &$this, 'front_scripts_and_styles' ) );
            add_action( 'admin_enqueue_scripts', array( &$this, 'admin_scripts_and_styles' ) );
            add_shortcode( 'tc_calendar', array( &$this, 'show_calendar' ) );
            add_filter( 'tc_event_fields', array( &$this, 'tc_add_event_fields' ) );
            add_filter( 'tc_shortcodes', array( &$this, 'tc_shortcodes_to_shortcode_builder' ) );
            //remove_filter('the_content', 'wpautop');
        }
        
        function localization()
        {
            // Load up the localization file if we're using WordPress in a different language
            // Place it in this plugin's "languages" folder and name it "tc-[value in wp-config].mo"
            
            if ( $this->location == 'mu-plugins' ) {
                load_muplugin_textdomain( 'ec', 'languages/' );
            } else {
                
                if ( $this->location == 'subfolder-plugins' ) {
                    //load_plugin_textdomain( 'ec', false, $this->plugin_dir . '/languages/' );
                    load_plugin_textdomain( 'ec', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
                } else {
                    
                    if ( $this->location == 'plugins' ) {
                        load_plugin_textdomain( 'ec', false, 'languages/' );
                    } else {
                    }
                
                }
            
            }
            
            $temp_locales = explode( '_', get_locale() );
            $this->language = ( $temp_locales[0] ? $temp_locales[0] : 'en' );
        }
        
        function front_scripts_and_styles( $scheme, $lang )
        {
            $color_schemes = tc_get_calendar_color_schemes();
            $selected_color_scheme = $color_schemes[$scheme];
            wp_enqueue_style(
                $this->name . '-fullcalendar',
                $this->plugin_url . 'includes/fullcalendar/fullcalendar.css',
                array(),
                $this->version
            );
            wp_enqueue_style(
                $this->name . '-front',
                $this->plugin_url . 'includes/css/front.css',
                array(),
                $this->version
            );
            if ( $selected_color_scheme['url'] !== '' ) {
                wp_enqueue_style(
                    $this->name . '-' . $selected_color_scheme['name'],
                    $selected_color_scheme['url'],
                    array(),
                    $this->version
                );
            }
            wp_enqueue_script(
                $this->name . '-moment',
                $this->plugin_url . 'includes/fullcalendar/lib/moment.min.js',
                array( 'jquery' ),
                $this->version,
                true
            );
            wp_enqueue_script(
                $this->name . '-fullcalendar',
                $this->plugin_url . 'includes/fullcalendar/fullcalendar.js',
                array( $this->name . '-moment', 'jquery' ),
                $this->version,
                true
            );
            wp_enqueue_script(
                $this->name . '-fullcalendar-lang',
                $this->plugin_url . 'includes/fullcalendar/lang/' . $lang . '.js',
                array( $this->name . '-fullcalendar' ),
                $this->version,
                true
            );
            wp_enqueue_script(
                $this->name . '-front-js',
                $this->plugin_url . 'js/front.js',
                '',
                $this->version,
                true
            );
        }
        
        function admin_scripts_and_styles()
        {
            wp_enqueue_style(
                $this->name . '-admin',
                $this->plugin_url . 'css/admin.css',
                array(),
                $this->version
            );
            wp_enqueue_script(
                $this->name . '-admin',
                $this->plugin_url . 'js/admin.js',
                array( 'jquery' ),
                $this->version
            );
        }
        
        function show_calendar( $atts )
        {
            ob_start();
            extract( shortcode_atts( array(
                'show_past_events' => '',
                'id'               => '',
                'color_scheme'     => 'default',
                'lang'             => 'en',
                'first_day'        => 1,
                'left_controls'    => 'prev,next today',
                'center_controls'  => 'title',
                'right_controls'   => 'month,agendaWeek,agendaDay',
            ), $atts ) );
            $terms = array();
            foreach ( $atts as $att => $val ) {
                if ( preg_match( '/et_/', $att ) ) {
                    $terms[] = str_replace( 'et_', '', $att );
                }
            }
            $this->front_scripts_and_styles( $color_scheme, $lang );
            $calendar_id = ( isset( $calendar_id ) && !empty($calendar_id) ? 'tc_calendar_' . $calendar_id : 'tc_calendar' );
            //$wp_events_search = new TC_Events_Search('', '', -1, 'publish');
            ?>
            <script>
                jQuery(document).ready(function () {
                    jQuery('#<?php 
            echo  $calendar_id ;
            ?>').fullCalendar({header: {
                            left: '<?php 
            echo  $left_controls ;
            ?>',
                            center: '<?php 
            echo  $center_controls ;
            ?>',
                            right: '<?php 
            echo  $right_controls ;
            ?>'
                        },
                        defaultDate: '<?php 
            echo  apply_filters( 'tc_default_date_calendar', date( "Y-m-d" ) ) ;
            ?>',
                        editable: false,
                        firstDay: <?php 
            echo  $first_day ;
            ?>,
                        timeFormat: '<?php 
            echo  dateformat_converter( get_option( 'time_format' ) ) ;
            ?>',
                        slotLabelFormat: '<?php 
            echo  dateformat_converter( get_option( 'time_format' ) ) ;
            ?>',
                        eventLimit: false, // allow "more" link when too many events
                        events: [
            <?php 
            $events_query = array(
                'post_type'      => 'tc_events',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
            );
            if ( count( $terms ) > 0 ) {
                $events_query['tax_query'] = array( array(
                    'taxonomy'         => 'event_category',
                    'field'            => 'id',
                    'terms'            => $terms,
                    'include_children' => false,
                ) );
            }
            $events = get_posts( $events_query );
            foreach ( $events as $event ) {
                $event = new TC_Event( $event->ID );
                $tc_get_start_date = get_post_meta( $event->id, 'event_date_time', true );
                
                if ( $atts['show_past_events'] == 'no' ) {
                    
                    if ( strtotime( $tc_get_start_date ) > current_time( 'timestamp', false ) ) {
                        $tc_event_display = true;
                    } else {
                        $tc_event_display = false;
                    }
                
                } else {
                    $tc_event_display = true;
                }
                
                
                if ( $tc_event_display == true ) {
                    $event_presentation_page = get_post_meta( $event->details->ID, 'event_presentation_page', true );
                    
                    if ( !empty($event_presentation_page) && is_numeric( $event_presentation_page ) ) {
                        $event_url = html_entity_decode( addslashes( get_permalink( $event_presentation_page ) ) );
                    } else {
                        $event_url = '';
                    }
                    
                    
                    if ( !empty($event->details->event_end_date_time) ) {
                        echo  "{\ntitle: '" . apply_filters( 'tc_calendar_title', html_entity_decode( addslashes( $event->details->post_title ) ) ) . "',\nstart: '" . esc_attr( $event->details->event_date_time ) . "',\nend: '" . esc_attr( $event->details->event_end_date_time ) . "',\nurl: '" . $event_url . "',\n}," ;
                    } else {
                        echo  "{\ntitle: '" . apply_filter( 'tc_calendar_title', html_entity_decode( addslashes( $event->details->post_title ) ) ) . "',\nstart: '" . esc_attr( $event->details->event_date_time ) . "',\nurl: '" . $event_url . "',\n}," ;
                    }
                
                }
            
            }
            //foreach ( $wp_events_search->get_results() as $event )
            ?>
                        ]
                    });
                });
            </script>
            <div id='<?php 
            echo  esc_attr( $calendar_id ) ;
            ?>'></div>
            <div class="tc-responsive-event"></div>
            <?php 
            $content = ob_get_clean();
            return $content;
        }
        
        function tc_add_event_fields( $fields )
        {
            $fields[] = array(
                'field_name'        => 'event_presentation_page',
                'field_title'       => __( 'Event Presentation Post / Page', 'ec' ),
                'placeholder'       => '',
                'field_type'        => 'function',
                'function'          => 'tc_get_posts_and_pages',
                'field_description' => __( 'Select an event presentation post or page. Selected page will be link to the event in the calendar.', 'ec' ),
                'table_visibility'  => false,
                'post_field_type'   => 'post_meta',
            );
            return $fields;
        }
        
        function tc_shortcodes_to_shortcode_builder( $shortcodes )
        {
            $shortcodes['tc_calendar'] = __( 'Display event calendar', 'ec' );
            return $shortcodes;
        }
    
    }
}
if ( !function_exists( 'is_plugin_active_for_network' ) ) {
    require_once ABSPATH . '/wp-admin/includes/plugin.php';
}

if ( is_multisite() && is_plugin_active_for_network( plugin_basename( __FILE__ ) ) ) {
    function tc_event_calendar_load()
    {
        global  $tc_event_calendar ;
        $tc_event_calendar = new TC_Event_Calendar();
    }
    
    add_action( 'tets_fs_loaded', 'tc_event_calendar_load' );
} else {
    $tc_event_calendar = new TC_Event_Calendar();
}

//converting php to JS time/date format
function dateformat_converter( $php_format )
{
    $symbolequivalent = array(
        'd' => 'dd',
        'D' => 'D',
        'j' => 'd',
        'l' => 'DD',
        'N' => '',
        'S' => '',
        'w' => '',
        'z' => 'o',
        'W' => '',
        'F' => 'MM',
        'm' => 'mm',
        'M' => 'M',
        'n' => 'm',
        't' => '',
        'L' => '',
        'o' => '',
        'Y' => 'yy',
        'y' => 'y',
        'a' => 'a',
        'A' => 'A',
        'B' => '',
        'g' => 'h',
        'G' => 'H',
        'h' => 'hh',
        'H' => 'HH',
        'i' => 'mm',
        's' => 'ss',
        'u' => '',
    );
    $jqueryui_format = "";
    $escaping = false;
    for ( $i = 0 ;  $i < strlen( $php_format ) ;  $i++ ) {
        $char = $php_format[$i];
        
        if ( $char === '\\' ) {
            // PHP date format escaping character
            $i++;
            
            if ( $escaping ) {
                $jqueryui_format .= $php_format[$i];
            } else {
                $jqueryui_format .= '\'' . $php_format[$i];
            }
            
            $escaping = true;
        } else {
            
            if ( $escaping ) {
                $jqueryui_format .= "'";
                $escaping = false;
            }
            
            
            if ( isset( $symbolequivalent[$char] ) ) {
                $jqueryui_format .= $symbolequivalent[$char];
            } else {
                $jqueryui_format .= $char;
            }
        
        }
    
    }
    return $jqueryui_format;
}

if ( !function_exists( 'tec_fs' ) ) {
    // Create a helper function for easy SDK access.
    function tec_fs()
    {
        global  $tec_fs ;
        
        if ( !isset( $tec_fs ) ) {
            // Activate multisite network integration.
            if ( !defined( 'WP_FS__PRODUCT_3169_MULTISITE' ) ) {
                define( 'WP_FS__PRODUCT_3169_MULTISITE', true );
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
            
            $tec_fs = fs_dynamic_init( array(
                'id'               => '3169',
                'slug'             => 'tickera-event-calendar',
                'premium_slug'     => 'tickera-event-calendar',
                'type'             => 'plugin',
                'public_key'       => 'pk_1c919dfb2a2444239e254015200e7',
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
        
        return $tec_fs;
    }

}
function tec_fs_is_parent_active_and_loaded()
{
    // Check if the parent's init SDK method exists.
    return function_exists( 'tets_fs' );
}

function tec_fs_is_parent_active()
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

function tec_fs_init()
{
    
    if ( tec_fs_is_parent_active_and_loaded() ) {
        // Init Freemius.
        tec_fs();
        // Parent is active, add your init code here.
    } else {
        // Parent is inactive, add your error handling here.
    }

}


if ( tec_fs_is_parent_active_and_loaded() ) {
    // If parent already included, init add-on.
    tec_fs_init();
} else {
    
    if ( tec_fs_is_parent_active() ) {
        // Init add-on only after the parent is loaded.
        add_action( 'tets_fs_loaded', 'tec_fs_init' );
    } else {
        // Even though the parent is not activated, execute add-on for activation / uninstall hooks.
        tec_fs_init();
    }

}
