<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

wp_enqueue_style( 'fw-ext-builder-frontend-grid' );

$shortcodes_extension = fw_ext( 'shortcodes' );

// Stamp every asset with the extension version so a manifest bump busts browser caches.
// Without this, wp_enqueue_* defaults `ver` to the WordPress version (e.g. ?ver=7.0), which
// never changes on our updates — so an edited background.init.js / styles.css keeps serving
// the stale cached copy (this once left a reverted video background stuck on its poster).
$sc_ver = $shortcodes_extension->manifest->get_version();

/**
 * Background video/poster runtime — background.init.js is now a self-contained
 * VANILLA replacement for the old Formstone stack (core.js / transition.js /
 * background.js and the deprecated jquery.fs.wallpaper.js), so no jQuery
 * dependency is enqueued and jQuery stays off the front end entirely. It
 * reuses the same fs-background-* class names, so background.css still
 * applies; the YouTube iframe API is loaded on demand by the runtime itself
 * (which also covers the old Safari-ordering fix, Unyson#1552).
 */
wp_enqueue_style(
	'fw-shortcode-section-background-video',
	fw_min_uri($shortcodes_extension->get_uri( '/shortcodes/section/static/css/background.css' )),
	array(),
	$sc_ver
);

wp_enqueue_script(
	'fw-shortcode-section',
	fw_min_uri($shortcodes_extension->get_uri( '/shortcodes/section/static/js/background.init.js' )),
	array(),
	$sc_ver,
	true
);

wp_enqueue_style(
	'fw-shortcode-section',
	fw_min_uri($shortcodes_extension->get_uri( '/shortcodes/section/static/css/styles.css' )),
	array(),
	$sc_ver
);

