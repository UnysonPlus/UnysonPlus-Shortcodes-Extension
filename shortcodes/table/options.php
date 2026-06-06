<?php if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}

$options = [
    'tab_content' => [
        'title'   => __('Content', 'fw'),
        'type'    => 'tab',
        'options' => [
            'table' => [
                'type'  => 'table',
                'label' => false,
                'desc'  => false,
                'help'  => __( 'Use the toolbar to add or remove rows and columns; the first row is treated as the header. Keep cell text short for clean responsive wrapping on mobile.', 'fw' ),
            ],
        ],
    ],

    'tab_table_options' => [
        'title'   => __( 'Table Options', 'fw' ),
        'type'    => 'tab',
        'options' => [
            'preset_group' => [
                'type'    => 'group',
                'options' => [
                    'table_preset' => [
                        'type'         => 'table-style-picker',
                        'label'        => __( 'Table Preset', 'fw' ),
                        'desc'         => __( 'A reusable table look from Theme Settings → Components → Tables. Styles the whole table (header, rows, stripes, hover, footer, borders). Leave as None to use the manual options below.', 'fw' ),
                        'choices'      => function_exists( 'sc_get_table_preset_choices' ) ? sc_get_table_preset_choices() : [ '' => __( 'None', 'fw' ) ],
                        'value'        => '',
                        'allow_none'   => true,
                        'placeholder'  => __( '— No preset —', 'fw' ),
                    ],
                    'frame_preset' => [
                        'type'         => 'border-style-picker',
                        'label'        => __( 'Frame (Border Preset)', 'fw' ),
                        'desc'         => __( 'Optional outer frame wrapped around the table, using a Border Preset (Theme Settings → Components → Borders). Stacks on top of the Table Preset.', 'fw' ),
                        'choices'      => function_exists( 'sc_get_border_preset_choices' ) ? sc_get_border_preset_choices() : [ '' => __( 'None', 'fw' ) ],
                        'value'        => '',
                        'allow_none'   => true,
                        'preview_text' => __( 'Frame', 'fw' ),
                        'placeholder'  => __( '— No frame —', 'fw' ),
                    ],
                ],
            ],
            'display_group' => [
                'type'    => 'group',
                'options' => [
                    'style_striped' => [
                        'type'  => 'switch',
                        'label' => __( 'Alternating Row Colors', 'fw' ),
                        'desc'  => __( 'Zebra-stripe the body rows. Applies to tabular tables only.', 'fw' ),
                        'value' => 'yes',
                    ],
                    'style_hover' => [
                        'type'  => 'switch',
                        'label' => __( 'Row Hover Highlight', 'fw' ),
                        'desc'  => __( 'Highlight a row when the mouse hovers over it.', 'fw' ),
                        'value' => 'yes',
                    ],
                    'style_bordered' => [
                        'type'  => 'switch',
                        'label' => __( 'Bordered', 'fw' ),
                        'desc'  => __( 'Draw borders around every cell.', 'fw' ),
                        'value' => 'no',
                    ],
                    'style_condensed' => [
                        'type'  => 'switch',
                        'label' => __( 'Compact', 'fw' ),
                        'desc'  => __( 'Tighter cell padding for dense tables.', 'fw' ),
                        'value' => 'no',
                    ],
                    'sticky_header' => [
                        'type'  => 'switch',
                        'label' => __( 'Sticky Header', 'fw' ),
                        'desc'  => __( 'Keep the header row visible while scrolling the table.', 'fw' ),
                        'value' => 'no',
                    ],
                    'caption' => [
                        'type'  => 'text',
                        'label' => __( 'Caption', 'fw' ),
                        'desc'  => __( 'Optional caption shown with the table. Leave empty for none.', 'fw' ),
                        'value' => '',
                    ],
                    'caption_position' => [
                        'type'    => 'select',
                        'label'   => __( 'Caption Position', 'fw' ),
                        'value'   => 'bottom',
                        'choices' => [
                            'bottom' => __( 'Below the table', 'fw' ),
                            'top'    => __( 'Above the table', 'fw' ),
                        ],
                    ],
                ],
            ],
            'visitor_group' => [
                'type'    => 'group',
                'options' => [
                    'enable_sort' => [
                        'type'  => 'switch',
                        'label' => __( 'Sorting', 'fw' ),
                        'desc'  => __( 'Let visitors sort the table by clicking a column header. Tabular tables without merged cells only.', 'fw' ),
                        'value' => 'no',
                    ],
                    'enable_search' => [
                        'type'  => 'switch',
                        'label' => __( 'Search / Filter', 'fw' ),
                        'desc'  => __( 'Show a search box so visitors can filter rows.', 'fw' ),
                        'value' => 'no',
                    ],
                    'enable_pagination' => [
                        'type'  => 'switch',
                        'label' => __( 'Pagination', 'fw' ),
                        'desc'  => __( 'Show only a number of rows per page at a time.', 'fw' ),
                        'value' => 'no',
                    ],
                    'pagination_length' => [
                        'type'  => 'text',
                        'label' => __( 'Rows Per Page', 'fw' ),
                        'desc'  => __( 'Used when pagination is enabled.', 'fw' ),
                        'value' => '10',
                    ],
                    'enable_length_change' => [
                        'type'  => 'switch',
                        'label' => __( 'Rows-Per-Page Selector', 'fw' ),
                        'desc'  => __( 'Let visitors change how many rows are shown.', 'fw' ),
                        'value' => 'yes',
                    ],
                    'enable_info' => [
                        'type'  => 'switch',
                        'label' => __( 'Info Line', 'fw' ),
                        'desc'  => __( 'Show the “Showing X to Y of Z” summary.', 'fw' ),
                        'value' => 'yes',
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
