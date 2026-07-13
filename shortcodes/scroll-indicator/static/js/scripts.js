/**
 * Scroll Indicator — click behaviour. An on-page anchor (#id) smooth-scrolls to that
 * element; a cue with no target (data-scroll-down) scrolls the viewport down ~90%.
 * Dependency-free, delegated (works for cues added after load).
 */
( function () {
	'use strict';
	document.addEventListener( 'click', function ( e ) {
		var cue = e.target.closest ? e.target.closest( '.sc-scroll-cue' ) : null;
		if ( ! cue ) { return; }

		var href = cue.getAttribute( 'href' ) || '';
		// On-page anchor → smooth-scroll to it.
		if ( href.charAt( 0 ) === '#' && href.length > 1 ) {
			var target = null;
			try { target = document.querySelector( href ); } catch ( err ) { target = null; }
			if ( target ) {
				e.preventDefault();
				target.scrollIntoView( { behavior: 'smooth', block: 'start' } );
			}
			return;
		}
		// No real target → scroll down one screen.
		if ( cue.hasAttribute( 'data-scroll-down' ) || href === '#' || href === '' ) {
			e.preventDefault();
			window.scrollBy( { top: Math.round( window.innerHeight * 0.9 ), left: 0, behavior: 'smooth' } );
		}
	}, false );
}() );
