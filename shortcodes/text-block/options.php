<?php
if ( ! defined( 'FW' ) ) { die( 'Forbidden' ); }

$options = [
    'tab_text' => [
        'title'   => __('Content', 'fw'),
        'type'    => 'tab',
        'options' => [
            'text' => [
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
        ],
    ],
    'tab_advanced' => sc_get_advanced_tab(),
];