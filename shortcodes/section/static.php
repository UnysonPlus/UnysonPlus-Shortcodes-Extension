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

if ( version_compare( $shortcodes_extension->manifest->get_version(), '1.3.9', '>=' ) ) {
	/**
	 * Updated to new version of formstone.js background
	 * which have new structure and new dependencies
	 * such as core.js , transition.js and background.js
	 * since v1.3.9
	 * jquery.fs.wallpaper.js, jquery.fs.wallpaper.min.js and scripts.js are @deprecated
	 * they remains for backward compatibility.
	 */

	// fixes https://github.com/ThemeFuse/Unyson/issues/1552
	{
		global $is_safari;

		if ($is_safari) {
			wp_enqueue_script('youtube-iframe-api', 'https://www.youtube.com/iframe_api');
		}
	}

	wp_enqueue_style(
		'fw-shortcode-section-background-video',
		fw_min_uri($shortcodes_extension->get_uri( '/shortcodes/section/static/css/background.css' )),
		array(),
		$sc_ver
	);

	wp_enqueue_script(
		'fw-shortcode-section-formstone-core',
		fw_min_uri($shortcodes_extension->get_uri( '/shortcodes/section/static/js/core.js' )),
		array( 'jquery' ),
		$sc_ver,
		true
	);
	wp_enqueue_script(
		'fw-shortcode-section-formstone-transition',
		fw_min_uri($shortcodes_extension->get_uri( '/shortcodes/section/static/js/transition.js' )),
		array( 'jquery' ),
		$sc_ver,
		true
	);
	wp_enqueue_script(
		'fw-shortcode-section-formstone-background',
		fw_min_uri($shortcodes_extension->get_uri( '/shortcodes/section/static/js/background.js' )),
		array( 'jquery' ),
		$sc_ver,
		true
	);
	wp_enqueue_script(
		'fw-shortcode-section',
		fw_min_uri($shortcodes_extension->get_uri( '/shortcodes/section/static/js/background.init.js' )),
		array(
			'fw-shortcode-section-formstone-core',
			'fw-shortcode-section-formstone-transition',
			'fw-shortcode-section-formstone-background'
		),
		$sc_ver,
		true
	);
} else {
	wp_enqueue_style(
		'fw-shortcode-section-background-video',
		fw_min_uri($shortcodes_extension->get_uri( '/shortcodes/section/static/css/jquery.fs.wallpaper.css' )),
		array(),
		$sc_ver
	);

	wp_enqueue_script(
		'fw-shortcode-section-background-video',
		fw_min_uri($shortcodes_extension->get_uri( '/shortcodes/section/static/js/jquery.fs.wallpaper.min.js' )),
		array( 'jquery' ),
		$sc_ver,
		true
	);
	wp_enqueue_script(
		'fw-shortcode-section',
		fw_min_uri($shortcodes_extension->get_uri( '/shortcodes/section/static/js/scripts.js' )),
		array( 'fw-shortcode-section-background-video' ),
		$sc_ver,
		true
	);
}

wp_enqueue_style(
	'fw-shortcode-section',
	fw_min_uri($shortcodes_extension->get_uri( '/shortcodes/section/static/css/styles.css' )),
	array(),
	$sc_ver
);

