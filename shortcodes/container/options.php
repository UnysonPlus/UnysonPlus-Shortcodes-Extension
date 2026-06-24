<?php if (!defined('FW')) die('Forbidden');

$options = [
	'is_fullwidth' => [
		'label' => __( 'Full Width', 'fw' ),
		'desc'  => __( 'Off: Boxed — the container is constrained to the site width (.fw-container). On: Full-width — it spans edge-to-edge (.fw-container-fluid).', 'fw' ),
		'help'  => __( 'A Container renders as a second container injected after the section\'s own container, so a section can hold both a boxed band and a full-width band (e.g. a contained heading above a full-bleed gallery).', 'fw' ),
		'type'  => 'switch',
		'value' => false,
	],
];
