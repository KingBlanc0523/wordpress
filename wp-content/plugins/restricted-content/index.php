<?php
/*
  Plugin Name: Restricted Content
  Plugin URI: https://tickera.com/
  Description: Restrict content easily to logged in users, users with a specific role or capability, Tickera or WooCommerce users who made any purchase or purchased a specific product.
  Author: Tickera.com
  Author URI: https://tickera.com/
  Version: 1.0.3
  Text Domain: rsc
  Domain Path: languages

  Copyright 2018 Tickera (https://tickera.com/)

 */

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

if (!class_exists('Restricted_Content')) {

    class Restricted_Content {

        var $version = '1.0.3';
        var $title = 'Restricted Content';
        var $name = 'rsc';
        var $dir_name = 'restricted-content';
        var $location = 'plugins';
        var $plugin_dir = '';
        var $plugin_url = '';

        function __construct() {
            $this->init_vars();

            include($this->plugin_dir . '/includes/admin-settings.php');

            add_action('plugins_loaded', array($this, 'localization'), 9);
            add_action('admin_enqueue_scripts', array($this, 'admin_header'));
            add_action('add_meta_boxes', array($this, 'add_metabox'));
            add_action('save_post', array($this, 'save_metabox_values'));

            add_filter('the_content', array($this, 'maybe_block_content'));
            add_filter('tc_the_content', array($this, 'maybe_block_content'));
            add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'plugin_action_link'), 10, 2);

            add_shortcode('RSC', array($this, 'rsc_shortcode'));
        }

        /**
         * setup proper directories
         * @global type $tc_plugin_dir
         * @global type $tc_plugin_url
         */
        function init_vars() {

            if (defined('WP_PLUGIN_URL') && defined('WP_PLUGIN_DIR') && file_exists(WP_PLUGIN_DIR . '/' . $this->dir_name . '/' . basename(__FILE__))) {
                $this->location = 'subfolder-plugins';
                $this->plugin_dir = WP_PLUGIN_DIR . '/' . $this->dir_name . '/';
                $this->plugin_url = plugins_url('/', __FILE__);
            } else if (defined('WP_PLUGIN_URL') && defined('WP_PLUGIN_DIR') && file_exists(WP_PLUGIN_DIR . '/' . basename(__FILE__))) {
                $this->location = 'plugins';
                $this->plugin_dir = WP_PLUGIN_DIR . '/';
                $this->plugin_url = plugins_url('/', __FILE__);
            } else if (is_multisite() && defined('WPMU_PLUGIN_URL') && defined('WPMU_PLUGIN_DIR') && file_exists(WPMU_PLUGIN_DIR . '/' . basename(__FILE__))) {
                $this->location = 'mu-plugins';
                $this->plugin_dir = WPMU_PLUGIN_DIR;
                $this->plugin_url = WPMU_PLUGIN_URL;
            } else {
                wp_die(sprintf(__('There was an issue determining where %s is installed. Please reinstall it.', 'rsc'), $this->title));
            }
        }

        function plugin_action_link($links, $file) {
            $settings_link = '<a href = "' . admin_url('options-general.php?page=restricted-content-settings') . '">' . __('Settings', 'tc') . '</a>';
            array_unshift($links, $settings_link);
            return $links;
        }

        /**
         * Plugin localization
         */
        function localization() {

            if ($this->location == 'mu-plugins') {
                load_muplugin_textdomain('rsc', 'languages/');
            } else if ($this->location == 'subfolder-plugins') {
                load_plugin_textdomain('rsc', false, dirname(plugin_basename(__FILE__)) . '/languages/');
            } else if ($this->location == 'plugins') {
                load_plugin_textdomain('rsc', false, 'languages/');
            } else {

            }

            $temp_locales = explode('_', get_locale());
            $this->language = ($temp_locales[0]) ? $temp_locales[0] : 'en';
        }

        /**
         * Blocks content if needed
         * Calls RSC shortcode to check if the block is needed (check rsc_shortcode method)
         * @global type $post
         * @param type $content
         * @return type
         */
        function maybe_block_content($content) {
            global $post;

            if (!is_admin() && isset($post)) {//make sure that we restrict the content only on the front-end
                {
                    $rsc_content_availability = get_post_meta($post->ID, '_rsc_content_availability', true);
                    if (empty($rsc_content_availability)) {
                        $rsc_content_availability = 'everyone';
                    }

                    if ($rsc_content_availability !== 'everyone') {
                        //Content shouldn't be available to everyone so we need to restrict it
                        $message = do_shortcode('[RSC post_id="' . $post->ID . '" type="' . $rsc_content_availability . '"]');
                        if ($message) {
                            $content = $message;
                        }
                    }
                }
            }

            return $content;
        }

        /**
         * Get a user role from current user
         * @global type $current_user
         * @return boolean
         */
        function get_current_user_role() {
            if (is_user_logged_in()) {
                global $current_user;
                $user_role = $current_user->roles[0];
                return $user_role;
            }
            return false;
        }

        /**
         * Restriction shortcode
         * This one does the all hard work ;)
         * @param type $atts
         * @return boolean
         */
        function rsc_shortcode($atts) {
            extract(
                    shortcode_atts(
                            array(
                'post_id' => false,
                'type' => 'everyone'
                            )
                            , $atts)
            );

            $message = false;

            $allowed_to_admins_capability = apply_filters('rsc_allowed_to_admins_capability', 'manage_options');

            if ($post_id && $type !== 'everyone' && !current_user_can($allowed_to_admins_capability)) {
                $rsc_settings = get_option('rsc_settings');

                switch ($type) {
                    case 'logged_in'://only logged in users should have access to the content
                        if (!is_user_logged_in()) {
                            $logged_in_message = apply_filters('rsc_logged_in_message', isset($rsc_settings['logged_in_message']) ? esc_html($rsc_settings['logged_in_message']) : esc_html(__('You must log in to view this content', 'rsc')), $post_id, $type);
                            $message = $logged_in_message;
                        } else {
                            $message = false;
                        }

                        break;
                    case 'role'://only specific user roles should have access to the content
                        $current_user_role = $this->get_current_user_role();
                        $user_role_message = apply_filters('rsc_role_message', isset($rsc_settings['user_role_message']) ? esc_html($rsc_settings['user_role_message']) : esc_html(__('You don\'t have required permissions to view this content.', 'rsc')), $post_id, $type);

                        if ($current_user_role) {

                            $rsc_user_role = get_post_meta($post_id, '_rsc_user_role', true);

                            if (is_array($rsc_user_role) && in_array($current_user_role, $rsc_user_role)) {
                                $message = false;
                            } else {
                                $message = $user_role_message;
                            }
                        } else {
                            $message = $user_role_message;
                        }

                        break;

                    case 'capability'://only users with specific capability should have access to the content
                        $required_capability = get_post_meta($post_id, '_rsc_capability', true);

                        if (!current_user_can($required_capability)) {
                            $capability_message = apply_filters('rsc_capability_message', isset($rsc_settings['capability_message']) ? esc_html($rsc_settings['capability_message']) : esc_html(__('You don\'t have required permissions to view this content.', 'rsc')), $post_id, $type);
                            $message = $capability_message;
                        } else {
                            $message = false;
                        }
                        break;
                    case 'tickera'://only Tickera users should have access to the content

                        $rsc_tickera_users = get_post_meta($post_id, '_rsc_tickera_users', true);

                        switch ($rsc_tickera_users) {
                            case 'anything'://at least one purchase of Tickera ticket is required for accessing the content

                                if (Restricted_Content::get_tickera_paid_user_orders_count() > 0) {
                                    $message = false;
                                } else {
                                    $message = apply_filters('rsc_tickera_any_ticket_type_message', isset($rsc_settings['tickera_any_ticket_type_message']) ? esc_html($rsc_settings['tickera_any_ticket_type_message']) : esc_html(__('This content is restricted to the attendees only. Please purchase ticket(s) in order to access this content.', 'rsc')), $post_id, $type);
                                }
                                break;

                            case 'event'://a purchase of at least one Tickera ticket type for a specific event is required to access the content
                                $rsc_tickera_users_event = get_post_meta($post_id, '_rsc_tickera_users_event', true);

                                if (Restricted_Content::get_tickera_paid_user_orders_count($rsc_tickera_users_event) > 0) {
                                    $message = false;
                                } else {
                                    $message = apply_filters('rsc_tickera_specific_event_message', isset($rsc_settings['tickera_specific_event_message']) ? esc_html($rsc_settings['tickera_specific_event_message']) : esc_html(__('Only attendees who purchased ticket(s) for following event(s): [rsc_tc_event] can access this content.', 'rsc')), $post_id, $type);
                                    if (preg_match('/[rsc_tc_event]/', $message)) {//show event titles only if [rsc_tc_event] is used
                                        $events_titles = array();
                                        foreach ($rsc_tickera_users_event as $rsc_tickera_users_event_key => $rsc_tickera_users_event_value) {
                                            $events_titles[] = get_the_title($rsc_tickera_users_event_value);
                                        }
                                        $message = str_replace('[rsc_tc_event]', implode(', ', $events_titles), $message);
                                    }
                                }
                                break;

                            case 'ticket_type'://a purchase of a specific ticket type is required for accessing the content

                                $rsc_tickera_users_ticket_type = get_post_meta($post_id, '_rsc_tickera_users_ticket_type', true);

                                if (Restricted_Content::get_tickera_paid_user_orders_count(false, $rsc_tickera_users_ticket_type) > 0) {
                                    $message = false;
                                } else {
                                    $message = apply_filters('rsc_tickera_specific_ticket_type_message', isset($rsc_settings['tickera_specific_ticket_type_message']) ? esc_html($rsc_settings['tickera_specific_ticket_type_message']) : esc_html(__('Only attendees who purchased following ticket type(s): [rsc_tc_ticket_type] can access this content.', 'rsc')), $post_id, $type);
                                    if (preg_match('/[rsc_tc_ticket_type]/', $message)) {//show event titles only if [rsc_tc_event] is used
                                        $ticket_types_titles = array();
                                        foreach ($rsc_tickera_users_ticket_type as $rsc_tickera_users_ticket_type_key => $rsc_tickera_users_ticket_type_value) {
                                            if (apply_filters('rsc_append_event_title_to_ticket_types_placeholder', true) == true) {
                                                $event_id = get_post_meta($rsc_tickera_users_ticket_type_value, 'event_name', true);
                                                if (empty($event_id)) {
                                                    $event_id = get_post_meta($rsc_tickera_users_ticket_type_value, '_event_name', true);
                                                }
                                                $event_title = apply_filters('rsc_event_title_ticket_types_placeholder', ' (' . get_the_title($event_id) . ' ' . __('event', 'rsc') . ')', $event_id, $rsc_tickera_users_ticket_type_value);
                                            } else {
                                                $event_title = '';
                                            }

                                            $ticket_types_titles[] = apply_filters('rsc_ticket_type_title_placeholder', get_the_title($rsc_tickera_users_ticket_type_value) . $event_title, $rsc_tickera_users_ticket_type_value);
                                        }
                                        $message = str_replace('[rsc_tc_ticket_type]', implode(', ', $ticket_types_titles), $message);
                                    }
                                }
                                break;
                        }

                        break;

                    case 'woo'://only WooCommerce users should have access to the content

                        $rsc_woo_users = get_post_meta($post_id, '_rsc_woo_users', true);

                        switch ($rsc_woo_users) {
                            case 'anything'://at least one purchase of any product is required for accessing the content
                                if (Restricted_Content::get_woo_paid_user_orders_count() > 0) {
                                    $message = false;
                                } else {
                                    $message = apply_filters('rsc_woo_any_product_message', isset($rsc_settings['woo_any_product_message']) ? esc_html($rsc_settings['woo_any_product_message']) : esc_html(__('This content is restricted to the clients only. Please purchase any product in order to access this content.', 'rsc')), $post_id, $type);
                                }
                                break;

                            case 'product'://a purchase of a specific product is required for accessing the content

                                $rsc_woo_users_product = get_post_meta($post_id, '_rsc_woo_users_product', true);

                                if (Restricted_Content::get_woo_paid_user_orders_count(false, $rsc_woo_users_product) > 0) {
                                    $message = false;
                                } else {
                                    $message = apply_filters('rsc_woo_specific_product_message', isset($rsc_settings['woo_specific_product_message']) ? esc_html($rsc_settings['woo_specific_product_message']) : esc_html(__('Only clients who purchased following product(s): [rsc_woo_product] can access this content.', 'rsc')), $post_id, $type);
                                    if (preg_match('/[rsc_woo_product]/', $message)) {//show product titles only if [rsc_woo_product] is used
                                        $product_titles = array();
                                        foreach ($rsc_woo_users_product as $rsc_woo_users_product_key => $rsc_woo_users_product_value) {
                                            $product_titles[] = apply_filters('rsc_woo_product_title_title_placeholder', get_the_title($rsc_woo_users_product_value), $rsc_woo_users_product_value);
                                        }
                                        $message = str_replace('[rsc_woo_product]', implode(', ', $product_titles), $message);
                                    }
                                }
                                break;
                        }

                        break;

                    default:
                        $message = false;
                }
            }

            if($message !== false && empty($message)){
                $message = ' ';
            }

            return !$message ? $message : '<div class="rsc_message">' . $message . '</div>'; //false means that user CAN access the content, otherwise a message will be shown (a reason why user can access content or who can access the content)
        }

        public static function get_woo_paid_user_orders_count($event_id = false, $product_id = false) {
            global $wpdb;

            $user_id = get_current_user_id();

            if ($user_id == 0) {
                return 0;
            }

            if (!$event_id && !$product_id) {//overall paid orders
                $paid_orders_count = $wpdb->get_var("SELECT COUNT(p.ID) FROM $wpdb->posts p, $wpdb->postmeta pm1, $wpdb->postmeta pm2 "
                        . "                                         WHERE p.ID = pm1.post_id AND p.ID = pm2.post_id"
                        . "                                         AND (p.post_status = 'wc-completed' OR p.post_status = 'wc-processing') "
                        . "                                         AND p.post_type = 'shop_order'");

                return (int) $paid_orders_count;
            }


            if (!$event_id && $product_id) {//paid orders for specific ticket type
                $current_user = wp_get_current_user();
                $user_email = $current_user->user_email;

                if (is_array($product_id)) {//ticket type id is actually a list of ids / array (so we need to build a bit complicated query)
                    if (count($product_id) > 1) {
                        foreach ($product_id as $product_id_key => $product_id_value) {
                            if (wc_customer_bought_product($user_email, $user_id, $product_id_value)) {
                                return 1;
                            }
                        }
                        return 0;
                    } else {//array contains only one element / ticket type id
                        if (wc_customer_bought_product($user_email, $user_id, $product_id[0])) {
                            return 1;
                        }
                    }
                }

                return 0;
            }
        }

        /**
         * Retrieves count of paid orders
         * Overall, for a specific event, for a specific ticket type
         * @global type $wpdb
         * @param type $event_id
         * @param type $ticket_type_id
         * @return type
         */
        public static function get_tickera_paid_user_orders_count($event_id = false, $ticket_type_id = false) {
            global $wpdb;

            $user_id = get_current_user_id();

            if ($user_id == 0) {
                return 0;
            }

            if (!$event_id && !$ticket_type_id) {//overall paid orders
                if (apply_filters('tc_is_woo', false) == true) {//Tickera is in the Bridge mode
                    $paid_orders_count = $wpdb->get_var("SELECT COUNT(p.ID) FROM $wpdb->posts p, $wpdb->postmeta pm1, $wpdb->postmeta pm2 "
                            . "                                         WHERE p.ID = pm1.post_id AND p.ID = pm2.post_id"
                            . "                                         AND (p.post_status = 'wc-completed' OR p.post_status = 'wc-processing') "
                            . "                                         AND p.post_type = 'shop_order'"
                            . "                                         AND pm1.meta_key = '_customer_user'"
                            . "                                         AND pm1.meta_value = $user_id"
                            . "                                         AND pm2.meta_key = 'tc_cart_info'");
                } else {
                    $paid_orders_count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(ID) FROM $wpdb->posts WHERE post_author = %d AND post_status = 'order_paid' AND post_type = 'tc_orders'", $user_id));
                }
                return $paid_orders_count;
            }


            if (!$event_id && $ticket_type_id) {//paid orders for specific ticket type
                $ticket_type_id_query_part = '';
                if (is_array($ticket_type_id)) {//ticket type id is actually a list of ids / array (so we need to build a bit complicated query)
                    if (count($ticket_type_id) > 1) {
                        $ticket_type_ids_count = count($ticket_type_id);
                        $ticket_type_id_query_part .= ' AND (';
                        $foreach_count = 1;
                        $extension = '';
                        foreach ($ticket_type_id as $ticket_type_id_key => $ticket_type_id_value) {
                            if ($ticket_type_ids_count == $foreach_count) {
                                $extension = '';
                            } else {
                                $extension = ' OR ';
                            }
                            $ticket_type_id_query_part .= " pm.meta_value  LIKE '%i:" . (int) $ticket_type_id_value . ";%' $extension";
                            $foreach_count++;
                        }
                        $ticket_type_id_query_part .= ') ';
                    } else {//array contains only one element / ticket type id
                        $ticket_type_id_query_part = " AND pm.meta_value LIKE '%i:" . (int) $ticket_type_id[0] . ";%'";
                    }
                } else {//argument is an integer (only one ticket type id)
                    $ticket_type_id_query_part = " AND pm.meta_value LIKE '%i:" . (int) $ticket_type_id . ";%'";
                }

                if (apply_filters('tc_is_woo', false) == false) {
                    $paid_orders_count = $wpdb->get_var("SELECT COUNT(p.ID) FROM $wpdb->posts p, $wpdb->postmeta pm
                                                                    WHERE p.ID = pm.post_id
                                                                    AND p.post_author = $user_id
                                                                    AND p.post_status = 'order_paid'
                                                                    AND p.post_type = 'tc_orders'
                                                                    AND pm.meta_key = 'tc_cart_contents'
                                                                    $ticket_type_id_query_part
                                                                    ");

                    return $paid_orders_count;
                } else {//Query for the Bridge for WooCommerce
                    $paid_orders_count = $wpdb->get_var("SELECT COUNT(p.ID) FROM $wpdb->posts p, $wpdb->postmeta pm, $wpdb->postmeta pm2
                                                                    WHERE p.ID = pm.post_id
                                                                    AND p.ID = pm2.post_id

                                                                    AND pm2.meta_key = '_customer_user'
                                                                    AND pm2.meta_value = $user_id

                                                                    AND (p.post_status = 'wc-completed' OR p.post_status = 'wc-processing')

                                                                    AND p.post_type = 'shop_order'
                                                                    AND pm.meta_key = 'tc_cart_contents'
                                                                    $ticket_type_id_query_part
                                                                    ");
                    return $paid_orders_count;
                }
            }

            if (apply_filters('tc_is_woo', false) == false) {//This check doesn't work with the Bridge for WooCommerce because it would be very expensive task for the database server
                if ($event_id && !$ticket_type_id) {//paid orders for specific event
                    $event_id_query_part = '';
                    if (is_array($event_id)) {//event id is actually a list of ids / array (so we need to build a bit complicated query)
                        if (count($event_id) > 1) {
                            $event_ids_count = count($event_id);
                            $event_id_query_part .= ' AND (';
                            $foreach_count = 1;
                            $extension = '';
                            foreach ($event_id as $event_id_key => $event_id_value) {
                                if ($event_ids_count == $foreach_count) {
                                    $extension = '';
                                } else {
                                    $extension = ' OR ';
                                }
                                $event_id_query_part .= " pm.meta_value  LIKE '%\"" . (int) $event_id_value . "\"%' $extension";
                                $foreach_count++;
                            }
                            $event_id_query_part .= ') ';
                        } else {//array contains only one element / event id
                            $event_id_query_part = " AND pm.meta_value LIKE '%\"" . (int) $event_id[0] . "\"%'";
                        }
                    } else {//argument is an integer (only one event id)
                        $event_id_query_part = " AND pm.meta_value LIKE '%\"" . (int) $event_id . "\"%'";
                    }

                    $paid_orders_count = $wpdb->get_var("SELECT COUNT(p.ID) FROM $wpdb->posts p, $wpdb->postmeta pm
                                                                    WHERE p.ID = pm.post_id
                                                                    AND p.post_author = $user_id
                                                                    AND p.post_status = 'order_paid'
                                                                    AND p.post_type = 'tc_orders'
                                                                    AND pm.meta_key = 'tc_parent_event'
                                                                    $event_id_query_part
                                                                    ");
                }
            } else {
                $paid_orders_count = 0;
            }

            return (int) $paid_orders_count;
        }

        public static function get_tickera_user_orders() {
            $user_id = get_current_user_id();
            $args = array(
                'author' => $user_id,
                'posts_per_page' => -1,
                'post_type' => 'tc_orders',
                'post_status' => 'order_paid'
            );
            return get_posts($args);
        }

        /**
         * Call admin scripts and styles
         * @global type $wp_version
         * @global type $post_type
         */
        function admin_header() {
            global $wp_version, $post_type;

            //wp_enqueue_script($this->name . '-font-awesome', 'https://use.fontawesome.com/bec919b88b.js', array(), $this->version);

            wp_enqueue_style($this->name . '-admin', $this->plugin_url . 'css/admin.css', array(), $this->version);
            wp_enqueue_style($this->name . '-admin-jquery-ui', '//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css', array(), $this->version);
            wp_enqueue_style($this->name . '-chosen', $this->plugin_url . 'css/chosen.min.css', array(), $this->version);

            wp_enqueue_script($this->name . '-admin', $this->plugin_url . 'js/admin.js', array('jquery', 'jquery-ui-tooltip', 'jquery-ui-core'), false, false);
            wp_localize_script($this->name . '-admin', 'rsc_vars', array(
                'ajaxUrl' => apply_filters('rsc_ajaxurl', admin_url('admin-ajax.php', (is_ssl() ? 'https' : 'http'))),
            ));

            wp_enqueue_script($this->name . '-chosen', $this->plugin_url . 'js/chosen.jquery.min.js', array($this->name . '-admin'), false, false);
        }

        /**
         * Sanitize String
         * @param type $string
         * @return type
         */
        function sanitize_string($string) {
            if ($string != strip_tags($string) || strpos($string, "\n") !== FALSE) {//string contain html tags
                $default_attribs = array(
                    'id' => array(),
                    'class' => array(),
                    'title' => array(),
                    'style' => array(),
                    'data' => array(),
                    'data-mce-id' => array(),
                    'data-mce-style' => array(),
                    'data-mce-bogus' => array(),
                );

                $allowed_tags = array(
                    'a' => array_merge($default_attribs, array(
                        'href' => array(),
                        'target' => array('_blank', '_top'),
                    )),
                    'abbr' => array(
                        'title' => true,
                    ),
                    'acronym' => array(
                        'title' => true,
                    ),
                    'cite' => array(),
                    'div' => $default_attribs,
                    'del' => array(
                        'datetime' => true,
                    ),
                    'em' => array(),
                    'q' => array(
                        'cite' => true,
                    ),
                    'strike' => array(),
                    'strong' => $default_attribs,
                    'blockquote' => $default_attribs,
                    'del' => $default_attribs,
                    'strike' => $default_attribs,
                    'em' => $default_attribs,
                    'code' => $default_attribs,
                    'span' => $default_attribs,
                    'img' => array(
                        'src' => array(),
                        'width' => array(),
                        'height' => array()
                    ),
                    'ins' => array(),
                    'p' => $default_attribs,
                    'u' => $default_attribs,
                    'i' => $default_attribs,
                    'b' => $default_attribs,
                    'ul' => $default_attribs,
                    'ol' => $default_attribs,
                    'li' => $default_attribs,
                    'br' => $default_attribs,
                    'hr' => $default_attribs,
                    'h1' => $default_attribs,
                    'h2' => $default_attribs,
                    'h3' => $default_attribs,
                    'h4' => $default_attribs,
                    'h5' => $default_attribs,
                    'h6' => $default_attribs,
                    'h7' => $default_attribs,
                    'h8' => $default_attribs,
                    'h9' => $default_attribs,
                    'h10' => $default_attribs,
                );
                return wp_kses($string, $allowed_tags);
            } else {
                return sanitize_text_field($string);
            }
        }

        /**
         * Sanitize array or string
         * @param type $input
         * @return type
         */
        function sanitize_array_or_string($input) {

            if (!is_array($input)) {
                return sanitize_text_field($input);
            }

            $new_input = array();

            foreach ($input as $key => $val) {
                if (is_array($val)) {
                    $input_2 = $this->sanitize_array_or_string($val);
                    foreach ($input_2 as $key_2 => $val_2) {
                        if (is_array($val_2)) {
                            $input_3 = $this->sanitize_array_or_string($val_2);
                            foreach ($input_3 as $key_3 => $val_3) {
                                if (is_array($val_3)) {
                                    $input_4 = $this->sanitize_array_or_string($val_3);
                                    foreach ($input_4 as $key_4 => $val_4) {
                                        if (is_array($val_4)) {
                                            $input_5 = $this->sanitize_array_or_string($val_4);
                                            foreach ($input_5 as $key_5 => $val_5) {
                                                $new_input[$key][$key_2][$key_3][$key_4][$key_5] = $this->sanitize_string($val_5);
                                            }
                                        } else {
                                            $new_input[$key][$key_2][$key_3][$key_4] = $this->sanitize_string($val_4);
                                        }
                                    }
                                } else {
                                    $new_input[$key][$key_2][$key_3] = $this->sanitize_string($val_3);
                                }
                            }
                        } else {
                            $new_input[$key][$key_2] = $this->sanitize_string($val_2);
                        }
                    }
                } else {
                    $new_input[$key] = $this->sanitize_string($val);
                }
            }

            return $new_input;
        }

        /**
         * Save metabox values on post save
         * @param type $post_id
         */
        function save_metabox_values($post_id) {
            $metas = array();

            foreach ($_POST as $field_name => $field_value) {
                if (preg_match('/_rsc_post_meta/', $field_name)) {
                    $metas[sanitize_key(str_replace('_rsc_post_meta', '', $field_name))] = $this->sanitize_array_or_string($field_value);
                }

                $metas = apply_filters('rsc_post_metas', $metas);

                if (isset($metas)) {
                    foreach ($metas as $key => $value) {
                        update_post_meta($post_id, $key, $value);
                    }
                }
            }
        }

        /**
         * Adds metabox for content availability
         */
        function add_metabox() {
            global $post_type;

            $rsc_skip_post_types = apply_filters('rsc_skip_post_types', array('product', 'tc_tickets', 'tc_orders'));//do not show restricted content meta fields for post types in the array

            if (!in_array($post_type, $rsc_skip_post_types)) {
                add_meta_box('rsc_metabox', __('Content Available To', 'rsc'), array($this, 'show_metabox'), null, 'normal', 'low');
            }
        }

        /**
         * Get all restriction options
         * @global type $tc
         * @return type
         */
        function get_restriction_options() {
            $restriction_options = array(
                'everyone' => array(__('Everyone', 'rsc'), false),
                'logged_in' => array(__('Logged in users', 'rsc'), false),
                'role' => array(__('Users with specific role', 'rsc'), array('Restricted_Content::get_sub_metabox', array('role'))),
                'capability' => array(__('Users with specific capability', 'rsc'), array('Restricted_Content::get_sub_metabox', array('capability'))),
            );

            if (class_exists('TC')) {
                global $tc;
                $restriction_options['tickera'] = array(sprintf(__('%s Users', 'rsc'), $tc->title), array('Restricted_Content::get_sub_metabox', array('tickera')));
            }

            if (class_exists('WooCommerce')) {
                global $tc;
                $restriction_options['woo'] = array(__('WooCommerce Users', 'rsc'), array('Restricted_Content::get_sub_metabox', array('woo')));
            }
            return apply_filters('rsc_restriction_options', $restriction_options);
        }

        /**
         * Shows metabox
         * @param type $post
         */
        function show_metabox($post) {

            $restriction_options = $this->get_restriction_options();
            $sub_metaboxes_functions = array();

            if (isset($post)) {
                $rsc_content_availability = get_post_meta($post->ID, '_rsc_content_availability', true);
                if (empty($rsc_content_availability)) {
                    $rsc_content_availability = 'everyone';
                }
            }

            $restriction_options_select = '<select name="_rsc_content_availability_rsc_post_meta" id="rsc_content_availability">';

            foreach ($restriction_options as $restriction_option_key => $restriction_option_values) {
                $selected = $rsc_content_availability == $restriction_option_key ? 'selected' : '';
                $restriction_options_select .= '<option value="' . esc_attr($restriction_option_key) . '" ' . $selected . '>' . $restriction_option_values[0] . '</option>';
                if ($restriction_option_values[1][0]) {
                    $sub_metaboxes_functions[] = array($restriction_option_values[1][0], $restriction_option_values[1][1]);
                }
            }

            $restriction_options_select .= '</select>';

            echo $restriction_options_select;

            foreach ($sub_metaboxes_functions as $sub_metaboxes_function_key => $sub_metaboxes_function_args) {
                Restricted_Content::execute_function($sub_metaboxes_function_args[0], $sub_metaboxes_function_args[1]);
            }
        }

        /**
         * Gets content for sub metaboxes
         * @global type $post
         * @global type $tc
         * @param type $type
         * @return type
         */
        public static function get_sub_metabox($type = false) {
            if (!$type) {
                return;
            }
            global $post;
            ?>
            <div class="rsc_sub_metabox rsc_sub_metabox_<?php echo esc_attr($type);
            ?> rsc_hide">
                 <?php
                 switch ($type) {
                     case 'role':
                         if (isset($post)) {
                             $rsc_user_role = get_post_meta($post->ID, '_rsc_user_role', true);

                             if (empty($rsc_user_role)) {
                                 $rsc_user_role_selected = 'administrator';
                             } else {
                                 $rsc_user_role_selected = $rsc_user_role;
                             }
                         }
                         ?>
                        <label><?php _e('Select a User Role', 'rsc'); ?></label>
                        <select name="_rsc_user_role_rsc_post_meta[]" multiple>
                            <?php
                            $editable_roles = array_reverse(get_editable_roles());

                            foreach ($editable_roles as $role => $details) {
                                $name = translate_user_role($details['name']);
                                ?>
                                <option <?php echo (is_array($rsc_user_role_selected) && in_array($role, $rsc_user_role_selected)) ? 'selected' : ''; ?> value="<?php echo esc_attr($role); ?>"><?php echo $name; ?></option>;
                                <?php
                            }
                            ?>
                        </select>
                        <?php
                        break;
                    case 'capability':
                        $rsc_capability_rsc = get_post_meta($post->ID, '_rsc_capability', true)
                        ?>
                        <label><?php _e('User Capability', 'rsc'); ?></label>
                        <input type="text" name="_rsc_capability_rsc_post_meta" value="<?php echo isset($rsc_capability_rsc) ? esc_attr($rsc_capability_rsc) : ''; ?>" placeholder="manage_options" />
                        <?php
                        break;
                    case 'tickera':
                        global $tc;
                        $rsc_tickera_users = get_post_meta($post->ID, '_rsc_tickera_users', true);
                        if (!isset($rsc_tickera_users) || empty($rsc_tickera_users)) {
                            $rsc_tickera_users = 'anything';
                        }
                        ?>
                        <label><?php _e('Who Purchased', 'rsc'); ?></label>
                        <input type="radio" name="_rsc_tickera_users_rsc_post_meta" class="rsc_tickera_radio" value="anything" <?php checked($rsc_tickera_users, 'anything', true); ?> /> <?php _e('Any ticket type', 'rsc'); ?><br />

                        <?php
                        if (apply_filters('tc_is_woo', false) == false) {//Tickera is in the Bridge mode
                            ?>
                            <input type="radio" name="_rsc_tickera_users_rsc_post_meta" class="rsc_tickera_radio" value="event" <?php checked($rsc_tickera_users, 'event', true); ?> /> <?php _e('Any ticket type for a specific event', 'rsc'); ?><br />
                            <?php
                        }
                        ?>
                        <input type="radio" name="_rsc_tickera_users_rsc_post_meta" class="rsc_tickera_radio" value="ticket_type" <?php checked($rsc_tickera_users, 'ticket_type', true); ?> /> <?php _e('Specific ticket type', 'rsc'); ?><br />

                        <div class="rsc_sub_sub rsc_tickera_event rsc_sub_hide rsc_sub_sub_metabox_event">
                            <select name="_rsc_tickera_users_event_rsc_post_meta[]" multiple>
                                <?php
                                $rsc_tickera_users_event = get_post_meta($post->ID, '_rsc_tickera_users_event', true);
                                if (!isset($rsc_tickera_users_event) || empty($rsc_tickera_users_event)) {
                                    $rsc_tickera_users_event = '';
                                }

                                $tc_events = get_posts(array(
                                    'post_type' => 'tc_events',
                                    'posts_per_page' => -1,
                                ));
                                foreach ($tc_events as $event) {
                                    ?>
                                    <option value="<?php echo (int) $event->ID; ?>" <?php echo (is_array($rsc_tickera_users_event) && in_array($event->ID, $rsc_tickera_users_event)) ? 'selected' : ''; ?>><?php echo $event->post_title; ?></option>
                                    <?php
                                }
                                ?>
                            </select>
                        </div>

                        <div class="rsc_sub_sub rsc_tickera_ticket_type rsc_sub_hide rsc_sub_sub_metabox_ticket_type">
                            <select name="_rsc_tickera_users_ticket_type_rsc_post_meta[]" multiple>
                                <?php
                                $rsc_tickera_users_ticket_type = get_post_meta($post->ID, '_rsc_tickera_users_ticket_type', true);
                                if (!isset($rsc_tickera_users_ticket_type) || empty($rsc_tickera_users_ticket_type)) {
                                    $rsc_tickera_users_ticket_type = '';
                                }

                                if (apply_filters('tc_is_woo', false) == false) {//Tickera is in the Bridge mode
                                    $tc_ticket_types = get_posts(array(
                                        'post_type' => 'tc_tickets',
                                        'posts_per_page' => -1,
                                    ));
                                } else {
                                    $tc_ticket_types = get_posts(array(
                                        'post_type' => 'product',
                                        'posts_per_page' => -1,
                                        'meta_key' => '_event_name'
                                    ));
                                }


                                foreach ($tc_ticket_types as $ticket_type) {
                                    $event_id = get_post_meta($ticket_type->ID, apply_filters('tc_event_name_field_name', 'event_name'), true);
                                    $event_title = get_the_title($event_id);
                                    if (empty($event_title)) {
                                        $event_title = sprintf(__('Event ID: %s', 'rsc'), $event_id);
                                    }
                                    ?>
                                    <option value="<?php echo (int) $ticket_type->ID; ?>" <?php echo (is_array($rsc_tickera_users_ticket_type) && in_array($ticket_type->ID, $rsc_tickera_users_ticket_type)) ? 'selected' : ''; ?>><?php echo $ticket_type->post_title . ' (' . $event_title . ')'; ?></option>
                                    <?php
                                }
                                ?>
                            </select>
                        </div>
                        <?php
                        break;

                    case 'woo':
                        global $tc;
                        $rsc_woo_users = get_post_meta($post->ID, '_rsc_woo_users', true);
                        if (!isset($rsc_woo_users) || empty($rsc_woo_users)) {
                            $rsc_woo_users = 'anything';
                        }
                        ?>
                        <label><?php _e('Who Purchased', 'rsc'); ?></label>
                        <input type="radio" name="_rsc_woo_users_rsc_post_meta" class="rsc_woo_radio" value="anything" <?php checked($rsc_woo_users, 'anything', true); ?> /> <?php _e('Any product', 'rsc'); ?><br />

                        <input type="radio" name="_rsc_woo_users_rsc_post_meta" class="rsc_woo_radio" value="product" <?php checked($rsc_woo_users, 'product', true); ?> /> <?php _e('Specific product', 'rsc'); ?><br />

                        <div class="rsc_sub_sub rsc_woo_product rsc_sub_hide rsc_sub_sub_metabox_product">
                            <select name="_rsc_woo_users_product_rsc_post_meta[]" multiple>
                                <?php
                                $rsc_woo_users_product = get_post_meta($post->ID, '_rsc_woo_users_product', true);
                                if (!isset($rsc_woo_users_product) || empty($rsc_woo_users_product)) {
                                    $rsc_woo_users_product = '';
                                }

                                $woo_products = get_posts(array(
                                    'post_type' => 'product',
                                    'posts_per_page' => -1,
                                ));

                                foreach ($woo_products as $product) {
                                    ?>
                                    <option value="<?php echo (int) $product->ID; ?>" <?php echo (is_array($rsc_woo_users_product) && in_array($product->ID, $rsc_woo_users_product)) ? 'selected' : ''; ?>><?php echo $product->post_title; ?></option>
                                    <?php
                                }
                                ?>
                            </select>
                        </div>
                        <?php
                        break;
                }
                ?>
            </div>
            <?php
        }

        /**
         * Execute functions
         * Used in show_metabox method
         * @param type $function_name
         * @param type $args
         */
        public static function execute_function($function_name = false, $args = array()) {
            call_user_func_array($function_name, $args);
        }

    }

    $rsc = new Restricted_Content();
}
