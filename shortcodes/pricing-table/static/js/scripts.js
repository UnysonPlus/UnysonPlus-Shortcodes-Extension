/**
 * Pricing Table — Monthly / Yearly billing toggle.
 * Clicking the switch OR either label flips `is-yearly` on the parent .fw-pt; CSS
 * swaps which price (--monthly / --yearly) shows. Event-delegated, so it works for
 * any table present now or injected later (builder preview, AJAX).
 */
( function () {
	'use strict';

	function setState( pt, yearly ) {
		pt.classList.toggle( 'is-yearly', yearly );
		var sw = pt.querySelector( '.fw-pt__billing-switch' );
		if ( sw ) { sw.setAttribute( 'aria-checked', yearly ? 'true' : 'false' ); }
		var m = pt.querySelector( '.fw-pt__billing-label--monthly' );
		var y = pt.querySelector( '.fw-pt__billing-label--yearly' );
		if ( m ) { m.classList.toggle( 'is-active', ! yearly ); }
		if ( y ) { y.classList.toggle( 'is-active', yearly ); }
	}

	document.addEventListener( 'click', function ( e ) {
		var sw = e.target.closest( '.fw-pt__billing-switch' );
		if ( sw ) {
			var pt = sw.closest( '.fw-pt' );
			if ( pt ) { setState( pt, ! pt.classList.contains( 'is-yearly' ) ); }
			return;
		}
		var lbl = e.target.closest( '.fw-pt__billing-label' );
		if ( lbl ) {
			var pt2 = lbl.closest( '.fw-pt' );
			if ( pt2 ) { setState( pt2, lbl.getAttribute( 'data-pt-billing' ) === 'yearly' ); }
		}
	} );

	// Keyboard: space / enter on the focused switch.
	document.addEventListener( 'keydown', function ( e ) {
		if ( e.key !== ' ' && e.key !== 'Enter' ) { return; }
		var sw = e.target.closest && e.target.closest( '.fw-pt__billing-switch' );
		if ( ! sw ) { return; }
		e.preventDefault();
		var pt = sw.closest( '.fw-pt' );
		if ( pt ) { setState( pt, ! pt.classList.contains( 'is-yearly' ) ); }
	} );
}() );
