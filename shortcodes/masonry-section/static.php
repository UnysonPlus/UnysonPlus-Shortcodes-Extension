<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$shortcodes_extension = fw_ext( 'shortcodes' );

// Version assets by file modification time, not the (opcached) manifest version —
// so any edit busts the `?ver` immediately even when a host like WP Engine is
// still serving the old manifest version from opcache. Falls back to the manifest
// version if the file can't be stat'd.
$mc_asset_ver = function ( $rel ) use ( $shortcodes_extension ) {
	$fs = $shortcodes_extension->get_path( $rel );
	return ( $fs && file_exists( $fs ) )
		? (string) filemtime( $fs )
		: $shortcodes_extension->manifest->get_version();
};

$mc_css = '/shortcodes/masonry-section/static/css/masonry-section.css';
$mc_js  = '/shortcodes/masonry-section/static/js/masonry-section.js';

wp_enqueue_style(
	'fw-shortcode-masonry-section',
	$shortcodes_extension->get_uri( $mc_css ),
	array(),
	$mc_asset_ver( $mc_css )
);

wp_enqueue_script(
	'fw-shortcode-masonry-section',
	$shortcodes_extension->get_uri( $mc_js ),
	array(),
	$mc_asset_ver( $mc_js ),
	true
);
