<?php if (!defined('FW')) die('Forbidden');

/**
 * Engine-agnostic assets for the [map] shortcode.
 *
 * The mapping library itself (Google Maps API or Leaflet/OpenStreetMap) is
 * enqueued per-render in FW_Shortcode_Map::enqueue_map_engine(), based on the
 * element's selected "Map Engine". scripts.js reads each wrapper's
 * data-map-engine and waits for the matching global (google.maps / L).
 */

$shortcodes_extension = fw_ext('shortcodes');

wp_enqueue_style(
	'fw-shortcode-map',
	fw_min_uri($shortcodes_extension->get_uri('/shortcodes/map/static/css/styles.css'))
);

wp_enqueue_script(
	'fw-shortcode-map-script',
	fw_min_uri($shortcodes_extension->get_uri('/shortcodes/map/static/js/scripts.js')),
	array('jquery', 'underscore'),
	fw()->manifest->get_version(),
	true
);
