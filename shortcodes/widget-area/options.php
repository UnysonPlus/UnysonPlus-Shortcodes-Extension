<?php if (!defined('FW')) die('Forbidden');

$options = [
    'tab_content' => [
        'title'   => __('Content', 'fw'),
        'type'    => 'tab',
        'options' => [
            'sidebar' => [
                'label'   => __('Sidebar', 'fw'),
                'desc'    => '',
                'type'    => 'select',
                'choices' => FW_Shortcode_Widget_Area::get_sidebars(),
            ],
        ],
    ],

    'tab_advanced' => sc_get_advanced_tab(),
];
