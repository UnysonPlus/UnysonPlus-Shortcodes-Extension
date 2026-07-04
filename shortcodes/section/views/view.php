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

$variant = ( isset( $atts['variant'] ) && in_array( $atts['variant'], array( 'alt', 'light', 'dark' ), true ) )
	? $atts['variant']
	: '';

if ( $variant !== '' ) {
	$section_extra_classes .= ' section--' . $variant;
}

// Per-section column-gap modifier classes — picked up by css-tokens.php's
// `.section--gap-{slug} .row` / `-x-` / `-y-` rules.
foreach ( array( 'gap' => 'section--gap-', 'gap_x' => 'section--gap-x-', 'gap_y' => 'section--gap-y-' ) as $att_key => $class_prefix ) {
	if ( empty( $atts[ $att_key ] ) ) { continue; }
	$slug = preg_replace( '/[^a-zA-Z0-9_-]/', '', (string) $atts[ $att_key ] );
	if ( $slug === '' ) { continue; }
	$section_extra_classes .= ' ' . $class_prefix . strtolower( $slug );
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
		$min_height = $preset; // e.g. "40vh" ('auto' means no min-height)
	}
} elseif ( is_string( $mh ) && $mh !== 'auto' ) {
	$min_height = trim( $mh ); // legacy: min_height saved as a plain string
}

// Columns Vertical Alignment (id: column_valign; the now-renamed old key
// content_valign is tolerated as a fallback). Positions the whole content block
// vertically within a tall (min-height) section via the section flex column.
$valign = isset( $atts['column_valign'] )
	? (string) $atts['column_valign']
	: ( isset( $atts['content_valign'] ) ? (string) $atts['content_valign'] : '' );
if ( $min_height !== '' ) {
	$section_style .= 'min-height:' . esc_attr( $min_height ) . ';';
}
if ( $valign === 'stretch' ) {
	// Default / Stretched — the columns fill the section height. Only meaningful with a Min
	// Height; the .section--valign-stretch rules grow the container + row to fill the section.
	if ( $min_height !== '' ) { $section_extra_classes .= ' section--valign-stretch'; }
} elseif ( $min_height !== '' || $valign === 'center' || $valign === 'bottom' ) {
	// Top (also the fallback for a legacy/empty value) / Center / Bottom — position the
	// content-height columns block within the taller section.
	$valign_map = array( 'center' => 'center', 'bottom' => 'flex-end' );
	$justify    = isset( $valign_map[ $valign ] ) ? $valign_map[ $valign ] : 'flex-start';
	$section_style .= 'display:flex;flex-direction:column;justify-content:' . $justify . ';';
}

// Columns Horizontal Alignment (id: column_halign). Routed through a modifier
// class so it can reach this section's auto-generated .fw-row(s) — same pattern
// as the section--gap-* classes above. Default (left) needs no class.
$halign = isset( $atts['column_halign'] ) ? (string) $atts['column_halign'] : '';
if ( in_array( $halign, array( 'center', 'right', 'between', 'around', 'evenly' ), true ) ) {
	$section_extra_classes .= ' section--cols-' . $halign;
}

// Reverse column order (id: reverse_columns) → modifier class on this section's row(s).
// "all" swaps side-by-side columns (row-reverse) and reverses the mobile stack;
// "mobile" only reverses the stack when the columns stack on phones.
$reverse = isset( $atts['reverse_columns'] ) ? (string) $atts['reverse_columns'] : '';
if ( $reverse === 'all' ) {
	$section_extra_classes .= ' section--rev';
} elseif ( $reverse === 'tablet' ) {
	$section_extra_classes .= ' section--rev-tablet';
} elseif ( $reverse === 'mobile' ) {
	$section_extra_classes .= ' section--rev-mobile';
}

$container_class = ( isset( $atts['is_fullwidth'] ) && $atts['is_fullwidth'] )
	? 'fw-container-fluid'
	: 'fw-container';

// When this section holds one or more Container elements, the items-corrector has already
// lifted the section's OWN columns into a default .fw-container item and kept the Container
// elements as siblings — so we render the corrected content directly and DON'T add our own
// .fw-container (that would nest them). Sections without a Container keep the original markup.
$has_inner_containers = ! empty( $atts['has_inner_containers'] );

// --- Shape Dividers (top / bottom) — an SVG-shaped edge at the section's top and/or bottom. ---
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
	if ( ! isset( $divider_paths[ $shape ] ) ) { return ''; }
	$sub   = ( isset( $dv[ $shape ] ) && is_array( $dv[ $shape ] ) ) ? $dv[ $shape ] : array();
	$color = $divider_color( isset( $sub['color'] ) ? $sub['color'] : array() );
	$h     = '100px';
	if ( isset( $sub['height'] ) && is_array( $sub['height'] ) ) {
		$num  = isset( $sub['height']['value'] ) ? trim( (string) $sub['height']['value'] ) : '';
		$unit = ( isset( $sub['height']['unit'] ) && in_array( $sub['height']['unit'], array( 'px', 'vh', '%' ), true ) ) ? $sub['height']['unit'] : 'px';
		if ( $num !== '' && is_numeric( $num ) ) { $h = $num . $unit; }
	}
	$flip = ( isset( $sub['flip'] ) && $sub['flip'] === 'yes' );
	// Top divider = the shape rotated 180° so it reads at the top edge; flip mirrors it.
	$tf    = ( $placement === 'top' ? 'rotate(180deg)' : '' ) . ( $flip ? ' scaleX(-1)' : '' );
	$style = 'height:' . $h . ';' . ( trim( $tf ) !== '' ? 'transform:' . trim( $tf ) . ';' : '' );
	return '<div class="sc-shape-divider sc-shape-divider--' . esc_attr( $placement ) . '" style="' . esc_attr( $style ) . '" aria-hidden="true">'
		. '<svg viewBox="0 0 1200 120" preserveAspectRatio="none" style="fill:' . esc_attr( $color ) . '"><path d="' . esc_attr( $divider_paths[ $shape ] ) . '"></path></svg>'
		. '</div>';
};
$divider_top_html    = $divider_html( isset( $atts['divider_top'] ) ? $atts['divider_top'] : array(), 'top' );
$divider_bottom_html = $divider_html( isset( $atts['divider_bottom'] ) ? $atts['divider_bottom'] : array(), 'bottom' );
if ( $divider_top_html !== '' || $divider_bottom_html !== '' ) {
	$section_extra_classes .= ' section--has-divider';
}

$attr = sc_build_wrapper_attr( $atts );

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
<?php
	echo $divider_top_html;    // phpcs:ignore WordPress.Security.EscapeOutput — built + value-sanitized above
	echo $divider_bottom_html; // phpcs:ignore WordPress.Security.EscapeOutput
?>
<?php if ( $has_inner_containers ) : // content is already a set of .fw-container[-fluid] sibling bands ?>
	<?php echo do_shortcode( $content ); ?>
<?php else : ?>
	<div class="<?php echo esc_attr( $container_class ); ?>">
		<?php echo do_shortcode( $content ); ?>
	</div>
<?php endif; ?>
</section>
