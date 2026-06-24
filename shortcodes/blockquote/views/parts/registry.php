<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Blockquote — design registry. Read by options.php (picker), view.php
 * (`fw-bq--design-<key>`), static.php (per-design CSS gating).
 */
return array(
	'classic'   => array( 'label' => __( 'Classic — accent border', 'fw' ), 'thumb' => 'classic.svg' ),
	'pullquote' => array( 'label' => __( 'Pullquote — large centered', 'fw' ), 'thumb' => 'pullquote.svg' ),
	'card'      => array( 'label' => __( 'Card — boxed', 'fw' ),             'thumb' => 'card.svg' ),
	'markquote' => array( 'label' => __( 'Big quote mark', 'fw' ),          'thumb' => 'markquote.svg' ),
	'minimal'   => array( 'label' => __( 'Minimal — italic', 'fw' ),        'thumb' => 'minimal.svg' ),
	'bordered'  => array( 'label' => __( 'Bordered — top & bottom rules', 'fw' ), 'thumb' => 'bordered.svg' ),
);
