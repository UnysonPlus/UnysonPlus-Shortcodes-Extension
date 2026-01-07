<?php if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}

$options = [
    'tab_content' => [
        'title'   => __('Content', 'fw'),
        'type'    => 'tab',
        'options' => [
            'image' => [
                'label' => __('Team Member Image', 'fw'),
                'desc'  => __('Either upload a new, or choose an existing image from your media library', 'fw'),
                'type'  => 'upload',
            ],
            'name' => [
                'label' => __('Team Member Name', 'fw'),
                'desc'  => __('Name of the person', 'fw'),
                'type'  => 'text',
                'value' => '',
            ],
            'job' => [
                'label' => __('Team Member Job Title', 'fw'),
                'desc'  => __('Job title of the person.', 'fw'),
                'type'  => 'text',
                'value' => '',
            ],
            'desc' => [
                'label' => __('Team Member Description', 'fw'),
                'desc'  => __('Enter a few words that describe the person', 'fw'),
                'type'  => 'textarea',
                'value' => '',
            ],
        ],
    ],

    'tab_advanced' => sc_get_advanced_tab(),
];
