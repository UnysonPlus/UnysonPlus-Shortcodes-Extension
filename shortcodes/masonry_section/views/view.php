<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * @var array  $atts
 * @var string $content
 */

$cols_lg = isset( $atts['cols_lg'] ) && $atts['cols_lg'] !== '' ? (int) $atts['cols_lg'] : 3;
$cols_md = isset( $atts['cols_md'] ) && $atts['cols_md'] !== '' ? (int) $atts['cols_md'] : 2;
$cols_sm = isset( $atts['cols_sm'] ) && $atts['cols_sm'] !== '' ? (int) $atts['cols_sm'] : 1;
$gap     = ! empty( $atts['gap'] ) ? $atts['gap'] : '1.5rem';

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
$style .= '--mc-lg:' . max( 1, $cols_lg ) . ';';
$style .= '--mc-md:' . max( 1, $cols_md ) . ';';
$style .= '--mc-sm:' . max( 1, $cols_sm ) . ';';
$style .= '--mc-gap:' . $gap . ';';
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
