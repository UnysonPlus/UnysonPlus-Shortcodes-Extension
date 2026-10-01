<?php if (!defined('FW')) die('Forbidden');

$shortcodes_extension = fw_ext('shortcodes');
wp_enqueue_style(
	'fw-shortcode-notification',
	fw_min_uri($shortcodes_extension->get_declared_URI('/shortcodes/notification/static/css/styles.css')),
	// NOTE: Font Awesome is NOT a dependency here. It is enqueued by sc_icon_render() only when the icon
	// really is a font icon — a converted site draws its glyphs as inline SVG, and naming FA here shipped
	// 24 KB of CSS plus five `font-display:block` faces to every page using this shortcode, unused.
	array()
);
wp_enqueue_script(
	'fw-shortcode-notification',
	fw_min_uri($shortcodes_extension->get_declared_URI('/shortcodes/notification/static/js/notification.js')),
	array(),
	false,
	true
);
