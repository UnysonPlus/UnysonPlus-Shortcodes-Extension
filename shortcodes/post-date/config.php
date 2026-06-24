<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array();

$cfg['page_builder'] = array(
	'title'          => __( 'Post Date', 'fw' ),
	'description'    => __( 'Display the current post published or modified date. Dynamic — reads the post being viewed. Built for Theme Builder Body Templates.', 'fw' ),
	'tab'            => __( 'Dynamic Content', 'fw' ),
	'popup_size'     => 'medium',
	'title_template' => '<div>Post Date <small>(dynamic)</small></div>',
);
