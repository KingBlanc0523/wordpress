<?php

if(isset($atts)){
    extract(shortcode_atts(array(
        'event_id' => false,
        'speakers_display' => false,
        'show_categories' => false,
        'grid_count' => false,
    ), $atts));
}

$tc_get_post_type = get_post_type(get_the_ID());


if(!isset($event_id) || $event_id == false) {
    $event_id = get_the_ID();
}
if(!isset($speakers_display) || $speakers_display == false) {
    $speakers_display = get_post_meta($event_id, 'tc_speakers_view', true);
}

if(!isset($grid_count) || $grid_count == false) {
    $grid_count = get_post_meta($event_id, 'tc_speakers_grid_num', true);
}

if(!isset($show_categories) || $show_categories == false) {
        $show_categories = get_post_meta(get_the_ID(), 'tc_speakers_grid_show_cats', true);
}

$tc_event_speakers_list = get_post_meta($event_id, 'tc_speakers', true);

if(empty($tc_event_speakers_list)){

        $tc_event_speakers_list[]=array();
    
    
}


if ($speakers_display == 'tc_grid') { 

    //include grid template   
    include plugin_dir_path( __FILE__ ) . 'tc-grid-template.php';
    
} elseif ($speakers_display == 'tc_slider') {
    
    //include grid template with featured image
    include plugin_dir_path( __FILE__ ) . 'tc-slider-speakers.php';
     
} elseif ($speakers_display == 'tc_list') {
    
    //include grid template with featured image
    include plugin_dir_path( __FILE__ ) . 'tc-list.php';
}
wp_reset_query();
?>
<div class="tc-clear"></div>