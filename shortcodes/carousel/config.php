<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array();

$cfg['page_builder'] = array(
	'title'          => __( 'Carousel / Slider', 'fw' ),
	'description'    => __( 'A flexible, touch-friendly carousel — image/heading/text/button slides, multi-slide-per-view, autoplay, loop, arrows & dots (Splide)', 'fw' ),
	'tab'            => __( 'Interactive Elements', 'fw' ),
	'popup_size'     => 'large',
	'title_template' => '<strong>{{= o["slides"] ? "Carousel (" + (o["slides"].length || 0) + " slides)" : "Carousel" }}</strong>',
);
