<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array();

$cfg['page_builder'] = array(
	'title'       => __( 'Text Block', 'fw' ),
	'description' => __( 'Add a Text Block', 'fw' ),
	'tab'         => __( 'Content Elements', 'fw' ),
	'popup_size'    => 'medium', // can be large, medium or small
	'title_template' => '<div>{{= o.text }}</div>',
);
