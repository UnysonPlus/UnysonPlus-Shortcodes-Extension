<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Steps / Process — design registry. Read by options.php (picker), view.php
 * (`fw-steps--design-<key>`), static.php.
 */
return array(
	'horizontal'  => array( 'label' => __( 'Horizontal', 'fw' ),        'thumb' => 'horizontal.svg' ),
	'vertical'    => array( 'label' => __( 'Vertical timeline', 'fw' ), 'thumb' => 'vertical.svg' ),
	'alternating' => array( 'label' => __( 'Alternating', 'fw' ),       'thumb' => 'alternating.svg' ),
	'cards'       => array( 'label' => __( 'Cards', 'fw' ),             'thumb' => 'cards.svg' ),
	'circles'     => array( 'label' => __( 'Circles', 'fw' ),           'thumb' => 'circles.svg' ),
);
