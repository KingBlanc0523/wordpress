jQuery(document).ready(function ($) {

    rsc_content_availability_options();
    rsc_tickera_users_options();
    rsc_woo_users_options();

    $('#rsc_content_availability').on('change', function () {
        rsc_content_availability_options();
        rsc_tickera_users_options();
        rsc_woo_users_options();
    });

    $('.rsc_tickera_radio').on('change', function () {
        rsc_tickera_users_options();
    });

    $('.rsc_woo_radio').on('change', function () {
        rsc_woo_users_options();
    });

    function rsc_woo_users_options() {

        var rsc_woo_users = $('.rsc_woo_radio:checked').val();
        var sub_metabox = $('.rsc_sub_sub_metabox_' + rsc_woo_users);

        if (sub_metabox.length > 0) {
            rsc_hide_all_sub_metaboxes('.rsc_sub_sub_metabox_product.rsc_sub_sub');
            sub_metabox.addClass('rsc_show');
            sub_metabox.removeClass('rsc_hide')
        } else {
            rsc_hide_all_sub_metaboxes('.rsc_sub_sub_metabox_product.rsc_sub_sub');
        }
    }

    function rsc_tickera_users_options() {

        var rsc_tickera_users = $('.rsc_tickera_radio:checked').val();

        var sub_metabox = $('.rsc_sub_sub_metabox_' + rsc_tickera_users);

        if (sub_metabox.length > 0) {
            rsc_hide_all_sub_metaboxes('.rsc_sub_sub_metabox_ticket_type.rsc_sub_sub');
            rsc_hide_all_sub_metaboxes('.rsc_sub_sub_metabox_event.rsc_sub_sub');
            sub_metabox.addClass('rsc_show');
            sub_metabox.removeClass('rsc_hide')
        } else {
            rsc_hide_all_sub_metaboxes('.rsc_sub_sub_metabox_ticket_type.rsc_sub_sub');
            rsc_hide_all_sub_metaboxes('.rsc_sub_sub_metabox_event.rsc_sub_sub');
        }
    }

    function rsc_content_availability_options() {
        var rsc_selected_content_availability = $('#rsc_content_availability').val();
        var sub_metabox = $('.rsc_sub_metabox_' + rsc_selected_content_availability);

        if (sub_metabox.length > 0) {
            rsc_hide_all_sub_metaboxes('.rsc_sub_metabox');
            sub_metabox.addClass('rsc_show');
            sub_metabox.removeClass('rsc_hide')
        } else {
            rsc_hide_all_sub_metaboxes('.rsc_sub_metabox');
        }
    }

    function rsc_hide_all_sub_metaboxes(element) {
        $(element).removeClass('rsc_show');
        $(element).addClass('rsc_hide');
    }

    function rsc_chosen() {
        $("#rsc_metabox select").css('width', '25em');
        $("#rsc_metabox select").css('display', 'block');
        $("#rsc_metabox select").chosen({disable_search_threshold: 10, allow_single_deselect: false});
        $("#rsc_metabox select").css('display', 'none');
        $("#rsc_metabox .chosen-container").css('width', '100%');
        $("#rsc_metabox .chosen-container").css('max-width', '25em');
        $("#rsc_metabox .chosen-container").css('min-width', '1em');
    }

    rsc_chosen();

});