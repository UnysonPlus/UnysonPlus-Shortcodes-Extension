<?php if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}

$calendar_shortcode = fw_ext('shortcodes')->get_shortcode('calendar');

$options = [
    'tab_content' => [
        'title'   => __('Content', 'fw'),
        'type'    => 'tab',
        'options' => [
            'group_content' => [
                'type'    => 'group',
                'options' => [
                    'data_provider' => [
                        'type'  => 'multi-picker',
                        'label' => false,
                        'desc'  => false,
                        'picker' => [
                            'population_method' => [
                                'label'   => __('Population Method', 'fw'),
                                'desc'    => __('Select calendar population method (Ex: events, custom)', 'fw'),
                                'help'    => __('"Custom" lets you add entries by hand; "Events" auto-fills the calendar from your events data. Switching this changes the fields shown below.', 'fw'),
                                'type'    => 'short-select',
                                'value'   => 'custom',
                                'choices' => $calendar_shortcode->_get_picker_dropdown_choices(),
                            ]
                        ],
                        'choices'      => $calendar_shortcode->_get_picker_choices(),
                        'show_borders' => false,
                        'hide_picker'  => true,
                    ],
                    'template' => [
                        'label'   => __('Calendar Type', 'fw'),
                        'desc'    => __('Select calendar type', 'fw'),
                        'help'    => __('Sets the initial view: "Daily" for a single-day agenda, "Weekly" for a 7-day grid, "Monthly" for a full month overview.', 'fw'),
                        'type'    => 'short-select',
                        'value'   => 'day',
                        'choices' => [
                            'day'   => __('Daily', 'fw'),
                            'week'  => __('Weekly', 'fw'),
                            'month' => __('Monthly', 'fw'),
                        ],
                    ],
                    'first_week_day' => [
                        'label'   => __('Start Week On', 'fw'),
                        'desc'    => __('Select first day of week', 'fw'),
                        'help'    => __('Sets which day appears in the leftmost column of the Weekly and Monthly views. Match your region\'s convention (Monday in much of Europe, Sunday in the US).', 'fw'),
                        'type'    => 'short-select',
                        'choices' => [
                            '1' => __('Monday', 'fw'),
                            '2' => __('Sunday', 'fw'),
                        ],
                        'value'   => 1,
                    ],
                ],
            ],
        ],
    ],

    'tab_styling' => [
        'title'   => __( 'Styling', 'fw' ),
        'type'    => 'tab',
        'options' => [
            'group_colors' => [
                'type'    => 'group',
                'options' => [
                    'text_color'       => sc_color_field_compact( array( 'label' => __( 'Text Color', 'fw' ),       'kind' => 'text' ) ),
                    'bg_color'         => sc_color_field_compact( array( 'label' => __( 'Background Color', 'fw' ), 'kind' => 'bg' ) ),
                    'font_size_preset' => sc_font_size_field( array(
                        'desc' => __( 'A named size from the framework presets. Customizable in Theme Settings on the official Unyson+ theme.', 'fw' ),
                    ) ),
                    'heading_color' => sc_color_field_compact( array(
                        'label' => __( 'Heading Color', 'fw' ),
                        'desc'  => __( 'Overrides the general Text Color for the calendar heading only.', 'fw' ),
                    ) ),
                    'buttons_color' => sc_color_field_compact( array(
                        'label' => __( 'Navigation Buttons Color', 'fw' ),
                        'desc'  => __( 'Overrides the general Text Color for the prev / today / next navigation buttons only.', 'fw' ),
                    ) ),
                ],
            ],
            'group_spacings' => [
                'type'    => 'group',
                'options' => [
                    'spacing' => array(
                        'type'  => 'spacing',
                        'label' => __( 'Margin & Padding', 'fw' ),
                        'desc'  => __( 'All Sides applies to every side at once; any per-side value (Top, Right, Bottom, Left) overrides it for that direction.', 'fw' ),
                        'help'  => sc_styling_help_text( 'spacing' ),
                    ),
                ],
            ],
        ],
    ],
    'tab_animation' => [
        'title'   => __( 'Animations', 'fw' ),
        'type'    => 'tab',
        'options' => sc_get_animation_fields(),
    ],
    'tab_advanced' => [
        'title'   => __('Advanced', 'fw'),
        'type'    => 'tab',
        'options' => [
            'advanced_settings' => [
                'type'    => 'group',
                'options' => array_merge(
                    sc_get_advanced_tab(),
                    [
                        /* 'title_extra' => [
                            'type'  => 'text',
                            'label' => __('Some Title', 'fw'),
                            'desc'  => __('Write some heading title content', 'fw'),
                        ],
                        'title_extra_2' => [
                            'type'  => 'text',
                            'label' => __('Some Title2', 'fw'),
                            'desc'  => __('Write some heading title content', 'fw'),
                        ],*/
                    ]
                ),
            ],
        ],
    ],
];
