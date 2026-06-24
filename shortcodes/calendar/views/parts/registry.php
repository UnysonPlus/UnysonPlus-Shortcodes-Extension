<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Calendar — design registry. Read by options.php (picker), view.php
 * (`fw-cal--design-<key>`), static.php.
 */
return array(
	'classic'  => array( 'label' => __( 'Classic', 'fw' ),  'thumb' => 'classic.svg' ),
	'minimal'  => array( 'label' => __( 'Minimal', 'fw' ),  'thumb' => 'minimal.svg' ),
	'cards'    => array( 'label' => __( 'Cards', 'fw' ),    'thumb' => 'cards.svg' ),
	'bordered' => array( 'label' => __( 'Bordered', 'fw' ), 'thumb' => 'bordered.svg' ),
	'dark'     => array( 'label' => __( 'Dark', 'fw' ),     'thumb' => 'dark.svg' ),
);
