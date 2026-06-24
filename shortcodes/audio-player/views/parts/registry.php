<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Audio Player — design registry. Read by options.php (picker), view.php
 * (`fw-ap--design-<key>`), static.php (per-design CSS gating).
 */
return array(
	'classic'  => array( 'label' => __( 'Classic bar', 'fw' ),       'thumb' => 'classic.svg' ),
	'card'     => array( 'label' => __( 'Card with cover', 'fw' ),   'thumb' => 'card.svg' ),
	'minimal'  => array( 'label' => __( 'Minimal', 'fw' ),           'thumb' => 'minimal.svg' ),
	'playlist' => array( 'label' => __( 'Playlist', 'fw' ),          'thumb' => 'playlist.svg' ),
);
