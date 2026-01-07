<?php if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}

$map_shortcode = fw_ext('shortcodes')->get_shortcode('map');

$options = [
    'tab_content' => [
        'title'   => __('Content', 'fw'),
        'type'    => 'tab',
        'options' => [
            'data_provider' => [
                'type'  => 'multi-picker',
                'label' => false,
                'desc'  => false,
                'picker' => [
                    'population_method' => [
                        'label'   => __('Population Method', 'fw'),
                        'desc'    => __('Select map population method (Ex: events, custom)', 'fw'),
                        'type'    => 'select',
                        'choices' => $map_shortcode->_get_picker_dropdown_choices(),
                    ]
                ],
                'choices'      => $map_shortcode->_get_picker_choices(),
                'show_borders' => false,
                'hide_picker'  => true,
            ],
            'gmap-key' => array_merge(
                [
                    'label' => __('Google Maps API Key', 'fw'),
                    'desc'  => sprintf(
                        __('Create an application in %sGoogle Console%s and add the Key here.', 'fw'),
                        '<a href="https://console.developers.google.com/flows/enableapi?apiid=places_backend,maps_backend,geocoding_backend,directions_backend,distance_matrix_backend,elevation_backend&keyType=CLIENT_SIDE&reusekey=true">',
                        '</a>'
                    ),
                ],
                version_compare(fw()->manifest->get_version(), '2.5.7', '>=')
                ? ['type' => 'gmap-key']
                : [
                    'type'       => 'text',
                    'fw-storage' => [
                        'type'      => 'wp-option',
                        'wp_option' => 'fw-option-types:gmap-key',
                    ],
                ]
            ),
            'map_type' => [
                'type'    => 'select',
                'label'   => __('Map Type', 'fw'),
                'desc'    => __('Select map type', 'fw'),
                'choices' => [
                    'roadmap'   => __('Roadmap', 'fw'),
                    'terrain'   => __('Terrain', 'fw'),
                    'satellite' => __('Satellite', 'fw'),
                    'hybrid'    => __('Hybrid', 'fw'),
                ],
            ],
            'map_height' => [
                'label' => __('Map Height', 'fw'),
                'desc'  => __('Set map height (Ex: 300)', 'fw'),
                'type'  => 'text',
            ],
            'disable_scrolling' => [
                'type'  => 'switch',
                'value' => false,
                'label' => __('Disable zoom on scroll', 'fw'),
                'desc'  => __('Prevent the map from zooming when scrolling until clicking on the map', 'fw'),
                'left-choice'  => [
                    'value' => false,
                    'label' => __('Yes', 'fw'),
                ],
                'right-choice' => [
                    'value' => true,
                    'label' => __('No', 'fw'),
                ],
            ],
        ],
    ],

    'tab_advanced' => sc_get_advanced_tab(),
];
