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
                'show_borders' => false,
                // Default = a standard solid line with no inline content. Without
                // this, a freshly-added divider has an empty ruler_type and the
                // view falls through to the invisible whitespace branch.
                'value'  => [
                    'ruler_type' => 'line',
                    'line'       => [
                        'line_design'  => 'std',
                        'content_type' => 'none',
                        'alignment'    => 'center',
                    ],
                ],
                'picker' => [
                    'ruler_type' => [
                        'type'    => 'select',
                        'label'   => __( 'Ruler Type', 'fw' ),
                        'help'    => __( 'Line draws a visible separator (optionally with text or an icon); Whitespace inserts an invisible vertical gap to space sections apart.', 'fw' ),
                        'choices' => [
                            'line'  => __( 'Line / Divider', 'fw' ),
                            'shape' => __( 'Shape / SVG', 'fw' ),
                            'space' => __( 'Whitespace', 'fw' ),
                        ]
                    ]
                ],
                'choices' => [
                    'line' => [
                        'line_design' => [
                            'type'    => 'select',
                            'label'   => __( 'Line Design', 'fw' ),
                            'help'    => __( 'Gradient Fade tapers off at both ends for a softer look; Ornamental adds a decorative glyph in the middle. Pick to match the page tone.', 'fw' ),
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
                            'help'    => __( 'Places text or an icon centered on the line, splitting it in two (like an "OR" separator). Choose None for a plain unbroken rule.', 'fw' ),
                            'choices' => [
                                'none' => __( 'None', 'fw' ),
                                'text' => __( 'Text / Title', 'fw' ),
                                'icon' => __( 'Icon', 'fw' ),
                            ]
                        ],
                        'title' => [
                            'type'  => 'text',
                            'label' => __( 'Divider Text', 'fw' ),
                            'help'  => __( 'Keep it short, such as "OR" or "Section Two", since this sits inline on the divider line.', 'fw' ),
                            'condition' => [ 'content_type' => 'text' ],
                        ],
                        'icon' => [
                            'type'  => 'icon',
                            'label' => __( 'Select Icon', 'fw' ),
                            'help'  => __( 'Pick a small, simple glyph; it is centered on the line, so intricate icons can look cramped at this size.', 'fw' ),
                            'condition' => [ 'content_type' => 'icon' ],
                        ],
                        'alignment' => [
                            'type'    => 'select',
                            'label'   => __( 'Content Alignment', 'fw' ),
                            'help'    => __( 'Positions the text or icon along the line. Left or Right pushes it to one side, leaving a longer rule on the other.', 'fw' ),
                            'choices' => [
                                'center' => __( 'Center', 'fw' ),
                                'left'   => __( 'Left', 'fw' ),
                                'right'  => __( 'Right', 'fw' ),
                            ],
                            'condition' => [ 'content_type' => ['text', 'icon'] ],
                        ],
                    ],
                    'shape' => [
                        'shape_style' => [
                            'type'    => 'select',
                            'label'   => __( 'Shape', 'fw' ),
                            'help'    => __( 'A decorative SVG silhouette stretched across the full width — great as a section boundary or a fancy separator.', 'fw' ),
                            'value'   => 'waves',
                            'choices' => [
                                'waves'    => __( 'Waves', 'fw' ),
                                'wave'     => __( 'Single Wave', 'fw' ),
                                'curve'    => __( 'Curve', 'fw' ),
                                'tilt'     => __( 'Tilt / Diagonal', 'fw' ),
                                'triangle' => __( 'Triangle', 'fw' ),
                                'zigzag'   => __( 'Zigzag / Peaks', 'fw' ),
                                'arrow'    => __( 'Arrow', 'fw' ),
                            ],
                        ],
                        'shape_height' => [
                            'type'  => 'text',
                            'label' => __( 'Shape Height (px)', 'fw' ),
                            'help'  => __( 'Vertical size of the shape band, in pixels. The shape stretches to the full width.', 'fw' ),
                            'value' => '60',
                        ],
                        'shape_flip_x' => [
                            'type'         => 'switch',
                            'label'        => __( 'Flip Horizontally', 'fw' ),
                            'right-choice' => [ 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ],
                            'left-choice'  => [ 'value' => 'no',  'label' => __( 'No',  'fw' ) ],
                            'value'        => 'no',
                        ],
                        'shape_flip_y' => [
                            'type'         => 'switch',
                            'label'        => __( 'Flip Vertically', 'fw' ),
                            'help'         => __( 'Point the shape downward instead of upward.', 'fw' ),
                            'right-choice' => [ 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ],
                            'left-choice'  => [ 'value' => 'no',  'label' => __( 'No',  'fw' ) ],
                            'value'        => 'no',
                        ],
                    ],
                    'space' => [
                        'height' => [
                            'label' => __( 'Height (px)', 'fw' ),
                            'help'  => __( 'The size of the invisible vertical gap, in pixels. Enter a number only, e.g. 50. Larger values push the next section further down.', 'fw' ),
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
            'margin_top'    => [ 'type' => 'text', 'label' => __( 'Margin Top (px)', 'fw' ), 'help' => __( 'Space above the divider, in pixels (number only). Combine with Margin Bottom to control how far it sits from the surrounding content.', 'fw' ) ],
            'margin_bottom' => [ 'type' => 'text', 'label' => __( 'Margin Bottom (px)', 'fw' ), 'help' => __( 'Space below the divider, in pixels (number only).', 'fw' ) ],
            'width'         => [ 'type' => 'text', 'label' => __( 'Width (%)', 'fw' ), 'help' => __( 'Divider width as a percentage of its container. Use a value under 100 for a short centered rule rather than a full-width line.', 'fw' ), 'value' => '100' ],
        ]
    ],
    'tab_styling' => [
        'title'   => __( 'Styling', 'fw' ),
        'type'    => 'tab',
        // Drop the default wrapper-level Text Color — Line / Icon / Text
        // colors below cover every visible element, so a wrapper Text
        // Color would just compete with the per-element picks.
        'options' => [
            'group_colors' => [
                'type'    => 'group',
                'options' => [
                    'bg_color' => sc_color_field_compact( array( 'label' => __( 'Background Color', 'fw' ), 'kind' => 'bg' ) ),
                    'line_color' => sc_color_field_compact( array(
                        'label' => __( 'Line Color', 'fw' ),
                        'desc'  => __( 'Color of the divider line itself (border / gradient). Falls back to currentColor.', 'fw' ),
                    ) ),
                    'icon_color' => sc_color_field_compact( array(
                        'label' => __( 'Icon Color', 'fw' ),
                        'desc'  => __( 'Color of the centered icon (when "Add Element" = Icon).', 'fw' ),
                    ) ),
                    'divider_text_color' => sc_color_field_compact( array(
                        'label' => __( 'Divider Text Color', 'fw' ),
                        'desc'  => __( 'Color of the centered text (when "Add Element" = Text / Title).', 'fw' ),
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
