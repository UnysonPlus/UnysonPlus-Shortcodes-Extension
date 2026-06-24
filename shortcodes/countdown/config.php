<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array();

$cfg['page_builder'] = array(
	'title'          => __( 'Countdown Timer', 'fw' ),
	'description'    => __( 'A live countdown to a target date & time — days, hours, minutes, seconds, with full typography control', 'fw' ),
	'tab'            => __( 'Interactive Elements', 'fw' ),
	'popup_size'     => 'medium',
	'title_template' => '<strong>{{= o["target"] ? o["target"] : "Countdown Timer" }}</strong>',
);
