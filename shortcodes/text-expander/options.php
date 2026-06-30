<?php
/**
 * Text Expander shortcode — option schema.
 *
 * Architecture notes:
 *  - Tabs follow the Unyson page-builder convention (tab → group → options).
 *  - Three orthogonal Layout options:
 *      show_btn_position  (inline | block_left | block_center | block_right)
 *      hide_btn_position  (inherit | inline | block_left | block_center | block_right)
 *      merge_boundary     (yes | no)
 *    These keys are stable identifiers consumed by view.php, the
 *    stylesheet, and scripts.js. Rename only in lock-step.
 *  - Directory `text-expander/` → Unyson registers the shortcode tag as
 *    `text_expander` (hyphens become underscores). CSS classes use the
 *    `fw-text-expander` prefix.
 *  - Field convention: `desc` is a one-line definition shown under the
 *    input; `help` is the longer tooltip explainer shown when authors
 *    hover the (?) icon next to the label.
 *
 * @package unysonplus\shortcodes\text_expander
 */

if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}

// Asset URL for the button-placement preview SVGs in static/img/.
// Resolved once so the image-picker choices below stay compact.
$fw_te_img_uri = fw_ext( 'shortcodes' )->get_declared_URI( '/shortcodes/text-expander/static/img' );

// Tiny helper: each image-picker choice has the same shape (one image at
// 60px high with a hover title). Keeps the show/hide picker arrays tidy.
$fw_te_btn_choice = function ( $file, $title ) use ( $fw_te_img_uri ) {
    return [
        'small' => [
            'src'    => $fw_te_img_uri . '/' . $file,
            'height' => 60,
            'title'  => $title,
        ],
    ];
};

$options = [

    /* ---------------------------------------------------------------------
     * Content tab — rich-text editors plus show/hide button text.
     * ------------------------------------------------------------------ */
    'tab_content' => [
        'title'   => __( 'Content', 'fw' ),
        'type'    => 'tab',
        'options' => [
            'group_content' => [
                'type'    => 'group',
                'options' => [
                    'visible_content' => [
                        'type'  => 'wp-editor',
                        'label' => __( 'Visible Content', 'fw' ),
                        'desc'  => __( 'Always shown on the page.', 'fw' ),
                        'help'  => __( 'This content stays visible whether the expander is open or closed. If you use Merge mode, only the LAST paragraph of this field is stitched onto the hidden content — every earlier paragraph renders verbatim with its original markup intact.', 'fw' ),
                    ],
                    'hidden_content' => [
                        'type'  => 'wp-editor',
                        'label' => __( 'Hidden Content', 'fw' ),
                        'desc'  => __( 'Revealed when the user clicks the toggle button.', 'fw' ),
                        'help'  => __( 'This content stays hidden until the user clicks Show More. If you use Merge mode, only the FIRST paragraph of this field is stitched onto the visible content — later paragraphs are revealed as their own paragraphs below.', 'fw' ),
                    ],
                ],
            ],
            'group_button' => [
                'type'    => 'group',
                'options' => [
                    'btn_show' => [
                        'type'  => 'text',
                        'label' => __( 'Button Text (Show)', 'fw' ),
                        'desc'  => __( 'Label for the toggle when collapsed.', 'fw' ),
                        'help'  => __( 'Default "Show More". Include the token {count} anywhere in this label to insert the word- or character-count of the hidden content at that position (requires Word/Character Count enabled in the Layout tab).', 'fw' ),
                        'value' => __( 'Show More', 'fw' ),
                    ],
                    'btn_hide' => [
                        'type'  => 'text',
                        'label' => __( 'Button Text (Hide)', 'fw' ),
                        'desc'  => __( 'Label for the toggle when expanded.', 'fw' ),
                        'help'  => __( 'Default "Show Less". Same {count} token rules as the Show label.', 'fw' ),
                        'value' => __( 'Show Less', 'fw' ),
                    ],
                ],
            ],
        ],
    ],

    /* ---------------------------------------------------------------------
     * Layout tab — button placement, text stitching, and behavioural switches.
     * ------------------------------------------------------------------ */
    'tab_layout' => [
        'title'   => __( 'Layout', 'fw' ),
        'type'    => 'tab',
        'options' => [
            'group_layout' => [
                'type'    => 'group',
                'options' => [

                    'show_btn_position' => [
                        'type'    => 'image-picker',
                        'label'   => __( '"Show More" Button Location', 'fw' ),
                        'desc'    => __( 'Choose where the expandable button appears when content is hidden.', 'fw' ),
                        'help'    => __( 'Connected to text places the link right at the end of your visible text string (like a standard magazine read-more link). Separate line moves the button down onto its own brand new line with custom layout alignments.', 'fw' ),
                        'choices' => [
                            'inline'       => $fw_te_btn_choice( 'btn-inline.svg',       __( 'Connected to text — right at the end of the last visible sentence', 'fw' ) ),
                            'block_left'   => $fw_te_btn_choice( 'btn-block-left.svg',   __( 'Separate line — below the text, aligned Left', 'fw' ) ),
                            'block_center' => $fw_te_btn_choice( 'btn-block-center.svg', __( 'Separate line — below the text, Centered', 'fw' ) ),
                            'block_right'  => $fw_te_btn_choice( 'btn-block-right.svg',  __( 'Separate line — below the text, aligned Right', 'fw' ) ),
                        ],
                        'value'   => 'inline',
                    ],

                    'hide_btn_position' => [
                        'type'    => 'image-picker',
                        'label'   => __( '"Show Less" Button Location', 'fw' ),
                        'desc'    => __( 'Choose where the collapse button appears once the text is expanded.', 'fw' ),
                        'help'    => __( 'Automatic matches your setting above, but automatically drops the button down to the absolute end of the newly revealed content. Manually choosing a "Separate line" option pins the close button to a specific static area.', 'fw' ),
                        'choices' => [
                            'inherit'      => $fw_te_btn_choice( 'btn-auto.svg',         __( 'Automatic (Recommended) — matches style above, placed after expanded text', 'fw' ) ),
                            'inline'       => $fw_te_btn_choice( 'btn-inline.svg',       __( 'Connected to text — right at the end of the last hidden sentence', 'fw' ) ),
                            'block_left'   => $fw_te_btn_choice( 'btn-block-left.svg',   __( 'Separate line — below expanded text, aligned Left', 'fw' ) ),
                            'block_center' => $fw_te_btn_choice( 'btn-block-center.svg', __( 'Separate line — below expanded text, Centered', 'fw' ) ),
                            'block_right'  => $fw_te_btn_choice( 'btn-block-right.svg',  __( 'Separate line — below expanded text, aligned Right', 'fw' ) ),
                        ],
                        'value'   => 'inherit',
                    ],

                    'merge_boundary' => [
                        'type'         => 'switch',
                        'label'        => __( 'Merge Boundary Paragraphs', 'fw' ),
                        'desc'         => __( 'Stitch the boundary paragraphs into one when expanded.', 'fw' ),
                        'help'         => __( 'When enabled, the LAST visible paragraph and the FIRST hidden paragraph are rendered inside a single &lt;p&gt; tag so the expander reads as one continuous sentence on expand. All other paragraphs are preserved. Independent of where the buttons sit.', 'fw' ),
                        'right-choice' => [ 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ],
                        'left-choice'  => [ 'value' => 'no',  'label' => __( 'No',  'fw' ) ],
                        'value'        => 'no',
                    ],

                    'show_ellipsis' => [
                        'type'         => 'switch',
                        'label'        => __( 'Show Ellipsis When Collapsed', 'fw' ),
                        'desc'         => __( 'Append … after the visible text when collapsed.', 'fw' ),
                        'help'         => __( 'Pure-CSS ellipsis injected via &lt;code&gt;::after&lt;/code&gt;. It only appears while the expander is collapsed and disappears the moment it is opened, so screen readers never announce a stray "…" character.', 'fw' ),
                        'right-choice' => [ 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ],
                        'left-choice'  => [ 'value' => 'no',  'label' => __( 'No',  'fw' ) ],
                        'value'        => 'no',
                    ],

                    'count_mode' => [
                        'type'    => 'select',
                        'label'   => __( 'Word/Character Count', 'fw' ),
                        'desc'    => __( 'Append a word or character count to the button label.', 'fw' ),
                        'help'    => __( 'Counts the hidden content client-side (so nested shortcodes are counted accurately) and rewrites the Show / Hide button labels. Use the {count} token inside the button text to place the number precisely; otherwise the count is appended in parentheses.', 'fw' ),
                        'choices' => [
                            'none'  => __( 'None', 'fw' ),
                            'words' => __( 'Word count', 'fw' ),
                            'chars' => __( 'Character count', 'fw' ),
                        ],
                        'value'   => 'none',
                    ],

                    'click_anywhere' => [
                        'type'         => 'switch',
                        'label'        => __( 'Click-Anywhere to Expand', 'fw' ),
                        'desc'         => __( 'Make the visible region clickable too.', 'fw' ),
                        'help'         => __( 'When collapsed, a click anywhere on the visible content opens the expander. Clicks on links, buttons, and form controls inside the visible area still fire normally — they are not intercepted.', 'fw' ),
                        'right-choice' => [ 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ],
                        'left-choice'  => [ 'value' => 'no',  'label' => __( 'No',  'fw' ) ],
                        'value'        => 'no',
                    ],

                    'native_details' => [
                        'type'         => 'switch',
                        'label'        => __( 'Use Native <details>', 'fw' ),
                        'desc'         => __( 'Render as a browser-native disclosure widget.', 'fw' ),
                        'help'         => __( 'Renders as &lt;details&gt;/&lt;summary&gt; instead of the custom widget. Best for SEO and assistive technology because the browser owns the toggle behaviour. Overrides Show / Hide button position, Toggle Icon, Animation, Click-Anywhere, and Word/Character Count options.', 'fw' ),
                        'right-choice' => [ 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ],
                        'left-choice'  => [ 'value' => 'no',  'label' => __( 'No',  'fw' ) ],
                        'value'        => 'no',
                    ],
                ],
            ],
        ],
    ],

    /* ---------------------------------------------------------------------
     * Styling tab — toggle button colour, icon, and initial state.
     * ------------------------------------------------------------------ */
    'tab_styling' => [
        'title'   => __( 'Styling', 'fw' ),
        'type'    => 'tab',
        'options' => [
            'group_colors' => [
                'type'    => 'group',
                'options' => [
                    'btn_color' => sc_color_field_compact( array(
                        'label' => __( 'Button Colour', 'fw' ),
                        'kind'  => 'text',
                        'desc'  => __( 'Color for both toggle buttons — a palette preset or a custom color. Overridden by the per-button colors below when those are picked.', 'fw' ),
                    ) ),
                    'visible_color' => sc_color_field_compact( array(
                        'label' => __( 'Visible Content Color', 'fw' ),
                        'desc'  => __( 'Color preset applied to every paragraph in Visible Content.', 'fw' ),
                    ) ),
                    'hidden_color' => sc_color_field_compact( array(
                        'label' => __( 'Hidden Content Color', 'fw' ),
                        'desc'  => __( 'Color preset applied to every paragraph in Hidden Content (and the bridge span when Merge is on).', 'fw' ),
                    ) ),
                    'btn_show_color' => sc_color_field_compact( array(
                        'label' => __( 'Show More Button Color', 'fw' ),
                        'desc'  => __( 'Color preset applied to the "Show More" toggle button. Overrides the free-form Button Colour above.', 'fw' ),
                    ) ),
                    'btn_hide_color' => sc_color_field_compact( array(
                        'label' => __( 'Show Less Button Color', 'fw' ),
                        'desc'  => __( 'Color preset applied to the "Show Less" toggle button. Overrides the free-form Button Colour above.', 'fw' ),
                    ) ),
                ],
            ],
            'group_options' => [
                'type'    => 'group',
                'options' => [
                    'toggle_icon' => [
                        'type'    => 'select',
                        'label'   => __( 'Toggle Icon', 'fw' ),
                        'desc'    => __( 'Optional icon next to the button label.', 'fw' ),
                        'help'    => __( 'Chevron rotates 90° when expanded. Plus / Minus swaps glyphs. Bypassed in native &lt;details&gt; mode because the browser draws its own disclosure triangle.', 'fw' ),
                        'choices' => [
                            'none'       => __( 'None', 'fw' ),
                            'chevron'    => __( 'Chevron (rotates)', 'fw' ),
                            'plus-minus' => __( 'Plus / Minus', 'fw' ),
                        ],
                        'value'   => 'none',
                    ],
                    'initially_open' => [
                        'type'         => 'switch',
                        'label'        => __( 'Initially Open', 'fw' ),
                        'desc'         => __( 'Render the expander already expanded.', 'fw' ),
                        'help'         => __( 'Server emits the expanded state on first paint so there is no flash. In native &lt;details&gt; mode this maps to the [open] HTML attribute.', 'fw' ),
                        'right-choice' => [ 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ],
                        'left-choice'  => [ 'value' => 'no',  'label' => __( 'No',  'fw' ) ],
                        'value'        => 'no',
                    ],
                ],
            ],
        ],
    ],

    /* ---------------------------------------------------------------------
     * Animations tab — reuses the shared shortcode animation helper.
     * ------------------------------------------------------------------ */
    'tab_animation' => [
        'title'   => __( 'Animations', 'fw' ),
        'type'    => 'tab',
        'options' => sc_get_animation_fields(),
    ],

    /* ---------------------------------------------------------------------
     * Advanced tab — CSS id/class, responsive visibility, unique id.
     * ------------------------------------------------------------------ */
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
