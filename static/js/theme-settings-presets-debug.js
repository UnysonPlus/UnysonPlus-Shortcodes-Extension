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


/**
 * Preset-accordion tracer. Logs why a preset box does (or does not) reveal its body
 * (.inside) when clicked. Click a Color preset vs a Typography preset and compare the
 * two logged snapshots. Also catches JS errors fired during the click (e.g. an Iris /
 * color-picker error that aborts the box render). Loads only with ?upw_preset_debug=1.
 */
( function () {
	'use strict';

	window.addEventListener( 'error', function ( ev ) {
		console.log( '%c[UPW preset JS error]', 'color:#b02a37;font-weight:bold',
			ev.message, '@', ( ev.filename || '' ) + ':' + ( ev.lineno || '' ) );
	} );

	function libOf( el ) {
		var box = el.closest ? el.closest( '.fw-option-type-addable-box' ) : null;
		if ( box && box.id ) { return box.id; }
		var tab = el.closest ? el.closest( '.fw-options-tab' ) : null;
		return tab && tab.id ? tab.id : '(unknown)';
	}

	function snap( optionBox ) {
		var pb     = optionBox.querySelector( '.fw-postbox' );
		var inside = optionBox.querySelector( '.inside' );
		var cs     = inside ? getComputedStyle( inside ) : null;
		return {
			hasPostbox:    !! pb,
			hasHeader:     !! optionBox.querySelector( '.postbox-header .hndle' ),
			hasInside:     !! inside,
			closed:        pb ? pb.classList.contains( 'closed' ) : null,
			insideDisplay: cs ? cs.display : null,
			insideVisible: cs ? ( cs.display !== 'none' && cs.visibility !== 'hidden' && parseFloat( cs.opacity ) > 0 ) : null,
			insideHeight:  inside ? inside.offsetHeight : null,
			insideKids:    inside ? inside.children.length : null,
			hasIris:       !! optionBox.querySelector( '.iris-picker, .wp-picker-container, .iris-initialized, .a8c-iris' ),
			hasColorField: !! optionBox.querySelector( '.fw-option-type-color-picker' )
		};
	}

	// Capture-phase so we see the state BEFORE the framework's own handler runs, then
	// sample again after it has had a chance to toggle.
	document.addEventListener( 'click', function ( e ) {
		var optionBox = e.target.closest && e.target.closest(
			'#fw-options-tab-components_container .fw-preset-2col .fw-option-box'
		);
		if ( ! optionBox ) { return; }

		var lib    = libOf( optionBox );
		var before = snap( optionBox );
		setTimeout( function () {
			var after = snap( optionBox );
			console.log( '%c[UPW preset click] ' + lib, 'color:#2f74e6;font-weight:bold' );
			console.table( { before: before, after: after } );
			if ( before.closed === after.closed ) {
				console.log( '%c  → .closed did NOT change: the toggle handler did not fire on this box.', 'color:#b02a37' );
			} else if ( ! after.insideVisible ) {
				console.log( '%c  → box opened but .inside is not visible (height=' + after.insideHeight + ', kids=' + after.insideKids + ').', 'color:#b02a37' );
			} else {
				console.log( '%c  → box opened and body is visible.', 'color:#1e7d4f' );
			}
		}, 200 );
	}, true );

	// Manual full dump of every preset box's current state.
	window.upwPresetTrace = function () {
		var boxes = document.querySelectorAll( '#fw-options-tab-components_container .fw-preset-2col .fw-option-box' );
		console.log( '%c[UPW preset trace] ' + boxes.length + ' preset box(es) in view', 'color:#2f74e6;font-weight:bold' );
		Array.prototype.forEach.call( boxes, function ( b, i ) {
			console.log( i, libOf( b ), snap( b ) );
		} );
	};
}() );
