<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Post Excerpt (dynamic) — renders the current queried post/page excerpt.
 *
 * @var array $atts
 */

$atts['base_class']       = 'post-excerpt';
$atts['unique_id_prefix'] = 'pe-';

$post_id = (int) get_the_ID();
if ( ! $post_id ) {
	$post_id = (int) get_queried_object_id();
}
if ( ! $post_id ) {
	return;
}

$excerpt = get_the_excerpt( $post_id );
if ( trim( (string) $excerpt ) === '' ) {
	return;
}

$align_map = array( 'left' => 'text-start', 'center' => 'text-center', 'right' => 'text-end', 'justify' => 'text-justify' );
$al        = isset( $atts['text_align'] ) ? (string) $atts['text_align'] : '';
if ( isset( $align_map[ $al ] ) ) {
	$atts['css_class'] = trim( ( isset( $atts['css_class'] ) ? $atts['css_class'] : '' ) . ' ' . $align_map[ $al ] );
}

$attr = sc_build_wrapper_attr( $atts );

echo '<div ' . fw_attr_to_html( $attr ) . '>' . wpautop( wp_kses_post( $excerpt ) ) . '</div>';
