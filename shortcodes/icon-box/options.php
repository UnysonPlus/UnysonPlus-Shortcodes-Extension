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
                        'type'         => 'icon',
                        'label'        => __( 'Icon', 'fw' ),
                        'preview_size' => 'medium',
                        'modal_size'   => 'medium',
                        'desc'         => __( 'Pick a font icon, a Lucide SVG, an emoji, or paste/upload your own SVG — all from the one picker.', 'fw' ),
                        'help'         => __( 'Font icons and SVGs recolour via the Icon Color option in the Styling tab (SVGs that use fill="currentColor"); emoji colours are fixed.', 'fw' ),
                    ],

                    // Retired: the picker above now handles emoji + SVG for every
                    // element, so the separate Custom Icon field is gone. Kept as a
                    // hidden option so any value saved before the picker gained those
                    // kinds is preserved and still rendered (see the view — the
                    // picked icon takes precedence when set).
                    'custom_icon' => [
                        'type'  => 'hidden',
                        'label' => false,
                    ],

                    'title' => [
                        'type'  => 'text',
                        'label' => __( 'Title', 'fw' ),
                        'help'  => __( 'The headline shown next to the icon, e.g. "Fast Delivery". Leave empty to render an icon-only box. Use the Title HTML Tag option below to set its heading level.', 'fw' ),
                    ],

                    'title_tag' => [
                        'type'    => 'select',
                        'label'   => __( 'Title HTML Tag', 'fw' ),
                        'desc'    => __( 'Choose the semantic tag used to render the title. Pick the heading level that fits the page outline.', 'fw' ),
                        'help'    => __( 'For SEO and accessibility, keep headings in order (an H3 here should sit under an H2 above it). Choose Span or Paragraph when the title is purely decorative and should not appear in the document outline.', 'fw' ),
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
                        'help'          => __( 'Supports rich text and nested shortcodes. Keep it short for grid layouts where several icon boxes sit side by side, so the boxes stay the same height.', 'fw' ),
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
                    'style' => call_user_func( function () {
                        $img = fw_ext( 'shortcodes' )->get_declared_URI( '/shortcodes/icon-box/static/img/layout' );
                        $swatch = function ( $file, $title ) use ( $img ) {
                            return array( 'small' => array( 'src' => $img . '/' . $file, 'height' => 60, 'title' => $title ) );
                        };
                        return array(
                        'type'    => 'image-picker',
                        'label'   => __( 'Icon Position', 'fw' ),
                        'help'    => __( 'Use "Icon above title" for centred feature grids, and the inline or side options for compact list-style rows. The side options ("Icon left/right of title & content") wrap both the title and the body text beside the icon.', 'fw' ),
                        'value'   => 'top-title',
                        'choices' => [
                            'top-title'             => $swatch( 'top-title.svg',             __( 'Icon above title', 'fw' ) ),
                            'inline-left'           => $swatch( 'inline-left.svg',           __( 'Icon inline, left of title', 'fw' ) ),
                            'inline-right'          => $swatch( 'inline-right.svg',          __( 'Icon inline, right of title', 'fw' ) ),
                            'stack-left'            => $swatch( 'stack-left.svg',            __( 'Icon left of title & content', 'fw' ) ),
                            'stack-right'           => $swatch( 'stack-right.svg',           __( 'Icon right of title & content', 'fw' ) ),
                            'between-title-content' => $swatch( 'between-title-content.svg', __( 'Icon between title and content (divider)', 'fw' ) ),
                        ],
                        );
                    } ),

                    // NOTE: the simple "Icon Badge" shape control was RETIRED from the UI
                    // in favour of Icon Badge Presets (Styling → Icon Badge Preset). Its
                    // saved value ('solid-circle' etc.) is still HONORED by the view for
                    // pages built before the switch — see views/view.php ($allowed_badges).
                    // Do not re-add the picker; new work uses presets.

                    'icon_align' => sc_alignment_field( array(
                        'label'   => __( 'Icon Alignment', 'fw' ),
                        'inherit' => true,
                        'desc'    => __( 'Horizontal alignment of the icon. Only takes effect when Icon Position is "Icon above title" or "Icon between title and content" — the inline / side layouts position the icon via flexbox instead.', 'fw' ),
                        'help'    => __( 'Typically you want this to match the Title and Content alignment below for a tidy column. Leave on Inherit to use the layout default rather than forcing a position.', 'fw' ),
                    ) ),

                    'title_align' => sc_alignment_field( array(
                        'label'   => __( 'Title Alignment', 'fw' ),
                        'inherit' => true,
                        'desc'    => __( 'Horizontal text alignment of the title. Outputs a Bootstrap text utility class.', 'fw' ),
                        'help'    => __( 'Set this independently of the Content alignment when you want, for example, a centred title above left-aligned body text. Leave on Inherit to follow the chosen Icon Position layout.', 'fw' ),
                    ) ),

                    'content_align' => sc_alignment_field( array(
                        'label'   => __( 'Content Alignment', 'fw' ),
                        'inherit' => true,
                        'desc'    => __( 'Horizontal text alignment of the body content. Outputs a Bootstrap text utility class.', 'fw' ),
                        'help'    => __( 'Justified or centred body text can be hard to read in long paragraphs — Left usually reads best. Leave on Inherit to follow the layout alignment.', 'fw' ),
                    ) ),

                    'mobile_stack' => [
                        'type'  => 'switch',
                        'label' => __( 'Stack on Mobile', 'fw' ),
                        'desc'  => __( 'Force the icon to move to the top on small screens regardless of the chosen layout.', 'fw' ),
                        'help'  => __( 'Keep this on (recommended) when using a side layout, so narrow phone screens do not squash the icon and text into a cramped row. Turn it off only if you have deliberately designed the inline layout to hold up on mobile.', 'fw' ),
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
                        'help'  => __( 'Enter a full URL (https://...) or a relative path. When set, avoid placing other links or buttons inside the Content, as nested links inside a clickable box are invalid HTML.', 'fw' ),
                    ],

                    'link_target' => [
                        'type'  => 'switch',
                        'label' => __( 'Open in New Tab', 'fw' ),
                        'help'  => __( 'Opens the Box Link in a new browser tab. Best reserved for links to external sites; in-tab navigation is usually expected for links within your own site.', 'fw' ),
                        'value' => false,
                    ],

                    'link_rel' => [
                        'type'    => 'select',
                        'label'   => __( 'Link Rel Attribute', 'fw' ),
                        'desc'    => __( 'SEO hint for search engines about the relationship of the linked page.', 'fw' ),
                        'help'    => __( 'Use Sponsored for paid or affiliate links, Nofollow for untrusted user-submitted destinations, and None for ordinary editorial links you want search engines to follow.', 'fw' ),
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

    'tab_styling' => [
        'title'   => __( 'Styling', 'fw' ),
        'type'    => 'tab',
        // Drop the wrapper-level Text Color — Title / Content / Icon colors
        // below cover every visible text element. Background Color stays.
        'options' => [
            'group_colors' => [
                'type'    => 'group',
                'options' => [
                    'box_style'        => sc_card_box_style_field(),
                    'bg_color'         => sc_color_field_compact( array( 'label' => __( 'Background Color', 'fw' ), 'kind' => 'bg' ) ),
                    'font_size_preset' => sc_font_size_field( array(
                        'desc' => __( 'A named size from the framework presets. Customizable in Theme Settings on the official Unyson+ theme.', 'fw' ),
                    ) ),
                    'title_color' => sc_color_field_compact( array(
                        'label' => __( 'Title Color', 'fw' ),
                        'desc'  => __( 'Color applied to the title. Pick a preset for a theme-managed class, or use the custom picker for a one-off hex.', 'fw' ),
                    ) ),
                    'content_color' => sc_color_field_compact( array(
                        'label' => __( 'Content Color', 'fw' ),
                        'desc'  => __( 'Color applied to the body content.', 'fw' ),
                    ) ),
                    'icon_color' => sc_color_field_compact( array(
                        'label' => __( 'Icon Color', 'fw' ),
                        'desc'  => __( 'Color applied to the icon (font icons only).', 'fw' ),
                    ) ),
                    'icon_size' => array(
                        'type'  => 'unit-input',
                        'label' => __( 'Icon Size', 'fw' ),
                        'desc'  => __( 'The icon size (Ex: 32px, 2.5rem). Leave empty for the default (2rem). Scales both font icons and inline-SVG icons.', 'fw' ),
                        'value' => array( 'value' => '', 'unit' => 'px' ),
                        'units' => array( 'px', 'rem', 'em' ),
                    ),
                    'icon_badge_preset' => sc_icon_badge_preset_field( array(
                        'desc' => __( 'Apply a reusable Icon Badge — a shaped tile (fill, border, corners, shadow) with its own icon colour + size and hover effects. Manage presets in Theme Settings → Components → Icon Badges.', 'fw' ),
                    ) ),
                    // NOTE: "Icon Badge Color" was RETIRED from the UI alongside the simple
                    // Icon Badge shape (both superseded by presets). The view still reads a
                    // saved icon_badge_color for legacy pages; new work uses presets.
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
