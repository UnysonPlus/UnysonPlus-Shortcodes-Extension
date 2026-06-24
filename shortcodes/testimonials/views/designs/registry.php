<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Testimonials — design registry (single source of truth).
 *
 * Each entry registers one selectable design/layout. Three places read this
 * one array, so adding a design is a single entry here (+ a template, a CSS
 * file, and an SVG thumbnail):
 *
 *   - options.php  → builds the `design` image-picker `choices`
 *   - view.php     → whitelists which `designs/<key>.php` template to include
 *   - static.php   → conditionally enqueues `css/designs/<css>` + `js/designs/<js>`
 *
 * Keys (NON-EMPTY strings — they become the saved value):
 *   label  : human label shown in the picker tooltip
 *   thumb  : SVG filename under static/img/designs/ (picker thumbnail)
 *   css    : per-design stylesheet filename under static/css/designs/ (null = base only)
 *   js     : per-design script filename under static/js/designs/ (null = none)
 *
 * `default` is the original view (Slider / Grid / Single via the Layout tab) and
 * carries NO extra css/js — the always-enqueued base styles.css covers it, so old
 * saved instances (which have no `design` att) render byte-identical to before.
 */
return array(
	'default' => array(
		'label' => __( 'Classic (Slider / Grid / Single)', 'fw' ),
		'thumb' => 'default.svg',
		'css'   => null,
		'js'    => null,
	),
	'marquee' => array(
		'label' => __( 'Marquee Wall', 'fw' ),
		'thumb' => 'marquee.svg',
		'css'   => 'marquee.css',
		'js'    => null,
	),
	'masonry' => array(
		'label' => __( 'Masonry Wall', 'fw' ),
		'thumb' => 'masonry.svg',
		'css'   => 'masonry.css',
		'js'    => null,
	),
	'split' => array(
		'label' => __( 'Image Split Slider', 'fw' ),
		'thumb' => 'split.svg',
		'css'   => 'split.css',
		'js'    => null,
	),
	'bubble' => array(
		'label' => __( 'Speech Bubble', 'fw' ),
		'thumb' => 'bubble.svg',
		'css'   => 'bubble.css',
		'js'    => null,
	),
	'stacked' => array(
		'label' => __( 'Stacked List', 'fw' ),
		'thumb' => 'stacked.svg',
		'css'   => 'stacked.css',
		'js'    => null,
	),
	'thumbnav' => array(
		'label' => __( 'Thumbnail Nav Slider', 'fw' ),
		'thumb' => 'thumbnav.svg',
		'css'   => 'thumbnav.css',
		'js'    => 'thumbnav.js',
	),
	'spotlight' => array(
		'label' => __( 'Spotlight Coverflow', 'fw' ),
		'thumb' => 'spotlight.svg',
		'css'   => 'spotlight.css',
		'js'    => null, // reuses the base .testimonials-splide mount
	),
	'bento' => array(
		'label' => __( 'Bento Featured Grid', 'fw' ),
		'thumb' => 'bento.svg',
		'css'   => 'bento.css',
		'js'    => null,
	),
	'zigzag' => array(
		'label' => __( 'Zigzag Alternating', 'fw' ),
		'thumb' => 'zigzag.svg',
		'css'   => 'zigzag.css',
		'js'    => null,
	),
	'pullquote' => array(
		'label' => __( 'Pull-Quote Editorial', 'fw' ),
		'thumb' => 'pullquote.svg',
		'css'   => 'pullquote.css',
		'js'    => null, // reuses the base .testimonials-splide mount (fade)
	),
);
