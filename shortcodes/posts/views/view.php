<?php
if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}

/**
 * @var array $atts
 */

/*
|--------------------------------------------------------------------------
| Shared helper getter (defined once across all shortcodes)
|--------------------------------------------------------------------------
*/
if ( ! function_exists( 'sc_get' ) ) {
    function sc_get( $path, $atts, $default = '' ) {
        if ( function_exists( 'fw_akg' ) ) {
            $v = fw_akg( $path, $atts, null );
            if ( $v !== null ) return $v;
        }
        return $default;
    }
}

/*
|--------------------------------------------------------------------------
| Back-compat reader: prefer the new multi-picker nested path, fall back to
| the legacy flat path, then the default. Used to normalise atts whose storage
| location moved when Layout Mode / Card Style / Pagination became pickers.
|--------------------------------------------------------------------------
*/
if ( ! function_exists( 'sc_posts_dp' ) ) {
    function sc_posts_dp( $atts, $new_path, $old_flat, $default = '' ) {
        if ( function_exists( 'fw_akg' ) ) {
            $v = fw_akg( $new_path, $atts, null );
            if ( $v !== null ) return $v;
        }
        return sc_get( $old_flat, $atts, $default );
    }
}

/*
|--------------------------------------------------------------------------
| Resolve a gap value to a CSS size. Accepts a Gap Scale preset slug (resolved
| to its size from unysonplus_get_gap_scale()) or a legacy numeric px value.
| Returns '' for empty / unknown → caller falls back to the base default gap.
|--------------------------------------------------------------------------
*/
if ( ! function_exists( 'sc_posts_gap_size' ) ) {
    function sc_posts_gap_size( $val ) {
        if ( $val === '' || $val === null ) {
            return '';
        }
        $val = (string) $val;
        // Match a Gap Scale preset slug FIRST — preset slugs are numeric ("0"–"5"),
        // so this must run before the legacy-px numeric check below.
        if ( function_exists( 'unysonplus_get_gap_scale' ) ) {
            foreach ( unysonplus_get_gap_scale() as $e ) {
                if ( ! is_array( $e ) || ! isset( $e['name'] ) || $e['name'] === '' ) {
                    continue;
                }
                $slug = function_exists( 'sc_sanitize_class' )
                    ? strtolower( sc_sanitize_class( $e['name'] ) )
                    : strtolower( preg_replace( '/[^a-zA-Z0-9_-]+/', '-', $e['name'] ) );
                if ( $slug === $val && isset( $e['size'] ) ) {
                    return preg_replace( '/[^0-9a-zA-Z%.\s-]/', '', (string) $e['size'] );
                }
            }
        }
        // Legacy numeric px value (e.g. "24" / "32" from before gap presets).
        if ( is_numeric( $val ) ) {
            return (int) $val . 'px';
        }
        return '';
    }
}

/*
|--------------------------------------------------------------------------
| Card-design registry loader (single source of truth, loaded once)
|--------------------------------------------------------------------------
*/
if ( ! function_exists( 'sc_posts_card_registry' ) ) {
    function sc_posts_card_registry() {
        static $registry = null;
        if ( $registry === null ) {
            $registry = require __DIR__ . '/parts/registry.php';
            if ( ! is_array( $registry ) ) $registry = [];
        }
        return $registry;
    }
}

/*
|--------------------------------------------------------------------------
| Normalise atts: resolve options that moved INTO the Design / Card /
| Pagination multi-pickers back to their original flat keys, so every existing
| read below (and in the card parts) keeps working unchanged. Legacy saved
| instances (flat keys, no pickers) pass straight through via the fallback.
|--------------------------------------------------------------------------
*/
if ( ! function_exists( 'sc_posts_normalize_atts' ) ) {
    function sc_posts_normalize_atts( $atts ) {
        /* Layout mode (was `layout_mode` → now `design/mode`). */
        $mode = sc_get( 'design/mode', $atts, sc_get( 'layout_mode', $atts, 'grid' ) );
        $atts['layout_mode'] = $mode;

        /* Card style (was `card_style` → now `card/style`). */
        $style = sc_get( 'card/style', $atts, sc_get( 'card_style', $atts, 'standard' ) );
        $atts['card_style'] = $style;

        /* Grid/Masonry sub-options live under design/<mode>/. */
        /* Merged "Columns" control (mirrors the gallery Grid design): the builder
           exposes only the DESKTOP count; tablet & phone auto-derive. Grid stores a
           multi-picker { count:'N', 'N':{ col_ratio:[…] } }; Masonry (and legacy)
           store a scalar count. col_ratio (grid split-slider) → per-column widths. */
        $cols_raw  = sc_posts_dp( $atts, "design/$mode/columns", 'columns_desktop', '3' );
        $col_ratio = array();
        if ( is_array( $cols_raw ) ) {
            $count  = isset( $cols_raw['count'] ) ? max( 1, (int) $cols_raw['count'] ) : 3;
            $reveal = ( isset( $cols_raw[ (string) $count ] ) && is_array( $cols_raw[ (string) $count ] ) ) ? $cols_raw[ (string) $count ] : array();
            if ( isset( $reveal['col_ratio'] ) && is_array( $reveal['col_ratio'] ) ) { $col_ratio = $reveal['col_ratio']; }
        } else {
            $count = ( $cols_raw !== null && $cols_raw !== '' ) ? max( 1, (int) $cols_raw ) : 3;
        }
        /* Tablet = min(N,2), or N-1 for 5+; phone = 1. */
        $atts['columns_desktop'] = (string) $count;
        $atts['columns_tablet']  = (string) ( $count >= 5 ? $count - 1 : min( $count, 2 ) );
        $atts['columns_mobile']  = '1';
        $atts['col_ratio']       = $col_ratio;
        // Default '' (not 24/32) → an absent gap falls back to the stylesheet's base
        // gap, matching the option defaults; sc_posts_gap_size('') returns '' → no var.
        $atts['column_gap']         = sc_posts_dp( $atts, "design/$mode/column_gap",       'column_gap',      '' );
        $atts['row_gap']            = sc_posts_dp( $atts, "design/$mode/row_gap",          'row_gap',         '' );
        $atts['equal_height']       = sc_posts_dp( $atts, "design/$mode/equal_height",     'equal_height',    'yes' );
        $atts['featured_treatment'] = sc_posts_dp( $atts, "design/$mode/featured_treatment", 'featured_treatment', 'none' );

        /* Slider sub-options live under design/slider/. */
        $atts['slider_arrows_position'] = sc_posts_dp( $atts, 'design/slider/slider_arrows_position', 'slider_arrows_position', 'outside' );
        $atts['slider_dots_position']   = sc_posts_dp( $atts, 'design/slider/slider_dots_position',   'slider_dots_position',   'below' );
        $atts['slider_autoplay']        = sc_posts_dp( $atts, 'design/slider/slider_autoplay',        'slider_autoplay',        'no' );
        $atts['slider_interval']        = sc_posts_dp( $atts, 'design/slider/slider_interval',        'slider_interval',        '5000' );
        $atts['slider_loop']            = sc_posts_dp( $atts, 'design/slider/slider_loop',            'slider_loop',            'yes' );

        /* Card side-layout sub-options live under card/<style>/. */
        $atts['image_width_ratio']      = sc_posts_dp( $atts, "card/$style/image_width_ratio",      'image_width_ratio',      '40-60' );
        $atts['image_vertical_align']   = sc_posts_dp( $atts, "card/$style/image_vertical_align",   'image_vertical_align',   'stretch' );
        $atts['content_vertical_align'] = sc_posts_dp( $atts, "card/$style/content_vertical_align", 'content_vertical_align', 'top' );
        /* Image Position (Left / Right) for horizontal styles — Side resolves it to
           side-left/side-right (render loop); Postcard / News List / Listicle / Filmstrip
           flip via a class in their parts. */
        $atts['image_position']         = sc_posts_dp( $atts, "card/$style/image_position",         'image_position',         'left' );

        /* Pagination (was `pagination_type` → now `pagination/type`). */
        $ptype = sc_get( 'pagination/type', $atts, sc_get( 'pagination_type', $atts, 'none' ) );
        $atts['pagination_type']     = $ptype;
        $atts['pagination_position'] = sc_posts_dp( $atts, "pagination/$ptype/pagination_position", 'pagination_position', 'below-grid' );
        $atts['pagination_align']    = sc_posts_dp( $atts, "pagination/$ptype/pagination_align",    'pagination_align',    'center' );

        /* Read-more style (was `readmore_style` → now `readmore/style`). */
        $atts['readmore_style'] = sc_get( 'readmore/style', $atts, sc_get( 'readmore_style', $atts, 'text-link' ) );

        return $atts;
    }
}

/*
|--------------------------------------------------------------------------
| Helper: resolve a template part — child theme → parent theme → bundled
|--------------------------------------------------------------------------
*/
if ( ! function_exists( 'sc_posts_locate_part' ) ) {
    function sc_posts_locate_part( $slug ) {
        $rel = '/extensions/shortcodes/shortcodes/posts/views/parts/card-' . sanitize_file_name( $slug ) . '.php';

        if ( function_exists( 'fw_get_stylesheet_customizations_directory' ) ) {
            $p = fw_get_stylesheet_customizations_directory( $rel );
            if ( $p && file_exists( $p ) ) return $p;
        }
        if ( function_exists( 'fw_get_template_customizations_directory' ) ) {
            $p = fw_get_template_customizations_directory( $rel );
            if ( $p && file_exists( $p ) ) return $p;
        }
        return __DIR__ . '/parts/card-' . sanitize_file_name( $slug ) . '.php';
    }
}

/*
|--------------------------------------------------------------------------
| Helper: build the WP_Query args from shortcode atts
|--------------------------------------------------------------------------
*/
if ( ! function_exists( 'sc_posts_build_query_args' ) ) {
    function sc_posts_build_query_args( $atts, $paged = 1 ) {

        $post_type = sc_get( 'post_type', $atts, 'post' );
        $ppp       = (int) sc_get( 'posts_per_page', $atts, 6 );
        if ( $ppp === 0 ) $ppp = 6;

        $args = [
            'post_type'           => $post_type,
            'post_status'         => 'publish',
            'posts_per_page'      => $ppp,
            'paged'               => max( 1, (int) $paged ),
            'ignore_sticky_posts' => true,
            'offset'              => (int) sc_get( 'offset', $atts, 0 ),
        ];

        /* Order */
        $orderby = sc_get( 'orderby', $atts, 'date' );
        $order   = sc_get( 'order', $atts, 'DESC' );
        // Allow-list orderby (WP_Query sanitizes internally too, but be explicit).
        $allowed_orderby = [ 'date', 'title', 'menu_order', 'rand', 'comment_count', 'modified', 'name', 'ID', 'author', 'meta_value_num' ];
        if ( ! in_array( $orderby, $allowed_orderby, true ) ) { $orderby = 'date'; }
        $args['orderby'] = $orderby;
        $args['order']   = in_array( $order, [ 'ASC', 'DESC' ], true ) ? $order : 'DESC';
        if ( $orderby === 'meta_value_num' ) {
            $mk = trim( (string) sc_get( 'meta_key', $atts, '' ) );
            if ( $mk !== '' ) {
                $args['meta_key'] = $mk;
            } else {
                $args['orderby'] = 'date';
            }
        }

        /* Include / exclude IDs */
        $include = array_filter( array_map( 'intval', explode( ',', (string) sc_get( 'include_ids', $atts, '' ) ) ) );
        $exclude = array_filter( array_map( 'intval', explode( ',', (string) sc_get( 'exclude_ids', $atts, '' ) ) ) );
        if ( ! empty( $include ) ) {
            $args['post__in'] = $include;
            $args['orderby']  = 'post__in';
        }
        if ( sc_get( 'exclude_current', $atts, 'yes' ) === 'yes' && is_singular() ) {
            $exclude[] = get_the_ID();
        }
        if ( ! empty( $exclude ) ) {
            $args['post__not_in'] = array_values( array_unique( $exclude ) );
        }

        /* Authors */
        $authors = array_filter( array_map( 'intval', explode( ',', (string) sc_get( 'author_ids', $atts, '' ) ) ) );
        if ( ! empty( $authors ) ) {
            $args['author__in'] = $authors;
        }

        /* Taxonomy filter (format: taxonomy:term,term) */
        $tax_filter = trim( (string) sc_get( 'taxonomy_filter', $atts, '' ) );
        if ( $tax_filter !== '' ) {
            $relation = sc_get( 'taxonomy_relation', $atts, 'IN' );
            $relation = in_array( $relation, [ 'IN', 'AND', 'NOT IN' ], true ) ? $relation : 'IN';
            $tax_query = [];
            foreach ( explode( ';', $tax_filter ) as $pair ) {
                $pair = trim( $pair );
                if ( $pair === '' || strpos( $pair, ':' ) === false ) continue;
                list( $tax, $terms ) = array_map( 'trim', explode( ':', $pair, 2 ) );
                $terms = array_filter( array_map( 'trim', explode( ',', $terms ) ) );
                if ( $tax === '' || empty( $terms ) ) continue;
                $tax_query[] = [
                    'taxonomy' => $tax,
                    'field'    => 'slug',
                    'terms'    => $terms,
                    'operator' => $relation,
                ];
            }
            if ( count( $tax_query ) > 1 ) {
                $tax_query['relation'] = 'AND';
            }
            if ( ! empty( $tax_query ) ) {
                $args['tax_query'] = $tax_query;
            }
        }

        /* Date range */
        $range = sc_get( 'date_range', $atts, 'any' );
        if ( $range !== 'any' ) {
            $map = [
                'last_7'    => '7 days ago',
                'last_30'   => '30 days ago',
                'last_90'   => '90 days ago',
                'this_year' => 'January 1 this year',
            ];
            if ( isset( $map[ $range ] ) ) {
                $args['date_query'] = [ [ 'after' => $map[ $range ], 'inclusive' => true ] ];
            }
        }

        /* Sticky handling */
        $sticky = sc_get( 'sticky_handling', $atts, 'default' );
        if ( $sticky === 'default' ) {
            $args['ignore_sticky_posts'] = false;
        } elseif ( $sticky === 'only' ) {
            $stickies = get_option( 'sticky_posts' );
            $args['post__in'] = ! empty( $stickies ) ? $stickies : [ 0 ];
        }
        // 'pin_top' and 'ignore' are handled at render time / via ignore_sticky_posts already set.

        return apply_filters( 'sc_posts_query_args', $args, $atts );
    }
}

/*
|--------------------------------------------------------------------------
| Helper: render the meta bar inside a card
|--------------------------------------------------------------------------
*/
if ( ! function_exists( 'sc_posts_render_meta' ) ) {
    function sc_posts_render_meta( $atts, $post_id ) {
        $items   = (array) sc_get( 'meta_items', $atts, [ 'date' => true, 'author' => true ] );
        $layout  = sc_get( 'meta_layout', $atts, 'inline-dot' );
        $date_fmt= sc_get( 'date_format', $atts, 'wp' );

        $parts = [];

        if ( ! empty( $items['date'] ) ) {
            switch ( $date_fmt ) {
                case 'relative':
                    $date_str = sprintf(
                        /* translators: %s: human-readable time difference */
                        __( '%s ago', 'fw' ),
                        human_time_diff( get_the_time( 'U', $post_id ), current_time( 'timestamp' ) )
                    );
                    break;
                case 'long':
                    $date_str = get_the_date( 'F j, Y', $post_id );
                    break;
                case 'short':
                    $date_str = get_the_date( 'd/m/Y', $post_id );
                    break;
                default:
                    $date_str = get_the_date( '', $post_id );
            }
            $icon = $layout === 'inline-icons' ? '<i class="fa-regular fa-calendar" aria-hidden="true"></i> ' : '';
            $parts[] = '<span class="posts__meta-item posts__meta-item--date">' . $icon . esc_html( $date_str ) . '</span>';
        }

        if ( ! empty( $items['author'] ) ) {
            $icon = $layout === 'inline-icons' ? '<i class="fa-regular fa-user" aria-hidden="true"></i> ' : '';
            $parts[] = '<span class="posts__meta-item posts__meta-item--author">' . $icon . esc_html( get_the_author_meta( 'display_name', get_post_field( 'post_author', $post_id ) ) ) . '</span>';
        }

        if ( ! empty( $items['comments'] ) ) {
            $count = (int) get_comments_number( $post_id );
            $icon  = $layout === 'inline-icons' ? '<i class="fa-regular fa-comment" aria-hidden="true"></i> ' : '';
            $parts[] = '<span class="posts__meta-item posts__meta-item--comments">' . $icon . sprintf( _n( '%d comment', '%d comments', $count, 'fw' ), $count ) . '</span>';
        }

        if ( ! empty( $items['reading_time'] ) ) {
            $words   = str_word_count( wp_strip_all_tags( get_post_field( 'post_content', $post_id ) ) );
            $minutes = max( 1, (int) ceil( $words / 200 ) );
            $icon    = $layout === 'inline-icons' ? '<i class="fa-regular fa-clock" aria-hidden="true"></i> ' : '';
            $parts[] = '<span class="posts__meta-item posts__meta-item--reading">' . $icon . sprintf( _n( '%d min read', '%d min read', $minutes, 'fw' ), $minutes ) . '</span>';
        }

        if ( empty( $parts ) ) return '';

        $sep_map = [
            'inline-dot'   => ' <span class="posts__meta-sep" aria-hidden="true">·</span> ',
            'inline-pipe'  => ' <span class="posts__meta-sep" aria-hidden="true">|</span> ',
            'inline-icons' => ' <span class="posts__meta-sep posts__meta-sep--gap" aria-hidden="true"></span> ',
            'stacked'      => '',
        ];

        if ( $layout === 'stacked' ) {
            return '<div class="posts__meta posts__meta--stacked entry-meta">' . implode( '', array_map( function ( $p ) {
                return '<div class="posts__meta-row">' . $p . '</div>';
            }, $parts ) ) . '</div>';
        }

        $sep = isset( $sep_map[ $layout ] ) ? $sep_map[ $layout ] : $sep_map['inline-dot'];
        return '<div class="posts__meta posts__meta--' . esc_attr( $layout ) . ' entry-meta">' . implode( $sep, $parts ) . '</div>';
    }
}

/*
|--------------------------------------------------------------------------
| Helper: render category chips
|--------------------------------------------------------------------------
*/
if ( ! function_exists( 'sc_posts_slug_enabled' ) ) {
    /**
     * Is a card block explicitly enabled in the Elements → block list?
     * Checks the raw `element_order` (independent of the self-heal in
     * sc_posts_get_ordered_slugs) so a single block can be toggled off. Returns
     * true when the list is empty (defaults = all on) or the slug is absent
     * (forward-compatible with blocks added after a saved order).
     */
    function sc_posts_slug_enabled( $atts, $slug ) {
        $items = sc_get( 'element_order', $atts, [] );
        if ( ! is_array( $items ) || empty( $items ) ) return true;
        foreach ( $items as $it ) {
            if ( empty( $it['slug'] ) || $it['slug'] !== $slug ) continue;
            $enabled = isset( $it['enabled'] ) ? $it['enabled'] : 'yes';
            $off = ( $enabled === 'no' || $enabled === false || $enabled === '0' || $enabled === 0 );
            return ! $off;
        }
        return true;
    }
}

if ( ! function_exists( 'sc_posts_render_cats' ) ) {
    function sc_posts_render_cats( $atts, $post_id ) {
        // Honour the "Categories / taxonomy chips" toggle in the block list for
        // EVERY placement — including the image-overlay positions, whose card
        // parts render the chips directly (bypassing the body-slug gate).
        if ( ! sc_posts_slug_enabled( $atts, 'cats' ) ) return '';
        $tax = sc_get( 'cat_taxonomy', $atts, 'category' );
        $max = max( 0, (int) sc_get( 'cat_max', $atts, 2 ) );
        if ( $tax === '' || $max === 0 ) return '';
        $terms = get_the_terms( $post_id, $tax );
        if ( empty( $terms ) || is_wp_error( $terms ) ) return '';
        $terms = array_slice( $terms, 0, $max );
        $pos   = sc_get( 'cat_position', $atts, 'above-title' );
        $html  = '<div class="posts__cats posts__cats--' . esc_attr( $pos ) . '">';
        foreach ( $terms as $term ) {
            $html .= sprintf(
                '<a class="posts__cat-chip" href="%s">%s</a>',
                esc_url( get_term_link( $term ) ),
                esc_html( $term->name )
            );
        }
        $html .= '</div>';
        return $html;
    }
}

/*
|--------------------------------------------------------------------------
| Helper: render featured image (with fallback) — used inside parts
|--------------------------------------------------------------------------
*/
if ( ! function_exists( 'sc_posts_render_image' ) ) {
    function sc_posts_render_image( $atts, $post_id, $cat_overlay_html = '' ) {
        $size  = sc_get( 'image_size', $atts, 'medium_large' );
        $ratio = sc_get( 'image_ratio', $atts, 'ratio-16-9' );
        $img   = '';
        if ( has_post_thumbnail( $post_id ) ) {
            $img = get_the_post_thumbnail( $post_id, $size, [
                'class'   => 'posts__img fw-img-responsive',
                'loading' => 'lazy',
                'alt'     => esc_attr( get_the_title( $post_id ) ),
            ] );
        } else {
            $fallback = trim( (string) sc_get( 'fallback_image_url', $atts, '' ) );
            if ( $fallback !== '' ) {
                $img = sprintf(
                    '<img src="%s" alt="" class="posts__img fw-img-responsive" loading="lazy">',
                    esc_url( $fallback )
                );
            }
        }
        if ( $img === '' ) return '';
        // Image Style preset (Theme Settings → Components → Image Styles): the
        // `.posts__image` anchor doubles as the `.imgs-wrap` so the preset's crop /
        // mask / filter / scrim apply to the thumbnail (the base rule targets the
        // inner <img>; scrim/duotone layers overlay the anchor, pointer-events:none).
        $imgs        = function_exists( 'sc_image_style_class' ) ? sc_image_style_class( $atts ) : '';
        $imgs_cls    = ( $imgs !== '' ) ? ' imgs-wrap ' . $imgs : '';
        // The title (below) is the primary, keyboard-focusable link to the same
        // permalink. Take the image link OUT of the tab order (tabindex=-1) so
        // keyboard users don't tab through two links to one destination; it stays
        // mouse-clickable, and any overlay category chips inside keep their own
        // focus. (aria-label kept for the mouse/AT click target.)
        return sprintf(
            '<a class="posts__image posts__image--%s%s" href="%s" tabindex="-1" aria-label="%s">%s%s</a>',
            esc_attr( $ratio ),
            esc_attr( $imgs_cls ),
            esc_url( get_permalink( $post_id ) ),
            esc_attr( get_the_title( $post_id ) ),
            $img,
            $cat_overlay_html
        );
    }
}

/*
|--------------------------------------------------------------------------
| Helper: render excerpt
|--------------------------------------------------------------------------
*/
if ( ! function_exists( 'sc_posts_render_excerpt' ) ) {
    function sc_posts_render_excerpt( $atts, $post_id ) {
        $source = sc_get( 'excerpt_source', $atts, 'auto' );
        $length = max( 1, (int) sc_get( 'excerpt_length', $atts, 25 ) );
        $suffix = (string) sc_get( 'excerpt_suffix', $atts, '…' );

        if ( $source === 'excerpt' ) {
            $raw = get_post_field( 'post_excerpt', $post_id );
        } elseif ( $source === 'content' ) {
            $raw = get_post_field( 'post_content', $post_id );
        } else {
            $raw = get_post_field( 'post_excerpt', $post_id );
            if ( trim( $raw ) === '' ) $raw = get_post_field( 'post_content', $post_id );
        }
        $raw = wp_strip_all_tags( strip_shortcodes( $raw ) );
        if ( $raw === '' ) return '';
        $trimmed = wp_trim_words( $raw, $length, $suffix );
        return '<div class="posts__excerpt entry-summary"><p>' . esc_html( $trimmed ) . '</p></div>';
    }
}

/*
|--------------------------------------------------------------------------
| Helper: render read-more
|--------------------------------------------------------------------------
*/
if ( ! function_exists( 'sc_posts_render_readmore' ) ) {
    function sc_posts_render_readmore( $atts, $post_id ) {
        $style = sc_get( 'readmore_style', $atts, 'text-link' );
        $text  = (string) sc_get( 'readmore_text', $atts, __( 'Read more', 'fw' ) );
        $label = wp_strip_all_tags( get_the_title( $post_id ) );
        $aria  = sprintf( __( 'Read more about %s', 'fw' ), $label );

        if ( $style === 'arrow-only' ) {
            return sprintf(
                '<a class="posts__readmore posts__readmore--arrow" href="%s" aria-label="%s"><span aria-hidden="true">→</span></a>',
                esc_url( get_permalink( $post_id ) ),
                esc_attr( $aria )
            );
        }

        /* Button style → reuse the theme button preset classes (color + size),
           gated under the Button choice in the Read-More picker. Empty values are
           a no-op, so the .posts__readmore--button base styling still applies. */
        $extra_classes = '';
        if ( $style === 'button' ) {
            $btn_style = trim( (string) sc_get( 'readmore/button/readmore_btn_style', $atts, '' ) );
            $btn_size  = trim( (string) sc_get( 'readmore/button/readmore_btn_size',  $atts, '' ) );
            $extra = array_filter( [ 'btn', $btn_style, $btn_size ] );
            if ( $extra ) $extra_classes = ' ' . implode( ' ', array_map( 'sanitize_html_class', $extra ) );
        }

        // Append the post title as visually-hidden text so the link's accessible
        // name AND its crawlable text read "Read more about <title>" (fixes the SEO
        // "descriptive link text" audit) while sighted users still see just "Read
        // more". No aria-label — the text content now provides the name.
        return sprintf(
            '<a class="posts__readmore posts__readmore--%s%s" href="%s">%s<span class="posts__readmore-sr"> %s</span></a>',
            esc_attr( $style ),
            $extra_classes,
            esc_url( get_permalink( $post_id ) ),
            esc_html( $text ),
            esc_html( sprintf( __( 'about %s', 'fw' ), $label ) )
        );
    }
}

/*
|--------------------------------------------------------------------------
| Helper: render title
|--------------------------------------------------------------------------
*/
if ( ! function_exists( 'sc_posts_render_title' ) ) {
    function sc_posts_render_title( $atts, $post_id ) {
        $tag = sc_get( 'title_tag', $atts, 'h3' );
        if ( ! in_array( $tag, [ 'h2', 'h3', 'h4', 'h5', 'div' ], true ) ) $tag = 'h3';
        return sprintf(
            '<%1$s class="posts__title entry-title"><a href="%2$s">%3$s</a></%1$s>',
            $tag,
            esc_url( get_permalink( $post_id ) ),
            esc_html( get_the_title( $post_id ) )
        );
    }
}

/*
|--------------------------------------------------------------------------
| Helper: render a single block by slug (used by parts to honour element_order)
|--------------------------------------------------------------------------
*/
if ( ! function_exists( 'sc_posts_render_block' ) ) {
    function sc_posts_render_block( $slug, $atts, $post_id, $cat_overlay_html = '' ) {
        switch ( $slug ) {
            case 'image':    return sc_posts_render_image( $atts, $post_id, $cat_overlay_html );
            case 'cats':     return sc_posts_render_cats( $atts, $post_id );
            case 'title':    return sc_posts_render_title( $atts, $post_id );
            case 'meta':     return sc_posts_render_meta( $atts, $post_id );
            case 'excerpt':  return sc_posts_render_excerpt( $atts, $post_id );
            case 'readmore': return sc_posts_render_readmore( $atts, $post_id );
        }
        return '';
    }
}

/*
|--------------------------------------------------------------------------
| Helper: return ordered, enabled element slugs (filtered for context)
|--------------------------------------------------------------------------
*/
if ( ! function_exists( 'sc_posts_get_ordered_slugs' ) ) {
    function sc_posts_get_ordered_slugs( $atts, $exclude = [] ) {
        $items = sc_get( 'element_order', $atts, [] );
        if ( ! is_array( $items ) || empty( $items ) ) {
            $items = [
                [ 'slug' => 'image',    'enabled' => 'yes' ],
                [ 'slug' => 'cats',     'enabled' => 'yes' ],
                [ 'slug' => 'title',    'enabled' => 'yes' ],
                [ 'slug' => 'meta',     'enabled' => 'yes' ],
                [ 'slug' => 'excerpt',  'enabled' => 'yes' ],
                [ 'slug' => 'readmore', 'enabled' => 'yes' ],
            ];
        }
        $out = [];
        foreach ( $items as $it ) {
            if ( empty( $it['slug'] ) ) continue;
            if ( in_array( $it['slug'], $out, true ) ) continue; // de-dup: a repeated slug must not render twice
            // Enabled unless explicitly off. Accept the legacy string 'no' AND a
            // hard boolean false (a bare `switch` used to store false instead of
            // 'yes'); everything else — 'yes', true, 1 — counts as visible.
            $enabled = isset( $it['enabled'] ) ? $it['enabled'] : 'yes'; // absent key = on (avoids a PHP notice)
            $off = ( $enabled === 'no' || $enabled === false || $enabled === '0' || $enabled === 0 );
            if ( ! $off && ! in_array( $it['slug'], $exclude, true ) ) {
                $out[] = $it['slug'];
            }
        }
        // Self-heal: a card with EVERY block disabled renders nothing — never an
        // intentional setup, and the symptom of the old bare-switch saving boolean
        // false for all rows. Fall back to the default block set so cards aren't blank.
        if ( empty( $out ) ) {
            foreach ( [ 'image', 'cats', 'title', 'meta', 'excerpt', 'readmore' ] as $slug ) {
                if ( ! in_array( $slug, $exclude, true ) ) { $out[] = $slug; }
            }
        }
        return $out;
    }
}

/*
|--------------------------------------------------------------------------
| Helper: render a single card by dispatching to the right template part
|--------------------------------------------------------------------------
*/
if ( ! function_exists( 'sc_posts_render_card' ) ) {
    function sc_posts_render_card( $atts, $post_id, $card_style, $index = 0 ) {
        $registry = sc_posts_card_registry();
        $part = isset( $registry[ $card_style ]['part'] ) ? $registry[ $card_style ]['part'] : 'standard';
        $part_file = sc_posts_locate_part( $part );

        if ( ! file_exists( $part_file ) ) {
            return '';
        }

        ob_start();
        // Vars exposed to the part:
        $sc_atts  = $atts;
        $sc_post  = get_post( $post_id );
        $sc_style = $card_style;
        $sc_index = (int) $index;
        include $part_file;
        return ob_get_clean();
    }
}

/*
|--------------------------------------------------------------------------
| Helper: render the card list (inner HTML of .posts__grid) for a set of posts
| Shared by the full render AND the AJAX load-more / filter endpoints so all
| three produce identical markup.
|--------------------------------------------------------------------------
*/
if ( ! function_exists( 'sc_posts_render_cards' ) ) {
    function sc_posts_render_cards( $atts, $posts_list, $start_index = 0 ) {
        if ( empty( $posts_list ) ) return '';
        $card_style  = sc_get( 'card_style', $atts, 'standard' );
        $featured_tx = sc_get( 'featured_treatment', $atts, 'none' );
        $registry    = sc_posts_card_registry();
        $style_meta  = isset( $registry[ $card_style ] ) ? $registry[ $card_style ] : [];
        $__boxp      = function_exists( 'sc_card_box_style_class' ) ? sc_card_box_style_class( $atts ) : ''; // Box Style per post card
        $out   = '';
        // $start_index keeps first-post treatments (hero-split / featured / zig-zag)
        // tied to the TRUE first post, so an AJAX page-2 append never re-applies them.
        $index = (int) $start_index;
        foreach ( $posts_list as $p ) {
            $effective_style = $card_style;

            // first_style → the first post uses a different style (hero-split).
            if ( ! empty( $style_meta['first_style'] ) && $index === 0 ) {
                $effective_style = $style_meta['first_style'];
            }
            // alternate → flip side-left / side-right per row (zig-zag).
            if ( ! empty( $style_meta['alternate'] ) ) {
                $effective_style = ( $index % 2 === 0 ) ? 'side-left' : 'side-right';
            }
            // "side" is the unified tile → resolve to the legacy side-left/side-right the
            // card-side part dispatches on, via the Image Position option.
            if ( $effective_style === 'side' ) {
                $effective_style = ( sc_get( 'image_position', $atts, 'left' ) === 'right' ) ? 'side-right' : 'side-left';
            }
            // Featured first-post treatments
            $extra_card_class = '';
            if ( $featured_tx === 'first-post-2x' && $index === 0 ) {
                $extra_card_class = ' posts__card--span-2';
            }
            if ( $featured_tx === 'first-post-hero' && $index === 0 ) {
                $effective_style  = 'overlay';
                $extra_card_class = ' posts__card--span-2 posts__card--featured';
            }

            if ( $__boxp !== '' ) { $extra_card_class .= ' ' . $__boxp; }
            $card_html = sc_posts_render_card( $atts, $p->ID, $effective_style, $index );
            if ( $extra_card_class !== '' ) {
                // splice class into the outermost article
                $card_html = preg_replace(
                    '/<article class="([^"]+)"/',
                    '<article class="$1' . esc_attr( $extra_card_class ) . '"',
                    $card_html,
                    1
                );
            }
            $out .= $card_html;
            $index++;
        }
        return $out;
    }
}

/*
|--------------------------------------------------------------------------
| Main render — produces the full markup
|--------------------------------------------------------------------------
*/
if ( ! function_exists( 'sc_posts_render' ) ) {
    function sc_posts_render( $atts ) {

        /* Resolve picker-moved options back to flat keys so all reads below work. */
        $atts = sc_posts_normalize_atts( $atts );

        $card_style    = sc_get( 'card_style',   $atts, 'standard' );
        $layout_mode   = sc_get( 'layout_mode',  $atts, 'grid' );
        $cols_d        = (int) sc_get( 'columns_desktop', $atts, 3 );
        $cols_t        = (int) sc_get( 'columns_tablet',  $atts, 2 );
        $cols_m        = (int) sc_get( 'columns_mobile',  $atts, 1 );
        $col_gap_size  = sc_posts_gap_size( sc_get( 'column_gap', $atts, '' ) );
        $row_gap_size  = sc_posts_gap_size( sc_get( 'row_gap',    $atts, '' ) );
        $card_padding  = sc_get( 'card_padding', $atts, 'regular' );
        $equal_height  = sc_get( 'equal_height',  $atts, 'yes' ) === 'yes';
        $text_align    = sc_get( 'text_align',    $atts, 'left' );
        $mobile_ovr    = sc_get( 'mobile_layout_override', $atts, 'inherit' );
        $featured_tx   = sc_get( 'featured_treatment', $atts, 'none' );

        $pag_type      = sc_get( 'pagination_type', $atts, 'none' );
        $pag_pos       = sc_get( 'pagination_position', $atts, 'below-grid' );
        $pag_align     = sc_get( 'pagination_align', $atts, 'center' );
        $live_filters  = sc_get( 'live_filters', $atts, 'no' ) === 'yes';
        $filters_pos   = sc_get( 'filters_position', $atts, 'above-grid' );
        $no_results    = sc_get( 'no_results_text', $atts, __( 'Sorry, no posts matched your criteria.', 'fw' ) );

        /* Slider */
        $slider_arrows = sc_get( 'slider_arrows_position', $atts, 'outside' );
        $slider_dots   = sc_get( 'slider_dots_position',   $atts, 'below' );
        $slider_auto   = sc_get( 'slider_autoplay', $atts, 'no' ) === 'yes';
        $slider_int    = (int) sc_get( 'slider_interval', $atts, 5000 );
        $slider_loop   = sc_get( 'slider_loop', $atts, 'yes' ) === 'yes';

        /* Wrapper */
        $atts['base_class']       = 'posts';
        $atts['unique_id_prefix'] = 'ps-';
        $wrapper_classes = [
            'posts--style-' . sanitize_html_class( $card_style ),
            'posts--mode-' . sanitize_html_class( $layout_mode ),
            'posts--cols-d-' . (int) $cols_d,
            'posts--cols-t-' . (int) $cols_t,
            'posts--cols-m-' . (int) $cols_m,
            'posts--ratio-' . sanitize_html_class( sc_get( 'image_ratio', $atts, 'ratio-16-9' ) ),
            'posts--align-' . sanitize_html_class( $text_align ),
            'posts--meta-pos-' . sanitize_html_class( sc_get( 'cat_position', $atts, 'above-title' ) ),
        ];
        if ( $equal_height )                         $wrapper_classes[] = 'posts--equal-height';
        if ( $featured_tx !== 'none' )               $wrapper_classes[] = 'posts--featured-' . sanitize_html_class( $featured_tx );
        if ( $live_filters )                         $wrapper_classes[] = 'posts--has-filters posts--filters-' . sanitize_html_class( $filters_pos );
        // Chips only get their pill padding/radius when a chip background is actually set.
        if ( function_exists( 'sc_color_to_css' ) && sc_color_to_css( sc_get( 'chip_bg', $atts, '' ) ) !== '' ) {
            $wrapper_classes[] = 'posts--chips-bg';
        }

        $atts['css_class'] = trim( implode( ' ', $wrapper_classes ) . ' ' . ( $atts['css_class'] ?? '' ) );

        /* Decide up-front whether we'll reuse the page's main query (the archive Body
           Template pattern) so the page size advertised to AJAX load-more / infinite
           scroll matches the query actually on screen — not the (ignored) atts ppp. */
        $use_current = ( sc_get( 'use_current_query', $atts, 'no' ) === 'yes' )
            && ! is_admin() && ! is_singular()
            && isset( $GLOBALS['wp_query'] ) && ( $GLOBALS['wp_query'] instanceof WP_Query )
            && $GLOBALS['wp_query']->have_posts();
        $ppp_attr = $use_current
            ? max( 1, (int) $GLOBALS['wp_query']->get( 'posts_per_page' ) )
            : (int) sc_get( 'posts_per_page', $atts, 6 );

        $extra_attrs = [
            'data-mobile-layout' => sanitize_html_class( $mobile_ovr ),
            'data-density'       => sanitize_html_class( $card_padding ),
            'data-pagination'    => sanitize_html_class( $pag_type ),
            'data-ppp'           => $ppp_attr,
        ];
        if ( $layout_mode === 'slider' ) {
            $extra_attrs['data-slider-arrows']   = sanitize_html_class( $slider_arrows );
            $extra_attrs['data-slider-dots']     = sanitize_html_class( $slider_dots );
            $extra_attrs['data-slider-autoplay'] = $slider_auto ? '1' : '0';
            $extra_attrs['data-slider-interval'] = (int) $slider_int;
            $extra_attrs['data-slider-loop']     = $slider_loop ? '1' : '0';
        }

        /* AJAX Load More / Infinite Scroll / Live Filters need the server to rebuild
           this instance's query later. The JS posts back only the wrapper's DOM id,
           so give AJAX-enabled instances a STABLE id and stash the resolved atts in a
           transient keyed by it (client never sends atts → nothing to tamper with).
           Non-AJAX instances stay id-less to keep the markup clean. */
        $ajax_enabled = in_array( $pag_type, [ 'ajax_loadmore', 'infinite' ], true ) || $live_filters;
        if ( $ajax_enabled ) {
            $instance_id = ! empty( $atts['css_id'] )
                ? sanitize_html_class( strtolower( str_replace( ' ', '-', trim( (string) $atts['css_id'] ) ) ) )
                : 'ps-' . substr( md5( wp_json_encode( $atts ) ), 0, 12 );
            $extra_attrs['id'] = $instance_id;
            set_transient( 'sc_posts_ax_' . $instance_id, [
                'atts'        => $atts,
                'use_current' => $use_current,
                'main_qv'     => ( $use_current && isset( $GLOBALS['wp_query'] ) ) ? $GLOBALS['wp_query']->query_vars : null,
                'ppp'         => $ppp_attr,
                'current_id'  => is_singular() ? (int) get_the_ID() : 0, // for Exclude Current (is_singular() is false in AJAX)
            ], WEEK_IN_SECONDS );
        }

        $atts['extra_attrs'] = $extra_attrs;

        $attr = sc_build_wrapper_attr( $atts );

        /* Build CSS custom properties for gaps + columns (positioning concern).
           Gaps come from the theme Gap Scale presets (resolved to a size) — only
           set the var when a preset is chosen, so an empty value falls back to the
           base default gap in styles.css. */
        $style_var = sprintf(
            '--posts-cols-d:%d;--posts-cols-t:%d;--posts-cols-m:%d;',
            $cols_d, $cols_t, $cols_m
        );
        /* Column Ratio (grid split-slider) — per-column widths for a FEATURED /
           dominant card. Emit an explicit desktop grid-template-columns
           (--posts-grid-tpl, fr units) ONLY when the user made the columns
           meaningfully UNEQUAL; an equal split falls back to the plain
           repeat(--posts-cols-d) grid. Tablet/phone stay equal (media queries). */
        $col_ratio = (array) sc_get( 'col_ratio', $atts, array() );
        // Emit an explicit template ONLY when the ratio's segment count matches the
        // desktop column count AND we're under 5 columns (5 = fixed-equal by design).
        // Guards against stale builder data (a ratio saved for a different count).
        if ( count( $col_ratio ) >= 2 && count( $col_ratio ) === $cols_d && $cols_d < 5 ) {
            $ws = array();
            foreach ( $col_ratio as $seg ) { $ws[] = ( is_array( $seg ) && isset( $seg['w'] ) ) ? max( 1, (float) $seg['w'] ) : 1; }
            if ( ( max( $ws ) - min( $ws ) ) > 2 ) { // meaningfully unequal
                $fr = array();
                foreach ( $ws as $w ) { $fr[] = rtrim( rtrim( number_format( $w, 2, '.', '' ), '0' ), '.' ) . 'fr'; }
                $style_var .= '--posts-grid-tpl:' . implode( ' ', $fr ) . ';';
            }
        }
        if ( $col_gap_size !== '' ) $style_var .= '--posts-col-gap:' . $col_gap_size . ';';
        if ( $row_gap_size !== '' ) $style_var .= '--posts-row-gap:' . $row_gap_size . ';';
        /* Per-part colors (compact presets) → --posts-* custom props consumed by styles.css.
           A preset resolves to var(--color-{slug}) (live-linked to the palette); a custom
           value is sanitised to color-safe characters. Unset = no var = theme defaults. */
        if ( function_exists( 'sc_color_to_css' ) ) {
            foreach ( array(
                'title_color'   => '--posts-title',
                'excerpt_color' => '--posts-excerpt',
                'meta_color'    => '--posts-meta',
                'chip_bg'       => '--posts-chip-bg',
                'chip_color'    => '--posts-chip',
                'accent_color'  => '--posts-accent',
            ) as $ck => $cv ) {
                $cval = sc_color_to_css( sc_get( $ck, $atts, '' ) );
                $cval = preg_replace( '/[^#0-9a-zA-Z(),.%\s-]/', '', (string) $cval );
                if ( $cval !== '' ) { $style_var .= $cv . ':' . $cval . ';'; }
            }
        }
        $attr['style'] = isset( $attr['style'] ) ? $attr['style'] . ';' . $style_var : $style_var;

        /* Query — either the current page's main query ("Posts for current page",
           the archive Body Template pattern) or a custom WP_Query from the atts. The
           render below only reads $query->posts, so reusing the main query is safe. */
        $paged = max( 1, (int) get_query_var( 'paged' ) ?: ( (int) get_query_var( 'page' ) ?: 1 ) );
        if ( $use_current ) {
            $query = $GLOBALS['wp_query'];
        } else {
            $q_args = sc_posts_build_query_args( $atts, $paged );
            $query  = new WP_Query( $q_args );
        }

        /* Pagination handling — sticky pin */
        $posts_list = $query->posts;
        if ( sc_get( 'sticky_handling', $atts, 'default' ) === 'pin_top' && ! empty( $posts_list ) ) {
            $stickies = (array) get_option( 'sticky_posts', [] );
            usort( $posts_list, function ( $a, $b ) use ( $stickies ) {
                $a_s = in_array( $a->ID, $stickies, true );
                $b_s = in_array( $b->ID, $stickies, true );
                if ( $a_s === $b_s ) return 0;
                return $a_s ? -1 : 1;
            } );
        }

        /* Render */
        ob_start();
        ?>
        <div <?php echo fw_attr_to_html( $attr ); ?>>

            <?php if ( $live_filters && in_array( $filters_pos, [ 'above-grid' ], true ) ) : ?>
                <?php echo sc_posts_render_filter_bar( $atts ); ?>
            <?php endif; ?>

            <div class="posts__layout-wrap posts__layout-wrap--<?php echo esc_attr( $filters_pos ); ?>">

                <?php if ( $live_filters && in_array( $filters_pos, [ 'left-sidebar' ], true ) ) : ?>
                    <?php echo sc_posts_render_filter_bar( $atts ); ?>
                <?php endif; ?>

                <div class="posts__main">

                    <?php if ( $pag_type === 'numeric' && in_array( $pag_pos, [ 'above-grid', 'both' ], true ) ) : ?>
                        <?php echo sc_posts_render_pagination( $query, $pag_align ); ?>
                    <?php endif; ?>

                    <?php if ( empty( $posts_list ) ) : ?>
                        <div class="posts__empty"><?php echo esc_html( $no_results ); ?></div>
                    <?php else : ?>

                        <div class="posts__grid">
                            <?php echo sc_posts_render_cards( $atts, $posts_list, 0 ); ?>
                        </div>

                        <?php if ( $pag_type === 'numeric' && in_array( $pag_pos, [ 'below-grid', 'both' ], true ) ) : ?>
                            <?php echo sc_posts_render_pagination( $query, $pag_align ); ?>
                        <?php endif; ?>

                        <?php if ( $pag_type === 'ajax_loadmore' && $query->max_num_pages > 1 ) : ?>
                            <div class="posts__loadmore-wrap posts__pagination--<?php echo esc_attr( $pag_align ); ?>">
                                <button type="button" class="posts__loadmore" data-page="2" data-max-page="<?php echo (int) $query->max_num_pages; ?>">
                                    <?php esc_html_e( 'Load more', 'fw' ); ?>
                                </button>
                            </div>
                        <?php endif; ?>

                        <?php if ( $pag_type === 'infinite' && $query->max_num_pages > 1 ) : ?>
                            <div class="posts__infinite-sentinel" data-page="2" data-max-page="<?php echo (int) $query->max_num_pages; ?>" aria-hidden="true"></div>
                        <?php endif; ?>

                    <?php endif; ?>
                </div>

                <?php if ( $live_filters && $filters_pos === 'right-sidebar' ) : ?>
                    <?php echo sc_posts_render_filter_bar( $atts ); ?>
                <?php endif; ?>
            </div>
        </div>
        <?php
        wp_reset_postdata();
        return ob_get_clean();
    }
}

/*
|--------------------------------------------------------------------------
| Helper: pagination markup
|--------------------------------------------------------------------------
*/
if ( ! function_exists( 'sc_posts_render_pagination' ) ) {
    function sc_posts_render_pagination( $query, $align ) {
        $big   = 999999999;
        $links = paginate_links( [
            'base'      => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
            'format'    => '?paged=%#%',
            'current'   => max( 1, get_query_var( 'paged' ) ),
            'total'     => $query->max_num_pages,
            'prev_text' => __( '« Prev', 'fw' ),
            'next_text' => __( 'Next »', 'fw' ),
        ] );
        if ( empty( $links ) ) return '';
        return '<nav class="posts__pagination posts__pagination--' . esc_attr( $align ) . '" aria-label="' . esc_attr__( 'Posts pagination', 'fw' ) . '">' . $links . '</nav>';
    }
}

/*
|--------------------------------------------------------------------------
| Helper: AJAX filter bar (terms of the chosen taxonomy)
|--------------------------------------------------------------------------
*/
if ( ! function_exists( 'sc_posts_render_filter_bar' ) ) {
    function sc_posts_render_filter_bar( $atts ) {
        $tax = sc_get( 'cat_taxonomy', $atts, 'category' );
        if ( $tax === '' ) return '';
        $terms = get_terms( [ 'taxonomy' => $tax, 'hide_empty' => true ] );
        if ( empty( $terms ) || is_wp_error( $terms ) ) return '';
        ob_start();
        ?>
        <div class="posts__filters" role="group" aria-label="<?php esc_attr_e( 'Filter posts by category', 'fw' ); ?>">
            <button class="posts__filter is-active" type="button" data-term="" aria-pressed="true">
                <?php esc_html_e( 'All', 'fw' ); ?>
            </button>
            <?php foreach ( $terms as $t ) : ?>
                <button class="posts__filter" type="button" data-term="<?php echo esc_attr( $t->slug ); ?>" aria-pressed="false">
                    <?php echo esc_html( $t->name ); ?>
                </button>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}

/*
|--------------------------------------------------------------------------
| Cache wrapper — short-circuits to a transient when enabled
|--------------------------------------------------------------------------
*/
/* This tail runs only when the file is included as the shortcode view (with $atts
   set). The AJAX handler require_once's this file purely for its sc_posts_* helpers,
   with no $atts — the guard stops the render/echo from firing in that context. */
if ( isset( $atts ) ) {
    $do_cache  = sc_get( 'cache_output', $atts, 'no' ) === 'yes';
    $cache_key = '';
    if ( $do_cache ) {
        /* Key must vary by the per-request context the output depends on, not atts
           alone: exclude_current adds the current post to the query, and
           use_current_query binds output to the queried object — two pages with
           identical atts must NOT share a cache entry (that served one page's list,
           with the wrong exclusion, on another). It must ALSO vary by the CODE
           version: without it, a plugin update keeps replaying pre-update markup
           from the transient until it expires (this served stale role="listitem"
           cards after the ARIA fix shipped). The extension version bumps on every
           meaningful change, so keying on it invalidates instantly on update. */
        $ver = '0';
        if ( function_exists( 'fw_ext' ) && fw_ext( 'shortcodes' ) ) {
            $ver = (string) fw_ext( 'shortcodes' )->manifest->get_version();
        }
        $ctx = (int) get_the_ID() . ':' . (int) get_queried_object_id();
        $cache_key = 'sc_posts_' . md5( $ver . '|' . wp_json_encode( $atts ) . '|' . max( 1, (int) get_query_var( 'paged' ) ) . '|' . $ctx );
        $cached    = get_transient( $cache_key );
        if ( $cached !== false ) {
            echo $cached;
            return;
        }
    }

    $html = sc_posts_render( $atts );

    if ( $do_cache ) {
        set_transient( $cache_key, $html, max( 1, (int) sc_get( 'cache_hours', $atts, 12 ) ) * HOUR_IN_SECONDS );
    }

    echo $html;
}
