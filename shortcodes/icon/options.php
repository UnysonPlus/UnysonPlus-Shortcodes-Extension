<?php if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}

$options = [
    'tab_content' => [
        'title'   => __('Content', 'fw'),
        'type'    => 'tab',
        'options' => [
            'icon' => [
                'type'  => 'icon-v2',
                'label' => __('Icon', 'fw'),
                'preview_size' => 'medium',
                'modal_size' => 'medium',
            ],
            'title' => [
                'type'  => 'text',
                'label' => __('Title', 'fw'),
                'desc'  => __('Icon title', 'fw'),
            ],
        ],
    ],

    'tab_advanced' => sc_get_advanced_tab(),
];
