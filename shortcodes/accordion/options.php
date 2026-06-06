<?php if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}

$options = [
    'tab_content' => [
        'title'   => __('Content', 'fw'),
        'type'    => 'tab',
        'options' => [
            'tabs' => [
                'type'          => 'addable-popup',
                'label'         => __( 'Accordion Items', 'fw' ),
                'popup-title'   => __( 'Add/Edit Tabs', 'fw' ),
                'desc'          => __( 'Create your tabs', 'fw' ),
                'help'          => __( 'Each row becomes one collapsible accordion item; drag to reorder. The list is empty by default, so add at least one item.', 'fw' ),
                'template'      => '{{=tab_title}}',
                'popup-options' => [
                    'tab_title'   => [
                        'type'  => 'text',
                        'label' => __('Title', 'fw'),
                        'help'  => __('The clickable header for this item, e.g. a question like "How do I get a refund?". Keep it short so it fits on one line.', 'fw'),
                    ],
                    'tab_content' => [
                        'type'  => 'wp-editor',
                        'label' => __('Content', 'fw'),
                        'help'  => __('The panel body revealed when this item is opened. Supports rich text, images, and other shortcodes.', 'fw'),
                    ],
                    'is_open' => [
                        'type'  => 'switch',
                        'label' => __('Open by Default', 'fw'),
                        'desc'  => __('Render THIS item already expanded on first load. Overrides the shortcode-level Initially Open setting for this single item.', 'fw'),
                        'help'  => __('Turn on to force just this one item open even when Initially Open is set to None; handy for highlighting a key item.', 'fw'),
                        'right-choice' => [ 'value' => 'yes', 'label' => __('Yes', 'fw') ],
                        'left-choice'  => [ 'value' => 'no',  'label' => __('No',  'fw') ],
                        'value' => 'no',
                    ],
                ]
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

            'title_tag' => [
                'type'    => 'select',
                'label'   => __( 'Title Tag', 'fw' ),
                'desc'    => __( 'Semantic heading level for every accordion item title. Match the page outline (e.g. use H3 inside a section whose own heading is H2). Always emits a real heading element so screen readers can navigate by heading.', 'fw' ),
                'help'    => __( 'This affects document structure and SEO, not size. Never skip levels (do not jump from H2 to H5) on the same page.', 'fw' ),
                'value'   => 'h3',
                'choices' => [
                    'h2' => 'H2',
                    'h3' => 'H3',
                    'h4' => 'H4',
                    'h5' => 'H5',
                    'h6' => 'H6',
                ],
            ],

            'icon_style' => [
                'type'    => 'select',
                'label'   => __( 'Icon Style', 'fw' ),
                'desc'    => __( 'Choose the toggle indicator icon.', 'fw' ),
                'help'    => __( 'Pick "Custom" to supply your own image or text/emoji in the fields below; choose "No Icon" for a cleaner header with no visual toggle cue.', 'fw' ),
                'choices' => [
                    'plus-minus' => __( 'Plus / Minus (+−)', 'fw' ),
                    'plus-x'     => __( 'Plus / X (+×)', 'fw' ),
                    'chevron'    => __( 'Chevron (›)', 'fw' ),
                    'arrow'      => __( 'Arrow (▶)', 'fw' ),
                    'none'       => __( 'No Icon', 'fw' ),
                    'custom'     => __( 'Custom (image or text/emoji)', 'fw' ),
                ],
                'value'   => 'plus-minus',
            ],
            'icon_position' => [
                'type'    => 'select',
                'label'   => __( 'Icon Position', 'fw' ),
                'desc'    => __( 'Place the icon on the left or right side of the title.', 'fw' ),
                'help'    => __( 'Right placement pushes the icon to the far edge of the title bar, a common FAQ pattern; left keeps it next to the text.', 'fw' ),
                'choices' => [
                    'left'  => __( 'Left', 'fw' ),
                    'right' => __( 'Right', 'fw' ),
                ],
                'value'   => 'left',
            ],
            'icon_closed_image' => [
                'type'  => 'upload',
                'label' => __( 'Custom Closed-State Image', 'fw' ),
                'desc'  => __( 'Used when Icon Style is "Custom" and the panel is closed. PNG, JPG, or SVG. Overrides the closed-state text/emoji below.', 'fw' ),
                'help'  => __( 'Use a small square asset (around 24-32px) for crisp results; SVG scales best. Has no effect unless Icon Style is set to Custom.', 'fw' ),
            ],
            'icon_open_image' => [
                'type'  => 'upload',
                'label' => __( 'Custom Open-State Image', 'fw' ),
                'desc'  => __( 'Used when Icon Style is "Custom" and the panel is open. Overrides the open-state text/emoji below.', 'fw' ),
                'help'  => __( 'Typically the closed icon rotated or its opposite (e.g. a minus to the closed plus). Leave empty to reuse the closed image for both states.', 'fw' ),
            ],
            'icon_closed_text' => [
                'type'  => 'short-text',
                'label' => __( 'Custom Closed-State Text', 'fw' ),
                'desc'  => __( 'Used when Icon Style is "Custom" and no closed-state image is uploaded. Examples: + ▼ ▶ 👇', 'fw' ),
                'help'  => __( 'A single character or emoji works best. Ignored if a Custom Closed-State Image is uploaded above.', 'fw' ),
                'value' => '+',
            ],
            'icon_open_text' => [
                'type'  => 'short-text',
                'label' => __( 'Custom Open-State Text', 'fw' ),
                'desc'  => __( 'Used when Icon Style is "Custom" and no open-state image is uploaded. Examples: − ▲ ▼ 👆', 'fw' ),
                'help'  => __( 'Pair it with the closed-state character so the toggle reads clearly (e.g. + when closed, − when open). Ignored if an open-state image is set.', 'fw' ),
                'value' => '−',
            ],
            'numbering' => [
                'type'   => 'multi-picker',
                'label'  => false,
                'desc'   => false,
                'picker' => [
                    'style' => [
                        'type'    => 'select',
                        'label'   => __( 'Item Numbering', 'fw' ),
                        'desc'    => __( 'Prefix each title with a number, letter, or custom label.', 'fw' ),
                        'help'    => __( 'Great for step-by-step guides or FAQs (Q1, Q2…). Choose "Custom…" to define your own token pattern in the field that appears.', 'fw' ),
                        'choices' => [
                            'none'                 => __( 'None', 'fw' ),
                            'decimal'              => __( '1, 2, 3', 'fw' ),
                            'decimal-leading-zero' => __( '01, 02, 03', 'fw' ),
                            'lower-alpha'          => __( 'a, b, c', 'fw' ),
                            'upper-alpha'          => __( 'A, B, C', 'fw' ),
                            'lower-roman'          => __( 'i, ii, iii', 'fw' ),
                            'upper-roman'          => __( 'I, II, III', 'fw' ),
                            'q-prefix'             => __( 'Q1, Q2, Q3', 'fw' ),
                            'custom'               => __( 'Custom…', 'fw' ),
                        ],
                        'value'   => 'none',
                    ],
                ],
                'choices' => [
                    'none'                 => [],
                    'decimal'              => [],
                    'decimal-leading-zero' => [],
                    'lower-alpha'          => [],
                    'upper-alpha'          => [],
                    'lower-roman'          => [],
                    'upper-roman'          => [],
                    'q-prefix'             => [],
                    'custom' => [
                        'template' => [
                            'type'  => 'text',
                            'label' => __( 'Custom Template', 'fw' ),
                            'desc'  => __( 'Tokens: {n}=1,2,3 — {0n}=01,02,03 — {a}/{A}=letters — {i}/{I}=Roman. Example: "Q{n}" or "Step {n}".', 'fw' ),
                            'help'  => __( 'Any literal text around the token is kept verbatim, so "Step {n}:" renders "Step 1:", "Step 2:" and so on.', 'fw' ),
                            'value' => 'Q{n}',
                        ],
                    ],
                ],
                'show_borders' => false,
            ],
            'numbering_start' => [
                'type'  => 'short-text',
                'label' => __( 'Start Number', 'fw' ),
                'desc'  => __( 'The number assigned to the first item. Defaults to 1. Use any integer to begin elsewhere (e.g. 5 to start at Q5 / e. / V).', 'fw' ),
                'help'  => __( 'Useful for continuing a list split across multiple accordions, so the second block can pick up where the first left off. Only applies when Item Numbering is not None.', 'fw' ),
                'value' => '1',
            ],
            'item_spacing' => sc_spacing_field( array(
                'label'  => __( 'Item Spacing', 'fw' ),
                'desc'   => __( 'Vertical gap between accordion items. Uses the theme spacing presets; applied as margin-bottom on every item except the last.', 'fw' ),
                'prefix' => 'mb',
            ) ),
            'title_alignment' => [
                'type'    => 'select',
                'label'   => __( 'Title Alignment', 'fw' ),
                'desc'    => __( 'Horizontal alignment of the title row (icon + number + text) inside the title bar.', 'fw' ),
                'help'    => __( 'Center reads well for short single-line titles; left is best for longer titles that may wrap. The icon stays at its chosen Icon Position regardless.', 'fw' ),
                'value'   => 'left',
                'choices' => [
                    'left'   => __( 'Left (Default)', 'fw' ),
                    'center' => __( 'Center', 'fw' ),
                    'right'  => __( 'Right', 'fw' ),
                ],
            ],
        ],],],
    ],

    'tab_behaviour' => [
        'title'   => __( 'Behaviour', 'fw' ),
        'type'    => 'tab',
        'options' => [
            'group_layout' => [
                'type'    => 'group',
                'options' => [



            'initially_open' => [
                'type'    => 'select',
                'label'   => __( 'Initially Open', 'fw' ),
                'desc'    => __( 'Which panels are expanded when the page loads.', 'fw' ),
                'help'    => __( 'A per-item "Open by Default" switch can override this for individual items. "All Open" only stays that way if Multiple Open is enabled.', 'fw' ),
                'choices' => [
                    'first' => __( 'First Item', 'fw' ),
                    'none'  => __( 'None (All Closed)', 'fw' ),
                    'all'   => __( 'All Open', 'fw' ),
                ],
                'value'   => 'first',
            ],
            'collapsible' => [
                'type'         => 'switch',
                'label'        => __( 'Collapsible', 'fw' ),
                'desc'         => __( 'Allow all panels to be closed at once.', 'fw' ),
                'help'          => __( 'When off, clicking the currently open item keeps it open (one item is always expanded). Turn on to let visitors close everything.', 'fw' ),
                'right-choice' => [
                    'value' => 'yes',
                    'label' => __( 'Yes', 'fw' ),
                ],
                'left-choice'  => [
                    'value' => 'no',
                    'label' => __( 'No', 'fw' ),
                ],
                'value'        => 'no',
            ],
            'multiple_open' => [
                'type'         => 'switch',
                'label'        => __( 'Multiple Open', 'fw' ),
                'desc'         => __( 'Allow more than one panel to be open at a time.', 'fw' ),
                'help'          => __( 'Off gives classic accordion behaviour where opening one item closes the others. Turn on if you want the Expand / Collapse All buttons to be meaningful.', 'fw' ),
                'right-choice' => [
                    'value' => 'yes',
                    'label' => __( 'Yes', 'fw' ),
                ],
                'left-choice'  => [
                    'value' => 'no',
                    'label' => __( 'No', 'fw' ),
                ],
                'value'        => 'no',
            ],
            'hash_linking' => [
                'type'         => 'switch',
                'label'        => __( 'URL Hash Deep-Linking', 'fw' ),
                'desc'         => __( 'Auto-open the item whose ID matches the URL hash (e.g. .../page/#accordion-abc-panel-3) and update the hash on toggle so links stay shareable. Closing items does not clear the hash.', 'fw' ),
                'help'         => __( 'Lets you link directly to a specific FAQ answer. Disable it if multiple accordions on one page cause unwanted scrolling or hash conflicts.', 'fw' ),
                'right-choice' => [ 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ],
                'left-choice'  => [ 'value' => 'no',  'label' => __( 'No',  'fw' ) ],
                'value'        => 'yes',
            ],
            'show_expand_collapse_all' => [
                'type'         => 'switch',
                'label'        => __( 'Show Expand / Collapse All Buttons', 'fw' ),
                'desc'         => __( 'Renders two convenience buttons above the accordion that open or close every item at once. Most useful when Multiple Open is also enabled.', 'fw' ),
                'help'         => __( 'Best for long lists where users may want to scan everything quickly. With Multiple Open off, "Expand All" can only show one item at a time.', 'fw' ),
                'right-choice' => [ 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ],
                'left-choice'  => [ 'value' => 'no',  'label' => __( 'No',  'fw' ) ],
                'value'        => 'no',
            ],
        ],],],
    ],

    'tab_styling' => [
        'title'   => __( 'Styling', 'fw' ),
        'type'    => 'tab',
        // Drop the wrapper-level Text Color AND Background Color — accordions
        // expose Title/Content text + bg pickers below (a single wrapper
        // colour would conflict with the per-element picks).
        'options' => [
            'group_colors' => [
                'type'    => 'group',
                'options' => [
                    'font_size_preset' => sc_font_size_field( array(
                        'desc' => __( 'A named size from the framework presets. Customizable in Theme Settings on the official Unyson+ theme.', 'fw' ),
                    ) ),
                    'tab_title_color' => sc_color_field_compact( array(
                        'label' => __( 'Title Color', 'fw' ),
                        'desc'  => __( 'Overrides the general Text Color for every accordion item title (applied across all items).', 'fw' ),
                    ) ),
                    'title_bg_color' => sc_color_field_compact( array(
                        'label' => __( 'Title Background Color', 'fw' ),
                        'kind'  => 'bg',
                        'desc'  => __( 'Background color preset applied to every accordion item title bar.', 'fw' ),
                    ) ),
                    'tab_content_color' => sc_color_field_compact( array(
                        'label' => __( 'Content Color', 'fw' ),
                        'desc'  => __( 'Overrides the general Text Color for every accordion item body (applied across all items).', 'fw' ),
                    ) ),
                    'content_bg_color' => sc_color_field_compact( array(
                        'label' => __( 'Content Background Color', 'fw' ),
                        'kind'  => 'bg',
                        'desc'  => __( 'Background color preset applied to every accordion item content panel.', 'fw' ),
                    ) ),
                    'icon_closed_color' => sc_color_field_compact( array(
                        'label' => __( 'Icon Color (Closed State)', 'fw' ),
                        'desc'  => __( 'Color for the toggle icon when an item is collapsed. Works for both Built-in (plus-minus / chevron / arrow / etc.) and Custom icon styles.', 'fw' ),
                    ) ),
                    'icon_open_color' => sc_color_field_compact( array(
                        'label' => __( 'Icon Color (Open State)', 'fw' ),
                        'desc'  => __( 'Color for the toggle icon when an item is expanded. Falls back to the Closed-state color when not picked.', 'fw' ),
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
