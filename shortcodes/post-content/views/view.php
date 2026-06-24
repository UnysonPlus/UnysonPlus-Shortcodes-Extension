<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Post Content (dynamic) — renders the current queried post/page content through
 * the standard the_content pipeline (wpautop, shortcodes, embeds, blocks).
 *
 * @var array $atts
 */

$atts['base_class']       = 'post-content';
$atts['unique_id_prefix'] = 'pc-';

$post_id = (int) get_the_ID();
if ( ! $post_id ) {
	$post_id = (int) get_queried_object_id();
}
if ( ! $post_id ) {
	return;
}

$post = get_post( $post_id );
if ( ! $post || trim( (string) $post->post_content ) === '' ) {
	return;
}

// Render through the_content so editor formatting, shortcodes and blocks work.
// The Body Template only fires for NON-builder posts (see the extension's
// template_include guard), so this never re-enters the builder render path.
$content = apply_filters( 'the_content', $post->post_content );
if ( trim( (string) $content ) === '' ) {
	return;
}

// Alignment → Bootstrap text-* utility on the wrapper.
$align_map = array( 'left' => 'text-start', 'center' => 'text-center', 'right' => 'text-end', 'justify' => 'text-justify' );
$al        = isset( $atts['text_align'] ) ? (string) $atts['text_align'] : '';
if ( isset( $align_map[ $al ] ) ) {
	$atts['css_class'] = trim( ( isset( $atts['css_class'] ) ? $atts['css_class'] : '' ) . ' ' . $align_map[ $al ] );
}

// sc_build_wrapper_attr auto-applies text_color / font_size_preset / spacing /
// animation / advanced via its filters.
$attr = sc_build_wrapper_attr( $atts );

echo '<div ' . fw_attr_to_html( $attr ) . '>' . $content . '</div>';
