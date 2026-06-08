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

$background_color = ! empty( $atts['background_color'] ) ? $atts['background_color'] : '';
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
if ( $background_color ) {
	$style .= 'background-color:' . $background_color . ';';
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
?>
<section <?php echo fw_attr_to_html( $attr ); ?>>
	<div class="<?php echo esc_attr( $container_class ); ?>">
		<?php echo do_shortcode( $content ); ?>
	</div>
</section>
