<?php if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}

/*
|--------------------------------------------------------------------------
| Dynamically populate post-type choices
|--------------------------------------------------------------------------
*/
$sc_post_type_choices = [];
if ( function_exists( 'get_post_types' ) ) {
    foreach ( get_post_types( [ 'public' => true ], 'objects' ) as $pt ) {
        if ( $pt->name === 'attachment' ) {
            continue;
        }
        $sc_post_type_choices[ $pt->name ] = $pt->labels->singular_name ?: $pt->name;
    }
}
if ( empty( $sc_post_type_choices ) ) {
    $sc_post_type_choices = [ 'post' => __( 'Post', 'fw' ), 'page' => __( 'Page', 'fw' ) ];
}

/*
|--------------------------------------------------------------------------
| Image-picker choices — Layout mode, Card style (from registry), Pagination.
| SVG thumbnails live under static/img/{layout,card,pagination}/.
|--------------------------------------------------------------------------
*/
$posts_uri = fw_ext( 'shortcodes' )->get_declared_URI( '/shortcodes/posts' );

$sc_img_choice = function ( $src, $label ) {
    // `title` rides through data-small-img-attr → the rendered <img> gets a native
    // hover tooltip showing the design name (the image-picker lib copies every
    // small-img attr onto the image).
    return [ 'small' => [ 'src' => $src, 'alt' => $label, 'title' => $label ], 'label' => $label ];
};

/* Layout modes */
$sc_layout_labels = [
    'grid'    => __( 'Grid', 'fw' ),
    'list'    => __( 'List', 'fw' ),
    'masonry' => __( 'Masonry', 'fw' ),
    'slider'  => __( 'Slider', 'fw' ),
];
$sc_layout_choices = [];
foreach ( $sc_layout_labels as $k => $lbl ) {
    $sc_layout_choices[ $k ] = $sc_img_choice( $posts_uri . '/static/img/layout/' . $k . '.svg', $lbl );
}

/* Card styles — from the registry (single source of truth) */
$sc_card_registry = require dirname( __FILE__ ) . '/views/parts/registry.php';
$sc_card_choices  = [];
foreach ( $sc_card_registry as $k => $def ) {
    $sc_card_choices[ $k ] = $sc_img_choice( $posts_uri . '/static/img/card/' . $def['thumb'], $def['label'] );
}

/* Pagination types */
$sc_pag_labels = [
    'none'          => __( 'None', 'fw' ),
    'numeric'       => __( 'Numeric', 'fw' ),
    'prev_next'     => __( 'Prev / Next', 'fw' ),
    'ajax_loadmore' => __( 'Load More', 'fw' ),
    'infinite'      => __( 'Infinite', 'fw' ),
];
$sc_pag_choices = [];
foreach ( $sc_pag_labels as $k => $lbl ) {
    $sc_pag_choices[ $k ] = $sc_img_choice( $posts_uri . '/static/img/pagination/' . $k . '.svg', $lbl );
}

/*
|--------------------------------------------------------------------------
| Reusable sub-option fragments (revealed inside the pickers)
|--------------------------------------------------------------------------
*/
$sc_opt_cols_d = [
    'label'   => __( 'Desktop Columns', 'fw' ),
    'type'    => 'select', 'value' => '3',
    'choices' => [ '1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6' ],
    'help'    => __( 'Cards per row on large screens.', 'fw' ),
];
$sc_opt_cols_t = [
    'label'   => __( 'Tablet Columns', 'fw' ),
    'type'    => 'select', 'value' => '2',
    'choices' => [ '1' => '1', '2' => '2', '3' => '3', '4' => '4' ],
];
$sc_opt_cols_m = [
    'label'   => __( 'Mobile Columns', 'fw' ),
    'type'    => 'select', 'value' => '1',
    'choices' => [ '1' => '1', '2' => '2' ],
];
/* Gaps use the theme Gap Scale presets (Theme Settings → spacing/gap presets),
   consistent with the Section/Row/Column containers. Empty = the base default.
   Legacy numeric (px) saves still resolve in view.php for back-compat. */
$sc_gap_choices = function_exists( 'sc_get_gap_select_choices' )
    ? sc_get_gap_select_choices( __( 'Use Default Gap', 'fw' ) )
    : [ '' => __( 'Use Default Gap', 'fw' ) ];
$sc_opt_col_gap = [ 'label' => __( 'Column Gap', 'fw' ), 'type' => 'select', 'value' => '', 'choices' => $sc_gap_choices ];
$sc_opt_row_gap = [ 'label' => __( 'Row Gap', 'fw' ),    'type' => 'select', 'value' => '', 'choices' => $sc_gap_choices ];
$sc_opt_equal_height = [
    'label' => __( 'Equal Card Heights', 'fw' ),
    'type'  => 'switch', 'value' => 'yes',
    'desc'  => __( 'Force cards in a row to match height.', 'fw' ),
];
$sc_opt_featured = [
    'label'   => __( 'Featured Post Treatment', 'fw' ),
    'type'    => 'select', 'value' => 'none',
    'choices' => [
        'none'            => __( 'None — all cards equal', 'fw' ),
        'first-post-2x'   => __( 'First post spans 2 columns', 'fw' ),
        'first-post-hero' => __( 'First post uses hero (overlay) style', 'fw' ),
    ],
    'desc' => __( 'Special handling for the first post in the result set.', 'fw' ),
];

/* Side-layout sub-controls (revealed for side / alternating / hero card styles) */
$sc_opt_img_ratio_split = [
    'label'   => __( 'Image / Content Width Ratio', 'fw' ),
    'type'    => 'select', 'value' => '40-60',
    'choices' => [ '30-70' => __( '30% / 70%', 'fw' ), '40-60' => __( '40% / 60%', 'fw' ), '50-50' => __( '50% / 50%', 'fw' ), '60-40' => __( '60% / 40%', 'fw' ) ],
];
$sc_opt_img_valign = [
    'label'   => __( 'Image Vertical Align', 'fw' ),
    'type'    => 'select', 'value' => 'stretch',
    'choices' => [ 'top' => __( 'Top', 'fw' ), 'center' => __( 'Center', 'fw' ), 'stretch' => __( 'Stretch', 'fw' ) ],
];
$sc_opt_content_valign = [
    'label'   => __( 'Content Vertical Align', 'fw' ),
    'type'    => 'select', 'value' => 'top',
    'choices' => [ 'top' => __( 'Top', 'fw' ), 'center' => __( 'Center', 'fw' ), 'bottom' => __( 'Bottom', 'fw' ), 'space-between' => __( 'Justify', 'fw' ) ],
];

/* Slider sub-controls (revealed for the Slider layout) */
$sc_opt_slider_arrows = [
    'label'   => __( 'Arrow Position', 'fw' ),
    'type'    => 'select', 'value' => 'outside',
    'choices' => [ 'inside' => __( 'Inside', 'fw' ), 'outside' => __( 'Outside', 'fw' ), 'above' => __( 'Above', 'fw' ), 'hidden' => __( 'Hidden', 'fw' ) ],
];
$sc_opt_slider_dots = [
    'label'   => __( 'Dots Position', 'fw' ),
    'type'    => 'select', 'value' => 'below',
    'choices' => [ 'below' => __( 'Below', 'fw' ), 'overlay-bottom' => __( 'Overlay', 'fw' ), 'hidden' => __( 'Hidden', 'fw' ) ],
];
$sc_opt_slider_autoplay = [ 'label' => __( 'Autoplay', 'fw' ), 'type' => 'switch', 'value' => 'no' ];
$sc_opt_slider_interval = [ 'label' => __( 'Autoplay Interval (ms)', 'fw' ), 'type' => 'short-text', 'value' => '5000' ];
$sc_opt_slider_loop     = [ 'label' => __( 'Loop', 'fw' ), 'type' => 'switch', 'value' => 'yes' ];

/* Pagination sub-controls */
$sc_opt_pag_position = [
    'label'   => __( 'Position', 'fw' ),
    'type'    => 'select', 'value' => 'below-grid',
    'choices' => [ 'below-grid' => __( 'Below grid', 'fw' ), 'above-grid' => __( 'Above grid', 'fw' ), 'both' => __( 'Both', 'fw' ) ],
];
$sc_opt_pag_align = [
    'label'   => __( 'Alignment', 'fw' ),
    'type'    => 'select', 'value' => 'center',
    'choices' => [ 'left' => __( 'Left', 'fw' ), 'center' => __( 'Center', 'fw' ), 'right' => __( 'Right', 'fw' ) ],
];

/* Card-style picker choices: only the styles that need the ratio/valign controls
   reveal sub-options (side / alternating reveal all three; hero reveals ratio). */
$sc_card_picker_choices = [];
foreach ( $sc_card_registry as $k => $def ) {
    if ( empty( $def['needs_ratio'] ) ) {
        continue;
    }
    if ( $k === 'hero-split' ) {
        $sc_card_picker_choices[ $k ] = [ 'image_width_ratio' => $sc_opt_img_ratio_split ];
    } else {
        $sc_card_picker_choices[ $k ] = [
            'image_width_ratio'      => $sc_opt_img_ratio_split,
            'image_vertical_align'   => $sc_opt_img_valign,
            'content_vertical_align' => $sc_opt_content_valign,
        ];
    }
}

$options = [

    /* ==========================================================
       TAB 1 — QUERY
       ========================================================== */
    'tab_query' => [
        'title'   => __( 'Query', 'fw' ),
        'type'    => 'tab',
        'options' => [
            'group_source' => [
                'type'    => 'group',
                'options' => [
                    'post_type' => [
                        'label'   => __( 'Post Type', 'fw' ),
                        'desc'    => __( 'Source content type.', 'fw' ),
                        'help'    => __( 'Lists every public post type registered on the site.', 'fw' ),
                        'type'    => 'select',
                        'value'   => 'post',
                        'choices' => $sc_post_type_choices,
                    ],
                    'taxonomy_filter' => [
                        'label' => __( 'Filter by Taxonomy / Terms', 'fw' ),
                        'desc'  => __( 'Format: taxonomy:term-slug,term-slug. e.g. category:news,tech. Leave empty for none.', 'fw' ),
                        'type'  => 'text',
                        'value' => '',
                    ],
                    'taxonomy_relation' => [
                        'label'   => __( 'Term Relation', 'fw' ),
                        'type'    => 'radio',
                        'value'   => 'IN',
                        'choices' => [
                            'IN'     => __( 'Match Any Term (OR)', 'fw' ),
                            'AND'    => __( 'Match All Terms (AND)', 'fw' ),
                            'NOT IN' => __( 'Exclude These Terms', 'fw' ),
                        ],
                    ],
                    'include_ids' => [
                        'label' => __( 'Include Only These IDs', 'fw' ),
                        'desc'  => __( 'Comma-separated post IDs (overrides taxonomy filter).', 'fw' ),
                        'type'  => 'text',
                        'value' => '',
                    ],
                    'exclude_ids' => [
                        'label' => __( 'Exclude These IDs', 'fw' ),
                        'desc'  => __( 'Comma-separated post IDs to hide.', 'fw' ),
                        'type'  => 'text',
                        'value' => '',
                    ],
                    'author_ids' => [
                        'label' => __( 'Limit to Authors', 'fw' ),
                        'desc'  => __( 'Comma-separated user IDs. Empty = all authors.', 'fw' ),
                        'type'  => 'text',
                        'value' => '',
                    ],
                ],
            ],
            'group_ordering' => [
                'type'    => 'group',
                'options' => [
                    'date_range' => [
                        'label'   => __( 'Date Range', 'fw' ),
                        'type'    => 'select',
                        'value'   => 'any',
                        'choices' => [
                            'any'       => __( 'Any time', 'fw' ),
                            'last_7'    => __( 'Last 7 days', 'fw' ),
                            'last_30'   => __( 'Last 30 days', 'fw' ),
                            'last_90'   => __( 'Last 90 days', 'fw' ),
                            'this_year' => __( 'This year', 'fw' ),
                        ],
                    ],
                    'posts_per_page' => [
                        'label' => __( 'Posts per Page', 'fw' ),
                        'desc'  => __( '-1 shows all matching posts.', 'fw' ),
                        'type'  => 'short-text',
                        'value' => '6',
                    ],
                    'offset' => [
                        'label' => __( 'Offset', 'fw' ),
                        'desc'  => __( 'Skip N posts from the start of the query.', 'fw' ),
                        'type'  => 'short-text',
                        'value' => '0',
                    ],
                    'orderby' => [
                        'label'   => __( 'Order By', 'fw' ),
                        'type'    => 'select',
                        'value'   => 'date',
                        'choices' => [
                            'date'           => __( 'Published date', 'fw' ),
                            'modified'       => __( 'Modified date', 'fw' ),
                            'title'          => __( 'Title (alphabetical)', 'fw' ),
                            'rand'           => __( 'Random', 'fw' ),
                            'comment_count'  => __( 'Comment count', 'fw' ),
                            'menu_order'     => __( 'Menu order', 'fw' ),
                            'meta_value_num' => __( 'Custom field (numeric)', 'fw' ),
                        ],
                    ],
                    'meta_key' => [
                        'label' => __( 'Custom Field Key', 'fw' ),
                        'desc'  => __( 'Required when Order By = Custom field (numeric).', 'fw' ),
                        'type'  => 'text',
                        'value' => '',
                    ],
                    'order' => [
                        'label'   => __( 'Order Direction', 'fw' ),
                        'type'    => 'radio',
                        'value'   => 'DESC',
                        'choices' => [ 'DESC' => __( 'Descending', 'fw' ), 'ASC' => __( 'Ascending', 'fw' ) ],
                    ],
                    'exclude_current' => [
                        'label' => __( 'Exclude Current Post', 'fw' ),
                        'desc'  => __( 'Useful for "Related Posts".', 'fw' ),
                        'type'  => 'switch',
                        'value' => 'yes',
                    ],
                    'sticky_handling' => [
                        'label'   => __( 'Sticky Post Handling', 'fw' ),
                        'type'    => 'select',
                        'value'   => 'default',
                        'choices' => [
                            'default' => __( 'Default WordPress behaviour', 'fw' ),
                            'pin_top' => __( 'Pin sticky posts to the top', 'fw' ),
                            'ignore'  => __( 'Ignore sticky status', 'fw' ),
                            'only'    => __( 'Show only sticky posts', 'fw' ),
                        ],
                    ],
                ],
            ],
        ],
    ],

    /* ==========================================================
       TAB 2 — DESIGN (layout mode + card style, both image-pickers)
       ========================================================== */
    'tab_design' => [
        'title'   => __( 'Design', 'fw' ),
        'type'    => 'tab',
        'options' => [
            'group_design' => [
                'type'    => 'group',
                'options' => [
                    'card' => [
                        'type'         => 'multi-picker',
                        'label'        => false,
                        'desc'         => false,
                        'show_borders' => false,
                        'picker'       => [
                            'style' => [
                                'label'   => __( 'Card Style', 'fw' ),
                                'type'    => 'image-picker',
                                'choices' => $sc_card_choices,
                                'desc'    => __( 'How each post card looks. Works with any Layout below. Side / Hero styles reveal extra image controls.', 'fw' ),
                            ],
                        ],
                        'value'        => [ 'style' => 'standard' ],
                        'choices'      => $sc_card_picker_choices,
                    ],
                    'design' => [
                        'type'         => 'multi-picker',
                        'label'        => false,
                        'desc'         => false,
                        'show_borders' => false,
                        'picker'       => [
                            'mode' => [
                                'label'   => __( 'Layout', 'fw' ),
                                'type'    => 'image-picker',
                                'choices' => $sc_layout_choices,
                                'desc'    => __( 'How the cards are arranged on the page. Only the chosen layout’s options appear below.', 'fw' ),
                            ],
                        ],
                        'value'        => [ 'mode' => 'grid' ],
                        'choices'      => [
                            'grid' => [
                                'columns_desktop'    => $sc_opt_cols_d,
                                'columns_tablet'     => $sc_opt_cols_t,
                                'columns_mobile'     => $sc_opt_cols_m,
                                'column_gap'         => $sc_opt_col_gap,
                                'row_gap'            => $sc_opt_row_gap,
                                'equal_height'       => $sc_opt_equal_height,
                                'featured_treatment' => $sc_opt_featured,
                            ],
                            'masonry' => [
                                'columns_desktop' => $sc_opt_cols_d,
                                'columns_tablet'  => $sc_opt_cols_t,
                                'columns_mobile'  => $sc_opt_cols_m,
                                'column_gap'      => $sc_opt_col_gap,
                                'row_gap'         => $sc_opt_row_gap,
                            ],
                            'list' => [
                                'row_gap' => $sc_opt_row_gap,
                            ],
                            'slider' => [
                                'slider_arrows_position' => $sc_opt_slider_arrows,
                                'slider_dots_position'   => $sc_opt_slider_dots,
                                'slider_autoplay'        => $sc_opt_slider_autoplay,
                                'slider_interval'        => $sc_opt_slider_interval,
                                'slider_loop'            => $sc_opt_slider_loop,
                            ],
                        ],
                    ],
                ],
            ],
            'group_appearance' => [
                'type'    => 'group',
                'options' => [
                    'image_size' => [
                        'label'   => __( 'Image Size', 'fw' ),
                        'type'    => 'select',
                        'value'   => 'medium_large',
                        'choices' => [
                            'thumbnail'    => __( 'Thumbnail', 'fw' ),
                            'medium'       => __( 'Medium', 'fw' ),
                            'medium_large' => __( 'Medium Large', 'fw' ),
                            'large'        => __( 'Large', 'fw' ),
                            'full'         => __( 'Full', 'fw' ),
                        ],
                    ],
                    'image_ratio' => [
                        'label'   => __( 'Image Crop Ratio', 'fw' ),
                        'type'    => 'select',
                        'value'   => 'ratio-16-9',
                        'choices' => [
                            'ratio-16-9' => __( 'Widescreen 16:9', 'fw' ),
                            'ratio-4-3'  => __( 'Standard 4:3', 'fw' ),
                            'ratio-3-2'  => __( 'Classic 3:2', 'fw' ),
                            'ratio-1-1'  => __( 'Square 1:1', 'fw' ),
                            'ratio-2-3'  => __( 'Portrait 2:3', 'fw' ),
                            'ratio-auto' => __( 'Original aspect', 'fw' ),
                        ],
                    ],
                    'fallback_image_url' => [
                        'label' => __( 'Fallback Image URL', 'fw' ),
                        'desc'  => __( 'Used when a post has no featured image. Empty = hide image on those cards.', 'fw' ),
                        'type'  => 'text',
                        'value' => '',
                    ],
                    'card_padding' => [
                        'label'   => __( 'Card Padding (Density)', 'fw' ),
                        'type'    => 'select',
                        'value'   => 'regular',
                        'choices' => [
                            'none'     => __( 'None (edge-to-edge)', 'fw' ),
                            'compact'  => __( 'Compact', 'fw' ),
                            'regular'  => __( 'Regular', 'fw' ),
                            'spacious' => __( 'Spacious', 'fw' ),
                        ],
                    ],
                    'text_align' => [
                        'label'   => __( 'Card Text Alignment', 'fw' ),
                        'type'    => 'select',
                        'value'   => 'left',
                        'choices' => [ 'left' => __( 'Left', 'fw' ), 'center' => __( 'Center', 'fw' ), 'right' => __( 'Right', 'fw' ) ],
                    ],
                    'mobile_layout_override' => [
                        'label'   => __( 'Mobile Card Style Override', 'fw' ),
                        'desc'    => __( 'Switch card style on mobile (≤ 782px) regardless of desktop choice.', 'fw' ),
                        'type'    => 'select',
                        'value'   => 'inherit',
                        'choices' => [
                            'inherit'   => __( 'Inherit from desktop', 'fw' ),
                            'standard'  => __( 'Standard (image top)', 'fw' ),
                            'side-left' => __( 'Side — image left', 'fw' ),
                            'minimal'   => __( 'Minimal (no image)', 'fw' ),
                        ],
                    ],
                ],
            ],
        ],
    ],

    /* ==========================================================
       TAB 3 — ELEMENTS
       ========================================================== */
    'tab_elements' => [
        'title'   => __( 'Elements', 'fw' ),
        'type'    => 'tab',
        'options' => [
            'group_order' => [
                'type'    => 'group',
                'options' => [
                    'element_order' => [
                        'label'    => __( 'Element Order (drag to reorder)', 'fw' ),
                        'desc'     => __( 'Reorder the blocks inside each card. Toggle Visible off to hide a block. Image position inside side/overlay cards is governed by Card Style, not this list.', 'fw' ),
                        'type'     => 'addable-box',
                        'template' => '{{- slug }}',
                        'limit'    => 6,
                        'sortable' => true,
                        'value'    => [
                            [ 'slug' => 'image',    'enabled' => 'yes' ],
                            [ 'slug' => 'cats',     'enabled' => 'yes' ],
                            [ 'slug' => 'title',    'enabled' => 'yes' ],
                            [ 'slug' => 'meta',     'enabled' => 'yes' ],
                            [ 'slug' => 'excerpt',  'enabled' => 'yes' ],
                            [ 'slug' => 'readmore', 'enabled' => 'yes' ],
                        ],
                        'box-options' => [
                            'slug' => [
                                'label'   => __( 'Block', 'fw' ),
                                'type'    => 'select',
                                'value'   => 'title',
                                'choices' => [
                                    'image'    => __( 'Featured image', 'fw' ),
                                    'cats'     => __( 'Categories / taxonomy chips', 'fw' ),
                                    'title'    => __( 'Title', 'fw' ),
                                    'meta'     => __( 'Meta bar (author / date / comments)', 'fw' ),
                                    'excerpt'  => __( 'Excerpt', 'fw' ),
                                    'readmore' => __( 'Read-more link', 'fw' ),
                                ],
                            ],
                            'enabled' => [
                                'label' => __( 'Visible', 'fw' ),
                                'type'  => 'switch',
                                'value' => 'yes',
                            ],
                        ],
                    ],
                ],
            ],
            'group_title' => [
                'type'    => 'group',
                'options' => [
                    'title_tag' => [
                        'label'   => __( 'Title HTML Tag', 'fw' ),
                        'type'    => 'select',
                        'value'   => 'h3',
                        'choices' => [ 'h2' => 'H2', 'h3' => 'H3', 'h4' => 'H4', 'h5' => 'H5', 'div' => 'div' ],
                    ],
                ],
            ],
            'group_categories' => [
                'type'    => 'group',
                'options' => [
                    'cat_position' => [
                        'label'   => __( 'Categories Position', 'fw' ),
                        'type'    => 'select',
                        'value'   => 'above-title',
                        'choices' => [
                            'above-title'               => __( 'Above title (default)', 'fw' ),
                            'below-title'               => __( 'Below title', 'fw' ),
                            'in-meta'                   => __( 'Inside meta bar', 'fw' ),
                            'image-overlay-top-left'    => __( 'Image overlay — top left', 'fw' ),
                            'image-overlay-top-right'   => __( 'Image overlay — top right', 'fw' ),
                            'image-overlay-bottom-left' => __( 'Image overlay — bottom left', 'fw' ),
                            'image-overlay-bottom-right'=> __( 'Image overlay — bottom right', 'fw' ),
                        ],
                    ],
                    'cat_taxonomy' => [
                        'label' => __( 'Taxonomy Slug for Chips', 'fw' ),
                        'desc'  => __( 'Default: category. e.g. post_tag or a custom taxonomy.', 'fw' ),
                        'type'  => 'text',
                        'value' => 'category',
                    ],
                    'cat_max' => [
                        'label' => __( 'Max Chips Shown', 'fw' ),
                        'type'  => 'short-text',
                        'value' => '2',
                    ],
                ],
            ],
            'group_meta' => [
                'type'    => 'group',
                'options' => [
                    'meta_items' => [
                        'label'   => __( 'Meta Bar Items', 'fw' ),
                        'type'    => 'checkboxes',
                        'value'   => [ 'date' => true, 'author' => true ],
                        'choices' => [
                            'date'         => __( 'Date', 'fw' ),
                            'author'       => __( 'Author', 'fw' ),
                            'comments'     => __( 'Comment count', 'fw' ),
                            'reading_time' => __( 'Reading time (estimated)', 'fw' ),
                        ],
                    ],
                    'meta_layout' => [
                        'label'   => __( 'Meta Bar Layout', 'fw' ),
                        'type'    => 'select',
                        'value'   => 'inline-dot',
                        'choices' => [
                            'inline-dot'   => __( 'Inline · dot separator', 'fw' ),
                            'inline-pipe'  => __( 'Inline | pipe separator', 'fw' ),
                            'inline-icons' => __( 'Inline with icons', 'fw' ),
                            'stacked'      => __( 'Stacked (one per line)', 'fw' ),
                        ],
                    ],
                    'date_format' => [
                        'label'   => __( 'Date Format', 'fw' ),
                        'type'    => 'select',
                        'value'   => 'wp',
                        'choices' => [
                            'wp'       => __( 'WordPress default', 'fw' ),
                            'relative' => __( 'Relative (e.g. "2 days ago")', 'fw' ),
                            'long'     => __( 'Long (e.g. "March 5, 2026")', 'fw' ),
                            'short'    => __( 'Short (e.g. "05/03/2026")', 'fw' ),
                        ],
                    ],
                ],
            ],
            'group_excerpt' => [
                'type'    => 'group',
                'options' => [
                    'excerpt_source' => [
                        'label'   => __( 'Excerpt Source', 'fw' ),
                        'type'    => 'select',
                        'value'   => 'auto',
                        'choices' => [
                            'auto'    => __( 'Auto (excerpt or trimmed content)', 'fw' ),
                            'excerpt' => __( 'Manual excerpt only', 'fw' ),
                            'content' => __( 'Trimmed post content', 'fw' ),
                        ],
                    ],
                    'excerpt_length' => [
                        'label' => __( 'Excerpt Length (words)', 'fw' ),
                        'type'  => 'short-text',
                        'value' => '25',
                    ],
                    'excerpt_suffix' => [
                        'label' => __( 'Excerpt Suffix', 'fw' ),
                        'type'  => 'short-text',
                        'value' => '…',
                    ],
                ],
            ],
            'group_readmore' => [
                'type'    => 'group',
                'options' => [
                    'readmore' => [
                        'type'         => 'multi-picker',
                        'label'        => false,
                        'desc'         => false,
                        'show_borders' => false,
                        'picker'       => [
                            'style' => [
                                'label'   => __( 'Read-More Style', 'fw' ),
                                'type'    => 'select',
                                'choices' => [
                                    'button'     => __( 'Button', 'fw' ),
                                    'text-link'  => __( 'Text link', 'fw' ),
                                    'arrow-only' => __( 'Arrow only (→)', 'fw' ),
                                ],
                                'desc' => __( 'Button reveals color + size controls below.', 'fw' ),
                            ],
                        ],
                        'value'        => [ 'style' => 'text-link' ],
                        'choices'      => [
                            'button' => [
                                'readmore_btn_style' => [
                                    'label'   => __( 'Button Color', 'fw' ),
                                    'type'    => 'select',
                                    'value'   => '',
                                    'choices' => function_exists( 'sc_get_button_style_choices' ) ? sc_get_button_style_choices() : [ '' => __( 'Default', 'fw' ) ],
                                    'desc'    => __( 'Reuses your theme button color presets.', 'fw' ),
                                ],
                                'readmore_btn_size' => [
                                    'label'   => __( 'Button Size', 'fw' ),
                                    'type'    => 'select',
                                    'value'   => '',
                                    'choices' => function_exists( 'sc_get_button_size_choices' ) ? sc_get_button_size_choices() : [ '' => __( 'Normal', 'fw' ) ],
                                ],
                            ],
                        ],
                    ],
                    'readmore_text' => [
                        'label' => __( 'Read-More Text', 'fw' ),
                        'desc'  => __( 'Ignored when the Arrow-only style is selected.', 'fw' ),
                        'type'  => 'text',
                        'value' => __( 'Read more', 'fw' ),
                    ],
                ],
            ],
        ],
    ],

    /* ==========================================================
       TAB 4 — NAVIGATION & CACHE
       ========================================================== */
    'tab_interactivity' => [
        'title'   => __( 'Navigation & Cache', 'fw' ),
        'type'    => 'tab',
        'options' => [
            'group_pagination' => [
                'type'    => 'group',
                'options' => [
                    'pagination' => [
                        'type'         => 'multi-picker',
                        'label'        => false,
                        'desc'         => false,
                        'show_borders' => false,
                        'picker'       => [
                            'type' => [
                                'label'   => __( 'Pagination Type', 'fw' ),
                                'type'    => 'image-picker',
                                'choices' => $sc_pag_choices,
                                'desc'    => __( 'How results split into pages. Only the chosen type’s options appear below.', 'fw' ),
                            ],
                        ],
                        'value'        => [ 'type' => 'none' ],
                        'choices'      => [
                            'numeric' => [
                                'pagination_position' => $sc_opt_pag_position,
                                'pagination_align'    => $sc_opt_pag_align,
                            ],
                            'prev_next' => [
                                'pagination_position' => $sc_opt_pag_position,
                                'pagination_align'    => $sc_opt_pag_align,
                            ],
                            'ajax_loadmore' => [
                                'pagination_align' => $sc_opt_pag_align,
                            ],
                        ],
                    ],
                ],
            ],
            'group_filters' => [
                'type'    => 'group',
                'options' => [
                    'live_filters' => [
                        'label' => __( 'AJAX Filter Bar', 'fw' ),
                        'desc'  => __( 'Show category chips that filter posts without a page reload.', 'fw' ),
                        'type'  => 'switch',
                        'value' => 'no',
                    ],
                    'filters_position' => [
                        'label'   => __( 'Filter Bar Position', 'fw' ),
                        'type'    => 'select',
                        'value'   => 'above-grid',
                        'choices' => [
                            'above-grid'    => __( 'Above grid', 'fw' ),
                            'left-sidebar'  => __( 'Left sidebar', 'fw' ),
                            'right-sidebar' => __( 'Right sidebar', 'fw' ),
                        ],
                    ],
                ],
            ],
            'group_cache' => [
                'type'    => 'group',
                'options' => [
                    'cache_output' => [
                        'label' => __( 'Enable Transient Caching', 'fw' ),
                        'desc'  => __( 'Cache rendered HTML. Auto-flushed when any post is saved. Avoid with Random order or AJAX pagination.', 'fw' ),
                        'type'  => 'switch',
                        'value' => 'no',
                    ],
                    'cache_hours' => [
                        'label'   => __( 'Cache Lifespan', 'fw' ),
                        'type'    => 'select',
                        'value'   => '12',
                        'choices' => [
                            '1'  => __( '1 hour', 'fw' ),
                            '6'  => __( '6 hours', 'fw' ),
                            '12' => __( '12 hours', 'fw' ),
                            '24' => __( '24 hours (1 day)', 'fw' ),
                        ],
                    ],
                ],
            ],
            'group_empty' => [
                'type'    => 'group',
                'options' => [
                    'no_results_text' => [
                        'label' => __( 'Empty-State Message', 'fw' ),
                        'type'  => 'text',
                        'value' => __( 'Sorry, no posts matched your criteria.', 'fw' ),
                    ],
                ],
            ],
        ],
    ],

    /* ==========================================================
       TAB 5 — STYLING
       ========================================================== */
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
