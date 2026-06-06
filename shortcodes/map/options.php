<?php if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}

$map_shortcode = fw_ext('shortcodes')->get_shortcode('map');

$options = [
    'tab_content' => [
        'title'   => __('Content', 'fw'),
        'type'    => 'tab',
        'options' => [
            'group_content' => [
            'type'    => 'group',
            'options' => [
            'data_provider' => [
                'type'  => 'multi-picker',
                'label' => false,
                'desc'  => false,
                'picker' => [
                    'population_method' => [
                        'label'   => __('Population Method', 'fw'),
                        'desc'    => __('Select map population method (Ex: events, custom)', 'fw'),
                        'help'    => __('"Custom" lets you place pins manually; "Events" auto-plots locations pulled from your events. Changing this swaps the fields shown below.', 'fw'),
                        'type'    => 'select',
                        'choices' => $map_shortcode->_get_picker_dropdown_choices(),
                    ]
                ],
                'choices'      => $map_shortcode->_get_picker_choices(),
                'show_borders' => false,
                'hide_picker'  => true,
            ],
            'map_engine' => [
                'type'   => 'multi-picker',
                'label'  => false,
                'desc'   => false,
                'picker' => [
                    'engine' => [
                        'type'    => 'select',
                        'label'   => __('Map Engine', 'fw'),
                        'desc'    => __('Select which map provider to use', 'fw'),
                        'help'    => __('"OpenStreetMap" is free and needs no API key. "Google Maps" needs a Google Maps API key with billing enabled in Google Cloud. Changing this swaps the fields shown below.', 'fw'),
                        'choices' => [
                            'osm'    => __('OpenStreetMap (free, no API key)', 'fw'),
                            'google' => __('Google Maps (requires API key)', 'fw'),
                        ],
                    ],
                ],
                'choices' => [
                    // ---- OpenStreetMap (Leaflet) ------------------------------------
                    // Map Style is itself a multi-picker: pick a provider, and only
                    // that provider's variant select + API-key field (if any) show.
                    'osm' => [
                        'osm_style' => [
                            'type'   => 'multi-picker',
                            'label'  => false,
                            'desc'   => false,
                            'picker' => [
                                'provider' => [
                                    'type'    => 'select',
                                    'label'   => __('Map Style', 'fw'),
                                    'desc'    => __('Tile provider. Providers marked "(API key)" reveal a key field below; the rest are keyless.', 'fw'),
                                    'help'    => sprintf(
                                        /* translators: %s: list of provider links */
                                        __('Free tile providers (visit for terms & attribution): %s. Keyless providers need no setup.', 'fw'),
                                        '<a href="https://www.openstreetmap.org/" target="_blank" rel="noopener">OpenStreetMap</a>, '
                                        . '<a href="https://carto.com/basemaps/" target="_blank" rel="noopener">CARTO</a>, '
                                        . '<a href="https://opentopomap.org/" target="_blank" rel="noopener">OpenTopoMap</a>, '
                                        . '<a href="https://www.cyclosm.org/" target="_blank" rel="noopener">CyclOSM</a>, '
                                        . '<a href="https://www.hotosm.org/" target="_blank" rel="noopener">Humanitarian OSM Team</a>, '
                                        . '<a href="https://www.esri.com/" target="_blank" rel="noopener">Esri</a>, '
                                        . '<a href="https://stadiamaps.com/" target="_blank" rel="noopener">Stadia Maps</a>, '
                                        . '<a href="https://www.thunderforest.com/" target="_blank" rel="noopener">Thunderforest</a>, '
                                        . '<a href="https://www.maptiler.com/" target="_blank" rel="noopener">MapTiler</a>'
                                    ),
                                    'choices' => [
                                        'osm'           => __('OpenStreetMap — Standard', 'fw'),
                                        'carto'         => __('CARTO (Light / Dark / Voyager)', 'fw'),
                                        'opentopomap'   => __('OpenTopoMap — Terrain', 'fw'),
                                        'cyclosm'       => __('CyclOSM — Cycling', 'fw'),
                                        'hot'           => __('Humanitarian (HOT)', 'fw'),
                                        'esri'          => __('Esri — World Imagery (Satellite)', 'fw'),
                                        'stadia'        => __('Stadia / Stamen (API key)', 'fw'),
                                        'thunderforest' => __('Thunderforest (API key)', 'fw'),
                                        'maptiler'      => __('MapTiler (API key)', 'fw'),
                                    ],
                                ],
                            ],
                            // Only providers with a variant choice and/or a key field
                            // need a group. Single-style keyless providers (osm,
                            // opentopomap, cyclosm, hot, esri) are omitted → reveal nothing.
                            'choices' => [
                                'carto' => [
                                    'carto_variant' => [
                                        'type'    => 'select',
                                        'label'   => __('CARTO Style', 'fw'),
                                        'value'   => 'carto_light',
                                        'choices' => [
                                            'carto_light'   => __('Positron (Light)', 'fw'),
                                            'carto_dark'    => __('Dark Matter (Dark)', 'fw'),
                                            'carto_voyager' => __('Voyager', 'fw'),
                                        ],
                                    ],
                                ],
                                'stadia' => [
                                    'stadia_variant' => [
                                        'type'    => 'select',
                                        'label'   => __('Stadia / Stamen Style', 'fw'),
                                        'value'   => 'stadia_alidade_smooth',
                                        'choices' => [
                                            'stadia_alidade_smooth'      => __('Alidade Smooth', 'fw'),
                                            'stadia_alidade_smooth_dark' => __('Alidade Smooth Dark', 'fw'),
                                            'stadia_outdoors'            => __('Outdoors', 'fw'),
                                            'stamen_toner'               => __('Stamen Toner', 'fw'),
                                            'stamen_terrain'             => __('Stamen Terrain', 'fw'),
                                            'stamen_watercolor'          => __('Stamen Watercolor', 'fw'),
                                        ],
                                    ],
                                    'stadia_key' => [
                                        'type'            => 'text',
                                        'label'           => __('Stadia Maps API Key', 'fw'),
                                        'desc'            => sprintf(
                                            __('Get a free key at %sStadia Maps%s.', 'fw'),
                                            '<a href="https://client.stadiamaps.com/signup/" target="_blank" rel="noopener">',
                                            '</a>'
                                        ),
                                        'help'            => __('Saved site-wide (enter once). Restrict the key to your domain in the Stadia dashboard.', 'fw'),
                                        'dynamic_content' => false,
                                        'fw-storage'      => [
                                            'type'      => 'wp-option',
                                            'wp_option' => 'unysonplus:stadia-key',
                                        ],
                                    ],
                                ],
                                'thunderforest' => [
                                    'tf_variant' => [
                                        'type'    => 'select',
                                        'label'   => __('Thunderforest Style', 'fw'),
                                        'value'   => 'tf_cycle',
                                        'choices' => [
                                            'tf_cycle'     => __('OpenCycleMap', 'fw'),
                                            'tf_transport' => __('Transport', 'fw'),
                                            'tf_landscape' => __('Landscape', 'fw'),
                                            'tf_outdoors'  => __('Outdoors', 'fw'),
                                        ],
                                    ],
                                    'thunderforest_key' => [
                                        'type'            => 'text',
                                        'label'           => __('Thunderforest API Key', 'fw'),
                                        'desc'            => sprintf(
                                            __('Get a free key at %sThunderforest%s.', 'fw'),
                                            '<a href="https://manage.thunderforest.com/users/sign_up" target="_blank" rel="noopener">',
                                            '</a>'
                                        ),
                                        'help'            => __('Saved site-wide (enter once). The free tier has a monthly request limit.', 'fw'),
                                        'dynamic_content' => false,
                                        'fw-storage'      => [
                                            'type'      => 'wp-option',
                                            'wp_option' => 'unysonplus:thunderforest-key',
                                        ],
                                    ],
                                ],
                                'maptiler' => [
                                    'maptiler_variant' => [
                                        'type'    => 'select',
                                        'label'   => __('MapTiler Style', 'fw'),
                                        'value'   => 'maptiler_streets',
                                        'choices' => [
                                            'maptiler_streets'   => __('Streets', 'fw'),
                                            'maptiler_satellite' => __('Satellite', 'fw'),
                                            'maptiler_outdoor'   => __('Outdoor / Topo', 'fw'),
                                        ],
                                    ],
                                    'maptiler_key' => [
                                        'type'            => 'text',
                                        'label'           => __('MapTiler API Key', 'fw'),
                                        'desc'            => sprintf(
                                            __('Get a free key at %sMapTiler Cloud%s.', 'fw'),
                                            '<a href="https://cloud.maptiler.com/account/keys/" target="_blank" rel="noopener">',
                                            '</a>'
                                        ),
                                        'help'            => __('Saved site-wide (enter once). Restrict the key to your domain in MapTiler Cloud.', 'fw'),
                                        'dynamic_content' => false,
                                        'fw-storage'      => [
                                            'type'      => 'wp-option',
                                            'wp_option' => 'unysonplus:maptiler-key',
                                        ],
                                    ],
                                ],
                            ],
                            'show_borders' => false,
                        ],
                    ],
                    // ---- Google Maps ------------------------------------------------
                    'google' => [
                        'gmap-key' => array_merge(
                            [
                                'label' => __('Google Maps API Key', 'fw'),
                                'desc'  => sprintf(
                                    __('Create an application in %sGoogle Console%s and add the Key here.', 'fw'),
                                    '<a href="https://console.developers.google.com/flows/enableapi?apiid=places_backend,maps_backend,geocoding_backend,directions_backend,distance_matrix_backend,elevation_backend&keyType=CLIENT_SIDE&reusekey=true" target="_blank" rel="noopener">',
                                    '</a>'
                                ),
                                'help'  => __('Saved site-wide, so you only enter it once. Restrict the key to your domain in Google Console to stop others from using your quota.', 'fw'),
                            ],
                            version_compare(fw()->manifest->get_version(), '2.5.7', '>=')
                            ? ['type' => 'gmap-key']
                            : [
                                'type'            => 'text',
                                'dynamic_content' => false,
                                'fw-storage'      => [
                                    'type'      => 'wp-option',
                                    'wp_option' => 'fw-option-types:gmap-key',
                                ],
                            ]
                        ),
                        'map_type' => [
                            'type'    => 'select',
                            'label'   => __('Map Type', 'fw'),
                            'desc'    => __('Select map type', 'fw'),
                            'help'    => __('"Roadmap" is the standard street view; "Satellite" shows aerial imagery, "Hybrid" overlays street labels on it, and "Terrain" emphasizes hills and elevation.', 'fw'),
                            'choices' => [
                                'roadmap'   => __('Roadmap', 'fw'),
                                'terrain'   => __('Terrain', 'fw'),
                                'satellite' => __('Satellite', 'fw'),
                                'hybrid'    => __('Hybrid', 'fw'),
                            ],
                        ],
                    ],
                ],
                'show_borders' => true,
            ],
            'map_height' => [
                'type'  => 'unit-input',
                'label' => __('Map Height', 'fw'),
                'desc'  => __('Set map height (Ex: 300px, 50vh)', 'fw'),
                'help'  => __('Pick a number and a unit. "px" is a fixed pixel height; "vh" is a percentage of the screen height (e.g. 50vh = half the viewport). "%" only works if a parent has its own height. The width always stretches to fill the container.', 'fw'),
                'value' => [ 'value' => '', 'unit' => 'px' ],
                'units' => [ 'px', 'vh', '%', 'rem', 'em' ],
            ],
            'disable_scrolling' => [
                'type'  => 'switch',
                'value' => false,
                'label' => __('Disable zoom on scroll', 'fw'),
                'desc'  => __('Prevent the map from zooming when scrolling until clicking on the map', 'fw'),
                'help'  => __('Turn this on for maps embedded mid-page so visitors can scroll past without the map hijacking their mouse wheel.', 'fw'),
                'left-choice'  => [
                    'value' => false,
                    'label' => __('Yes', 'fw'),
                ],
                'right-choice' => [
                    'value' => true,
                    'label' => __('No', 'fw'),
                ],
            ],
            ], // group_content options
            ], // group_content
        ],
    ],

    'tab_styling' => [
        'title'   => __( 'Styling', 'fw' ),
        'type'    => 'tab',
        'options' => [
            'group_colors' => [
                'type'    => 'group',
                'options' => [
                    'bg_color' => sc_color_field_compact( array( 'label' => __( 'Background Color', 'fw' ), 'kind' => 'bg' ) ),
                ],
            ],
            'group_spacings' => [
                'type'    => 'group',
                'options' => [
                    'spacing'  => array(
                        'type'  => 'spacing',
                        'label' => __( 'Margin & Padding', 'fw' ),
                        'desc'  => __( 'All Sides applies to every side at once; any per-side value (Top, Right, Bottom, Left) overrides it for that direction.', 'fw' ),
                        'help'  => sc_styling_help_text( 'spacing' ),
                    ),
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
