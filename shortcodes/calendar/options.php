<?php if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}

$calendar_shortcode = fw_ext('shortcodes')->get_shortcode('calendar');

$options = [
    'tab_content' => [
        'title'   => __('Content', 'fw'),
        'type'    => 'tab',
        'options' => [
            'data_provider' => [
                'type'  => 'multi-picker',
                'label' => false,
                'desc'  => false,
                'picker' => [
                    'population_method' => [
                        'label'   => __('Population Method', 'fw'),
                        'desc'    => __('Select calendar population method (Ex: events, custom)', 'fw'),
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
                'type'    => 'short-select',
                'choices' => [
                    '1' => __('Monday', 'fw'),
                    '2' => __('Sunday', 'fw'),
                ],
                'value'   => 1,
            ],
        ],
    ],

    'tab_advanced' => sc_get_advanced_tab(),
];
