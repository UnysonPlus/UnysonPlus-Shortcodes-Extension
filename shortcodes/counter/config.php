<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array();

$cfg['page_builder'] = array(
	'title'          => __( 'Animated Counter', 'fw' ),
	'description'    => __( 'A number that counts up when scrolled into view — with prefix/suffix, label and typography control', 'fw' ),
	'tab'            => __( 'Interactive Elements', 'fw' ),
	'popup_size'     => 'medium',
	'title_template' => '<strong>{{= ( o["prefix"] || "" ) + ( o["number"] || "0" ) + ( o["suffix"] || "" ) }}</strong>',
);
