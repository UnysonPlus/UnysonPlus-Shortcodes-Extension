<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Image Box — design registry (single source of truth).
 *
 * The Design control is a POPOVER multi-picker of 6 layout FAMILIES
 * (Stacked / Side / Overlay / Card / Frame). Each family reveals its own
 * variation sub-options; those variations collapse back to one of the flat
 * design KEYS below at render time, so the 7 PHP parts (views/parts/box-*.php)
 * and the ~20 `.imgbox--design-<key>` CSS selectors are reused unchanged. Adding
 * a look is therefore either a new variation choice (front-end only) or, for a
 * genuinely new appearance, a new `designs` entry + `static/css/design/<key>.css`.
 *
 * Readers:
 *   - options.php       → builds the family image-picker + per-family reveals.
 *   - views/parts/resolve.php → collapses (family + variation values) → flat key.
 *   - views/view.php    → dispatches to parts/box-<part>.php + emits the classes.
 *   - static.php        → auto-gates static/css/design/<key>.css (per instance).
 *
 * Value shape (saved under the `design_settings` att — a NEW key, never a legacy
 * scalar, so a stray old string can't feed the multi-picker and trip the
 * illegal-string-offset "blank error:" modal trap):
 *   design_settings => [ 'family' => 'overlay', 'overlay' => [ 'reveal' => 'fade', … ] ]
 *
 * Back-compat: a legacy scalar `design="overlay-slide"` still resolves — the
 * resolver falls back to reading the flat `design` att when `design_settings`
 * is absent.
 */

return array(

	/* ======================================================================
	   FAMILIES — the popover tiles. `part` is the default part; `thumb` is a
	   representative existing SVG (dedicated family thumbs can replace these
	   later). `default` is the flat key a freshly-picked family resolves to.
	   ====================================================================== */
	'families' => array(
		'stacked' => array(
			'label'   => __( 'Stacked', 'fw' ),
			'thumb'   => 'stacked.svg',
			'default' => 'stacked',
		),
		'side' => array(
			'label'   => __( 'Side', 'fw' ),
			'thumb'   => 'side-left.svg',
			'default' => 'side-left',
		),
		'overlay' => array(
			'label'   => __( 'Overlay', 'fw' ),
			'thumb'   => 'overlay-scrim.svg',
			'default' => 'overlay-scrim',
		),
		'card' => array(
			'label'   => __( 'Card', 'fw' ),
			'thumb'   => 'card.svg',
			'default' => 'card',
		),
		'frame' => array(
			'label'   => __( 'Frame', 'fw' ),
			'thumb'   => 'polaroid.svg',
			'default' => 'polaroid',
		),
	),

	/* ======================================================================
	   FLAT DESIGNS — the render target. `part` = views/parts/box-<part>.php;
	   `content_over_image` / `hover_reveal` become wrapper-class flags. This is
	   what view.php/static.php consume after the resolver collapses the family.
	   ====================================================================== */
	'designs' => array(

		/* --- Stacked ------------------------------------------------------- */
		'stacked'        => array( 'part' => 'stacked' ),
		'stacked-center' => array( 'part' => 'stacked' ),
		'icon-feature'   => array( 'part' => 'stacked' ),

		/* --- Side ---------------------------------------------------------- */
		'side-left'   => array( 'part' => 'side' ),
		'side-right'  => array( 'part' => 'side' ),
		'circle-side' => array( 'part' => 'side' ),
		'split-panel' => array( 'part' => 'split' ),

		/* --- Overlay (content over image) ---------------------------------- */
		'overlay-fade'    => array( 'part' => 'overlay', 'content_over_image' => true, 'hover_reveal' => true ),
		'overlay-slide'   => array( 'part' => 'overlay', 'content_over_image' => true, 'hover_reveal' => true ),
		'overlay-center'  => array( 'part' => 'overlay', 'content_over_image' => true, 'hover_reveal' => true ),
		'overlay-frame'   => array( 'part' => 'overlay', 'content_over_image' => true, 'hover_reveal' => true ),
		'overlay-scrim'   => array( 'part' => 'overlay', 'content_over_image' => true ),
		'caption-bar'     => array( 'part' => 'overlay', 'content_over_image' => true ),
		'editorial-cover' => array( 'part' => 'overlay', 'content_over_image' => true ),
		// Magazine "overlapping panel" — title/content overlaps the image edge
		// (CSS-grid overlap). Uses its own box-overlap.php part.
		'overlay-offset'  => array( 'part' => 'overlap', 'content_over_image' => true ),

		/* --- Card ---------------------------------------------------------- */
		'card'          => array( 'part' => 'card' ),
		'caption-below' => array( 'part' => 'card' ),

		/* --- Frame --------------------------------------------------------- */
		'polaroid'    => array( 'part' => 'frame' ),
		'postcard'    => array( 'part' => 'frame' ),
		'badge'       => array( 'part' => 'frame' ),
		'photo-stack' => array( 'part' => 'frame' ),
	),

	/* ======================================================================
	   LEGACY MAP — old flat key → the family + variation values that reproduce
	   it. Lets a saved `design="…"` scalar (or an items-corrector conversion)
	   pre-select the right family in the editor. Not needed for the frontend
	   (the resolver handles a bare scalar directly).
	   ====================================================================== */
	'legacy' => array(
		'stacked'         => array( 'family' => 'stacked', 'sub' => array( 'align' => 'standard', 'media' => 'full' ) ),
		'stacked-center'  => array( 'family' => 'stacked', 'sub' => array( 'align' => 'centered', 'media' => 'full' ) ),
		'icon-feature'    => array( 'family' => 'stacked', 'sub' => array( 'align' => 'centered', 'media' => 'compact' ) ),
		'side-left'       => array( 'family' => 'side', 'sub' => array( 'image_side' => 'left', 'shape' => 'rectangle', 'panel' => 'no' ) ),
		'side-right'      => array( 'family' => 'side', 'sub' => array( 'image_side' => 'right', 'shape' => 'rectangle', 'panel' => 'no' ) ),
		'circle-side'     => array( 'family' => 'side', 'sub' => array( 'image_side' => 'left', 'shape' => 'circle', 'panel' => 'no' ) ),
		'split-panel'     => array( 'family' => 'side', 'sub' => array( 'image_side' => 'left', 'shape' => 'rectangle', 'panel' => 'yes' ) ),
		'overlay-fade'    => array( 'family' => 'overlay', 'sub' => array( 'reveal' => 'fade' ) ),
		'overlay-slide'   => array( 'family' => 'overlay', 'sub' => array( 'reveal' => 'slide' ) ),
		'overlay-center'  => array( 'family' => 'overlay', 'sub' => array( 'reveal' => 'center' ) ),
		'overlay-frame'   => array( 'family' => 'overlay', 'sub' => array( 'reveal' => 'frame' ) ),
		'overlay-scrim'   => array( 'family' => 'overlay', 'sub' => array( 'reveal' => 'scrim' ) ),
		'caption-bar'     => array( 'family' => 'overlay', 'sub' => array( 'reveal' => 'bar' ) ),
		'editorial-cover' => array( 'family' => 'overlay', 'sub' => array( 'reveal' => 'cover' ) ),
		'overlay-offset'  => array( 'family' => 'overlay', 'sub' => array( 'reveal' => 'overlap' ) ),
		'card'            => array( 'family' => 'card', 'sub' => array( 'style' => 'card' ) ),
		'caption-below'   => array( 'family' => 'card', 'sub' => array( 'style' => 'caption-below' ) ),
		'polaroid'        => array( 'family' => 'frame', 'sub' => array( 'style' => 'polaroid' ) ),
		'postcard'        => array( 'family' => 'frame', 'sub' => array( 'style' => 'postcard' ) ),
		'badge'           => array( 'family' => 'frame', 'sub' => array( 'style' => 'badge' ) ),
		'photo-stack'     => array( 'family' => 'frame', 'sub' => array( 'style' => 'photo-stack' ) ),
	),
);
