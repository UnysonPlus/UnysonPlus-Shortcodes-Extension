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
$title_styling       = sc_extract_styling_atts( $atts, array( 'title_color' ) );
$quote_styling       = sc_extract_styling_atts( $atts, array( 'quote_color' ) );
$author_name_styling = sc_extract_styling_atts( $atts, array( 'author_name_color' ) );
$author_job_styling  = sc_extract_styling_atts( $atts, array( 'author_job_color' ) );
$site_link_styling   = sc_extract_styling_atts( $atts, array( 'site_link_color' ) );

$title_class_extra       = implode( ' ', $title_styling['classes'] );
$quote_class_extra       = implode( ' ', $quote_styling['classes'] );
$author_name_class_extra = implode( ' ', $author_name_styling['classes'] );
$author_job_class_extra  = implode( ' ', $author_job_styling['classes'] );
$site_link_class_extra   = implode( ' ', $site_link_styling['classes'] );

$title_style_extra       = $title_styling['styles']       ? implode( '; ', $title_styling['styles'] )       : '';
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

/* --- Resolve the chosen design (safe: defaults to 'default' for legacy/missing).
   New path is the design_settings multi-picker; falls back to the original
   scalar `design` att, then to 'default'. --- */
$ts_designs = require dirname( __FILE__ ) . '/designs/registry.php';
$design     = sc_get( 'design_settings/design', $atts, sc_get( 'design', $atts, 'default' ) );
if ( ! is_string( $design ) || ! isset( $ts_designs[ $design ] ) ) {
    $design = 'default';
}

/* Reader for options that moved INTO the per-design multi-picker: prefer the
   new nested path (design_settings/<design>/<sub>), fall back to the legacy
   flat path, then the default — so existing saved instances keep rendering. */
$ts_dp = function ( $sub, $old_flat, $default ) use ( $atts, $design ) {
    return sc_get( 'design_settings/' . $design . '/' . $sub, $atts, sc_get( $old_flat, $atts, $default ) );
};
$design_file = dirname( __FILE__ ) . '/designs/' . $design . '.php';
if ( ! file_exists( $design_file ) ) {
    $design      = 'default';
    $design_file = dirname( __FILE__ ) . '/designs/default.php';
}

/* Wrapper base (+ design-<key> hook for per-design CSS scoping) */
$atts['base_class']       = 'testimonials';
$atts['unique_id_prefix'] = 'ts-';
$attr = sc_build_wrapper_attr( $atts );
$attr['class'] = trim( ( isset( $attr['class'] ) ? $attr['class'] : '' ) . ' design-' . $design );

/* Content */
$title        = sc_get( 'title', $atts, sc_get( 'group/title', $atts, '' ) );
$testimonials = sc_get( 'testimonials', $atts, sc_get( 'group/testimonials', $atts, [] ) );
if ( ! is_array( $testimonials ) ) $testimonials = [];

/* Layout — Classic design only (moved into design_settings/default/*). */
$layout_choice   = $ts_dp( 'layout_type/layout_choice', 'layout_type/layout_choice', 'carousel' ); // carousel|grid|single
$grid_columns    = $ts_dp( 'layout_type/grid/grid_columns', 'layout_type/grid/grid_columns', 'row-cols-3' );
$gutter          = $ts_dp( 'layout_type/grid/gutter', 'gutter', '' );
$items_per_slide = (int) $ts_dp( 'items_per_slide', 'items_per_slide', 1 );
if ( $items_per_slide < 1 ) $items_per_slide = 1;

/* Cross-design appearance (stay top-level — no path change). */
$text_align      = sc_get( 'text_align', $atts, '' );
$container_cls   = sc_get( 'container_type', $atts, 'container' );
$container_cls   = $container_cls ?: 'container';
$avatar_shape    = sc_get( 'avatar_shape', $atts, 'rounded-circle' );
$avatar_size     = sc_get( 'avatar_size', $atts, 'avatar-md' );
$show_rating     = sc_get( 'show_rating', $atts, 'yes' ) === 'yes';

/* Style — Classic design only (moved into design_settings/default/*). */
$card_style       = $ts_dp( 'card_style', 'card_style', '' );
$show_avatar      = true; /* default unless explicitly hidden */
$avatar_position  = $ts_dp( 'avatar_position', 'avatar_position', 'top' ); // top|left|right|none
if ( $avatar_position === 'none' ) $show_avatar = false;

/* Avatar dimensions (custom utility classes expected in CSS) */
$avatar_dim_map = [ 'avatar-sm' => 64, 'avatar-md' => 96, 'avatar-lg' => 128 ];
$avatar_dim     = isset( $avatar_dim_map[ $avatar_size ] ) ? $avatar_dim_map[ $avatar_size ] : 96;

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
$container_cls = ( $container_cls === 'container-fluid' ) ? 'fw-container-fluid' : 'fw-container';
$grid_columns  = str_replace( 'row-cols-', 'fw-row-cols-', (string) $grid_columns );

/* Dispatch to the chosen design template (inherits all of the above). */
include $design_file;
