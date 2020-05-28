jQuery(window).ready(function($) {

setTimeout(function(){

    var $grid = $('.tc-masonry-cat-wrap').isotope({
        itemSelector: '.tc-speakers-grid',
        layoutMode: 'fitRows'
    });

}, 500);


// filter functions
    var filterFns = {
        // show if number is greater than 50
        numberGreaterThan50: function() {
            var number = $(this).find('.number').text();
            return parseInt(number, 10) > 50;
        },
        // show if name ends with -ium
        ium: function() {
            var name = $(this).find('.name').text();
            return name.match(/ium$/);
        }
    };

    var $grid = $('.tc-masonry-cat-wrap').isotope({
        itemSelector: '.tc-speakers-grid',
        layoutMode: 'fitRows'
    });

    $('#tc-speakers-tax').on('click', '.tc-sort-button', function() {
        var filterValue = $(this).attr('data-filter');
        // use filterFn if matches value
        filterValue = filterFns[ filterValue ] || filterValue;
        $grid.isotope({filter: filterValue});
    });

    // bind sort button click
    $('#sorts').on('click', 'button', function() {
        var sortByValue = $(this).attr('data-sort-by');
        $grid.isotope({sortBy: sortByValue});
    });


    if (tc_event_parameters.tc_speakers_popup == 'yes') {

        jQuery('.tc-magnific-popup-ajax').click(function() {

            var tc_speaker_id = jQuery(this).attr('data-post-id');

            var data = {
                'action': 'tc_ajax_load_speaker',
                'tc_speaker_id': tc_speaker_id    
            };
         
            jQuery.post(tc_event_parameters.ajaxurl, data, function(response) {
                jQuery('#tc-speaker-popup').html(response);
            });

        });

        function tc_animate_popup_open() {
            var checkExist = setInterval(function() {
                if ($('.tc-popup-content-wrap').length) {
                    jQuery(".tc-popup-content-wrap").animate({
                        top: "-65",
                        opacity: 1                       
                    }, 500, function() {
                        // Animation complete.
                    });

                    jQuery(".tc-speaker-featured-image-popup img").animate({
                        left: "0",
                        opacity: 1
                    }, 500, function() {
                        // Animation complete.
                    });
                    
                    jQuery("#tc-speaker-popup .mfp-close.mfp-close-in-featured").animate({
                        top: "10",
                        opacity: 1
                    }, 500, function() {
                        // Animation complete.
                    });

                    clearInterval(checkExist);
                }
            }, 100); // check every 100ms

        }
        
        
        function tc_animate_popup_close() {
                    jQuery(".tc-popup-content-wrap").animate({
                        top: "0",
                        opacity: 0
                    }, 500, function() {
                        // Animation complete.
                    });

                    jQuery(".tc-speaker-featured-image-popup img").animate({
                        left: "150",
                        opacity: 0
                    }, 500, function() {
                        // Animation complete.
                    });
                    
                    jQuery("#tc-speaker-popup .mfp-close.mfp-close-in-featured").animate({
                        top: "-35",
                        opacity: 0
                    }, 500, function() {
                        // Animation complete.
                    });

        }
        
                        
        jQuery('.tc-magnific-popup-ajax').magnificPopup({
            type:'inline',
            showCloseBtn: true,
            closeBtnInside: true,
            midClick: true,
            removalDelay: 500,
              callbacks: {
                beforeClose: function(){
                    tc_animate_popup_close();
                    
                },
                close: function() {
                    setTimeout(function(){ 
                          jQuery('#tc-speaker-popup').html('');
                    }, 500);
                },
                open: function(){
                    
                    tc_animate_popup_open();
                  
                }
            }
        });

        
    }
    

if( tc_event_parameters.tc_speakers_view == 'tc_grid_featured_image') {


    jQuery('.tc-speakers-grid').click(function(){
    
        var tc_speakers_id = jQuery(this).attr('data-post-id');
        
        
		var data = {
			'action': 'tc_get_selected_speaker',
			'tc_post_id': tc_speakers_id
		};

		// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
		jQuery.post(tc_event_parameters.ajaxurl, data, function(response) {
			
                        jQuery('.tc-featured-speaker').html(response);
                        
		});
        
    });
    

}

    if (tc_event_parameters.tc_speakers_view == 'tc_slider') {

        jQuery('.tc-speaker-image-wrap, .tc-speakers-slider .flex-direction-nav li a').hover(function(){
            
            
            var tc_height_description = jQuery(this).find('.tc-speaker-excerpt').height();
            jQuery(this).find('.tc-speaker-description-wrap').attr('style','bottom: 0;');
            
            
        }, function(){
            var tc_height_description = jQuery(this).find('.tc-speaker-excerpt').height();
            jQuery(this).find('.tc-speaker-description-wrap').attr('style', 'bottom: -'+tc_height_description+'px;');
        });
 
    } //if (tc_event_parameters.tc_speakers_view == 'tc_slider')

});

jQuery(window).load(function() {
    
    
        jQuery('.tc-speakers-slider').flexslider({
            animation: "slide",
            smoothHeight: true,
            prevText: "<i class='fa fa-angle-left' aria-hidden='true'></i>", //String: Set the text for the "previous" directionNav item
            nextText: "<i class='fa fa-angle-right' aria-hidden='true'></i>",
            directionNav: false,
            controlNav: true,
            start: function () {
                jQuery('.tc-speakers-slider li').each(function () {
                    var tc_height_description = jQuery(this).find('.tc-speaker-excerpt').height();
                    jQuery(this).find('.tc-speaker-description-wrap').attr('style', 'bottom: -' + tc_height_description + 'px;');
                });

            },

            after: function () {

            },

        });
        
  

  
function tc_set_nav_images(){
        var tc_previous_image = jQuery('li.flex-active-slide').prev().find('.tc-speaker-image-wrap img').attr("src");
        var tc_next_image = jQuery('li.flex-active-slide').next().find('.tc-speaker-image-wrap img').attr("src");
       
       jQuery('.tc-speakers-slider .flex-direction-nav .flex-next').html('<img src='+tc_next_image+'>');
       jQuery('.tc-speakers-slider .flex-direction-nav .flex-prev').html('<img src='+tc_previous_image+'>');
}
  
});