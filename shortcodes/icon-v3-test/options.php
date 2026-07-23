<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Test element for the icon-v3 option type. Deliberately minimal — just the
 * icon-v3 picker + an optional label — so the new picker can be exercised in
 * the builder without any other options getting in the way.
 */
$options = array(
	'tab_content' => array(
		'title'   => __( 'Content', 'fw' ),
		'type'    => 'tab',
		'options' => array(
			'group_content' => array(
				'type'    => 'group',
				'options' => array(
					'icon'  => array(
						'type'         => 'icon-v3',
						'label'        => __( 'Icon (v3)', 'fw' ),
						'desc'         => __( 'Pick a glyph using the in-development icon-v3 picker.', 'fw' ),
						'help'         => __( 'This element uses the experimental icon-v3 option type so it can be tested independently of the stable icon-v2 elements.', 'fw' ),
						'preview_size' => 'medium',
						'modal_size'   => 'medium',
					),
					'title' => array(
						'type'  => 'text',
						'label' => __( 'Title', 'fw' ),
						'desc'  => __( 'Optional label shown next to the icon.', 'fw' ),
					),
				),
			),
		),
	),

	'tab_advanced' => array(
		'title'   => __( 'Advanced', 'fw' ),
		'type'    => 'tab',
		'options' => array(
			'advanced_settings' => array(
				'type'    => 'group',
				'options' => function_exists( 'sc_get_advanced_tab' ) ? sc_get_advanced_tab() : array(),
			),
		),
	),
);
