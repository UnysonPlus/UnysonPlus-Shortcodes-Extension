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
 * NEXT: `bars` (a full-width band per plan, price + CTA right) — 5% of the measured corpus. Add the entry
 * here together with its partial, stylesheet and thumbnail; a registered layout with no partial would fall
 * back to the grid silently, which reads as a bug.
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
);
