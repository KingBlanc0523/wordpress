jQuery(document).ready(function ($) {
    window.tc_labels = {
        assign: function ()
        {
            if ($('#tc-labels-single-select').css('display') == 'block') {
                tc_labels.assign_single();
            }
            if ($('#tc-labels-multi-select').css('display') == 'block') {
                tc_labels.assign_multi();
            }
            $('#tc_ticket_type_widget').hide();
            $('#tc-seat-labels-settings').hide();
            $(".tc-group-wrap *").removeClass('ui-selected');
        },
        is_label_available: function (label) {
            var is_available = true;
            $.each($(".tc_set_seat:not(.ui-selected) span p"), function () {
                if ($(this).html() == label) {
                    is_available = false;
                }
            });
            return is_available;
        },
        show_same_label_error_message: function () {
            $("#tc-seating-same-label-error-dialog").dialog({
                bgiframe: true,
                closeOnEscape: false,
                draggable: false,
                resizable: false,
                dialogClass: "no-close",
                modal: true,
                title: tc_controls_vars.label_error_message,
                closeText: "<i class='fa fa-times'></i>",
                buttons: [
                    {
                        text: tc_controls_vars.ok,
                        click: function () {
                            $(this).dialog("close");
                        }
                    },
                ]
            });
        },
        assign_single: function () {
            //判断用户是否存在
            if ($('select[name=ticket_type_id] option:selected').html() == 'Send Tickets to User') {
                $.ajaxSettings.async = false;
                $.post(tc_controls_vars.ajaxUrl, {action: "tc_get_user_id", username: $('#tc-labels-single-select .tc_label_letter').val()}, function (data) {
                    data = eval('('+data+')');
                    if(data.status == 0){
                        $("#tc-user-not-found").dialog({
                            bgiframe: true,
                            closeOnEscape: false,
                            draggable: false,
                            resizable: false,
                            dialogClass: "no-close",
                            modal: true,
                            title: 'error',
                            closeText: "<i class='fa fa-times'></i>",
                            buttons: [
                                {
                                    text: tc_controls_vars.yes,
                                    click: function () {
                                        $(this).dialog("close");
                                    }
                                }
                            ]
                        });
                        return false;
                    }else{
                        if (tc_labels.is_label_available($('#tc-labels-single-select .tc_label_letter').val())) {
                            $.each($(".ui-selected:not(.tc-object-selectable)"), function () {
                                var tc_seat_label_single = $('#tc-labels-single-select .tc_label_letter').val();
                                var tc_seat_label_single = tc_seat_label_single.replace(/-/g , "—");
                                $(this).find('span').remove();
                                $(this).append('<span><div class="tc-arrow-up"></div><p>' + tc_seat_label_single + '</p></span>');
                                $(this).removeClass('tc_need_seat_label');
                            });

                            $('#tc-labels-single-select .tc_label_letter').val('');
                        } else {
                            tc_labels.show_same_label_error_message();
                        }
                    }
                })
                $.ajaxSettings.async = true;
            }else{
                if (tc_labels.is_label_available($('#tc-labels-single-select .tc_label_letter').val())) {
                    $.each($(".ui-selected:not(.tc-object-selectable)"), function () {
                        var tc_seat_label_single = $('#tc-labels-single-select .tc_label_letter').val();
                        var tc_seat_label_single = tc_seat_label_single.replace(/-/g , "—");
                        $(this).find('span').remove();
                        $(this).append('<span><div class="tc-arrow-up"></div><p>' + tc_seat_label_single + '</p></span>');
                        $(this).removeClass('tc_need_seat_label');
                    });

                    $('#tc-labels-single-select .tc_label_letter').val('');
                } else {
                    tc_labels.show_same_label_error_message();
                }
            }

        },
        assign_multi: function () {
            if ($('select[name=ticket_type_id] option:selected').html() == 'Send Tickets to User') {
                $.ajaxSettings.async = false;
                $.post(tc_controls_vars.ajaxUrl, {action: "tc_get_user_id", username: $('#tc-labels-multi-select .tc_label_letter').val()}, function (data) {
                    data = eval('('+data+')');
                    if(data.status == 0){
                        $("#tc-user-not-found").dialog({
                            bgiframe: true,
                            closeOnEscape: false,
                            draggable: false,
                            resizable: false,
                            dialogClass: "no-close",
                            modal: true,
                            title: 'error',
                            closeText: "<i class='fa fa-times'></i>",
                            buttons: [
                                {
                                    text: tc_controls_vars.yes,
                                    click: function () {
                                        $(this).dialog("close");
                                    }
                                }
                            ]
                        });
                        return false;
                    }else{
                        var row_sign = $('#tc-labels-multi-select .tc_label_letter').val();
                        var row_sign = row_sign.replace(/-/g , "—");

                        var col_from = parseInt($('#tc-labels-multi-select .tc_label_from_multi').val());
                        var col_to = parseInt($('#tc-labels-multi-select .tc_label_to_multi').val());
                        var selected_with_ticket_types = 0;
                        var increment_by = 1;
                        var next_step = 1;

                        $.each($(".ui-selected:not(.tc-object-selectable)"), function () {
                            if ($(this).hasClass('tc_set_seat')) {
                                selected_with_ticket_types++;
                            }
                        });
                        if (col_to > col_from) {//from 1-30 for instance
                            next_step = col_from;
                            increment_by = ((col_to - col_from) + 1) / selected_with_ticket_types;
                        } else {//from 30-1 for instance
                            next_step = col_from;
                            increment_by = ((col_from - col_to) + 1) / selected_with_ticket_types;
                        }

                        label_availability_errors = 0;

                        $.each($(".ui-selected:not(.tc-object-selectable)"), function () {
                            if (tc_labels.is_label_available(row_sign + (next_step))) {
                                if ($(this).hasClass('tc_set_seat')) {
                                    $(this).find('span').remove();
                                    if ($('select[name=ticket_type_id] option:selected').html() == 'Send Tickets to User') {
                                        var username = $('#tc-labels-multi-select .tc_label_letter').val();
                                        var username = username.replace(/-/g , "—");
                                        $(this).append('<span><div class="tc-arrow-up"></div><p>' + username + '</p></span>');
                                    } else {
                                        $(this).append('<span><div class="tc-arrow-up"></div><p>' + row_sign + (next_step) + '</p></span>');
                                    }
                                    if (col_to > col_from) {
                                        next_step = next_step + Math.ceil(increment_by);
                                    } else {
                                        next_step = next_step - Math.ceil(increment_by);
                                    }
                                }

                                $(this).removeClass('tc_need_seat_label');
                            } else {
                                label_availability_errors++;
                            }
                        });


                        if (label_availability_errors > 0) {
                            tc_labels.show_same_label_error_message();
                        }

                        $('#tc-labels-multi-select .tc_label_letter').val('');
                    }
                })
                $.ajaxSettings.async = true;
            } else {
                var row_sign = $('#tc-labels-multi-select .tc_label_letter').val();
                var row_sign = row_sign.replace(/-/g , "—");

                var col_from = parseInt($('#tc-labels-multi-select .tc_label_from_multi').val());
                var col_to = parseInt($('#tc-labels-multi-select .tc_label_to_multi').val());
                var selected_with_ticket_types = 0;
                var increment_by = 1;
                var next_step = 1;

                $.each($(".ui-selected:not(.tc-object-selectable)"), function () {
                    if ($(this).hasClass('tc_set_seat')) {
                        selected_with_ticket_types++;
                    }
                });
                if (col_to > col_from) {//from 1-30 for instance
                    next_step = col_from;
                    increment_by = ((col_to - col_from) + 1) / selected_with_ticket_types;
                } else {//from 30-1 for instance
                    next_step = col_from;
                    increment_by = ((col_from - col_to) + 1) / selected_with_ticket_types;
                }

                label_availability_errors = 0;

                $.each($(".ui-selected:not(.tc-object-selectable)"), function () {
                    if (tc_labels.is_label_available(row_sign + (next_step))) {
                        if ($(this).hasClass('tc_set_seat')) {
                            $(this).find('span').remove();
                            if ($('select[name=ticket_type_id] option:selected').html() == 'Send Tickets to User') {
                                var username = $('#tc-labels-multi-select .tc_label_letter').val();
                                var username = username.replace(/-/g , "—");
                                $(this).append('<span><div class="tc-arrow-up"></div><p>' + username + '</p></span>');
                            } else {
                                $(this).append('<span><div class="tc-arrow-up"></div><p>' + row_sign + (next_step) + '</p></span>');
                            }
                            if (col_to > col_from) {
                                next_step = next_step + Math.ceil(increment_by);
                            } else {
                                next_step = next_step - Math.ceil(increment_by);
                            }
                        }

                        $(this).removeClass('tc_need_seat_label');
                    } else {
                        label_availability_errors++;
                    }
                });


                if (label_availability_errors > 0) {
                    tc_labels.show_same_label_error_message();
                }

                $('#tc-labels-multi-select .tc_label_letter').val('');
            }
        },
        unset: function (unselect) {
            if (unselect == 'undefined' || unselect == null) {
                unselect = false;
            }
            $.each($(".ui-selected"), function () {
                if ($(this).hasClass('tc_set_seat')) {

                    $(this).find('span').remove();
                }
            });
            if (unselect == true) {
                $('#tc_ticket_type_widget').hide();
                $('#tc-seat-labels-settings').hide();
                $(".tc-group-wrap *").removeClass('ui-selected');
            }
        }
    }
});