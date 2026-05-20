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
                    'title'   => [
                        'type'  => 'text',
                        'label' => __('Title', 'fw'),
                        'desc'  => __('This can be left blank', 'fw')
                    ],
                    'message' => [
                        'type'   => 'wp-editor',
                        //'teeny'  => false, //Whether to output the minimal editor configuration used in PressThis
                        'reinit' => true,
                        'label'  => __('Content', 'fw'),
                        'desc'   => __('Enter content for this text block', 'fw'),
                        'tinymce' => true, //Load TinyMCE, can be used to pass settings directly to TinyMCE using an array. Default: true
                        'size' => 'large',
                        'editor_height' => 425, //The height to set the editor in pixels. If set, will be used instead of textarea_rows. 
                        //'editor_type' => 'tinymce',
                        'shortcodes' => true,
                        'wpautop' => true, //Whether to use wpautop for adding in paragraphs. Note that the paragraphs are added automatically when wpautop is false.
                        'value' => ''
                    ],
                    'button_label' => [
                        'label' => __('Button Label', 'fw'),
                        'desc'  => __('This is the text that appears on your button', 'fw'),
                        'type'  => 'text',
                        'value' => 'Click'
                    ],
                    'button_link' => [
                        'label' => __('Button Link', 'fw'),
                        'desc'  => __('Where should your button link to', 'fw'),
                        'type'  => 'text',
                        'value' => '#'
                    ],
                    'button_target' => [
                        'type'  => 'switch',
                        'label' => __('Open Link in New Window', 'fw'),
                        'desc'  => __('Select here if you want to open the linked page in a new window', 'fw'),
                        'right-choice' => [
                            'value' => '_blank',
                            'label' => __('Yes', 'fw'),
                        ],
                        'left-choice' => [
                            'value' => '_self',
                            'label' => __('No', 'fw'),
                        ],
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
