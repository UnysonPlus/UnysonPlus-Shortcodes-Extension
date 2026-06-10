<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * @var array  $atts
 * @var string $content
 */

// Only emit --mc-gap when the user picked an EXPLICIT gap. When empty (the
// default), we leave it unset so the CSS falls back to the site/section gutter
// (`--bs-gutter-x`) — exactly what a standard `[section]` row uses — so the
// masonry's gaps match the rest of the site instead of hardcoding 1.5rem.
$gap = isset( $atts['gap'] ) ? trim( (string) $atts['gap'] ) : '';

// Background: prefer the background-pro value; fall back to the legacy
// background_color (a hex) for masonry sections saved before the upgrade.
$bgv      = ( ! empty( $atts['background'] ) && is_array( $atts['background'] ) ) ? $atts['background'] : null;
$bg_style = function_exists( 'sc_bg_pro_style' ) ? sc_bg_pro_style( $bgv ) : '';
if ( $bg_style === '' && ! empty( $atts['background_color'] ) ) {
	$bg_style = 'background-color:' . $atts['background_color'] . ';';
}
$bg_video_attr    = function_exists( 'sc_bg_pro_video_attr' ) ? sc_bg_pro_video_attr( $bgv ) : array();
$padding_top      = isset( $atts['padding_top'] ) ? trim( $atts['padding_top'] ) : '';
$padding_bottom   = isset( $atts['padding_bottom'] ) ? trim( $atts['padding_bottom'] ) : '';

$container_class = ( isset( $atts['is_fullwidth'] ) && $atts['is_fullwidth'] === 'yes' )
	? 'fw-container-fluid'
	: 'fw-container';

$attr = function_exists( 'sc_build_wrapper_attr' ) ? sc_build_wrapper_attr( $atts ) : array();

$classes = 'masonry-section';
if ( ! empty( $attr['class'] ) ) {
	$classes .= ' ' . $attr['class'];
}
$attr['class'] = $classes;

$style  = '';
if ( $gap !== '' ) {
	$style .= '--mc-gap:' . $gap . ';';
}
if ( $bg_style !== '' ) {
	$style .= $bg_style;
}
if ( $padding_top !== '' ) {
	$style .= 'padding-top:' . $padding_top . ';';
}
if ( $padding_bottom !== '' ) {
	$style .= 'padding-bottom:' . $padding_bottom . ';';
}
if ( ! empty( $attr['style'] ) ) {
	$style = rtrim( $attr['style'], '; ' ) . ';' . $style;
}
$attr['style'] = $style;

// Background video (best-effort): emits the Formstone data-attr + class. Playback
// relies on the section video script being present on the page.
if ( ! empty( $bg_video_attr ) ) {
	$attr = array_merge( $attr, $bg_video_attr );
	$attr['class'] = trim( $attr['class'] . ' background-video' );
}
?>
<section <?php echo fw_attr_to_html( $attr ); ?>>
	<div class="<?php echo esc_attr( $container_class ); ?>">
		<?php echo do_shortcode( $content ); ?>
	</div>
</section>
