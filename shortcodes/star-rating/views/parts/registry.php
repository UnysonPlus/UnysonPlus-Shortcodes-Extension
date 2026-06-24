<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Star Rating — symbol design registry. Read by options.php (picker), view.php
 * (`fw-sr--design-<key>`), static.php (per-design CSS gating).
 */
return array(
	'star'   => array( 'label' => __( 'Stars', 'fw' ),   'thumb' => 'star.svg' ),
	'heart'  => array( 'label' => __( 'Hearts', 'fw' ),  'thumb' => 'heart.svg' ),
	'circle' => array( 'label' => __( 'Circles', 'fw' ), 'thumb' => 'circle.svg' ),
	'bar'    => array( 'label' => __( 'Bar', 'fw' ),      'thumb' => 'bar.svg' ),
);
