<?php if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}

/*
|--------------------------------------------------------------------------
| Dynamically populate post-type and taxonomy choices
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

$options = [

    /* ==========================================
       TAB 1 — QUERY
       ========================================== */
    'tab_query' => [
        'title'   => __( 'Query', 'fw' ),
        'type'    => 'tab',
        'options' => [

            'post_type' => [
                'label'   => __( 'Post Type', 'fw' ),
                'desc'    => __( 'Source content type.', 'fw' ),
                'type'    => 'select',
                'value'   => 'post',
                'choices' => $sc_post_type_choices,
            ],

            'taxonomy_filter' => [
                'label' => __( 'Filter by Taxonomy / Terms', 'fw' ),
                'desc'  => __( 'Format: taxonomy:term-slug,term-slug. Examples: category:news,tech  or  post_tag:featured. Leave empty for none.', 'fw' ),
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
                'desc'  => __( 'Comma-separated post IDs to cherry-pick (overrides taxonomy filter).', 'fw' ),
                'type'  => 'text',
                'value' => '',
            ],

            'exclude_ids' => [
                'label' => __( 'Exclude These IDs', 'fw' ),
                'desc'  => __( 'Comma-separated post IDs to hide from the query.', 'fw' ),
                'type'  => 'text',
                'value' => '',
            ],

            'author_ids' => [
                'label' => __( 'Limit to Authors', 'fw' ),
                'desc'  => __( 'Comma-separated user IDs. Empty = all authors.', 'fw' ),
                'type'  => 'text',
                'value' => '',
            ],

            'date_range' => [
                'label'   => __( 'Date Range', 'fw' ),
                'type'    => 'select',
                'value'   => 'any',
                'choices' => [
                    'any'         => __( 'Any time', 'fw' ),
                    'last_7'      => __( 'Last 7 days', 'fw' ),
                    'last_30'     => __( 'Last 30 days', 'fw' ),
                    'last_90'     => __( 'Last 90 days', 'fw' ),
                    'this_year'   => __( 'This year', 'fw' ),
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
                'desc'  => __( 'Required when Order By = Custom field (numeric). E.g. views_count.', 'fw' ),
                'type'  => 'text',
                'value' => '',
            ],

            'order' => [
                'label'   => __( 'Order Direction', 'fw' ),
                'type'    => 'radio',
                'value'   => 'DESC',
                'choices' => [
                    'DESC' => __( 'Descending', 'fw' ),
                    'ASC'  => __( 'Ascending', 'fw' ),
                ],
            ],

            'exclude_current' => [
                'label' => __( 'Exclude Current Post', 'fw' ),
                'desc'  => __( 'Prevent the post you are viewing from appearing in its own grid (useful for "Related Posts").', 'fw' ),
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

    /* ==========================================
       TAB 2 — LAYOUT & POSITIONING  (the new tab)
       ========================================== */
    'tab_layout' => [
        'title'   => __( 'Layout & Positioning', 'fw' ),
        'type'    => 'tab',
        'options' => [

            'layout_mode' => [
                'label'   => __( 'Layout Mode', 'fw' ),
                'desc'    => __( 'Top-level navigation/flow. Card visual style is set separately below.', 'fw' ),
                'type'    => 'select',
                'value'   => 'grid',
                'choices' => [
                    'grid'    => __( 'Grid (CSS Grid)', 'fw' ),
                    'list'    => __( 'List (one per row)', 'fw' ),
                    'masonry' => __( 'Masonry (fluid heights)', 'fw' ),
                    'slider'  => __( 'Slider / Carousel', 'fw' ),
                ],
            ],

            'card_style' => [
                'label'   => __( 'Card Style', 'fw' ),
                'desc'    => __( 'Positional layout of elements inside each card.', 'fw' ),
                'type'    => 'select',
                'value'   => 'standard',
                'choices' => [
                    'standard'    => __( 'Standard — image top, content below', 'fw' ),
                    'side-left'   => __( 'Side — image left, content right', 'fw' ),
                    'side-right'  => __( 'Side — image right, content left', 'fw' ),
                    'overlay'     => __( 'Overlay — content over image', 'fw' ),
                    'minimal'     => __( 'Minimal — no image, text only', 'fw' ),
                    'hero-split'  => __( 'Hero split — first post 2× with overlay', 'fw' ),
                    'alternating' => __( 'Alternating — odd left, even right (zig-zag)', 'fw' ),
                ],
            ],

            /* ---- Side-layout sub-controls ---- */
            'image_width_ratio' => [
                'label'   => __( 'Image / Content Width Ratio (side layouts)', 'fw' ),
                'desc'    => __( 'Applies only when card style is Side, Alternating, or first hero card.', 'fw' ),
                'type'    => 'select',
                'value'   => '40-60',
                'choices' => [
                    '30-70' => __( '30% image / 70% content', 'fw' ),
                    '40-60' => __( '40% / 60%', 'fw' ),
                    '50-50' => __( '50% / 50%', 'fw' ),
                    '60-40' => __( '60% / 40%', 'fw' ),
                ],
            ],

            'image_vertical_align' => [
                'label'   => __( 'Image Vertical Align (side layouts)', 'fw' ),
                'type'    => 'select',
                'value'   => 'stretch',
                'choices' => [
                    'top'     => __( 'Top', 'fw' ),
                    'center'  => __( 'Center', 'fw' ),
                    'stretch' => __( 'Stretch to match content height', 'fw' ),
                ],
            ],

            'content_vertical_align' => [
                'label'   => __( 'Content Vertical Align (side layouts)', 'fw' ),
                'type'    => 'select',
                'value'   => 'top',
                'choices' => [
                    'top'           => __( 'Top', 'fw' ),
                    'center'        => __( 'Center', 'fw' ),
                    'bottom'        => __( 'Bottom', 'fw' ),
                    'space-between' => __( 'Justify (push read-more to bottom)', 'fw' ),
                ],
            ],

            /* ---- Grid columns per breakpoint ---- */
            'columns_desktop' => [
                'label'   => __( 'Desktop Columns', 'fw' ),
                'type'    => 'select',
                'value'   => '3',
                'choices' => [ '1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6' ],
            ],
            'columns_tablet' => [
                'label'   => __( 'Tablet Columns', 'fw' ),
                'type'    => 'select',
                'value'   => '2',
                'choices' => [ '1' => '1', '2' => '2', '3' => '3', '4' => '4' ],
            ],
            'columns_mobile' => [
                'label'   => __( 'Mobile Columns', 'fw' ),
                'type'    => 'select',
                'value'   => '1',
                'choices' => [ '1' => '1', '2' => '2' ],
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

            /* ---- Gap & density ---- */
            'column_gap' => [
                'label'      => __( 'Column Gap (px)', 'fw' ),
                'type'       => 'short-text',
                'value'      => '24',
            ],
            'row_gap' => [
                'label'      => __( 'Row Gap (px)', 'fw' ),
                'type'       => 'short-text',
                'value'      => '32',
            ],
            'card_padding' => [
                'label'   => __( 'Card Padding (Density)', 'fw' ),
                'type'    => 'select',
                'value'   => 'regular',
                'choices' => [
                    'none'     => __( 'None (text edge-to-edge)', 'fw' ),
                    'compact'  => __( 'Compact', 'fw' ),
                    'regular'  => __( 'Regular', 'fw' ),
                    'spacious' => __( 'Spacious', 'fw' ),
                ],
            ],
            'equal_height' => [
                'label' => __( 'Equal Card Heights', 'fw' ),
                'desc'  => __( 'Force cards in a row to match height.', 'fw' ),
                'type'  => 'switch',
                'value' => 'yes',
            ],

            /* ---- Image ---- */
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
                'desc'  => __( 'Used when a post has no featured image. Leave empty to hide image entirely on those cards.', 'fw' ),
                'type'  => 'text',
                'value' => '',
            ],

            /* ---- Featured / first-post treatment ---- */
            'featured_treatment' => [
                'label'   => __( 'Featured Post Treatment', 'fw' ),
                'desc'    => __( 'Special handling for the first post in the result set.', 'fw' ),
                'type'    => 'select',
                'value'   => 'none',
                'choices' => [
                    'none'           => __( 'None — all cards equal', 'fw' ),
                    'first-post-2x'  => __( 'First post spans 2 columns', 'fw' ),
                    'first-post-hero'=> __( 'First post uses hero (overlay) style', 'fw' ),
                ],
            ],

            'text_align' => [
                'label'   => __( 'Card Text Alignment', 'fw' ),
                'type'    => 'select',
                'value'   => 'left',
                'choices' => [
                    'left'   => __( 'Left', 'fw' ),
                    'center' => __( 'Center', 'fw' ),
                    'right'  => __( 'Right', 'fw' ),
                ],
            ],
        ],
    ],

    /* ==========================================
       TAB 3 — ELEMENTS (visibility, order, per-element positioning)
       ========================================== */
    'tab_elements' => [
        'title'   => __( 'Elements', 'fw' ),
        'type'    => 'tab',
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

            /* ---- Title ---- */
            'title_tag' => [
                'label'   => __( 'Title HTML Tag', 'fw' ),
                'type'    => 'select',
                'value'   => 'h3',
                'choices' => [
                    'h2'  => 'H2',
                    'h3'  => 'H3',
                    'h4'  => 'H4',
                    'h5'  => 'H5',
                    'div' => 'div',
                ],
            ],

            /* ---- Categories ---- */
            'cat_position' => [
                'label'   => __( 'Categories Position', 'fw' ),
                'type'    => 'select',
                'value'   => 'above-title',
                'choices' => [
                    'above-title'              => __( 'Above title (default)', 'fw' ),
                    'below-title'              => __( 'Below title', 'fw' ),
                    'in-meta'                  => __( 'Inside meta bar', 'fw' ),
                    'image-overlay-top-left'   => __( 'Image overlay — top left', 'fw' ),
                    'image-overlay-top-right'  => __( 'Image overlay — top right', 'fw' ),
                    'image-overlay-bottom-left'=> __( 'Image overlay — bottom left', 'fw' ),
                    'image-overlay-bottom-right'=>__( 'Image overlay — bottom right', 'fw' ),
                ],
            ],
            'cat_taxonomy' => [
                'label' => __( 'Taxonomy Slug for Chips', 'fw' ),
                'desc'  => __( 'Which taxonomy the chip pills should display. Default: category.', 'fw' ),
                'type'  => 'text',
                'value' => 'category',
            ],
            'cat_max' => [
                'label' => __( 'Max Chips Shown', 'fw' ),
                'type'  => 'short-text',
                'value' => '2',
            ],

            /* ---- Meta bar ---- */
            'meta_items' => [
                'label'   => __( 'Meta Bar Items', 'fw' ),
                'desc'    => __( 'Which items appear in the meta bar (when meta is visible).', 'fw' ),
                'type'    => 'checkboxes',
                'value'   => [ 'date' => true, 'author' => true ],
                'choices' => [
                    'date'     => __( 'Date', 'fw' ),
                    'author'   => __( 'Author', 'fw' ),
                    'comments' => __( 'Comment count', 'fw' ),
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

            /* ---- Excerpt ---- */
            'excerpt_source' => [
                'label'   => __( 'Excerpt Source', 'fw' ),
                'type'    => 'select',
                'value'   => 'auto',
                'choices' => [
                    'auto'    => __( 'Auto (post excerpt or trimmed content)', 'fw' ),
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

            /* ---- Read more ---- */
            'readmore_style' => [
                'label'   => __( 'Read-More Style', 'fw' ),
                'type'    => 'select',
                'value'   => 'text-link',
                'choices' => [
                    'button'      => __( 'Button', 'fw' ),
                    'text-link'   => __( 'Text link', 'fw' ),
                    'arrow-only'  => __( 'Arrow only (→)', 'fw' ),
                ],
            ],
            'readmore_text' => [
                'label' => __( 'Read-More Text', 'fw' ),
                'type'  => 'text',
                'value' => __( 'Read more', 'fw' ),
            ],
        ],
    ],

    /* ==========================================
       TAB 4 — INTERACTIVITY (pagination, filters, slider, cache)
       ========================================== */
    'tab_interactivity' => [
        'title'   => __( 'Navigation & Cache', 'fw' ),
        'type'    => 'tab',
        'options' => [

            'pagination_type' => [
                'label'   => __( 'Pagination Type', 'fw' ),
                'type'    => 'select',
                'value'   => 'none',
                'choices' => [
                    'none'          => __( 'None (static display)', 'fw' ),
                    'numeric'       => __( 'Numeric (1, 2, 3…)', 'fw' ),
                    'prev_next'     => __( 'Previous / Next', 'fw' ),
                    'ajax_loadmore' => __( 'AJAX Load More button', 'fw' ),
                    'infinite'      => __( 'AJAX Infinite scroll', 'fw' ),
                ],
            ],
            'pagination_position' => [
                'label'   => __( 'Pagination Position', 'fw' ),
                'type'    => 'select',
                'value'   => 'below-grid',
                'choices' => [
                    'below-grid' => __( 'Below grid', 'fw' ),
                    'above-grid' => __( 'Above grid', 'fw' ),
                    'both'       => __( 'Both above and below', 'fw' ),
                ],
            ],
            'pagination_align' => [
                'label'   => __( 'Pagination Alignment', 'fw' ),
                'type'    => 'select',
                'value'   => 'center',
                'choices' => [
                    'left'   => __( 'Left', 'fw' ),
                    'center' => __( 'Center', 'fw' ),
                    'right'  => __( 'Right', 'fw' ),
                ],
            ],

            'live_filters' => [
                'label' => __( 'AJAX Filter Bar', 'fw' ),
                'desc'  => __( 'Show category chips above/beside the grid that filter posts without a page reload.', 'fw' ),
                'type'  => 'switch',
                'value' => 'no',
            ],
            'filters_position' => [
                'label'   => __( 'Filter Bar Position', 'fw' ),
                'type'    => 'select',
                'value'   => 'above-grid',
                'choices' => [
                    'above-grid'    => __( 'Above grid', 'fw' ),
                    'left-sidebar'  => __( 'Left sidebar (wraps grid in 2-col flex)', 'fw' ),
                    'right-sidebar' => __( 'Right sidebar', 'fw' ),
                ],
            ],

            /* ---- Slider sub-controls (active when layout_mode=slider) ---- */
            'slider_arrows_position' => [
                'label'   => __( 'Slider — Arrow Position', 'fw' ),
                'type'    => 'select',
                'value'   => 'outside',
                'choices' => [
                    'inside'  => __( 'Inside (over slides)', 'fw' ),
                    'outside' => __( 'Outside (next to slides)', 'fw' ),
                    'above'   => __( 'Above slides', 'fw' ),
                    'hidden'  => __( 'Hidden', 'fw' ),
                ],
            ],
            'slider_dots_position' => [
                'label'   => __( 'Slider — Dots Position', 'fw' ),
                'type'    => 'select',
                'value'   => 'below',
                'choices' => [
                    'below'          => __( 'Below', 'fw' ),
                    'overlay-bottom' => __( 'Overlay (over slides, bottom)', 'fw' ),
                    'hidden'         => __( 'Hidden', 'fw' ),
                ],
            ],
            'slider_autoplay' => [
                'label' => __( 'Slider — Autoplay', 'fw' ),
                'type'  => 'switch',
                'value' => 'no',
            ],
            'slider_interval' => [
                'label' => __( 'Slider — Autoplay Interval (ms)', 'fw' ),
                'type'  => 'short-text',
                'value' => '5000',
            ],
            'slider_loop' => [
                'label' => __( 'Slider — Loop', 'fw' ),
                'type'  => 'switch',
                'value' => 'yes',
            ],

            /* ---- Cache ---- */
            'cache_output' => [
                'label' => __( 'Enable Transient Caching', 'fw' ),
                'desc'  => __( 'Cache rendered HTML. Auto-flushed when any post is saved.', 'fw' ),
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

            'no_results_text' => [
                'label' => __( 'Empty-State Message', 'fw' ),
                'type'  => 'text',
                'value' => __( 'Sorry, no posts matched your criteria.', 'fw' ),
            ],
        ],
    ],

    /* ==========================================
       TAB 5 — ADVANCED
       ========================================== */
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
