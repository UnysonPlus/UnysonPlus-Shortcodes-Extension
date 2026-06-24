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

$attachment_id = ! empty( $atts['image']['attachment_id'] ) ? $atts['image']['attachment_id'] : 0;

// Image source: attachment ID (enables responsive srcset + exact-crop) or URL.
$image_url = ! empty( $atts['image']['url'] ) ? $atts['image']['url'] : '';
if ( empty( $image_url ) && empty( $attachment_id ) ) {
	return;
}

// Wrapper attributes. The sc_build_wrapper_attr filter chain folds the Styling
// tab (background color, margin & padding) and Animations into $attr as classes
// + inline style, so a wrapper is only worth rendering when something actually
// needs it (styling, animation, CSS id/class, custom attrs).
$atts['base_class']       = 'image';
$atts['unique_id_prefix'] = 'img-';
$attr = sc_build_wrapper_attr( $atts );

$should_wrap = function_exists( 'sc_needs_wrapper' )
	? sc_needs_wrapper( $atts )
	: ( ! empty( $atts['css_id'] ) || ! empty( $atts['css_class'] ) );

$css_id = isset( $attr['id'] ) ? $attr['id'] : '';

// Class placement: with a wrapper, the <div> owns base/unique/styling classes
// and the id; the <img> only needs the responsive helper. Without a wrapper the
// <img> (or its <a>) keeps carrying them, exactly as before.
if ( $should_wrap ) {
	$img_classes = array( 'img-fluid' );
} else {
	$img_classes = array_values( array_filter( preg_split( '/\s+/', trim( isset( $attr['class'] ) ? $attr['class'] : '' ) ) ) );
	if ( ! in_array( 'img-fluid', $img_classes, true ) ) {
		$img_classes[] = 'img-fluid';
	}
}

$link     = ! empty( $atts['link'] ) ? $atts['link'] : '';
$has_link = ( '' !== $link );

// CSS id lands on the <img> only when no wrapper / link will carry it.
$extra_attr = array();
if ( ! $should_wrap && ! $has_link && $css_id ) {
	$extra_attr['id'] = $css_id;
}

// Modern <img>: responsive srcset (or exact-crop + 2x), width/height attrs for
// CLS, fetchpriority/eager for above-the-fold, lazy otherwise — via fw_image_tag.
$fetchpriority = ( ! empty( $atts['fetchpriority'] ) && 'high' === $atts['fetchpriority'] ) ? 'high' : '';

$img_html = fw_image_tag(
	$attachment_id ? $attachment_id : $image_url,
	array(
		'width'         => isset( $atts['width'] ) ? $atts['width'] : '',
		'height'        => isset( $atts['height'] ) ? $atts['height'] : '',
		'class'         => implode( ' ', $img_classes ),
		'fetchpriority' => $fetchpriority,
		'fallback_size' => 'large',
		'extra_attr'    => $extra_attr,
	)
);

if ( '' === $img_html ) {
	return;
}

if ( $has_link ) {
	$target = ( ! empty( $atts['target'] ) && in_array( $atts['target'], array( '_self', '_blank' ), true ) )
		? $atts['target']
		: '_self';

	// Build the anchor by hand: esc_url (not fw_html_tag) so query-string
	// ampersands in the link aren't double-encoded, and unsafe protocols
	// (e.g. javascript:) are stripped.
	$anchor = '<a href="' . esc_url( $link ) . '" target="' . esc_attr( $target ) . '"';

	// Harden links that open a new tab against window.opener hijacking / referrer leakage.
	if ( '_blank' === $target ) {
		$anchor .= ' rel="noopener noreferrer"';
	}
	if ( ! $should_wrap && $css_id ) {
		$anchor .= ' id="' . esc_attr( $css_id ) . '"';
	}
	$anchor .= '>';

	$inner_html = $anchor . $img_html . '</a>';
} else {
	$inner_html = $img_html;
}

// Render the wrapper only when it carries something (styling / animation / id /
// class / custom attrs). Padding on this wrapper is what makes a background
// color show as a frame around the image.
if ( $should_wrap ) {
	echo '<div ' . fw_attr_to_html( $attr ) . '>' . $inner_html . '</div>';
} else {
	echo $inner_html;
}
