<?php if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}

/**
 * Table of Contents — edit-modal options (atts schema).
 *
 * The TOC is a SIMPLE content element: it renders an (initially empty) <nav>
 * carrying its configuration as data-* attributes, and the frontend JS
 * (static/js/scripts.js) scans the resolved scope for the chosen heading
 * levels, assigns slug ids to them, and builds the clickable list. Nothing
 * here is rendered server-side beyond the shell, so every behavioural knob
 * below maps to a data-* attribute consumed by that script.
 */

$options = [

    /* ---------------------------------------------------------------------
     * CONTENT
     * ------------------------------------------------------------------ */
    'tab_content' => [
        'title'   => __( 'Content', 'fw' ),
        'type'    => 'tab',
        'options' => [
            'group_content' => [
                'type'    => 'group',
                'options' => [
                    'title' => [
                        'type'            => 'text',
                        'dynamic_content' => false,
                        'label'           => __( 'Title', 'fw' ),
                        'desc'            => __( 'Heading shown above the list. Leave empty to hide it.', 'fw' ),
                        'help'            => __( 'e.g. "Table of Contents", "On this page", "Contents". You can wrap part of it in inline HTML to emphasise a word.', 'fw' ),
                        'value'           => __( 'Table of Contents', 'fw' ),
                    ],
                    'levels' => [
                        'type'    => 'checkboxes',
                        'label'   => __( 'Heading Levels', 'fw' ),
                        'desc'    => __( 'Which heading tags to include in the list.', 'fw' ),
                        'help'    => __( 'Tick the levels you want listed. The classic blog/article TOC is H2 + H3. Pick only H2 for a short, top-level outline, or add H4–H6 for very deep documents. Headings inside the TOC box itself are always ignored.', 'fw' ),
                        'choices' => [
                            'h1' => __( 'H1', 'fw' ),
                            'h2' => __( 'H2', 'fw' ),
                            'h3' => __( 'H3', 'fw' ),
                            'h4' => __( 'H4', 'fw' ),
                            'h5' => __( 'H5', 'fw' ),
                            'h6' => __( 'H6', 'fw' ),
                        ],
                        'value'   => [ 'h2' => true, 'h3' => true ],
                    ],
                    'hierarchical' => [
                        'type'         => 'switch',
                        'label'        => __( 'Hierarchical View', 'fw' ),
                        'desc'         => __( 'Indent sub-headings under their parent (nested) instead of one flat list.', 'fw' ),
                        'help'         => __( 'On: an H3 is indented beneath the H2 that precedes it. Off: every heading sits at the same level, in document order.', 'fw' ),
                        'left-choice'  => [ 'value' => 'no',  'label' => __( 'No', 'fw' ) ],
                        'right-choice' => [ 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ],
                        'value'        => 'yes',
                    ],
                    'min_headings' => [
                        'type'  => 'text',
                        'label' => __( 'Minimum Headings', 'fw' ),
                        'desc'  => __( 'Only show the table of contents if the page has at least this many matching headings.', 'fw' ),
                        'help'  => __( 'Prevents a one-item TOC on short pages. If fewer headings are found, the whole box hides itself. Set to 1 to always show it.', 'fw' ),
                        'value' => '2',
                    ],
                ],
            ],
            'group_numbering' => [
                'type'    => 'group',
                'options' => [
                    'numeration' => [
                        'type'    => 'select',
                        'label'   => __( 'Numeration', 'fw' ),
                        'desc'    => __( 'How each item is marked.', 'fw' ),
                        'help'    => __( 'Decimal (nested) is the classic outline style — 1, 1.1, 1.2, 2 … Decimal (flat) restarts the count at each level. Choose Bullets for dots, or None for a plain link list.', 'fw' ),
                        'choices' => [
                            'none'           => __( 'None', 'fw' ),
                            'decimal_nested' => __( 'Decimal numbers (nested) — 1, 1.1, 1.2', 'fw' ),
                            'decimal'        => __( 'Decimal numbers (flat) — 1, 2, 3', 'fw' ),
                            'roman'          => __( 'Roman numerals — I, II, III', 'fw' ),
                            'upper_alpha'    => __( 'Letters — A, B, C', 'fw' ),
                            'bullets'        => __( 'Bullets', 'fw' ),
                        ],
                        'value'   => 'decimal_nested',
                    ],
                    'numeration_suffix' => [
                        'type'    => 'select',
                        'label'   => __( 'Numeration Suffix', 'fw' ),
                        'desc'    => __( 'Symbol added after the number, e.g. "1." or "1)". Ignored for Bullets / None.', 'fw' ),
                        'choices' => [
                            ''  => __( 'None', 'fw' ),
                            '.' => __( 'Dot ( . )', 'fw' ),
                            ')' => __( 'Parenthesis ( ) )', 'fw' ),
                        ],
                        'value'   => '.',
                    ],
                ],
            ],
            'group_header' => [
                'type'    => 'group',
                'options' => [
                    'collapsible' => [
                        'type'         => 'switch',
                        'label'        => __( 'Collapsible', 'fw' ),
                        'desc'         => __( 'Add a Show / Hide toggle next to the title so readers can fold the list away.', 'fw' ),
                        'left-choice'  => [ 'value' => 'no',  'label' => __( 'No', 'fw' ) ],
                        'right-choice' => [ 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ],
                        'value'        => 'no',
                    ],
                    'collapsed_default' => [
                        'type'         => 'switch',
                        'label'        => __( 'Collapsed by Default', 'fw' ),
                        'desc'         => __( 'Start with the list hidden (only the title + toggle visible). Requires Collapsible.', 'fw' ),
                        'left-choice'  => [ 'value' => 'no',  'label' => __( 'No', 'fw' ) ],
                        'right-choice' => [ 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ],
                        'value'        => 'no',
                    ],
                    'label_show' => [
                        'type'            => 'text',
                        'dynamic_content' => false,
                        'label'           => __( 'Toggle Label — Show', 'fw' ),
                        'desc'            => __( 'Text of the toggle when the list is hidden.', 'fw' ),
                        'value'           => __( 'show', 'fw' ),
                    ],
                    'label_hide' => [
                        'type'            => 'text',
                        'dynamic_content' => false,
                        'label'           => __( 'Toggle Label — Hide', 'fw' ),
                        'desc'            => __( 'Text of the toggle when the list is visible.', 'fw' ),
                        'value'           => __( 'hide', 'fw' ),
                    ],
                ],
            ],
        ],
    ],

    /* ---------------------------------------------------------------------
     * BEHAVIOR
     * ------------------------------------------------------------------ */
    'tab_behavior' => [
        'title'   => __( 'Behavior', 'fw' ),
        'type'    => 'tab',
        'options' => [
            'group_scope' => [
                'type'    => 'group',
                'options' => [
                    'scope' => [
                        'type'    => 'select',
                        'label'   => __( 'Scan Scope', 'fw' ),
                        'desc'    => __( 'Where to look for headings.', 'fw' ),
                        'help'    => __( 'Content area (recommended) auto-detects the main article/content wrapper, so the theme header, footer and sidebar headings are ignored. Whole page scans the entire document body. Custom selector lets you target any element by CSS selector.', 'fw' ),
                        'choices' => [
                            'content' => __( 'Content area (auto-detect)', 'fw' ),
                            'page'    => __( 'Whole page', 'fw' ),
                            'custom'  => __( 'Custom selector', 'fw' ),
                        ],
                        'value'   => 'content',
                    ],
                    'scope_selector' => [
                        'type'            => 'text',
                        'dynamic_content' => false,
                        'label'           => __( 'Custom Scope Selector', 'fw' ),
                        'desc'            => __( 'A CSS selector for the container to scan. Used only when Scan Scope is "Custom selector".', 'fw' ),
                        'help'            => __( 'e.g. ".entry-content", "#main", "article.post". The TOC scans for headings inside the FIRST element that matches.', 'fw' ),
                        'value'           => '',
                    ],
                    'skip_text' => [
                        'type'  => 'textarea',
                        'label' => __( 'Skip Headings (by text)', 'fw' ),
                        'desc'  => __( 'One per line. Any heading whose text contains a line listed here is left out of the table of contents.', 'fw' ),
                        'help'  => __( 'Case-insensitive "contains" match — e.g. "Comments" skips a "Leave a Comment" heading. Use it to hide boilerplate headings you don\'t want listed.', 'fw' ),
                        'value' => '',
                    ],
                ],
            ],
            'group_scroll' => [
                'type'    => 'group',
                'options' => [
                    'smooth_scroll' => [
                        'type'         => 'switch',
                        'label'        => __( 'Smooth Scroll', 'fw' ),
                        'desc'         => __( 'Animate the jump to a heading instead of snapping instantly.', 'fw' ),
                        'left-choice'  => [ 'value' => 'no',  'label' => __( 'No', 'fw' ) ],
                        'right-choice' => [ 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ],
                        'value'        => 'yes',
                    ],
                    'scroll_offset' => [
                        'type'  => 'unit-input',
                        'label' => __( 'Scroll Offset Top', 'fw' ),
                        'desc'  => __( 'Extra space left above the target heading when jumping — set this to your sticky header\'s height so the heading isn\'t hidden underneath it.', 'fw' ),
                        'help'  => __( 'If your site has a fixed/sticky header bar, enter its height (e.g. 80px) so clicked headings land just below it. Leave at 0 if there is no sticky header.', 'fw' ),
                        'units' => [ 'px' ],
                        'value' => [ 'value' => '0', 'unit' => 'px' ],
                        'min'   => 0,
                    ],
                    'scrollspy' => [
                        'type'         => 'switch',
                        'label'        => __( 'Highlight Active Heading', 'fw' ),
                        'desc'         => __( 'As the reader scrolls, highlight the TOC link for the heading currently in view (scrollspy).', 'fw' ),
                        'left-choice'  => [ 'value' => 'no',  'label' => __( 'No', 'fw' ) ],
                        'right-choice' => [ 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ],
                        'value'        => 'yes',
                    ],
                ],
            ],
            'group_seo' => [
                'type'    => 'group',
                'options' => [
                    'nofollow' => [
                        'type'         => 'switch',
                        'label'        => __( 'Nofollow Links', 'fw' ),
                        'desc'         => __( 'Add rel="nofollow" to the TOC anchor links.', 'fw' ),
                        'left-choice'  => [ 'value' => 'no',  'label' => __( 'No', 'fw' ) ],
                        'right-choice' => [ 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ],
                        'value'        => 'no',
                    ],
                    'noindex' => [
                        'type'         => 'switch',
                        'label'        => __( 'Wrap in noindex', 'fw' ),
                        'desc'         => __( 'Surround the TOC with <!--noindex--> … <!--/noindex--> so crawlers that honour it (e.g. Yandex) skip the link list.', 'fw' ),
                        'left-choice'  => [ 'value' => 'no',  'label' => __( 'No', 'fw' ) ],
                        'right-choice' => [ 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ],
                        'value'        => 'no',
                    ],
                ],
            ],
        ],
    ],

    /* ---------------------------------------------------------------------
     * STYLING
     * ------------------------------------------------------------------ */
    'tab_styling' => [
        'title'   => __( 'Styling', 'fw' ),
        'type'    => 'tab',
        'options' => [
            'group_box' => [
                'type'    => 'group',
                'options' => [
                    'width' => [
                        'type'    => 'select',
                        'label'   => __( 'Width', 'fw' ),
                        'desc'    => __( 'Box width within its column.', 'fw' ),
                        'choices' => [
                            'full'   => __( 'Full width', 'fw' ),
                            'auto'   => __( 'Auto (fit content)', 'fw' ),
                            'custom' => __( 'Custom', 'fw' ),
                        ],
                        'value'   => 'full',
                    ],
                    'custom_width' => [
                        'type'  => 'unit-input',
                        'label' => __( 'Custom Width', 'fw' ),
                        'desc'  => __( 'Used only when Width is "Custom".', 'fw' ),
                        'units' => [ 'px', '%', 'rem', 'em', 'vw' ],
                        'value' => [ 'value' => '', 'unit' => 'px' ],
                        'min'   => 0,
                    ],
                    'float' => [
                        'type'    => 'select',
                        'label'   => __( 'Float', 'fw' ),
                        'desc'    => __( 'Let the article text wrap around the box.', 'fw' ),
                        'help'    => __( 'None keeps the box on its own line (block). Left / Right floats it so following paragraphs wrap beside it — pair with a Custom or Auto width.', 'fw' ),
                        'choices' => [
                            ''      => __( 'None', 'fw' ),
                            'left'  => __( 'Left', 'fw' ),
                            'right' => __( 'Right', 'fw' ),
                        ],
                        'value'   => '',
                    ],
                    'sticky' => [
                        'type'         => 'switch',
                        'label'        => __( 'Sticky on Scroll', 'fw' ),
                        'desc'         => __( 'Pin the box so it stays in view while the reader scrolls (uses CSS position: sticky).', 'fw' ),
                        'help'         => __( 'Works best in a sidebar column. The browser keeps the box pinned within its parent column; it still scrolls away when the column ends.', 'fw' ),
                        'left-choice'  => [ 'value' => 'no',  'label' => __( 'No', 'fw' ) ],
                        'right-choice' => [ 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ],
                        'value'        => 'no',
                    ],
                    'sticky_offset' => [
                        'type'  => 'unit-input',
                        'label' => __( 'Sticky Offset Top', 'fw' ),
                        'desc'  => __( 'Gap kept above the box when pinned. Set to your sticky header height. Used only when Sticky is on.', 'fw' ),
                        'units' => [ 'px', 'rem', 'em' ],
                        'value' => [ 'value' => '20', 'unit' => 'px' ],
                        'min'   => 0,
                    ],
                ],
            ],
            'group_typography' => [
                'type'    => 'group',
                'options' => [
                    'title_size' => sc_font_size_field( array(
                        'label' => __( 'Title Font Size', 'fw' ),
                        'desc'  => __( 'Named size preset for the TOC title.', 'fw' ),
                    ) ),
                    'items_size' => sc_font_size_field( array(
                        'label' => __( 'Items Font Size', 'fw' ),
                        'desc'  => __( 'Named size preset for the list links.', 'fw' ),
                    ) ),
                ],
            ],
            'group_colors' => [
                'type'    => 'group',
                'options' => [
                    'bg_color' => sc_color_field_compact( array(
                        'label' => __( 'Background Color', 'fw' ),
                        'kind'  => 'bg',
                    ) ),
                    'border_color' => sc_color_field_compact( array(
                        'label' => __( 'Border Color', 'fw' ),
                        'kind'  => 'text',
                        'desc'  => __( 'Color of the box border. Leave default for no border.', 'fw' ),
                    ) ),
                    'title_color' => sc_color_field_compact( array(
                        'label' => __( 'Title Color', 'fw' ),
                        'kind'  => 'text',
                    ) ),
                    'link_color' => sc_color_field_compact( array(
                        'label' => __( 'Link Color', 'fw' ),
                        'kind'  => 'text',
                    ) ),
                    'link_hover_color' => sc_color_field_compact( array(
                        'label' => __( 'Link Hover Color', 'fw' ),
                        'kind'  => 'text',
                    ) ),
                    'link_active_color' => sc_color_field_compact( array(
                        'label' => __( 'Active Link Color', 'fw' ),
                        'kind'  => 'text',
                        'desc'  => __( 'Color of the highlighted link when "Highlight Active Heading" is on.', 'fw' ),
                    ) ),
                ],
            ],
            'group_spacings' => [
                'type'    => 'group',
                'options' => [
                    'spacing' => array(
                        'type'  => 'spacing',
                        'label' => __( 'Margin & Padding', 'fw' ),
                        'desc'  => __( 'All Sides applies to every side at once; any per-side value overrides it for that direction.', 'fw' ),
                        'help'  => sc_styling_help_text( 'spacing' ),
                    ),
                ],
            ],
        ],
    ],

    /* ---------------------------------------------------------------------
     * ANIMATIONS
     * ------------------------------------------------------------------ */
    'tab_animation' => [
        'title'   => __( 'Animations', 'fw' ),
        'type'    => 'tab',
        'options' => sc_get_animation_fields(),
    ],

    /* ---------------------------------------------------------------------
     * ADVANCED
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
