/* Unyson+ Testimonials — mount Splide on each testimonials carousel. Options
   come from the element's data-splide JSON (built in view.php), which Splide
   reads natively on mount. Shares the bundled Splide library with the Carousel
   shortcode (no Bootstrap / caroufredsel). */
( function () {
	'use strict';

	function init() {
		if ( typeof window.Splide === 'undefined' ) {
			return;
		}
		var els = [].slice.call( document.querySelectorAll( '.testimonials-splide' ) );
		els.forEach( function ( el ) {
			if ( el.classList.contains( 'is-initialized' ) || el.splide ) {
				return;
			}
			try {
				new window.Splide( el ).mount();
			} catch ( e ) { /* ignore a malformed slider rather than break the page */ }
		} );
	}

	if ( document.readyState !== 'loading' ) {
		init();
	} else {
		document.addEventListener( 'DOMContentLoaded', init );
	}
} )();
