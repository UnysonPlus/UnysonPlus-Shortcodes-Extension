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
                    'title' => [
                        'type'  => 'text',
                        'label' => __('Title', 'fw'),
                        'desc'  => __('Write the heading title content', 'fw'),
                    ],
                    'subtitle' => [
                        'type'  => 'text',
                        'label' => __('Subtitle', 'fw'),
                        'desc'  => __('Write the heading subtitle content', 'fw'),
                    ],
                    'heading' => [
                        'type'    => 'select',
                        'label'   => __('Title Tag', 'fw'),
                        'desc'    => __('Select the Title\'s heading tag', 'fw'),
                        'choices' => [
                            'h1' => 'H1',
                            'h2' => 'H2',
                            'h3' => 'H3',
                            'h4' => 'H4',
                            'h5' => 'H5',
                            'h6' => 'H6',
                        ],
                        'value' => 'h2'
                    ],
                    'centered' => [
                        'type'  => 'switch',
                        'label' => __('Centered', 'fw'),
                        'left-choice' => [
                            'value' => 'no',
                            'label' => __('No', 'fw'),
                        ],
                        'right-choice' => [
                            'value' => 'yes',
                            'label' => __('Yes', 'fw'),
                        ],
                        'value' => 'no' 
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
                        'title_class' => [
                            'label' => __('Title Class', 'fw'),
                            'desc'  => false,
                            'type'  => 'text',
                        ],
                        'subtitle_class' => [
                            'label' => __('Subtitle Class', 'fw'),
                            'desc'  => false,
                            'type'  => 'text',
                        ],
                    ]
                ),
            ],
        ],
    ],
];
