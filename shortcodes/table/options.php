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
