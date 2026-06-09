<?php if (!defined('FW')) die('Forbidden');

$shortcodes_extension = fw_ext('shortcodes');


wp_enqueue_style(
	'fw-shortcode-tabs',
	fw_min_uri($shortcodes_extension->get_uri('/shortcodes/tabs/static/css/styles.css'))
);
/*
wp_enqueue_script(
	'fw-shortcode-tabs',
	fw_min_uri($shortcodes_extension->get_uri('/shortcodes/tabs/static/js/scripts.js')),
	array('jquery-ui-tabs'),
	false,
	true
); */
