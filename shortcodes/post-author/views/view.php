<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Post Author (dynamic) — renders the current queried post's author.
 *
 * @var array $atts
 */

$atts['base_class']       = 'post-author';
$atts['unique_id_prefix'] = 'pa-';

$post_id = (int) get_the_ID();
if ( ! $post_id ) {
	$post_id = (int) get_queried_object_id();
}
$author_id = $post_id ? (int) get_post_field( 'post_author', $post_id ) : 0;
if ( ! $author_id ) {
	return;
}

$name = get_the_author_meta( 'display_name', $author_id );
if ( $name === '' ) {
	return;
}

$name_html = esc_html( $name );
if ( isset( $atts['link_to_author'] ) && $atts['link_to_author'] === 'yes' ) {
	$name_html = '<a href="' . esc_url( get_author_posts_url( $author_id ) ) . '">' . $name_html . '</a>';
}

$avatar = '';
if ( isset( $atts['show_avatar'] ) && $atts['show_avatar'] === 'yes' ) {
	$size   = isset( $atts['avatar_size'] ) ? max( 16, min( 256, (int) $atts['avatar_size'] ) ) : 48;
	$avatar = get_avatar( $author_id, $size, '', $name, array( 'class' => 'post-author__avatar' ) );
	if ( $avatar ) {
		$avatar .= ' ';
	}
}

$prefix = isset( $atts['author_prefix'] ) ? trim( (string) $atts['author_prefix'] ) : '';
$prefix = ( $prefix !== '' ) ? esc_html( $prefix ) . ' ' : '';

$align_map = array( 'left' => 'text-start', 'center' => 'text-center', 'right' => 'text-end' );
$al        = isset( $atts['text_align'] ) ? (string) $atts['text_align'] : '';
if ( isset( $align_map[ $al ] ) ) {
	$atts['css_class'] = trim( ( isset( $atts['css_class'] ) ? $atts['css_class'] : '' ) . ' ' . $align_map[ $al ] );
}

$attr = sc_build_wrapper_attr( $atts );

echo '<div ' . fw_attr_to_html( $attr ) . '>' . $avatar . $prefix . $name_html . '</div>';
