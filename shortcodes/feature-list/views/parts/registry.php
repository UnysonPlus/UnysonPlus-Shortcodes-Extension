<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Feature List — marker design registry. Read by options.php (picker), view.php
 * (`fw-fl--design-<key>`), static.php (per-design CSS gating).
 */
return array(
	'check'   => array( 'label' => __( 'Checklist', 'fw' ),        'thumb' => 'check.svg' ),
	'icon'    => array( 'label' => __( 'Per-item icons', 'fw' ),   'thumb' => 'icon.svg' ),
	'numbered'=> array( 'label' => __( 'Numbered', 'fw' ),         'thumb' => 'numbered.svg' ),
	'bullet'  => array( 'label' => __( 'Bullets', 'fw' ),          'thumb' => 'bullet.svg' ),
	'badge'   => array( 'label' => __( 'Badge icons (boxed)', 'fw' ), 'thumb' => 'badge.svg' ),
);
