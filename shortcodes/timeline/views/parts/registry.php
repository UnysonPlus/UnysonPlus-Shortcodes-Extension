<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Timeline — design (layout) registry. Read by options.php (picker),
 * view.php (`fw-tl--design-<key>`), static.php (per-design CSS gating).
 */
return array(
	'alternating' => array(
		'label' => __( 'Vertical — alternating sides', 'fw' ),
		'thumb' => 'alternating.svg',
	),
	'left' => array(
		'label' => __( 'Vertical — line on the left', 'fw' ),
		'thumb' => 'left.svg',
	),
	'right' => array(
		'label' => __( 'Vertical — line on the right', 'fw' ),
		'thumb' => 'right.svg',
	),
	'horizontal' => array(
		'label' => __( 'Horizontal — scrolling', 'fw' ),
		'thumb' => 'horizontal.svg',
	),
);
