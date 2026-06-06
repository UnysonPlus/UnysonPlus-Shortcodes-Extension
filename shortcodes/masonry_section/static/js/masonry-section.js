/**
 * Masonry Section — left-to-right CSS-grid masonry engine.
 *
 * Each `.masonry-section .fw-row` is a CSS grid with a 1px row unit
 * (grid-auto-rows). For every grid child we measure its natural height (the item
 * is `align-self:start`, so it never stretches and measuring stays stable) plus
 * its bottom margin, and set `grid-row-end: span N` so items pack vertically with
 * source order preserved across columns.
 *
 * Recomputes on: initial load, window load (late images), window resize
 * (debounced), and per-item content/size changes via ResizeObserver. No library.
 */
( function () {
	'use strict';

	var ROW_UNIT = 1; // px — must match `grid-auto-rows` in masonry-section.css

	function layoutRow( row ) {
		if ( ! row || window.getComputedStyle( row ).display !== 'grid' ) {
			return;
		}

		var children = row.children;
		var i, item, h, mb;

		// Reset spans first so each item reports its natural (un-stretched) height.
		for ( i = 0; i < children.length; i++ ) {
			children[ i ].style.gridRowEnd = '';
		}

		// Measure and assign spans.
		for ( i = 0; i < children.length; i++ ) {
			item = children[ i ];
			mb   = parseFloat( window.getComputedStyle( item ).marginBottom ) || 0;
			h    = item.getBoundingClientRect().height + mb;
			item.style.gridRowEnd = 'span ' + Math.max( 1, Math.ceil( h / ROW_UNIT ) );
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

	// Re-layout only the row owning a changed item.
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
		window.addEventListener( 'load', layoutAll ); // late images without fixed dimensions
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}

	// Exposed so other contexts (e.g. the editor preview) can reuse the engine.
	window.fwMasonryLayout = layoutAll;
} )();
