<?php if ( ! defined( 'FW' ) ) { die( 'Forbidden' ); }

$cfg = array();

$cfg['page_builder'] = array(
	'title'       => __( 'Code Block', 'fw' ),
	'description' => __( 'Add a HTML/CSS/Javascript Block', 'fw' ),
	'tab'         => __( 'Content Elements', 'fw' ),
	'popup_size'    => 'large', // can be large, medium or small
	'title_template' => '<div>{{= o.code }}</div>',
);
