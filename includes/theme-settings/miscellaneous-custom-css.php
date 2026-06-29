<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Theme Settings → Miscellaneous → Custom CSS.
 *
 * Stored theme-scoped under the `misc_custom_css` multi container — the SAME key the
 * theme used, so existing values carry over with no migration. Emitted by the handler
 * in miscellaneous-handlers.php (folded into the plugin's combined presets stylesheet).
 *
 * @var array $options Filled with the option schema (loaded via upw_ts_get_options()).
 */

$options = array(
	'misc_custom_css' => array(
		'type'          => 'multi',
		'label'         => false,
		'inner-options' => array(
			'custom_css' => array(
				'label'  => false,
				'desc'   => __( 'Added to the plugin\'s combined presets stylesheet, which loads after all theme + plugin styles so it wins the cascade.', 'fw' ),
				'type'   => 'code-editor',
				'value'  => '',
				'mode'   => 'css',
				'height' => 400,
			),
		),
	),
);
