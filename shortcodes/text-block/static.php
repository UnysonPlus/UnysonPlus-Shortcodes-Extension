<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

// Front-end CSS for the text-block layout/typography options (max-width, columns, balanced wrap,
// drop cap). Text alignment uses the theme's Bootstrap text-* utilities, so it needs nothing here.
$ext = fw_ext( 'shortcodes' );

wp_enqueue_style(
	'fw-shortcode-text-block',
	$ext->get_declared_URI( '/shortcodes/text-block/static/css/styles.css' ),
	array(),
	$ext->manifest->get_version()
);

// No JavaScript: the drop-cap line sizing is computed in pure CSS (styles.css) from the --dc-lines
// custom property and the --fw-line theme variable.
