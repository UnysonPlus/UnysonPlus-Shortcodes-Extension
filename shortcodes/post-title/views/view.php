<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Post Title (dynamic) — renders the current queried post/page title.
 *
 * @var array $atts
 */

$atts['base_class']       = 'post-title';
$atts['unique_id_prefix'] = 'pt-';

// Resolve the post: the loop id first (a Body Template sets it up), else the main
// queried object (so it also works outside a loop).
$post_id = (int) get_the_ID();
if ( ! $post_id ) {
	$post_id = (int) get_queried_object_id();
}
$title = $post_id ? get_the_title( $post_id ) : '';
if ( $title === '' ) {
	return;
}

// Alignment → Bootstrap text-* utility on the heading (kept off as inline style).
$align_map = array( 'left' => 'text-start', 'center' => 'text-center', 'right' => 'text-end', 'justify' => 'text-justify' );
$al        = isset( $atts['text_align'] ) ? (string) $atts['text_align'] : '';
if ( isset( $align_map[ $al ] ) ) {
	$atts['css_class'] = trim( ( isset( $atts['css_class'] ) ? $atts['css_class'] : '' ) . ' ' . $align_map[ $al ] );
}

$allowed_tags = array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p' );
$htag         = ( isset( $atts['heading_tag'] ) && in_array( $atts['heading_tag'], $allowed_tags, true ) ) ? $atts['heading_tag'] : 'h1';

$inner = esc_html( $title );
if ( isset( $atts['link_to_post'] ) && $atts['link_to_post'] === 'yes' && $post_id ) {
	$inner = '<a href="' . esc_url( get_permalink( $post_id ) ) . '">' . $inner . '</a>';
}

// sc_build_wrapper_attr auto-applies text_color / font_size_preset / spacing /
// animation / advanced (css_class, css_id, custom attrs) via its filters.
$attr = sc_build_wrapper_attr( $atts );

echo '<' . $htag . ' ' . fw_attr_to_html( $attr ) . '>' . $inner . '</' . $htag . '>';
