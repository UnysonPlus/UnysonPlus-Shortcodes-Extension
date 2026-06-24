<?php if ( ! defined( 'FW' ) ) { die( 'Forbidden' ); }

$shortcodes_extension = fw_ext( 'shortcodes' );

// Version assets by file mtime so any edit busts the ?ver immediately (matches
// the masonry-section pattern; survives opcached manifest versions on hosts).
$bs_asset_ver = function ( $rel ) use ( $shortcodes_extension ) {
	$fs = $shortcodes_extension->get_path( $rel );
	return ( $fs && file_exists( $fs ) )
		? (string) filemtime( $fs )
		: $shortcodes_extension->manifest->get_version();
};

$bs_css = '/shortcodes/bleed-section/static/css/bleed-section.css';

wp_enqueue_style(
	'fw-shortcode-bleed-section',
	$shortcodes_extension->get_uri( $bs_css ),
	array( 'fw-ext-builder-frontend-grid' ), // .fw- grid + order/utility classes
	$bs_asset_ver( $bs_css )
);
