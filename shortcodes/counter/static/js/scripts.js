/* Unyson+ Animated Counter — count from start to target when scrolled into view. */
( function () {
	'use strict';

	var EASING = {
		'linear': function ( t ) { return t; },
		'ease-out': function ( t ) { return 1 - Math.pow( 1 - t, 3 ); },
		'ease-in-out': function ( t ) { return t < 0.5 ? 2 * t * t : 1 - Math.pow( -2 * t + 2, 2 ) / 2; }
	};

	function format( n, decimals, sep ) {
		var s = ( decimals > 0 ) ? n.toFixed( decimals ) : String( Math.round( n ) );
		if ( sep ) {
			var parts = s.split( '.' );
			parts[ 0 ] = parts[ 0 ].replace( /\B(?=(\d{3})+(?!\d))/g, ',' );
			s = parts.join( '.' );
		}
		return s;
	}

	function run( el ) {
		if ( el.getAttribute( 'data-counted' ) === '1' ) { return; }
		el.setAttribute( 'data-counted', '1' );

		var target = parseFloat( el.getAttribute( 'data-target' ) ) || 0;
		var start = parseFloat( el.getAttribute( 'data-start' ) ) || 0;
		var duration = parseInt( el.getAttribute( 'data-duration' ), 10 ) || 2000;
		var decimals = parseInt( el.getAttribute( 'data-decimals' ), 10 ) || 0;
		var sep = el.getAttribute( 'data-sep' ) === '1';
		var ease = EASING[ el.getAttribute( 'data-easing' ) ] || EASING[ 'ease-out' ];
		var numEl = el.querySelector( '.fw-counter__num' );
		if ( ! numEl ) { return; }

		if ( duration <= 0 || ( window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) ) {
			numEl.textContent = format( target, decimals, sep );
			return;
		}

		var begin = null;
		function step( ts ) {
			if ( begin === null ) { begin = ts; }
			var p = Math.min( ( ts - begin ) / duration, 1 );
			numEl.textContent = format( start + ( target - start ) * ease( p ), decimals, sep );
			if ( p < 1 ) {
				requestAnimationFrame( step );
			} else {
				numEl.textContent = format( target, decimals, sep );
			}
		}
		numEl.textContent = format( start, decimals, sep );
		requestAnimationFrame( step );
	}

	function init() {
		var els = [].slice.call( document.querySelectorAll( '.fw-counter__value' ) );
		if ( ! els.length ) { return; }

		if ( ! ( 'IntersectionObserver' in window ) || ! ( 'requestAnimationFrame' in window ) ) {
			els.forEach( run );
			return;
		}
		var io = new IntersectionObserver( function ( entries ) {
			entries.forEach( function ( e ) {
				if ( e.isIntersecting ) {
					run( e.target );
					io.unobserve( e.target );
				}
			} );
		}, { threshold: 0.4 } );
		els.forEach( function ( el ) { io.observe( el ); } );
	}

	if ( document.readyState !== 'loading' ) {
		init();
	} else {
		document.addEventListener( 'DOMContentLoaded', init );
	}
} )();
