<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * @var array $atts
 */

// Skip if no image
if ( empty( $atts['image'] ) ) {
	return;
}

// Width & height
$width  = ( is_numeric( $atts['width'] ) && ( $atts['width'] > 0 ) ) ? (int) $atts['width'] : '';
$height = ( is_numeric( $atts['height'] ) && ( $atts['height'] > 0 ) ) ? (int) $atts['height'] : '';

// Resize or use original
if ( ! empty( $width ) && ! empty( $height ) ) {
	$image = fw_resize( $atts['image']['attachment_id'], $width, $height, true );
} else {
	$image = $atts['image']['url'];
}

// Alt text
$alt = get_post_meta( $atts['image']['attachment_id'], '_wp_attachment_image_alt', true );

// Build wrapper attributes
$atts['base_class']       = 'image';
$atts['unique_id_prefix'] = 'img-';
$attr = sc_build_wrapper_attr( $atts );

// Extract id + classes
$css_id    = isset( $attr['id'] ) ? $attr['id'] : '';
$css_class = isset( $attr['class'] ) ? $attr['class'] : '';

// Ensure img-fluid is placed 3rd
$css_class_parts = preg_split( '/\s+/', trim( $css_class ) );
$css_class_parts = array_filter( $css_class_parts ); // remove empties

// Remove any existing img-fluid to avoid duplicates
$css_class_parts = array_diff( $css_class_parts, [ 'img-fluid' ] );

// Rebuild with img-fluid at 3rd position
if ( count( $css_class_parts ) >= 2 ) {
	$ordered_classes = [
		$css_class_parts[0],          // base "image"
		$css_class_parts[1],          // unique "img-xxxxxx"
		'img-fluid'                   // always third
	];
	$remaining = array_slice( $css_class_parts, 2 );
	$css_class = implode( ' ', array_merge( $ordered_classes, $remaining ) );
} else {
	// Fallback if something weird happens
	array_splice( $css_class_parts, 2, 0, 'img-fluid' );
	$css_class = implode( ' ', $css_class_parts );
}

// Build img attributes
$img_attributes = array(
	'src'   => $image,
	'alt'   => $alt ? $alt : $image,
	'class' => $css_class,
);

if ( ! empty( $width ) ) {
	$img_attributes['width'] = $width;
}

if ( ! empty( $height ) ) {
	$img_attributes['height'] = $height;
}

// Output
if ( empty( $atts['link'] ) ) {
	echo fw_html_tag( 'img', $img_attributes );
} else {
	$link_attr = array(
		'href' => $atts['link'],
		'target' => $atts['target'],
	);
	if ( ! empty( $css_id ) ) {
		$link_attr['id'] = $css_id;
	}
	echo fw_html_tag( 'a', $link_attr, fw_html_tag( 'img', $img_attributes ) );
}
