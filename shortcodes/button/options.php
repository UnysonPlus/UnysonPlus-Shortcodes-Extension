<?php if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}

// Button Style defaults to the FIRST preset (Primary in the default order) and has
// no "None" row — a styleless button is rarely intended (use the Link preset for
// text-only). Computed once so the default tracks whatever preset is listed first.
$sc_button_style_choices = function_exists( 'sc_get_button_style_choices' ) ? sc_get_button_style_choices() : array();
$sc_button_style_default = '';
if ( is_array( $sc_button_style_choices ) && $sc_button_style_choices ) {
    reset( $sc_button_style_choices );
    $sc_button_style_default = (string) key( $sc_button_style_choices );
}

$options = [
    'tab_content' => [
        'title'   => __('Content', 'fw'),
        'type'    => 'tab',
        'options' => [
            'group_content' => [
                'type'    => 'group',
                'options' => [
                    'label'  => [
                        'label' => __('Button Label', 'fw'),
                        'desc'  => __('This is the text that appears on your button', 'fw'),
                        'help'  => __('Keep it short and action-oriented, e.g. "Get Started" or "Download". Leave it empty to render an icon-only button (set a Button Icon below).', 'fw'),
                        'type'  => 'text',
                        'value' => 'Submit'
                    ],
                    'link'   => [
                        'label' => __('Button Link', 'fw'),
                        'desc'  => __('Where should your button link to', 'fw'),
                        'help'  => __('Accepts a full URL (https://...), a relative path, an anchor such as #section-id, or mailto:/tel: links. The default "#" links nowhere — replace it with your real destination.', 'fw'),
                        'type'  => 'text',
                        'value' => '#'
                    ],
                    'target' => [
                        'type'  => 'switch',
                        'label'   => __('Open Link in New Window', 'fw'),
                        'desc'    => __('Select here if you want to open the linked page in a new window', 'fw'),
                        'help'    => __('Recommended for links to external websites so visitors do not lose your page. Leave it off for links within your own site, where staying in the same tab is expected.', 'fw'),
                        'right-choice' => [
                            'value' => '_blank',
                            'label' => __('Yes', 'fw'),
                        ],
                        'left-choice' => [
                            'value' => '_self',
                            'label' => __('No', 'fw'),
                        ],
                    ],
                    'icon' => [
                        'label' => __('Button Icon', 'fw'),
                        // 'desc'  => __('Optional icon class (e.g. bi bi-star)', 'fw'),
                        'help'  => __('Optional icon shown alongside the label, e.g. an arrow for "Next" or a cart for "Buy". Use Icon Position below to place it before or after the text.', 'fw'),
                        'type'  => 'icon-v2',
                        'preview_size' => 'medium',
                        'modal_size' => 'medium',
                    ],
                    'icon_position' => [
                        'label'   => __('Icon Position', 'fw'),
                        'help'    => __('Whether the Button Icon sits to the left (Before) or right (After) of the label. Arrows usually read best After; leading symbols like a cart or download glyph read best Before. Ignored when no icon is set.', 'fw'),
                        'type'    => 'select',
                        'choices' => [
                            'before' => __('Before Label', 'fw'),
                            'after'  => __('After Label', 'fw'),
                        ],
                        'value' => 'after'
                    ],
                ],
            ],
        ],
    ],

    'tab_styling' => [
        'title'   => __('Styling', 'fw'),
        'type'    => 'tab',
        'options' => [
            'group_options' => [
                'type'    => 'group',
                'options' => [
                    'style' => [
                        'label'        => __('Button Style', 'fw'),
                        'desc'         => __('Sourced from Theme Settings → General → Buttons. Includes the outline presets. Each option previews the real button.', 'fw'),
                        'type'         => 'button-style-picker',
                        'choices'      => $sc_button_style_choices,
                        'value'        => $sc_button_style_default,
                        'allow_none'   => false,
                        'preview_text' => __( 'Button', 'fw' ),
                        'help'         => sc_styling_help_text( 'button_style' ),
                    ],
                    'size' => [
                        'label'        => __('Button Size', 'fw'),
                        'desc'         => __('Sourced from Theme Settings → General → Buttons → Sizes. Each option previews the button at that size.', 'fw'),
                        'type'         => 'button-style-picker',
                        'choices'      => sc_get_button_size_choices(),
                        'preview_text' => __( 'Button', 'fw' ),
                        // Size classes carry no color, so ride them on a primary button
                        // (otherwise the preview is an unstyled, background-less .btn).
                        'preview_base' => 'btn btn-primary',
                        'help'         => sc_styling_help_text( 'button_size' ),
                    ],
                    // Shape overrides ONLY the border-radius the Size preset would apply
                    // (Default = keep the Size's radius). Decouples "pill" from "large".
                    // Image-picker: each tile shows the corner silhouette (Style + Size
                    // above are visual too), so the shape is picked by eye.
                    'shape' => call_user_func( function () {
                        $img = fw_ext( 'shortcodes' )->get_declared_URI( '/shortcodes/button/static/img/shapes' );
                        $tile = function ( $file, $title ) use ( $img ) {
                            return array( 'small' => array( 'src' => $img . '/' . $file, 'height' => 34, 'title' => $title ) );
                        };
                        return array(
                            'label'   => __( 'Button Shape', 'fw' ),
                            'desc'    => __( 'Corner rounding. Default keeps the radius from the selected Size; Pill / Rounded / Square override it.', 'fw' ),
                            'help'    => __( 'Shape overrides the border-radius that the Button Size preset would otherwise apply — handy for a pill CTA at any size.', 'fw' ),
                            'type'    => 'image-picker',
                            'value'   => 'default',
                            'choices' => array(
                                'default' => $tile( 'default.svg', __( 'Default (from Size)', 'fw' ) ),
                                'pill'    => $tile( 'pill.svg',    __( 'Pill', 'fw' ) ),
                                'rounded' => $tile( 'rounded.svg', __( 'Rounded', 'fw' ) ),
                                'square'  => $tile( 'square.svg',  __( 'Square', 'fw' ) ),
                            ),
                        );
                    } ),
                    // Multi-picker: the Custom Width field only appears when "Custom"
                    // is selected. Saved shape: width => [ 'mode' => '', 'custom' => [ 'custom_width' => {value,unit} ] ].
                    'width' => [
                        'type'   => 'multi-picker',
                        'label'        => false,
		                'desc'         => false,
                        'picker' => [
                            'mode' => [
                                'label'  => __('Button Width', 'fw'),
                                'desc'   => __('Auto fits the label; Full Width spans its container; Custom reveals a width field.', 'fw'),
                                'help'   => __('Full Width is handy for stacked mobile buttons or call-to-action panels. Choosing Custom unlocks the Custom Width field below for an exact size.', 'fw'),
                                'type'    => 'select',
                                'choices' => [
                                    ''       => __('Auto (fit content)', 'fw'),
                                    'w-100'  => __('Full Width', 'fw'),
                                    'custom' => __('Custom', 'fw'),
                                ],
                                'value'   => '',
                            ],
                        ],
                        'choices' => [
                            'custom' => [
                                'custom_width' => [
                                    'label' => __('Custom Width', 'fw'),
                                    'help'  => __('Enter a number and unit, e.g. 200px or 50%. Percentage widths are relative to the button\'s container. Only used when Button Width is set to Custom.', 'fw'),
                                    'type'  => 'unit-input',
                                    'units' => array( 'px', '%', 'rem', 'em', 'vw' ),
                                    'min'   => 0,
                                ],
                            ],
                        ],
                        'show_borders' => false,
                    ],
                    'alignment' => [
                        'label'   => __('Alignment', 'fw'),
                        'desc'    => __('Aligns the button within its container. Has no effect when Button Width is Full Width.', 'fw'),
                        'help'    => __('Use this to centre a button under a paragraph or push it to one edge of its column. Leave on Default to inherit the surrounding text alignment.', 'fw'),
                        'type'    => 'select',
                        'choices' => [
                            ''       => __('Default (inherit)', 'fw'),
                            'left'   => __('Left', 'fw'),
                            'center' => __('Center', 'fw'),
                            'right'  => __('Right', 'fw'),
                        ],
                        'value'   => '',
                    ],
                    'state' => [
                        'label'   => __('Button State', 'fw'),
                        'help'    => __('Active renders the button in its pressed-down style; Disabled greys it out and blocks clicks. Use Disabled for buttons that are intentionally not yet available — it is a visual state only, not a security measure.', 'fw'),
                        'type'    => 'select',
                        'choices' => [
                            ''         => __('Normal', 'fw'),
                            'active'   => __('Active', 'fw'),
                            'disabled' => __('Disabled', 'fw'),
                        ]
                    ],
                    'hover_animation' => [
                        'label'   => __('Hover Animation', 'fw'),
                        'desc'    => __('Motion applied on hover/focus. Works with any button style or gradient — it animates transform/shadow only, so your preset keeps its colors. Open the dropdown and hover a button to preview.', 'fw'),
                        'help'    => __('A subtle effect (e.g. a slight lift or grow) draws the eye to a key call-to-action. Keep it restrained on pages with many buttons so the motion does not become distracting.', 'fw'),
                        'type'    => 'button-hover-animation',
                        'choices' => sc_get_hover_animation_choices(),
                        // Load the .btnfx-* effect classes in the options form so previews animate.
                        'fx_css'  => fw_min_uri(fw_ext('shortcodes')->get_declared_URI('/shortcodes/button/static/css/hover-fx.css')),
                    ],
                ],
            ],
            'group_spacings' => [
                'type'    => 'group',
                'options' => [
                    'spacing' => array(
                        'type'  => 'spacing',
                        'mode'  => 'margin',
                        'label' => __( 'Margin', 'fw' ),
                        'desc'  => __( 'Spacing around the button. All Sides applies to every side at once; any per-side value (Top, Right, Bottom, Left) overrides it. Padding comes from the Button Size preset, so it is not set here.', 'fw' ),
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
