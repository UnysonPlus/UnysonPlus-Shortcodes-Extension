<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Post Date (dynamic) — renders the current queried post's published / modified date.
 *
 * @var array $atts
 */

$atts['base_class']       = 'post-date';
$atts['unique_id_prefix'] = 'pd-';

$post_id = (int) get_the_ID();
if ( ! $post_id ) {
	$post_id = (int) get_queried_object_id();
}
if ( ! $post_id ) {
	return;
}

$format     = isset( $atts['date_format'] ) ? trim( (string) $atts['date_format'] ) : '';
$is_modified = ( isset( $atts['date_type'] ) && $atts['date_type'] === 'modified' );

$date     = $is_modified ? get_the_modified_date( $format, $post_id ) : get_the_date( $format, $post_id );
$datetime = $is_modified ? get_post_modified_time( 'c', false, $post_id ) : get_post_time( 'c', false, $post_id );
if ( $date === '' || $date === false ) {
	return;
}

$inner = '<time datetime="' . esc_attr( $datetime ) . '">' . esc_html( $date ) . '</time>';
if ( isset( $atts['link_to_post'] ) && $atts['link_to_post'] === 'yes' ) {
	$inner = '<a href="' . esc_url( get_permalink( $post_id ) ) . '">' . $inner . '</a>';
}

$align_map = array( 'left' => 'text-start', 'center' => 'text-center', 'right' => 'text-end' );
$al        = isset( $atts['text_align'] ) ? (string) $atts['text_align'] : '';
if ( isset( $align_map[ $al ] ) ) {
	$atts['css_class'] = trim( ( isset( $atts['css_class'] ) ? $atts['css_class'] : '' ) . ' ' . $align_map[ $al ] );
}

$attr = sc_build_wrapper_attr( $atts );

echo '<div ' . fw_attr_to_html( $attr ) . '>' . $inner . '</div>';
