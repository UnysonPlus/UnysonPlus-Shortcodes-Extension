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
<?php if ( $has_inner_containers ) : // content is already a set of .fw-container[-fluid] sibling bands ?>
	<?php echo do_shortcode( $content ); ?>
<?php else : ?>
	<div class="<?php echo esc_attr( $container_class ); ?>">
		<?php echo do_shortcode( $content ); ?>
	</div>
<?php endif; ?>
</section>
