<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Featured Image (dynamic) — renders the current queried post/page featured image.
 *
 * @var array $atts
 */

$atts['base_class']       = 'featured-image';
$atts['unique_id_prefix'] = 'fi-';

$post_id = (int) get_the_ID();
if ( ! $post_id ) {
	$post_id = (int) get_queried_object_id();
}
$thumb_id = $post_id ? (int) get_post_thumbnail_id( $post_id ) : 0;
if ( ! $thumb_id ) {
	return;
}

$size    = isset( $atts['image_size'] ) ? (string) $atts['image_size'] : 'large';
$sizes   = function_exists( 'get_intermediate_image_sizes' ) ? get_intermediate_image_sizes() : array();
$sizes[] = 'thumbnail';
$sizes[] = 'medium';
$sizes[] = 'large';
$sizes[] = 'full';
if ( ! in_array( $size, $sizes, true ) ) {
	$size = 'large';
}

$img = wp_get_attachment_image( $thumb_id, $size );
if ( $img === '' ) {
	return;
}

$link_to = isset( $atts['link_to'] ) ? (string) $atts['link_to'] : 'none';
if ( $link_to === 'post' && $post_id ) {
	$img = '<a href="' . esc_url( get_permalink( $post_id ) ) . '">' . $img . '</a>';
} elseif ( $link_to === 'file' ) {
	$full = wp_get_attachment_image_url( $thumb_id, 'full' );
	if ( $full ) {
		$img = '<a href="' . esc_url( $full ) . '">' . $img . '</a>';
	}
}

// Alignment → Bootstrap text-* utility on the wrapper (the inline <img> centers).
$align_map = array( 'left' => 'text-start', 'center' => 'text-center', 'right' => 'text-end' );
$al        = isset( $atts['text_align'] ) ? (string) $atts['text_align'] : '';
if ( isset( $align_map[ $al ] ) ) {
	$atts['css_class'] = trim( ( isset( $atts['css_class'] ) ? $atts['css_class'] : '' ) . ' ' . $align_map[ $al ] );
}

// sc_build_wrapper_attr auto-applies spacing / animation / advanced via filters.
$attr = sc_build_wrapper_attr( $atts );

echo '<div ' . fw_attr_to_html( $attr ) . '>' . $img . '</div>';
