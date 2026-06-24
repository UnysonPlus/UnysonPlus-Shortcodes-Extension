<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Animated Heading — animation registry. Read by options.php (picker), view.php
 * (`fw-ah--anim-<key>`), static.php (per-design CSS gating).
 */
return array(
	'typewriter' => array( 'label' => __( 'Typewriter', 'fw' ), 'thumb' => 'typewriter.svg' ),
	'fade'       => array( 'label' => __( 'Fade', 'fw' ),       'thumb' => 'fade.svg' ),
	'slide'      => array( 'label' => __( 'Slide up', 'fw' ),   'thumb' => 'slide.svg' ),
	'flip'       => array( 'label' => __( 'Flip', 'fw' ),       'thumb' => 'flip.svg' ),
	'zoom'       => array( 'label' => __( 'Zoom', 'fw' ),       'thumb' => 'zoom.svg' ),
	'clip'       => array( 'label' => __( 'Clip reveal', 'fw' ),'thumb' => 'clip.svg' ),
);
