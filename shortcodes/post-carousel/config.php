<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array();

$cfg['page_builder'] = array(
	'title'       => __( 'Post Carousel', 'fw' ),
	'description' => __( 'A touch-friendly slider of posts (or any post type) — image, title, excerpt, meta and a read-more, in several card designs.', 'fw' ),
	'tab'         => __( 'Components', 'fw' ),
	'popup_size'  => 'large',

	'title_template' => '<strong>{{= ( o && o["post_type"] ) ? ( "Carousel: " + o["post_type"] ) : "Post Carousel" }}</strong>',
);
