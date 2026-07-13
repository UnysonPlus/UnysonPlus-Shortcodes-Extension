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
                    'title'   => [
                        'type'  => 'text',
                        'label' => __('Title', 'fw'),
                        'desc'  => __('This can be left blank', 'fw'),
                        'help'  => __('The bold headline above the content, e.g. "Ready to get started?". Leave empty if the content alone carries the message.', 'fw')
                    ],
                    'message' => [
                        'type'   => 'wp-editor',
                        //'teeny'  => false, //Whether to output the minimal editor configuration used in PressThis
                        'reinit' => true,
                        'label'  => __('Content', 'fw'),
                        'desc'   => __('Enter content for this text block', 'fw'),
                        'help'   => __('Use the visual editor for formatting, links, and lists. Keep it concise; this sits between the Title and the button.', 'fw'),
                        'tinymce' => true, //Load TinyMCE, can be used to pass settings directly to TinyMCE using an array. Default: true
                        'size' => 'large',
                        'editor_height' => 425, //The height to set the editor in pixels. If set, will be used instead of textarea_rows. 
                        //'editor_type' => 'tinymce',
                        'shortcodes' => true,
                        'wpautop' => true, //Whether to use wpautop for adding in paragraphs. Note that the paragraphs are added automatically when wpautop is false.
                        'value' => ''
                    ],
                    'button_label' => [
                        'label' => __('Button Label', 'fw'),
                        'desc'  => __('This is the text that appears on your button', 'fw'),
                        'help'  => __('Use a short action phrase, e.g. "Get Started" or "Contact Us". Empty labels leave the button blank.', 'fw'),
                        'type'  => 'text',
                        'value' => 'Click'
                    ],
                    'button_link' => [
                        'label' => __('Button Link', 'fw'),
                        'desc'  => __('Where should your button link to', 'fw'),
                        'help'  => __('Enter a full URL (e.g. https://example.com/signup) or an on-page anchor like #contact. The default "#" goes nowhere, so replace it.', 'fw'),
                        'type'  => 'text',
                        'value' => '#'
                    ],
                    'button_target' => [
                        'type'  => 'switch',
                        'label' => __('Open Link in New Window', 'fw'),
                        'desc'  => __('Select here if you want to open the linked page in a new window', 'fw'),
                        'help'  => __('Recommended for links to external sites so visitors keep your page open. Leave off for links within your own site.', 'fw'),
                        'right-choice' => [
                            'value' => '_blank',
                            'label' => __('Yes', 'fw'),
                        ],
                        'left-choice' => [
                            'value' => '_self',
                            'label' => __('No', 'fw'),
                        ],
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
                    'column_split' => [
                        'type'          => 'column-split',
                        'label'         => __( 'Content / Button Split', 'fw' ),
                        'desc'          => __( 'Drag the divider to set how the row is shared between the content and the button.', 'fw' ),
                        'help'          => __( 'The content takes the left share, the button the right (shown as a fraction in lowest form, e.g. 3/4 or 3/5). The divider snaps to twelfths and fifths. On narrow screens the two stack vertically.', 'fw' ),
                        'value'         => '3/4',
                        'denominator'   => 12,
                        'fractions'     => [ '1/12', '1/6', '1/5', '1/4', '1/3', '2/5', '5/12', '1/2', '7/12', '3/5', '2/3', '3/4', '4/5', '5/6', '11/12' ],
                        'show_fraction' => true,
                        'panes'         => [
                            [ 'label' => __( 'Content', 'fw' ), 'icon' => 'dashicons-text' ],
                            [ 'label' => __( 'Button', 'fw' ),  'icon' => 'dashicons-button' ],
                        ],
                    ],
                ],
            ],
        ],
    ],

    'tab_styling' => [
        'title'   => __( 'Styling', 'fw' ),
        'type'    => 'tab',
        // Drop the default wrapper-level Text Color — Title Color and Content
        // Color below cover both text elements, so a wrapper-level pick would
        // just compete with the per-element picks.
        'options' => [
            'group_colors' => [
                'type'    => 'group',
                'options' => [
                    'bg_color'         => sc_color_field_compact( array( 'label' => __( 'Background Color', 'fw' ), 'kind' => 'bg' ) ),
                    'font_size_preset' => sc_font_size_field( array(
                        'desc' => __( 'A named size from the framework presets. Customizable in Theme Settings on the official Unyson+ theme.', 'fw' ),
                    ) ),
                    'title_color' => sc_color_field_compact( array(
                        'label' => __( 'Title Color', 'fw' ),
                        'desc'  => __( 'Color preset applied to the call-to-action title.', 'fw' ),
                    ) ),
                    // Key stays `message_color` for back-compat with saved
                    // instances; the editor-facing label is "Content Color"
                    // to match the Content tab where the field is "Content".
                    'message_color' => sc_color_field_compact( array(
                        'label' => __( 'Content Color', 'fw' ),
                        'desc'  => __( 'Color preset applied to the call-to-action content / body.', 'fw' ),
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
