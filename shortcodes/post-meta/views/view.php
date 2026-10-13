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
	// Editor-only note; a visitor still gets nothing at all.
	if ( fw_is_editor_context() ) {
		echo sc_editor_notice( __( 'Enter the custom field name to display.', 'fw' ) );
	}
	return;
}

$post_id = (int) get_the_ID();
if ( ! $post_id ) {
	$post_id = (int) get_queried_object_id();
}
if ( ! $post_id ) {
	if ( fw_is_editor_context() ) {
		echo sc_editor_notice( __( 'No post to read the field from — this element belongs in a post or a template.', 'fw' ) );
	}
	return;
}

// Custom Fields stores its values with the post's Unyson+ options, not as separate post meta, so
// read there first, then fall back to plain post meta (fields saved by code or other plugins).
$value = function_exists( 'fw_get_db_post_option' ) ? fw_get_db_post_option( $post_id, $key, null ) : null;
if ( $value === null || $value === '' || $value === array() ) {
	$value = get_post_meta( $post_id, $key, true );
}
if ( is_array( $value ) ) {
	if ( isset( $value['value'] ) && ! is_array( $value['value'] ) ) {
		// A measurement field: { value, unit }.
		$value = trim( $value['value'] . ' ' . ( isset( $value['unit'] ) && ! is_array( $value['unit'] ) ? $value['unit'] : '' ) );
	} else {
		// A list or choice field: show the plain values, skip nested data such as uploads.
		$value = implode( ', ', array_filter( array_map( function ( $v ) { return is_scalar( $v ) ? (string) $v : ''; }, $value ), 'strlen' ) );
	}
}
$value = is_scalar( $value ) ? (string) $value : '';
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
