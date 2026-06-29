/**
 * Component-Presets layout diagnostic (Theme Settings).
 *
 * Loads ONLY when the page URL contains ?upw_preset_debug=1. It finds every element
 * that is wider than the viewport (the cause of horizontal page overflow), logs them
 * as a sortable table (deepest/narrowest first — usually the real culprit), and
 * outlines them. Paste the table here and the exact offending element + width chain
 * is known, so the fix can be made precise instead of guessed.
 *
 * Re-runs on load and on every tab click (the preset sub-tabs inject lazily).
 */
( function () {
	'use strict';

	function chainOf( el ) {
		var parts = [];
		var node = el;
		var hops = 0;
		while ( node && node !== document.body && hops < 8 ) {
			var cls = ( node.className || '' ).toString().trim().split( /\s+/ ).slice( 0, 2 ).join( '.' );
			parts.unshift( node.tagName.toLowerCase() + ( cls ? '.' + cls : '' ) );
			node = node.parentElement;
			hops++;
		}
		return parts.join( ' > ' );
	}

	function run() {
		var docW = document.documentElement.clientWidth;
		var rows = [];
		var scope = document.getElementById( 'wpbody-content' ) || document.body;

		scope.querySelectorAll( '*' ).forEach( function ( el ) {
			var ow = el.offsetWidth;
			if ( ow > docW + 2 ) {
				var p = el.parentElement;
				rows.push( {
					el: el,
					tag: el.tagName.toLowerCase(),
					classes: ( el.className || '' ).toString().slice( 0, 90 ),
					offsetW: ow,
					scrollW: el.scrollWidth,
					parentW: p ? p.clientWidth : 0,
					overParent: p ? ow - p.clientWidth : 0
				} );
			}
		} );

		// Narrowest wide element first: that's typically where the over-width
		// originates; everything wider is an ancestor stretched by it.
		rows.sort( function ( a, b ) { return a.offsetW - b.offsetW; } );

		console.log( '%c[UPW preset overflow diagnostic]', 'font-weight:bold;color:#2f74e6',
			'viewport=' + docW + 'px · ' + rows.length + ' element(s) wider than the viewport' );

		if ( ! rows.length ) {
			console.log( 'No horizontal overflow detected right now.' );
			return;
		}

		console.table( rows.map( function ( r ) {
			return {
				tag: r.tag,
				classes: r.classes,
				offsetW: r.offsetW,
				scrollW: r.scrollW,
				parentW: r.parentW,
				overParent: r.overParent
			};
		} ) );

		// The single best clue: the NARROWEST element that still overflows its parent.
		var origin = rows.filter( function ( r ) { return r.overParent > 0; } )[ 0 ] || rows[ 0 ];
		console.log( '%cLikely origin:', 'font-weight:bold;color:#b02a37', chainOf( origin.el ),
			'(offsetW=' + origin.offsetW + ', parentW=' + origin.parentW + ')' );

		// Outline everything wide.
		rows.forEach( function ( r, i ) {
			r.el.style.outline = ( r === origin ? '3px solid #b02a37' : '1px dashed #2f74e6' );
			r.el.setAttribute( 'data-upw-wide', i );
		} );
	}

	function schedule() { setTimeout( run, 600 ); }

	if ( document.readyState === 'complete' ) { schedule(); }
	else { window.addEventListener( 'load', schedule ); }

	document.addEventListener( 'click', function ( e ) {
		if ( e.target.closest && e.target.closest( '.nav-tab, .fw-options-tabs-list a' ) ) { setTimeout( run, 400 ); }
	} );

	// Expose a manual trigger.
	window.upwPresetOverflow = run;
}() );
