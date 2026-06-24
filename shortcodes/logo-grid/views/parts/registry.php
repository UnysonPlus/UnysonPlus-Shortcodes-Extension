<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Logo Grid — layout registry. Read by options.php (picker), view.php
 * (`fw-lg--design-<key>`), static.php (per-design CSS gating + Splide for carousel).
 *
 *   splide : true → the design needs the vendored Splide slider (carousel)
 */
return array(
	'grid'     => array( 'label' => __( 'Grid', 'fw' ),            'thumb' => 'grid.svg',     'splide' => false ),
	'boxed'    => array( 'label' => __( 'Boxed grid', 'fw' ),      'thumb' => 'boxed.svg',    'splide' => false ),
	'carousel' => array( 'label' => __( 'Carousel', 'fw' ),        'thumb' => 'carousel.svg', 'splide' => true ),
	'marquee'  => array( 'label' => __( 'Marquee / ticker', 'fw' ),'thumb' => 'marquee.svg',  'splide' => false ),
);
