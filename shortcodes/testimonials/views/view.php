<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * @var array $atts
 *
 * Thin dispatcher. All shared data-prep happens here, then the chosen design's
 * template under designs/<key>.php is included (it inherits every variable below
 * by scope). Adding a design = drop designs/<key>.php + register it in
 * designs/registry.php — this file never changes.
 */

/* Per-element color picks (kept off the wrapper). sc_extract_styling_atts
   gives both preset classes AND compact-picker custom-hex inline styles. */
$quote_styling       = sc_extract_styling_atts( $atts, array( 'quote_color' ) );
$author_name_styling = sc_extract_styling_atts( $atts, array( 'author_name_color' ) );
$author_job_styling  = sc_extract_styling_atts( $atts, array( 'author_job_color' ) );
$site_link_styling   = sc_extract_styling_atts( $atts, array( 'site_link_color' ) );

$quote_class_extra       = implode( ' ', $quote_styling['classes'] );
$author_name_class_extra = implode( ' ', $author_name_styling['classes'] );
$author_job_class_extra  = implode( ' ', $author_job_styling['classes'] );
$site_link_class_extra   = implode( ' ', $site_link_styling['classes'] );

$quote_style_extra       = $quote_styling['styles']       ? implode( '; ', $quote_styling['styles'] )       : '';
$author_name_style_extra = $author_name_styling['styles'] ? implode( '; ', $author_name_styling['styles'] ) : '';
$author_job_style_extra  = $author_job_styling['styles']  ? implode( '; ', $author_job_styling['styles'] )  : '';
$site_link_style_extra   = $site_link_styling['styles']   ? implode( '; ', $site_link_styling['styles'] )   : '';

/* Helper getter */
if ( ! function_exists( 'sc_get' ) ) {
    function sc_get( $path, $atts, $default = '' ) {
        if ( function_exists( 'fw_akg' ) ) {
            $v = fw_akg( $path, $atts, null );
            if ( $v !== null ) return $v;
        }
        return $default;
    }
}

/* --- Resolve the chosen design via the pluggable-designs layer (built-in designs
   PLUS installed design packs). Falls back to 'default'. The layer resolves
   design_settings/design → legacy scalar `design` → 'default', and returns the
   partial path (an uploads pack's view.php, or a built-in designs/<key>.php). --- */
if ( function_exists( 'fw_sc_design_resolve' ) ) {
    $design      = fw_sc_design_resolve( 'testimonials', $atts, 'default' );
    $design_file = fw_sc_design_partial( 'testimonials', $design );
} else {
    $ts_designs = require dirname( __FILE__ ) . '/designs/registry.php';
    $design     = sc_get( 'design_settings/design', $atts, sc_get( 'design', $atts, 'default' ) );
    if ( ! is_string( $design ) || ! isset( $ts_designs[ $design ] ) ) { $design = 'default'; }
    $design_file = dirname( __FILE__ ) . '/designs/' . $design . '.php';
}

/* Reader for options that moved INTO the per-design multi-picker: prefer the
   new nested path (design_settings/<design>/<sub>), fall back to the legacy
   flat path, then the default — so existing saved instances keep rendering.
   Pack options live under the same design_settings/<key>/<sub> path. */
$ts_dp = function ( $sub, $old_flat, $default ) use ( $atts, $design ) {
    return sc_get( 'design_settings/' . $design . '/' . $sub, $atts, sc_get( $old_flat, $atts, $default ) );
};

if ( ! $design_file || ! file_exists( $design_file ) ) {
    $design      = 'default';
    $design_file = dirname( __FILE__ ) . '/designs/default.php';
}

/* Wrapper base (+ design-<key> hook for per-design CSS scoping) */
$atts['base_class']       = 'testimonials';
$atts['unique_id_prefix'] = 'ts-';
$attr = sc_build_wrapper_attr( $atts );
$attr['class'] = trim( ( isset( $attr['class'] ) ? $attr['class'] : '' ) . ' design-' . $design );

/* Content */
$testimonials = sc_get( 'testimonials', $atts, sc_get( 'group/testimonials', $atts, [] ) );
if ( ! is_array( $testimonials ) ) $testimonials = [];

/* Layout — Classic design only (moved into design_settings/default/*). */
$layout_choice   = $ts_dp( 'layout_type/layout_choice', 'layout_type/layout_choice', 'carousel' ); // carousel|grid|single
$grid_columns    = $ts_dp( 'layout_type/grid/grid_columns', 'layout_type/grid/grid_columns', 'row-cols-3' );
$gutter          = $ts_dp( 'layout_type/grid/gutter', 'gutter', '' );
$items_per_slide = (int) $ts_dp( 'items_per_slide', 'items_per_slide', 1 );
if ( $items_per_slide < 1 ) $items_per_slide = 1;

/* Cross-design appearance (stay top-level — no path change). */
/* Text Alignment now stores image-picker KEYS ('' / left / center / right) →
   map to the Bootstrap text-* class. Legacy saves stored the class directly
   ('text-center' / 'text-end'), so pass those through unchanged for back-compat. */
$text_align_raw  = sc_get( 'text_align', $atts, '' );
$text_align      = ( is_string( $text_align_raw ) && strpos( $text_align_raw, 'text-' ) === 0 )
	? $text_align_raw
	: sc_alignment_class( $text_align_raw );
$container_cls   = sc_get( 'container_type', $atts, 'container' ); // '' = None → no wrapper, fills the parent (e.g. nested in a section)
$avatar_shape    = sc_get( 'avatar_shape', $atts, 'rounded-circle' );
$avatar_size     = sc_get( 'avatar_size', $atts, 'avatar-md' );
$show_rating     = sc_get( 'show_rating', $atts, 'yes' ) === 'yes';

/* Rating star style (symbol / colors / size) from the element's Rating option group.
   Registered request-scoped so every design's sc_render_rating() call renders with it. */
if ( function_exists( 'sc_render_rating_set_style' ) && function_exists( 'sc_rating_style_from_atts' ) ) {
    sc_render_rating_set_style( sc_rating_style_from_atts( $atts, 'rating_' ) );
}

/* Style — Classic design only (moved into design_settings/default/*). */
$card_style       = $ts_dp( 'card_style', 'card_style', '' );
$box_style        = function_exists( 'sc_card_box_style_class' ) ? sc_card_box_style_class( $atts ) : ''; // Box Style per testimonial card (grid/card designs)
$show_avatar      = true; /* default unless explicitly hidden */
$avatar_position  = $ts_dp( 'avatar_position', 'avatar_position', 'top' ); // top|left|right|none (LEGACY fallback; the Card Rows slots own position now)
if ( $avatar_position === 'none' ) $show_avatar = false;

/* Card Rows — the slot designer for the Classic (default) design's card. When set, sc_render_card
   composes each card from these rows (avatar position = which row + its direction), superseding the
   old card_style / avatar_position / show_rating options. Empty → the legacy avatar-top layout. */
$card_rows        = function_exists( 'sc_card_rows_value' ) ? sc_card_rows_value( $atts, 'card_rows' ) : array();

/* Per-design SLOT FILTER (from the design study): slots a STRUCTURAL design renders in its own fixed
   position, so they're removed from the Card Rows body. Card-grid designs (default/masonry/bento/
   stacked/marquee) filter nothing — the rows drive the whole card. Structural designs fix the image
   (Split/Zigzag = media column, Thumbnav = thumb rail) or the quote (Bubble = balloon). Passed to
   sc_render_card as 'filter_slots'; a design view that owns a slot renders it itself + filters it here. */
$ts_card_filters  = array(
	'split'    => array( 'avatar', 'identity' ),
	'zigzag'   => array( 'avatar', 'identity' ),
	'thumbnav' => array( 'avatar', 'identity' ),
	'bubble'   => array( 'quote' ),
);
$card_filter      = isset( $ts_card_filters[ $design ] ) ? $ts_card_filters[ $design ] : array();

/* Avatar dimensions (custom utility classes expected in CSS) */
$avatar_dim_map = [ 'avatar-sm' => 64, 'avatar-md' => 96, 'avatar-lg' => 128 ];
$avatar_dim     = isset( $avatar_dim_map[ $avatar_size ] ) ? $avatar_dim_map[ $avatar_size ] : 96;

/* Shared sc_render_card() args — any card-family OR structural design can call
   sc_render_card( $t, $card_args ) (a structural design merges 'filter_slots' => $card_filter, and
   renders the filtered slot itself in its fixed position). Keeps every design's card body identical. */
$card_args = array(
	'card_rows'                => $card_rows,
	'box_class'                => $box_style,
	'card_style'               => $card_style,
	'text_align'               => $text_align,
	'show_avatar'              => $show_avatar,
	'avatar_shape'             => $avatar_shape,
	'avatar_size'              => $avatar_size,
	'avatar_dim'               => $avatar_dim,
	'show_rating'              => $show_rating,
	'avatar_position'          => $avatar_position,
	'quote_color_class'        => $quote_class_extra,
	'author_name_color_class'  => $author_name_class_extra,
	'author_job_color_class'   => $author_job_class_extra,
	'site_link_color_class'    => $site_link_class_extra,
	'quote_color_style'        => $quote_style_extra,
	'author_name_color_style'  => $author_name_style_extra,
	'author_job_color_style'   => $author_job_style_extra,
	'site_link_color_style'    => $site_link_style_extra,
);

/* Carousel behavior — read from the active design's group (Classic/Split/Thumbnav). */
$carousel_autoplay    = $ts_dp( 'carousel_autoplay', 'carousel_autoplay', 'yes' ) === 'yes';
$carousel_interval    = (int) $ts_dp( 'carousel_interval', 'carousel_interval', 5000 );
$carousel_pause_hover = $ts_dp( 'carousel_pause_hover', 'carousel_pause_hover', 'yes' ) === 'yes';
$carousel_controls    = $ts_dp( 'carousel_controls', 'carousel_controls', 'yes' ) === 'yes';
$carousel_indicators  = $ts_dp( 'carousel_indicators', 'carousel_indicators', 'yes' ) === 'yes';
$carousel_wrap        = $ts_dp( 'carousel_wrap', 'carousel_wrap', 'yes' ) === 'yes';
$indicator_style      = $ts_dp( 'carousel_indicator_style', 'carousel_indicator_style', 'dots' ); // dots|lines|none

/* New per-design controls (no legacy fallback needed — brand-new options). */
$marquee_speed     = sc_get( 'design_settings/marquee/marquee_speed', $atts, 'normal' );
$marquee_direction = sc_get( 'design_settings/marquee/marquee_direction', $atts, 'left' );
$masonry_columns   = (int) sc_get( 'design_settings/masonry/masonry_columns', $atts, 3 );
$bubble_columns    = (int) sc_get( 'design_settings/bubble/bubble_columns', $atts, 3 );

/* Map saved Bootstrap grid/container values onto the plugin's self-contained
   .fw- grid (the plugin no longer ships Bootstrap). Saved values are unchanged
   in the DB — only the emitted class names are translated here. */
$container_cls = ( $container_cls === 'container-fluid' ) ? 'fw-container-fluid' : ( '' === $container_cls ? '' : 'fw-container' );
$grid_columns  = str_replace( 'row-cols-', 'fw-row-cols-', (string) $grid_columns );

/* Dispatch to the chosen design template (inherits all of the above). */
include $design_file;

/* Optional Review + AggregateRating JSON-LD — machine-readable customer testimonials.
   itemReviewed is a minimal Organization (the site) so the reviews are not orphaned. */
if ( sc_get( 'reviews_schema', $atts, 'no' ) === 'yes' && ! empty( $testimonials ) ) {
	$reviewed = [ '@type' => 'Organization', 'name' => wp_strip_all_tags( get_bloginfo( 'name' ) ) ];
	$reviews  = [];
	$rsum     = 0.0;
	$rcount   = 0;
	foreach ( $testimonials as $t ) {
		$body = isset( $t['content'] ) ? trim( wp_strip_all_tags( (string) $t['content'] ) ) : '';
		$name = isset( $t['author_name'] ) ? trim( wp_strip_all_tags( (string) $t['author_name'] ) ) : '';
		if ( $body === '' && $name === '' ) { continue; }
		$rev = [ '@type' => 'Review', 'itemReviewed' => $reviewed ];
		if ( $name !== '' ) { $rev['author'] = [ '@type' => 'Person', 'name' => $name ]; }
		if ( $body !== '' ) { $rev['reviewBody'] = preg_replace( '/\s+/u', ' ', $body ); }
		$rv = isset( $t['rating'] ) ? (float) $t['rating'] : 0;
		if ( $rv > 0 ) {
			$rev['reviewRating'] = [ '@type' => 'Rating', 'ratingValue' => $rv, 'bestRating' => 5, 'worstRating' => 1 ];
			$rsum += $rv; $rcount++;
		}
		$reviews[] = $rev;
	}
	if ( ! empty( $reviews ) ) {
		$graph = [];
		if ( $rcount > 0 ) {
			$graph[] = [
				'@context'    => 'https://schema.org',
				'@type'       => 'AggregateRating',
				'itemReviewed' => $reviewed,
				'ratingValue' => round( $rsum / $rcount, 1 ),
				'reviewCount' => $rcount,
				'bestRating'  => 5,
				'worstRating' => 1,
			];
		}
		foreach ( $reviews as $r ) { $r['@context'] = 'https://schema.org'; $graph[] = $r; }
		foreach ( $graph as $node ) {
			echo '<script type="application/ld+json">' . wp_json_encode( $node, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
		}
	}
}
