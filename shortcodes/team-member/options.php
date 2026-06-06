<?php if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}

$options = [
    'tab_content' => [
        'title'   => __('Content', 'fw'),
        'type'    => 'tab',
        'options' => [
            'image' => [
                'label' => __('Team Member Image', 'fw'),
                'desc'  => __('Either upload a new, or choose an existing image from your media library', 'fw'),
                'help'  => __('Use a square headshot for the most consistent look across multiple team members in a row.', 'fw'),
                'type'  => 'upload',
            ],
            'name' => [
                'label' => __('Team Member Name', 'fw'),
                'desc'  => __('Name of the person', 'fw'),
                'help'  => __('Shown as the main heading, e.g. "Jane Doe". This is the most prominent text in the card.', 'fw'),
                'type'  => 'text',
                'value' => '',
            ],
            'job' => [
                'label' => __('Team Member Job Title', 'fw'),
                'desc'  => __('Job title of the person.', 'fw'),
                'help'  => __('The role shown under the name, e.g. "Marketing Director". Keep it short so it fits on one line.', 'fw'),
                'type'  => 'text',
                'value' => '',
            ],
            'desc' => [
                'label' => __('Team Member Description', 'fw'),
                'desc'  => __('Enter a few words that describe the person', 'fw'),
                'help'  => __('A short bio or specialty line. Keep lengths similar across members so cards line up evenly.', 'fw'),
                'type'  => 'textarea',
                'value' => '',
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
