<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Author Box — design registry. Read by options.php (picker), view.php
 * (`fw-ab--design-<key>`), static.php (per-design CSS gating).
 */
return array(
	'card'     => array( 'label' => __( 'Card — avatar left', 'fw' ), 'thumb' => 'card.svg' ),
	'centered' => array( 'label' => __( 'Centered', 'fw' ),           'thumb' => 'centered.svg' ),
	'banner'   => array( 'label' => __( 'Banner — wide', 'fw' ),      'thumb' => 'banner.svg' ),
	'minimal'  => array( 'label' => __( 'Minimal — no card', 'fw' ),  'thumb' => 'minimal.svg' ),
);
