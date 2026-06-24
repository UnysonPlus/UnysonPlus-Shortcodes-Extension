<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array();

$cfg['page_builder'] = array(
	'title'          => __( 'Post Terms', 'fw' ),
	'description'    => __( 'Display the current post\'s categories, tags, or any taxonomy terms. Dynamic — reads the post being viewed. Built for Theme Builder Body Templates.', 'fw' ),
	'tab'            => __( 'Dynamic Content', 'fw' ),
	'popup_size'     => 'medium',
	'title_template' => '<div>Post Terms <small>(dynamic)</small></div>',
);
