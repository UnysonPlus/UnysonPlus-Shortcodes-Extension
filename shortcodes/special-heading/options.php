<?php if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}

$options = [
    'tab_content' => [
        'title'   => __('Content', 'fw'),
        'type'    => 'tab',
        'options' => [
            'title' => [
                'type'  => 'text',
                'label' => __('Heading Title', 'fw'),
                'desc'  => __('Write the heading title content', 'fw'),
            ],
            'subtitle' => [
                'type'  => 'text',
                'label' => __('Heading Subtitle', 'fw'),
                'desc'  => __('Write the heading subtitle content', 'fw'),
            ],
            'heading' => [
                'type'    => 'select',
                'label'   => __('Heading Size', 'fw'),
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

    'tab_advanced' => sc_get_advanced_tab(),
];
