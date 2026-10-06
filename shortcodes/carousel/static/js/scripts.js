/* Unyson+ Carousel — mount Splide on each carousel. Options come from the element's
   data-splide JSON (built in view.php), which Splide reads natively on mount. */
( function () {
	'use strict';

	function init( scope ) {
		if ( typeof window.Splide === 'undefined' ) {
			return;
		}
		var els = [].slice.call( ( scope || document ).querySelectorAll( '.fw-carousel .splide' ) );
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
		document.addEventListener( 'DOMContentLoaded', function () { init(); } );
	}

	/* Re-init hook for surfaces that insert this element's markup after page load (the
	   block editor's preview, the Elementor editor's re-renders): they call every
	   window.fwShortcodeInit entry with the document or element holding the new markup.
	   init() skips what it has already initialised, so repeated calls are safe. */
	window.fwShortcodeInit = window.fwShortcodeInit || [];
	window.fwShortcodeInit.push( init );
} )();
