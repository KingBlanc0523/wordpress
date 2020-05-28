<?php

/**
 * Plugin Name: AffiliateWP - Tickera Integration
 * Plugin URI: https://tickera.com
 * Description: Integrates Tickera with AffiliateWP
 * Author: Tickera
 * Author URI: https://tickera.com
 * Version: 1.0
 */

/**
 * Load the conversion script
 */
function affwp_tickera_integration_script() {

    if (!function_exists('affiliate_wp')) {
        return;
    }

    global $wp;

    $confirmation_page = get_option('tc_confirmation_page_id', false);

    if (!is_page($confirmation_page)) {
        return;
    }

    $tc_order_return = isset($wp->query_vars['tc_order_return']) ? $wp->query_vars['tc_order_return'] : '';

    if ($tc_order_return !== '') {
        $order = tc_get_order_id_by_name($tc_order_return);
        $order = new TC_Order($order->ID);
    }

    $amount = $order->details->tc_payment_info['total'];

    if (!apply_filters('affwp_auto_complete_referral', true)) {
        $status = 'pending';
    } else {
        $status = 'unpaid';
    }

    $reference = $order->id;

    $event_ids = get_post_meta($reference, 'tc_parent_event', true);

    $description = array();

    if ($event_ids) {
        foreach ($event_ids as $id) {
            $description[] = get_the_title($id);
        }
    }

    $description = implode(', ', $description);

    // Referral arguments
    $args = array(
        'amount' => $amount,
        'status' => $status,
        'reference' => $reference,
        'description' => $description,
        'context' => 'tickera'
    );

    // add the conversion script to the page
    affiliate_wp()->tracking->conversion_script($args);
}

add_action('wp_head', 'affwp_tickera_integration_script');

/**
 * Link referral to order
 */
function affwp_tickera_reference_link($reference = 0, $referral) {

    if (empty($referral->context) || 'tickera' != $referral->context) {
        return $reference;
    }

    $url = admin_url('post.php?action=edit&post=' . $reference);

    return '<a href="' . esc_url($url) . '">' . $reference . '</a>';
}

add_filter('affwp_referral_reference_column', 'affwp_tickera_reference_link', 10, 2);
