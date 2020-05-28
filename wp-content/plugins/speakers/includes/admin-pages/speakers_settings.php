<?php
global $TC_speakers;

$error_message = '';

if (isset($_POST['save_speakers_settings_nonce']) && wp_verify_nonce($_POST['save_speakers_settings_nonce'], 'save_speakers_settings')) {
    update_option('tc_speakers_settings', $_POST['tc_speakers']);
    $tc_speakers_settings = TC_Speakers::get_settings();
}

$tc_speakers_settings = TC_Speakers::get_settings();
?>
<div class="wrap tc_wrap">
    <div id="poststuff" class="metabox-holder tc-settings">
    <form action="" method="post" enctype="multipart/form-data">

    <?php if (!empty($error_message)) {
        ?>
        <div class="error"><p><?php echo $error_message; ?></p></div>
    <?php }
    ?>

            <div class="postbox">
                <h3><span><?php _e('Speaker Settings', 'tcsc'); ?></span></h3>
                <div class="inside">
                    <table class="form-table">
                        <tbody>
                            
                            <tr>
                                <th scope="row"><label><?php _e('Show Speakers In Popup', 'tcsc') ?></label></th>
                                <td>
                                <?php
                                    if(!isset($tc_speakers_settings['show_popup'])){
                                        $tc_speakers_settings['show_popup'] = 'no';
                                    }
                                ?>
                                    <input name="tc_speakers[show_popup]" <?php checked($tc_speakers_settings['show_popup'], 'yes', true);  ?> type="radio" value="yes" >Yes
                                    <input name="tc_speakers[show_popup]" <?php checked($tc_speakers_settings['show_popup'], 'no', true); ?> type="radio" value="no">No
                                </td>
                            </tr>

                            <tr>
                                <th scope="row"><label for="reserved_seat_color"><?php _e('Speakers Slug', 'tcsc') ?></label></th>
                                <td>
                                    <input name="tc_speakers[speakers_slug]" type="text" id="tc_speakers_slug" value="<?php echo isset($tc_speakers_settings['speakers_slug']) ? $tc_speakers_settings['speakers_slug'] : 'tc-speakers'; ?>" class="regular-text">
                                </td>
                            </tr>

                            <tr>
                                <th scope="row"><label for="reserved_seat_color"><?php _e('Speakers Category Slug', 'tcsc') ?></label></th>
                                <td>
                                    <input name="tc_speakers[speakers_category_slug]" type="text" id="tc_speakers_slug" value="<?php echo isset($tc_speakers_settings['speakers_category_slug']) ? $tc_speakers_settings['speakers_category_slug'] : 'tc-speakers-archive'; ?>" class="regular-text">
                                </td>
                            </tr>
                                                     

                        </tbody>
                    </table>
                </div>
            </div>

            
            <?php wp_nonce_field('save_speakers_settings', 'save_speakers_settings_nonce'); ?>
    </div>
    <?php submit_button(); ?>

    </form>
    </div>
</div>