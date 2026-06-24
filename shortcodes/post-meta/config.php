<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array();

$cfg['page_builder'] = array(
	'title'          => __( 'Post Meta', 'fw' ),
	'description'    => __( 'Display a custom field (post meta) value by key. Dynamic — reads the post being viewed. Built for Theme Builder Body Templates.', 'fw' ),
	'tab'            => __( 'Dynamic Content', 'fw' ),
	'popup_size'     => 'medium',
	'title_template' => '<div>Post Meta <small>(dynamic)</small></div>',
);
