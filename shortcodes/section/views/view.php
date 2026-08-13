<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * @var array  $atts
 * @var string $content
 */

// Background is a single background-pro value now. Pull in the legacy →
// background-pro migration helpers so sections saved before this control still
// resolve a background.
if ( ! function_exists( 'section_migrate_legacy_background' ) ) {
	require_once dirname( __DIR__ ) . '/includes/migration.php';
}

$bg_video_data_attr    = array();
$section_extra_classes = '';

// Section Variant = a Section Style preset slug. Any registered style is valid (not
// just the three built-ins), so validate against the live slug map; fall back to the
// built-in slugs if the preset system isn't loaded. Sanitized to a css-safe slug so a
// stray value can't inject a class.
$variant = isset( $atts['variant'] ) ? preg_replace( '/[^a-z0-9_-]/', '', strtolower( (string) $atts['variant'] ) ) : '';
if ( $variant !== '' ) {
	$valid_slugs = function_exists( 'unysonplus_section_style_preset_slug_map' )
		? array_values( unysonplus_section_style_preset_slug_map() )
		: array( 'alt', 'light', 'dark' );
	if ( ! in_array( $variant, $valid_slugs, true ) ) {
		$variant = '';
	}
}

if ( $variant !== '' ) {
	$section_extra_classes .= ' section--' . $variant;
}

// Per-section column-gap modifier classes — picked up by css-tokens.php's
// `.section--gap-{slug} .row` / `-x-` / `-y-` rules.
// Gap is now per-device: array( base, md, lg ). base = section--gap-{slug} (all
// widths); md/lg add section--gap-{bp}-{slug} overrides. A legacy scalar folds into base.
$gap_resp = fw_akg( 'gap', $atts, array() );
if ( ! is_array( $gap_resp ) ) { $gap_resp = array( 'base' => (string) $gap_resp ); }
foreach ( array( 'base' => '', 'md' => '-md', 'lg' => '-lg' ) as $layer => $infix ) {
	$slug = preg_replace( '/[^a-zA-Z0-9_-]/', '', (string) ( isset( $gap_resp[ $layer ] ) ? $gap_resp[ $layer ] : '' ) );
	if ( $slug === '' ) { continue; }
	$section_extra_classes .= ' section--gap' . $infix . '-' . strtolower( $slug );
}
// Gap X / Y overrides — now per-device ( base, md, lg ), matching Gap. base applies at
// all widths (section--gap-x-{slug}); md/lg add section--gap-{x|y}-{bp}-{slug} overrides
// (css-tokens.php). A legacy scalar folds into base. Only bite once Gap is set.
foreach ( array( 'gap_x' => 'section--gap-x', 'gap_y' => 'section--gap-y' ) as $att_key => $class_base ) {
	$resp = fw_akg( $att_key, $atts, array() );
	if ( ! is_array( $resp ) ) { $resp = array( 'base' => (string) $resp ); }
	foreach ( array( 'base' => '', 'md' => '-md', 'lg' => '-lg' ) as $layer => $infix ) {
		$slug = preg_replace( '/[^a-zA-Z0-9_-]/', '', (string) ( isset( $resp[ $layer ] ) ? $resp[ $layer ] : '' ) );
		if ( $slug === '' ) { continue; }
		$section_extra_classes .= ' ' . $class_base . $infix . '-' . strtolower( $slug );
	}
}

// --- Background (background-pro): new value, else migrated legacy atts. ---
$bgv = ( ! empty( $atts['background'] ) && is_array( $atts['background'] ) )
	? $atts['background']
	: section_migrate_legacy_background( $atts );

$section_style = sc_bg_pro_style( $bgv );

$__vattr = sc_bg_pro_video_attr( $bgv );
if ( ! empty( $__vattr ) ) {
	$bg_video_data_attr     = array_merge( $bg_video_data_attr, $__vattr );
	$section_extra_classes .= ' background-video';
}

// --- Min height + content vertical alignment (hero-style full-screen sections). ---
// Min Height — hybrid multi-picker: a viewport preset (e.g. "40vh") or a Custom
// unit-input ({value, unit}). Tolerates the legacy plain-string value too.
// A FIXED-ENUM viewport preset (40/60/80/100vh) renders as a PREDEFINED utility class
// `.section--minh-{40|60|80|100}` (styles.css) — reusable, no per-instance CSS. Only a Custom
// height (an arbitrary value with no predefined class) rides the per-page dynamic file
// (sc_section_dynamic_css). $min_height is also resolved as a plain string to drive the valign logic.
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
		$min_height = $preset; // e.g. "80vh" ('auto' means no min-height)
	}
} elseif ( is_string( $mh ) && $mh !== 'auto' ) {
	$min_height = trim( $mh ); // legacy: min_height saved as a plain string
}
// Predefined class for the fixed-enum viewport presets.
if ( preg_match( '/^(40|60|80|100)vh$/', $min_height, $mh_pm ) ) {
	$section_extra_classes .= ' section--minh-' . $mh_pm[1];
}

// Columns Vertical Alignment (id: column_valign; the now-renamed old key
// content_valign is tolerated as a fallback). Positions the whole content block
// vertically within a tall (min-height) section via the section flex column.
$valign = isset( $atts['column_valign'] )
	? (string) $atts['column_valign']
	: ( isset( $atts['content_valign'] ) ? (string) $atts['content_valign'] : '' );
// NOTE: the actual `min-height` is emitted to the per-page dynamic CSS FILE (sc_section_dynamic_css
// → dynamic-css.php), scoped to this section's `.u{hash}`, NOT as an inline style here. $min_height
// is still resolved above only to drive the vertical-alignment logic below.
// Columns Vertical Alignment is a FIXED ENUM → PREDEFINED `.section--valign-*` utility classes
// (styles.css). Stretch grows the container/row to fill the section (needs a Min Height); center/bottom
// make the section a flex column and position the content block; top/'' sit at the top naturally.
if ( $valign === 'stretch' ) {
	if ( $min_height !== '' ) { $section_extra_classes .= ' section--valign-stretch'; }
} elseif ( $valign === 'center' ) {
	$section_extra_classes .= ' section--valign-center';
} elseif ( $valign === 'bottom' ) {
	$section_extra_classes .= ' section--valign-bottom';
}

// Columns Horizontal Alignment (id: column_halign) — now a per-device value:
// array( base, md, lg ). Routed through a modifier class so it can reach this
// section's auto-generated .fw-row(s). base = section--cols-{v} (all widths); md/lg
// add section--cols-{bp}-{v} overrides (styles.css). Default (left) needs no class.
// A legacy scalar folds into base.
$halign_resp = fw_akg( 'column_halign', $atts, array() );
if ( ! is_array( $halign_resp ) ) { $halign_resp = array( 'base' => (string) $halign_resp ); }
$halign_valid = array( 'center', 'right', 'between', 'around', 'evenly' );
foreach ( array( 'base' => '', 'md' => '-md', 'lg' => '-lg' ) as $layer => $infix ) {
	$hv = isset( $halign_resp[ $layer ] ) ? (string) $halign_resp[ $layer ] : '';
	if ( in_array( $hv, $halign_valid, true ) ) {
		$section_extra_classes .= ' section--cols' . $infix . '-' . $hv;
	}
}

// Reverse column order (id: reverse_columns) → modifier classes on this section's
// row(s). Now a per-device switch: array( base, md, lg ) of yes/no/''. base = the
// existing `.section--rev` (row-reverse from md up, column-reverse where the columns
// stack); md/lg add on/off overrides from their breakpoint up. A LEGACY select value
// migrates: all → reverse everywhere; tablet → reverse < lg (base on, lg off); mobile →
// reverse < md (base on, md off).
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
if ( $rb ) { $section_extra_classes .= ' section--rev'; }
if ( $rm !== $rb ) { $section_extra_classes .= $rm ? ' section--rev-md-on' : ' section--rev-md-off'; }
if ( $rl !== $rm ) { $section_extra_classes .= $rl ? ' section--rev-lg-on' : ' section--rev-lg-off'; }

// Text Alignment (id: text_align) — the CSS `text-align` for ALL content in this
// section, applied as a Bootstrap `text-*` utility on the section wrapper. Inherited
// property, so it cascades to every nested heading / paragraph / button. '' (Inherit)
// emits nothing so sections saved before this option are unchanged. Different axis
// from Columns Horizontal Alignment (which positions the columns as flex items).
$text_align_class = function_exists( 'sc_alignment_class' ) ? sc_alignment_class( $atts['text_align'] ?? '' ) : '';
if ( $text_align_class !== '' ) {
	$section_extra_classes .= ' ' . $text_align_class;
}

$container_class = ( isset( $atts['is_fullwidth'] ) && $atts['is_fullwidth'] )
	? 'fw-container-fluid'
	: 'fw-container';

// Container Width — a NAMED library preset (narrow / medium / wide / your own) renders as a reusable
// `.section--cw-{slug}` class, generated ONCE in presets-{hash}.css by css-tokens.php (shared + cached).
// Only a CUSTOM (arbitrary) width routes to the per-page dynamic file (sc_section_dynamic_css); `inherit`
// uses the global Container Width. No inline style either way.
$container_style = '';
$cwv = isset( $atts['container_width'] ) ? $atts['container_width'] : '';
if ( is_array( $cwv ) ) {
	$cw_preset = isset( $cwv['preset'] ) ? (string) $cwv['preset'] : 'inherit';
	if ( $cw_preset !== '' && $cw_preset !== 'inherit' && $cw_preset !== 'custom' ) {
		$cw_slug = preg_replace( '/[^a-zA-Z0-9_-]/', '', $cw_preset );
		if ( $cw_slug !== '' ) { $section_extra_classes .= ' section--cw-' . $cw_slug; }
	}
}

// When this section holds one or more Container elements, the items-corrector has already
// lifted the section's OWN columns into a default .fw-container item and kept the Container
// elements as siblings — so we render the corrected content directly and DON'T add our own
// .fw-container (that would nest them). Sections without a Container keep the original markup.
$has_inner_containers = ! empty( $atts['has_inner_containers'] );

// --- Shape Dividers (top / bottom) — an SVG-shaped edge at the section's top and/or bottom. ---
// Geometry now comes from the Shape Dividers preset library (unysonplus_shape_divider_path);
// this hardcoded map is only the fallback for when that resolver is unavailable (keeps the
// built-in four working even without the shortcodes preset library loaded).
$divider_paths = array(
	'tilt'     => 'M1200 120L0 16.48 0 0 1200 0 1200 120z',
	'curve'    => 'M600 112.77C268.63 112.77 0 65.52 0 7.23V120h1200V7.23c0 58.29-268.63 105.54-600 105.54z',
	'wave'     => 'M0 0v46.29c47.79 22.2 103.59 32.17 158 28 70.36-5.37 136.33-33.31 206.8-37.5 73.84-4.36 147.54 16.88 218.2 35.26 69.27 18 138.3 24.88 209.4 13.08 36.15-6 69.85-17.84 104.45-29.34C989.49 25 1113-14.29 1200 52.47V0z',
	'triangle' => 'M1200 0L0 0 598.97 114.72 1200 0z',
);
$divider_color = function ( $cval ) {
	// sc_color_field_compact value {predefined:'bg-slug', custom:'#hex'} → a safe CSS colour.
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
$divider_html = function ( $dv, $placement ) use ( $divider_paths, $divider_color ) {
	if ( ! is_array( $dv ) ) { return ''; }
	$shape = isset( $dv['shape'] ) ? (string) $dv['shape'] : 'none';
	if ( $shape === '' || $shape === 'none' ) { return ''; }
	// Resolve the shape's FULL markup from the preset library (every <g>/<path>, so multi-layer
	// + opacity dividers survive; fills are already normalised to currentColor). User-added
	// shapes included. Fall back to the built-in single-path map if the resolver isn't available.
	if ( function_exists( 'unysonplus_shape_divider_markup' ) ) {
		$geo     = unysonplus_shape_divider_markup( $shape );
		$inner   = $geo['inner'];
		$viewbox = $geo['viewBox'];
	} else {
		$inner   = isset( $divider_paths[ $shape ] ) ? '<path d="' . esc_attr( $divider_paths[ $shape ] ) . '" fill="currentColor"/>' : '';
		$viewbox = '0 0 1200 120';
	}
	if ( trim( $inner ) === '' ) { return ''; }
	$sub   = ( isset( $dv[ $shape ] ) && is_array( $dv[ $shape ] ) ) ? $dv[ $shape ] : array();
	$color = $divider_color( isset( $sub['color'] ) ? $sub['color'] : array() );
	$h     = '100px';
	if ( isset( $sub['height'] ) && is_array( $sub['height'] ) ) {
		$num  = isset( $sub['height']['value'] ) ? trim( (string) $sub['height']['value'] ) : '';
		$unit = ( isset( $sub['height']['unit'] ) && in_array( $sub['height']['unit'], array( 'px', 'vh', '%' ), true ) ) ? $sub['height']['unit'] : 'px';
		if ( $num !== '' && is_numeric( $num ) ) { $h = $num . $unit; }
	}
	$flip = ( isset( $sub['flip'] ) && $sub['flip'] === 'yes' );
	// Shapes are stored top-oriented (shapedividers.com standard); a BOTTOM divider rotates 180°
	// so the solid edge sits at the section's bottom. Flip mirrors it horizontally.
	$tf    = ( $placement === 'bottom' ? 'rotate(180deg)' : '' ) . ( $flip ? ' scaleX(-1)' : '' );
	// Colour drives every layer via `color` (each path fills with currentColor); opacities layer on top.
	$style = 'height:' . $h . ';color:' . $color . ';' . ( trim( $tf ) !== '' ? 'transform:' . trim( $tf ) . ';' : '' );
	return '<div class="sc-shape-divider sc-shape-divider--' . esc_attr( $placement ) . '" style="' . esc_attr( $style ) . '" aria-hidden="true">'
		. '<svg viewBox="' . esc_attr( $viewbox ) . '" preserveAspectRatio="none" fill="currentColor">' . $inner . '</svg>'
		. '</div>';
};
$divider_top_html    = $divider_html( isset( $atts['divider_top'] ) ? $atts['divider_top'] : array(), 'top' );
$divider_bottom_html = $divider_html( isset( $atts['divider_bottom'] ) ? $atts['divider_bottom'] : array(), 'bottom' );
if ( $divider_top_html !== '' || $divider_bottom_html !== '' ) {
	$section_extra_classes .= ' section--has-divider';
}

// Background Pattern — a reusable CSS/HTML pattern drawn as a decorative layer behind the
// content (over the Background). Stored as a preset id; rendered as an aria-hidden .pattern-layer.
$pattern_html = '';
$bp = isset( $atts['background_pattern'] ) ? $atts['background_pattern'] : '';
$pat_id = is_array( $bp ) ? ( isset( $bp['pattern'] ) ? (string) $bp['pattern'] : '' ) : (string) $bp;
if ( $pat_id === 'none' ) { $pat_id = ''; }
if ( $pat_id !== '' && function_exists( 'unysonplus_pattern_render_layer' ) ) {
	$pattern_html = unysonplus_pattern_render_layer( $pat_id );
	if ( $pattern_html !== '' ) {
		$section_extra_classes .= ' upw-has-pattern';
	}
}

$attr = sc_build_wrapper_attr( $atts );

// Min Height / Container Width / vertical-align flex now ride the per-page dynamic CSS file
// (sc_section_dynamic_css → dynamic-css.php) scoped to this section's `.u{hash}`. Ensure that scope
// class is on the wrapper so those rules have a target even when the section carries no user Custom
// CSS (sc_build_wrapper_attr only adds it when custom_css is set). De-duped against the existing class.
if ( function_exists( 'sc_element_scope_class' ) && function_exists( 'sc_section_dynamic_css' ) ) {
	$sec_scope = sc_element_scope_class( $atts );
	if ( $sec_scope !== '' && sc_section_dynamic_css( $atts, $sec_scope ) !== ''
		&& strpos( ' ' . ( isset( $attr['class'] ) ? $attr['class'] : '' ) . ' ', ' ' . $sec_scope . ' ' ) === false ) {
		$section_extra_classes .= ' ' . $sec_scope;
	}
}

if ( ! empty( $section_style ) ) {
	$existing_style = ! empty( $attr['style'] ) ? rtrim( $attr['style'], '; ' ) . '; ' : '';
	$attr['style']  = $existing_style . $section_style;
}

$attr = array_merge( $attr, $bg_video_data_attr );

if ( ! empty( $section_extra_classes ) ) {
	$existing_class = ! empty( $attr['class'] ) ? $attr['class'] . ' ' : '';
	$attr['class']  = $existing_class . trim( $section_extra_classes );
}
?>
<section <?php echo fw_attr_to_html( $attr ); ?>>
<?php echo $pattern_html; // phpcs:ignore WordPress.Security.EscapeOutput — admin-authored, scoped + script-stripped ?>
<?php
	echo $divider_top_html;    // phpcs:ignore WordPress.Security.EscapeOutput — built + value-sanitized above
	echo $divider_bottom_html; // phpcs:ignore WordPress.Security.EscapeOutput
?>
<?php if ( $has_inner_containers ) : // content is already a set of .fw-container[-fluid] sibling bands ?>
	<?php echo do_shortcode( $content ); ?>
<?php else : ?>
	<div class="<?php echo esc_attr( $container_class ); ?>"<?php echo $container_style !== '' ? ' style="' . esc_attr( $container_style ) . '"' : ''; ?>>
		<?php echo do_shortcode( $content ); ?>
	</div>
<?php endif; ?>
</section>
