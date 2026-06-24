<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Posts — card-design registry (single source of truth).
 *
 * Each entry registers one selectable Card Style. Three places read this array:
 *   - options.php  → builds the `card` image-picker `choices`
 *   - view.php     → dispatches each post to `parts/card-<part>.php`
 *   - (thumbnails) → static/img/card/<thumb>
 *
 * Adding a card design = one entry here + `views/parts/card-<part>.php` +
 * `static/img/card/<thumb>` + (optional) `static/css/card/<key>.css`. No other
 * file changes — the picker, dispatcher and gating all read this.
 *
 * PER-DESIGN CSS (only the used design loads): the base styles.css carries the
 * shared/structural CSS; a design's OWN CSS goes in static/css/card/<key>.css
 * and is enqueued ONLY for instances that use that style (static.php's
 * per-instance hook auto-detects the file by name — no list to maintain). The
 * original structural styles (standard / side-left / side-right / overlay /
 * minimal / hero-split / alternating) have no file; the base covers them.
 *
 * Keys (NON-EMPTY strings — they become the saved Card Style value):
 *   label        : human label shown in the picker
 *   thumb        : SVG filename under static/img/card/
 *   part         : which views/parts/card-<part>.php renders it
 *   first_style  : (optional) effective style to use for the FIRST post only
 *                  (composition — e.g. hero-split renders the first post as overlay)
 *   alternate    : (optional, bool) flip side-left / side-right per row (zig-zag)
 *   needs_ratio  : (optional, bool) reveals the image-ratio + vertical-align
 *                  sub-options in the Card Style picker (side / alternating / hero)
 */
return array(
	'standard' => array(
		'label' => __( 'Standard — image top', 'fw' ),
		'thumb' => 'standard.svg',
		'part'  => 'standard',
	),
	'side-left' => array(
		'label'       => __( 'Side — image left', 'fw' ),
		'thumb'       => 'side-left.svg',
		'part'        => 'side',
		'needs_ratio' => true,
	),
	'side-right' => array(
		'label'       => __( 'Side — image right', 'fw' ),
		'thumb'       => 'side-right.svg',
		'part'        => 'side',
		'needs_ratio' => true,
	),
	'overlay' => array(
		'label' => __( 'Overlay — content over image', 'fw' ),
		'thumb' => 'overlay.svg',
		'part'  => 'overlay',
	),
	'minimal' => array(
		'label' => __( 'Minimal — no image, text only', 'fw' ),
		'thumb' => 'minimal.svg',
		'part'  => 'minimal',
	),
	'hero-split' => array(
		'label'       => __( 'Hero split — first post 2× overlay', 'fw' ),
		'thumb'       => 'hero-split.svg',
		'part'        => 'standard',
		'first_style' => 'overlay',
		'needs_ratio' => true,
	),
	'alternating' => array(
		'label'       => __( 'Alternating — zig-zag side', 'fw' ),
		'thumb'       => 'alternating.svg',
		'part'        => 'side',
		'alternate'   => true,
		'needs_ratio' => true,
	),

	/* --- New designs --------------------------------------------------- */
	'gradient' => array(
		'label' => __( 'Gradient Overlay (Magazine)', 'fw' ),
		'thumb' => 'gradient.svg',
		'part'  => 'gradient',
	),
	'listicle' => array(
		'label' => __( 'Numbered Listicle', 'fw' ),
		'thumb' => 'listicle.svg',
		'part'  => 'listicle',
	),
	'newslist' => array(
		'label' => __( 'Compact News List', 'fw' ),
		'thumb' => 'newslist.svg',
		'part'  => 'newslist',
	),
	'editorial' => array(
		'label' => __( 'Editorial Big-Title', 'fw' ),
		'thumb' => 'editorial.svg',
		'part'  => 'editorial',
	),
	'polaroid' => array(
		'label' => __( 'Polaroid', 'fw' ),
		'thumb' => 'polaroid.svg',
		'part'  => 'polaroid',
	),
	'timeline' => array(
		'label' => __( 'Timeline', 'fw' ),
		'thumb' => 'timeline.svg',
		'part'  => 'timeline',
	),
	'tile' => array(
		'label' => __( 'Tile (Hover Reveal)', 'fw' ),
		'thumb' => 'tile.svg',
		'part'  => 'tile',
	),
	'circular' => array(
		'label' => __( 'Circular', 'fw' ),
		'thumb' => 'circular.svg',
		'part'  => 'circular',
	),
	'accent' => array(
		'label' => __( 'Accent Bar', 'fw' ),
		'thumb' => 'accent.svg',
		'part'  => 'accent',
	),
	'cover' => array(
		'label' => __( 'Magazine Cover', 'fw' ),
		'thumb' => 'cover.svg',
		'part'  => 'cover',
	),
	'quote' => array(
		'label' => __( 'Quote-Led', 'fw' ),
		'thumb' => 'quote.svg',
		'part'  => 'quote',
	),
	'postcard' => array(
		'label' => __( 'Postcard', 'fw' ),
		'thumb' => 'postcard.svg',
		'part'  => 'postcard',
	),
	'badge' => array(
		'label' => __( 'Bordered Badge', 'fw' ),
		'thumb' => 'badge.svg',
		'part'  => 'badge',
	),
	'filmstrip' => array(
		'label' => __( 'Filmstrip', 'fw' ),
		'thumb' => 'filmstrip.svg',
		'part'  => 'filmstrip',
	),
	'diagonal' => array(
		'label' => __( 'Diagonal Split', 'fw' ),
		'thumb' => 'diagonal.svg',
		'part'  => 'diagonal',
	),
	'glass' => array(
		'label' => __( 'Glassmorphism', 'fw' ),
		'thumb' => 'glass.svg',
		'part'  => 'glass',
	),
);
