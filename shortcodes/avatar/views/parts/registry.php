<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Avatar — design registry (single source of truth).
 *
 * Read by three places, so adding a design is one entry here (+ an SVG
 * thumbnail under static/img/design/):
 *   - options.php → builds the `design` image-picker `choices`
 *   - view.php    → emits the `fw-avatar--<key>` modifier class (whitelisted)
 *   - static.php  → (base CSS covers every design; no per-design file needed)
 *
 * Keys are NON-EMPTY strings (they become the saved value). Each design is a
 * pure CSS treatment applied to the avatar(s) in BOTH Single and Group modes.
 */
return array(
	'plain'    => array( 'label' => __( 'Plain', 'fw' ),        'thumb' => 'plain.svg' ),
	'bordered' => array( 'label' => __( 'Bordered', 'fw' ),     'thumb' => 'bordered.svg' ),
	'ring'     => array( 'label' => __( 'Accent Ring', 'fw' ),  'thumb' => 'ring.svg' ),
	'shadow'   => array( 'label' => __( 'Soft Shadow', 'fw' ),  'thumb' => 'shadow.svg' ),
	'soft'     => array( 'label' => __( 'Soft Tint', 'fw' ),    'thumb' => 'soft.svg' ),
);
