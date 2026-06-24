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
$title  = sc_get( 'title', $atts, sc_get( 'group/title', $atts, '' ) );
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

/* Cross-design appearance (top-level on the Style tab). */
$container_type = sc_get( 'container_type', $atts, '' );
$click_action   = sc_get( 'click_action', $atts, 'lightbox' );
$captions       = sc_get( 'captions', $atts, 'none' );
$caption_source = sc_get( 'caption_source', $atts, 'caption' );
$rounded        = sc_get( 'rounded', $atts, 'rounded' );
$hover_zoom      = sc_get( 'hover_zoom', $atts, 'yes' ) === 'yes';

/* Per-element color picks (kept off the wrapper). */
$title_styling   = sc_extract_styling_atts( $atts, array( 'title_color' ) );
$caption_styling = sc_extract_styling_atts( $atts, array( 'caption_color' ) );

$title_class_extra   = implode( ' ', $title_styling['classes'] );
$title_style_extra   = $title_styling['styles']   ? implode( '; ', $title_styling['styles'] )   : '';
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
