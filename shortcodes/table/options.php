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

    'tab_advanced' => sc_get_advanced_tab(),
];
