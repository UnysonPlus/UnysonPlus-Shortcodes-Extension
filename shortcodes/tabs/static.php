<?php if (!defined('FW')) die('Forbidden');

$shortcodes_extension = fw_ext('shortcodes');


wp_enqueue_style(
	'fw-shortcode-tabs',
	fw_min_uri($shortcodes_extension->get_uri('/shortcodes/tabs/static/css/styles.css')),
	array('fw-ext-builder-frontend-grid') // .fw- grid (vertical tabs) + utilities
);

// Self-contained tab switching — vanilla JS, no Bootstrap / jQuery dependency.
wp_enqueue_script(
	'fw-shortcode-tabs',
	fw_min_uri($shortcodes_extension->get_uri('/shortcodes/tabs/static/js/scripts.js')),
	array(),
	$shortcodes_extension->manifest->get_version(),
	true
);
