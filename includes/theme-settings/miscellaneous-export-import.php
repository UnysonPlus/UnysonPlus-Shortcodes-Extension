<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Theme Settings → Miscellaneous → Export / Import.
 *
 * The control itself (export button + import file form) is rendered by the
 * self-contained subsystem in settings-export-import.php (required by loader.php),
 * which works on any theme — it operates on the framework's theme-settings store.
 *
 * @var array $options Filled with the option schema (loaded via upw_ts_get_options()).
 */

$options = array(
	'misc_export_import' => array(
		'type'  => 'html-full',
		'label' => false,
		'html'  => function_exists( 'unysonplus_settings_io_misc_field_html' )
			? unysonplus_settings_io_misc_field_html()
			: '',
	),
);
