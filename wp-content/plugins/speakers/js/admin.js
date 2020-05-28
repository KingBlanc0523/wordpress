jQuery(window).ready(function ($) {

    $("select.tc_speakers, select.tc_speakers_grid").chosen({allow_single_deselect: false});

    var tc_color_picker_options = {
        defaultColor: true,
    };

    $( '.tc-color-selector' ).wpColorPicker( tc_color_picker_options );


    //show hide option in the backend
    var tc_speakers_show = jQuery('#tc_speakers').val();
    tc_show_hide_column_option(tc_speakers_show);

    jQuery( "#tc_speakers" ).change(function() {
        var tc_option_value = jQuery(this).val();
        tc_show_hide_column_option(tc_option_value);
    });
   
    function tc_show_hide_column_option($this){

        if($this == 'tc_grid') {
            jQuery('#tc_speakers_grid_chosen').show();
            jQuery('#tc-categories-wrap').show();
            jQuery('#tc-speakers-categories-options').show();
            jQuery('#tc-speakers-grid-count-options').show();
        } else if($this == 'tc_list'){
            jQuery('#tc_speakers_grid_chosen').hide();
            jQuery('#tc-categories-wrap').show(); 
            jQuery('#tc-speakers-categories-options').show(); 
            jQuery('#tc-speakers-grid-count-options').show(); 
        } else {
            jQuery('#tc_speakers_grid_chosen').hide();
            jQuery('#tc-categories-wrap').hide();
            jQuery('#tc-speakers-categories-options').hide();
        }
        
    }

});