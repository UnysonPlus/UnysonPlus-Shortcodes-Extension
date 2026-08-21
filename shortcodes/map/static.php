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
	array(),
	fw()->manifest->get_version(),
	true
);

// Serve Leaflet's default marker icons from the plugin instead of a third-party CDN, so OSM markers
// never render as broken images when the CDN is blocked/unreachable (or a page CSP disallows it).
// scripts.js reads this base; it falls back to the unpkg CDN if the variable is somehow absent.
//
// wp_add_inline_script(), NOT wp_localize_script(): localize's $l10n parameter must be an ARRAY.
// Passing it a bare string still emitted a usable `var`, but WordPress 5.7+ answers it with a
// _doing_it_wrong() notice — which on a WP_DEBUG site prints into the page, and printed into the
// Gutenberg block preview's markup. Same global, same contract, no notice.
wp_add_inline_script(
	'fw-shortcode-map-script',
	'var fwMapIconBase = ' . wp_json_encode(
		$shortcodes_extension->get_uri('/shortcodes/map/static/img/')
	) . ';',
	'before'
);
