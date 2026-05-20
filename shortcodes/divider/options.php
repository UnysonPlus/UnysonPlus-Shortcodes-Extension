<?php if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}

$options = [
    'tab_content' => [
        'title'   => __( 'Content', 'fw' ),
        'type'    => 'tab',
        'options' => [
            'style' => [
                'type'   => 'multi-picker',
                'label'  => false,
                'desc'   => false,
                'picker' => [
                    'ruler_type' => [
                        'type'    => 'select',
                        'label'   => __( 'Ruler Type', 'fw' ),
                        'choices' => [
                            'line'  => __( 'Line / Divider', 'fw' ),
                            'space' => __( 'Whitespace', 'fw' ),
                        ]
                    ]
                ],
                'choices' => [
                    'line' => [
                        'line_design' => [
                            'type'    => 'select',
                            'label'   => __( 'Line Design', 'fw' ),
                            'choices' => [
                                'std'      => __( 'Standard (Solid)', 'fw' ),
                                'gradient' => __( 'Gradient Fade', 'fw' ),
                                'ornament' => __( 'Ornamental / Glyph', 'fw' ),
                                'shadow'   => __( 'Inner Shadow', 'fw' ),
                            ]
                        ],
                        'content_type' => [
                            'type'    => 'select',
                            'label'   => __( 'Add Element', 'fw' ),
                            'choices' => [
                                'none' => __( 'None', 'fw' ),
                                'text' => __( 'Text / Title', 'fw' ),
                                'icon' => __( 'Icon', 'fw' ),
                            ]
                        ],
                        'title' => [
                            'type'  => 'text',
                            'label' => __( 'Divider Text', 'fw' ),
                            'condition' => [ 'content_type' => 'text' ],
                        ],
                        'icon' => [
                            'type'  => 'icon',
                            'label' => __( 'Select Icon', 'fw' ),
                            'condition' => [ 'content_type' => 'icon' ],
                        ],
                        'alignment' => [
                            'type'    => 'select',
                            'label'   => __( 'Content Alignment', 'fw' ),
                            'choices' => [
                                'center' => __( 'Center', 'fw' ),
                                'left'   => __( 'Left', 'fw' ),
                                'right'  => __( 'Right', 'fw' ),
                            ],
                            'condition' => [ 'content_type' => ['text', 'icon'] ],
                        ],
                    ],
                    'space' => [
                        'height' => [
                            'label' => __( 'Height (px)', 'fw' ),
                            'type'  => 'text',
                            'value' => '50'
                        ]
                    ]
                ]
            ]
        ]
    ],
    'tab_layout' => [
        'title'   => __( 'Layout', 'fw' ),
        'type'    => 'tab',
        'options' => [
            'margin_top'    => [ 'type' => 'text', 'label' => __( 'Margin Top (px)', 'fw' ) ],
            'margin_bottom' => [ 'type' => 'text', 'label' => __( 'Margin Bottom (px)', 'fw' ) ],
            'width'         => [ 'type' => 'text', 'label' => __( 'Width (%)', 'fw' ), 'value' => '100' ],
        ]
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
