<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Before / After — design registry (single source of truth).
 *
 * Each entry registers one selectable Design (a visual "skin" of the comparison:
 * handle + label look). Three places read this array:
 *   - options.php  → builds the `design` image-picker `choices`
 *   - view.php     → resolves the design + its flags, adds the `--design-<key>` class
 *   - static.php   → auto-gates per-design CSS (static/css/design/<key>.css)
 *
 * Every design shares ONE structure (two stacked image layers + a draggable
 * handle + labels) and ONE JS engine; the design only changes the CSS skin. The
 * comparison's BEHAVIOUR (orientation, drag/hover/toggle, start position, ratio)
 * is set by cross-design options on the Design tab, so any skin works with any
 * behaviour. The base styles.css covers every skin here (no per-design files),
 * but the static.php hook will auto-load static/css/design/<key>.css if added.
 *
 * Keys (NON-EMPTY strings — they become the saved Design value):
 *   label        : human label shown in the picker
 *   thumb        : SVG filename under static/img/design/
 *   force_labels : (optional, bool) always show the Before/After labels
 */
return array(
	'classic' => array(
		'label' => __( 'Classic — round handle', 'fw' ),
		'thumb' => 'classic.svg',
	),
	'circle' => array(
		'label' => __( 'Circle knob', 'fw' ),
		'thumb' => 'circle.svg',
	),
	'arrows' => array(
		'label' => __( 'Arrows — no knob ring', 'fw' ),
		'thumb' => 'arrows.svg',
	),
	'line' => array(
		'label' => __( 'Minimal line', 'fw' ),
		'thumb' => 'line.svg',
	),
	'labeled' => array(
		'label'        => __( 'Labeled badges', 'fw' ),
		'thumb'        => 'labeled.svg',
		'force_labels' => true,
	),
	'framed' => array(
		'label'        => __( 'Framed card', 'fw' ),
		'thumb'        => 'framed.svg',
		'force_labels' => true,
	),
);
