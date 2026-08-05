<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Tabs — DESIGN REGISTRY (SKIN shape). Each key is a visual tab style applied as
 * CSS over the one shared markup; there is no per-design render partial. Read by:
 *   - options.php  → the Design image-picker (via fw_sc_design_picker_choices).
 *   - views/view.php → resolve + emit `tabs--design-<key>` + `design-<key>`.
 *   - static.php    → auto-gate an optional static/css/design/<key>.css.
 * Thumbs live in static/img/design/<key>.svg. Adding a design = one entry here
 * + a thumb (+ optional CSS).
 */

return array(
	'underline' => array( 'label' => __( 'Underline', 'fw' ),        'thumb' => 'underline.svg' ),
	'pills'     => array( 'label' => __( 'Pills', 'fw' ),             'thumb' => 'pills.svg' ),
	'segmented' => array( 'label' => __( 'Segmented Toggle', 'fw' ),  'thumb' => 'segmented.svg' ),
	'boxed'     => array( 'label' => __( 'Boxed / Folder', 'fw' ),    'thumb' => 'boxed.svg' ),
	'minimal'   => array( 'label' => __( 'Minimal', 'fw' ),           'thumb' => 'minimal.svg' ),
	'buttons'   => array( 'label' => __( 'Buttons', 'fw' ),           'thumb' => 'buttons.svg' ),
	'popover'   => array( 'label' => __( 'Popover (floating)', 'fw' ), 'thumb' => 'popover.svg' ),
);
