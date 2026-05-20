<?php if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}

$options = [

    'tab_content' => [
        'title'   => __( 'Content', 'fw' ),
        'type'    => 'tab',
        'options' => [
            'group_content' => [
                'type'    => 'group',
                'options' => [
                    'icon' => [
                        'type'         => 'icon-v2',
                        'label'        => __( 'Icon', 'fw' ),
                        'preview_size' => 'medium',
                        'modal_size'   => 'medium',
                        'desc'         => __( 'Pick an icon from the library. Will be ignored if "Custom Icon" below is filled.', 'fw' ),
                    ],

                    'custom_icon' => [
                        'type'  => 'text',
                        'label' => __( 'Custom Icon (Emoji / SVG)', 'fw' ),
                        'desc'  => __( 'Optional. If filled, this overrides the Icon picker above. Accepts an emoji (e.g. ⭐) or inline SVG markup.', 'fw' ),
                    ],

                    'title' => [
                        'type'  => 'text',
                        'label' => __( 'Title', 'fw' ),
                    ],

                    'title_tag' => [
                        'type'    => 'select',
                        'label'   => __( 'Title HTML Tag', 'fw' ),
                        'desc'    => __( 'Choose the semantic tag used to render the title. Pick the heading level that fits the page outline.', 'fw' ),
                        'value'   => 'h3',
                        'choices' => [
                            'h3'   => __( 'H3', 'fw' ),
                            'h4'   => __( 'H4', 'fw' ),
                            'h5'   => __( 'H5', 'fw' ),
                            'h6'   => __( 'H6', 'fw' ),
                            'span' => __( 'Span (decorative, not a heading)', 'fw' ),
                            'p'    => __( 'Paragraph', 'fw' ),
                        ],
                    ],

                    'content' => [
                        'type'          => 'wp-editor',
                        'label'         => __( 'Content', 'fw' ),
                        'desc'          => __( 'Optional body text shown alongside the icon and title.', 'fw' ),
                        'size'          => 'large',
                        'reinit'        => true,
                        'tinymce'       => true,
                        'editor_height' => 225,
                        'shortcodes'    => true,
                        'wpautop'       => true,
                        'value'         => '',
                    ],
                ],
            ],
        ],
    ],

    'tab_layout' => [
        'title'   => __( 'Layout', 'fw' ),
        'type'    => 'tab',
        'options' => [
            'group_layout' => [
                'type'    => 'group',
                'options' => [
                    'style' => [
                        'type'    => 'select',
                        'label'   => __( 'Icon Position', 'fw' ),
                        'value'   => 'top-title',
                        'choices' => [
                            'top-title'             => __( 'Icon above title', 'fw' ),
                            'inline-left'           => __( 'Icon inline, left of title', 'fw' ),
                            'inline-right'          => __( 'Icon inline, right of title', 'fw' ),
                            'stack-left'            => __( 'Icon left of title & content', 'fw' ),
                            'stack-right'           => __( 'Icon right of title & content', 'fw' ),
                            'between-title-content' => __( 'Icon between title and content (divider)', 'fw' ),
                        ],
                    ],

                    'mobile_stack' => [
                        'type'  => 'switch',
                        'label' => __( 'Stack on Mobile', 'fw' ),
                        'desc'  => __( 'Force the icon to move to the top on small screens regardless of the chosen layout.', 'fw' ),
                        'value' => true,
                    ],
                ],
            ],
        ],
    ],

    'tab_link' => [
        'title'   => __( 'Link & SEO', 'fw' ),
        'type'    => 'tab',
        'options' => [
            'group_link' => [
                'type'    => 'group',
                'options' => [
                    'box_link' => [
                        'type'  => 'text',
                        'label' => __( 'Box Link URL', 'fw' ),
                        'desc'  => __( 'Optional. If provided, the entire icon box becomes clickable.', 'fw' ),
                    ],

                    'link_target' => [
                        'type'  => 'switch',
                        'label' => __( 'Open in New Tab', 'fw' ),
                        'value' => false,
                    ],

                    'link_rel' => [
                        'type'    => 'select',
                        'label'   => __( 'Link Rel Attribute', 'fw' ),
                        'desc'    => __( 'SEO hint for search engines about the relationship of the linked page.', 'fw' ),
                        'value'   => 'sponsored',
                        'choices' => [
                            'none'      => __( 'None', 'fw' ),
                            'nofollow'  => __( 'Nofollow', 'fw' ),
                            'sponsored' => __( 'Sponsored', 'fw' ),
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
        'title'   => __( 'Advanced', 'fw' ),
        'type'    => 'tab',
        'options' => [
            'advanced_settings' => [
                'type'    => 'group',
                'options' => sc_get_advanced_tab(),
            ],
        ],
    ],
];
