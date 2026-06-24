<?php if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}

/* Build the `design` image-picker choices from the single-source-of-truth
   registry, so adding a design there automatically lists it here. SVG
   thumbnails live under static/img/designs/. */
$ts_uri          = fw_ext( 'shortcodes' )->get_declared_URI( '/shortcodes/testimonials' );
$ts_designs      = require dirname( __FILE__ ) . '/views/designs/registry.php';
$ts_design_choices = [];
foreach ( $ts_designs as $ts_key => $ts_def ) {
    $ts_design_choices[ $ts_key ] = [
        'small' => [
            'src' => $ts_uri . '/static/img/designs/' . $ts_def['thumb'],
            'alt' => $ts_def['label'],
        ],
        'label' => $ts_def['label'],
    ];
}

/* ---------------------------------------------------------------------------
 * Reusable Carousel sub-options. Shared by the designs that run on a slider
 * (Classic, Image Split, Thumbnail Nav) — each design's choice picks the
 * subset it actually uses, so the user only ever sees relevant controls.
 * ------------------------------------------------------------------------- */
$ts_opt_autoplay = [
    'label' => __('Autoplay', 'fw'),
    'type'  => 'switch',
    'right-choice' => ['value'=>'yes','label'=>__('Yes','fw')],
    'left-choice'  => ['value'=>'no','label'=>__('No','fw')],
    'value' => 'yes',
    'desc'  => __('Auto cycle slides.', 'fw'),
];
$ts_opt_interval = [
    'label' => __('Autoplay Interval (ms)', 'fw'),
    'type'  => 'text',
    'value' => '5000',
    'desc'  => __('Delay between auto slides, in milliseconds.', 'fw'),
];
$ts_opt_pause_hover = [
    'label' => __('Pause on Hover', 'fw'),
    'type'  => 'switch',
    'right-choice' => ['value'=>'yes','label'=>__('Yes','fw')],
    'left-choice'  => ['value'=>'no','label'=>__('No','fw')],
    'value' => 'yes',
    'desc'  => __('Stop cycling while hovered.', 'fw'),
];
$ts_opt_controls = [
    'label' => __('Show Prev/Next', 'fw'),
    'type'  => 'switch',
    'right-choice' => ['value'=>'yes','label'=>__('Yes','fw')],
    'left-choice'  => ['value'=>'no','label'=>__('No','fw')],
    'value' => 'yes',
    'desc'  => __('Display navigation arrows.', 'fw'),
];
$ts_opt_indicators = [
    'label' => __('Show Indicators', 'fw'),
    'type'  => 'switch',
    'right-choice' => ['value'=>'yes','label'=>__('Yes','fw')],
    'left-choice'  => ['value'=>'no','label'=>__('No','fw')],
    'value' => 'yes',
    'desc'  => __('Display slide markers.', 'fw'),
];
$ts_opt_indicator_style = [
    'label' => __('Indicator Style', 'fw'),
    'type'  => 'select',
    'value' => 'dots',
    'choices' => [
        'none'  => __('None', 'fw'),
        'dots'  => __('Dots', 'fw'),
        'lines' => __('Lines', 'fw'),
    ],
    'desc' => __('Indicator appearance.', 'fw'),
];
$ts_opt_wrap = [
    'label' => __('Wrap Around (Loop)', 'fw'),
    'type'  => 'switch',
    'right-choice' => ['value'=>'yes','label'=>__('Yes','fw')],
    'left-choice'  => ['value'=>'no','label'=>__('No','fw')],
    'value' => 'yes',
    'desc'  => __('Loop from last slide back to the first.', 'fw'),
];

/* Reusable column-count select (1–4) for the grid-like designs. */
$ts_columns_choices = [
    '1' => '1',
    '2' => '2',
    '3' => '3',
    '4' => '4',
];

$options = [
    'tab_content' => [
        'title'   => __('Content', 'fw'),
        'type'    => 'tab',
        'options' => [
            'group' => [
                'type'    => 'group',
                'options' => [
                    'title' => [
                        'label' => __('Title', 'fw'),
                        'desc'  => __('Section heading displayed above the testimonials list.', 'fw'),
                        'help'  => __('Plain heading text shown before the testimonial items.', 'fw'),
                        'type'  => 'text',
                    ],
                    'testimonials' => [
                        'label'         => __('Testimonials', 'fw'),
                        'popup-title'   => __('Add/Edit Testimonial', 'fw'),
                        'desc'          => __('Manage testimonial entries.', 'fw'),
                        'help'          => __('Each item becomes one testimonial card / slide.', 'fw'),
                        'type'          => 'addable-popup',
                        'template'      => '{{=author_name}}',
                        'popup-options' => [
                            'content' => [
                                'label' => __('Quote', 'fw'),
                                'desc'  => __('Main testimonial text. Light inline formatting is allowed.', 'fw'),
                                'help'  => __('Shown inside the blockquote. You may use a safe inline subset — &lt;strong&gt;, &lt;em&gt;, &lt;a&gt;, &lt;br&gt; — for bold, italic, links and line breaks; block-level/styling markup is stripped to protect each design’s typography.', 'fw'),
                                'type'  => 'textarea',
                            ],
                            'author_avatar' => [
                                'label' => __('Image', 'fw'),
                                'desc'  => __('Upload or choose an image.', 'fw'),
                                'help'  => __('Avatar shown using responsive image utilities.', 'fw'),
                                'type'  => 'upload',
                            ],
                            'author_name' => [
                                'label' => __('Name', 'fw'),
                                'desc'  => __('Person’s name.', 'fw'),
                                'help'  => __('Displayed as bold author line beneath the quote.', 'fw'),
                                'type'  => 'text',
                            ],
                            'author_job' => [
                                'label' => __('Position', 'fw'),
                                'desc'  => __('Job title or role.', 'fw'),
                                'help'  => __('Optional metadata line (small, muted).', 'fw'),
                                'type'  => 'text',
                            ],
                            'site_name' => [
                                'label' => __('Website Name', 'fw'),
                                'desc'  => __('Shown as link text if URL set.', 'fw'),
                                'help'  => __('Anchor text for the source / company link.', 'fw'),
                                'type'  => 'text',
                            ],
                            'site_url' => [
                                'label' => __('Website Link', 'fw'),
                                'desc'  => __('External site URL.', 'fw'),
                                'help'  => __('Adds rel="nofollow" and target="_blank".', 'fw'),
                                'type'  => 'text',
                            ],
                            'rating' => [
                                'label' => __('Rating', 'fw'),
                                'desc'  => __('0–5 in 0.5 steps.', 'fw'),
                                'help'  => __('Renders 5 Rating stars.', 'fw'),
                                'type'  => 'slider',
                                'value' => 5,
                                'properties' => [
                                    'min'  => 0,
                                    'max'  => 5,
                                    'step' => .5,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],

    /* ----------------------------------------------------------------------
     * DESIGN — the design image-picker drives a multi-picker that reveals
     * ONLY the chosen design's options (Layout + Carousel folded in here).
     * `design_settings` is a NEW option id (the old scalar `design` att is
     * harmless/ignored), so no migration and no blank-modal on legacy saves.
     * -------------------------------------------------------------------- */
    'tab_design' => [
        'title'   => __('Design', 'fw'),
        'type'    => 'tab',
        'options' => [
            'group' => [
                'type'    => 'group',
                'options' => [
                    'design_settings' => [
                        'type'         => 'multi-picker',
                        'label'        => false,
                        'desc'         => false,
                        'show_borders' => false,
                        'picker'       => [
                            'design' => [
                                'label'   => __('Design', 'fw'),
                                'type'    => 'image-picker',
                                'choices' => $ts_design_choices,
                                'desc'    => __('Pick the testimonial layout/design.', 'fw'),
                                'help'    => __('Each design is a self-contained layout with its own positioning and animation. Only the chosen design’s options appear below. Cross-design appearance (avatars, colors, spacing) lives on the Style tab.', 'fw'),
                            ],
                        ],
                        'value'        => [ 'design' => 'default' ],
                        'choices'      => [

                            /* Classic — Slider / Grid / Single + Carousel controls. */
                            'default' => [
                                'layout_type' => [
                                    'type'         => 'multi-picker',
                                    'label'        => false,
                                    'desc'         => false,
                                    'show_borders' => false,
                                    'picker'       => [
                                        'layout_choice' => [
                                            'label'   => __('Layout Type', 'fw'),
                                            'type'    => 'select',
                                            'choices' => [
                                                'carousel' => __('Carousel', 'fw'),
                                                'grid'     => __('Grid', 'fw'),
                                                'single'   => __('Single', 'fw'),
                                            ],
                                            'desc' => __('Carousel uses the bundled Splide slider; Grid tiles cards; Single shows the first item only.', 'fw'),
                                        ],
                                    ],
                                    'value'   => [ 'layout_choice' => 'carousel' ],
                                    'choices' => [
                                        'grid' => [
                                            'grid_columns' => [
                                                'label'   => __('Grid Columns (Desktop)', 'fw'),
                                                'type'    => 'select',
                                                'value'   => 'row-cols-3',
                                                'choices' => [
                                                    'row-cols-1' => '1',
                                                    'row-cols-2' => '2',
                                                    'row-cols-3' => '3',
                                                    'row-cols-4' => '4',
                                                ],
                                                'desc' => __('Columns per row on desktop.', 'fw'),
                                            ],
                                            'gutter' => [
                                                'label' => __('Gutter Size', 'fw'),
                                                'type'  => 'select',
                                                'choices' => [
                                                    ''    => __('Default', 'fw'),
                                                    'g-0' => 'g-0', 'g-1' => 'g-1', 'g-2' => 'g-2',
                                                    'g-3' => 'g-3', 'g-4' => 'g-4', 'g-5' => 'g-5',
                                                ],
                                                'desc' => __('Spacing between columns.', 'fw'),
                                            ],
                                        ],
                                    ],
                                ],
                                'items_per_slide' => [
                                    'label' => __('Items per Slide (Carousel)', 'fw'),
                                    'type'  => 'select',
                                    'value' => '1',
                                    'choices' => [ '1' => '1', '2' => '2', '3' => '3' ],
                                    'desc'  => __('Cards shown simultaneously per carousel slide.', 'fw'),
                                ],
                                'card_style' => [
                                    'label' => __('Card Style', 'fw'),
                                    'type'  => 'select',
                                    'choices' => [
                                        ''                                  => __('Plain', 'fw'),
                                        'card card-body'                    => __('Card', 'fw'),
                                        'card card-body border'             => __('Card Bordered', 'fw'),
                                        'card card-body shadow'             => __('Card Shadow', 'fw'),
                                        'card card-body bg-light'           => __('Card Light', 'fw'),
                                        'card card-body bg-dark text-light' => __('Card Dark', 'fw'),
                                    ],
                                    'desc' => __('Visual container style for each item.', 'fw'),
                                ],
                                'avatar_position' => [
                                    'label' => __('Avatar Position', 'fw'),
                                    'type'  => 'select',
                                    'value' => 'top',
                                    'choices' => [
                                        'top'   => __('Top (Above Content)', 'fw'),
                                        'left'  => __('Left of Content', 'fw'),
                                        'right' => __('Right of Content', 'fw'),
                                        'none'  => __('Hide Avatar', 'fw'),
                                    ],
                                    'desc' => __('Placement of the avatar relative to the quote.', 'fw'),
                                ],
                                'carousel_autoplay'        => $ts_opt_autoplay,
                                'carousel_interval'        => $ts_opt_interval,
                                'carousel_pause_hover'     => $ts_opt_pause_hover,
                                'carousel_controls'        => $ts_opt_controls,
                                'carousel_indicators'      => $ts_opt_indicators,
                                'carousel_indicator_style' => $ts_opt_indicator_style,
                                'carousel_wrap'            => $ts_opt_wrap,
                            ],

                            /* Marquee Wall — scroll speed + direction. */
                            'marquee' => [
                                'marquee_speed' => [
                                    'label' => __('Scroll Speed', 'fw'),
                                    'type'  => 'select',
                                    'value' => 'normal',
                                    'choices' => [
                                        'slow'   => __('Slow', 'fw'),
                                        'normal' => __('Normal', 'fw'),
                                        'fast'   => __('Fast', 'fw'),
                                    ],
                                    'desc' => __('How fast the row scrolls.', 'fw'),
                                ],
                                'marquee_direction' => [
                                    'label' => __('Direction', 'fw'),
                                    'type'  => 'select',
                                    'value' => 'left',
                                    'choices' => [
                                        'left'  => __('Right → Left', 'fw'),
                                        'right' => __('Left → Right', 'fw'),
                                    ],
                                    'desc' => __('Scroll direction.', 'fw'),
                                ],
                            ],

                            /* Masonry Wall — column count. */
                            'masonry' => [
                                'masonry_columns' => [
                                    'label'   => __('Columns (Desktop)', 'fw'),
                                    'type'    => 'select',
                                    'value'   => '3',
                                    'choices' => $ts_columns_choices,
                                    'desc'    => __('Masonry column count on desktop.', 'fw'),
                                ],
                            ],

                            /* Speech Bubble — column count. */
                            'bubble' => [
                                'bubble_columns' => [
                                    'label'   => __('Columns (Desktop)', 'fw'),
                                    'type'    => 'select',
                                    'value'   => '3',
                                    'choices' => $ts_columns_choices,
                                    'desc'    => __('Bubble grid column count on desktop.', 'fw'),
                                ],
                            ],

                            /* Image Split Slider — carousel controls. */
                            'split' => [
                                'carousel_autoplay'        => $ts_opt_autoplay,
                                'carousel_interval'        => $ts_opt_interval,
                                'carousel_pause_hover'     => $ts_opt_pause_hover,
                                'carousel_controls'        => $ts_opt_controls,
                                'carousel_indicators'      => $ts_opt_indicators,
                                'carousel_indicator_style' => $ts_opt_indicator_style,
                                'carousel_wrap'            => $ts_opt_wrap,
                            ],

                            /* Thumbnail Nav Slider — carousel controls (its own nav, no indicators). */
                            'thumbnav' => [
                                'carousel_autoplay'    => $ts_opt_autoplay,
                                'carousel_interval'    => $ts_opt_interval,
                                'carousel_pause_hover' => $ts_opt_pause_hover,
                                'carousel_controls'    => $ts_opt_controls,
                                'carousel_wrap'        => $ts_opt_wrap,
                            ],

                            /* Spotlight Coverflow — full carousel controls. */
                            'spotlight' => [
                                'carousel_autoplay'        => $ts_opt_autoplay,
                                'carousel_interval'        => $ts_opt_interval,
                                'carousel_pause_hover'     => $ts_opt_pause_hover,
                                'carousel_controls'        => $ts_opt_controls,
                                'carousel_indicators'      => $ts_opt_indicators,
                                'carousel_indicator_style' => $ts_opt_indicator_style,
                                'carousel_wrap'            => $ts_opt_wrap,
                            ],

                            /* Zigzag Alternating — which side the first photo sits on. */
                            'zigzag' => [
                                'zigzag_start' => [
                                    'label'   => __('First Photo Side', 'fw'),
                                    'type'    => 'select',
                                    'value'   => 'left',
                                    'choices' => [
                                        'left'  => __('Left', 'fw'),
                                        'right' => __('Right', 'fw'),
                                    ],
                                    'desc' => __('Which side the first row’s photo starts on (rows then alternate).', 'fw'),
                                ],
                            ],

                            /* Pull-Quote Editorial — crossfade carousel controls. */
                            'pullquote' => [
                                'carousel_autoplay'    => $ts_opt_autoplay,
                                'carousel_interval'    => $ts_opt_interval,
                                'carousel_pause_hover' => $ts_opt_pause_hover,
                                'carousel_controls'    => $ts_opt_controls,
                                'carousel_indicators'  => $ts_opt_indicators,
                                'carousel_wrap'        => $ts_opt_wrap,
                            ],

                            /* Bento Featured Grid & Stacked List have no design-specific
                               options — intentionally omitted so the picker reveals nothing
                               extra for them. */
                        ],
                    ],
                ],
            ],
        ],
    ],

    /* ----------------------------------------------------------------------
     * STYLE — cross-design appearance + colors + spacing (the old "Style" and
     * "Styling" tabs merged; their names were near-identical and confusing).
     * These options apply across designs, so they stay top-level (no path
     * change vs. before).
     * -------------------------------------------------------------------- */
    'tab_style' => [
        'title'   => __('Style', 'fw'),
        'type'    => 'tab',
        'options' => [
            'group_appearance' => [
                'type'    => 'group',
                'options' => [
                    'container_type' => [
                        'label' => __('Container', 'fw'),
                        'type'  => 'select',
                        'value' => 'container',
                        'choices' => [
                            ''                => __('None', 'fw'),
                            'container'       => __('Container', 'fw'),
                            'container-fluid' => __('Fluid', 'fw'),
                        ],
                        'desc' => __('Outer width wrapper.', 'fw'),
                    ],
                    'text_align' => [
                        'label' => __('Text Align', 'fw'),
                        'type'  => 'select',
                        'choices' => [
                            ''            => __('Left', 'fw'),
                            'text-center' => __('Center', 'fw'),
                            'text-end'    => __('Right', 'fw'),
                        ],
                        'desc' => __('Alignment of text content (where the design honours it).', 'fw'),
                    ],
                    'avatar_shape' => [
                        'label' => __('Avatar Shape', 'fw'),
                        'type'  => 'select',
                        'value' => 'rounded-circle',
                        'choices' => [
                            'rounded-circle' => __('Circle', 'fw'),
                            'rounded'        => __('Rounded', 'fw'),
                            'rounded-0'      => __('Square', 'fw'),
                        ],
                        'desc' => __('Avatar corner radius.', 'fw'),
                    ],
                    'avatar_size' => [
                        'label' => __('Avatar Size', 'fw'),
                        'type'  => 'select',
                        'value' => 'avatar-md',
                        'choices' => [
                            'avatar-sm' => __('Small', 'fw'),
                            'avatar-md' => __('Medium', 'fw'),
                            'avatar-lg' => __('Large', 'fw'),
                        ],
                        'desc' => __('Avatar size (mainly affects the Classic design).', 'fw'),
                    ],
                    'show_rating' => [
                        'label' => __('Show Rating Stars', 'fw'),
                        'type'  => 'switch',
                        'right-choice' => ['value'=>'yes','label'=>__('Yes','fw')],
                        'left-choice'  => ['value'=>'no','label'=>__('No','fw')],
                        'value' => 'yes',
                        'desc'  => __('Toggle star display across all designs.', 'fw'),
                    ],
                ],
            ],
            'group_colors' => [
                'type'    => 'group',
                'options' => [
                    'text_color'       => sc_color_field_compact( array( 'label' => __( 'Text Color', 'fw' ),       'kind' => 'text' ) ),
                    'bg_color'         => sc_color_field_compact( array( 'label' => __( 'Background Color', 'fw' ), 'kind' => 'bg' ) ),
                    'font_size_preset' => sc_font_size_field( array(
                        'desc' => __( 'A named size from the framework presets. Customizable in Theme Settings on the official Unyson+ theme.', 'fw' ),
                    ) ),
                    'title_color' => sc_color_field_compact( array(
                        'label' => __( 'Section Title Color', 'fw' ),
                        'desc'  => __( 'Overrides the general Text Color for the section heading above the testimonials.', 'fw' ),
                    ) ),
                    'quote_color' => sc_color_field_compact( array(
                        'label' => __( 'Quote Color', 'fw' ),
                        'desc'  => __( 'Overrides the general Text Color for the quote / blockquote body in every testimonial.', 'fw' ),
                    ) ),
                    'author_name_color' => sc_color_field_compact( array(
                        'label' => __( 'Author Name Color', 'fw' ),
                        'desc'  => __( 'Overrides the general Text Color for the author name line in every testimonial.', 'fw' ),
                    ) ),
                    'author_job_color' => sc_color_field_compact( array(
                        'label' => __( 'Author Job Color', 'fw' ),
                        'desc'  => __( 'Overrides the general Text Color for the author job/role line in every testimonial.', 'fw' ),
                    ) ),
                    'site_link_color' => sc_color_field_compact( array(
                        'label' => __( 'Site Link Color', 'fw' ),
                        'desc'  => __( 'Overrides the general Text Color for the Website Name link (its href comes from Website Link).', 'fw' ),
                    ) ),
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
                'options' => sc_get_advanced_tab(),
            ],
        ],
    ],
];
