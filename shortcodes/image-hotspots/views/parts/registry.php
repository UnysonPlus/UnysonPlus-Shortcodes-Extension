<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Image Hotspots — pin design registry. Read by options.php (picker), view.php
 * (`fw-hs--design-<key>`), static.php (per-design CSS gating).
 */
return array(
	'pulse'    => array( 'label' => __( 'Pulsing dot', 'fw' ),  'thumb' => 'pulse.svg' ),
	'dot'      => array( 'label' => __( 'Plain dot', 'fw' ),    'thumb' => 'dot.svg' ),
	'numbered' => array( 'label' => __( 'Numbered', 'fw' ),     'thumb' => 'numbered.svg' ),
	'icon'     => array( 'label' => __( 'Icon / plus', 'fw' ),  'thumb' => 'icon.svg' ),
);
