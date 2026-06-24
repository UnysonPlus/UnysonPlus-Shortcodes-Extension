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

// Serve Leaflet's default marker icons from the plugin instead of a third-party CDN, so OSM markers
// never render as broken images when the CDN is blocked/unreachable (or a page CSP disallows it).
// scripts.js reads this base; it falls back to the unpkg CDN if the variable is somehow absent.
wp_localize_script(
	'fw-shortcode-map-script',
	'fwMapIconBase',
	$shortcodes_extension->get_uri('/shortcodes/map/static/img/')
);
