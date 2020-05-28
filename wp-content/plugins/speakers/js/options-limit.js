jQuery(window).ready(function ($) {

    jQuery('.tc-shortcode-builder-button').click(function(){
        jQuery( "#tc-speakers-display-options select" ).change(function() {
             var tc_option_value = jQuery(this).val();
                     
            if(tc_option_value == 'tc_grid') {
                jQuery('#tc-speakers-grid-count-options').show();
                jQuery('#tc-speakers-categories-options').show();
            } else if(tc_option_value == 'tc_list'){
                jQuery('#tc-speakers-grid-count-options').show(); 
                jQuery('#tc-speakers-categories-options').show();
            } else {
                jQuery('#tc-speakers-grid-count-options').hide();
                jQuery('#tc-speakers-categories-options').hide();
            }
        });
    });

});