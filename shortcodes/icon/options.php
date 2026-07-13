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
                    'icon' => [
                        'type'  => 'icon-v2',
                        'label' => __('Icon', 'fw'),
                        'help'  => __('Pick the glyph to display. Font icons can be recoloured and resized via the Styling tab, unlike pasted emoji. This is the main content of the shortcode, so an icon should always be chosen.', 'fw'),
                        'preview_size' => 'medium',
                        'modal_size' => 'medium',
                    ],
                    'title' => [
                        'type'  => 'text',
                        'label' => __('Title', 'fw'),
                        'desc'  => __('Icon title', 'fw'),
                        'help'  => __('Optional tooltip text shown on hover; it also serves as an accessible label for screen readers. Leave it empty for purely decorative icons.', 'fw'),
                    ],
                ],
            ],
        ],
    ],

    'tab_styling' => [
        'title'   => __( 'Styling', 'fw' ),
        'type'    => 'tab',
        // Drop the default wrapper-level Text Color — the named Title Color
        // and Icon Color below cover both inner elements, so a wrapper-level
        // text colour would just compete with the per-element picks.
        'options' => [
            'group_colors' => [
                'type'    => 'group',
                'options' => [
                    'bg_color' => sc_color_field_compact( array( 'label' => __( 'Background Color', 'fw' ), 'kind' => 'bg' ) ),
                    'title_color' => sc_color_field_compact( array(
                        'label' => __( 'Title Color', 'fw' ),
                        'desc'  => __( 'Color preset applied to the title text.', 'fw' ),
                    ) ),
                    'icon_color' => sc_color_field_compact( array(
                        'label' => __( 'Icon Color', 'fw' ),
                        'desc'  => __( 'Color preset applied to the icon glyph (font icons only).', 'fw' ),
                    ) ),
                    'icon_size' => array(
                        'type'  => 'unit-input',
                        'label' => __( 'Icon Size', 'fw' ),
                        'desc'  => __( 'The glyph size (Ex: 24px, 2rem). Leave empty for the default.', 'fw' ),
                        'help'  => __( 'Sets the icon\'s size. Scales BOTH font icons and inline-SVG icons (the SVG is normalised to 1em, so this one control resizes either kind). Leave the number empty to keep the theme default.', 'fw' ),
                        'value' => array( 'value' => '', 'unit' => 'px' ),
                        'units' => array( 'px', 'rem', 'em' ),
                    ),
                ],
            ],
            'group_spacings' => [
                'type'    => 'group',
                'options' => [
                    'spacing' => array(
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
