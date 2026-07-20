<?php if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}

// The standard Advanced tab (Unique ID, CSS ID / CSS Class / Custom CSS, Responsive, Custom
// Attributes). Inject the per-part class fields INTO the CSS group, right below CSS Class, so
// all the class targets read as one group instead of being stranded at the bottom of the tab.
$sc_sh_advanced = sc_get_advanced_tab();
$sc_sh_part_classes = [
    'overline_class' => [
        'label' => __('Overline Class', 'fw'),
        'desc'  => false,
        'type'  => 'text',
        'dynamic_content' => false,
        'help'  => __('Add your own CSS class(es), space-separated, to target the overline from custom CSS.', 'fw'),
    ],
    'title_class' => [
        'label' => __('Title Class', 'fw'),
        'desc'  => false,
        'type'  => 'text',
        'dynamic_content' => false,
        'help'  => __('Add your own CSS class(es), space-separated, to target the title from custom CSS.', 'fw'),
    ],
    'subtitle_class' => [
        'label' => __('Subtitle Class', 'fw'),
        'desc'  => false,
        'type'  => 'text',
        'dynamic_content' => false,
        'help'  => __('Add your own CSS class(es), space-separated, to target the subtitle from custom CSS.', 'fw'),
    ],
];
if ( isset( $sc_sh_advanced['group_css']['options'] ) && is_array( $sc_sh_advanced['group_css']['options'] ) ) {
    $sc_sh_css_opts = [];
    foreach ( $sc_sh_advanced['group_css']['options'] as $sc_sh_k => $sc_sh_v ) {
        // No dynamic-content picker on Special Heading's CSS ID / CSS Class either (special-heading
        // override only — the shared sc_get_advanced_tab() is untouched for other shortcodes).
        if ( in_array( $sc_sh_k, array( 'css_id', 'css_class' ), true ) && is_array( $sc_sh_v ) ) {
            $sc_sh_v['dynamic_content'] = false;
        }
        $sc_sh_css_opts[ $sc_sh_k ] = $sc_sh_v;
        if ( $sc_sh_k === 'css_class' ) { // drop the part-class fields right under CSS Class
            $sc_sh_css_opts = array_merge( $sc_sh_css_opts, $sc_sh_part_classes );
        }
    }
    $sc_sh_advanced['group_css']['options'] = $sc_sh_css_opts;
}

$options = [
    'tab_content' => [
        'title'   => __('Content', 'fw'),
        'type'    => 'tab',
        'options' => [
            'group_content' => [
                'type'    => 'group',
                'options' => [
                    'overline' => [
                        'type'  => 'text',
                        'label' => __('Overline', 'fw'),
                        'desc'  => __('Small label shown above the title. Also known as eyebrow headline. Leave empty to hide.', 'fw'),
                        'help'  => __('Examples: "FAQs", "Our process", "Step 1". Give it a rule or uppercase kicker look with Overline Style on the Layout tab.', 'fw'),
                    ],
                    'title' => [
                        'type'  => 'text',
                        'label' => __('Title', 'fw'),
                        'desc'  => __('Write the heading title content', 'fw'),
                        'help'  => __('You can wrap part of the text in inline HTML — e.g. an &lt;em&gt; or a coloured &lt;span&gt; — to emphasise a word.', 'fw'),
                    ],
                    'icon' => [
                        'type'  => 'icon-v2',
                        'label' => __('Title Icon', 'fw'),
                        'desc'  => __('Icon shown before the title. Pick an icon font, emoji, SVG, or upload an image. Leave empty for no icon.', 'fw'),
                        'help'  => __('Kept as a separate icon field (not markup in the Title) so the heading text stays clean and semantic.', 'fw'),
                    ],
                    'subtitle' => [
                        'type'  => 'text',
                        'label' => __('Subtitle', 'fw'),
                        'desc'  => __('Write the heading subtitle content', 'fw'),
                        'help'  => __('A short supporting line under the title — keep it to a sentence or two. For longer copy use a Text Block instead.', 'fw'),
                    ],
                    'heading' => [
                        'type'    => 'select',
                        'label'   => __('Title Tag', 'fw'),
                        'desc'    => __('Select the Title\'s heading tag', 'fw'),
                        'help'    => __('This sets the SEO / semantic level only, not the visual size. Use one H1 per page; H2 suits most section titles. To resize without changing the tag, use Title Display Size on the Styling tab.', 'fw'),
                        'choices' => [
                            'h1' => 'H1',
                            'h2' => 'H2',
                            'h3' => 'H3',
                            'h4' => 'H4',
                            'h5' => 'H5',
                            'h6' => 'H6',
                        ],
                        'value' => 'h2'
                    ],
                ],
            ],
        ],
    ],

    'tab_layout' => [
        'title'   => __( 'Layout', 'fw' ),
        'type'    => 'tab',
        'options' => [
            'group_alignment' => [
                'type'    => 'group',
                'options' => [
                    'alignment' => sc_alignment_field( array(
                        'label'   => __( 'Alignment', 'fw' ),
                        'inherit' => true,
                        'desc'    => __( 'Master horizontal alignment for the whole heading. Each element below can override it.', 'fw' ),
                        'help'    => __( 'Leave on Inherit to follow the theme / parent alignment (nothing is forced). Pick Left, Center or Right to set the overline, title and subtitle together. Use the per-element controls below only when one line should differ.', 'fw' ),
                    ) ),
                    'overline_align' => sc_alignment_field( array(
                        'label'   => __( 'Overline Alignment', 'fw' ),
                        'inherit' => true,
                        'desc'    => __( 'Inherit follows the master Alignment above.', 'fw' ),
                        'help'    => __( 'The dashed "Inherit" swatch keeps this line in step with the master Alignment; pick Left, Center or Right to override just the overline.', 'fw' ),
                    ) ),
                    'title_align' => sc_alignment_field( array(
                        'label'   => __( 'Title Alignment', 'fw' ),
                        'inherit' => true,
                        'desc'    => __( 'Inherit follows the master Alignment above.', 'fw' ),
                        'help'    => __( 'The dashed "Inherit" swatch keeps this line in step with the master Alignment; pick Left, Center or Right to override just the title.', 'fw' ),
                    ) ),
                    'subtitle_align' => sc_alignment_field( array(
                        'label'   => __( 'Subtitle Alignment', 'fw' ),
                        'inherit' => true,
                        'desc'    => __( 'Inherit follows the master Alignment above.', 'fw' ),
                        'help'    => __( 'The dashed "Inherit" swatch keeps this line in step with the master Alignment; pick Left, Center or Right to override just the subtitle.', 'fw' ),
                    ) ),
                ],
            ],
            'group_layout' => [
                'type'    => 'group',
                'options' => [
                    'overline_uppercase' => [
                        'type'         => 'switch',
                        'label'        => __( 'Overline Uppercase', 'fw' ),
                        'desc'         => __( 'Render the overline label as a small, letter-spaced uppercase "kicker".', 'fw' ),
                        'help'         => __( 'An independent toggle — combine it with any Marker or Container below, or use it on its own. Turn off for normal sentence case.', 'fw' ),
                        'left-choice'  => [ 'value' => 'no',  'label' => __( 'No', 'fw' ) ],
                        'right-choice' => [ 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ],
                        'value'        => 'no',
                    ],
                    'overline_marker' => [
                        'type'    => 'select',
                        'label'   => __( 'Overline Marker', 'fw' ),
                        'desc'    => __( 'A small decorative mark shown with the overline label.', 'fw' ),
                        'help'    => __( 'Line / Dot / Bar sit beside the label (see Marker Position); "Lines, both sides" flanks the text (best centred). The mark uses the Overline Color. Choose None for plain text.', 'fw' ),
                        'choices' => [
                            ''      => __( 'None', 'fw' ),
                            'rule'  => __( 'Line (—)', 'fw' ),
                            'dot'   => __( 'Dot (●)', 'fw' ),
                            'lines' => __( 'Lines, both sides (—— ——)', 'fw' ),
                            'bar'   => __( 'Vertical bar (│)', 'fw' ),
                        ],
                        'value' => '',
                    ],
                    'overline_marker_position' => [
                        'type'    => 'select',
                        'label'   => __( 'Marker Position', 'fw' ),
                        'desc'    => __( 'Whether the marker sits before or after the label.', 'fw' ),
                        'help'    => __( 'Leading is the usual editorial look ("— FAQs"); Trailing puts the mark after the text. Ignored when Marker is None or "Lines, both sides".', 'fw' ),
                        'choices' => [
                            'before' => __( 'Leading (before text)', 'fw' ),
                            'after'  => __( 'Trailing (after text)', 'fw' ),
                        ],
                        'value' => 'before',
                    ],
                    'overline_container' => [
                        'type'    => 'select',
                        'label'   => __( 'Overline Container', 'fw' ),
                        'desc'    => __( 'An optional shape around the label.', 'fw' ),
                        'help'    => __( 'Pill = a tinted rounded badge; Outline pill = a bordered, transparent badge; Underline = a short line under the text. The pill tint / border follows the Overline Color. Combine freely with the Marker — e.g. a dot inside a pill, or a pill with no marker.', 'fw' ),
                        'choices' => [
                            ''             => __( 'None', 'fw' ),
                            'pill'         => __( 'Pill (filled)', 'fw' ),
                            'pill-outline' => __( 'Pill (outline)', 'fw' ),
                            'underline'    => __( 'Underline', 'fw' ),
                        ],
                        'value' => '',
                    ],
                    'element_spacing' => [
                        'type'    => 'select',
                        'label'   => __( 'Element Spacing', 'fw' ),
                        'desc'    => __( 'Vertical spacing between the overline, title and subtitle.', 'fw' ),
                        'help'    => __( 'Normal keeps the theme\'s default rhythm. Tight pulls the three lines closer together; Relaxed adds more breathing room between them.', 'fw' ),
                        'choices' => [
                            ''        => __( 'Normal', 'fw' ),
                            'tight'   => __( 'Tight', 'fw' ),
                            'relaxed' => __( 'Relaxed', 'fw' ),
                        ],
                        'value' => '',
                    ],
                    'block_max_width' => [
                        'type'  => 'unit-input',
                        'label' => __( 'Heading Max Width', 'fw' ),
                        'desc'  => __( 'Constrain the whole heading block to a readable measure, e.g. 720px or 50ch. Centered automatically when Alignment is Center. Leave empty for full width.', 'fw' ),
                        'help'  => __( 'Caps the heading width on large screens. ch ties it to the text (50ch ≈ 50 characters); % / vw are relative to the column / viewport; px / rem / em are fixed. Pairs well with Center alignment.', 'fw' ),
                        'units' => [ 'px', '%', 'rem', 'em', 'ch', 'vw' ],
                        'value' => [ 'value' => '', 'unit' => 'px' ],
                        'min'   => 0,
                    ],
                ],
            ],
        ],
    ],

    'tab_styling' => [
        'title'   => __( 'Styling', 'fw' ),
        'type'    => 'tab',
        // No wrapper-level Text Color — Title Color + Subtitle Color below
        // cover both visible text elements. font_size_preset is skipped too:
        // the title's size comes from the Title Tag (h1–h6) on the Content tab.
        'options' => [
            'group_typography' => [
                'type'    => 'group',
                'options' => [
                    'display_size' => [
                        'type'    => 'select',
                        'label'   => __( 'Title Display Size', 'fw' ),
                        'desc'    => __( 'Visually enlarge the title independently of its heading tag (keeps the tag for SEO). Default uses the tag\'s own size.', 'fw' ),
                        'help'    => __( 'Applies a larger "Display" size while KEEPING the heading\'s own weight (a Display size changes size only, not weight) and its SEO tag (set in Title Tag) — ideal for hero titles. These map to the Display Text Styles under Theme Settings → Components → Text Styles.', 'fw' ),
                        'choices' => [
                            ''          => __( 'Default (from tag)', 'fw' ),
                            'display-1' => __( 'Display 1 (largest)', 'fw' ),
                            'display-2' => __( 'Display 2', 'fw' ),
                            'display-3' => __( 'Display 3', 'fw' ),
                            'display-4' => __( 'Display 4', 'fw' ),
                            'display-5' => __( 'Display 5', 'fw' ),
                            'display-6' => __( 'Display 6', 'fw' ),
                        ],
                        'value' => '',
                    ],
                    'title_max_width' => [
                        'type'  => 'unit-input',
                        'label' => __( 'Title Max Width', 'fw' ),
                        'desc'  => __( 'Constrain the title line length independently of the block, e.g. 16ch or 640px — useful to force a clean 2-line headline. Centered automatically when the title is Center-aligned. Leave empty for full width.', 'fw' ),
                        'help'  => __( 'Caps the title only (the overline/subtitle are unaffected — use Heading Max Width to cap the whole block). ch ties it to the text (16ch ≈ 16 characters); % / vw are relative to the column / viewport; px / rem / em are fixed.', 'fw' ),
                        'units' => [ 'px', '%', 'rem', 'em', 'ch', 'vw' ],
                        'value' => [ 'value' => '', 'unit' => 'px' ],
                        'min'   => 0,
                    ],
                    'subtitle_size' => sc_font_size_field( array(
                        'label' => __( 'Subtitle Font Size', 'fw' ),
                        'desc'  => __( 'Named size preset applied to the subtitle only.', 'fw' ),
                    ) ),
                    'subtitle_max_width' => [
                        'type'  => 'unit-input',
                        'label' => __( 'Subtitle Max Width', 'fw' ),
                        'desc'  => __( 'Constrain the subtitle line length for readability, e.g. 60ch or 600px. Leave empty for full width.', 'fw' ),
                        'help'  => __( 'A narrower subtitle is easier to read. 60ch ≈ 60 characters per line — the classic comfortable reading measure.', 'fw' ),
                        'units' => [ 'px', 'rem', 'em', 'ch', '%', 'vw' ],
                        'value' => [ 'value' => '', 'unit' => 'rem' ],
                        'min'   => 0,
                    ],
                ],
            ],
            'group_colors' => [
                'type'    => 'group',
                'options' => [
                    'bg_color' => sc_color_field_compact( array( 'label' => __( 'Background Color', 'fw' ), 'kind' => 'bg' ) ),
                    'overline_color' => sc_color_field_compact( array(
                        'label' => __( 'Overline Color', 'fw' ),
                        'kind'  => 'text',
                        'desc'  => __( 'Color applied to the overline label only.', 'fw' ),
                    ) ),
                    'title_color' => sc_color_field_compact( array(
                        'label' => __( 'Title Color', 'fw' ),
                        'kind'  => 'text',
                        'desc'  => __( 'Color applied to the title only.', 'fw' ),
                    ) ),
                    'subtitle_color' => sc_color_field_compact( array(
                        'label' => __( 'Subtitle Color', 'fw' ),
                        'kind'  => 'text',
                        'desc'  => __( 'Color applied to the subtitle only.', 'fw' ),
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
                'options' => $sc_sh_advanced, // CSS group now carries Overline/Title/Subtitle Class
            ],
        ],
    ],
];
