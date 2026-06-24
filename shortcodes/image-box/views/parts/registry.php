<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Image Box — design registry (single source of truth).
 *
 * Each entry registers one selectable Design. Three places read this array:
 *   - options.php  → builds the `design` image-picker `choices`
 *   - view.php     → dispatches the box to `parts/box-<part>.php`
 *   - static.php   → auto-gates the per-design CSS (static/css/design/<key>.css)
 *
 * Adding a design = one entry here + (reuse or add) `views/parts/box-<part>.php`
 * + a thumbnail `static/img/design/<thumb>` + (optional) per-design CSS
 * `static/css/design/<key>.css`. No other file changes — the picker, the
 * dispatcher and the CSS gating all read this.
 *
 * PER-DESIGN CSS (only the chosen design loads): the base styles.css carries the
 * shared/structural CSS (the box shell, media frame, content stack, hover-effect
 * primitives) used by every design; a design's OWN look lives in
 * static/css/design/<key>.css and is enqueued only for instances that pick it
 * (static.php's per-instance hook auto-detects the file by name — no list to
 * maintain). Structural designs (stacked / side / card) have no file; the base
 * covers them.
 *
 * Keys (NON-EMPTY strings — they become the saved Design value):
 *   label              : human label shown in the picker
 *   thumb              : SVG filename under static/img/design/
 *   part               : which views/parts/box-<part>.php renders it
 *   content_over_image : (optional, bool) the text stack sits ON the image
 *                        (overlay/frame family) rather than beside/below it
 *   hover_reveal       : (optional, bool) the content is hidden until hover
 *   needs_media_width  : (optional, bool) reveals the Media Width control as
 *                        meaningful (side family — image vs content split)
 *   family             : grouping label (informational; not used for dispatch)
 */
return array(

	/* --- Structure --------------------------------------------------------- */
	'stacked' => array(
		'label'  => __( 'Stacked — image top', 'fw' ),
		'thumb'  => 'stacked.svg',
		'part'   => 'stacked',
		'family' => 'structure',
	),
	'stacked-center' => array(
		'label'  => __( 'Stacked — centered', 'fw' ),
		'thumb'  => 'stacked-center.svg',
		'part'   => 'stacked',
		'family' => 'structure',
	),
	'icon-feature' => array(
		'label'  => __( 'Feature — small image, text below', 'fw' ),
		'thumb'  => 'icon-feature.svg',
		'part'   => 'stacked',
		'family' => 'structure',
	),
	'side-left' => array(
		'label'             => __( 'Side — image left', 'fw' ),
		'thumb'             => 'side-left.svg',
		'part'              => 'side',
		'needs_media_width' => true,
		'family'            => 'structure',
	),
	'side-right' => array(
		'label'             => __( 'Side — image right', 'fw' ),
		'thumb'             => 'side-right.svg',
		'part'              => 'side',
		'needs_media_width' => true,
		'family'            => 'structure',
	),

	/* --- Hover overlays ---------------------------------------------------- */
	'overlay-fade' => array(
		'label'              => __( 'Overlay — fade in on hover', 'fw' ),
		'thumb'              => 'overlay-fade.svg',
		'part'               => 'overlay',
		'content_over_image' => true,
		'hover_reveal'       => true,
		'family'             => 'overlay',
	),
	'overlay-slide' => array(
		'label'              => __( 'Overlay — slide up on hover', 'fw' ),
		'thumb'              => 'overlay-slide.svg',
		'part'               => 'overlay',
		'content_over_image' => true,
		'hover_reveal'       => true,
		'family'             => 'overlay',
	),
	'overlay-center' => array(
		'label'              => __( 'Overlay — centered on hover', 'fw' ),
		'thumb'              => 'overlay-center.svg',
		'part'               => 'overlay',
		'content_over_image' => true,
		'hover_reveal'       => true,
		'family'             => 'overlay',
	),
	'overlay-frame' => array(
		'label'              => __( 'Overlay — frame draw on hover', 'fw' ),
		'thumb'              => 'overlay-frame.svg',
		'part'               => 'overlay',
		'content_over_image' => true,
		'hover_reveal'       => true,
		'family'             => 'overlay',
	),
	'overlay-scrim' => array(
		'label'              => __( 'Overlay — always-on gradient scrim', 'fw' ),
		'thumb'              => 'overlay-scrim.svg',
		'part'               => 'overlay',
		'content_over_image' => true,
		'family'             => 'overlay',
	),

	/* --- Captions & cards -------------------------------------------------- */
	'card' => array(
		'label'  => __( 'Card — bordered, image top', 'fw' ),
		'thumb'  => 'card.svg',
		'part'   => 'card',
		'family' => 'card',
	),
	'caption-below' => array(
		'label'  => __( 'Caption — clean strip below', 'fw' ),
		'thumb'  => 'caption-below.svg',
		'part'   => 'card',
		'family' => 'card',
	),
	'caption-bar' => array(
		'label'              => __( 'Caption — solid bar over image', 'fw' ),
		'thumb'              => 'caption-bar.svg',
		'part'               => 'overlay',
		'content_over_image' => true,
		'family'             => 'card',
	),

	/* --- Frames ------------------------------------------------------------ */
	'polaroid' => array(
		'label'  => __( 'Polaroid frame', 'fw' ),
		'thumb'  => 'polaroid.svg',
		'part'   => 'frame',
		'family' => 'frame',
	),
	'postcard' => array(
		'label'  => __( 'Postcard frame', 'fw' ),
		'thumb'  => 'postcard.svg',
		'part'   => 'frame',
		'family' => 'frame',
	),
	'badge' => array(
		'label'  => __( 'Bordered badge', 'fw' ),
		'thumb'  => 'badge.svg',
		'part'   => 'frame',
		'family' => 'frame',
	),

	/* --- More designs ------------------------------------------------------ */
	'circle-side' => array(
		'label'             => __( 'Circle — round image beside text', 'fw' ),
		'thumb'             => 'circle-side.svg',
		'part'              => 'side',
		'needs_media_width' => true,
		'family'            => 'structure',
	),
	'split-panel' => array(
		'label'             => __( 'Split — image + colour panel', 'fw' ),
		'thumb'             => 'split-panel.svg',
		'part'              => 'split',
		'needs_media_width' => true,
		'family'            => 'structure',
	),
	'photo-stack' => array(
		'label'  => __( 'Photo stack — layered frames', 'fw' ),
		'thumb'  => 'photo-stack.svg',
		'part'   => 'frame',
		'family' => 'frame',
	),
	'editorial-cover' => array(
		'label'              => __( 'Editorial cover — title at top', 'fw' ),
		'thumb'              => 'editorial-cover.svg',
		'part'               => 'overlay',
		'content_over_image' => true,
		'family'             => 'overlay',
	),
	'flip-card' => array(
		'label'  => __( 'Flip card — flips on hover', 'fw' ),
		'thumb'  => 'flip-card.svg',
		'part'   => 'flip',
		'family' => 'card',
	),
);
