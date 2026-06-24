<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Post Carousel — card design registry. Read by options.php (picker), view.php
 * (`fw-pc--design-<key>`), static.php (per-design CSS gating).
 */
return array(
	'standard' => array( 'label' => __( 'Standard — image top', 'fw' ),    'thumb' => 'standard.svg' ),
	'overlay'  => array( 'label' => __( 'Overlay — text over image', 'fw' ),'thumb' => 'overlay.svg' ),
	'minimal'  => array( 'label' => __( 'Minimal — no image', 'fw' ),       'thumb' => 'minimal.svg' ),
);
