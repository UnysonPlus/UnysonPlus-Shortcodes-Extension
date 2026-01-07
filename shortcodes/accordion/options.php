<?php if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}

$options = [
    'tab_content' => [
        'title'   => __('Content', 'fw'),
        'type'    => 'tab',
        'options' => [
            'tabs' => [
                'type'          => 'addable-popup',
                'label'         => __( 'Tabs', 'fw' ),
                'popup-title'   => __( 'Add/Edit Tabs', 'fw' ),
                'desc'          => __( 'Create your tabs', 'fw' ),
                'template'      => '{{=tab_title}}',
                'popup-options' => [
                    'tab_title'   => [
                        'type'  => 'text',
                        'label' => __('Title', 'fw')
                    ],
                    'tab_content' => [
                        'type'  => 'textarea',
                        'label' => __('Content', 'fw')
                    ]
                ]
            ],
        ],
    ],
    
    'tab_advanced' => sc_get_advanced_tab(),
];
