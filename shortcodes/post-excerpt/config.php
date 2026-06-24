<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array();

$cfg['page_builder'] = array(
	'title'          => __( 'Post Excerpt', 'fw' ),
	'description'    => __( 'Display the current post or page excerpt. Dynamic — reads the post being viewed. Built for Theme Builder Body Templates.', 'fw' ),
	'tab'            => __( 'Dynamic Content', 'fw' ),
	'popup_size'     => 'medium',
	'title_template' => '<div>Post Excerpt <small>(dynamic)</small></div>',
);
