<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Gallery — design registry (single source of truth).
 *
 * Each entry registers one selectable gallery design/layout. Three places read
 * this one array, so adding a design is a single entry here (+ a template, a CSS
 * file, and an SVG thumbnail):
 *
 *   - options.php  → builds the `design` image-picker `choices`
 *   - view.php     → whitelists which `designs/<key>.php` template to include
 *   - static.php   → conditionally enqueues `css/designs/<css>` (+ `js/designs/<js>`,
 *                    + Splide when `splide` is true)
 *
 * Keys (NON-EMPTY strings — they become the saved value):
 *   label  : human label shown in the picker tooltip
 *   thumb  : SVG filename under static/img/designs/ (picker thumbnail)
 *   css    : per-design stylesheet filename under static/css/designs/ (null = base only)
 *   js     : per-design script filename under static/js/designs/ (null = none)
 *   splide : true → the design needs the vendored Splide slider (carousel)
 *
 * `grid` is the default design and is covered by the always-enqueued base
 * styles.css, so an instance that has never picked a design renders as a grid.
 */
return array(
	'grid' => array(
		'label'  => __( 'Grid', 'fw' ),
		'thumb'  => 'grid.svg',
		'css'    => 'grid.css',
		'js'     => null,
		'splide' => false,
	),
	'masonry' => array(
		'label'  => __( 'Masonry', 'fw' ),
		'thumb'  => 'masonry.svg',
		'css'    => 'masonry.css',
		'js'     => null,
		'splide' => false,
	),
	'justified' => array(
		'label'  => __( 'Justified Rows', 'fw' ),
		'thumb'  => 'justified.svg',
		'css'    => 'justified.css',
		'js'     => null,
		'splide' => false,
	),
	'metro' => array(
		'label'  => __( 'Metro / Bento', 'fw' ),
		'thumb'  => 'metro.svg',
		'css'    => 'metro.css',
		'js'     => null,
		'splide' => false,
	),
	'carousel' => array(
		'label'  => __( 'Carousel Slider', 'fw' ),
		'thumb'  => 'carousel.svg',
		'css'    => 'carousel.css',
		'js'     => 'carousel.js',
		'splide' => true,
	),
	'polaroid' => array(
		'label'  => __( 'Polaroid Scatter', 'fw' ),
		'thumb'  => 'polaroid.svg',
		'css'    => 'polaroid.css',
		'js'     => null,
		'splide' => false,
	),
	'showcase' => array(
		'label'  => __( 'Showcase (Thumbnails)', 'fw' ),
		'thumb'  => 'showcase.svg',
		'css'    => 'showcase.css',
		'js'     => 'showcase.js',
		'splide' => false,
	),
	'cards' => array(
		'label'  => __( 'Cards', 'fw' ),
		'thumb'  => 'cards.svg',
		'css'    => 'cards.css',
		'js'     => null,
		'splide' => false,
	),
	'slideshow' => array(
		'label'  => __( 'Slideshow / Fade', 'fw' ),
		'thumb'  => 'slideshow.svg',
		'css'    => 'slideshow.css',
		'js'     => 'carousel.js', // shared Splide mount
		'splide' => true,
	),
	'thumbslider' => array(
		'label'  => __( 'Thumbnail Slider', 'fw' ),
		'thumb'  => 'thumbslider.svg',
		'css'    => 'thumbslider.css',
		'js'     => 'thumbnail-slider.js',
		'splide' => true,
	),
	'coverflow' => array(
		'label'  => __( 'Coverflow', 'fw' ),
		'thumb'  => 'coverflow.svg',
		'css'    => 'coverflow.css',
		'js'     => 'carousel.js', // shared Splide mount
		'splide' => true,
	),
	'marquee' => array(
		'label'  => __( 'Marquee / Ticker', 'fw' ),
		'thumb'  => 'marquee.svg',
		'css'    => 'marquee.css',
		'js'     => null,
		'splide' => false,
	),
	'filmstrip' => array(
		'label'  => __( 'Filmstrip (Scroll)', 'fw' ),
		'thumb'  => 'filmstrip.svg',
		'css'    => 'filmstrip.css',
		'js'     => null,
		'splide' => false,
	),
	'spotlight' => array(
		'label'  => __( 'Spotlight', 'fw' ),
		'thumb'  => 'spotlight.svg',
		'css'    => 'spotlight.css',
		'js'     => null,
		'splide' => false,
	),
	'honeycomb' => array(
		'label'  => __( 'Honeycomb', 'fw' ),
		'thumb'  => 'honeycomb.svg',
		'css'    => 'honeycomb.css',
		'js'     => null,
		'splide' => false,
	),
	'accordion' => array(
		'label'  => __( 'Image Accordion', 'fw' ),
		'thumb'  => 'accordion.svg',
		'css'    => 'accordion.css',
		'js'     => null,
		'splide' => false,
	),
	'flipcards' => array(
		'label'  => __( 'Flip Cards', 'fw' ),
		'thumb'  => 'flipcards.svg',
		'css'    => 'flipcards.css',
		'js'     => null,
		'splide' => false,
	),
	'stack' => array(
		'label'  => __( 'Stack / Banners', 'fw' ),
		'thumb'  => 'stack.svg',
		'css'    => 'stack.css',
		'js'     => null,
		'splide' => false,
	),
);
