    jQuery(window).ready(function($){
       
       jQuery('.tc-test-submission').click(function(){
           
           jQuery('.tc-show-message').empty();
           tc_api_key = jQuery('#api_key').val();
           tc_list_id = jQuery('#list_id').val();

            var data = {
                    action: 'ajax_mailchimp_check',
                    tc_api_key: tc_api_key,
                    tc_list_id: tc_list_id
            };

            jQuery.post(ajaxurl, data, function(response) {
                    jQuery('.tc-show-message').append(response);
            }); //jQuery.post(ajaxurl, data, function(response)
           
       }); //jQuery('.tc-test-submission').click(function(){
       
    }); //jQuery(window).ready(function($){
    