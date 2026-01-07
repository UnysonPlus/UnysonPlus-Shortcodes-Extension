<?php if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}

$options = [
    'tab_content' => [
        'title'   => __('Content', 'fw'),
        'type'    => 'tab',
        'options' => [
            'style' => [
                'type'    => 'select',
                'label'   => __('Box Style', 'fw'),
                'choices' => [
                    'iconbox-1' => __('Icon above title', 'fw'),
                    'iconbox-2' => __('Icon in line with title', 'fw'),
                ],
            ],
            'icon' => [
                'type'  => 'icon',
                'label' => __('Choose an Icon', 'fw'),
            ],
            'title' => [
                'type'  => 'text',
                'label' => __('Title of the Box', 'fw'),
            ],
            'content' => [
                'type'  => 'textarea',
                'label' => __('Content', 'fw'),
                'desc'  => __('Enter the desired content', 'fw'),
            ],
        ],
    ],

    'tab_advanced' => sc_get_advanced_tab(),
];
