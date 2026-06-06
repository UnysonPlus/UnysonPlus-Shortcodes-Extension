<?php if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}

$options = [
    'tab_content' => [
        'title'   => __('Content', 'fw'),
        'type'    => 'tab',
        'options' => [
            'message' => [
                'label' => __('Message', 'fw'),
                'desc'  => __('Notification message body', 'fw'),
                'help'  => __('Basic HTML such as &lt;a&gt;, &lt;strong&gt; and &lt;br&gt; is allowed here, so you can add links or line breaks inside the alert.', 'fw'),
                'type'  => 'textarea', // Swapped to textarea for better multi-line/HTML support
                'value' => __('Message!', 'fw'),
            ],
            'label_text' => [
                'label' => __('Custom Label', 'fw'),
                'desc'  => __('Optional. Leave empty to use the default type label (e.g., "Success!", "Warning!").', 'fw'),
                'help'  => __('Use this to replace the bold heading word, e.g. "Heads up" or "Order confirmed", instead of the automatic label tied to the Type.', 'fw'),
                'type'  => 'text',
                'value' => '',
            ],
            'type' => [
                'label'   => __('Type', 'fw'),
                'desc'    => __('Notification type color scheme and icon', 'fw'),
                'help'    => __('Sets the semantic meaning: e.g. "Success" for confirmations, "Warning" for cautions, "Danger" for errors. It also drives the default label and icon unless you override them.', 'fw'),
                'type'    => 'select',
                'value'   => 'info',
                'choices' => [
                    'primary'   => __('Primary', 'fw'),
                    'secondary' => __('Secondary', 'fw'),
                    'success'   => __('Success', 'fw'),
                    'info'      => __('Information', 'fw'),
                    'warning'   => __('Warning', 'fw'),
                    'danger'    => __('Danger', 'fw'),
                    'light'     => __('Light', 'fw'),
                    'dark'      => __('Dark', 'fw'),
                ],
            ],
            'border_style' => [
                'label'   => __('Border Style', 'fw'),
                'desc'    => __('Visual treatment for the notification box', 'fw'),
                'help'    => __('"Filled" gives a solid colored background; "Outline" keeps it transparent with a colored border; "Accent Left Border" adds just a thick colored bar on the left.', 'fw'),
                'type'    => 'select',
                'value'   => 'filled',
                'choices' => [
                    'filled'      => __('Filled (Default)', 'fw'),
                    'outline'     => __('Outline (Transparent background)', 'fw'),
                    'accent-left' => __('Accent Left Border', 'fw'),
                ],
            ],
            'icon' => [
                'type'         => 'icon-v2',
                'label'        => __( 'Icon', 'fw' ),
                'preview_size' => 'medium',
                'modal_size'   => 'medium',
                'desc'         => __( 'Pick an icon from the library. Will be ignored if "Custom Icon" below is filled.', 'fw' ),
                'help'         => __( 'Leave this on its default to show the icon that matches the chosen Type. Pick a specific icon only when you want something other than the type default.', 'fw' ),
            ],

            'custom_icon' => [
                'type'  => 'text',
                'label' => __( 'Custom Icon (Emoji / SVG)', 'fw' ),
                'desc'  => __( 'Optional. If filled, this overrides the Icon picker above. Accepts an emoji (e.g. ⭐) or inline SVG markup.', 'fw' ),
                'help'  => __( 'Handy for a quick emoji without browsing the icon library, or for pasting a brand-specific SVG. The Icon Color setting does not apply to emoji.', 'fw' ),
            ],
            'layout' => [
                'label'   => __('Layout Style', 'fw'),
                'desc'    => __('Choose how the icon, label, and message are structured', 'fw'),
                'help'    => __('"Inline" keeps everything on one row (best for short alerts); "Stacked" puts the label on its own line above the message (better for longer text).', 'fw'),
                'type'    => 'select',
                'value'   => 'inline',
                'choices' => [
                    'inline'  => __('Inline (Single Row)', 'fw'),
                    'stacked' => __('Stacked (Label Above Message)', 'fw'),
                ],
            ],
            'dismissible' => [
                'label'        => __('Dismissible', 'fw'),
                'desc'         => __('Enable a close button so users can dismiss the alert', 'fw'),
                'help'         => __('Required for the "Auto-dismiss Seconds" option below to have any effect. Leave off for permanent notices you always want visible.', 'fw'),
                'type'         => 'switch',
                'right-choice' => [
                    'value' => true,
                    'label' => __('Yes', 'fw'),
                ],
                'left-choice'  => [
                    'value' => false,
                    'label' => __('No', 'fw'),
                ],
                'value'        => false,
            ],
            'auto_dismiss' => [
                'label' => __('Auto-dismiss Seconds', 'fw'),
                'desc'  => __('Automatically close the alert after this many seconds. Set to 0 to disable. Only takes effect when "Dismissible" is enabled.', 'fw'),
                'help'  => __('Enter a whole number of seconds, e.g. 5 for a brief "Saved!" toast. Keep it long enough for visitors to actually read longer messages.', 'fw'),
                'type'  => 'short-text',
                'value' => '0',
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
                    'font_size_preset' => sc_font_size_field( array(
                        'desc' => __( 'A named size from the framework presets. Customizable in Theme Settings on the official Unyson+ theme.', 'fw' ),
                    ) ),
                    'label_color' => sc_color_field_compact( array(
                        'label' => __( 'Label Color', 'fw' ),
                        'desc'  => __( 'Overrides the general Text Color for the label only (e.g. "Success!" / "Warning!" / your Custom Label).', 'fw' ),
                    ) ),
                    'message_color' => sc_color_field_compact( array(
                        'label' => __( 'Message Color', 'fw' ),
                        'desc'  => __( 'Overrides the general Text Color for the message body only.', 'fw' ),
                    ) ),
                    'icon_color' => sc_color_field_compact( array(
                        'label' => __( 'Icon Color', 'fw' ),
                        'desc'  => __( 'Overrides the general Text Color for the icon (font icons inherit by default; custom emoji ignores this).', 'fw' ),
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
                    []
                ),
            ],
        ],
    ],
];