<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Theme Settings → Miscellaneous → Custom Scripts (head / body-open / footer).
 *
 * Stored theme-scoped under the `misc_custom_scripts` multi container — the SAME key
 * the theme used, so existing values carry over with no migration. Emitted by the
 * handlers in miscellaneous-handlers.php.
 *
 * @var array $options Filled with the option schema (loaded via upw_ts_get_options()).
 */

$options = array(
	'misc_custom_scripts' => array(
		'type'          => 'multi',
		'label'         => false,
		'inner-options' => array(
			'custom_head_scripts' => array(
				'label'  => __( 'Inside <head>', 'fw' ),
				'desc'   => __( 'Pasted verbatim before &lt;/head&gt;. Wrap JS in &lt;script&gt; tags.', 'fw' ),
				'type'   => 'code-editor',
				'value'  => '',
				'mode'   => 'htmlmixed',
				'height' => 200,
			),
			'custom_body_open_scripts' => array(
				'label'  => __( 'After opening <body>', 'fw' ),
				'desc'   => __( 'Pasted verbatim immediately after &lt;body&gt; opens. Used for tag-manager &lt;noscript&gt; fallbacks.', 'fw' ),
				'type'   => 'code-editor',
				'value'  => '',
				'mode'   => 'htmlmixed',
				'height' => 200,
			),
			'custom_footer_scripts' => array(
				'label'  => __( 'Before </body>', 'fw' ),
				'desc'   => __( 'Pasted verbatim before &lt;/body&gt; closes.', 'fw' ),
				'type'   => 'code-editor',
				'value'  => '',
				'mode'   => 'htmlmixed',
				'height' => 200,
			),
		),
	),
);
