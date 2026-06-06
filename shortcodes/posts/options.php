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
                'help'    => __( 'Lists every public post type registered on the site. Pick a custom type (e.g. Portfolio, Product) to pull that content instead of standard posts.', 'fw' ),
                'type'    => 'select',
                'value'   => 'post',
                'choices' => $sc_post_type_choices,
            ],

            'taxonomy_filter' => [
                'label' => __( 'Filter by Taxonomy / Terms', 'fw' ),
                'desc'  => __( 'Format: taxonomy:term-slug,term-slug. Examples: category:news,tech  or  post_tag:featured. Leave empty for none.', 'fw' ),
                'help'  => __( 'Use the term slug (lowercase, hyphenated), not its display name. Combine with the Term Relation option below to control whether posts must match any or all of the listed terms.', 'fw' ),
                'type'  => 'text',
                'value' => '',
            ],

            'taxonomy_relation' => [
                'label'   => __( 'Term Relation', 'fw' ),
                'help'    => __( 'Controls how multiple terms in the filter above are matched. Match All is useful for narrow cross-sections (e.g. posts tagged both "news" and "tech"); Exclude hides posts in those terms.', 'fw' ),
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
                'help'  => __( 'Use this to hand-pick an exact set of posts, e.g. 12,48,103. Note that order here does not set display order — use the Order By options for that.', 'fw' ),
                'type'  => 'text',
                'value' => '',
            ],

            'exclude_ids' => [
                'label' => __( 'Exclude These IDs', 'fw' ),
                'desc'  => __( 'Comma-separated post IDs to hide from the query.', 'fw' ),
                'help'  => __( 'Handy for omitting a few specific posts from an otherwise automatic query. Combine with Exclude Current Post to also drop the post being viewed.', 'fw' ),
                'type'  => 'text',
                'value' => '',
            ],

            'author_ids' => [
                'label' => __( 'Limit to Authors', 'fw' ),
                'desc'  => __( 'Comma-separated user IDs. Empty = all authors.', 'fw' ),
                'help'  => __( 'Use numeric user IDs, not usernames (e.g. 1,5). Good for building an author-specific feed such as "More from this writer".', 'fw' ),
                'type'  => 'text',
                'value' => '',
            ],

            'date_range' => [
                'label'   => __( 'Date Range', 'fw' ),
                'help'    => __( 'Restricts results to posts published within the chosen window, relative to today. Use "Last 30 days" for a rolling recent-news block that stays fresh automatically.', 'fw' ),
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
                'help'  => __( 'For grids, pick a multiple of your column count so rows stay full (e.g. 6 for 3 columns). Avoid -1 on large sites as it loads every post at once.', 'fw' ),
                'type'  => 'short-text',
                'value' => '6',
            ],

            'offset' => [
                'label' => __( 'Offset', 'fw' ),
                'desc'  => __( 'Skip N posts from the start of the query.', 'fw' ),
                'help'  => __( 'Useful when a separate block already shows the latest posts: set Offset to skip those and avoid duplicates. Offset is ignored when pagination is enabled.', 'fw' ),
                'type'  => 'short-text',
                'value' => '0',
            ],

            'orderby' => [
                'label'   => __( 'Order By', 'fw' ),
                'help'    => __( 'Choosing Custom field (numeric) requires filling in the Custom Field Key below. Random reshuffles on every page load, so it is not cache-friendly.', 'fw' ),
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
                'help'  => __( 'Enter the exact meta_key stored against the posts. The values are sorted numerically, so this only works for fields holding numbers (view counts, prices, ratings).', 'fw' ),
                'type'  => 'text',
                'value' => '',
            ],

            'order' => [
                'label'   => __( 'Order Direction', 'fw' ),
                'help'    => __( 'Descending shows newest or highest first; Ascending shows oldest or lowest first. For Title sorting, Ascending gives A→Z.', 'fw' ),
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
                'help'  => __( 'Only has an effect when the shortcode is placed on a single post or page. On archive or static pages it does nothing.', 'fw' ),
                'type'  => 'switch',
                'value' => 'yes',
            ],

            'sticky_handling' => [
                'label'   => __( 'Sticky Post Handling', 'fw' ),
                'help'    => __( 'Sticky posts are those marked "Stick to the top" in WordPress. Use Ignore for a clean chronological feed, or Only to build a curated highlights block.', 'fw' ),
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
                'help'    => __( 'Grid and Masonry use the column settings below; Slider turns the result set into a carousel and enables the Slider controls in the Navigation tab. Pick List for a stacked, full-width feed.', 'fw' ),
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
                'help'    => __( 'Side, Overlay, and Hero styles enable the image-ratio and vertical-align sub-controls below. Minimal hides the image regardless of the Element Order list.', 'fw' ),
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
                'help'    => __( 'A larger image share (e.g. 60/40) suits image-led cards; a smaller share (30/70) gives the text more room. Has no effect on Standard, Overlay, or Minimal styles.', 'fw' ),
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
                'help'    => __( 'Stretch makes the image fill the full card height for a flush edge; Top or Center keep its natural height and align it within taller content. Side layouts only.', 'fw' ),
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
                'help'    => __( 'Center vertically balances short text next to a tall image. Pick Justify to pin the read-more link to the bottom so it lines up across cards. Side layouts only.', 'fw' ),
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
                'help'    => __( 'Number of cards per row on large screens. 3 or 4 reads well for most blog grids; 5–6 suits compact or minimal cards. Ignored in List and Slider modes.', 'fw' ),
                'type'    => 'select',
                'value'   => '3',
                'choices' => [ '1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6' ],
            ],
            'columns_tablet' => [
                'label'   => __( 'Tablet Columns', 'fw' ),
                'help'    => __( 'Cards per row on medium screens (roughly 600–1024px). Usually one or two fewer than desktop to keep cards from getting too narrow.', 'fw' ),
                'type'    => 'select',
                'value'   => '2',
                'choices' => [ '1' => '1', '2' => '2', '3' => '3', '4' => '4' ],
            ],
            'columns_mobile' => [
                'label'   => __( 'Mobile Columns', 'fw' ),
                'help'    => __( 'Cards per row on phones. 1 is the safest choice for readability; only use 2 with very compact or minimal cards.', 'fw' ),
                'type'    => 'select',
                'value'   => '1',
                'choices' => [ '1' => '1', '2' => '2' ],
            ],

            'mobile_layout_override' => [
                'label'   => __( 'Mobile Card Style Override', 'fw' ),
                'desc'    => __( 'Switch card style on mobile (≤ 782px) regardless of desktop choice.', 'fw' ),
                'help'    => __( 'Side layouts often feel cramped on phones — set this to Standard or Minimal for a cleaner small-screen view. Leave on Inherit to keep the desktop style everywhere.', 'fw' ),
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
                'help'       => __( 'Horizontal space between cards in a row. Enter a number only (no "px"). Smaller values (12–16) suit dense grids; larger (32+) gives an airier feel.', 'fw' ),
                'type'       => 'short-text',
                'value'      => '24',
            ],
            'row_gap' => [
                'label'      => __( 'Row Gap (px)', 'fw' ),
                'help'       => __( 'Vertical space between rows of cards. Number only. It is common to set this a little larger than the column gap for visual balance.', 'fw' ),
                'type'       => 'short-text',
                'value'      => '32',
            ],
            'card_padding' => [
                'label'   => __( 'Card Padding (Density)', 'fw' ),
                'help'    => __( 'Inner spacing around each card\'s content. None lets text and images run edge-to-edge (good for overlay styles); Spacious gives a roomy, premium feel.', 'fw' ),
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
                'help'  => __( 'Keeps a tidy grid when posts have different excerpt or title lengths. Turn this off for Masonry, where uneven heights are the intended effect.', 'fw' ),
                'type'  => 'switch',
                'value' => 'yes',
            ],

            /* ---- Image ---- */
            'image_size' => [
                'label'   => __( 'Image Size', 'fw' ),
                'help'    => __( 'Which registered WordPress image size to request for the featured image. Pick the smallest size that still looks sharp at your card width to keep pages fast.', 'fw' ),
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
                'help'    => __( 'Forces every featured image into a uniform shape so cards line up neatly, cropping from the center as needed. Choose Original aspect to avoid any cropping.', 'fw' ),
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
                'help'  => __( 'Paste a full image URL (e.g. a placeholder or brand graphic from the Media Library). Keeps card heights consistent when some posts lack a featured image.', 'fw' ),
                'type'  => 'text',
                'value' => '',
            ],

            /* ---- Featured / first-post treatment ---- */
            'featured_treatment' => [
                'label'   => __( 'Featured Post Treatment', 'fw' ),
                'desc'    => __( 'Special handling for the first post in the result set.', 'fw' ),
                'help'    => __( 'Gives the newest (or first cherry-picked) post extra prominence at the top of the grid. The first-post styles work best with 2 or more desktop columns.', 'fw' ),
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
                'help'    => __( 'Sets horizontal alignment for the title, meta, and excerpt within each card. Center reads well for minimal or overlay styles; Left is easiest for longer excerpts.', 'fw' ),
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
                'help'     => __( 'Listing a block twice has no effect — each appears once. To temporarily hide a block without losing its position, just switch its Visible toggle off rather than deleting the row.', 'fw' ),
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
                        'help'    => __( 'Which card element this row represents. Pick a different block in each row to avoid duplicates.', 'fw' ),
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
                        'help'  => __( 'Turn off to hide this block while keeping its place in the order, so you can re-enable it later without re-sorting.', 'fw' ),
                        'type'  => 'switch',
                        'value' => 'yes',
                    ],
                ],
            ],

            /* ---- Title ---- */
            'title_tag' => [
                'label'   => __( 'Title HTML Tag', 'fw' ),
                'help'    => __( 'Sets the heading level for SEO and accessibility, not the visual size (styling controls that). Keep it below the page\'s main H1 — H3 is a safe default inside content.', 'fw' ),
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
                'help'    => __( 'The image-overlay options only show when the card actually has an image (Standard, Side, Overlay, or Hero styles). Use a non-overlay position for Minimal cards.', 'fw' ),
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
                'help'  => __( 'Enter the taxonomy slug, e.g. post_tag for tags or a custom taxonomy like product_cat. Make sure the chosen Post Type actually uses this taxonomy or no chips will appear.', 'fw' ),
                'type'  => 'text',
                'value' => 'category',
            ],
            'cat_max' => [
                'label' => __( 'Max Chips Shown', 'fw' ),
                'help'  => __( 'Caps how many taxonomy pills appear per card so they do not wrap onto several lines. 1–2 keeps cards tidy; set a high number to show them all.', 'fw' ),
                'type'  => 'short-text',
                'value' => '2',
            ],

            /* ---- Meta bar ---- */
            'meta_items' => [
                'label'   => __( 'Meta Bar Items', 'fw' ),
                'desc'    => __( 'Which items appear in the meta bar (when meta is visible).', 'fw' ),
                'help'    => __( 'These only render if the Meta block is enabled in the Element Order list above. Reading time is estimated from word count at roughly 200 words per minute.', 'fw' ),
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
                'help'    => __( 'Controls how the selected meta items are separated. Inline keeps them on one line; Stacked puts each on its own line, which suits narrow side or minimal cards.', 'fw' ),
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
                'help'    => __( 'WordPress default follows the format set under Settings > General. Relative ("2 days ago") feels current but is less precise; Long or Short give a fixed calendar date.', 'fw' ),
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
                'help'    => __( 'Manual excerpt only shows nothing for posts that have no hand-written excerpt. Use Auto for the most reliable result across a mixed set of posts.', 'fw' ),
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
                'help'  => __( 'Word limit used when the excerpt is auto-generated or trimmed from content. Has no effect on hand-written manual excerpts, which are shown in full.', 'fw' ),
                'type'  => 'short-text',
                'value' => '25',
            ],
            'excerpt_suffix' => [
                'label' => __( 'Excerpt Suffix', 'fw' ),
                'help'  => __( 'Appended after a trimmed excerpt to signal there is more, e.g. an ellipsis or " Read on". Leave blank for no trailing characters.', 'fw' ),
                'type'  => 'short-text',
                'value' => '…',
            ],

            /* ---- Read more ---- */
            'readmore_style' => [
                'label'   => __( 'Read-More Style', 'fw' ),
                'help'    => __( 'Visual treatment of the read-more link. Button draws the most attention; Arrow only is the most subtle. Only renders when the Read-more block is enabled in Element Order.', 'fw' ),
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
                'help'  => __( 'The link or button label, e.g. "Read more", "Continue reading", or "View project". Ignored when the Arrow only style is selected.', 'fw' ),
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
                'help'    => __( 'Anything other than None splits results into pages using Posts per Page as the page size. AJAX options load more without a full reload but will not work if Offset is set.', 'fw' ),
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
                'help'    => __( 'Where the page links sit relative to the grid. Both is handy for long lists so readers do not have to scroll back up to navigate. Ignored when Pagination Type is None.', 'fw' ),
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
                'help'    => __( 'Horizontal alignment of the pagination controls within their row. Center is the conventional choice; match it to your card text alignment for a cohesive look.', 'fw' ),
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
                'help'  => __( 'Chips are built from the terms of the taxonomy used in the Categories settings. Works best with a generous Posts per Page so filtered results have enough to show.', 'fw' ),
                'type'  => 'switch',
                'value' => 'no',
            ],
            'filters_position' => [
                'label'   => __( 'Filter Bar Position', 'fw' ),
                'help'    => __( 'Only applies when the AJAX Filter Bar is enabled. Sidebar options wrap the grid in a two-column layout, so allow fewer grid columns to leave room.', 'fw' ),
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
                'help'    => __( 'Only applies when Layout Mode is Slider. Inside overlays arrows on the slides (good with overlay cards); Outside needs a little horizontal breathing room around the carousel.', 'fw' ),
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
                'help'    => __( 'Slider mode only. The dot indicators show how many slides exist and the current position. Overlay places them over the slide; Hidden removes them entirely.', 'fw' ),
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
                'help'  => __( 'Slider mode only. When on, slides advance automatically using the interval below. Leave off for accessibility-sensitive pages where motion should be user-initiated.', 'fw' ),
                'type'  => 'switch',
                'value' => 'no',
            ],
            'slider_interval' => [
                'label' => __( 'Slider — Autoplay Interval (ms)', 'fw' ),
                'help'  => __( 'Time each slide stays before advancing, in milliseconds (5000 = 5 seconds). Only used when Autoplay is on. Give text-heavy slides a longer interval so they can be read.', 'fw' ),
                'type'  => 'short-text',
                'value' => '5000',
            ],
            'slider_loop' => [
                'label' => __( 'Slider — Loop', 'fw' ),
                'help'  => __( 'Slider mode only. When on, the carousel wraps from the last slide back to the first seamlessly. Autoplay generally pairs best with looping enabled.', 'fw' ),
                'type'  => 'switch',
                'value' => 'yes',
            ],

            /* ---- Cache ---- */
            'cache_output' => [
                'label' => __( 'Enable Transient Caching', 'fw' ),
                'desc'  => __( 'Cache rendered HTML. Auto-flushed when any post is saved.', 'fw' ),
                'help'  => __( 'Speeds up heavy queries by reusing saved HTML. Avoid it when Order By is Random or AJAX pagination is on, since cached output would defeat those features.', 'fw' ),
                'type'  => 'switch',
                'value' => 'no',
            ],
            'cache_hours' => [
                'label'   => __( 'Cache Lifespan', 'fw' ),
                'help'    => __( 'How long cached HTML is kept before being rebuilt. Only matters when Transient Caching is on. The cache is also cleared automatically whenever a post is saved.', 'fw' ),
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
                'help'  => __( 'Shown when the query (or an active filter) returns no posts. Keep it friendly and, where helpful, suggest clearing filters or checking back later.', 'fw' ),
                'type'  => 'text',
                'value' => __( 'Sorry, no posts matched your criteria.', 'fw' ),
            ],
        ],
    ],

    /* ==========================================
       TAB 5 — ADVANCED
       ========================================== */
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
