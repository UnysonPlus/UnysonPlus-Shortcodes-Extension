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
 *   category     : (optional) 'decorated' groups the design under the picker's
 *                  "Decorated" header (a skin reproducible via Box / Image Style
 *                  presets); anything else is a structural "Layout". Presentational
 *                  only — the saved value is unchanged.
 *   has_position : (optional, bool) reveals an Image Position (Left / Right) sub-option
 *                  in the Card Style picker. `side` resolves it to side-left/side-right
 *                  (view); the other horizontal styles flip via a `--img-right` class.
 *   hidden       : (optional, bool) keep the entry for the renderer's dispatch (legacy
 *                  saved values, Alternating's per-row style) but hide it from the picker.
 */
return array(
	'standard' => array(
		'label' => __( 'Standard — image top', 'fw' ),
		'thumb' => 'standard.svg',
		'part'  => 'standard',
	),
	'side' => array(
		'label'        => __( 'Side — image beside content', 'fw' ),
		'thumb'        => 'side-left.svg',
		'part'         => 'side',
		'needs_ratio'  => true,
		'has_position' => true, // reveals Image Position (Left / Right); the view resolves to side-left/right
	),
	// Legacy directional values — kept for existing saved pages AND as the Alternating
	// design's per-row effective style; hidden from the picker (superseded by Side +
	// Image Position, which resolves to these). Never remove — the renderer dispatches them.
	'side-left' => array(
		'label'       => __( 'Side — image left', 'fw' ),
		'thumb'       => 'side-left.svg',
		'part'        => 'side',
		'needs_ratio' => true,
		'hidden'      => true,
	),
	'side-right' => array(
		'label'       => __( 'Side — image right', 'fw' ),
		'thumb'       => 'side-right.svg',
		'part'        => 'side',
		'needs_ratio' => true,
		'hidden'      => true,
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
		'category' => 'decorated', // reproducible via Box / Image Style presets
	),
	'listicle' => array(
		'label' => __( 'Numbered Listicle', 'fw' ),
		'thumb' => 'listicle.svg',
		'part'  => 'listicle',
		'has_position' => true, // reveals Image Position (Left / Right)
	),
	'newslist' => array(
		'label' => __( 'Compact News List', 'fw' ),
		'thumb' => 'newslist.svg',
		'part'  => 'newslist',
		'has_position' => true, // reveals Image Position (Left / Right)
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
		'category' => 'decorated', // reproducible via Box / Image Style presets
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
		'category' => 'decorated', // reproducible via Box / Image Style presets
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
		'category' => 'decorated', // reproducible via Box / Image Style presets
	),
	'cover' => array(
		'label' => __( 'Magazine Cover', 'fw' ),
		'thumb' => 'cover.svg',
		'part'  => 'cover',
		'category' => 'decorated', // reproducible via Box / Image Style presets
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
		'has_position' => true, // reveals Image Position (Left / Right)
	),
	'badge' => array(
		'label' => __( 'Bordered Badge', 'fw' ),
		'thumb' => 'badge.svg',
		'part'  => 'badge',
		'category' => 'decorated', // reproducible via Box / Image Style presets
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
		'category' => 'decorated', // reproducible via Box / Image Style presets
	),
	'glass' => array(
		'label' => __( 'Glassmorphism', 'fw' ),
		'thumb' => 'glass.svg',
		'part'  => 'glass',
		'category' => 'decorated', // reproducible via Box / Image Style presets
	),
);
