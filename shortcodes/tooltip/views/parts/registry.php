<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Tooltip — theme registry. Read by options.php (picker), view.php
 * (`fw-tt--design-<key>`), static.php (per-design CSS gating).
 */
return array(
	'dark'     => array( 'label' => __( 'Dark', 'fw' ),     'thumb' => 'dark.svg' ),
	'light'    => array( 'label' => __( 'Light', 'fw' ),    'thumb' => 'light.svg' ),
	'accent'   => array( 'label' => __( 'Accent', 'fw' ),   'thumb' => 'accent.svg' ),
	'gradient' => array( 'label' => __( 'Gradient', 'fw' ), 'thumb' => 'gradient.svg' ),
);
