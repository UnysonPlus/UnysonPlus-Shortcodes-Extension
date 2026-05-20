<?php if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}

$options = [
    'tab_content' => [
        'title'   => __('Content', 'fw'),
        'type'    => 'tab',
        'options' => [
            'message' => [
                'label' => __('Message', 'fw'),
                'desc'  => __('Notification message', 'fw'),
                'type'  => 'text',
                'value' => __('Message!', 'fw'),
            ],
            'type' => [
                'label'   => __('Type', 'fw'),
                'desc'    => __('Notification type', 'fw'),
                'type'    => 'select',
                'value'   => 'info',
                'choices' => [
                    'primary'   => __('Primary', 'fw'),
                    'secondary' => __('Secondary', 'fw'),
                    'success'   => __('Success', 'fw'),
                    'info'      => __('Information', 'fw'),
                    'warning'   => __('Warning', 'fw'),
                    'danger'    => __('Danger', 'fw'),
                    'light'     => __('Light', 'fw'),
                    'dark'      => __('Dark', 'fw'),
                ],
            ],
            'dismissible' => [
                'label'        => __('Dismissible', 'fw'),
                'desc'         => __('Enable a close button so users can dismiss the alert', 'fw'),
                'type'         => 'switch',
                'right-choice' => [
                    'value' => true,
                    'label' => __('Yes', 'fw'),
                ],
                'left-choice'  => [
                    'value' => false,
                    'label' => __('No', 'fw'),
                ],
                'value'        => false,
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
