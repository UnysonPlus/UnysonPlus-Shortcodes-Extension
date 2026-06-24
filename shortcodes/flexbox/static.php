<?php if (!defined('FW')) die('Forbidden');

// The flexbox relies on the page-builder grid (.col-* widths, flex utilities) the
// same way the section / row / column shortcodes do.
wp_enqueue_style('fw-ext-builder-frontend-grid');

// Flexbox layout helper: in a row, unsized children stack (full-width), sized
// children (col-*) flow side-by-side. See static/css/styles.css.
$shortcodes_extension = fw_ext('shortcodes');
wp_enqueue_style(
	'fw-shortcode-flexbox',
	$shortcodes_extension->get_declared_URI('/shortcodes/flexbox/static/css/styles.css'),
	array('fw-ext-builder-frontend-grid'),
	$shortcodes_extension->manifest->get_version()
);
