<?php if (!defined('FW')) die('Forbidden');

$shortcodes_extension = fw_ext('shortcodes');

// Splide (vendored under the Carousel shortcode, already minified — enqueue as-is).
// The carousel layout repoints the old Bootstrap-JS carousel at Splide so the
// plugin no longer depends on Bootstrap's JS.
wp_enqueue_style(
	'splide',
	$shortcodes_extension->get_declared_URI( '/shortcodes/carousel/static/vendor/splide-core.min.css' )
);
wp_enqueue_script(
	'splide',
	$shortcodes_extension->get_declared_URI( '/shortcodes/carousel/static/vendor/splide.min.js' ),
	array(),
	'4.1.4',
	true
);

wp_enqueue_style(
	'fw-shortcode-testimonials',
	fw_min_uri($shortcodes_extension->get_declared_URI('/shortcodes/testimonials/static/css/styles.css')),
	array('splide', 'fw-ext-builder-frontend-grid') // ratings are inline SVG now — no Font Awesome dependency
);
wp_enqueue_script(
	'fw-shortcode-testimonials',
	fw_min_uri($shortcodes_extension->get_declared_URI('/shortcodes/testimonials/static/js/scripts.js')),
	array('splide'),
	$shortcodes_extension->manifest->get_version(),
	true
);

/* ---------------------------------------------------------------------------
 * Per-design assets (registry-driven). The base styles.css / scripts.js above
 * always load (they cover the Classic/default design + shared avatars, ratings
 * and Splide). A design's OWN css/js is enqueued only for instances that pick
 * it — via the per-instance `fw_ext_shortcodes_enqueue_static:testimonials`
 * action, which (unlike this file) receives the instance atts.
 *
 * Legacy instances have no `design` att → resolve to 'default' → no extra
 * asset is enqueued, so they keep loading exactly the base set as before.
 * ------------------------------------------------------------------------- */
if ( ! function_exists( '_fw_testimonials_enqueue_design_static' ) ) :
	function _fw_testimonials_enqueue_design_static( $data ) {
		$atts = shortcode_parse_atts( $data['atts_string'] );
		if ( ! is_array( $atts ) ) {
			return;
		}
		$post_id = ( isset( $data['post'] ) && isset( $data['post']->ID ) ) ? $data['post']->ID : 0;
		$atts    = fw_ext_shortcodes_decode_attr( $atts, 'testimonials', $post_id );
		if ( is_wp_error( $atts ) || ! is_array( $atts ) ) {
			return;
		}

		$design = fw_akg( 'design_settings/design', $atts, null ); // new multi-picker path
		if ( ! is_string( $design ) || $design === '' ) {
			$design = ( isset( $atts['design'] ) && is_string( $atts['design'] ) ) ? $atts['design'] : 'default'; // legacy scalar fallback
		}
		$registry = require dirname( __FILE__ ) . '/views/designs/registry.php';
		if ( ! isset( $registry[ $design ] ) ) {
			$design = 'default';
		}

		$ext     = fw_ext( 'shortcodes' );
		$base     = '/shortcodes/testimonials/static';
		$version  = $ext->manifest->get_version();
		$design_d = $registry[ $design ];

		if ( ! empty( $design_d['css'] ) ) {
			wp_enqueue_style(
				'fw-shortcode-testimonials-' . $design,
				$ext->get_declared_URI( $base . '/css/designs/' . $design_d['css'] ),
				array( 'fw-shortcode-testimonials' ),
				$version
			);
		}
		if ( ! empty( $design_d['js'] ) ) {
			wp_enqueue_script(
				'fw-shortcode-testimonials-' . $design,
				$ext->get_declared_URI( $base . '/js/designs/' . $design_d['js'] ),
				array( 'splide', 'fw-shortcode-testimonials' ),
				$version,
				true
			);
		}
	}
	add_action( 'fw_ext_shortcodes_enqueue_static:testimonials', '_fw_testimonials_enqueue_design_static' );
endif;


/* Quote renderer — allows a SAFE INLINE subset only (bold / italic / link /
   line-break) so authors can lightly format a quote without a full editor,
   while block-level + styling markup is stripped to protect each design's
   typography. Bare newlines (plain textarea input) become <br>. Use this in
   place of esc_html() for the quote body in every design. */
if ( ! function_exists( 'sc_testimonial_quote_html' ) ) {
	function sc_testimonial_quote_html( $content ) {
		$allowed = array(
			'strong' => array(),
			'b'      => array(),
			'em'     => array(),
			'i'      => array(),
			'br'     => array(),
			'a'      => array(
				'href'   => true,
				'title'  => true,
				'target' => true,
				'rel'    => true,
			),
		);
		$content = wp_kses( (string) $content, $allowed );
		return nl2br( $content, false );
	}
}

/* Shared per-item field extractor for the design templates (escaping happens
   at output in each template). Returns raw values with safe defaults. */
if ( ! function_exists( 'sc_testimonial_fields' ) ) {
	function sc_testimonial_fields( $t ) {
		return array(
			'content'     => isset( $t['content'] ) ? $t['content'] : '',
			'author_name' => isset( $t['author_name'] ) ? $t['author_name'] : '',
			'author_job'  => isset( $t['author_job'] ) ? $t['author_job'] : '',
			'site_name'   => isset( $t['site_name'] ) ? $t['site_name'] : '',
			'site_url'    => isset( $t['site_url'] ) ? $t['site_url'] : '',
			'rating'      => isset( $t['rating'] ) ? $t['rating'] : '',
			'avatar'      => ! empty( $t['author_avatar']['url'] ) ? $t['author_avatar']['url'] : '',
		);
	}
}


/* Rating renderer — self-contained inline SVG stars (no Font Awesome
   dependency, so ratings render on any theme). Full = filled, half = 50%
   gradient fill + outline, empty = outline. Stars inherit color via
   currentColor (see .testimonial-rating in styles.css). */
if ( ! function_exists( 'sc_render_rating' ) ) {
    function sc_render_rating( $rating ) {
        if ($rating === '' || $rating === null) return '';
        $rating = (float) $rating;
        if ($rating <= 0) return '';
        if ($rating > 5) $rating = 5.0;

        $full  = (int) floor($rating);
        $half  = ($rating - $full) >= 0.5 ? 1 : 0;
        if ($full >= 5) { $full = 5; $half = 0; }
        $empty = 5 - $full - $half;

        static $uid = 0;
        $path = 'M12 .587l3.668 7.431 8.2 1.192-5.934 5.784 1.401 8.169L12 18.896l-7.335 3.867 1.401-8.169L.132 9.21l8.2-1.192z';

        $star = function ( $type ) use ( $path, &$uid ) {
            $svg = '<svg class="ts-star ts-star--' . $type . '" viewBox="0 0 24 24" width="1em" height="1em" aria-hidden="true" focusable="false">';
            if ( $type === 'half' ) {
                $gid  = 'ts-star-h' . ( ++$uid );
                $svg .= '<defs><linearGradient id="' . $gid . '">'
                      . '<stop offset="50%" stop-color="currentColor" stop-opacity="1"/>'
                      . '<stop offset="50%" stop-color="currentColor" stop-opacity="0"/>'
                      . '</linearGradient></defs>'
                      . '<path d="' . $path . '" fill="url(#' . $gid . ')" stroke="currentColor" stroke-width="1"/>';
            } elseif ( $type === 'full' ) {
                $svg .= '<path d="' . $path . '" fill="currentColor"/>';
            } else {
                $svg .= '<path d="' . $path . '" fill="none" stroke="currentColor" stroke-width="1.5"/>';
            }
            return $svg . '</svg>';
        };

        $out = '<span class="testimonial-rating" role="img" aria-label="' . esc_attr( sprintf( __( 'Rated %s out of 5', 'fw' ), number_format($rating,1) ) ) . '">';
        for ($i=0; $i<$full; $i++)  $out .= $star('full');
        if ($half)                  $out .= $star('half');
        for ($i=0; $i<$empty; $i++) $out .= $star('empty');
        $out .= '</span>';
        return $out;
    }
}

if ( ! function_exists( 'sc_render_card' ) ) {
    function sc_render_card( $t, $args ) {
        $card_style      = $args['card_style'];
        $text_align      = $args['text_align'];
        $show_avatar     = $args['show_avatar'];
        $avatar_shape    = $args['avatar_shape'];
        $avatar_size     = $args['avatar_size'];
        $avatar_dim      = (int) $args['avatar_dim'];
        $show_rating     = $args['show_rating'];
        $avatar_position = $args['avatar_position']; // top|left|right|none

        // Per-element color picks from the parent Styling tab. Empty strings
        // when no preset is picked, in which case the class addition is a no-op.
        $quote_color_class       = isset( $args['quote_color_class'] )       ? trim( $args['quote_color_class'] )       : '';
        $author_name_color_class = isset( $args['author_name_color_class'] ) ? trim( $args['author_name_color_class'] ) : '';
        $author_job_color_class  = isset( $args['author_job_color_class'] )  ? trim( $args['author_job_color_class'] )  : '';
        $site_link_color_class   = isset( $args['site_link_color_class'] )   ? trim( $args['site_link_color_class'] )   : '';

        // Compact-picker custom-hex inline styles (peer-of-class for each
        // per-element field). Empty when only a preset / nothing is picked.
        $quote_color_style       = isset( $args['quote_color_style'] )       ? trim( $args['quote_color_style'] )       : '';
        $author_name_color_style = isset( $args['author_name_color_style'] ) ? trim( $args['author_name_color_style'] ) : '';
        $author_job_color_style  = isset( $args['author_job_color_style'] )  ? trim( $args['author_job_color_style'] )  : '';
        $site_link_color_style   = isset( $args['site_link_color_style'] )   ? trim( $args['site_link_color_style'] )   : '';

        $maybe_style = function ( $s ) {
            return $s !== '' ? ' style="' . esc_attr( $s ) . '"' : '';
        };

        $content     = isset( $t['content'] ) ? $t['content'] : '';
        $author_name = isset( $t['author_name'] ) ? $t['author_name'] : '';
        $author_job  = isset( $t['author_job'] ) ? $t['author_job'] : '';
        $site_name   = isset( $t['site_name'] ) ? $t['site_name'] : '';
        $site_url    = isset( $t['site_url'] ) ? $t['site_url'] : '';
        $rating_val  = isset( $t['rating'] ) ? $t['rating'] : '';

        $avatar_html = '';
        if ( $show_avatar && ! empty( $t['author_avatar']['url'] ) ) {
            $url = esc_url( $t['author_avatar']['url'] );
            // Center on mobile, normal flow desktop
            $avatar_html = '<div class="testimonial-avatar text-center flex-shrink-0 mx-auto mb-3">'
                . '<img src="' . $url . '" alt="' . esc_attr( $author_name ) . '" class="img-fluid '
                . esc_attr( $avatar_shape . ' ' . $avatar_size )
                . '" style="width:' . $avatar_dim . 'px;height:' . $avatar_dim . 'px;object-fit:cover;" />'
                . '</div>';
        }

        $rating_html = $show_rating && function_exists('sc_render_rating')
            ? sc_render_rating( $rating_val )
            : '';

        $site_html = '';
        if ( $site_name && $site_url ) {
            $site_class = trim( 'testimonial-site ' . $site_link_color_class );
            $site_html  = '<span class="' . esc_attr( $site_class ) . '"' . $maybe_style( $site_link_color_style ) . '><a href="' . esc_url( $site_url ) . '" rel="nofollow" target="_blank">' . esc_html( $site_name ) . '</a></span>';
        }

        $job_class  = trim( 'testimonial-job ' . $author_job_color_class );
        $meta_parts = array_filter( [
            $author_job ? '<span class="' . esc_attr( $job_class ) . '"' . $maybe_style( $author_job_color_style ) . '>' . esc_html( $author_job ) . '</span>' : '',
            $site_html,
        ] );
        $author_meta = $meta_parts
            ? '<div class="testimonial-meta small text-muted">' . implode( ' <span class="sep">|</span> ', $meta_parts ) . '</div>'
            : '';

        $author_block = '';
        if ( $author_name ) {
            $name_class    = trim( 'testimonial-author fw-semibold ' . $author_name_color_class );
            $author_block .= '<div class="' . esc_attr( $name_class ) . '"' . $maybe_style( $author_name_color_style ) . '>' . esc_html( $author_name ) . '</div>';
        }
        $author_block .= $author_meta;
        if ( $rating_html ) {
            $author_block .= '<div class="mt-2">' . $rating_html . '</div>';
        }

        $quote_class = trim( 'testimonial-quote mb-3 ' . $quote_color_class );
        $quote_html  = '<blockquote class="' . esc_attr( $quote_class ) . '"' . $maybe_style( $quote_color_style ) . '><p class="mb-0">'
            . sc_testimonial_quote_html( $content ) . '</p></blockquote>' . $author_block;

        $classes = trim( 'testimonial-item ' . $card_style . ' ' . $text_align . ' avatar-pos-' . $avatar_position );
        $classes = preg_replace( '/\s+/', ' ', $classes );

        ob_start();

        // Stacked (top) or no avatar
        if ( ! $show_avatar || $avatar_position === 'top' ) {
            echo '<div class="' . esc_attr( $classes ) . ' d-flex flex-column w-100 text-center">';
            if ( $show_avatar && $avatar_position === 'top' ) {
                echo '<div>' . $avatar_html . '</div>';
            }
            echo '<div class="testimonial-body">' . $quote_html . '</div>';
            echo '</div>';
        }
        // Left / Right with mobile stacking
        elseif ( $avatar_position === 'left' || $avatar_position === 'right' ) {
            // Stack vertically on mobile (centered), horizontal from md up
            $row_classes = 'd-flex flex-column flex-md-row align-items-start gap-3 w-100';
            if ( $avatar_position === 'right' ) {
                $row_classes .= ' flex-md-row-reverse';
            }
            // Body text centered on mobile, original alignment (or start) on md+
            $body_text_classes = 'testimonial-body flex-grow-1 text-center text-md-start';
            echo '<div class="' . esc_attr( $classes ) . ' ' . $row_classes . '">';
            echo $avatar_html;
            echo '<div class="' . esc_attr( $body_text_classes ) . '">' . $quote_html . '</div>';
            echo '</div>';
        }

        return ob_get_clean();
    }
}
?>