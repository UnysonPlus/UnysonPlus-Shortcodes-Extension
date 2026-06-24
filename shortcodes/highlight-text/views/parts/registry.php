<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Highlight Text — effect registry. Read by options.php (picker), view.php
 * (`fw-hl--fx-<key>`), static.php (per-design CSS gating).
 */
return array(
	'marker'    => array( 'label' => __( 'Marker highlight', 'fw' ), 'thumb' => 'marker.svg' ),
	'gradient'  => array( 'label' => __( 'Gradient fill', 'fw' ),    'thumb' => 'gradient.svg' ),
	'underline' => array( 'label' => __( 'Underline', 'fw' ),        'thumb' => 'underline.svg' ),
	'outline'   => array( 'label' => __( 'Outline text', 'fw' ),     'thumb' => 'outline.svg' ),
	'glow'      => array( 'label' => __( 'Glow', 'fw' ),             'thumb' => 'glow.svg' ),
	'dropcap'   => array( 'label' => __( 'Drop cap', 'fw' ),         'thumb' => 'dropcap.svg' ),
);
