/**
 * Masonry Section — tetris-packing engine (frontend).
 *
 * Each `.masonry-section .fw-row` is a 12-track grid with a 1px row unit. For
 * every column we compute an EXPLICIT placement (grid-column AND grid-row start)
 * with a deterministic bin-packer — we do NOT use `grid-auto-flow: dense`, which
 * mis-places items when their row-spans change via JS (it keeps the placement it
 * computed while the spans were still tiny and never re-flows). Each column is
 * placed at the leftmost column-range with the smallest current top, so columns
 * keep their real widths (read from the fw-col-{bp}-N class, mobile-first
 * cascade) and short ones tuck up to fill the gaps.
 *
 * Recomputes on: load, window load (late images), resize (debounced), and
 * per-item size changes (ResizeObserver). No library.
 */
( function () {
	'use strict';

	var ROW_UNIT = 1; // px — must match `grid-auto-rows` in masonry-section.css
	var COLS = 12;
	var BP_SM = 576, BP_MD = 768, BP_LG = 992; // match frontend-grid.css

	// Largest fw-col[-bp]-N class on the element, or 0 if none.
	function colAt( item, bp ) {
		var prefix = ( bp === '' ) ? 'fw-col-' : 'fw-col-' + bp + '-';
		for ( var n = COLS; n >= 1; n-- ) {
			if ( item.classList.contains( prefix + n ) ) {
				return n;
			}
		}
		if ( item.classList.contains( bp === '' ? 'fw-col' : 'fw-col-' + bp ) ) {
			return COLS;
		}
		return 0;
	}

	// Effective /12 span for the current viewport (mobile-first cascade), or 0 if
	// no width class is present yet.
	function colSpanFor( item ) {
		var w    = window.innerWidth;
		var base = colAt( item, '' );
		var sm = colAt( item, 'sm' ), md = colAt( item, 'md' ), lg = colAt( item, 'lg' );

		if ( ! base && ! sm && ! md && ! lg ) {
			return 0; // not ready
		}

		var span = base || sm || md || lg || COLS;
		if ( w >= BP_SM && sm ) { span = sm; }
		if ( w >= BP_MD && md ) { span = md; }
		if ( w >= BP_LG && lg ) { span = lg; }

		return Math.max( 1, Math.min( COLS, span ) );
	}

	function layoutRow( row ) {
		if ( ! row || window.getComputedStyle( row ).display !== 'grid' ) {
			return;
		}

		var children = row.children, i, k;

		// Phase 1 — widths (span, auto start) so heights measure at the real width.
		var spans = [], notReady = false;
		for ( i = 0; i < children.length; i++ ) {
			var s = colSpanFor( children[ i ] );
			if ( s === 0 ) { notReady = true; }
			spans[ i ] = Math.max( 1, Math.min( COLS, s || COLS ) );
			children[ i ].style.gridColumn = 'span ' + spans[ i ];
			children[ i ].style.gridRow = '';
		}
		// If a class isn't present yet (rare on the frontend — server-rendered),
		// retry; meanwhile we still lay out what we know.
		if ( notReady ) {
			window.setTimeout( function () { layoutRow( row ); }, 80 );
		}

		// Phase 2 — measure heights (incl. bottom-margin gap) → row spans.
		var rowSpans = [];
		for ( i = 0; i < children.length; i++ ) {
			var mb = parseFloat( window.getComputedStyle( children[ i ] ).marginBottom ) || 0;
			var h  = children[ i ].getBoundingClientRect().height + mb;
			rowSpans[ i ] = Math.max( 1, Math.ceil( h / ROW_UNIT ) );
		}

		// Phase 3 — explicit bin-pack: leftmost column-range with the smallest top.
		var colH = [];
		for ( i = 0; i < COLS; i++ ) { colH[ i ] = 0; }

		for ( i = 0; i < children.length; i++ ) {
			var span = spans[ i ];
			var rowSpan = rowSpans[ i ];

			var bestCol = 0, bestY = Infinity;
			for ( var c = 0; c + span <= COLS; c++ ) {
				var y = 0;
				for ( k = c; k < c + span; k++ ) { if ( colH[ k ] > y ) { y = colH[ k ]; } }
				if ( y < bestY ) { bestY = y; bestCol = c; }
			}

			children[ i ].style.gridColumn = ( bestCol + 1 ) + ' / span ' + span;
			children[ i ].style.gridRow = ( bestY + 1 ) + ' / span ' + rowSpan;

			var top = bestY + rowSpan;
			for ( k = bestCol; k < bestCol + span; k++ ) { colH[ k ] = top; }
		}
	}

	function layoutSection( section ) {
		section.classList.add( 'fw-masonry-ready' );
		var rows = section.querySelectorAll( '.fw-row' );
		for ( var i = 0; i < rows.length; i++ ) {
			layoutRow( rows[ i ] );
		}
	}

	function layoutAll() {
		var sections = document.querySelectorAll( '.masonry-section' );
		for ( var i = 0; i < sections.length; i++ ) {
			layoutSection( sections[ i ] );
		}
	}

	var ro = ( typeof ResizeObserver !== 'undefined' )
		? new ResizeObserver( function ( entries ) {
			var rows = [];
			for ( var i = 0; i < entries.length; i++ ) {
				var row = entries[ i ].target.parentNode;
				while ( row && ! ( row.classList && row.classList.contains( 'fw-row' ) ) ) {
					row = row.parentNode;
				}
				if ( row && rows.indexOf( row ) === -1 ) {
					rows.push( row );
				}
			}
			for ( var j = 0; j < rows.length; j++ ) {
				layoutRow( rows[ j ] );
			}
		} )
		: null;

	function observeItems() {
		if ( ! ro ) {
			return;
		}
		var items = document.querySelectorAll( '.masonry-section .fw-row > *' );
		for ( var i = 0; i < items.length; i++ ) {
			ro.observe( items[ i ] );
		}
	}

	var resizeTimer = null;
	function onResize() {
		if ( resizeTimer ) {
			window.clearTimeout( resizeTimer );
		}
		resizeTimer = window.setTimeout( layoutAll, 120 );
	}

	function init() {
		layoutAll();
		observeItems();
		window.addEventListener( 'resize', onResize );
		window.addEventListener( 'load', layoutAll );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}

	window.fwMasonryLayout = layoutAll;
} )();
