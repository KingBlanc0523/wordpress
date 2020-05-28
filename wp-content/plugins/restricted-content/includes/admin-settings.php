<?php
/* * *****************************************
 * Restricted Content Admin Settings Page
 * ***************************************** */

function rsc_settings_page() {
    $rsc_settings = get_option('rsc_settings');
    ?>
    <div class="wrap">
        <h1><?php _e('Restrict Content Messages', 'rsc'); ?></h1>
        <?php
        if (!isset($_REQUEST['updated'])) {
            $_REQUEST['updated'] = false;
        }
        ?>
        <?php if (false !== $_REQUEST['updated']) { ?>
            <div class="updated fade"><p><strong><?php _e('Options saved', 'rsc'); ?> ); ?></strong></p></div>
        <?php } ?>

        <div id="poststuff">



            <form method="post" action="options.php">

                <?php settings_fields('rsc_settings_group'); ?>

                <div class="postbox">
                    <h3><?php _e('General', 'rsc'); ?></h3>

                    <div class="inside">
                        <table class="form-table">

                            <tr valign="top">
                                <th><?php _e('Logged In', 'rsc'); ?></th>
                                <td>
                                    <input id="rsc_settings[logged_in_message]" class="large-text" name="rsc_settings[logged_in_message]" type="text" value="<?php echo isset($rsc_settings['logged_in_message']) ? esc_html($rsc_settings['logged_in_message']) : esc_html(__('You must log in to view this content.', 'rsc')); ?>" /><br/>
                                    <label class="description" for="rsc_settings[logged_in_message]"><?php _e('Message displayed when a user is not logged in and can\'t view content.', 'rsc'); ?></label><br/>
                                </td>
                            </tr>

                            <tr valign="top">
                                <th><?php _e('User Role', 'rsc'); ?></th>
                                <td>
                                    <input id="rsc_settings[user_role_message]" class="large-text" name="rsc_settings[user_role_message]" type="text" value="<?php echo isset($rsc_settings['user_role_message']) ? esc_html($rsc_settings['user_role_message']) : esc_html(__('You don\'t have required permissions to view this content.', 'rsc')); ?>" /><br/>
                                    <label class="description" for="rsc_settings[user_role_message]"><?php _e('Message displayed when a user does not have required user role to view restricted content.', 'rsc'); ?></label><br/>
                                </td>
                            </tr>

                            <tr valign="top">
                                <th><?php _e('Capability', 'rsc'); ?></th>
                                <td>
                                    <input id="rsc_settings[capability_message]" class="large-text" name="rsc_settings[capability_message]" type="text" value="<?php echo isset($rsc_settings['capability_message']) ? esc_html($rsc_settings['capability_message']) : esc_html(__('You don\'t have required permissions to view this content.', 'rsc')); ?>" /><br/>
                                    <label class="description" for="rsc_settings[capability_message]"><?php _e('Message displayed when a user does not have required capability to view restricted content.', 'rsc'); ?></label><br/>
                                </td>
                            </tr>

                        </table>
                    </div>

                </div><!-- general restriction messages -->

                <?php
                if (class_exists('TC')) {
                    global $tc;

                    $tickera_warning_message = false;

                    if (apply_filters('tc_is_woo', false) == true) {//Tickera is in the Bridge mode
                        $tickera_title = $tc->title . ' (' . __('Bridge for WooCommerce', 'rsc') . ')';
                    } else {
                        $tickera_title = $tc->title;
                        $tc_general_settings = get_option('tc_general_setting', false);

                        if (!isset($tc_general_settings['force_login']) || $tc_general_settings['force_login'] !== 'yes') {
                            $tickera_warning_message = sprintf(__('WARNING: In order to track user purchases, you\'ll need to set "Force Login" option in %s to YES.', 'rsc'), '<a href="' . admin_url('edit.php?post_type=tc_events&page=tc_settings') . '">' . sprintf(__('%s Settings', 'rsc'), $tc->title) . '</a>');
                        }
                    }
                    ?>

                    <div class="postbox">

                        <h3><?php echo $tickera_title; ?></h3>
                        <?php
                        if ($tickera_warning_message) {
                            ?>
                            <div class="warning-message"><?php echo $tickera_warning_message; ?></div>
                            <?php
                        }
                        ?>

                        <div class="inside">
                            <table class="form-table">

                                <tr valign="top">
                                    <th><?php _e('Any Ticket Type', 'rsc'); ?></th>
                                    <td>
                                        <input id="rsc_settings[tickera_any_ticket_type_message]" class="large-text" name="rsc_settings[tickera_any_ticket_type_message]" type="text" value="<?php echo isset($rsc_settings['tickera_any_ticket_type_message']) ? esc_html($rsc_settings['tickera_any_ticket_type_message']) : esc_html(__('This content is restricted to the attendees only. Please purchase ticket(s) in order to access this content.', 'rsc')); ?>" /><br/>
                                        <label class="description" for="rsc_settings[tickera_any_ticket_type_message]"><?php _e('Message displayed when a user didn\'t purchase any ticket and can\'t view restricted content.', 'rsc'); ?></label><br/>
                                    </td>
                                </tr>
                                <?php
                                if (apply_filters('tc_is_woo', false) == false) {//Specific event option is not available for Bridge for WooCommerce because task would be to expensive for the database server
                                    ?>

                                    <tr valign="top">
                                        <th><?php _e('Specific Event', 'rsc'); ?></th>
                                        <td>
                                            <input id="rsc_settings[tickera_specific_event_message]" class="large-text" name="rsc_settings[tickera_specific_event_message]" type="text" value="<?php echo isset($rsc_settings['tickera_specific_event_message']) ? esc_html($rsc_settings['tickera_specific_event_message']) : esc_html(__('Only attendees who purchased ticket(s) for following event(s): [rsc_tc_event] can access this content.', 'rsc')); ?>" /><br/>
                                            <label class="description" for="rsc_settings[tickera_specific_event_message]"><?php _e('Message displayed when a user didn\'t purchase any ticket for a specific event and can\'t view restricted content. You can use <strong>[rsc_tc_event]</strong> placeholder for required event(s).', 'rsc'); ?></label><br/>
                                        </td>
                                    </tr>
                                <?php } ?>

                                <tr valign="top">
                                    <th><?php _e('Specific Ticket Type', 'rsc'); ?></th>
                                    <td>
                                        <input id="rsc_settings[tickera_specific_ticket_type_message]" class="large-text" name="rsc_settings[tickera_specific_ticket_type_message]" type="text" value="<?php echo isset($rsc_settings['tickera_specific_ticket_type_message']) ? esc_html($rsc_settings['tickera_specific_ticket_type_message']) : esc_html(__('Only attendees who purchased following ticket type(s): [rsc_tc_ticket_type] can access this content.', 'rsc')); ?>" /><br/>
                                        <label class="description" for="rsc_settings[tickera_specific_ticket_type_message]"><?php _e('Message displayed when a user didn\'t purchase a specific ticket type and can\'t view restricted content. You can use <strong>[rsc_tc_ticket_type]</strong> placeholder for required ticket type(s).', 'rsc'); ?></label><br/>
                                    </td>
                                </tr>


                            </table>
                        </div>
                    </div><!-- Tickera restriction messages box -->
                    <?php
                }
                ?>

                <?php
                if (class_exists('WooCommerce')) {
                    $woo_warning_message = false;
                    ?>
                    <div class="postbox">

                        <h3><?php _e('WooCommerce', 'rsc'); ?></h3>

                        <?php
                        if ($woo_warning_message) {
                            ?>
                            <div class="warning-message"><?php echo $woo_warning_message; ?></div>
                            <?php
                        }
                        ?>

                        <div class="inside">
                            <table class="form-table">

                                <tr valign="top">
                                    <th><?php _e('Any Product', 'rsc'); ?></th>
                                    <td>
                                        <input id="rsc_settings[woo_any_product_message]" class="large-text" name="rsc_settings[woo_any_product_message]" type="text" value="<?php echo isset($rsc_settings['woo_any_product_message']) ? esc_html($rsc_settings['woo_any_product_message']) : esc_html(__('This content is restricted to the clients only. Please purchase any product in order to access this content.', 'rsc')); ?>" /><br/>
                                        <label class="description" for="rsc_settings[woo_any_product_message]"><?php _e('Message displayed when a user didn\'t purchase any WooCommerce product and can\'t view restricted content.', 'rsc'); ?></label><br/>
                                    </td>
                                </tr>

                                <tr valign="top">
                                    <th><?php _e('Specific Product', 'rsc'); ?></th>
                                    <td>
                                        <input id="rsc_settings[woo_specific_product_message]" class="large-text" name="rsc_settings[woo_specific_product_message]" type="text" value="<?php echo isset($rsc_settings['woo_specific_product_message']) ? esc_html($rsc_settings['woo_specific_product_message']) : esc_html(__('Only clients who purchased following product(s): [rsc_woo_product] can access this content.', 'rsc')); ?>" /><br/>
                                        <label class="description" for="rsc_settings[woo_specific_product_message]"><?php _e('Message displayed when a user didn\'t purchase a specific product and can\'t view restricted content. You can use <strong>[rsc_woo_product]</strong> placeholder for required ticket type(s).', 'rsc'); ?></label><br/>
                                    </td>
                                </tr>

                            </table>
                        </div>

                    </div><!--WooCommerce restriction messages -->
                    <?php
                }
                ?>


                <p class="submit">
                    <input type="submit" class="button-primary" value="<?php _e('Save Settings', 'rsc'); ?>" />
                </p>


            </form>
        </div>
    </div>

    <?php
}

function rsc_register_settings() {
    register_setting('rsc_settings_group', 'rsc_settings');
}

add_action('admin_init', 'rsc_register_settings');

function rsc_settings_menu() {
    add_submenu_page('options-general.php', __('Restricted Content Settings', 'rsc'), __('Restricted Content', 'rsc'), 'manage_options', 'restricted-content-settings', 'rsc_settings_page');
}

add_action('admin_menu', 'rsc_settings_menu');
