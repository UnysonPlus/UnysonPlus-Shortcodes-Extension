<?php
if ( ! defined( 'FW' ) ) { die( 'Forbidden' ); }

// URI + swatch helper for this shortcode's option thumbnails (drop-cap style image-picker).
$tb_opt_img   = function_exists( 'fw_ext' ) ? fw_ext( 'shortcodes' )->get_declared_URI( '/shortcodes/text-block/static/img/options' ) : '';
$tb_dc_swatch = function ( $file, $title ) use ( $tb_opt_img ) {
	return array( 'small' => array( 'src' => $tb_opt_img . '/' . $file, 'height' => 46, 'title' => $title ) );
};

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
                'help'   => __( 'Supports rich text, links, images, and nested shortcodes. Use the Styling tab for colors and font size rather than inline formatting so the block stays theme-consistent.', 'fw' ),
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
    'tab_styling' => [
        'title'   => __( 'Styling', 'fw' ),
        'type'    => 'tab',
        'options' => [
            'group_colors' => [
                'type'    => 'group',
                'options' => [
                    'text_color'       => sc_color_field_compact( array( 'label' => __( 'Text Color', 'fw' ),       'kind' => 'text' ) ),
                    'bg_color'         => sc_color_field_compact( array( 'label' => __( 'Background Color', 'fw' ), 'kind' => 'bg' ) ),
                    'link_color'       => sc_color_field_compact( array(
                        'label' => __( 'Link Color', 'fw' ),
                        'kind'  => 'text',
                        'desc'  => __( 'Color for links inside this block. Leave unset to keep the theme link color.', 'fw' ),
                    ) ),
                    'font_size_preset' => sc_font_size_field( array(
                        'desc' => __( 'A named size from the framework presets. Customizable in Theme Settings on the official Unyson+ theme.', 'fw' ),
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
            'group_layout' => [
                'type'    => 'group',
                'options' => [
                    'text_align' => function_exists( 'sc_alignment_field' )
                        ? sc_alignment_field( array(
                            'label'   => __( 'Text Alignment', 'fw' ),
                            'inherit' => true,
                            'desc'    => __( 'Horizontal alignment for the text in this block.', 'fw' ),
                            'help'    => __( 'Leave on Inherit to follow the column / theme alignment. Output as a Bootstrap text-* class on the block wrapper — never as inline style on the paragraphs.', 'fw' ),
                        ) )
                        : array(
                            'type'    => 'select',
                            'label'   => __( 'Text Alignment', 'fw' ),
                            'value'   => '',
                            'choices' => array( '' => __( 'Inherit', 'fw' ), 'left' => __( 'Left', 'fw' ), 'center' => __( 'Center', 'fw' ), 'right' => __( 'Right', 'fw' ) ),
                        ),
                    'max_width' => [
                        'type'         => 'multi-picker',
                        'label'        => false,
                        'desc'         => false,
                        'value'        => [ 'preset' => 'full' ],
                        'picker'       => [
                            'preset' => [
                                'label'   => __( 'Max Width', 'fw' ),
                                'desc'    => __( 'Constrain the block width for readability; a constrained block is centered automatically.', 'fw' ),
                                'help'    => __( 'Long lines are hard to read — "Reading" (~65 characters) is the comfortable measure for body copy. The presets emit a class on the wrapper; "Custom" emits an inline max-width so you can dial in any exact value.', 'fw' ),
                                'type'    => 'select',
                                'choices' => [
                                    'full'   => __( 'Full width', 'fw' ),
                                    'narrow' => __( 'Narrow (~45 ch)', 'fw' ),
                                    'read'   => __( 'Reading (~65 ch)', 'fw' ),
                                    'medium' => __( 'Medium (720px)', 'fw' ),
                                    'wide'   => __( 'Wide (960px)', 'fw' ),
                                    'custom' => __( 'Custom…', 'fw' ),
                                ],
                            ],
                        ],
                        'choices'      => [
                            // Revealed only when "Custom…" is picked.
                            'custom' => [
                                'custom_width' => [
                                    'type'  => 'unit-input',
                                    'label' => __( 'Custom Max Width', 'fw' ),
                                    'desc'  => __( 'Output as an inline max-width on the block (and centered).', 'fw' ),
                                    'units' => [ 'px', 'rem', 'em', '%', 'ch', 'vw' ],
                                    'value' => [ 'value' => '680', 'unit' => 'px' ],
                                ],
                            ],
                        ],
                        'show_borders' => false,
                    ],
                    'columns' => [
                        'type'    => 'select',
                        'label'   => __( 'Text Columns', 'fw' ),
                        'desc'    => __( 'Flow the text into newspaper-style columns (collapses to one column on small screens).', 'fw' ),
                        'value'   => '1',
                        'choices' => [ '1' => __( '1 column', 'fw' ), '2' => __( '2 columns', 'fw' ), '3' => __( '3 columns', 'fw' ) ],
                    ],
                    'balance' => [
                        'type'         => 'switch',
                        'label'        => __( 'Balanced Wrapping', 'fw' ),
                        'desc'         => __( 'Even out line lengths (CSS text-wrap: balance) so no lone "orphan" word is left on the last line.', 'fw' ),
                        'value'        => 'no',
                        'right-choice' => [ 'value' => 'yes', 'label' => __( 'On', 'fw' ) ],
                        'left-choice'  => [ 'value' => 'no',  'label' => __( 'Off', 'fw' ) ],
                    ],
                    'line_height' => [
                        'type'    => 'select',
                        'label'   => __( 'Line Height', 'fw' ),
                        'desc'    => __( 'Vertical spacing between lines of text. Output as a class on the block wrapper.', 'fw' ),
                        'value'   => '',
                        'choices' => [
                            ''        => __( 'Inherit', 'fw' ),
                            'tight'   => __( 'Tight', 'fw' ),
                            'snug'    => __( 'Snug', 'fw' ),
                            'normal'  => __( 'Normal', 'fw' ),
                            'relaxed' => __( 'Relaxed', 'fw' ),
                            'loose'   => __( 'Loose', 'fw' ),
                        ],
                    ],
                    'para_spacing' => [
                        'type'    => 'select',
                        'label'   => __( 'Paragraph Spacing', 'fw' ),
                        'desc'    => __( 'Vertical gap between paragraphs in this block.', 'fw' ),
                        'value'   => '',
                        'choices' => [
                            ''   => __( 'Inherit', 'fw' ),
                            'sm' => __( 'Small', 'fw' ),
                            'md' => __( 'Medium', 'fw' ),
                            'lg' => __( 'Large', 'fw' ),
                        ],
                    ],
                    'lead' => [
                        'type'         => 'switch',
                        'label'        => __( 'Lead Paragraph', 'fw' ),
                        'desc'         => __( 'Enlarge the first paragraph as an introductory lead-in.', 'fw' ),
                        'value'        => 'no',
                        'right-choice' => [ 'value' => 'yes', 'label' => __( 'On', 'fw' ) ],
                        'left-choice'  => [ 'value' => 'no',  'label' => __( 'Off', 'fw' ) ],
                    ],
                    'link_underline' => [
                        'type'    => 'select',
                        'label'   => __( 'Link Underline', 'fw' ),
                        'desc'    => __( 'Underline behavior for links inside this block.', 'fw' ),
                        'value'   => '',
                        'choices' => [
                            ''       => __( 'Inherit', 'fw' ),
                            'always' => __( 'Always', 'fw' ),
                            'hover'  => __( 'On hover', 'fw' ),
                            'none'   => __( 'Never', 'fw' ),
                        ],
                    ],
                ],
            ],
            'group_dropcap' => [
                'type'    => 'group',
                'options' => [
                    'dropcap' => [
                        'type'         => 'multi-picker',
                        'label'        => false,
                        'desc'         => false,
                        'show_borders' => false,
                        'picker'       => [
                            'enabled' => [
                                'type'         => 'switch',
                                'label'        => __( 'Drop Cap', 'fw' ),
                                'desc'         => __( 'Enlarge the first letter(s) of the first paragraph for an editorial lead-in.', 'fw' ),
                                'right-choice' => [ 'value' => 'yes', 'label' => __( 'On', 'fw' ) ],
                                'left-choice'  => [ 'value' => 'no',  'label' => __( 'Off', 'fw' ) ],
                            ],
                        ],
                        'value'   => [ 'enabled' => 'no' ],
                        'choices' => [
                            'yes' => [
                                'dropcap_style' => [
                                    'type'    => 'image-picker',
                                    'label'   => __( 'Style', 'fw' ),
                                    'value'   => 'dropped',
                                    'choices' => [
                                        'dropped' => $tb_dc_swatch( 'dropcap-dropped.svg', __( 'Dropped', 'fw' ) ),
                                        'accent'  => $tb_dc_swatch( 'dropcap-accent.svg',  __( 'Accent color', 'fw' ) ),
                                        'boxed'   => $tb_dc_swatch( 'dropcap-boxed.svg',   __( 'Boxed', 'fw' ) ),
                                        'outline' => $tb_dc_swatch( 'dropcap-outline.svg', __( 'Outline', 'fw' ) ),
                                    ],
                                ],
                                'dropcap_font' => [
                                    'type'    => 'select',
                                    'label'   => __( 'Font', 'fw' ),
                                    'desc'    => __( 'Font family for the drop-cap letter only — applied as an inline style on the cap, since it is a single decorative glyph that does not map to a preset class.', 'fw' ),
                                    'value'   => '',
                                    'choices' => [
                                        ''          => __( 'Inherit (block font)', 'fw' ),
                                        'serif'     => __( 'Serif', 'fw' ),
                                        'sans'      => __( 'Sans-serif', 'fw' ),
                                        'mono'      => __( 'Monospace', 'fw' ),
                                        'condensed' => __( 'Condensed', 'fw' ),
                                    ],
                                ],
                                'dropcap_lines' => [
                                    'type'         => 'number',
                                    'label'        => __( 'Lines to Drop', 'fw' ),
                                    'desc'         => __( 'How many text lines tall the drop cap should be. Drives an inline font-size sized so the cap spans that many lines.', 'fw' ),
                                    'value'        => 3,
                                    'min'          => 2,
                                    'max'          => 8,
                                    'step'         => 1,
                                    'numeric_type' => 'integer',
                                ],
                                'dropcap_chars' => [
                                    'type'         => 'number',
                                    'label'        => __( 'Characters', 'fw' ),
                                    'desc'         => __( 'How many leading letters get the drop cap. Use 1 for a classic single initial.', 'fw' ),
                                    'value'        => 1,
                                    'min'          => 1,
                                    'max'          => 10,
                                    'step'         => 1,
                                    'numeric_type' => 'integer',
                                ],
                                'dropcap_gap' => [
                                    'type'    => 'select',
                                    'label'   => __( 'Distance from Text', 'fw' ),
                                    'desc'    => __( 'Gap between the drop cap and the text that wraps around it.', 'fw' ),
                                    'value'   => 'md',
                                    'choices' => [ 'none' => __( 'None', 'fw' ), 'sm' => __( 'Small', 'fw' ), 'md' => __( 'Normal', 'fw' ), 'lg' => __( 'Large', 'fw' ) ],
                                ],
                                'dropcap_color' => sc_color_field_compact( array(
                                    'label' => __( 'Drop Cap Color', 'fw' ),
                                    'kind'  => 'text',
                                    'desc'  => __( 'Color for the Accent / Boxed / Outline drop-cap styles. Leave unset to use the theme accent.', 'fw' ),
                                ) ),
                            ],
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
