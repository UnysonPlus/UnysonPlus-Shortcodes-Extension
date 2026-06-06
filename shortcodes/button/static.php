<?php if (!defined('FW')) die('Forbidden');

$shortcodes_extension = fw_ext('shortcodes');

// Button styles — normalizes icon sizing / line-height across icon packs
// (Dashicons ships a fixed 20px box that ignores the button size) plus the
// legacy .btn-black helper.
wp_enqueue_style(
	'fw-shortcode-button',
	$shortcodes_extension->get_declared_URI('/shortcodes/button/static/css/styles.css')
);

// Hover animations (.btnfx-* motion-only classes). Loads on the front end and in
// the page-builder preview (same enqueue chain). The Hover Animation field just
// adds one of these classes to the button.
wp_enqueue_style(
	'fw-shortcode-button-hover-fx',
	$shortcodes_extension->get_declared_URI('/shortcodes/button/static/css/hover-fx.css'),
	array('fw-shortcode-button'),
	$shortcodes_extension->manifest->get_version()
);
