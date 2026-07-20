<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * @var array  $atts
 * @var string $content
 *
 * A Container renders a second `.fw-container` / `.fw-container-fluid` band inside a
 * section, as a SIBLING of the section's own container (the items-corrector lifts it out
 * so it is not nested). Its columns are grouped into `.fw-row`(s) by the corrector,
 * exactly like a section's columns — so the markup is `.fw-container[-fluid] > .fw-row > columns`.
 *
 * Styling mirrors the Section's curated subset: Background (background-pro) + Min Height +
 * Columns alignment + Spacing (padding / gap). The alignment / gap values reuse the shared
 * `.section--cols-*` / `.section--rev*` / `.section--gap-*` modifier classes (class-only
 * descendant selectors), so no container-specific CSS is needed for them; only the
 * "Default / Stretched" vertical alignment needs a container-scoped rule (styles.css),
 * because the section's stretch CSS keys on a `> .fw-container` child this element lacks.
 */

// Give the wrapper a unique class (for the Live Editor stamp + optional scoping) and let
// sc_build_wrapper_attr auto-apply the Spacing (padding_top/bottom) utility classes.
$atts['unique_id_prefix'] = 'ct-';

$is_fluid        = ! empty( $atts['is_fullwidth'] );
$container_class = $is_fluid ? 'fw-container-fluid' : 'fw-container';

$extra_classes = '';
$box_style     = '';

// --- Background (background-pro) → inline style + optional video layer. ---
$bgv = ( ! empty( $atts['background'] ) && is_array( $atts['background'] ) ) ? $atts['background'] : array();
if ( ! empty( $bgv ) && function_exists( 'sc_bg_pro_style' ) ) {
	$box_style .= sc_bg_pro_style( $bgv );
}
$bg_video_attr = array();
if ( ! empty( $bgv ) && function_exists( 'sc_bg_pro_video_attr' ) ) {
	$__vattr = sc_bg_pro_video_attr( $bgv );
	if ( ! empty( $__vattr ) ) {
		$bg_video_attr  = $__vattr;
		$extra_classes .= ' background-video';
	}
}

// --- Min Height (hybrid multi-picker: viewport preset OR Custom {value,unit}). ---
$min_height = '';
$mh = isset( $atts['min_height'] ) ? $atts['min_height'] : '';
if ( is_array( $mh ) ) {
	$preset = isset( $mh['preset'] ) ? (string) $mh['preset'] : '';
	if ( $preset === 'custom' ) {
		$uv   = ( isset( $mh['custom']['custom_height'] ) && is_array( $mh['custom']['custom_height'] ) ) ? $mh['custom']['custom_height'] : array();
		$num  = isset( $uv['value'] ) ? trim( (string) $uv['value'] ) : '';
		$unit = isset( $uv['unit'] ) ? (string) $uv['unit'] : 'px';
		if ( $num !== '' ) { $min_height = $num . $unit; }
	} elseif ( $preset !== '' && $preset !== 'auto' ) {
		$min_height = $preset; // e.g. "40vh"
	}
} elseif ( is_string( $mh ) && $mh !== 'auto' && $mh !== '' ) {
	$min_height = trim( $mh ); // tolerate a legacy plain-string value
}
if ( $min_height !== '' ) {
	$box_style .= 'min-height:' . esc_attr( $min_height ) . ';';
}

// --- Columns Vertical Alignment (id: column_valign). Positions the row(s) vertically when
// the container is taller than its content (min-height). Stretch grows the row (styles.css);
// Center / Bottom flex-justify the content-height row block. ---
$valign = isset( $atts['column_valign'] ) ? (string) $atts['column_valign'] : '';
if ( $valign === 'stretch' ) {
	if ( $min_height !== '' ) { $extra_classes .= ' section--valign-stretch'; }
} elseif ( $min_height !== '' || $valign === 'center' || $valign === 'bottom' ) {
	$valign_map = array( 'center' => 'center', 'bottom' => 'flex-end' );
	$justify    = isset( $valign_map[ $valign ] ) ? $valign_map[ $valign ] : 'flex-start';
	$box_style .= 'display:flex;flex-direction:column;justify-content:' . $justify . ';';
}

// --- Columns Horizontal Alignment (id: column_halign; per-device). base = section--cols-{v}
// (all widths); md/lg add section--cols-{bp}-{v} overrides. Default (left) needs no class. ---
$halign_resp = fw_akg( 'column_halign', $atts, array() );
if ( ! is_array( $halign_resp ) ) { $halign_resp = array( 'base' => (string) $halign_resp ); }
$halign_valid = array( 'center', 'right', 'between', 'around', 'evenly' );
foreach ( array( 'base' => '', 'md' => '-md', 'lg' => '-lg' ) as $layer => $infix ) {
	$hv = isset( $halign_resp[ $layer ] ) ? (string) $halign_resp[ $layer ] : '';
	if ( in_array( $hv, $halign_valid, true ) ) {
		$extra_classes .= ' section--cols' . $infix . '-' . $hv;
	}
}

// --- Reverse column order (id: reverse_columns; per-device switch yes/no/''). base =
// section--rev; md/lg add on/off overrides from their breakpoint up. Legacy scalar migrates. ---
$rev_raw = fw_akg( 'reverse_columns', $atts, array() );
if ( ! is_array( $rev_raw ) ) {
	$legacy = (string) $rev_raw;
	if ( $legacy === 'all' )        { $rev_raw = array( 'base' => 'yes' ); }
	elseif ( $legacy === 'mobile' ) { $rev_raw = array( 'base' => 'yes', 'md' => 'no' ); }
	elseif ( $legacy === 'tablet' ) { $rev_raw = array( 'base' => 'yes', 'lg' => 'no' ); }
	else                            { $rev_raw = array(); }
}
$rb = ( isset( $rev_raw['base'] ) && $rev_raw['base'] === 'yes' );
$rm = ( isset( $rev_raw['md'] ) && $rev_raw['md'] !== '' ) ? ( $rev_raw['md'] === 'yes' ) : $rb;
$rl = ( isset( $rev_raw['lg'] ) && $rev_raw['lg'] !== '' ) ? ( $rev_raw['lg'] === 'yes' ) : $rm;
if ( $rb ) { $extra_classes .= ' section--rev'; }
if ( $rm !== $rb ) { $extra_classes .= $rm ? ' section--rev-md-on' : ' section--rev-md-off'; }
if ( $rl !== $rm ) { $extra_classes .= $rl ? ' section--rev-lg-on' : ' section--rev-lg-off'; }

// --- Per-container column-gap modifier classes (mirrors the Section's Gap / Gap X / Gap Y).
// base = section--gap{,-x,-y}-{slug} (all widths); md/lg add breakpoint overrides. ---
$gap_resp = fw_akg( 'gap', $atts, array() );
if ( ! is_array( $gap_resp ) ) { $gap_resp = array( 'base' => (string) $gap_resp ); }
foreach ( array( 'base' => '', 'md' => '-md', 'lg' => '-lg' ) as $layer => $infix ) {
	$slug = preg_replace( '/[^a-zA-Z0-9_-]/', '', (string) ( isset( $gap_resp[ $layer ] ) ? $gap_resp[ $layer ] : '' ) );
	if ( $slug === '' ) { continue; }
	$extra_classes .= ' section--gap' . $infix . '-' . strtolower( $slug );
}
foreach ( array( 'gap_x' => 'section--gap-x', 'gap_y' => 'section--gap-y' ) as $att_key => $class_base ) {
	$resp = fw_akg( $att_key, $atts, array() );
	if ( ! is_array( $resp ) ) { $resp = array( 'base' => (string) $resp ); }
	foreach ( array( 'base' => '', 'md' => '-md', 'lg' => '-lg' ) as $layer => $infix ) {
		$slug = preg_replace( '/[^a-zA-Z0-9_-]/', '', (string) ( isset( $resp[ $layer ] ) ? $resp[ $layer ] : '' ) );
		if ( $slug === '' ) { continue; }
		$extra_classes .= ' ' . $class_base . $infix . '-' . strtolower( $slug );
	}
}

// sc_build_wrapper_attr auto-applies the Spacing (padding_top/bottom) utility classes +
// the Live Editor data-fw-item-id stamp (via its registered filters). The .fw-container[-fluid]
// layout class + our styling classes/style are layered on top.
// Background Pattern — a reusable CSS/HTML pattern drawn as a decorative layer behind the
// content (over the Background). Stored as a preset id; rendered as an aria-hidden .pattern-layer.
$pattern_html = '';
$bp = isset( $atts['background_pattern'] ) ? $atts['background_pattern'] : '';
$pat_id = is_array( $bp ) ? ( isset( $bp['pattern'] ) ? (string) $bp['pattern'] : '' ) : (string) $bp;
if ( $pat_id === 'none' ) { $pat_id = ''; }
if ( $pat_id !== '' && function_exists( 'unysonplus_pattern_render_layer' ) ) {
	$pattern_html = unysonplus_pattern_render_layer( $pat_id );
	if ( $pattern_html !== '' ) {
		$extra_classes .= ' upw-has-pattern';
	}
}

$attr = sc_build_wrapper_attr( $atts );

$attr['class'] = trim( $container_class . ' ' . ( isset( $attr['class'] ) ? $attr['class'] : '' ) . $extra_classes );

if ( $box_style !== '' ) {
	$existing      = ( isset( $attr['style'] ) && $attr['style'] !== '' ) ? rtrim( $attr['style'], '; ' ) . ';' : '';
	$attr['style'] = $existing . $box_style;
}

$attr = array_merge( $attr, $bg_video_attr );
?>
<div <?php echo fw_attr_to_html( $attr ); ?>>
	<?php echo $pattern_html; // phpcs:ignore WordPress.Security.EscapeOutput — admin-authored, scoped + script-stripped ?>
	<?php echo do_shortcode( $content ); ?>
</div>
