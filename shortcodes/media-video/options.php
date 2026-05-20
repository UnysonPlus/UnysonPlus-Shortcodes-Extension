<?php if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}

$options = [
    'tab_content' => [
        'title'   => __('Content', 'fw'),
        'type'    => 'tab',
        'options' => [
            'url' => [
                'type'  => 'text',
                'label' => __('Insert Video URL', 'fw'),
                'desc'  => __('Insert Video URL to embed this video', 'fw'),
            ],
            'width' => [
                'type'  => 'text',
                'label' => __('Video Max Width', 'fw'),
                'desc'  => __('Maximum width of the video in pixels. Do not include "px" sign.', 'fw'),
                'help'  => __('Height will be automatically handled by the aspect ratio.', 'fw'),
                'value' => 600,
            ],
            'ratio' => [
                'type'    => 'select',
                'label'   => __('Aspect Ratio', 'fw'),
                'desc'    => __('Choose the aspect ratio for the video. Portrait ratios available too.'),
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
