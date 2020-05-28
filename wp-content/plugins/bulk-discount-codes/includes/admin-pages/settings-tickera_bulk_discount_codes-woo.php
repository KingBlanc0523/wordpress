<?php
$discounts = new TC_Discounts();
$fields = $discounts->get_discount_fields(true);
$columns = $discounts->get_columns();

function tc_add_bulk_discounts() {
    global $user_id, $post;

    if (isset($_POST['add_new_discounts'])) {
        set_time_limit(0); //we have to increase since it might take some time

        $discount_codes_raw = trim($_POST['post_titles_post_title']);
        $discount_codes_array = explode("\n", $discount_codes_raw);
        $discount_codes = array_filter($discount_codes_array, 'trim'); // remove any extra \r characters left behind

        foreach ($discount_codes as $discount_code) {
            $title = $discount_code;

            $metas = array();

            $metas['discount_type'] = $_POST['discount_type'];
            $metas['coupon_amount'] = $_POST['coupon_amount'];
            $metas['individual_use'] = $_POST['individual_use'];
            $metas['product_ids'] = $_POST[''];
            $metas['exclude_product_ids'] = $_POST[''];
            $metas['usage_limit'] = $_POST['usage_limit'];
            $metas['expiry_date'] = $_POST['expiry_date'];
            $metas['apply_before_tax'] = '';
            $metas['free_shipping'] = 'no';

            $arg = array(
                'post_author' => $user_id,
                'post_excerpt' => '',
                'post_content' => '',
                'post_status' => 'publish',
                'post_title' => $discount_code,
                'post_type' => 'shop_coupon',
            );

            if (isset($_POST['post_id'])) {
                $arg['ID'] = $_POST['post_id']; //for edit
            }

            $post_id = @wp_insert_post($arg, true);

            //Update post meta
            if ($post_id !== 0) {
                if (isset($metas)) {
                    foreach ($metas as $key => $value) {
                        update_post_meta($post_id, $key, $value);
                    }
                }
            }
        }
    }
}

if (isset($_POST['add_new_discounts'])) {
    if (check_admin_referer('save_discounts')) {
        if (current_user_can('manage_options') || current_user_can('add_discount_cap')) {
            tc_add_bulk_discounts();
            $message = __('Discount Codes data has been saved successfully.', 'tc-bdc');
        } else {
            $message = __('You do not have required permissions for this action.', 'tc-bdc');
        }
    }
}
?>
<div class="wrap tc_wrap">
    <div id="poststuff" class="metabox-holder tc-settings">

        <?php
        if (isset($message)) {
            ?>
            <div id="message" class="updated fade"><p><?php echo esc_attr($message); ?></p></div>
            <?php
        }
        ?>

        <form action="" method="post" enctype = "multipart/form-data">
            <div id="store_settings" class="postbox">
                <h3><?php echo $discounts->form_title; ?></h3>
                <div class="inside">
                    <?php wp_nonce_field('save_discounts'); ?>
                    <table class="discount-table">
                        <tbody>
                            <tr valign="top">
                                <th>Discount Codes</th>
                                <td><textarea rows="4" cols="50" name="post_titles_post_title"></textarea>
                                    <br /><span><?php _e('Discount Code, e.g. ABC123. One discount code per line.', 'tc-bdc'); ?>
                                    </span>
                                </td>

                            </tr>

                            <tr valign = "top">
                                <th><?php _e('Discount Type', 'tc-bdc'); ?></th>
                                <td><select name="discount_type">
                                        <option value="percent"><?php _e('Percentage discount', 'tc-bdc'); ?></option>
                                        <option value="fixed_cart"><?php _e('Fixed cart discount', 'tc-bdc'); ?></option>
                                    </select>
                                </td>
                            </tr>

                            <tr valign = "top">
                                <th><?php _e('Amount', 'tc-bdc'); ?></th>
                                <td><input type="text" name="coupon_amount" value="" /><br/>
                                    <span><?php _e('For example: 9.99', 'tc-bdc'); ?></span>
                                </td>
                            </tr>

                            <tr valign = "top">
                                <th><?php _e('Individual use only', 'tc-bdc'); ?></th>
                                <td>
                                    <select name = "individual_use">
                                        <option value="no"><?php _e('No', 'tc-bdc'); ?></option>
                                        <option value="yes"><?php _e('Yes', 'tc-bdc'); ?></option>
                                    </select>
                                    <span class="description"><?php _e('Set it to "Yes" if the coupon cannot be used in conjunction with other coupons.', 'tc-bdc'); ?></span>
                                </td>
                            </tr>

                            <tr valign = "top">
                                <th><?php _e('Usage Limit', 'tc-bdc'); ?></th>
                                <td><input type = "text" name = "usage_limit" value = "" /> <span class = "description"><?php _e('Usage limit per coupon. Leave empty for unlimited.', 'tc-bdc'); ?></span>
                                </td>
                            </tr>
                            
                            <tr valign = "top">
                                <th><?php _e('Coupon expiry date', 'tc-bdc'); ?></th>
                                <td><input type = "text" name = "expiry_date" id="expiry_date" value = "" />
                                </td>
                            </tr>

                        </tbody>
                    </table>
                </div>
            </div>
            <?php submit_button(__('Add Discount Codes', 'tc-bdc'), 'primary', 'add_new_discounts', true);
            ?>

        </form>
    </div>
</div><!-- .wrap -->