/* Unyson+ Countdown Timer — tick down to a target timestamp, update every second. */
( function () {
	'use strict';

	function pad( n ) { return n < 10 ? '0' + n : '' + n; }

	function setUnit( el, unit, val, padded ) {
		var n = el.querySelector( '.fw-countdown__unit[data-unit="' + unit + '"] .fw-countdown__num' );
		if ( n ) { n.textContent = padded ? pad( val ) : String( val ); }
	}

	function complete( el ) {
		var mode = el.getAttribute( 'data-oncomplete' );
		if ( mode === 'hide' ) {
			el.style.display = 'none';
		} else if ( mode === 'message' ) {
			var units = el.querySelector( '.fw-countdown__units' );
			if ( units ) { units.style.display = 'none'; }
			var done = el.querySelector( '.fw-countdown__done' );
			if ( done ) { done.style.display = ''; }
		}
		// 'zeros' → leave the units showing 00.
	}

	// Render the element once; returns true when the countdown has finished.
	function tick( el ) {
		var target = parseInt( el.getAttribute( 'data-target' ), 10 );
		if ( ! target ) { return true; }

		var diff = target - Date.now();
		var ended = diff <= 0;
		if ( ended ) { diff = 0; }

		var total = Math.floor( diff / 1000 );
		var days = Math.floor( total / 86400 );
		var hours = Math.floor( ( total % 86400 ) / 3600 );
		var minutes = Math.floor( ( total % 3600 ) / 60 );
		var seconds = total % 60;

		setUnit( el, 'days', days, false );
		setUnit( el, 'hours', hours, true );
		setUnit( el, 'minutes', minutes, true );
		setUnit( el, 'seconds', seconds, true );

		if ( ended ) { complete( el ); return true; }
		return false;
	}

	function init() {
		var els = [].slice.call( document.querySelectorAll( '.fw-countdown' ) );
		els.forEach( function ( el ) {
			if ( tick( el ) ) { return; }
			var iv = setInterval( function () {
				if ( tick( el ) ) { clearInterval( iv ); }
			}, 1000 );
		} );
	}

	if ( document.readyState !== 'loading' ) {
		init();
	} else {
		document.addEventListener( 'DOMContentLoaded', init );
	}
} )();
