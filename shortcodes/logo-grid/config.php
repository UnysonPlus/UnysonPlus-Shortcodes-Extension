<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array();

$cfg['page_builder'] = array(
	'title'       => __( 'Logo Grid', 'fw' ),
	'description' => __( 'A grid, boxed grid, carousel or marquee of client / partner logos, with an optional grayscale-to-color hover.', 'fw' ),
	'tab'         => __( 'Media Elements', 'fw' ),
	'popup_size'  => 'large',

	// Canvas preview: each logo (uploaded image, inline SVG mark, or name-only) shows on
	// ONE line with a consistent gap. Inline SVG marks are forced to fill:currentColor +
	// a set height (via an injected style attr) so a white frontend mark is still visible
	// on the light builder canvas, and sizes correctly regardless of its own attributes.
	'title_template' => '
		{{ if ( o && o["logos"] && o["logos"].length ) { }}
			<div style="display:flex;flex-wrap:wrap;gap:18px;align-items:center;margin-top:.5rem;color:#334155;">
				{{ for ( var i = 0; i < Math.min( o["logos"].length, 10 ); i++ ) {
					var l = o["logos"][i];
					var u = ( l.image && l.image.url ) ? l.image.url : "";
					var nm = l.name || "";
					var sv = l.svg ? String( l.svg ).replace( /<svg/i, \'<svg style="height:22px;width:auto;max-width:none;fill:currentColor;vertical-align:middle"\' ) : "";
					if ( !u && !sv && !nm ) { continue; }
				}}
					<span style="display:inline-flex;align-items:center;gap:6px;white-space:nowrap;">{{ if ( u ) { }}<img src="{{- u }}" style="height:22px;max-width:90px;object-fit:contain;vertical-align:middle;" />{{ } else if ( sv ) { }}{{= sv }}{{ } }}{{ if ( nm ) { }}<b style="font-size:13px;font-weight:700;">{{- nm }}</b>{{ } }}</span>
				{{ } }}
			</div>
		{{ } else { }}
			<em>No logos added</em>
		{{ } }}
	',
);
