<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Social Share — design (button style) registry. Read by options.php (picker),
 * view.php (`fw-ss--design-<key>`), static.php (per-design CSS gating).
 */
return array(
	'brand' => array(
		'label' => __( 'Brand colors (filled)', 'fw' ),
		'thumb' => 'brand.svg',
	),
	'mono' => array(
		'label' => __( 'Monochrome (filled)', 'fw' ),
		'thumb' => 'mono.svg',
	),
	'outline' => array(
		'label' => __( 'Outline (brand)', 'fw' ),
		'thumb' => 'outline.svg',
	),
	'soft' => array(
		'label' => __( 'Soft tint', 'fw' ),
		'thumb' => 'soft.svg',
	),
	'text' => array(
		'label' => __( 'Minimal — icon + label', 'fw' ),
		'thumb' => 'text.svg',
	),
);
