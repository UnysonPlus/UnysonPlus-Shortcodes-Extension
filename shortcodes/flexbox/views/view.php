<?php if (!defined('FW')) die('Forbidden');

/**
 * Flexbox — renders a semantic flex container. Children sit side-by-side (row) or
 * stacked (column) via CSS flexbox on this element, so it needs no Row/Column.
 *
 * @var array  $atts
 * @var string $content
 */

$atts['base_class']       = 'fw-flexbox';
$atts['unique_id_prefix'] = 'fx-';

$allowed_tags = array( 'div', 'section', 'header', 'main', 'article', 'aside', 'footer', 'nav' );
$tag = ( isset( $atts['html_tag'] ) && in_array( $atts['html_tag'], $allowed_tags, true ) ) ? $atts['html_tag'] : 'div';

$classes = array( 'd-flex' );

$dir       = ( isset( $atts['direction'] ) && $atts['direction'] === 'column' ) ? 'column' : 'row';
$rev       = ( isset( $atts['reverse'] ) && $atts['reverse'] === 'yes' ) ? '-reverse' : '';
$classes[] = ( $dir === 'row' ? 'flex-row' : 'flex-column' ) . $rev;
if ( ! isset( $atts['wrap'] ) || $atts['wrap'] !== 'no' ) {
	$classes[] = 'flex-wrap';
}

// Gap: a spacing-preset slug (Theme Settings → Default Gap). Resolve it to its
// CSS size and apply as the flex `gap` (added to the wrapper style below).
$gap_slug = isset( $atts['gap'] ) ? (string) $atts['gap'] : '';
$gap_size = '';
if ( $gap_slug !== '' && function_exists( 'unysonplus_get_gap_scale' ) && function_exists( 'sc_sanitize_class' ) ) {
	foreach ( unysonplus_get_gap_scale() as $g ) {
		if ( isset( $g['name'] ) && strtolower( sc_sanitize_class( $g['name'] ) ) === $gap_slug && ! empty( $g['size'] ) ) {
			$gap_size = $g['size'];
			break;
		}
	}
}

$jc = isset( $atts['justify_content'] ) ? (string) $atts['justify_content'] : '';
if ( in_array( $jc, array( 'start', 'center', 'end', 'between', 'around', 'evenly' ), true ) ) {
	$classes[] = 'justify-content-' . $jc;
}

$ai = isset( $atts['align_items'] ) ? (string) $atts['align_items'] : '';
if ( in_array( $ai, array( 'start', 'center', 'end', 'stretch', 'baseline' ), true ) ) {
	$classes[] = 'align-items-' . $ai;
}

// Align content (wrapped multi-line, container).
$ac = isset( $atts['align_content'] ) ? (string) $atts['align_content'] : '';
if ( in_array( $ac, array( 'start', 'center', 'end', 'between', 'around', 'stretch' ), true ) ) {
	$classes[] = 'align-content-' . $ac;
}

// Item-in-parent properties: align-self, order, grow.
$as = isset( $atts['align_self'] ) ? (string) $atts['align_self'] : '';
if ( in_array( $as, array( 'start', 'center', 'end', 'stretch', 'baseline' ), true ) ) {
	$classes[] = 'align-self-' . $as;
}

// Order (1…12 + first/last) via the frontend grid's fw-order-* classes — Bootstrap's
// own order-* only covers 0..5, so we use the same fw-order-* the Column uses.
$ord = isset( $atts['order'] ) ? (string) $atts['order'] : '';
if ( in_array( $ord, array( 'first', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', 'last' ), true ) ) {
	$classes[] = 'fw-order-' . $ord;
}

if ( isset( $atts['flex_grow'] ) && $atts['flex_grow'] === 'yes' ) {
	$classes[] = 'flex-grow-1';
}

// Border / Box Style preset (border + corners + shadow + bg fill). The boxp-* CSS
// is generated globally from the saved border presets, so we just add the class.
$bp = isset( $atts['border_preset'] ) ? (string) $atts['border_preset'] : '';
if ( $bp !== '' && preg_match( '/^boxp-[a-z0-9_-]+$/i', $bp ) ) {
	$classes[] = $bp;
}

// This container's OWN width (multi-picker): preset = none | 1..12 | custom; the
// "custom" preset carries an exact value in width.custom.width_custom.
$w_preset = 'none';
$w_custom = '';
if ( isset( $atts['width'] ) && is_array( $atts['width'] ) ) {
	$w_preset = isset( $atts['width']['preset'] ) ? (string) $atts['width']['preset'] : 'none';
	if ( $w_preset === 'custom' && isset( $atts['width']['custom']['width_custom'] )
	     && is_array( $atts['width']['custom']['width_custom'] ) ) {
		$wc      = $atts['width']['custom']['width_custom'];
		$wc_val  = isset( $wc['value'] ) ? preg_replace( '/[^0-9.\-]/', '', (string) $wc['value'] ) : '';
		$wc_unit = isset( $wc['unit'] ) && in_array( $wc['unit'], array( '%', 'px', 'rem', 'vw' ), true ) ? $wc['unit'] : '%';
		if ( $wc_val !== '' ) {
			$w_custom = $wc_val . $wc_unit;
		}
	}
}

$cols12 = array( '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12' );
if ( $w_custom === '' && in_array( $w_preset, $cols12, true ) ) {
	$classes[] = 'col-md-' . $w_preset; // base = tablet & up
}

// Phone override (mobile-first): below the md breakpoint, emit col-N. Default
// (empty) inherits — the element stacks full-width on phones, the usual behavior.
// Custom Width disables this (it is a single explicit value).
if ( $w_custom === '' ) {
	$w_phone = isset( $atts['width_phone'] ) ? (string) $atts['width_phone'] : '';
	if ( in_array( $w_phone, $cols12, true ) ) {
		$classes[] = 'col-' . $w_phone;
	}
}

$atts['css_class'] = trim( ( isset( $atts['css_class'] ) ? $atts['css_class'] : '' ) . ' ' . implode( ' ', $classes ) );

// sc_build_wrapper_attr auto-applies base_class + unique class + bg_color/spacing
// (Styling) + animation + Advanced (css_id, custom attrs) via its filters.
$attr = sc_build_wrapper_attr( $atts );

// Min height (exact value, e.g. 60vh) — for vertical centring with align-items.
$min_h = '';
if ( isset( $atts['min_height'] ) && is_array( $atts['min_height'] )
     && isset( $atts['min_height']['value'] ) && trim( (string) $atts['min_height']['value'] ) !== '' ) {
	$mh_val  = preg_replace( '/[^0-9.\-]/', '', (string) $atts['min_height']['value'] );
	$mh_unit = isset( $atts['min_height']['unit'] ) && in_array( $atts['min_height']['unit'], array( 'vh', 'px', 'rem', '%' ), true )
		? $atts['min_height']['unit'] : 'vh';
	if ( $mh_val !== '' ) {
		$min_h = $mh_val . $mh_unit;
	}
}

// Flex gap (resolved preset size) + custom width + min height — appended to any
// spacing-driven style sc_build_wrapper_attr already produced.
$extra_style = '';
if ( $gap_size !== '' ) {
	$extra_style .= 'gap:' . $gap_size . ';';
}
if ( $w_custom !== '' ) {
	$extra_style .= 'flex:0 0 ' . $w_custom . ';max-width:' . $w_custom . ';';
}
if ( $min_h !== '' ) {
	$extra_style .= 'min-height:' . $min_h . ';';
}
if ( $extra_style !== '' ) {
	$existing      = isset( $attr['style'] ) && $attr['style'] !== '' ? rtrim( $attr['style'], '; ' ) . '; ' : '';
	$attr['style'] = $existing . $extra_style;
}

echo '<' . $tag . ' ' . fw_attr_to_html( $attr ) . '>';
echo do_shortcode( $content );
echo '</' . $tag . '>';
