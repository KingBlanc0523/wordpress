var el = wp.element.createElement,
        registerBlockType = wp.blocks.registerBlockType,
        BlockControls = wp.editor.BlockControls,
        InspectorControls = wp.editor.InspectorControls,
        ServerSideRender = wp.components.ServerSideRender;

var AlignmentToolbar = wp.editor.AlignmentToolbar;
var RichText = wp.editor.RichText;
var SelectControl = wp.components.SelectControl;
var RangeControl = wp.components.RangeControl;
var TextControl = wp.components.TextControl;
var ToggleControl = wp.components.ToggleControl;

var __ = wp.i18n.__;

registerBlockType('tickera/event-speakers', {
    title: __('Event Speakers'),
    description: __('Show Speakers For A Specific Event'),
    icon: 'groups',
    category: 'widgets',
    keywords: [ 
      __( 'Tickera' ), 
      __( 'Speakers' ), 
    ],
    supports: {
        html: false,
    },
    attributes: {
        event_id: {
            type: 'string',
        },
        speakers_display: {
            type: 'string',
            default: 'tc_grid'
        },
        grid_count: {
            type: 'string',
            default: '2'
        },
        show_categories: {
            type: 'boolean',
            default: 'yes',
        }

    },
    edit: function (props) {
        var events = jQuery.parseJSON(tc_event_add_speakers.events);
        var event_ids = [

        ];
        events.forEach(function (entry) {
            event_ids.push({value: entry[0], label: entry[1]});
        });

        return [
            el(
                    InspectorControls,
                    {key: 'controls'},
                    el(
                            SelectControl,
                            {
                                label: __('Event'),
                                value: props.attributes.event_id,
                                onChange: function change_val(value) {
                                    return props.setAttributes({event_id: value});
                                },
                                options: event_ids
                            }
                    ),
                    
                
                    el(
                            SelectControl,
                            {
                                label: __('Display:'),
                                value: props.attributes.speakers_display,
                                onChange: function change_val(value) {
                                    return props.setAttributes({speakers_display: value});
                                },
                                options: [
                                    {value: 'tc_grid', label: __('Grid')},
                                    {value: 'tc_slider', label: __('Slider')},
                                    {value: 'tc_list', label: __('List')},
                                ]
                            }
                    ),
     
                    el(
                            SelectControl,
                            {
                                label: __('Number Of Columns:'),
                                value: props.attributes.grid_count,
                                onChange: function change_val(value) {
                                    return props.setAttributes({grid_count: value});
                                },
                                options: [
                                    {value: '2', label: __('2')},
                                    {value: '3', label: __('3')},
                                    {value: '4', label: __('4')},
                                ]
                            }
                    ),
                    el(
                            SelectControl,
                            {
                                label: __('Show Categories:'),
                                value: props.attributes.show_categories,
                                onChange: function change_val(value) {
                                    return props.setAttributes({show_categories: value});
                                },
                                options: [
                                    {value: 'yes', label: __('Yes')},
                                    {value: 'no', label: __('No')},
                                ]
                            }
                    ),
                    
                    ),

            el(ServerSideRender, {
                block: "tickera/event-speakers",
                attributes: props.attributes
            })

        ];
    },
    save: function (props) {
        return null;
    },
});