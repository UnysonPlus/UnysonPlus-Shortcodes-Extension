<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Post Terms (dynamic) — renders the current queried post's terms for a taxonomy.
 *
 * @var array $atts
 */

$atts['base_class']       = 'post-terms';
$atts['unique_id_prefix'] = 'pt-';

$post_id = (int) get_the_ID();
if ( ! $post_id ) {
	$post_id = (int) get_queried_object_id();
}
if ( ! $post_id ) {
	return;
}

$taxonomy = isset( $atts['taxonomy'] ) ? (string) $atts['taxonomy'] : 'category';
if ( ! taxonomy_exists( $taxonomy ) ) {
	$taxonomy = 'category';
}

$sep = isset( $atts['term_separator'] ) ? (string) $atts['term_separator'] : ', ';
if ( $sep === '' ) {
	$sep = ', ';
}
$linked = ! ( isset( $atts['link_terms'] ) && $atts['link_terms'] === 'no' );

if ( $linked ) {
	$inner = get_the_term_list( $post_id, $taxonomy, '', esc_html( $sep ), '' );
	if ( is_wp_error( $inner ) || $inner === false || $inner === '' ) {
		return;
	}
} else {
	$terms = get_the_terms( $post_id, $taxonomy );
	if ( ! $terms || is_wp_error( $terms ) ) {
		return;
	}
	$names = array();
	foreach ( $terms as $term ) {
		$names[] = esc_html( $term->name );
	}
	$inner = implode( esc_html( $sep ), $names );
}

$prefix = isset( $atts['term_prefix'] ) ? trim( (string) $atts['term_prefix'] ) : '';
$prefix = ( $prefix !== '' ) ? esc_html( $prefix ) . ' ' : '';

$align_map = array( 'left' => 'text-start', 'center' => 'text-center', 'right' => 'text-end' );
$al        = isset( $atts['text_align'] ) ? (string) $atts['text_align'] : '';
if ( isset( $align_map[ $al ] ) ) {
	$atts['css_class'] = trim( ( isset( $atts['css_class'] ) ? $atts['css_class'] : '' ) . ' ' . $align_map[ $al ] );
}

$attr = sc_build_wrapper_attr( $atts );

echo '<div ' . fw_attr_to_html( $attr ) . '>' . $prefix . $inner . '</div>';
