<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Video Popup — play-button design registry. Read by options.php (picker),
 * view.php (`fw-vp--design-<key>`), static.php (per-design CSS gating).
 */
return array(
	'classic' => array( 'label' => __( 'Classic — filled circle', 'fw' ), 'thumb' => 'classic.svg' ),
	'pulse'   => array( 'label' => __( 'Pulse rings', 'fw' ),            'thumb' => 'pulse.svg' ),
	'outline' => array( 'label' => __( 'Outline ring', 'fw' ),           'thumb' => 'outline.svg' ),
	'soft'    => array( 'label' => __( 'Soft glass', 'fw' ),             'thumb' => 'soft.svg' ),
	'minimal' => array( 'label' => __( 'Minimal triangle', 'fw' ),       'thumb' => 'minimal.svg' ),
);
