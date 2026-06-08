<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * @var array  $atts
 * @var string $content
 */

$bg_url = '';
if ( ! empty( $atts['background_image'] ) ) {
	$bi = $atts['background_image'];
	if ( is_array( $bi ) ) {
		if ( ! empty( $bi['url'] ) ) {
			$bg_url = $bi['url'];
		} elseif ( ! empty( $bi['data']['icon'] ) ) {
			$bg_url = $bi['data']['icon'];
		} elseif ( ! empty( $bi['attachment_id'] ) && function_exists( 'wp_get_attachment_url' ) ) {
			$bg_url = wp_get_attachment_url( $bi['attachment_id'] );
		}
	} elseif ( is_numeric( $bi ) && function_exists( 'wp_get_attachment_url' ) ) {
		$bg_url = wp_get_attachment_url( $bi );
	}
}

$parallax_strength = isset( $atts['parallax_strength'] ) ? floatval( $atts['parallax_strength'] ) : 0.4;
$parallax_strength = max( 0, min( 1, $parallax_strength ) );

$overlay_color    = ! empty( $atts['overlay_color'] ) ? $atts['overlay_color'] : '';
$background_color = ! empty( $atts['background_color'] ) ? $atts['background_color'] : '';

$min_height       = ! empty( $atts['min_height'] ) ? $atts['min_height'] : '60vh';
$vertical_align   = ! empty( $atts['content_vertical_align'] ) ? $atts['content_vertical_align'] : 'center';
$container_class  = ( isset( $atts['is_fullwidth'] ) && $atts['is_fullwidth'] === 'yes' )
	? 'fw-container-fluid'
	: 'fw-container';

$attr = function_exists( 'sc_build_wrapper_attr' ) ? sc_build_wrapper_attr( $atts ) : array();

$section_classes = 'fw-hero-section';
if ( ! empty( $attr['class'] ) ) {
	$section_classes .= ' ' . $attr['class'];
}
$attr['class'] = $section_classes;

$section_style  = 'min-height:' . esc_attr( $min_height ) . ';';
$section_style .= 'display:flex;flex-direction:column;justify-content:' . esc_attr( $vertical_align ) . ';';
if ( $background_color ) {
	$section_style .= 'background-color:' . esc_attr( $background_color ) . ';';
}
if ( ! empty( $attr['style'] ) ) {
	$section_style = rtrim( $attr['style'], '; ' ) . ';' . $section_style;
}
$attr['style'] = $section_style;

$attr['data-parallax-strength'] = (string) $parallax_strength;
if ( $bg_url ) {
	$attr['data-parallax-image'] = esc_url( $bg_url );
}
?>
<section <?php echo fw_attr_to_html( $attr ); ?>>
	<?php if ( $bg_url ) : ?>
		<div class="fw-hero-section__bg" style="background-image:url(<?php echo esc_url( $bg_url ); ?>);"></div>
	<?php endif; ?>
	<?php if ( $overlay_color ) : ?>
		<div class="fw-hero-section__overlay" style="background-color:<?php echo esc_attr( $overlay_color ); ?>;"></div>
	<?php endif; ?>
	<div class="fw-hero-section__inner <?php echo esc_attr( $container_class ); ?>">
		<?php echo do_shortcode( $content ); ?>
	</div>
</section>
