jQuery(window).ready(function(){
    
    function prevent_click_on_mobile(event){
        event.preventDefault();      
    } //prevent_click_on_mobile
    
    jQuery('a.fc-day-grid-event').click(function(event){
        
        /* GETS LINKS AND TITLE AND PASTES THEM TO CREATED DIV */
        /* CHECK THE WIDTH OF THE WINDOW AND IF NEEDED GIVE POPUP FOR THE EVENT */
        get_window_width = jQuery(window).width();
        if(get_window_width < 480) {
        event.stopPropagation();
        var tc_get_post_link = jQuery(this).attr("href");
        
        var tc_get_post_title = "<div class='tc-calendar-title'><a href='"+tc_get_post_link+"'>"+jQuery('.fc-content .fc-title', this).text()+"</a></div>";        
        var tc_get_post_time = "<div class='tc-calendar-time'>"+jQuery('.fc-content .fc-time', this).text()+"</div>";
            
        jQuery('.tc-responsive-event').html(tc_get_post_title + tc_get_post_time);        
        
        var parentOffset = jQuery("#tc_calendar").parent().offset(); 

        var mouseX = event.pageX - parentOffset.left;
        var mouseY = event.pageY - parentOffset.top;

        jQuery('.tc-responsive-event').css({'top':mouseY,'left':mouseX}).fadeIn(200);
        
            prevent_click_on_mobile(event);
        } // if(get_window_width < 480)
    }); //jQuery('a.fc-day-grid-event').click(function(event)

    jQuery(document).click(function() {
        jQuery('.tc-responsive-event').fadeOut(200);
    }); //jQuery( ".fc-view-container" ).mouseleave(function()
});