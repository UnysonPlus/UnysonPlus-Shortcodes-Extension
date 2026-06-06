<?php if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}

$options = [
    'tab_content' => [
        'title'   => __('Content', 'fw'),
        'type'    => 'tab',
        'options' => [
            'group_content' => [
            'type'    => 'group',
            'options' => [
            'url' => [
                'type'            => 'text',
                'label'           => __('Insert Video URL', 'fw'),
                'desc'            => __('Insert Video URL to embed this video', 'fw'),
                'help'            => __('Paste a YouTube, Vimeo, or other oEmbed-supported page URL (e.g. https://youtu.be/xxxx) - not the raw .mp4 file or the iframe embed code.', 'fw'),
                'dynamic_content' => false,
            ],
            'width' => [
                'type'  => 'unit-input',
                'label' => __('Video Max Width', 'fw'),
                'desc'  => __('Maximum width of the video (Ex: 600px, 80%).', 'fw'),
                'help'  => __('Pick a number and a unit. "px" is a fixed cap; "%" / "vw" are relative to the container / viewport. The video stays centered and the height follows the aspect ratio.', 'fw'),
                'value' => [ 'value' => 600, 'unit' => 'px' ],
                'units' => [ 'px', '%', 'vw', 'rem', 'em' ],
            ],
            'ratio' => [
                'type'    => 'select',
                'label'   => __('Aspect Ratio', 'fw'),
                'desc'    => __('Choose the aspect ratio for the video. Portrait ratios available too.'),
                'help'    => __('Match the ratio to the source video to avoid letterboxing. Use 16:9 for most modern videos, or a Portrait ratio for vertical clips.', 'fw'),
                'value'   => '16x9',
                'choices' => [
                    '16x9' => 'Landscape 16:9 (Widescreen)',
                    '4x3'  => 'Landscape 4:3 (Standard)',
                    '1x1'  => 'Square 1:1',
                    '21x9' => 'Landscape 21:9 (Ultra Wide)',
                    '9x16' => 'Portrait 9:16 (Widescreen)',
                    '3x4'  => 'Portrait 3:4 (Standard)',
                ],
            ],
            ], // group_content options
            ], // group_content
        ],
    ],

    'tab_styling' => [
        'title'   => __( 'Styling', 'fw' ),
        'type'    => 'tab',
        'options' => [
            'group_colors' => [
                'type'    => 'group',
                'options' => [
                    'bg_color' => sc_color_field_compact( array( 'label' => __( 'Background Color', 'fw' ), 'kind' => 'bg' ) ),
                ],
            ],
            'group_spacings' => [
                'type'    => 'group',
                'options' => [
                    'spacing'  => array(
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
