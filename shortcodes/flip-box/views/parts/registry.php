<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Flip Box — design registry (single source of truth). Each design is a CSS skin
 * of the two faces (`fw-fb--design-<key>`). Color-driven designs read the `--fb-*`
 * vars (so the Styling-tab colors override them); each design also sets a signature
 * default palette on the wrapper that the color pickers override. Read by
 * options.php (picker), view.php, static.php (auto-gates static/css/design/<key>.css
 * — none ship, base covers all).
 *
 * (The legacy `image` design was retired — a Front/Back Background Image now shows on
 * ANY design via `fw-fb--has-*-image`. Old `design="image"` content falls back to
 * `solid` in view.php and still shows its image.)
 */
return array(
	'solid' => array(
		'label' => __( 'Solid — flat colors', 'fw' ),
		'thumb' => 'solid.svg',
	),
	'elevated' => array(
		'label' => __( 'Elevated — soft shadow', 'fw' ),
		'thumb' => 'elevated.svg',
	),
	'minimal' => array(
		'label' => __( 'Minimal — hairline border', 'fw' ),
		'thumb' => 'minimal.svg',
	),
	'outline' => array(
		'label' => __( 'Outline front', 'fw' ),
		'thumb' => 'outline.svg',
	),
	'gradient' => array(
		'label' => __( 'Gradient faces', 'fw' ),
		'thumb' => 'gradient.svg',
	),
	'glass' => array(
		'label' => __( 'Glass — frosted blur', 'fw' ),
		'thumb' => 'glass.svg',
	),
	'dark' => array(
		'label' => __( 'Dark', 'fw' ),
		'thumb' => 'dark.svg',
	),
	'neumorph' => array(
		'label' => __( 'Neumorphic', 'fw' ),
		'thumb' => 'neumorph.svg',
	),
);
