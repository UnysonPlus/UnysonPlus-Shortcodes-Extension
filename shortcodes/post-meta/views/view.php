<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Post Meta (dynamic) — renders a custom field value from the queried post.
 *
 * @var array $atts
 */

$atts['base_class']       = 'post-meta';
$atts['unique_id_prefix'] = 'pm-';

$key = isset( $atts['meta_key'] ) ? trim( (string) $atts['meta_key'] ) : '';
if ( $key === '' ) {
	return;
}

$post_id = (int) get_the_ID();
if ( ! $post_id ) {
	$post_id = (int) get_queried_object_id();
}
if ( ! $post_id ) {
	return;
}

$value = get_post_meta( $post_id, $key, true );
if ( is_array( $value ) ) {
	$value = implode( ', ', array_map( 'strval', $value ) );
}
$value = (string) $value;
if ( trim( $value ) === '' ) {
	return;
}

$before = isset( $atts['before_text'] ) ? esc_html( (string) $atts['before_text'] ) : '';
$after  = isset( $atts['after_text'] ) ? esc_html( (string) $atts['after_text'] ) : '';
$inner  = $before . esc_html( $value ) . $after;

$align_map = array( 'left' => 'text-start', 'center' => 'text-center', 'right' => 'text-end' );
$al        = isset( $atts['text_align'] ) ? (string) $atts['text_align'] : '';
if ( isset( $align_map[ $al ] ) ) {
	$atts['css_class'] = trim( ( isset( $atts['css_class'] ) ? $atts['css_class'] : '' ) . ' ' . $align_map[ $al ] );
}

$attr = sc_build_wrapper_attr( $atts );

echo '<div ' . fw_attr_to_html( $attr ) . '>' . $inner . '</div>';
