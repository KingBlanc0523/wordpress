<?php

function tc_get_posts_and_pages($field_name = '', $post_id = '') {
    if ($post_id !== '') {
        $currently_selected = get_post_meta($post_id, $field_name, true);
    } else {
        $currently_selected = '';
    }

    $args = apply_filters('tc_get_posts_and_pages_args', array(
        'posts_per_page' => -1,
        'orderby' => 'date',
        'order' => 'DESC',
        'post_type' => array('post', 'page'),
        'post_status' => 'publish',
        'suppress_filters' => true
    ));

    $posts = get_posts($args);
    ?>
    <select name="<?php echo $field_name; ?>_post_meta">
        <option value=""><?php _e('None', 'ec'); ?></option>
        <?php
        foreach ($posts as $post) {
            ?>
            <option value="<?php echo $post->ID; ?>" <?php selected($currently_selected, $post->ID, true); ?>><?php echo $post->post_title; ?></option>
            <?php
        }
        ?>
    </select>
    <?php
}

function tc_get_calendar_color_schemes() {
    global $tc_event_calendar;

    $color_schemes = array(
        'default' => array(
            'name' => __('Default', 'ec'),
            'url' => '',
            'colors' => array('#ffffff', '#fcf8e3', '#3a87ad')
        ),
        'blue' => array(
            'name' => __('Blue', 'ec'),
            'url' => $tc_event_calendar->plugin_url . 'includes/css/blue.css',
            'colors' => array('#3498db', '#3486BD', '#50A9E4')
        ),
        'dark' => array(
            'name' => __('Dark', 'ec'),
            'url' => $tc_event_calendar->plugin_url . 'includes/css/dark.css',
            'colors' => array('#2c3e50', '#3B4E61', '#2980b9')
        ),
        'flat' => array(
            'name' => __('Flat', 'ec'),
            'url' => $tc_event_calendar->plugin_url . 'includes/css/flat.css',
            'colors' => array('#1abc9c', '#16a085', '#179078')
        ),
        'orange' => array(
            'name' => __('Orange', 'ec'),
            'url' => $tc_event_calendar->plugin_url . 'includes/css/orange.css',
            'colors' => array('#e67e22', '#CC6B2C', '#EF8E38')
        ),
        'red' => array(
            'name' => __('Red', 'ec'),
            'url' => $tc_event_calendar->plugin_url . 'includes/css/red.css',
            'colors' => array('#e74c3c', '#EF5C4D', '#CA3F30')
        ),
    );

    return apply_filters('tc_calendar_color_schemes', $color_schemes);
}

function show_tc_calendar_attributes() {
    ?>
    <table id="tc-calendar-shortcode" class="shortcode-table" style="display:none">
        <tr>
            <th scope="row"><?php _e('Select a calendar theme', 'ec'); ?></th>
            <td>
                <?php
                $color_schemes = tc_get_calendar_color_schemes();
                $selected = 0;
                foreach ($color_schemes as $color_scheme => $color_scheme_info) {
                    ?>
                    <div class="color-option">
                        <input name="color_scheme" id="admin_color_<?php echo esc_attr($color_scheme); ?>" type="radio" value="<?php echo $color_scheme; ?>" class="tog" <?php echo $selected == 0 ? 'checked="checked"' : ''; ?>>
                        <label for="admin_color_<?php echo esc_attr($color_scheme); ?>"><?php echo esc_attr($color_scheme_info['name']); ?></label>
                        <table class="color-palette">
                            <tbody>
                                <?php foreach ($color_scheme_info['colors'] as $color) { ?>
                                    <tr>
                                        <td style="background-color: <?php echo $color; ?>">&nbsp;</td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>

                    <?php
                    $selected++;
                }
                ?>
            </td>
        </tr>

        <tr>
            <th scope="row"><?php _e('Select calendar language', 'ec'); ?></th>
            <td>
                <select name="lang">
                    <option value="en" selected="selected">English</option>
                    <option value="ar-ma">Arabic (Morocco)</option>
                    <option value="ar-sa">Arabic (Saudi Arabia)</option>
                    <option value="ar-tn">Arabic (Tunisia)</option>
                    <option value="ar">Arabic</option>
                    <option value="bg">Bulgarian</option>
                    <option value="ca">Catalan</option>
                    <option value="cs">Czech</option>
                    <option value="da">Danish</option>
                    <option value="de-at">German (Austria)</option>
                    <option value="de">German</option>
                    <option value="el">Greek</option>
                    <option value="en-au">English (Australia)</option>
                    <option value="en-ca">English (Canada)</option>
                    <option value="en-gb">English (United Kingdom)</option>
                    <option value="es">Spanish</option>
                    <option value="fi">Finnish</option>
                    <option value="fr-ca">French (Canada)</option>
                    <option value="fr">French</option>
                    <option value="he">Hebrew</option>
                    <option value="hi">Hindi (India)</option>
                    <option value="hr">Croatian</option>
                    <option value="hu">Hungarian</option>
                    <option value="id">Indonesian</option>
                    <option value="is">Icelandic</option>
                    <option value="it">Italian</option>
                    <option value="ja">Japanese</option>
                    <option value="ko">Korean</option>
                    <option value="lt">Lithuanian</option>
                    <option value="lv">Latvian</option>
                    <option value="nb">Norwegian Bokmål (Norway)</option>
                    <option value="nl">Dutch</option>
                    <option value="pl">Polish</option>
                    <option value="pt-br">Portuguese (Brazil)</option>
                    <option value="pt">Portuguese</option>
                    <option value="ro">Romanian</option>
                    <option value="ru">Russian</option>
                    <option value="sk">Slovak</option>
                    <option value="sl">Slovenian</option>
                    <option value="sr-cyrl">Serbian Cyrillic</option>
                    <option value="sr">Serbian</option>
                    <option value="sv">Swedish</option>
                    <option value="th">Thai</option>
                    <option value="tr">Turkish</option>
                    <option value="uk">Ukrainian</option>
                    <option value="vi">Vietnamese</option>
                    <option value="zh-cn">Chinese (China)</option>
                    <option value="zh-tw">Chinese (Taiwan)</option>
                </select>
            </td>
        </tr>


        <tr>
            <th scope="row"><?php _e('Show/Hide Past Events', 'ec'); ?></th>
            <td>

                <input name="show_past_events" id="tc_show_past_events_yes" type="radio" checked value="yes" class="tog">
                <label for="tc_show_past_events_yes">Show Past Events</label>

                <input name="show_past_events" id="tc_hide_past_events_no" type="radio" value="no" class="tog">
                <label for="tc_hide_past_events_no">Hide Past Events</label>

            </td>
        </tr>

        <tr>
            <th scope="row"><?php _e('Show events for categories', 'ec'); ?></th>
            <td>
                <?php
                $taxonomia = array(
                    'post_tag',
                    'my_tax',
                );

                $args = array(
                    'orderby' => 'name',
                    'order' => 'ASC',
                    'hide_empty' => true,
                    'exclude' => array(),
                    'exclude_tree' => array(),
                    'include' => array(),
                    'number' => '',
                    'fields' => 'all',
                    'slug' => '',
                    'parent' => '',
                    'hierarchical' => true,
                    'child_of' => 0,
                    'childless' => false,
                    'get' => '',
                    'name__like' => '',
                    'description__like' => '',
                    'pad_counts' => false,
                    'offset' => '',
                    'search' => '',
                    'cache_domain' => 'core'
                );

                $terms = get_terms(array(
                    'taxonomy' => 'event_category',
                    'hide_empty' => false,
                ));

                foreach ($terms as $term) {
                    ?>
                    <label><?php echo $term->name; ?>
                        <input name="et_<?php echo esc_attr($term->term_id); ?>" type="checkbox" checked value="1" />
                    </label>
                    <?php
                }
                ?>
                <p><?php _e('Note: If none selected, all events will be visible.', 'ec'); ?></p>
            </td>
        </tr>


    </table>

    <?php
}
?>
