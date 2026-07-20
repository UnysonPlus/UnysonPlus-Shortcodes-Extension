<?php if (!defined('FW')) die('Forbidden');

// The container relies on the page-builder grid (.fw-container / .fw-row) the same
// way the section and row shortcodes do.
wp_enqueue_style('fw-ext-builder-frontend-grid');

$shortcodes_extension = fw_ext( 'shortcodes' );

// Container-specific styling (the "Default / Stretched" vertical alignment row-growth
// rule). Versioned by file mtime so any edit busts the ?ver immediately, surviving
// opcached manifest versions on hosts (matches the bleed-section pattern).
$ct_css = '/shortcodes/container/static/css/styles.css';
$ct_fs  = $shortcodes_extension->get_path( $ct_css );
$ct_ver = ( $ct_fs && file_exists( $ct_fs ) ) ? (string) filemtime( $ct_fs ) : $shortcodes_extension->manifest->get_version();

wp_enqueue_style(
	'fw-shortcode-container',
	$shortcodes_extension->get_uri( $ct_css ),
	array( 'fw-ext-builder-frontend-grid' ),
	$ct_ver
);

// Background-pro colour / gradient / image layers render inline (no extra assets). Video
// backgrounds reuse the parent Section's formstone scripts + the global
// `$('.background-video').background()` init, which are always present because a Container
// only ever renders inside a Section — so no video enqueue is duplicated here.
