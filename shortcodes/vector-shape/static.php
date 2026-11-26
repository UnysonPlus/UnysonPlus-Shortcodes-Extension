<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

wp_enqueue_style(
	'fw-shortcode-vector-shape',
	fw_min_uri( fw_ext( 'shortcodes' )->get_declared_URI( '/shortcodes/vector-shape/static/css/styles.css' ) )
);
