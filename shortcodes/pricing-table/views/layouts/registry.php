<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Pricing table — LAYOUT registry (single source of truth).
 *
 * A LAYOUT is the plans' structure and owns its own render partial; a DESIGN (classic / modern / minimal /
 * gradient / dark / outline, in views/parts/registry.php) is a SKIN — paint on whatever structure is chosen.
 * Keeping them on separate axes is what lets a price LIST exist at all: it is a different shape, not a
 * different colour, and no amount of skinning turns a card grid into one.
 *
 * Three places read this one array, so adding a layout is one entry here plus a partial, a stylesheet and a
 * thumbnail:
 *   - options.php → the `design_settings` multi-picker's layout choices (and each layout's own options)
 *   - view.php    → which designs/<key>.php partial to include
 *   - static.php  → which css/designs/<css> to enqueue
 *
 * A registered layout with no partial would fall back to the grid silently, which reads as a bug — so an
 * entry here always ships with its partial, its rules and its thumbnail together.
 *
 * `bars` is deliberately NOT converter-detected. The conversion plan had guessed a `display:flex` row would
 * mean bars, but a flex row IS cards side by side and already maps to `grid`; and re-measured across the
 * capture corpus, priced groups whose plans are stacked SKINNED boxes came to 0 of 43 classifiable groups.
 * There is no source population to detect, so adding a detector rule could only put the 31 correctly-gridded
 * groups at risk. It exists for someone choosing it by hand — which is reason enough for a layout option,
 * just not for a conversion rule.
 *
 * Keys (the saved value):
 *   label : shown in the picker tooltip
 *   thumb : SVG under static/img/layouts/
 *   css   : stylesheet under static/css/designs/ (null = the base styles.css covers it)
 *
 * `grid` is the default and is covered by the always-enqueued base stylesheet, so an instance that has never
 * chosen a layout — i.e. every pricing table saved before this existed — renders exactly as it always did.
 */
return array(
	'grid' => array(
		'label' => __( 'Grid — cards side by side', 'fw' ),
		'thumb' => 'grid.svg',
		'css'   => null,
	),
	'list' => array(
		'label' => __( 'List — menu rows, price right', 'fw' ),
		'thumb' => 'list.svg',
		'css'   => null, // scoped under .fw-pt__list in the base stylesheet — see the note there
	),
	'bars' => array(
		'label' => __( 'Bars — full-width bands, price + button right', 'fw' ),
		'thumb' => 'bars.svg',
		'css'   => null, // scoped under .fw-pt__bars in the base stylesheet, for the same reason as list
	),
);
