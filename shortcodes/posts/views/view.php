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
if ( ! function_exists( 'sc_posts_render_cats' ) ) {
    function sc_posts_render_cats( $atts, $post_id ) {
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
        return sprintf(
            '<a class="posts__image posts__image--%s" href="%s" aria-label="%s">%s%s</a>',
            esc_attr( $ratio ),
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
        return sprintf(
            '<a class="posts__readmore posts__readmore--%s" href="%s" aria-label="%s">%s</a>',
            esc_attr( $style ),
            esc_url( get_permalink( $post_id ) ),
            esc_attr( $aria ),
            esc_html( $text )
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
            if ( ! empty( $it['enabled'] ) && $it['enabled'] !== 'no' && ! in_array( $it['slug'], $exclude, true ) ) {
                $out[] = $it['slug'];
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
    function sc_posts_render_card( $atts, $post_id, $card_style ) {
        $part_map = [
            'standard'   => 'standard',
            'side-left'  => 'side',
            'side-right' => 'side',
            'overlay'    => 'overlay',
            'minimal'    => 'minimal',
            'hero-split' => 'standard', // hero-split uses standard for non-first cards
            'alternating'=> 'side',
        ];
        $part = isset( $part_map[ $card_style ] ) ? $part_map[ $card_style ] : 'standard';
        $part_file = sc_posts_locate_part( $part );

        if ( ! file_exists( $part_file ) ) {
            return '';
        }

        ob_start();
        // Vars exposed to the part:
        $sc_atts  = $atts;
        $sc_post  = get_post( $post_id );
        $sc_style = $card_style;
        include $part_file;
        return ob_get_clean();
    }
}

/*
|--------------------------------------------------------------------------
| Main render — produces the full markup
|--------------------------------------------------------------------------
*/
if ( ! function_exists( 'sc_posts_render' ) ) {
    function sc_posts_render( $atts ) {

        $card_style    = sc_get( 'card_style',   $atts, 'standard' );
        $layout_mode   = sc_get( 'layout_mode',  $atts, 'grid' );
        $cols_d        = (int) sc_get( 'columns_desktop', $atts, 3 );
        $cols_t        = (int) sc_get( 'columns_tablet',  $atts, 2 );
        $cols_m        = (int) sc_get( 'columns_mobile',  $atts, 1 );
        $col_gap       = (int) sc_get( 'column_gap', $atts, 24 );
        $row_gap       = (int) sc_get( 'row_gap',    $atts, 32 );
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

        $atts['css_class'] = trim( implode( ' ', $wrapper_classes ) . ' ' . ( $atts['css_class'] ?? '' ) );

        $extra_attrs = [
            'data-mobile-layout' => sanitize_html_class( $mobile_ovr ),
            'data-density'       => sanitize_html_class( $card_padding ),
            'data-pagination'    => sanitize_html_class( $pag_type ),
            'data-ppp'           => (int) sc_get( 'posts_per_page', $atts, 6 ),
        ];
        if ( $layout_mode === 'slider' ) {
            $extra_attrs['data-slider-arrows']   = sanitize_html_class( $slider_arrows );
            $extra_attrs['data-slider-dots']     = sanitize_html_class( $slider_dots );
            $extra_attrs['data-slider-autoplay'] = $slider_auto ? '1' : '0';
            $extra_attrs['data-slider-interval'] = (int) $slider_int;
            $extra_attrs['data-slider-loop']     = $slider_loop ? '1' : '0';
        }
        $atts['extra_attrs'] = $extra_attrs;

        $attr = sc_build_wrapper_attr( $atts );

        /* Build CSS custom properties for gaps + columns (positioning concern). */
        $style_var = sprintf(
            '--posts-col-gap:%dpx;--posts-row-gap:%dpx;--posts-cols-d:%d;--posts-cols-t:%d;--posts-cols-m:%d;',
            $col_gap, $row_gap, $cols_d, $cols_t, $cols_m
        );
        $attr['style'] = isset( $attr['style'] ) ? $attr['style'] . ';' . $style_var : $style_var;

        /* Query */
        $paged = max( 1, (int) get_query_var( 'paged' ) ?: ( (int) get_query_var( 'page' ) ?: 1 ) );
        $q_args = sc_posts_build_query_args( $atts, $paged );
        $query  = new WP_Query( $q_args );

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

                        <div class="posts__grid" role="list">
                            <?php
                            $index = 0;
                            foreach ( $posts_list as $p ) :
                                $effective_style = $card_style;

                                // Hero-split: first post uses overlay
                                if ( $card_style === 'hero-split' && $index === 0 ) {
                                    $effective_style = 'overlay';
                                }
                                // Alternating: flip for odd cards
                                if ( $card_style === 'alternating' ) {
                                    $effective_style = ( $index % 2 === 0 ) ? 'side-left' : 'side-right';
                                }
                                // Featured first-post treatments
                                $extra_card_class = '';
                                if ( $featured_tx === 'first-post-2x' && $index === 0 ) {
                                    $extra_card_class = ' posts__card--span-2';
                                }
                                if ( $featured_tx === 'first-post-hero' && $index === 0 ) {
                                    $effective_style = 'overlay';
                                    $extra_card_class = ' posts__card--span-2 posts__card--featured';
                                }

                                $card_html = sc_posts_render_card( $atts, $p->ID, $effective_style );
                                if ( $extra_card_class !== '' ) {
                                    // splice class into the outermost article
                                    $card_html = preg_replace(
                                        '/<article class="([^"]+)"/',
                                        '<article class="$1' . esc_attr( $extra_card_class ) . '"',
                                        $card_html,
                                        1
                                    );
                                }
                                echo $card_html;
                                $index++;
                            endforeach;
                            ?>
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
        <div class="posts__filters" role="tablist">
            <button class="posts__filter is-active" type="button" data-term="">
                <?php esc_html_e( 'All', 'fw' ); ?>
            </button>
            <?php foreach ( $terms as $t ) : ?>
                <button class="posts__filter" type="button" data-term="<?php echo esc_attr( $t->slug ); ?>">
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
$do_cache  = sc_get( 'cache_output', $atts, 'no' ) === 'yes';
$cache_key = '';
if ( $do_cache ) {
    $cache_key = 'sc_posts_' . md5( wp_json_encode( $atts ) . '|' . max( 1, (int) get_query_var( 'paged' ) ) );
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
