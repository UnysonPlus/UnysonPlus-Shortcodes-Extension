<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Modal / Popup — modal-style registry. Read by options.php (picker), view.php
 * (`fw-mp--design-<key>`), static.php (per-design CSS gating).
 */
return array(
	'center'       => array( 'label' => __( 'Centered card', 'fw' ),  'thumb' => 'center.svg' ),
	'drawer-right' => array( 'label' => __( 'Drawer — right', 'fw' ),  'thumb' => 'drawer-right.svg' ),
	'drawer-left'  => array( 'label' => __( 'Drawer — left', 'fw' ),   'thumb' => 'drawer-left.svg' ),
	'fullscreen'   => array( 'label' => __( 'Fullscreen', 'fw' ),      'thumb' => 'fullscreen.svg' ),
);
