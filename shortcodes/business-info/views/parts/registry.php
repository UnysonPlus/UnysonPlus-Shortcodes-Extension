<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Business Info — design registry. Read by options.php (picker), view.php
 * (`fw-bi--design-<key>`), static.php (per-design CSS gating).
 */
return array(
	'table'   => array( 'label' => __( 'Hours table', 'fw' ),         'thumb' => 'table.svg' ),
	'card'    => array( 'label' => __( 'Card — info + hours', 'fw' ), 'thumb' => 'card.svg' ),
	'split'   => array( 'label' => __( 'Split — contact + hours', 'fw' ), 'thumb' => 'split.svg' ),
	'compact' => array( 'label' => __( 'Compact', 'fw' ),             'thumb' => 'compact.svg' ),
);
