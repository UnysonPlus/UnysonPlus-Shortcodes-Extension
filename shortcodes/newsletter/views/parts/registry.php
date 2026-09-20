<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Newsletter — design registry. Read by options.php (picker), view.php
 * (`fw-nl--design-<key>`), static.php (per-design CSS gating).
 */
return array(
	'inline'  => array( 'label' => __( 'Inline — input + button', 'fw' ), 'thumb' => 'inline.svg' ),
	'stacked' => array( 'label' => __( 'Stacked', 'fw' ),                 'thumb' => 'stacked.svg' ),
	'boxed'   => array( 'label' => __( 'Boxed card', 'fw' ),              'thumb' => 'boxed.svg' ),
	'capsule' => array( 'label' => __( 'Capsule — button inside the field', 'fw' ), 'thumb' => 'capsule.svg' ),
);
