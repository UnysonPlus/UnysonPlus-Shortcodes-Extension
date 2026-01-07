<?php if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}

$options = [
    'tab_content' => [
        'title'   => __('Content', 'fw'),
        'type'    => 'tab',
        'options' => [
            'style' => [
                'type'  => 'multi-picker',
                'label' => false,
                'desc'  => false,
                'picker' => [
                    'ruler_type' => [
                        'type'    => 'select',
                        'label'   => __('Ruler Type', 'fw'),
                        'desc'    => __('Here you can set the styling and size of the HR element', 'fw'),
                        'choices' => [
                            'line'  => __('Line', 'fw'),
                            'space' => __('Whitespace', 'fw'),
                        ]
                    ]
                ],
                'choices' => [
                    'space' => [
                        'height' => [
                            'label' => __('Height', 'fw'),
                            'desc'  => __('How much whitespace do you need? Enter a pixel value. Positive value will increase the whitespace, negative value will reduce it. eg: \'50\', \'-25\', \'200\'', 'fw'),
                            'type'  => 'text',
                            'value' => '50'
                        ]
                    ]
                ]
            ]
        ]
    ],

    'tab_advanced' => sc_get_advanced_tab(),
];
