<?php if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}

$options = [
    'tab_content' => [
        'title'   => __('Content', 'fw'),
        'type'    => 'tab',
        'options' => [
            'image' => [
                'type'  => 'upload',
                'label' => __('Choose Image', 'fw'),
                'desc'  => __('Either upload a new, or choose an existing image from your media library', 'fw'),
            ],
            'size' => [
                'type'    => 'group',
                'options' => [
                    'width' => [
                        'type'  => 'text',
                        'label' => __('Width', 'fw'),
                        'desc'  => __('Set image width', 'fw'),
                        'value' => 300,
                    ],
                    'height' => [
                        'type'  => 'text',
                        'label' => __('Height', 'fw'),
                        'desc'  => __('Set image height', 'fw'),
                        'value' => 200,
                    ],
                ],
            ],
            'image-link-group' => [
                'type'    => 'group',
                'options' => [
                    'link' => [
                        'type'  => 'text',
                        'label' => __('Image Link', 'fw'),
                        'desc'  => __('Where should your image link to?', 'fw'),
                    ],
                    'target' => [
                        'type'         => 'switch',
                        'label'        => __('Open Link in New Window', 'fw'),
                        'desc'         => __('Select here if you want to open the linked page in a new window', 'fw'),
                        'right-choice' => [
                            'value' => '_blank',
                            'label' => __('Yes', 'fw'),
                        ],
                        'left-choice'  => [
                            'value' => '_self',
                            'label' => __('No', 'fw'),
                        ],
                    ],
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
