<?php
$error_message = '';

if (isset($_POST['save_checkout_recaptcha_settings_nonce']) && wp_verify_nonce($_POST['save_checkout_recaptcha_settings_nonce'], 'save_checkout_recaptcha_settings')) {
    update_option('tc_checkout_recaptcha_settings', $_POST['tc_checkout_recaptcha']);

    $settings = TC_Checkout_reCAPTCHA::get_settings();
}

$settings = TC_Checkout_reCAPTCHA::get_settings();
?>
<div class="wrap tc_wrap">
    <?php if (!empty($error_message)) {
        ?>
        <div class="error"><p><?php echo $error_message; ?></p></div>
    <?php }
    ?>

    <div id="poststuff">
        <form action="" method="post" enctype="multipart/form-data">
            <div class="postbox">
                <h3 class="hndle"><span><?php _e('Settings', 'chre'); ?></span></h3>
                <div class="inside">
                    <p class="description"><?php printf(__('If you do not have keys already then visit %shttps://www.google.com/recaptcha/admin%s', 'chre'), '<a href="https://www.google.com/recaptcha/admin" target="_blank">', '</a>'); ?></p>
                    <table class="form-table">
                        <tbody>

                            <tr>
                                <?php
                                $show_recaptcha = isset($settings['show_recaptcha']) ? $settings['show_recaptcha'] : '0';
                                ?>
                                <th scope="row"><label for="show_recaptcha"><?php _e('Show reCAPTCHA on the checkout page', 'chre') ?></label></th>
                                <td>
                                    <input type="radio" name="tc_checkout_recaptcha[show_recaptcha]" <?php checked($show_recaptcha, '1', true); ?> value="1"> <?php _e('Yes', 'chre'); ?>
                                    <input type="radio" name="tc_checkout_recaptcha[show_recaptcha]" <?php checked($show_recaptcha, '0', true); ?> value="0"> <?php _e('No', 'chre'); ?>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row"><label><?php _e('Language', 'chre') ?></label></th>
                                <td>
                                    <select name="tc_checkout_recaptcha[language]" id="language">
                                        <?php
                                        $languages = array();
                                        $languages['ar'] = __('Arabic', 'chre');
                                        $languages['af'] = __('Afrikaans', 'chre');
                                        $languages['am'] = __('Amharic', 'chre');
                                        $languages['hy'] = __('Armenian', 'chre');
                                        $languages['az'] = __('Azerbaijani', 'chre');
                                        $languages['eu'] = __('Basque', 'chre');
                                        $languages['bn'] = __('Bengali', 'chre');
                                        $languages['bg'] = __('Bulgarian', 'chre');
                                        $languages['ca'] = __('Catalan', 'chre');
                                        $languages['zh-HK'] = __('Chinese (Hong Kong)', 'chre');
                                        $languages['zh-CN'] = __('Chinese (Simplified)', 'chre');
                                        $languages['zh-TW'] = __('Chinese (Traditional)', 'chre');
                                        $languages['hr'] = __('Croatian', 'chre');
                                        $languages['cs'] = __('Czech', 'chre');
                                        $languages['da'] = __('Danish', 'chre');
                                        $languages['nl'] = __('Dutch', 'chre');
                                        $languages['en-GB'] = __('English (UK)', 'chre');
                                        $languages['en'] = __('English (US)', 'chre');
                                        $languages['et'] = __('Estonian', 'chre');
                                        $languages['fil'] = __('Filipino', 'chre');
                                        $languages['fi'] = __('Finnish', 'chre');
                                        $languages['fr'] = __('French', 'chre');
                                        $languages['fr-CA'] = __('French (Canadian)', 'chre');
                                        $languages['gl'] = __('Galician', 'chre');
                                        $languages['ka'] = __('Georgian', 'chre');
                                        $languages['de'] = __('German', 'chre');
                                        $languages['de-AT'] = __('German (Austria)', 'chre');
                                        $languages['de-CH'] = __('German (Switzerland)', 'chre');
                                        $languages['el'] = __('Greek', 'chre');
                                        $languages['gu'] = __('Gujarati', 'chre');
                                        $languages['iw'] = __('Hebrew', 'chre');
                                        $languages['hi'] = __('Hindi', 'chre');
                                        $languages['hu'] = __('Hungarain', 'chre');
                                        $languages['is'] = __('Icelandic', 'chre');
                                        $languages['id'] = __('Indonesian', 'chre');
                                        $languages['it'] = __('Italian', 'chre');
                                        $languages['ja'] = __('Japanese', 'chre');
                                        $languages['kn'] = __('Kannada', 'chre');
                                        $languages['ko'] = __('Korean', 'chre');
                                        $languages['lo'] = __('Laothian', 'chre');
                                        $languages['lv'] = __('Latvian', 'chre');
                                        $languages['lt'] = __('Lithuanian', 'chre');
                                        $languages['ms'] = __('Malay', 'chre');
                                        $languages['ml'] = __('Malayalam', 'chre');
                                        $languages['mr'] = __('Marathi', 'chre');
                                        $languages['mn'] = __('Mongolian', 'chre');
                                        $languages['no'] = __('Norwegian', 'chre');
                                        $languages['fa'] = __('Persian', 'chre');
                                        $languages['pl'] = __('Polish', 'chre');
                                        $languages['pt'] = __('Portuguese', 'chre');
                                        $languages['pt-BR'] = __('Portuguese (Brazil)', 'chre');
                                        $languages['pt-PT'] = __('Portuguese (Portugal)', 'chre');
                                        $languages['ro'] = __('Romanian', 'chre');
                                        $languages['ru'] = __('Russian', 'chre');
                                        $languages['sr'] = __('Serbian', 'chre');
                                        $languages['si'] = __('Sinhalese', 'chre');
                                        $languages['sk'] = __('Slovak', 'chre');
                                        $languages['sl'] = __('Slovenian', 'chre');
                                        $languages['es'] = __('Spanish', 'chre');
                                        $languages['es-419'] = __('Spanish (Latin America)', 'chre');
                                        $languages['sw'] = __('Swahili', 'chre');
                                        $languages['sv'] = __('Swedish', 'chre');
                                        $languages['ta'] = __('Tamil', 'chre');
                                        $languages['te'] = __('Telugu', 'chre');
                                        $languages['th'] = __('Thai', 'chre');
                                        $languages['tr'] = __('Turkish', 'chre');
                                        $languages['uk'] = __('Ukrainian', 'chre');
                                        $languages['ur'] = __('Urdu', 'chre');
                                        $languages['vi'] = __('Vietnamese', 'chre');
                                        $languages['zu'] = __('Zulu', 'chre');
                                        
                                        $selected_language = isset($settings['language']) ? $settings['language'] : 'en';
                                        
                                        foreach ($languages as $language_code => $language) {
                                            ?>
                                        <option value="<?php echo esc_attr($language_code); ?>" <?php selected($language_code, $selected_language, true)?>><?php echo $language; ?></option>
                                            <?php
                                        }
                                        ?>
                                    </select>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row"><label><?php _e('Incomplete reCaptcha error message', 'chre') ?></label></th>
                                <td>
                                    <input name="tc_checkout_recaptcha[error_message]" autocomplete="off" type="text" id="error_message" value="<?php echo isset($settings['error_message']) ? $settings['error_message'] : __('Please complete the reCAPTCHA', 'chre'); ?>" placeholder="<?php echo esc_attr(__('Your Site Key here', 'chre')); ?>" class="regular-text">
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row"><label><?php _e('Site key', 'chre') ?></label></th>
                                <td>
                                    <input name="tc_checkout_recaptcha[site_key]" autocomplete="off" type="text" id="site_key" value="<?php echo isset($settings['site_key']) ? $settings['site_key'] : ''; ?>" placeholder="<?php echo esc_attr(__('Your Site Key here', 'chre')); ?>" class="regular-text">
                                </td>
                            </tr>

                            <tr>
                                <th scope="row"><label><?php _e('Secret key', 'chre') ?></label></th>
                                <td>
                                    <input name="tc_checkout_recaptcha[secret_key]" autocomplete="off" type="text" id="secret_key" value="<?php echo isset($settings['secret_key']) ? $settings['secret_key'] : ''; ?>" placeholder="<?php echo esc_attr(__('Your Secret Key here', 'chre')); ?>" class="regular-text">
                                </td>
                            </tr>

                        </tbody>
                    </table>
                </div>
            </div>
            <?php wp_nonce_field('save_checkout_recaptcha_settings', 'save_checkout_recaptcha_settings_nonce');
            ?>
            <?php submit_button(); ?>
        </form>
    </div>
</div>