<?php

/*
 Plugin Name: Speakers
 Plugin URI: https://tickera.com/
 Description: List speaker profiles for your next event easily
 Author: Tickera.com
 Author URI: https://tickera.com/
 Version: 1.0
 TextDomain: tcsp
 Domain Path: /languages/
*/
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
// Exit if accessed directly

if ( !class_exists( 'TC_Speakers' ) ) {
    class TC_Speakers
    {
        var  $version = '1.0' ;
        var  $title = 'Speakers' ;
        var  $name = 'speakers' ;
        var  $dir_name = 'speakers' ;
        var  $location = 'plugins' ;
        var  $plugin_dir = '' ;
        var  $plugin_url = '' ;
        function __construct()
        {
            global  $post ;
            $this->init_vars();
            $this->load_plugin_textdomain();
            add_action( 'init', array( $this, 'register_custom_posts' ), 0 );
            add_filter(
                'enter_title_here',
                array( $this, 'enter_title_here' ),
                10,
                2
            );
            add_action( 'do_meta_boxes', array( $this, 'replace_featured_image_title' ) );
            add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 99 );
            add_action( 'save_post', array( $this, 'save_metabox_values' ) );
            add_action( 'wp_enqueue_scripts', array( $this, 'front_scripts_and_styles' ) );
            add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts_and_styles' ) );
            add_action( 'after_setup_theme', array( $this, 'tc_speaker_image_size' ) );
            add_shortcode( 'tc_speakers', array( $this, 'tc_speakers_page' ) );
            add_filter( 'excerpt_length', array( $this, 'tc_speakers_excerpt' ), 999 );
            add_action( 'init', array( $this, 'tc_speakers_taxonomy' ) );
            add_filter( 'the_content', array( $this, 'tc_add_speakers_info' ) );
            add_filter( 'the_content', array( $this, 'tc_add_speakers' ) );
            add_filter(
                'tc_settings_new_menus',
                array( $this, 'tc_settings_speakers_new_menu' ),
                10,
                1
            );
            add_action( 'tc_settings_menu_tc_speakers', array( $this, 'tc_settings_menu_tickera_speakers' ) );
            add_action( 'wp_ajax_tc_ajax_load_speaker', array( $this, 'tc_ajax_load_speaker' ) );
            add_action( 'wp_ajax_nopriv_tc_ajax_load_speaker', array( $this, 'tc_ajax_load_speaker' ) );
            add_filter( 'tc_speakers_post_type_args', array( $this, 'tc_speakers_post_type_args' ) );
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
            add_action( 'tc_shortcodes', array( $this, 'tc_add_speakers_shortcode' ) );
            add_filter( 'tc_event_fields', array( $this, 'tc_add_event_fields' ) );
            
            if ( function_exists( 'register_block_type' ) ) {
                add_action( 'init', array( $this, 'register_gutenberg_blocks_speakers' ) );
                add_action( 'enqueue_block_editor_assets', array( $this, 'register_extra_scripts_speakers' ) );
            }
        
        }
        
        function tc_add_event_fields( $fields )
        {
            $fields[] = array(
                'field_name'        => 'event_presentation_page',
                'field_title'       => __( 'Event Presentation Post / Page', 'tcsp' ),
                'placeholder'       => '',
                'field_type'        => 'function',
                'function'          => 'tc_get_posts_and_pages',
                'field_description' => __( 'Select an event presentation post or page. Selected page will be link to the event in the calendar.', 'tcsp' ),
                'table_visibility'  => false,
                'post_field_type'   => 'post_meta',
            );
            return $fields;
        }
        
        function tc_add_speakers_shortcode( $shortcodes )
        {
            $shortcodes['tc_speakers'] = __( 'Speakers', 'tcsp' );
            return $shortcodes;
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
                //Delete posts and post metas
                $wpdb->query( "\r\n                DELETE\r\n                p, pm\r\n                FROM {$wpdb->posts} p\r\n                JOIN {$wpdb->postmeta} pm on pm.post_id = p.id\r\n\t\t WHERE p.post_type IN ('tc_speakers')\r\n\t\t" );
                //Delete options
                $options = array( 'tc_speakers_settings' );
                foreach ( $options as $option ) {
                    delete_option( $option );
                }
            }
        
        }
        
        /**
         * Set up some basic varss
         */
        function init_vars()
        {
            
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
                        wp_die( sprintf( __( 'There was an issue determining where %s is installed. Please reinstall it.', 'tcsp' ), $this->title ) );
                    }
                
                }
            
            }
        
        }
        
        /**
         * Load plugin translation
         */
        function load_plugin_textdomain()
        {
            $locale = apply_filters( 'plugin_locale', get_locale(), 'tcsp' );
            load_textdomain( 'tcsp', WP_LANG_DIR . '/' . $locale . '.mo' );
            load_textdomain( 'tcsp', WP_LANG_DIR . '/speakers/' . $locale . '.mo' );
            load_plugin_textdomain( 'tcsp', false, plugin_basename( dirname( __FILE__ ) ) . "/languages" );
        }
        
        /**
         * Register tc_seat_charts custom post type
         */
        function register_custom_posts()
        {
            $args = array(
                'labels'             => array(
                'name'               => __( 'Speakers', 'tcsp' ),
                'singular_name'      => __( 'Speaker', 'tcsp' ),
                'add_new'            => __( 'Add New', 'tcsp' ),
                'add_new_item'       => __( 'Add New Speaker', 'tcsp' ),
                'edit_item'          => __( 'Edit Speaker', 'tcsp' ),
                'edit'               => __( 'Edit', 'tcsp' ),
                'new_item'           => __( 'New Speaker', 'tcsp' ),
                'view_item'          => __( 'View Speaker', 'tcsp' ),
                'search_items'       => __( 'Search Speakers', 'tcsp' ),
                'not_found'          => __( 'No Speakers Found', 'tcsp' ),
                'not_found_in_trash' => __( 'No Speaker profiles found in Trash', 'tcsp' ),
                'view'               => __( 'View Speaker', 'tcsp' ),
            ),
                'public'             => true,
                'show_ui'            => true,
                'publicly_queryable' => true,
                'hierarchical'       => true,
                'has_archive'        => true,
                'query_var'          => true,
                'show_in_menu'       => 'edit.php?post_type=tc_events',
                'supports'           => array(
                'title',
                'editor',
                'thumbnail',
                'excerpt'
            ),
            );
            register_post_type( 'tc_speakers', apply_filters( 'tc_speakers_post_type_args', $args ) );
        }
        
        function tc_speakers_taxonomy()
        {
            // Add new taxonomy, make it hierarchical (like categories)
            $speakers_slug = ( isset( $tc_general_settings['speakers_category_slug'] ) && !empty($tc_general_settings['speakers_category_slug']) ? $tc_general_settings['speakers_category_slug'] : 'tc-speakers-archive' );
            $labels = array(
                'name'              => _x( 'Speaker Categories', 'taxonomy general name', 'tcsp' ),
                'singular_name'     => _x( 'Speaker Categories', 'taxonomy singular name', 'tcsp' ),
                'search_items'      => __( 'Search Speaker Categories', 'tcsp' ),
                'all_items'         => __( 'All Speakers', 'tcsp' ),
                'parent_item'       => __( 'Parent Speakers', 'tcsp' ),
                'parent_item_colon' => __( 'Parent Speakers:', 'tcsp' ),
                'edit_item'         => __( 'Edit Speaker', 'tcsp' ),
                'update_item'       => __( 'Update Speaker Category', 'tcsp' ),
                'add_new_item'      => __( 'Add New Speaker Category', 'tcsp' ),
                'new_item_name'     => __( 'New Speaker Name', 'tcsp' ),
                'menu_name'         => __( 'Speaker', 'tcsp' ),
            );
            $args = array(
                'hierarchical'      => true,
                'labels'            => $labels,
                'show_ui'           => true,
                'show_admin_column' => true,
                'query_var'         => true,
                'rewrite'           => array(
                'slug' => $speakers_slug,
            ),
            );
            register_taxonomy( 'tc_speakers_taxonomy', array( 'tc_speakers' ), $args );
        }
        
        //add menu in the backend
        function tc_settings_speakers_new_menu( $menus )
        {
            $menus['tc_speakers'] = __( 'Speakers', 'tcsp' );
            return $menus;
        }
        
        /**
         * Loads admin settings page for the add-on
         */
        function tc_settings_menu_tickera_speakers()
        {
            require_once $this->plugin_dir . 'includes/admin-pages/speakers_settings.php';
        }
        
        /**
         * Gets add-on settings
         * @return type
         */
        public static function get_settings()
        {
            $tc_speakers_settings = get_option( 'tc_speakers_settings' );
            return $tc_speakers_settings;
        }
        
        function tc_speakers_post_type_args( $args )
        {
            global  $tc ;
            $tc_speaker_settings = get_option( 'tc_speakers_settings', false );
            $speakers_slug = ( isset( $tc_speaker_settings['speakers_slug'] ) && !empty($tc_speaker_settings['speakers_slug']) ? $tc_speaker_settings['speakers_slug'] : 'tc-speakers' );
            $args['menu_position'] = $tc->admin_menu_position;
            $args['show_ui'] = true;
            $args['has_archive'] = true;
            $args['rewrite'] = array(
                'slug'       => $speakers_slug,
                'with_front' => false,
            );
            $args['supports'] = array( 'title', 'editor', 'thumbnail' );
            return $args;
        }
        
        //add speaker social links to single post
        function tc_add_speakers_info( $content )
        {
            
            if ( is_single() ) {
                //speakers info meta
                $speaker_website = get_post_meta( get_the_ID(), 'speaker_website', true );
                $speaker_facebook = get_post_meta( get_the_ID(), 'speaker_facebook', true );
                $speaker_twitter = get_post_meta( get_the_ID(), 'speaker_twitter', true );
                $speaker_linkedin = get_post_meta( get_the_ID(), 'speaker_linkedin', true );
                $speaker_youtube = get_post_meta( get_the_ID(), 'speaker_youtube', true );
                $speaker_vimeo = get_post_meta( get_the_ID(), 'speaker_vimeo', true );
                $speaker_instagram = get_post_meta( get_the_ID(), 'speaker_instagram', true );
                $speaker_pinterest = get_post_meta( get_the_ID(), 'speaker_pinterest', true );
                $content = '<div class="tc-speakers-social-single">
                    ' . (( $speaker_facebook !== '' ? '<a href="' . $speaker_facebook . '" ><i class="fa fa-facebook-square" aria-hidden="true"></i></a>' : '' )) . '
                    ' . (( $speaker_twitter !== '' ? '<a href="' . $speaker_twitter . '" ><i class="fa fa-twitter" aria-hidden="true"></i></a>' : '' )) . '
                    ' . (( $speaker_linkedin !== '' ? '<a href="' . $speaker_linkedin . '" ><i class="fa fa-linkedin" aria-hidden="true"></i></a>' : '' )) . '
                    ' . (( $speaker_youtube !== '' ? '<a href="' . $speaker_youtube . '" ><i class="fa fa-youtube" aria-hidden="true"></i></a>' : '' )) . '
                    ' . (( $speaker_vimeo !== '' ? '<a href="' . $speaker_vimeo . '" ><i class="fa fa-vimeo" aria-hidden="true"></i></a>' : '' )) . '
                    ' . (( $speaker_instagram !== '' ? '<a href="' . $speaker_instagram . '" ><i class="fa fa-instagram" aria-hidden="true"></i></a>' : '' )) . '
                    ' . (( $speaker_pinterest !== '' ? '<a href="' . $speaker_pinterest . '" ><i class="fa fa-pinterest" aria-hidden="true"></i></a>' : '' )) . '
                    ' . (( $speaker_website !== '' ? '<a href="' . $speaker_website . '" ><i class="fa fa-link" aria-hidden="true"></i></a>' : '' )) . '
                    </div> <!-- .tc-speakers-social -->' . $content;
            }
            
            return $content;
        }
        
        /**
         * Rename post title to Speaker Name
         */
        function enter_title_here( $enter_title_here, $post )
        {
            if ( get_post_type( $post ) == 'tc_speakers' ) {
                $enter_title_here = __( 'Speaker Name', 'tcsp' );
            }
            return $enter_title_here;
        }
        
        /**
         * Replace feature image title > "Speaker Photo"
         */
        function replace_featured_image_title()
        {
            remove_meta_box( 'postimagediv', 'tc_speakers', 'side' );
            add_meta_box(
                'postimagediv',
                __( 'Speaker Photo', 'tcsp' ),
                'post_thumbnail_meta_box',
                'tc_speakers',
                'side',
                'low'
            );
        }
        
        function save_metabox_values( $post_id )
        {
            
            if ( get_post_type( $post_id ) == 'tc_speakers' ) {
                $metas = array();
                foreach ( $_POST as $field_name => $field_value ) {
                    if ( preg_match( '/_post_meta/', $field_name ) ) {
                        $metas[sanitize_key( str_replace( '_post_meta', '', $field_name ) )] = sanitize_text_field( $field_value );
                    }
                    $metas = apply_filters( 'tc_speakers_metas', $metas );
                    if ( isset( $metas ) ) {
                        foreach ( $metas as $key => $value ) {
                            update_post_meta( $post_id, $key, $value );
                        }
                    }
                }
            } else {
                if ( get_post_type( $post_id ) == 'tc_events' ) {
                    if ( !isset( $_POST['tc_speakers_post_meta'] ) ) {
                        update_post_meta( $post_id, 'tc_speakers', '' );
                    }
                }
            }
        
        }
        
        function tc_add_speakers( $content )
        {
            $tc_speakers_show_type = get_post_meta( get_the_ID(), 'tc_speakers_show_type', true );
            global  $post, $post_type ;
            if ( !is_admin() && $post_type == 'tc_events' ) {
                if ( $tc_speakers_show_type == 'automatic' ) {
                    include plugin_dir_path( __FILE__ ) . '/templates/shortcode-event-speakers.php';
                }
            }
            return $content;
        }
        
        function add_meta_boxes()
        {
            global  $pagenow, $typenow, $post ;
            add_meta_box(
                'tc-speakers-speaker-title',
                __( 'Speaker Title', 'tcsp' ),
                'TC_Speakers::tc_speakers_speaker_title',
                'tc_speakers',
                'side',
                'low'
            );
            add_meta_box(
                'tc-speakers-social-profiles',
                __( 'Speaker Website & Social Profiles', 'tcsp' ),
                'TC_Speakers::tc_speakers_social_profiles',
                'tc_speakers',
                'normal',
                'low'
            );
            add_meta_box(
                'tc-speakers',
                __( 'Speakers', 'tcsp' ),
                'TC_Speakers::get_speakers_select',
                'tc_events',
                'side',
                'low'
            );
        }
        
        public static function tc_speakers_speaker_title()
        {
            global  $post ;
            $speaker_title = get_post_meta( $post->ID, 'speaker_title', true );
            ?>
            <input type="text" name="speaker_title_post_meta" value="<?php 
            echo  esc_attr( $speaker_title ) ;
            ?>" placeholder="<?php 
            echo  esc_attr( __( 'Director, CEO, President, HR,... ', 'tcsp' ) ) ;
            ?>" />
            <?php 
        }
        
        function tc_ajax_load_speaker()
        {
            ob_start();
            $tc_speaker_id = $_POST['tc_speaker_id'];
            $tc_speaker = get_post( $_POST['tc_speaker_id'] );
            $speaker_website = get_post_meta( $tc_speaker_id, 'speaker_website', true );
            $speaker_facebook = get_post_meta( $tc_speaker_id, 'speaker_facebook', true );
            $speaker_twitter = get_post_meta( $tc_speaker_id, 'speaker_twitter', true );
            $speaker_linkedin = get_post_meta( $tc_speaker_id, 'speaker_linkedin', true );
            $speaker_youtube = get_post_meta( $tc_speaker_id, 'speaker_youtube', true );
            $speaker_vimeo = get_post_meta( $tc_speaker_id, 'speaker_vimeo', true );
            $speaker_instagram = get_post_meta( $tc_speaker_id, 'speaker_instagram', true );
            $speaker_pinterest = get_post_meta( $tc_speaker_id, 'speaker_pinterest', true );
            echo  "<div class='tc-speaker-featured-image-popup'>" . get_the_post_thumbnail( $tc_speaker->ID, 'large' ) . '<button title="' . __( 'Close (Esc)', 'tcsp' ) . '" type="button" class="mfp-close mfp-close-in-featured">×</button></div>' ;
            echo  "<div class='tc-popup-content-wrap'>" ;
            echo  "<div class='tc-speaker-title-popup'><h3>" . get_the_title( $tc_speaker->ID ) . "</h3></div>" ;
            echo  '<div class="tc-speakers-social-single-popup">
                    ' . (( $speaker_facebook !== '' ? '<a href="' . $speaker_facebook . '" ><i class="fa fa-facebook-square" aria-hidden="true"></i></a>' : '' )) . '
                    ' . (( $speaker_twitter !== '' ? '<a href="' . $speaker_twitter . '" ><i class="fa fa-twitter" aria-hidden="true"></i></a>' : '' )) . '
                    ' . (( $speaker_linkedin !== '' ? '<a href="' . $speaker_linkedin . '" ><i class="fa fa-linkedin" aria-hidden="true"></i></a>' : '' )) . '
                    ' . (( $speaker_youtube !== '' ? '<a href="' . $speaker_youtube . '" ><i class="fa fa-youtube" aria-hidden="true"></i></a>' : '' )) . '
                    ' . (( $speaker_vimeo !== '' ? '<a href="' . $speaker_vimeo . '" ><i class="fa fa-vimeo" aria-hidden="true"></i></a>' : '' )) . '
                    ' . (( $speaker_instagram !== '' ? '<a href="' . $speaker_instagram . '" ><i class="fa fa-instagram" aria-hidden="true"></i></a>' : '' )) . '
                    ' . (( $speaker_pinterest !== '' ? '<a href="' . $speaker_pinterest . '" ><i class="fa fa-pinterest" aria-hidden="true"></i></a>' : '' )) . '
                    ' . (( $speaker_website !== '' ? '<a href="' . $speaker_website . '" ><i class="fa fa-link" aria-hidden="true"></i></a>' : '' )) . '
                </div> <!-- .tc-speakers-social -->' ;
            echo  '<div class="tc-content-popup"><p>' . $tc_speaker->post_content . '</p></div>' ;
            echo  '</div>' ;
            exit;
        }
        
        public static function tc_speakers_social_profiles()
        {
            global  $post ;
            $speaker_website = get_post_meta( $post->ID, 'speaker_website', true );
            $speaker_facebook = get_post_meta( $post->ID, 'speaker_facebook', true );
            $speaker_twitter = get_post_meta( $post->ID, 'speaker_twitter', true );
            $speaker_likedin = get_post_meta( $post->ID, 'speaker_linkedin', true );
            $speaker_youtube = get_post_meta( $post->ID, 'speaker_youtube', true );
            $speaker_vimeo = get_post_meta( $post->ID, 'speaker_vimeo', true );
            $speaker_instagram = get_post_meta( $post->ID, 'speaker_instagram', true );
            $speaker_pinterest = get_post_meta( $post->ID, 'speaker_pinterest', true );
            ?>

            <label><span class="label_title"><?php 
            _e( 'Website URL', 'tcsp' );
            ?></span>
                <input type="text" name="speaker_website_post_meta" value="<?php 
            echo  esc_attr( $speaker_website ) ;
            ?>" placeholder="<?php 
            echo  esc_attr( __( 'http://www.example.com/', 'tcsp' ) ) ;
            ?>" />
            </label>

            <label><span class="label_title"><?php 
            _e( 'Facebook', 'tcsp' );
            ?></span>
                <input type="text" name="speaker_facebook_post_meta" value="<?php 
            echo  esc_attr( $speaker_facebook ) ;
            ?>" placeholder="<?php 
            echo  esc_attr( __( 'https://www.facebook.com/speaker-name', 'tcsp' ) ) ;
            ?>" />
            </label>

            <label><span class="label_title"><?php 
            _e( 'Twitter', 'tcsp' );
            ?></span>
                <input type="text" name="speaker_twitter_post_meta" value="<?php 
            echo  esc_attr( $speaker_twitter ) ;
            ?>" placeholder="<?php 
            echo  esc_attr( __( 'https://twitter.com/speaker-name', 'tcsp' ) ) ;
            ?>" />
            </label>

            <label><span class="label_title"><?php 
            _e( 'LinkedIn', 'tcsp' );
            ?></span>
                <input type="text" name="speaker_linkedin_post_meta" value="<?php 
            echo  esc_attr( $speaker_likedin ) ;
            ?>" placeholder="<?php 
            echo  esc_attr( __( 'https://rs.linkedin.com/in/speaker-name', 'tcsp' ) ) ;
            ?>" />
            </label>

            <label><span class="label_title"><?php 
            _e( 'Youtube', 'tcsp' );
            ?></span>
                <input type="text" name="speaker_youtube_post_meta" value="<?php 
            echo  esc_attr( $speaker_youtube ) ;
            ?>" placeholder="<?php 
            echo  esc_attr( __( 'https://www.youtube.com/user/speaker-name', 'tcsp' ) ) ;
            ?>" />
            </label>

            <label><span class="label_title"><?php 
            _e( 'Vimeo', 'tcsp' );
            ?></span>
                <input type="text" name="speaker_vimeo_post_meta" value="<?php 
            echo  esc_attr( $speaker_vimeo ) ;
            ?>" placeholder="<?php 
            echo  esc_attr( __( 'https://vimeo.com/speaker-name', 'tcsp' ) ) ;
            ?>" />
            </label>

            <label><span class="label_title"><?php 
            _e( 'Instagram', 'tcsp' );
            ?></span>
                <input type="text" name="speaker_instagram_post_meta" value="<?php 
            echo  esc_attr( $speaker_instagram ) ;
            ?>" placeholder="<?php 
            echo  esc_attr( __( 'https://www.instagram.com/speaker-name/', 'tcsp' ) ) ;
            ?>" />
            </label>

            <label><span class="label_title"><?php 
            _e( 'Pinterest', 'tcsp' );
            ?></span>
                <input type="text" name="speaker_pinterest_post_meta" value="<?php 
            echo  esc_attr( $speaker_pinterest ) ;
            ?>" placeholder="<?php 
            echo  esc_attr( __( 'https://www.pinterest.com/speaker-name/', 'tcsp' ) ) ;
            ?>" />
            </label>
            <?php 
        }
        
        function front_scripts_and_styles()
        {
            $tc_speakers_settings = get_option( 'tc_speakers_settings' );
            
            if ( apply_filters( 'tc_speakers_use_default_front_css', true ) == true ) {
                wp_enqueue_style(
                    $this->name . '-front',
                    $this->plugin_url . 'css/front.css',
                    array(),
                    $this->version
                );
                wp_enqueue_style(
                    $this->name . '-nice',
                    $this->plugin_url . 'css/nice-speakers.css',
                    array(),
                    $this->version
                );
                wp_enqueue_style(
                    $this->name . '-flexslider',
                    $this->plugin_url . 'includes/flexslider/flexslider.css',
                    array(),
                    $this->version
                );
                
                if ( $tc_speakers_settings['show_popup'] == 'yes' ) {
                    wp_enqueue_style(
                        $this->name . '-magnific-popup',
                        $this->plugin_url . 'includes/magnific-popup/dist/magnific-popup.css',
                        array(),
                        $this->version
                    );
                    wp_enqueue_script(
                        $this->name . '-magnific-popup',
                        $this->plugin_url . 'includes/magnific-popup/dist/jquery.magnific-popup.min.js',
                        array(),
                        '1.0.0',
                        true
                    );
                }
                
                wp_enqueue_script(
                    $this->name . '-isotope',
                    $this->plugin_url . 'includes/isotope-master/dist/isotope.pkgd.min.js',
                    array(),
                    '1.0.0',
                    true
                );
                wp_enqueue_script(
                    $this->name . '-flexslider',
                    $this->plugin_url . 'includes/flexslider/jquery.flexslider-min.js',
                    array(),
                    '1.0.0',
                    true
                );
                wp_register_script(
                    $this->name . '-front-js',
                    $this->plugin_url . 'js/front.js',
                    array(),
                    '1.0.0',
                    true
                );
                $tc_speakers_view = get_post_meta( get_the_ID(), 'tc_speakers_view', true );
                $tc_event_parameters = array(
                    'tc_speakers_view'  => $tc_speakers_view,
                    'ajaxurl'           => admin_url( 'admin-ajax.php', ( is_ssl() ? 'https' : 'http' ) ),
                    'tc_speakers_popup' => $tc_speakers_settings['show_popup'],
                );
                wp_localize_script( $this->name . '-front-js', 'tc_event_parameters', $tc_event_parameters );
                wp_enqueue_script( $this->name . '-front-js' );
            }
        
        }
        
        function register_gutenberg_blocks_speakers()
        {
            register_block_type( 'tickera/event-speakers', array(
                'editor_script'   => 'tc_event_add_speakers',
                'editor_style'    => 'tc_event_add_speakers',
                'render_callback' => array( $this, 'tc_speakers_page' ),
                'attributes'      => array(
                'event_id'         => array(
                'type' => 'string',
            ),
                'speakers_display' => array(
                'type' => 'string',
            ),
                'grid_count'       => array(
                'type' => 'string',
            ),
                'show_categories'  => array(
                'type' => 'string',
            ),
            ),
            ) );
        }
        
        /**
         * Register extra scripts needed.
         */
        function register_extra_scripts_speakers()
        {
            $wp_tickets_search = new TC_Tickets_Search( '', '', -1 );
            $ticket_types = array();
            $ticket_types[] = array( 0, '' );
            foreach ( $wp_tickets_search->get_results() as $ticket_type ) {
                $ticket = new TC_Ticket( $ticket_type->ID );
                $ticket_types[] = array( $ticket_type->ID, $ticket->details->post_title );
            }
            $wp_events_search = new TC_Events_Search( '', '', -1 );
            $events = array();
            $events[] = array( 0, '' );
            foreach ( $wp_events_search->get_results() as $event_item ) {
                $event = new TC_Ticket( $event_item->ID );
                $events[] = array( $event_item->ID, $event->details->post_title );
            }
            wp_register_script(
                'tc_speakers_block_editor',
                plugins_url( 'gutenberg/tc_speakers_block_editor.js', __FILE__ ),
                array(
                'wp-editor',
                'wp-blocks',
                'wp-i18n',
                'wp-element',
                'jquery'
            ),
                $this->version
            );
            wp_localize_script( 'tc_speakers_block_editor', 'tc_event_add_speakers', array(
                'events' => json_encode( $events ),
            ) );
            wp_enqueue_script( 'tc_speakers_block_editor' );
            $tc_speakers_settings = get_option( 'tc_speakers_settings' );
            
            if ( apply_filters( 'tc_speakers_use_default_front_css', true ) == true ) {
                wp_enqueue_style(
                    $this->name . '-front',
                    $this->plugin_url . 'css/front.css',
                    array(),
                    $this->version
                );
                wp_enqueue_style(
                    $this->name . '-nice',
                    $this->plugin_url . 'css/nice-speakers.css',
                    array(),
                    $this->version
                );
                wp_enqueue_style(
                    $this->name . '-flexslider',
                    $this->plugin_url . 'includes/flexslider/flexslider.css',
                    array(),
                    $this->version
                );
                
                if ( $tc_speakers_settings['show_popup'] == 'yes' ) {
                    wp_enqueue_style(
                        $this->name . '-magnific-popup',
                        $this->plugin_url . 'includes/magnific-popup/dist/magnific-popup.css',
                        array(),
                        $this->version
                    );
                    wp_enqueue_script(
                        $this->name . '-magnific-popup',
                        $this->plugin_url . 'includes/magnific-popup/dist/jquery.magnific-popup.min.js',
                        array(),
                        '1.0.0',
                        true
                    );
                }
                
                wp_enqueue_script(
                    $this->name . '-isotope',
                    $this->plugin_url . 'includes/isotope-master/dist/isotope.pkgd.min.js',
                    array(),
                    '1.0.0',
                    true
                );
                wp_enqueue_script(
                    $this->name . '-flexslider',
                    $this->plugin_url . 'includes/flexslider/jquery.flexslider-min.js',
                    array(),
                    '1.0.0',
                    true
                );
                wp_register_script(
                    $this->name . '-front-js',
                    $this->plugin_url . 'js/front.js',
                    array(),
                    '1.0.0',
                    true
                );
                $tc_speakers_view = get_post_meta( get_the_ID(), 'tc_speakers_view', true );
                $tc_event_parameters = array(
                    'tc_speakers_view'  => $tc_speakers_view,
                    'ajaxurl'           => admin_url( 'admin-ajax.php', ( is_ssl() ? 'https' : 'http' ) ),
                    'tc_speakers_popup' => $tc_speakers_settings['show_popup'],
                );
                wp_localize_script( $this->name . '-front-js', 'tc_event_parameters', $tc_event_parameters );
                wp_enqueue_script( $this->name . '-front-js' );
            }
        
        }
        
        function tc_speaker_image_size()
        {
            add_image_size(
                'tc-speakers-size',
                500,
                500,
                true
            );
            add_image_size(
                'tc-speakers-slider',
                1200,
                600,
                true
            );
        }
        
        function admin_enqueue_scripts_and_styles()
        {
            global  $post, $post_type, $tc ;
            
            if ( $post_type == 'tc_speakers' || $post_type == 'tc_events' || isset( $_GET['tab'] ) && $_GET['tab'] == 'tc_speakers' ) {
                wp_enqueue_style( $this->name . '-admin', $this->plugin_url . 'css/admin.css' );
                wp_enqueue_script( $this->name . '-speakers-admin', plugins_url( 'js/admin.js', __FILE__ ) );
                wp_enqueue_style(
                    $this->name . '-chosen',
                    $tc->plugin_url . 'css/chosen.min.css',
                    array(),
                    $tc->version
                );
                wp_enqueue_script(
                    $this->name . '-chosen',
                    $tc->plugin_url . 'js/chosen.jquery.min.js',
                    array( $tc->name . 'chosen-admin' ),
                    false,
                    false
                );
            }
            
            wp_enqueue_script( $this->name . '-options-limit', plugins_url( 'js/options-limit.js', __FILE__ ) );
        }
        
        /**
         * Get speakers drop down select
         * @param type $field_name
         * @param type $post_id
         */
        public static function get_speakers_select()
        {
            global  $tc, $post ;
            $currently_selected = get_post_meta( $post->ID, 'tc_speakers', true );
            $speakers = get_posts( array(
                'posts_per_page' => -1,
                'post_status'    => 'publish',
                'post_type'      => 'tc_speakers',
            ) );
            ?>
            <select name="tc_speakers_post_meta[]" class="tc_speakers" multiple="true">
                <?php 
            foreach ( $speakers as $speaker ) {
                ?>
                    <option value="<?php 
                echo  (int) $speaker->ID ;
                ?>" <?php 
                echo  ( is_array( $currently_selected ) && in_array( $speaker->ID, $currently_selected ) ? 'selected' : '' ) ;
                ?>><?php 
                echo  $speaker->post_title ;
                ?></option>
                    <?php 
            }
            ?>
            </select>

            <?php 
            $tc_speakers_show_type = get_post_meta( $post->ID, 'tc_speakers_show_type', true );
            $tc_speakers_view = get_post_meta( $post->ID, 'tc_speakers_view', true );
            $tc_speakers_grid_num = get_post_meta( $post->ID, 'tc_speakers_grid_num', true );
            $tc_speakers_grid_show_cats = get_post_meta( $post->ID, 'tc_speakers_grid_show_cats', true );
            if ( $tc_speakers_show_type == '' || !isset( $tc_speakers_show_type ) ) {
                $tc_speakers_show_type = 'automatic';
            }
            $tc_speakers_display = apply_filters( 'tc_speakers_display', array(
                'tc_grid'   => __( 'Grid', 'tcsp' ),
                'tc_slider' => __( 'Slider', 'tcsp' ),
                'tc_list'   => __( 'List', 'tcsp' ),
            ) );
            ?>
            <div class="tc_speakers_show_type"><input type="radio" name="tc_speakers_show_type_post_meta" value="automatic" <?php 
            checked( $tc_speakers_show_type, 'automatic', true );
            ?>><?php 
            _e( 'Show automatically', 'tcsp' );
            ?></div>
            <div class="tc_speakers_show_type"><input type="radio" name="tc_speakers_show_type_post_meta" value="shortcode" <?php 
            checked( $tc_speakers_show_type, 'shortcode', true );
            ?>><?php 
            _e( 'Show via shortcode [tc_speakers]', 'tcsp' );
            ?></div>

            <label><?php 
            _e( 'Display:', 'tcsp' );
            ?> </label>
            <br />
            
            <select name="tc_speakers_view_post_meta" class="tc_speakers"  id="tc_speakers">
                <?php 
            foreach ( $tc_speakers_display as $tc_key => $tc_speakers_display_single ) {
                ?>
                    <option value="<?php 
                echo  $tc_key ;
                ?>" <?php 
                if ( $tc_key == $tc_speakers_view ) {
                    echo  "selected" ;
                }
                ?>><?php 
                echo  $tc_speakers_display_single ;
                ?></option>
                    <?php 
            }
            ?>
            </select>
            
           
            <select name="tc_speakers_grid_num_post_meta" class="tc_speakers_grid" id="tc_speakers_grid">
                <option value="2" <?php 
            if ( $tc_speakers_grid_num == '2' ) {
                echo  "selected" ;
            }
            ?>>2</option>
                <option value="3" <?php 
            if ( $tc_speakers_grid_num == '3' ) {
                echo  "selected" ;
            }
            ?>>3</option>
                <option value="4" <?php 
            if ( $tc_speakers_grid_num == '4' ) {
                echo  "selected" ;
            }
            ?>>4</option>
            </select>

            <div class="clear"></div>
            <div class="tc-categories-wrap" id="tc-categories-wrap">
                <label><?php 
            _e( 'Show Categories', 'tcsp' );
            ?></label>
                <div class="tc-input-radio-wrap"><input type="radio" <?php 
            echo  ( $tc_speakers_grid_show_cats == 'yes' || empty($tc_speakers_grid_show_cats) ? 'checked' : '' ) ;
            ?> name="tc_speakers_grid_show_cats_post_meta" value="yes"><?php 
            _e( 'Yes', 'tcsp' );
            ?></div>
                <div class="tc-input-radio-wrap"><input type="radio" <?php 
            echo  ( $tc_speakers_grid_show_cats == 'no' ? 'checked' : '' ) ;
            ?> name="tc_speakers_grid_show_cats_post_meta" value="no"><?php 
            _e( 'No', 'tcsp' );
            ?></div>
            </div><!-- .tc-categories-wrap -->

            <?php 
            do_action( 'tc_speakers_meta_fields' );
            ?>
            
            <?php 
        }
        
        public static function tc_speakers_page( $atts )
        {
            global  $tc ;
            global  $content ;
            ob_start();
            include plugin_dir_path( __FILE__ ) . '/templates/shortcode-event-speakers.php';
            $output = ob_get_clean();
            return $output;
        }
        
        public static function tc_speakers_excerpt( $length )
        {
            
            if ( get_post_type() == 'tc_speakers' ) {
                return 10;
            } else {
                return $length;
            }
        
        }
    
    }
    //$TC_speakers = new TC_Speakers();
}

/**
 * Flush permalinks uppon plugin activation
 */
function tc_speakers_activation()
{
    flush_rewrite_rules();
}

register_activation_hook( __FILE__, 'tc_speakers_activation' );
if ( !function_exists( 'is_plugin_active_for_network' ) ) {
    require_once ABSPATH . '/wp-admin/includes/plugin.php';
}

if ( is_multisite() && is_plugin_active_for_network( plugin_basename( __FILE__ ) ) ) {
    function tc_speakers_reader_load()
    {
        global  $TC_speakers ;
        $TC_speakers = new TC_Speakers();
    }
    
    add_action( 'tets_fs_loaded', 'tc_speakers_reader_load' );
} else {
    $TC_speakers = new TC_Speakers();
}

if ( !function_exists( 'tcspeakers_fs' ) ) {
    // Create a helper function for easy SDK access.
    function tcspeakers_fs()
    {
        global  $tcspeakers_fs ;
        
        if ( !isset( $tcspeakers_fs ) ) {
            // Activate multisite network integration.
            if ( !defined( 'WP_FS__PRODUCT_4045_MULTISITE' ) ) {
                define( 'WP_FS__PRODUCT_4045_MULTISITE', true );
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
            
            $tcspeakers_fs = fs_dynamic_init( array(
                'id'               => '4045',
                'slug'             => 'speakers',
                'premium_slug'     => 'speakers',
                'type'             => 'plugin',
                'public_key'       => 'pk_038a5718ecf0b3aec527990a52b70',
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
        
        return $tcspeakers_fs;
    }

}
function tcspeakers_fs_is_parent_active_and_loaded()
{
    // Check if the parent's init SDK method exists.
    return function_exists( 'tets_fs' );
}

function tcspeakers_fs_is_parent_active()
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

function tcspeakers_fs_init()
{
    
    if ( tcspeakers_fs_is_parent_active_and_loaded() ) {
        // Init Freemius.
        tcspeakers_fs();
        // Parent is active, add your init code here.
    } else {
        // Parent is inactive, add your error handling here.
    }

}


if ( tcspeakers_fs_is_parent_active_and_loaded() ) {
    // If parent already included, init add-on.
    tcspeakers_fs_init();
} else {
    
    if ( tcspeakers_fs_is_parent_active() ) {
        // Init add-on only after the parent is loaded.
        add_action( 'tets_fs_loaded', 'tcspeakers_fs_init' );
    } else {
        // Even though the parent is not activated, execute add-on for activation / uninstall hooks.
        tcspeakers_fs_init();
    }

}

function show_tc_speakers_attributes()
{
    global  $post ;
    ?>
	<table id="tc-speakers-shortcode" class="shortcode-table" style="display:none">
			<?php 
    
    if ( $post->post_type !== 'tc_events' ) {
        ?>
				<tr>
					<th scope="row"><?php 
        _e( 'Event', 'tc' );
        ?></th>
					<td>
						<select name="event_id">
							<?php 
        $wp_events_search = new TC_Events_Search( '', '', -1 );
        foreach ( $wp_events_search->get_results() as $event ) {
            $event = new TC_Event( $event->ID );
            ?>
								<option value="<?php 
            echo  esc_attr( $event->details->ID ) ;
            ?>"><?php 
            echo  $event->details->post_title ;
            ?></option>
								<?php 
        }
        ?>
						</select>
					</td>
				</tr>
				<?php 
    } else {
        ?>
				<tr>
					<th scope="row"><?php 
        _e( 'Event', 'tc' );
        ?></th>
					<td><?php 
        _e( 'Current Event', 'tc' );
        ?></td>
				</tr>
				<?php 
    }
    
    ?>
		</table>

<?php 
}
