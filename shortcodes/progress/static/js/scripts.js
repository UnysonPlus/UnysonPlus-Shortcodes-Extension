/* Unyson+ Progress Bars — fill bars / rings / gauges + count the % up when scrolled into view. */
( function () {
	'use strict';

	var reduce = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	function countUp( el, target ) {
		if ( reduce ) { el.textContent = target + '%'; return; }
		var start = null, dur = 900;
		function step( ts ) {
			if ( start === null ) { start = ts; }
			var p = Math.min( 1, ( ts - start ) / dur );
			el.textContent = Math.round( p * target ) + '%';
			if ( p < 1 ) { requestAnimationFrame( step ); }
		}
		requestAnimationFrame( step );
	}

	function fill( wrap ) {
		// Horizontal bars: set width to data-width.
		[].forEach.call( wrap.querySelectorAll( '.fw-progress__fill' ), function ( b ) {
			var w = b.getAttribute( 'data-width' );
			if ( w ) { b.style.width = w; }
		} );
		// Vertical bars: grow the fill to its data-height.
		[].forEach.call( wrap.querySelectorAll( '.fw-progress__vfill' ), function ( b ) {
			var h = b.getAttribute( 'data-height' );
			if ( h ) { b.style.height = h; }
		} );
		// Circles / gauges / pies: relax the stroke to its final dash offset.
		[].forEach.call( wrap.querySelectorAll( '.fw-progress__svg-fill' ), function ( s ) {
			var o = s.getAttribute( 'data-offset' );
			if ( o !== null ) { s.style.strokeDashoffset = o; }
		} );
		// Count the numbers up.
		[].forEach.call( wrap.querySelectorAll( '.fw-progress__value[data-count]' ), function ( v ) {
			countUp( v, parseInt( v.getAttribute( 'data-count' ), 10 ) || 0 );
		} );
	}

	function init() {
		[].forEach.call( document.querySelectorAll( '.fw-progress' ), function ( wrap ) {
			// Animation disabled → items already carry their final state inline.
			if ( wrap.getAttribute( 'data-animate' ) === '0' || ! ( 'IntersectionObserver' in window ) ) {
				fill( wrap );
				return;
			}
			var io = new IntersectionObserver( function ( entries, obs ) {
				entries.forEach( function ( e ) {
					if ( e.isIntersecting ) { fill( e.target ); obs.unobserve( e.target ); }
				} );
			}, { threshold: 0.25 } );
			io.observe( wrap );
		} );
	}

	if ( document.readyState !== 'loading' ) {
		init();
	} else {
		document.addEventListener( 'DOMContentLoaded', init );
	}
} )();
