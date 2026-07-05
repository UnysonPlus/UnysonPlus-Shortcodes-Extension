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

// --- Responsive helper: read a { base, md, lg } value (tolerating a legacy scalar). ---
$fx_resp = function ( $key ) use ( $atts ) {
	$v = fw_akg( $key, $atts, null );
	if ( is_array( $v ) ) {
		return array(
			'base' => isset( $v['base'] ) ? (string) $v['base'] : '',
			'md'   => isset( $v['md'] )   ? (string) $v['md']   : '',
			'lg'   => isset( $v['lg'] )   ? (string) $v['lg']   : '',
		);
	}
	return array( 'base' => ( $v === null ? '' : (string) $v ), 'md' => '', 'lg' => '' );
};
$fx_layers = array( 'base' => '', 'md' => '-md', 'lg' => '-lg' );

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
	return 'flex-' . ( $bp === '' ? '' : $bp . '-' ) . ( $d === 'column' ? 'column' : 'row' ) . ( $r === 'yes' ? '-reverse' : '' );
};
$classes[] = $fx_dir_class( '', $dir_eff['base'], $rev_eff['base'] );
if ( $dir_eff['md'] !== $dir_eff['base'] || $rev_eff['md'] !== $rev_eff['base'] ) { $classes[] = $fx_dir_class( 'md', $dir_eff['md'], $rev_eff['md'] ); }
if ( $dir_eff['lg'] !== $dir_eff['md']  || $rev_eff['lg'] !== $rev_eff['md'] )  { $classes[] = $fx_dir_class( 'lg', $dir_eff['lg'], $rev_eff['lg'] ); }

// Wrap (per-device switch, default on). Emit a class at base always, and at md/lg
// only when it differs from the device below (flex-{bp}-wrap / flex-{bp}-nowrap).
$wrap_eff = $fx_switch_eff( 'wrap', 'yes' );
$wrap_cls = function ( $bp, $w ) { return 'flex-' . ( $bp === '' ? '' : $bp . '-' ) . ( $w === 'no' ? 'nowrap' : 'wrap' ); };
$classes[] = $wrap_cls( '', $wrap_eff['base'] );
if ( $wrap_eff['md'] !== $wrap_eff['base'] ) { $classes[] = $wrap_cls( 'md', $wrap_eff['md'] ); }
if ( $wrap_eff['lg'] !== $wrap_eff['md'] )  { $classes[] = $wrap_cls( 'lg', $wrap_eff['lg'] ); }

// Gap (per-device): a spacing-preset slug → sc-cgap-{slug} utility (which sets the
// flex `gap` via var(--gap-{slug}); generated in css-tokens.php), one per device.
$gap_r = $fx_resp( 'gap' );
foreach ( $fx_layers as $layer => $infix ) {
	$gslug = function_exists( 'sc_sanitize_class' )
		? strtolower( sc_sanitize_class( (string) $gap_r[ $layer ] ) )
		: preg_replace( '/[^a-z0-9_-]/i', '', strtolower( (string) $gap_r[ $layer ] ) );
	if ( $gslug !== '' ) { $classes[] = 'sc-cgap' . $infix . '-' . $gslug; }
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
	if ( in_array( $jc_resp[ $layer ], $jc_valid, true ) ) { $classes[] = 'justify-content' . $infix . '-' . $jc_resp[ $layer ]; }
}

// Align items (per-device, cross axis).
$ai_valid = array( 'start', 'center', 'end', 'stretch', 'baseline' );
$ai_r     = $fx_resp( 'align_items' );
foreach ( $fx_layers as $layer => $infix ) {
	if ( in_array( $ai_r[ $layer ], $ai_valid, true ) ) { $classes[] = 'align-items' . $infix . '-' . $ai_r[ $layer ]; }
}

// Align content (per-device, wrapped multi-line container). Empty = default (no class).
$ac_valid = array( 'start', 'center', 'end', 'between', 'around', 'stretch' );
$ac_r     = $fx_resp( 'align_content' );
foreach ( $fx_layers as $layer => $infix ) {
	if ( in_array( $ac_r[ $layer ], $ac_valid, true ) ) { $classes[] = 'align-content' . $infix . '-' . $ac_r[ $layer ]; }
}

// Item-in-parent properties: align-self, order, grow — align-self & order per-device.
$as_valid = array( 'start', 'center', 'end', 'stretch', 'baseline' );
$as_r     = $fx_resp( 'align_self' );
foreach ( $fx_layers as $layer => $infix ) {
	if ( in_array( $as_r[ $layer ], $as_valid, true ) ) { $classes[] = 'align-self' . $infix . '-' . $as_r[ $layer ]; }
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
$grow_cls = function ( $bp, $g ) { return 'flex-grow-' . ( $bp === '' ? '' : $bp . '-' ) . ( $g === 'yes' ? '1' : '0' ); };
if ( $grow_eff['base'] === 'yes' ) { $classes[] = $grow_cls( '', 'yes' ); }
if ( $grow_eff['md'] !== $grow_eff['base'] ) { $classes[] = $grow_cls( 'md', $grow_eff['md'] ); }
if ( $grow_eff['lg'] !== $grow_eff['md'] )  { $classes[] = $grow_cls( 'lg', $grow_eff['lg'] ); }

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
	$out = array( 'preset' => '', 'custom' => '' );
	if ( is_array( $L ) ) {
		$out['preset'] = isset( $L['preset'] ) ? (string) $L['preset'] : '';
		if ( $out['preset'] === 'custom' && isset( $L['custom']['width_custom'] ) && is_array( $L['custom']['width_custom'] ) ) {
			$wc   = $L['custom']['width_custom'];
			$val  = isset( $wc['value'] ) ? preg_replace( '/[^0-9.\-]/', '', (string) $wc['value'] ) : '';
			$unit = ( isset( $wc['unit'] ) && in_array( $wc['unit'], array( '%', 'px', 'rem', 'vw' ), true ) ) ? $wc['unit'] : '%';
			if ( $val !== '' ) { $out['custom'] = $val . $unit; }
		}
	}
	return $out;
};
$w_raw = fw_akg( 'width', $atts, null );
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
		'base' => array( 'preset' => in_array( $wp, $fx_frac, true ) ? $wp : '', 'custom' => '' ),
		'md'   => $flat,
		'lg'   => array( 'preset' => '', 'custom' => '' ),
	);
}
$w_is_set = function ( $L ) use ( $fx_frac ) {
	return in_array( $L['preset'], $fx_frac, true ) || ( $L['preset'] === 'custom' && $L['custom'] !== '' );
};
foreach ( $fx_layers as $layer => $infix ) {
	if ( in_array( $wl[ $layer ]['preset'], $fx_frac, true ) ) {
		$classes[] = 'fw-col' . $infix . '-' . $wl[ $layer ]['preset'];
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
if ( ( $wl['base']['custom'] !== '' || $wl['md']['custom'] !== '' || $wl['lg']['custom'] !== ''
       || $mh['base'] !== '' || $mh['md'] !== '' || $mh['lg'] !== '' )
     && isset( $attr['class'] ) && preg_match( '/\bfx-[a-z0-9]+\b/i', (string) $attr['class'], $um ) ) {
	$uid    = '.' . $um[0];
	$md_set = $w_is_set( $wl['md'] );
	$lg_set = $w_is_set( $wl['lg'] );
	$decl   = function ( $v ) { return 'flex:0 0 ' . $v . ' !important;max-width:' . $v . ' !important;'; };
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

// Background video (background-pro): merge its data-attrs + flag class; the theme's
// bg-video JS reads `.background-video[data-...]` and injects the <video> element.
if ( function_exists( 'sc_bg_pro_video_attr' ) ) {
	$__vattr = sc_bg_pro_video_attr( $bgv );
	if ( ! empty( $__vattr ) ) {
		$attr          = array_merge( $attr, $__vattr );
		$attr['class'] = trim( ( isset( $attr['class'] ) ? $attr['class'] : '' ) . ' background-video' );
	}
}

if ( $w_custom_css !== '' ) {
	echo '<style>' . $w_custom_css . '</style>'; // phpcs:ignore WordPress.Security.EscapeOutput — generated, value-sanitized CSS
}
echo '<' . $tag . ' ' . fw_attr_to_html( $attr ) . '>';
echo do_shortcode( $content );
echo '</' . $tag . '>';
