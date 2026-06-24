/* Unyson+ Testimonials — "Thumbnail Nav Slider" design.
   Mounts a main quote slider + an avatar-thumbnail nav slider and syncs them.
   Configs come from the element's data-thumbnav-main / data-thumbnav-nav JSON
   (built in designs/thumbnav.php). Shares the bundled Splide library. */
( function () {
	'use strict';

	function parse( el, attr ) {
		try {
			return JSON.parse( el.getAttribute( attr ) || '{}' );
		} catch ( e ) {
			return {};
		}
	}

	function init() {
		if ( typeof window.Splide === 'undefined' ) {
			return;
		}
		var roots = [].slice.call( document.querySelectorAll( '.ts-thumbnav' ) );
		roots.forEach( function ( root ) {
			if ( root.classList.contains( 'is-initialized' ) ) {
				return;
			}
			var mainEl = root.querySelector( '.ts-thumbnav__main' );
			var navEl  = root.querySelector( '.ts-thumbnav__nav' );
			if ( ! mainEl || ! navEl ) {
				return;
			}
			try {
				var main = new window.Splide( mainEl, parse( root, 'data-thumbnav-main' ) );
				var nav  = new window.Splide( navEl, parse( root, 'data-thumbnav-nav' ) );
				main.sync( nav );
				main.mount();
				nav.mount();
				root.classList.add( 'is-initialized' );
			} catch ( e ) { /* ignore a malformed slider rather than break the page */ }
		} );
	}

	if ( document.readyState !== 'loading' ) {
		init();
	} else {
		document.addEventListener( 'DOMContentLoaded', init );
	}
} )();
