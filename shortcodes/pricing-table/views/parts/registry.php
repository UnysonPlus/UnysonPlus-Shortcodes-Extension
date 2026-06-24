<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Pricing Table — design registry (single source of truth).
 *
 *   - options.php → builds the `design` image-picker `choices`
 *   - view.php    → adds the `fw-pt--design-<key>` class
 *   - static.php  → auto-gates static/css/design/<key>.css (none ship; base covers all)
 *
 * Every design shares one structure (a row of plan cards); the design is a CSS
 * skin. Add a design = one entry here + a thumbnail (+ optional per-design CSS).
 */
return array(
	'classic' => array(
		'label' => __( 'Classic — bordered cards', 'fw' ),
		'thumb' => 'classic.svg',
	),
	'modern' => array(
		'label' => __( 'Modern — soft shadow', 'fw' ),
		'thumb' => 'modern.svg',
	),
	'minimal' => array(
		'label' => __( 'Minimal — borderless', 'fw' ),
		'thumb' => 'minimal.svg',
	),
	'gradient' => array(
		'label' => __( 'Gradient header', 'fw' ),
		'thumb' => 'gradient.svg',
	),
	'dark' => array(
		'label' => __( 'Dark cards', 'fw' ),
		'thumb' => 'dark.svg',
	),
	'outline' => array(
		'label' => __( 'Outline — accent border', 'fw' ),
		'thumb' => 'outline.svg',
	),
);
