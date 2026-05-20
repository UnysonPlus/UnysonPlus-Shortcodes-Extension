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
                'type'  => 'upload',
            ],
            'name' => [
                'label' => __('Team Member Name', 'fw'),
                'desc'  => __('Name of the person', 'fw'),
                'type'  => 'text',
                'value' => '',
            ],
            'job' => [
                'label' => __('Team Member Job Title', 'fw'),
                'desc'  => __('Job title of the person.', 'fw'),
                'type'  => 'text',
                'value' => '',
            ],
            'desc' => [
                'label' => __('Team Member Description', 'fw'),
                'desc'  => __('Enter a few words that describe the person', 'fw'),
                'type'  => 'textarea',
                'value' => '',
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
