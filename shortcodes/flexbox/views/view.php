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

// Display mode: Flex (default) / Grid / Block. Flex keeps the d-flex utility class; Grid and
// Block set their display (+ grid template-columns) via a scoped rule keyed to the fx- class
// (below), so existing flexboxes are unchanged and the flex utility classes stay inert on them.
$display  = ( isset( $atts['display'] ) && in_array( $atts['display'], array( 'flex', 'grid', 'block' ), true ) ) ? $atts['display'] : 'flex';

// Is THIS box a direct child of a Grid Div? A parent flexbox pushes its display onto this stack
// before rendering its children (see the render at the end), so the top is our immediate parent's
// display. When it's a grid, the tracks size us and we drop any CUSTOM width below (no redundant
// per-element rule) — a twelfths preset is kept, since in a grid its fw-span class is the span.
$parent_is_grid = ( ! empty( $GLOBALS['_fw_flex_stack'] ) && end( $GLOBALS['_fw_flex_stack'] ) === 'grid' );

// Only a Flex Div carries the flex utility classes (d-flex + direction/wrap below); a Grid
// Div carries the `fw-grid` marker (its children's fw-col-* map to grid spans via CSS); a
// Block Div carries neither — keeping its markup clean instead of littered with inert classes.
$classes = ( $display === 'flex' ) ? array( 'fw-flex' ) : ( ( $display === 'grid' ) ? array( 'fw-grid' ) : array() );

// --- Responsive helper: read a { base, md, lg } value (tolerating a legacy scalar).
// The parsing lives in the shared fw_sc_resp_value() (shortcodes helpers.php) so
// section / flexbox / column share ONE reader; this thin alias keeps the local call
// sites terse. $fx_layers is the shared breakpoint → class-infix map. ---
$fx_resp   = function ( $key ) use ( $atts ) { return fw_sc_resp_value( $atts, $key ); };
$fx_layers = fw_sc_bp_layers();

// --- Effective mobile-first cascade for a per-device switch { base, md, lg } (each
// 'yes'/'no'/''; a blank device inherits the smaller one). Tolerates a legacy scalar.
// Returns array( base, md, lg ) fully resolved to 'yes'/'no'. ---
$fx_switch_eff = function ( $key, $default ) use ( $atts ) {
	$v = fw_akg( $key, $atts, null );
	if ( is_array( $v ) ) {
		$resp = array(
			'base' => isset( $v['base'] ) ? (string) $v['base'] : $default,
			'md'   => isset( $v['md'] )   ? (string) $v['md']   : '',
			'lg'   => isset( $v['lg'] )   ? (string) $v['lg']   : '',
		);
	} else {
		$resp = array( 'base' => in_array( (string) $v, array( 'yes', 'no' ), true ) ? (string) $v : $default, 'md' => '', 'lg' => '' );
	}
	$out  = array();
	$prev = $default;
	foreach ( array( 'base', 'md', 'lg' ) as $L ) {
		$x = in_array( $resp[ $L ], array( 'yes', 'no' ), true ) ? $resp[ $L ] : ( $L === 'base' ? $default : $prev );
		$out[ $L ] = $x;
		$prev = $x;
	}
	return $out;
};

// Direction + Reverse — both per-device. Effective direction/reverse cascade
// mobile-first (a blank device inherits the smaller one); a device emits a class when
// its direction OR its reverse differs from the device below it. Legacy: flat direction
// + direction_mobile/tablet migrate to { base, md, lg }; a scalar reverse folds to base.
$dir_v = fw_akg( 'direction', $atts, null );
if ( is_array( $dir_v ) ) {
	$dir_resp = array(
		'base' => isset( $dir_v['base'] ) ? (string) $dir_v['base'] : 'row',
		'md'   => isset( $dir_v['md'] )   ? (string) $dir_v['md']   : '',
		'lg'   => isset( $dir_v['lg'] )   ? (string) $dir_v['lg']   : '',
	);
} else {
	$d_base = ( $dir_v === 'column' ) ? 'column' : 'row';
	$d_mob  = (string) fw_akg( 'direction_mobile', $atts, '' );
	$d_tab  = (string) fw_akg( 'direction_tablet', $atts, '' );
	$dir_resp = array(
		'base' => in_array( $d_mob, array( 'row', 'column' ), true ) ? $d_mob : $d_base,
		'md'   => in_array( $d_tab, array( 'row', 'column' ), true ) ? $d_tab : $d_base,
		'lg'   => $d_base,
	);
}
$rev_v = fw_akg( 'reverse', $atts, null );
if ( is_array( $rev_v ) ) {
	$rev_resp = array(
		'base' => isset( $rev_v['base'] ) ? (string) $rev_v['base'] : 'no',
		'md'   => isset( $rev_v['md'] )   ? (string) $rev_v['md']   : '',
		'lg'   => isset( $rev_v['lg'] )   ? (string) $rev_v['lg']   : '',
	);
} else {
	$rev_resp = array( 'base' => ( $rev_v === 'yes' ) ? 'yes' : 'no', 'md' => '', 'lg' => '' );
}
$dir_eff = array();
$rev_eff = array();
$prev_d  = 'row';
$prev_r  = 'no';
foreach ( array( 'base', 'md', 'lg' ) as $layer ) {
	$d = in_array( $dir_resp[ $layer ], array( 'row', 'column' ), true ) ? $dir_resp[ $layer ] : ( $layer === 'base' ? 'row' : $prev_d );
	$r = in_array( $rev_resp[ $layer ], array( 'yes', 'no' ), true )      ? $rev_resp[ $layer ] : ( $layer === 'base' ? 'no'  : $prev_r );
	$dir_eff[ $layer ] = $d;
	$rev_eff[ $layer ] = $r;
	$prev_d = $d;
	$prev_r = $r;
}
$fx_dir_class = function ( $bp, $d, $r ) {
	return 'fw-flex-' . ( $bp === '' ? '' : $bp . '-' ) . ( $d === 'column' ? 'column' : 'row' ) . ( $r === 'yes' ? '-reverse' : '' );
};
// flex-direction / -reverse are flex-only container props — emit them just for a Flex Div
// (inert under grid / block, so we leave them off there to keep the markup clean).
if ( $display === 'flex' ) {
	// Skip the base class for plain `row` — flex-direction:row is already the CSS default, so the
	// class would be noise on every default Div. Emit only a non-default base (column / reverse)
	// and any per-device override.
	if ( $dir_eff['base'] !== 'row' || $rev_eff['base'] !== 'no' ) { $classes[] = $fx_dir_class( '', $dir_eff['base'], $rev_eff['base'] ); }
	if ( $dir_eff['md'] !== $dir_eff['base'] || $rev_eff['md'] !== $rev_eff['base'] ) { $classes[] = $fx_dir_class( 'md', $dir_eff['md'], $rev_eff['md'] ); }
	if ( $dir_eff['lg'] !== $dir_eff['md']  || $rev_eff['lg'] !== $rev_eff['md'] )  { $classes[] = $fx_dir_class( 'lg', $dir_eff['lg'], $rev_eff['lg'] ); }
}

// Wrap (per-device switch, default on). Emit a class at base always, and at md/lg
// only when it differs from the device below (flex-{bp}-wrap / flex-{bp}-nowrap).
$wrap_eff = $fx_switch_eff( 'wrap', 'yes' );
$wrap_cls = function ( $bp, $w ) { return 'fw-flex-' . ( $bp === '' ? '' : $bp . '-' ) . ( $w === 'no' ? 'nowrap' : 'wrap' ); };
if ( $display === 'flex' ) {
	// The base `.fw-flex` class already sets flex-wrap:wrap (the Div's default), so emit a base
	// class ONLY to turn wrapping OFF (fw-flex-nowrap); a per-device change still emits its class.
	if ( $wrap_eff['base'] === 'no' ) { $classes[] = $wrap_cls( '', 'no' ); }
	if ( $wrap_eff['md'] !== $wrap_eff['base'] ) { $classes[] = $wrap_cls( 'md', $wrap_eff['md'] ); }
	if ( $wrap_eff['lg'] !== $wrap_eff['md'] )  { $classes[] = $wrap_cls( 'lg', $wrap_eff['lg'] ); }
}

// Gap (per-device): a spacing-preset slug → sc-cgap-{slug} utility (which sets the
// flex `gap` via var(--gap-{slug}); generated in css-tokens.php), one per device.
$gap_r = $fx_resp( 'gap' );
foreach ( $fx_layers as $layer => $infix ) {
	$gslug = function_exists( 'sc_sanitize_class' )
		? strtolower( sc_sanitize_class( (string) $gap_r[ $layer ] ) )
		: preg_replace( '/[^a-z0-9_-]/i', '', strtolower( (string) $gap_r[ $layer ] ) );
	if ( $gslug !== '' ) { $classes[] = 'fw-gap' . $infix . '-' . $gslug; }
}

// Justify content (per-device, main axis). Legacy justify_content_mobile/tablet migrate.
$jc_valid = array( 'start', 'center', 'end', 'between', 'around', 'evenly' );
$jc_v     = fw_akg( 'justify_content', $atts, null );
if ( is_array( $jc_v ) ) {
	$jc_resp = array(
		'base' => isset( $jc_v['base'] ) ? (string) $jc_v['base'] : '',
		'md'   => isset( $jc_v['md'] )   ? (string) $jc_v['md']   : '',
		'lg'   => isset( $jc_v['lg'] )   ? (string) $jc_v['lg']   : '',
	);
} else {
	$j_base = (string) $jc_v;
	$j_mob  = (string) fw_akg( 'justify_content_mobile', $atts, '' );
	$j_tab  = (string) fw_akg( 'justify_content_tablet', $atts, '' );
	if ( in_array( $j_mob, $jc_valid, true ) || in_array( $j_tab, $jc_valid, true ) ) {
		$norm    = function ( $v ) use ( $jc_valid ) { return in_array( $v, $jc_valid, true ) ? $v : 'start'; };
		$jc_resp = array(
			'base' => $norm( in_array( $j_mob, $jc_valid, true ) ? $j_mob : $j_base ),
			'md'   => $norm( in_array( $j_tab, $jc_valid, true ) ? $j_tab : $j_base ),
			'lg'   => $norm( $j_base ),
		);
	} else {
		$jc_resp = array( 'base' => $j_base, 'md' => '', 'lg' => '' );
	}
}
foreach ( $fx_layers as $layer => $infix ) {
	if ( in_array( $jc_resp[ $layer ], $jc_valid, true ) ) { $classes[] = 'fw-justify' . $infix . '-' . $jc_resp[ $layer ]; }
}

// Align items (per-device, cross axis).
$ai_valid = array( 'start', 'center', 'end', 'stretch', 'baseline' );
$ai_r     = $fx_resp( 'align_items' );
foreach ( $fx_layers as $layer => $infix ) {
	if ( in_array( $ai_r[ $layer ], $ai_valid, true ) ) { $classes[] = 'fw-items' . $infix . '-' . $ai_r[ $layer ]; }
}

// Align content (per-device, wrapped multi-line container). Empty = default (no class).
$ac_valid = array( 'start', 'center', 'end', 'between', 'around', 'stretch' );
$ac_r     = $fx_resp( 'align_content' );
foreach ( $fx_layers as $layer => $infix ) {
	if ( in_array( $ac_r[ $layer ], $ac_valid, true ) ) { $classes[] = 'fw-content' . $infix . '-' . $ac_r[ $layer ]; }
}

// Item-in-parent properties: align-self, order, grow — align-self & order per-device.
$as_valid = array( 'start', 'center', 'end', 'stretch', 'baseline' );
$as_r     = $fx_resp( 'align_self' );
foreach ( $fx_layers as $layer => $infix ) {
	if ( in_array( $as_r[ $layer ], $as_valid, true ) ) { $classes[] = 'fw-self' . $infix . '-' . $as_r[ $layer ]; }
}

// Order (per-device) via the frontend grid's fw-order-* classes (first / 1…12 / last).
$ord_valid = array( 'first', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', 'last' );
$ord_r     = $fx_resp( 'order' );
foreach ( $fx_layers as $layer => $infix ) {
	if ( in_array( $ord_r[ $layer ], $ord_valid, true ) ) { $classes[] = 'fw-order' . $infix . '-' . $ord_r[ $layer ]; }
}

// Grow to Fill (per-device switch, default off). Base emits only when on; md/lg emit
// (flex-grow-{bp}-1 / -0) only when they differ from the device below.
$grow_eff = $fx_switch_eff( 'flex_grow', 'no' );
$grow_cls = function ( $bp, $g ) { return 'fw-grow-' . ( $bp === '' ? '' : $bp . '-' ) . ( $g === 'yes' ? '1' : '0' ); };
if ( $grow_eff['base'] === 'yes' ) { $classes[] = $grow_cls( '', 'yes' ); }
if ( $grow_eff['md'] !== $grow_eff['base'] ) { $classes[] = $grow_cls( 'md', $grow_eff['md'] ); }
if ( $grow_eff['lg'] !== $grow_eff['md'] )  { $classes[] = $grow_cls( 'lg', $grow_eff['lg'] ); }

// Prevent Shrinking (switch, default off). On -> flex-shrink-0 so the box keeps its size
// in a Flex row instead of being squeezed (a fixed sidebar / logo beside a flexible area).
if ( (string) fw_akg( 'no_shrink', $atts, 'no' ) === 'yes' ) { $classes[] = 'fw-shrink-0'; }

// Border / Box Style preset (border + corners + shadow + bg fill). The boxp-* CSS
// is generated globally from the saved border presets, so we just add the class.
$bp = isset( $atts['border_preset'] ) ? (string) $atts['border_preset'] : '';
if ( $bp !== '' && preg_match( '/^boxp-[a-z0-9_-]+$/i', $bp ) ) {
	$classes[] = $bp;
}

// ----- Width Override (responsive: none | 1…12 fractions | custom, per device) -----
// Fractions emit the plugin's own fw-col-{bp}-{v} grid classes (self-contained; the
// canvas width handle sets the same base value). Custom exact widths can't be a
// utility class, so they are applied as scoped @media rules further below. Legacy
// shapes migrate: the old flat width{preset} applied at tablet-up (→ md) and the
// retired width_phone → base.
$fx_frac    = array( '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12' );
$fx_w_layer = function ( $L ) {
	$out = array( 'preset' => '', 'custom' => '', 'fifth' => '', 'keyword' => '' );
	if ( is_array( $L ) ) {
		$out['preset'] = isset( $L['preset'] ) ? (string) $L['preset'] : '';
		$p = $out['preset'];
		// Fifths (1/5…4/5) aren't clean twelfths, so they emit a SHARED fw-fifth-N class (N = the
		// numerator) — no per-cell width <style>. Content keywords size the box to its content
		// (intrinsic sizing) and still go through the scoped-width path.
		$fifths = array( '1_5' => 1, '2_5' => 2, '3_5' => 3, '4_5' => 4 );
		$kw     = array( 'fit' => 'fit-content', 'max' => 'max-content', 'min' => 'min-content' );
		if ( isset( $fifths[ $p ] ) ) {
			$out['fifth'] = $fifths[ $p ];
		} elseif ( isset( $kw[ $p ] ) ) {
			$out['keyword'] = $p;
		} elseif ( $p === 'custom' && isset( $L['custom']['width_custom'] ) && is_array( $L['custom']['width_custom'] ) ) {
			$wc   = $L['custom']['width_custom'];
			$val  = isset( $wc['value'] ) ? preg_replace( '/[^0-9.\-]/', '', (string) $wc['value'] ) : '';
			$unit = ( isset( $wc['unit'] ) && in_array( $wc['unit'], array( '%', 'px', 'rem', 'vw' ), true ) ) ? $wc['unit'] : '%';
			if ( $val !== '' ) {
				// SNAP: an exact fraction stored as a custom % (a legacy page saved e.g. 20% or 16.66%
				// before the class existed) maps to its shared class — identical width, clean output.
				// A fifth first (20/40/60/80), then a twelfth; anything else stays a scoped custom.
				$snapped = false;
				if ( $unit === '%' ) {
					$fv = (float) $val;
					foreach ( array( 20 => 1, 40 => 2, 60 => 3, 80 => 4 ) as $pp => $nn ) {
						if ( abs( $fv - $pp ) < 0.25 ) { $out['fifth'] = $nn; $snapped = true; break; }
					}
					if ( ! $snapped ) {
						for ( $nn = 1; $nn <= 12; $nn++ ) {
							if ( abs( $fv - ( $nn / 12 * 100 ) ) < 0.25 ) { $out['preset'] = (string) $nn; $snapped = true; break; }
						}
					}
				}
				if ( ! $snapped ) { $out['custom'] = $val . $unit; }
			}
		}
	}
	return $out;
};
$w_raw = fw_akg( 'width', $atts, null );
// A Section-tag Div is always a full-width band (like the classic Section) — ignore any stored
// Width so it can never render narrower / left-aligned. Constrain its content with Content Width
// (which centres a full-width band) instead. The Width control is also hidden in the editor.
if ( $tag === 'section' ) { $w_raw = null; }
if ( is_array( $w_raw ) && ( isset( $w_raw['base'] ) || isset( $w_raw['md'] ) || isset( $w_raw['lg'] ) ) ) {
	$wl = array(
		'base' => $fx_w_layer( isset( $w_raw['base'] ) ? $w_raw['base'] : array() ),
		'md'   => $fx_w_layer( isset( $w_raw['md'] )   ? $w_raw['md']   : array() ),
		'lg'   => $fx_w_layer( isset( $w_raw['lg'] )   ? $w_raw['lg']   : array() ),
	);
} else {
	// Legacy flat width{preset,custom} applied tablet-up (md); width_phone → base.
	$flat = $fx_w_layer( is_array( $w_raw ) ? $w_raw : array() );
	$wp   = (string) fw_akg( 'width_phone', $atts, '' );
	$wl   = array(
		'base' => array( 'preset' => in_array( $wp, $fx_frac, true ) ? $wp : '', 'custom' => '', 'fifth' => '', 'keyword' => '' ),
		'md'   => $flat,
		'lg'   => array( 'preset' => '', 'custom' => '', 'fifth' => '', 'keyword' => '' ),
	);
}
// Grid cell: the parent's tracks size it, so drop any width that would be a redundant per-element
// rule fighting the grid (a custom %, a fifth, or a content keyword). This cleans equal-grid cells
// on EXISTING pages with no data change; a twelfths preset survives and becomes the grid-column
// span via its fw-span class.
if ( $parent_is_grid ) {
	$wl['base']['custom']  = $wl['md']['custom']  = $wl['lg']['custom']  = '';
	$wl['base']['fifth']   = $wl['md']['fifth']   = $wl['lg']['fifth']   = '';
	$wl['base']['keyword'] = $wl['md']['keyword'] = $wl['lg']['keyword'] = '';
}
$w_is_set = function ( $L ) use ( $fx_frac ) {
	// A twelfths fw-span, a fifth (fw-fifth), a content keyword (fw-w-*), OR a scoped custom value.
	return in_array( $L['preset'], $fx_frac, true ) || $L['fifth'] !== '' || $L['keyword'] !== '' || $L['custom'] !== '';
};
foreach ( $fx_layers as $layer => $infix ) {
	if ( in_array( $wl[ $layer ]['preset'], $fx_frac, true ) ) {
		$classes[] = 'fw-span' . $infix . '-' . $wl[ $layer ]['preset'];
	} elseif ( $wl[ $layer ]['fifth'] !== '' ) {
		$classes[] = 'fw-fifth' . $infix . '-' . $wl[ $layer ]['fifth'];
	} elseif ( $wl[ $layer ]['keyword'] !== '' ) {
		$classes[] = 'fw-w' . $infix . '-' . $wl[ $layer ]['keyword'];
	}
}

$atts['css_class'] = trim( ( isset( $atts['css_class'] ) ? $atts['css_class'] : '' ) . ' ' . implode( ' ', $classes ) );

// --- Background (background-pro): new value, else migrated legacy bg_color. ---
// The Styling tab's old compact Background Color (bg_color) was replaced by the full
// background-pro control. Migrate an existing bg_color into a background-pro color layer so
// legacy flexboxes keep their colour: bg_color's `predefined` is a slug (e.g. "bg-red"), so
// resolve it to var(--color-{slug}) (bg-pro treats the color value as concrete CSS).
if ( ! function_exists( 'flexbox_migrate_bg_color' ) ) {
	function flexbox_migrate_bg_color( $atts ) {
		$bgc   = isset( $atts['bg_color'] ) ? $atts['bg_color'] : '';
		$color = '';
		if ( is_array( $bgc ) ) {
			if ( ! empty( $bgc['custom'] ) ) {
				$color = (string) $bgc['custom'];
			} elseif ( ! empty( $bgc['predefined'] ) ) {
				$slug = preg_replace( '/^(?:bg|text)-/', '', (string) $bgc['predefined'] );
				if ( $slug !== '' ) { $color = 'var(--color-' . $slug . ')'; }
			}
		} elseif ( is_string( $bgc ) && $bgc !== '' ) {
			$color = $bgc;
		}
		if ( $color === '' ) { return array(); }
		return array( 'color' => array( 'value' => array( 'predefined' => '', 'custom' => $color ) ) );
	}
}
$bgv = ( ! empty( $atts['background'] ) && is_array( $atts['background'] ) )
	? $atts['background']
	: flexbox_migrate_bg_color( $atts );
// Rendered here (below) — drop bg_color so the styling filter doesn't also apply it.
unset( $atts['bg_color'] );

// sc_build_wrapper_attr auto-applies base_class + unique class + spacing (Styling)
// + animation + Advanced (css_id, custom attrs) via its filters.
$attr = sc_build_wrapper_attr( $atts );

// Min height (per-device) — for vertical centring with align-items. Emitted as scoped
// @media rules below (mobile-first: base, then md ≥768, lg ≥992). Legacy flat
// { value, unit } folds into base. A blank device inherits the smaller one.
$fx_mh_layer = function ( $L ) {
	if ( is_array( $L ) && isset( $L['value'] ) && trim( (string) $L['value'] ) !== '' ) {
		$v = preg_replace( '/[^0-9.\-]/', '', (string) $L['value'] );
		$u = ( isset( $L['unit'] ) && in_array( $L['unit'], array( 'vh', 'px', 'rem', '%' ), true ) ) ? $L['unit'] : 'vh';
		if ( $v !== '' ) { return $v . $u; }
	}
	return '';
};
$mh_raw = fw_akg( 'min_height', $atts, null );
if ( is_array( $mh_raw ) && ( isset( $mh_raw['base'] ) || isset( $mh_raw['md'] ) || isset( $mh_raw['lg'] ) ) ) {
	$mh = array(
		'base' => $fx_mh_layer( isset( $mh_raw['base'] ) ? $mh_raw['base'] : array() ),
		'md'   => $fx_mh_layer( isset( $mh_raw['md'] )   ? $mh_raw['md']   : array() ),
		'lg'   => $fx_mh_layer( isset( $mh_raw['lg'] )   ? $mh_raw['lg']   : array() ),
	);
} else {
	$mh = array( 'base' => $fx_mh_layer( is_array( $mh_raw ) ? $mh_raw : array() ), 'md' => '', 'lg' => '' );
}

// Background-pro layers (color / gradient / image) — sits UNDER gap/width/min-height so it
// composes with the spacing-driven style sc_build_wrapper_attr already produced.
$bg_style = function_exists( 'sc_bg_pro_style' ) ? sc_bg_pro_style( $bgv ) : '';

// (Flex gap rides on sc-cgap-* classes; width fractions on fw-col-* classes; Custom
// width + per-device Min Height are scoped @media rules below, since they can't be
// utility classes.)
$extra_style = $bg_style;
if ( $extra_style !== '' ) {
	$existing      = isset( $attr['style'] ) && $attr['style'] !== '' ? rtrim( $attr['style'], '; ' ) . '; ' : '';
	$attr['style'] = $existing . $extra_style;
}

// Custom Width + per-device Min Height → scoped @media rules keyed to this flexbox's
// unique fx-* class. Custom width is bounded mobile-first so a smaller custom does not
// bleed past a larger override; min-height layers just cascade (later wins).
$w_custom_css = '';
$fx_uid = ( isset( $attr['class'] ) && preg_match( '/\bfx-[a-z0-9]+\b/i', (string) $attr['class'], $um ) ) ? '.' . $um[0] : '';
if ( $fx_uid !== ''
     && ( $wl['base']['custom'] !== '' || $wl['md']['custom'] !== '' || $wl['lg']['custom'] !== ''
       || $mh['base'] !== '' || $mh['md'] !== '' || $mh['lg'] !== '' ) ) {
	$uid    = $fx_uid;
	$md_set = $w_is_set( $wl['md'] );
	$lg_set = $w_is_set( $wl['lg'] );
	$decl   = function ( $v ) {
		// Content keywords size the box to its content — set `width` and release the fixed-basis
		// cap; everything else (a %, px, rem, vw, or a fifth) is a fixed flex-basis + max-width.
		if ( in_array( $v, array( 'fit-content', 'max-content', 'min-content' ), true ) ) {
			return 'flex:0 0 auto !important;width:' . $v . ' !important;max-width:none !important;';
		}
		return 'flex:0 0 ' . $v . ' !important;max-width:' . $v . ' !important;';
	};
	if ( $wl['base']['custom'] !== '' ) {
		if ( $md_set )     { $w_custom_css .= '@media (max-width:767.98px){' . $uid . '{' . $decl( $wl['base']['custom'] ) . '}}'; }
		elseif ( $lg_set ) { $w_custom_css .= '@media (max-width:991.98px){' . $uid . '{' . $decl( $wl['base']['custom'] ) . '}}'; }
		else               { $w_custom_css .= $uid . '{' . $decl( $wl['base']['custom'] ) . '}'; }
	}
	if ( $wl['md']['custom'] !== '' ) {
		$w_custom_css .= $lg_set
			? '@media (min-width:768px) and (max-width:991.98px){' . $uid . '{' . $decl( $wl['md']['custom'] ) . '}}'
			: '@media (min-width:768px){' . $uid . '{' . $decl( $wl['md']['custom'] ) . '}}';
	}
	if ( $wl['lg']['custom'] !== '' ) {
		$w_custom_css .= '@media (min-width:992px){' . $uid . '{' . $decl( $wl['lg']['custom'] ) . '}}';
	}
	if ( $mh['base'] !== '' ) { $w_custom_css .= $uid . '{min-height:' . $mh['base'] . ';}'; }
	if ( $mh['md'] !== '' )   { $w_custom_css .= '@media (min-width:768px){' . $uid . '{min-height:' . $mh['md'] . ';}}'; }
	if ( $mh['lg'] !== '' )   { $w_custom_css .= '@media (min-width:992px){' . $uid . '{min-height:' . $mh['lg'] . ';}}'; }
}

// Grid item span: a fraction Width doubles as a CSS Grid span when this box is a child of a
// Grid Div. That mapping now lives in static CSS (frontend-grid.css: `.fw-grid > .fw-col-N
// { grid-column: span N }`, keyed to the parent's `fw-grid` marker), so nothing is emitted
// per element here — the child just keeps its fw-col-* class. 12-track model: 1/3 = span 4,
// 2/3 = span 8; use a 12-column Grid parent so 1/3 + 2/3 = 4 + 8 tiles exactly.

// Row Gap / Column Gap overrides (per-device): a blank device inherits the single Gap. They
// reuse the same --gap-{slug} vars the Gap utility (sc-cgap-*) uses, emitted as scoped rules
// keyed to this box's fx-* class (mobile-first: base, then md >=768, lg >=992).
if ( $fx_uid !== '' ) {
	$fx_gap_slug = function ( $v ) {
		return function_exists( 'sc_sanitize_class' )
			? strtolower( sc_sanitize_class( (string) $v ) )
			: preg_replace( '/[^a-z0-9_-]/i', '', strtolower( (string) $v ) );
	};
	$fx_axis_gap = function ( $prop, $resp ) use ( $fx_uid, $fx_gap_slug, $fx_layers ) {
		$css = '';
		foreach ( $fx_layers as $layer => $infix ) {
			$slug = $fx_gap_slug( isset( $resp[ $layer ] ) ? $resp[ $layer ] : '' );
			if ( $slug === '' ) { continue; }
			$rule = $fx_uid . '{' . $prop . ':var(--gap-' . $slug . ');}';
			if ( $infix === '' )        { $css .= $rule; }
			elseif ( $infix === '-md' ) { $css .= '@media (min-width:768px){' . $rule . '}'; }
			elseif ( $infix === '-lg' ) { $css .= '@media (min-width:992px){' . $rule . '}'; }
		}
		return $css;
	};
	$w_custom_css .= $fx_axis_gap( 'row-gap', $fx_resp( 'row_gap' ) );
	$w_custom_css .= $fx_axis_gap( 'column-gap', $fx_resp( 'col_gap' ) );
}

// Background video (background-pro): merge its data-attrs + flag class; the theme's
// bg-video JS reads `.background-video[data-...]` and injects the <video> element.
if ( function_exists( 'sc_bg_pro_video_attr' ) ) {
	$__vattr = sc_bg_pro_video_attr( $bgv );
	if ( ! empty( $__vattr ) ) {
		$attr          = array_merge( $attr, $__vattr );
		$attr['class'] = trim( ( isset( $attr['class'] ) ? $attr['class'] : '' ) . ' background-video' );
	}
}

// Display = Grid: the `fw-grid` class (already on the element) sets display:grid, and for a NUMERIC
// column count 1–12 a shared `fw-grid-N` class sets the tracks — so a normal grid needs NO
// per-element style. Auto-fit, a raw template, or a count > 12 fall back to one inline
// grid-template-columns (the documented long tail). Dense packing is the shared `fw-grid-dense` class.
$display_inline = '';
if ( $display === 'grid' ) {
	$grid_classes = array();
	if ( (string) fw_akg( 'grid_dense', $atts, 'no' ) === 'yes' ) { $grid_classes[] = 'fw-grid-dense'; }
	if ( isset( $atts['grid_autofit'] ) && $atts['grid_autofit'] === 'yes' ) {
		$gmv  = ( isset( $atts['grid_min']['value'] ) ) ? preg_replace( '/[^0-9.]/', '', (string) $atts['grid_min']['value'] ) : '';
		$gmu  = ( isset( $atts['grid_min']['unit'] ) && in_array( $atts['grid_min']['unit'], array( 'px', 'rem', 'em', '%' ), true ) ) ? $atts['grid_min']['unit'] : 'px';
		$gmin = ( $gmv !== '' ) ? $gmv . $gmu : '240px';
		$display_inline = 'grid-template-columns:repeat(auto-fit,minmax(' . $gmin . ',1fr));';
	} else {
		$gc = isset( $atts['grid_columns'] ) ? trim( (string) $atts['grid_columns'] ) : '3';
		if ( $gc === '' ) { $gc = '3'; }
		if ( ctype_digit( $gc ) && (int) $gc >= 1 && (int) $gc <= 12 ) {
			$grid_classes[] = 'fw-grid-' . (int) $gc;
		} elseif ( ctype_digit( $gc ) ) {
			$display_inline = 'grid-template-columns:repeat(' . min( 24, (int) $gc ) . ',minmax(0,1fr));';
		} elseif ( preg_match( '#^[0-9a-zA-Z%.,()/\\s_-]+$#', $gc ) ) {
			$display_inline = 'grid-template-columns:' . $gc . ';';
		} else {
			$grid_classes[] = 'fw-grid-3';
		}
	}
	if ( ! empty( $grid_classes ) ) {
		$attr['class'] = trim( ( isset( $attr['class'] ) ? $attr['class'] : '' ) . ' ' . implode( ' ', $grid_classes ) );
	}
}
// Block Div: emit NO inline display — a div / section is block by default, and leaving the
// property off lets the CSS auto-row rule take over when the block holds fractional-Width
// children (frontend-grid.css: `.fw-flexbox:not(.d-flex):not(.fw-grid):has(> [class*="fw-col-"])`
// becomes a flex row). So dropping two 1/2 Divs into a Section lays them side-by-side without
// the user having to switch the Section to Flex — while a plain block band still stacks.
if ( $display_inline !== '' ) {
	$existing      = ( isset( $attr['style'] ) && $attr['style'] !== '' ) ? rtrim( $attr['style'], '; ' ) . ';' : '';
	$attr['style'] = $existing . $display_inline;
}

// Section width: a section-tag Div is a full-width BAND. By DEFAULT it is contained to the site
// container (the shared `.fw-contained` class — background AND content). Turn on "Full-Width Band"
// to make the background span the window edge-to-edge while the content stays inset to the Content
// Width (or the site container) — the classic full-bleed hero, via the shared `.fw-full-bleed` class
// + a --fw-cw variable. A Content Width on a non-band Div just caps that box's own width.
$full_width = ( $tag === 'section' && (string) fw_akg( 'full_width', $atts, 'no' ) === 'yes' );
$cw_raw     = fw_akg( 'content_width', $atts, null );
$cw_has     = false;
$cw_css     = 'var(--container-max-desktop, var(--site-max-width, 1170px))';
// Content Width is now a multi-picker: { preset, custom:{ custom_width:{value,unit} } }. A named
// library slug resolves to its px via unysonplus_container_width_map() (same source as the classic
// Section, so the two stay in sync); 'custom' reads the unit-input; 'inherit'/'' means no cap. A
// LEGACY flat { value, unit } (saved before the picker) is still honoured as a Custom width.
if ( is_array( $cw_raw ) && isset( $cw_raw['preset'] ) ) {
	$cw_preset = (string) $cw_raw['preset'];
	if ( $cw_preset === 'custom' ) {
		$wc   = ( isset( $cw_raw['custom']['custom_width'] ) && is_array( $cw_raw['custom']['custom_width'] ) ) ? $cw_raw['custom']['custom_width'] : array();
		$cw_v = isset( $wc['value'] ) ? preg_replace( '/[^0-9.\-]/', '', (string) $wc['value'] ) : '';
		$cw_u = ( isset( $wc['unit'] ) && in_array( $wc['unit'], array( 'px', 'rem', '%', 'vw' ), true ) ) ? $wc['unit'] : 'px';
		if ( $cw_v !== '' ) { $cw_has = true; $cw_css = $cw_v . $cw_u; }
	} elseif ( $cw_preset !== '' && $cw_preset !== 'inherit' ) {
		$cw_map = function_exists( 'unysonplus_container_width_map' ) ? unysonplus_container_width_map() : array( 'narrow' => '768px', 'medium' => '896px', 'wide' => '1024px' );
		if ( isset( $cw_map[ $cw_preset ] ) ) { $cw_has = true; $cw_css = $cw_map[ $cw_preset ]; }
	}
} elseif ( is_array( $cw_raw ) && isset( $cw_raw['value'] ) && trim( (string) $cw_raw['value'] ) !== '' ) {
	$cw_v = preg_replace( '/[^0-9.\-]/', '', (string) $cw_raw['value'] );
	$cw_u = ( isset( $cw_raw['unit'] ) && in_array( $cw_raw['unit'], array( 'px', 'rem', '%', 'vw' ), true ) ) ? $cw_raw['unit'] : 'px';
	if ( $cw_v !== '' ) { $cw_has = true; $cw_css = $cw_v . $cw_u; }
}
if ( $full_width ) {
	// Full-bleed band: background edge-to-edge, content inset to the content width (the class handles
	// the padding + a min gutter). Only a NON-default content width needs the variable, keyed to fx-*.
	$attr['class'] = trim( ( isset( $attr['class'] ) ? $attr['class'] : '' ) . ' fw-full-bleed' );
	if ( $cw_has ) {
		$decl = '--fw-cw:' . $cw_css . ';';
		if ( $fx_uid !== '' ) {
			$w_custom_css .= $fx_uid . '{' . $decl . '}';
		} else {
			$existing      = ( isset( $attr['style'] ) && $attr['style'] !== '' ) ? rtrim( $attr['style'], '; ' ) . ';' : '';
			$attr['style'] = $existing . $decl;
		}
	}
} elseif ( $cw_has ) {
	// A boxed content width → cap the box itself, but NEVER past the viewport minus the site gutter
	// (min(cap, 100% - 2*gutter) keeps a gutter at every width with no extra padding). Per-element,
	// so it rides the consolidated footer stylesheet; only a box with no fx- class falls back inline.
	$cw_decl = 'max-width:min(' . $cw_css . ', calc(100% - 2 * var(--container-gutter, clamp(1.25rem, 3vw, 2rem))));margin-left:auto;margin-right:auto;';
	if ( $fx_uid !== '' ) {
		$w_custom_css .= $fx_uid . '{' . $cw_decl . '}';
	} else {
		$existing      = ( isset( $attr['style'] ) && $attr['style'] !== '' ) ? rtrim( $attr['style'], '; ' ) . ';' : '';
		$attr['style'] = $existing . $cw_decl;
	}
} elseif ( $tag === 'section' ) {
	// The DEFAULT contained section (site width + gutter, centred) is the shared `.fw-contained` class.
	$attr['class'] = trim( ( isset( $attr['class'] ) ? $attr['class'] : '' ) . ' fw-contained' );
}

// Aspect Ratio: lock the box to a width : height ratio (aspect-ratio). Accept "16 / 9",
// "16/9", "16:9" (normalised to "16 / 9") or a bare number ("1"); anything else is dropped.
$ar_raw = trim( (string) fw_akg( 'aspect_ratio', $atts, '' ) );
if ( $ar_raw !== '' && preg_match( '#^[0-9]+(?:\.[0-9]+)?(?:\s*[/:]\s*[0-9]+(?:\.[0-9]+)?)?$#', $ar_raw ) ) {
	$ar            = trim( preg_replace( '#\s*[/:]\s*#', ' / ', $ar_raw ) );
	$existing      = ( isset( $attr['style'] ) && $attr['style'] !== '' ) ? rtrim( $attr['style'], '; ' ) . ';' : '';
	$attr['style'] = $existing . 'aspect-ratio:' . $ar . ';';
}

// Responsive Collapse (default on): a multi-column Grid / Flex row steps down on smaller screens
// with no per-device setup — 2 columns on tablets (768–991px), a single stacked column on phones
// (<768px). Applied via SHARED, cacheable classes defined once in frontend-grid.css
// (fw-grid-collapse / fw-grid-collapse-1 / fw-collapse) — NOT a per-element @media <style> block —
// so N identical children don't each emit an identical rule. A Grid using Auto-fit already reflows,
// so it is left alone. Explicit per-device Widths still win (a child's own phone-width rule loads
// after the shared sheet, at equal specificity).
if ( (string) fw_akg( 'responsive_collapse', $atts, 'yes' ) === 'yes' ) {
	$collapse_class = '';
	if ( $display === 'grid' && ! ( isset( $atts['grid_autofit'] ) && $atts['grid_autofit'] === 'yes' ) ) {
		$gcn = isset( $atts['grid_columns'] ) ? trim( (string) $atts['grid_columns'] ) : '3';
		// Equal numeric grid of 3–8 -> 2 cols on tablet, 1 on phone (fw-grid-collapse). Everything
		// else (a 2-col grid, a 12-track spanning grid, or a raw template) just stacks to one column
		// on phones (fw-grid-collapse-1, which also clears any child spans).
		$collapse_class = ( ctype_digit( $gcn ) && (int) $gcn >= 3 && (int) $gcn <= 8 ) ? 'fw-grid-collapse' : 'fw-grid-collapse-1';
	} elseif ( $display === 'flex' ) {
		$collapse_class = 'fw-collapse';
	}
	if ( $collapse_class !== '' ) {
		$attr['class'] = trim( ( isset( $attr['class'] ) ? $attr['class'] : '' ) . ' ' . $collapse_class );
	}
}

// Any remaining per-element rules (a hand-typed custom width, a per-device value, min-height, gap
// overrides — the long tail that no shared class can express) are COLLECTED into one consolidated
// <style> printed once in the footer, keyed to this box's .fx-* class, instead of a <style> block
// glued before each element. If the footer has already fired (a late / AJAX render), fall back to
// printing inline right here so the CSS is never lost.
if ( $w_custom_css !== '' ) {
	if ( function_exists( 'did_action' ) && did_action( 'wp_footer' ) ) {
		echo '<style>' . $w_custom_css . '</style>'; // phpcs:ignore WordPress.Security.EscapeOutput — generated, value-sanitized CSS
	} else {
		if ( ! isset( $GLOBALS['_fw_flex_inline_css'] ) ) {
			$GLOBALS['_fw_flex_inline_css'] = '';
			if ( function_exists( 'add_action' ) ) {
				add_action( 'wp_footer', array( 'FW_Shortcode_Flexbox', '_print_inline_css' ), 98 );
			}
		}
		$GLOBALS['_fw_flex_inline_css'] .= $w_custom_css;
	}
}
// ----- Section-parity decoration (Text Alignment, and — for a Section-tag band only —
// Section Variant, Shape Dividers and Background Pattern). Ported from the classic Section so a
// Flexbox set to <section> can be a full hero band. Text Alignment applies to ANY tag; the other
// three are meaningful only on a full-width section band, so the editor also hides them off-section.
$fx_extra_classes    = '';
$fx_pattern_html     = '';
$fx_divider_top_html = '';
$fx_divider_bot_html = '';

// Text Alignment (any tag) — the CSS `text-align` for all inline/text content, as a Bootstrap
// `text-*` utility. Inherited, so it cascades to nested headings / paragraphs / buttons. '' = Inherit.
$fx_ta_class = function_exists( 'sc_alignment_class' ) ? sc_alignment_class( isset( $atts['text_align'] ) ? $atts['text_align'] : '' ) : '';
if ( $fx_ta_class !== '' ) { $fx_extra_classes .= ' ' . $fx_ta_class; }

if ( $tag === 'section' ) {
	// Section Variant = a Section Style preset slug. Validate against the live slug map (any
	// registered style, not just the built-ins); fall back to the built-in slugs otherwise.
	$fx_variant = isset( $atts['variant'] ) ? preg_replace( '/[^a-z0-9_-]/', '', strtolower( (string) $atts['variant'] ) ) : '';
	if ( $fx_variant !== '' ) {
		$fx_valid_variants = function_exists( 'unysonplus_section_style_preset_slug_map' )
			? array_values( unysonplus_section_style_preset_slug_map() )
			: array( 'alt', 'light', 'dark' );
		if ( in_array( $fx_variant, $fx_valid_variants, true ) ) {
			$fx_extra_classes .= ' section--' . $fx_variant;
		}
	}

	// Shape Dividers (top / bottom) — an SVG-shaped edge. Geometry from the Shape Dividers preset
	// library (unysonplus_shape_divider_markup); a built-in path map is the fallback.
	$fx_divider_paths = array(
		'tilt'     => 'M1200 120L0 16.48 0 0 1200 0 1200 120z',
		'curve'    => 'M600 112.77C268.63 112.77 0 65.52 0 7.23V120h1200V7.23c0 58.29-268.63 105.54-600 105.54z',
		'wave'     => 'M0 0v46.29c47.79 22.2 103.59 32.17 158 28 70.36-5.37 136.33-33.31 206.8-37.5 73.84-4.36 147.54 16.88 218.2 35.26 69.27 18 138.3 24.88 209.4 13.08 36.15-6 69.85-17.84 104.45-29.34C989.49 25 1113-14.29 1200 52.47V0z',
		'triangle' => 'M1200 0L0 0 598.97 114.72 1200 0z',
	);
	$fx_divider_color = function ( $cval ) {
		$cval   = is_array( $cval ) ? $cval : array();
		$custom = isset( $cval['custom'] ) ? trim( (string) $cval['custom'] ) : '';
		if ( $custom !== '' && preg_match( '/^(#[0-9a-fA-F]{3,8}|rgba?\([0-9.,%\s]+\))$/', $custom ) ) { return $custom; }
		$pre = isset( $cval['predefined'] ) ? trim( (string) $cval['predefined'] ) : '';
		if ( $pre !== '' ) {
			$slug = preg_replace( '/[^a-z0-9_-]/i', '', preg_replace( '/^(?:bg|text)-/', '', $pre ) );
			if ( $slug !== '' ) { return 'var(--color-' . $slug . ')'; }
		}
		return '#ffffff';
	};
	$fx_divider_html = function ( $dv, $placement ) use ( $fx_divider_paths, $fx_divider_color ) {
		if ( ! is_array( $dv ) ) { return ''; }
		$shape = isset( $dv['shape'] ) ? (string) $dv['shape'] : 'none';
		if ( $shape === '' || $shape === 'none' ) { return ''; }
		if ( function_exists( 'unysonplus_shape_divider_markup' ) ) {
			$geo     = unysonplus_shape_divider_markup( $shape );
			$inner   = $geo['inner'];
			$viewbox = $geo['viewBox'];
		} else {
			$inner   = isset( $fx_divider_paths[ $shape ] ) ? '<path d="' . esc_attr( $fx_divider_paths[ $shape ] ) . '" fill="currentColor"/>' : '';
			$viewbox = '0 0 1200 120';
		}
		if ( trim( $inner ) === '' ) { return ''; }
		$sub   = ( isset( $dv[ $shape ] ) && is_array( $dv[ $shape ] ) ) ? $dv[ $shape ] : array();
		$color = $fx_divider_color( isset( $sub['color'] ) ? $sub['color'] : array() );
		$h     = '100px';
		if ( isset( $sub['height'] ) && is_array( $sub['height'] ) ) {
			$num  = isset( $sub['height']['value'] ) ? trim( (string) $sub['height']['value'] ) : '';
			$unit = ( isset( $sub['height']['unit'] ) && in_array( $sub['height']['unit'], array( 'px', 'vh', '%' ), true ) ) ? $sub['height']['unit'] : 'px';
			if ( $num !== '' && is_numeric( $num ) ) { $h = $num . $unit; }
		}
		$flip  = ( isset( $sub['flip'] ) && $sub['flip'] === 'yes' );
		$tf    = ( $placement === 'bottom' ? 'rotate(180deg)' : '' ) . ( $flip ? ' scaleX(-1)' : '' );
		$style = 'height:' . $h . ';color:' . $color . ';' . ( trim( $tf ) !== '' ? 'transform:' . trim( $tf ) . ';' : '' );
		return '<div class="sc-shape-divider sc-shape-divider--' . esc_attr( $placement ) . '" style="' . esc_attr( $style ) . '" aria-hidden="true">'
			. '<svg viewBox="' . esc_attr( $viewbox ) . '" preserveAspectRatio="none" fill="currentColor">' . $inner . '</svg>'
			. '</div>';
	};
	$fx_divider_top_html = $fx_divider_html( isset( $atts['divider_top'] ) ? $atts['divider_top'] : array(), 'top' );
	$fx_divider_bot_html = $fx_divider_html( isset( $atts['divider_bottom'] ) ? $atts['divider_bottom'] : array(), 'bottom' );
	if ( $fx_divider_top_html !== '' || $fx_divider_bot_html !== '' ) {
		$fx_extra_classes .= ' section--has-divider';
	}

	// Background Pattern — a reusable decorative layer over the Background. Stored as a preset id;
	// rendered as an aria-hidden .pattern-layer (first child, so it sits behind the content).
	$fx_pat    = isset( $atts['background_pattern'] ) ? $atts['background_pattern'] : '';
	$fx_pat_id = is_array( $fx_pat ) ? ( isset( $fx_pat['pattern'] ) ? (string) $fx_pat['pattern'] : '' ) : (string) $fx_pat;
	if ( $fx_pat_id === 'none' ) { $fx_pat_id = ''; }
	if ( $fx_pat_id !== '' && function_exists( 'unysonplus_pattern_render_layer' ) ) {
		$fx_pattern_html = unysonplus_pattern_render_layer( $fx_pat_id );
		if ( $fx_pattern_html !== '' ) { $fx_extra_classes .= ' upw-has-pattern'; }
	}
}

if ( $fx_extra_classes !== '' ) {
	$attr['class'] = trim( ( isset( $attr['class'] ) ? $attr['class'] : '' ) . ' ' . trim( $fx_extra_classes ) );
}

// Clean output for an EMPTY box: a flexbox with no child content (e.g. the blank cells of a grid the
// user only partly filled) doesn't need its "lay out my children" classes — display flex/grid and the
// direction / wrap / justify / align / gap / responsive-collapse utilities all do nothing with no
// children, so they're pure DOM noise. Drop them, but KEEP every class that describes the box itself
// or how it sits in ITS parent (fx-* id, width span / fifth / keyword, align-self, order, grow /
// shrink, border preset, section band, background, text-align, pattern/divider markers) — so an empty
// GRID CELL still occupies its track. Guarded on truly-empty content (trim === ''): a box with ANY
// content, even a single element or a nested empty shortcode, is left exactly as-is, so a configured
// box (e.g. one child centred via flex) is never touched. Empties are safe regardless of their options
// because there are no children for those options to act on.
if ( trim( (string) $content ) === '' && isset( $attr['class'] ) && $attr['class'] !== '' ) {
	$fx_kept = array();
	foreach ( preg_split( '/\s+/', trim( (string) $attr['class'] ) ) as $fx_cl ) {
		if ( $fx_cl === '' ) { continue; }
		// fw-flex / fw-flex-* (NOT fw-flexbox), fw-grid*, fw-collapse, fw-justify*, fw-items*,
		// fw-content-* (NOT fw-contained), fw-gap* — the child-layout utilities.
		if ( preg_match( '/^(fw-flex(-.*)?|fw-grid.*|fw-collapse|fw-justify.*|fw-items.*|fw-content-.*|fw-gap.*)$/', $fx_cl ) ) { continue; }
		$fx_kept[] = $fx_cl;
	}
	$attr['class'] = implode( ' ', $fx_kept );
	// The inline grid template (its display:grid rode on the now-removed fw-grid class) is inert too.
	if ( isset( $attr['style'] ) && $attr['style'] !== '' ) {
		$attr['style'] = trim( preg_replace( '/grid-template-columns:[^;]*;?/', '', (string) $attr['style'] ) );
		if ( $attr['style'] === '' ) { unset( $attr['style'] ); }
	}
}

echo '<' . $tag . ' ' . fw_attr_to_html( $attr ) . '>';
// Decorative layers first (pattern behind content, then the shaped edges), then the children.
echo $fx_pattern_html;     // phpcs:ignore WordPress.Security.EscapeOutput — admin-authored, scoped + script-stripped
echo $fx_divider_top_html; // phpcs:ignore WordPress.Security.EscapeOutput — built + value-sanitized above
echo $fx_divider_bot_html; // phpcs:ignore WordPress.Security.EscapeOutput
// Publish OUR display to nested children (so a grid cell can drop its redundant width, above),
// render them, then pop — keeping the stack balanced for siblings.
$GLOBALS['_fw_flex_stack'][] = $display;
echo do_shortcode( $content );
array_pop( $GLOBALS['_fw_flex_stack'] );
echo '</' . $tag . '>';
