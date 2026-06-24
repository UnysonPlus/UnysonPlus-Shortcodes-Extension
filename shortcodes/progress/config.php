<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array();

$cfg['page_builder'] = array(
	'title'          => __( 'Progress Bars', 'fw' ),
	'description'    => __( 'Animated skill / progress bars — label + percentage, filling to width when scrolled into view', 'fw' ),
	'tab'            => __( 'Interactive Elements', 'fw' ),
	'popup_size'     => 'large',
	'title_template' => '<strong>{{= o["bars"] ? "Progress Bars (" + (o["bars"].length || 0) + ")" : "Progress Bars" }}</strong>',
);
