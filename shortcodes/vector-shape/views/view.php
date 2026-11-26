<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * @var array $atts
 */
$svg = ( isset( $atts['svg'] ) && is_string( $atts['svg'] ) ) ? trim( $atts['svg'] ) : '';
if ( '' !== $svg && function_exists( 'fw_upw_sanitize_svg' ) ) {
	$svg = (string) fw_upw_sanitize_svg( $svg );
}
if ( '' === $svg ) {
	if ( function_exists( 'sc_editor_notice' ) && current_user_can( 'edit_posts' ) ) {
		echo sc_editor_notice( __( 'Vector Shape: no shape yet. Open its settings and choose Draw / edit shape.', 'fw' ) );
	}
	return;
}

$label = isset( $atts['label'] ) ? trim( (string) $atts['label'] ) : '';
// A described shape is an image; an undescribed one is decorative.
$svg = preg_replace(
	'/<svg\b/i',
	'' !== $label ? '<svg role="img" aria-label="' . esc_attr( $label ) . '" focusable="false"' : '<svg aria-hidden="true" focusable="false"',
	$svg,
	1
);

$width = '';
if ( isset( $atts['width'] ) && is_array( $atts['width'] ) && isset( $atts['width']['value'] ) && is_numeric( $atts['width']['value'] ) ) {
	$unit  = ( isset( $atts['width']['unit'] ) && in_array( $atts['width']['unit'], array( 'px', '%', 'em', 'rem', 'vw' ), true ) ) ? $atts['width']['unit'] : 'px';
	$width = 'width:' . ( 0 + $atts['width']['value'] ) . $unit;
}
$mono  = isset( $atts['color_mode'] ) && 'mono' === $atts['color_mode'];
$align = ( isset( $atts['align'] ) && in_array( $atts['align'], array( 'center', 'right' ), true ) ) ? $atts['align'] : '';

$color_style  = '';
$mono_classes = array();
if ( $mono && function_exists( 'sc_extract_styling_atts' ) ) {
	$cs           = sc_extract_styling_atts( $atts, array( 'shape_color' ) );
	$color_style  = $cs['styles'] ? implode( ';', $cs['styles'] ) : '';
	$mono_classes = (array) $cs['classes'];
}

$atts['base_class']       = 'vshape';
$atts['unique_id_prefix'] = 'vs-';
$attr  = sc_build_wrapper_attr( $atts );
$extra = $mono_classes;
if ( $mono ) { $extra[] = 'vshape--mono'; }
if ( $align ) { $extra[] = 'vshape--' . $align; }
if ( $extra ) {
	$attr['class'] = trim( ( isset( $attr['class'] ) ? $attr['class'] : '' ) . ' ' . implode( ' ', array_map( 'sanitize_html_class', $extra ) ) );
}
if ( '' !== $color_style ) {
	$attr['style'] = trim( ( isset( $attr['style'] ) && '' !== $attr['style'] ? rtrim( $attr['style'], '; ' ) . ';' : '' ) . $color_style );
}

$inner = '<span class="vshape__svg"' . ( '' !== $width ? ' style="' . esc_attr( $width ) . '"' : '' ) . '>' . $svg . '</span>';
$link  = isset( $atts['link'] ) ? trim( (string) $atts['link'] ) : '';
if ( '' !== $link ) {
	$inner = '<a class="vshape__link" href="' . esc_url( $link ) . '"' . ( '' !== $label ? '' : ' aria-label="' . esc_attr__( 'Link', 'fw' ) . '"' ) . '>' . $inner . '</a>';
}
?>
<div <?php echo fw_attr_to_html( $attr ); ?>><?php echo $inner; // sanitised SVG + escaped attributes ?></div>
