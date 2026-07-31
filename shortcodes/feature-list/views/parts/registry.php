<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Feature List — Default Marker registry. Read by options.php (picker), view.php
 * (`fw-fl--design-<key>`), static.php (per-design CSS gating).
 *
 * These are the *fallback* markers used for an item that has NO per-item icon —
 * a per-item icon (set on the Content tab) always OVERRIDES the marker. Legacy
 * `icon` / `badge` designs are handled as back-compat aliases in view.php (they
 * fold into `check` + Icon Style, since icons now show under any marker).
 */
return array(
	'check'      => array( 'label' => __( 'Checkmark', 'fw' ),          'thumb' => 'check.svg' ),
	'check_icon' => array( 'label' => __( 'Checkmark + icon', 'fw' ),   'thumb' => 'check-icon.svg' ),
	'numbered'   => array( 'label' => __( 'Numbered', 'fw' ),           'thumb' => 'numbered.svg' ),
	'bullet'     => array( 'label' => __( 'Bullets', 'fw' ),            'thumb' => 'bullet.svg' ),
	'none'       => array( 'label' => __( 'Icons only', 'fw' ),         'thumb' => 'icon.svg' ),
);
