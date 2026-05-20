<?php if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}

$options = [
    'tab_content' => [
        'title'   => __('Content', 'fw'),
        'type'    => 'tab',
        'options' => [
            'tabs' => [
                'type'          => 'addable-popup',
                'label'         => __( 'Tabs', 'fw' ),
                'popup-title'   => __( 'Add/Edit Tabs', 'fw' ),
                'desc'          => __( 'Create your tabs', 'fw' ),
                'template'      => '{{=tab_title}}',
                'popup-options' => [
                    'tab_title'   => [
                        'type'  => 'text',
                        'label' => __('Title', 'fw')
                    ],
                    'tab_content' => [
                        'type'  => 'wp-editor',
                        'label' => __('Content', 'fw')
                    ]
                ]
            ],
        ],
    ],

    'tab_layout' => [
        'title'   => __( 'Layout', 'fw' ),
        'type'    => 'tab',
        'options' => [
            'group_layout' => [
                'type'    => 'group',
                'options' => [

            'icon_style' => [
                'type'    => 'select',
                'label'   => __( 'Icon Style', 'fw' ),
                'desc'    => __( 'Choose the toggle indicator icon.', 'fw' ),
                'choices' => [
                    'plus-minus' => __( 'Plus / Minus (+−)', 'fw' ),
                    'plus-x'     => __( 'Plus / X (+×)', 'fw' ),
                    'chevron'    => __( 'Chevron (›)', 'fw' ),
                    'arrow'      => __( 'Arrow (▶)', 'fw' ),
                    'none'       => __( 'No Icon', 'fw' ),
                    'custom'     => __( 'Custom (image or text/emoji)', 'fw' ),
                ],
                'value'   => 'plus-minus',
            ],
            'icon_position' => [
                'type'    => 'select',
                'label'   => __( 'Icon Position', 'fw' ),
                'desc'    => __( 'Place the icon on the left or right side of the title.', 'fw' ),
                'choices' => [
                    'left'  => __( 'Left', 'fw' ),
                    'right' => __( 'Right', 'fw' ),
                ],
                'value'   => 'left',
            ],
            'icon_closed_image' => [
                'type'  => 'upload',
                'label' => __( 'Custom Closed-State Image', 'fw' ),
                'desc'  => __( 'Used when Icon Style is "Custom" and the panel is closed. PNG, JPG, or SVG. Overrides the closed-state text/emoji below.', 'fw' ),
            ],
            'icon_open_image' => [
                'type'  => 'upload',
                'label' => __( 'Custom Open-State Image', 'fw' ),
                'desc'  => __( 'Used when Icon Style is "Custom" and the panel is open. Overrides the open-state text/emoji below.', 'fw' ),
            ],
            'icon_closed_text' => [
                'type'  => 'short-text',
                'label' => __( 'Custom Closed-State Text', 'fw' ),
                'desc'  => __( 'Used when Icon Style is "Custom" and no closed-state image is uploaded. Examples: + ▼ ▶ 👇', 'fw' ),
                'value' => '+',
            ],
            'icon_open_text' => [
                'type'  => 'short-text',
                'label' => __( 'Custom Open-State Text', 'fw' ),
                'desc'  => __( 'Used when Icon Style is "Custom" and no open-state image is uploaded. Examples: − ▲ ▼ 👆', 'fw' ),
                'value' => '−',
            ],
            'numbering' => [
                'type'   => 'multi-picker',
                'label'  => false,
                'desc'   => false,
                'picker' => [
                    'style' => [
                        'type'    => 'select',
                        'label'   => __( 'Item Numbering', 'fw' ),
                        'desc'    => __( 'Prefix each title with a number, letter, or custom label.', 'fw' ),
                        'choices' => [
                            'none'                 => __( 'None', 'fw' ),
                            'decimal'              => __( '1, 2, 3', 'fw' ),
                            'decimal-leading-zero' => __( '01, 02, 03', 'fw' ),
                            'lower-alpha'          => __( 'a, b, c', 'fw' ),
                            'upper-alpha'          => __( 'A, B, C', 'fw' ),
                            'lower-roman'          => __( 'i, ii, iii', 'fw' ),
                            'upper-roman'          => __( 'I, II, III', 'fw' ),
                            'q-prefix'             => __( 'Q1, Q2, Q3', 'fw' ),
                            'custom'               => __( 'Custom…', 'fw' ),
                        ],
                        'value'   => 'none',
                    ],
                ],
                'choices' => [
                    'none'                 => [],
                    'decimal'              => [],
                    'decimal-leading-zero' => [],
                    'lower-alpha'          => [],
                    'upper-alpha'          => [],
                    'lower-roman'          => [],
                    'upper-roman'          => [],
                    'q-prefix'             => [],
                    'custom' => [
                        'template' => [
                            'type'  => 'text',
                            'label' => __( 'Custom Template', 'fw' ),
                            'desc'  => __( 'Tokens: {n}=1,2,3 — {0n}=01,02,03 — {a}/{A}=letters — {i}/{I}=Roman. Example: "Q{n}" or "Step {n}".', 'fw' ),
                            'value' => 'Q{n}',
                        ],
                    ],
                ],
                'show_borders' => false,
            ],
            'numbering_start' => [
                'type'  => 'short-text',
                'label' => __( 'Start Number', 'fw' ),
                'desc'  => __( 'The number assigned to the first item. Defaults to 1. Use any integer to begin elsewhere (e.g. 5 to start at Q5 / e. / V).', 'fw' ),
                'value' => '1',
            ],
        ],],],
    ],

    'tab_behaviour' => [
        'title'   => __( 'Behaviour', 'fw' ),
        'type'    => 'tab',
        'options' => [
            'group_layout' => [
                'type'    => 'group',
                'options' => [



            'initially_open' => [
                'type'    => 'select',
                'label'   => __( 'Initially Open', 'fw' ),
                'desc'    => __( 'Which panels are expanded when the page loads.', 'fw' ),
                'choices' => [
                    'first' => __( 'First Item', 'fw' ),
                    'none'  => __( 'None (All Closed)', 'fw' ),
                    'all'   => __( 'All Open', 'fw' ),
                ],
                'value'   => 'first',
            ],
            'collapsible' => [
                'type'         => 'switch',
                'label'        => __( 'Collapsible', 'fw' ),
                'desc'         => __( 'Allow all panels to be closed at once.', 'fw' ),
                'right-choice' => [
                    'value' => 'yes',
                    'label' => __( 'Yes', 'fw' ),
                ],
                'left-choice'  => [
                    'value' => 'no',
                    'label' => __( 'No', 'fw' ),
                ],
                'value'        => 'no',
            ],
            'multiple_open' => [
                'type'         => 'switch',
                'label'        => __( 'Multiple Open', 'fw' ),
                'desc'         => __( 'Allow more than one panel to be open at a time.', 'fw' ),
                'right-choice' => [
                    'value' => 'yes',
                    'label' => __( 'Yes', 'fw' ),
                ],
                'left-choice'  => [
                    'value' => 'no',
                    'label' => __( 'No', 'fw' ),
                ],
                'value'        => 'no',
            ],
        ],],],
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
                'options' => sc_get_advanced_tab(),
            ],
        ],
    ],
];
