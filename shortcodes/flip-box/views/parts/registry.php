<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Flip Box — design registry (single source of truth). Each design is a CSS skin
 * of the two faces. Read by options.php (picker), view.php (`fw-fb--design-<key>`),
 * static.php (auto-gates static/css/design/<key>.css — none ship, base covers all).
 */
return array(
	'solid' => array(
		'label' => __( 'Solid — flat colors', 'fw' ),
		'thumb' => 'solid.svg',
	),
	'gradient' => array(
		'label' => __( 'Gradient faces', 'fw' ),
		'thumb' => 'gradient.svg',
	),
	'outline' => array(
		'label' => __( 'Outline front', 'fw' ),
		'thumb' => 'outline.svg',
	),
	'image' => array(
		'label'      => __( 'Image front + overlay', 'fw' ),
		'thumb'      => 'image.svg',
		'front_image' => true,
	),
);
