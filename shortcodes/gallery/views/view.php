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

/* Helper getter (shared with other shortcodes when both render on a page). */
if ( ! function_exists( 'sc_get' ) ) {
	function sc_get( $path, $atts, $default = '' ) {
		if ( function_exists( 'fw_akg' ) ) {
			$v = fw_akg( $path, $atts, null );
			if ( $v !== null ) return $v;
		}
		return $default;
	}
}

/* Content */
$images = sc_get( 'images', $atts, sc_get( 'group/images', $atts, array() ) );
if ( ! is_array( $images ) ) {
	$images = array();
}

/* Resolve the chosen design (safe: defaults to 'grid' for legacy/missing). */
$g_designs = require dirname( __FILE__ ) . '/designs/registry.php';
$design    = sc_get( 'design_settings/design', $atts, sc_get( 'design', $atts, 'grid' ) );
if ( ! is_string( $design ) || ! isset( $g_designs[ $design ] ) ) {
	$design = 'grid';
}
$design_file = dirname( __FILE__ ) . '/designs/' . $design . '.php';
if ( ! file_exists( $design_file ) ) {
	$design      = 'grid';
	$design_file = dirname( __FILE__ ) . '/designs/grid.php';
}

/* Per-design option reader: design_settings/<design>/<sub>, with a default. */
$g_dp = function ( $sub, $default ) use ( $atts, $design ) {
	return sc_get( 'design_settings/' . $design . '/' . $sub, $atts, $default );
};

/*
 * Resolve the Columns control into flat per-device counts + the optional grid
 * Column-Ratio. The builder only exposes the DESKTOP count; tablet & phone are
 * FIXED automatically (tablet = min(desktop, 2), phone = 1). Tolerates every
 * historical shape so no gallery needs migrating:
 *   1. new grid   → columns = { count:'N', 'N':{ col_ratio } }
 *   2. new others → columns = 'N' (scalar)
 *   3. legacy     → columns = 'N' + sibling columns_tablet / columns_mobile / col_ratio (honoured if present)
 *   4. absent     → the passed default.
 * Returns array( desktop, tablet, mobile, col_ratio ).
 */
$g_cols = function ( $d = 3 ) use ( $g_dp ) {
	$raw = $g_dp( 'columns', null );

	if ( is_array( $raw ) ) { // new grid multi-picker shape
		$count  = isset( $raw['count'] ) ? max( 1, (int) $raw['count'] ) : (int) $d;
		$reveal = ( isset( $raw[ (string) $count ] ) && is_array( $raw[ (string) $count ] ) ) ? $raw[ (string) $count ] : array();
		$ratio  = ( isset( $reveal['col_ratio'] ) && is_array( $reveal['col_ratio'] ) ) ? $reveal['col_ratio'] : array();
	} else { // scalar count (new non-grid, legacy, or absent)
		$count = ( $raw !== null && $raw !== '' ) ? max( 1, (int) $raw ) : (int) $d;
		$ratio = (array) $g_dp( 'col_ratio', array() ); // legacy sibling
	}

	// Auto responsive rule: phone = 1 column; tablet = desktop − 1 for large grids
	// (5–6 columns) so they only step down by one, else capped at 2. A legacy per-device
	// save (columns_tablet / columns_mobile) still wins when present.
	//   desktop → tablet:  1→1  2→2  3→2  4→2  5→4  6→5   (phone always 1)
	$auto_tablet = ( $count >= 5 ) ? ( $count - 1 ) : min( $count, 2 );
	$leg_t  = $g_dp( 'columns_tablet', null );
	$leg_m  = $g_dp( 'columns_mobile', null );
	$tablet = ( $leg_t !== null && $leg_t !== '' ) ? max( 1, (int) $leg_t ) : $auto_tablet;
	$mobile = ( $leg_m !== null && $leg_m !== '' ) ? max( 1, (int) $leg_m ) : 1;

	return array(
		'desktop'   => $count,
		'tablet'    => $tablet,
		'mobile'    => $mobile,
		'col_ratio' => $ratio,
	);
};

/* Cross-design appearance (top-level on the Style tab). */
$container_type = sc_get( 'container_type', $atts, '' );
$click_action   = sc_get( 'click_action', $atts, 'lightbox' );
$captions       = sc_get( 'captions', $atts, 'none' );
$caption_source = sc_get( 'caption_source', $atts, 'caption' );
$rounded        = sc_get( 'rounded', $atts, 'rounded' );
$hover_zoom      = sc_get( 'hover_zoom', $atts, 'yes' ) === 'yes';

/* Unified card control (Style tab): a Box Preset class on each card <figure>. */
$box_style      = sc_get( 'box_style', $atts, '' );
$box_style      = ( is_string( $box_style ) && preg_match( '/^boxp-[a-z0-9_-]+$/i', $box_style ) ) ? $box_style : '';

/* Per-card hover: when a Hover Interaction (Animations tab) is added to this Gallery, the engine
 * returns the class/attrs to stamp on EACH card — so it lands per card, not on the whole grid
 * (which would tilt/animate as one block). Empty array when no hover / engine inactive. */
$hover_item     = function_exists( 'upw_hover_collection_item_attr' ) ? upw_hover_collection_item_attr( $atts ) : array();

/* Per-element color picks (kept off the wrapper). */
$caption_styling = sc_extract_styling_atts( $atts, array( 'caption_color' ) );

$caption_class_extra = implode( ' ', $caption_styling['classes'] );
$caption_style_extra = $caption_styling['styles'] ? implode( '; ', $caption_styling['styles'] ) : '';

/* Normalize images into render-ready items. */
$items = sc_gallery_get_items( $images, 'large' );

/* A unique lightbox group id for this instance (scopes prev/next to it). */
$lightbox_group = function_exists( 'wp_unique_id' ) ? wp_unique_id( 'fw-gal-' ) : uniqid( 'fw-gal-' );

/* Shared tile args every design passes to sc_gallery_render_tile(). */
$tile_args = array(
	'click_action'   => $click_action,
	'group'          => $lightbox_group,
	'captions'       => $captions,
	'caption_source' => $caption_source,
	'rounded'        => $rounded,
	'hover_zoom'     => $hover_zoom,
	'caption_class'  => $caption_class_extra,
	'caption_style'  => $caption_style_extra,
	'box_style'      => $box_style,
	'item_hover'     => $hover_item,
);

/* Wrapper base (+ design-<key> hook for per-design CSS scoping). */
$atts['base_class']       = 'fw-gallery';
$atts['unique_id_prefix'] = 'gal-';
$attr          = sc_build_wrapper_attr( $atts );
$attr['class'] = trim( ( isset( $attr['class'] ) ? $attr['class'] : '' ) . ' design-' . $design );

/* Container class (self-contained .fw- grid; the plugin no longer ships Bootstrap). */
$container_cls = '';
if ( $container_type === 'container' ) {
	$container_cls = 'fw-container';
} elseif ( $container_type === 'container-fluid' ) {
	$container_cls = 'fw-container-fluid';
}

/* Nothing to render — bail (but keep an editor-friendly note in the builder). */
if ( empty( $items ) ) {
	if ( is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
		echo '<div ' . fw_attr_to_html( $attr ) . '><div class="fw-gallery__empty">'
			. esc_html__( 'No images added yet.', 'fw' ) . '</div></div>';
	}
	return;
}

/* Dispatch to the chosen design template (inherits all of the above). */
include $design_file;
