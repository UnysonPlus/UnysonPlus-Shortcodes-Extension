/* Unyson+ Tabs — self-contained tab switching (no Bootstrap / jQuery).
 *
 * The view renders each tab trigger with `data-fw-toggle="tab"` and
 * `data-fw-target="#paneId"`. We delegate clicks (and basic arrow-key nav)
 * off the document so it also works for tabs injected after load. The active
 * pane gets `.active` (display) and, when it opts into fade, `.show` (opacity).
 */
( function () {
	'use strict';

	function panesContainer( pane ) {
		return pane && pane.parentNode ? pane.parentNode : null;
	}

	function activate( trigger ) {
		var sel = trigger.getAttribute( 'data-fw-target' );
		if ( ! sel ) {
			return;
		}
		var pane = document.querySelector( sel );
		if ( ! pane ) {
			return;
		}

		// Deactivate sibling triggers within the same tablist / nav.
		var nav = trigger.closest( '.nav, [role="tablist"]' );
		if ( nav ) {
			nav.querySelectorAll( '.nav-link' ).forEach( function ( link ) {
				link.classList.remove( 'active' );
				link.setAttribute( 'aria-selected', 'false' );
				link.setAttribute( 'tabindex', '-1' );
			} );
		}
		trigger.classList.add( 'active' );
		trigger.setAttribute( 'aria-selected', 'true' );
		trigger.setAttribute( 'tabindex', '0' );

		// Deactivate sibling panes, then activate the target.
		var content = panesContainer( pane );
		if ( content ) {
			[].slice.call( content.children ).forEach( function ( node ) {
				if ( node.classList && node.classList.contains( 'tab-pane' ) ) {
					node.classList.remove( 'active', 'show' );
				}
			} );
		}
		pane.classList.add( 'active' );
		if ( pane.classList.contains( 'fade' ) ) {
			window.requestAnimationFrame( function () {
				pane.classList.add( 'show' );
			} );
		} else {
			pane.classList.add( 'show' );
		}
	}

	function onClick( e ) {
		var trigger = e.target.closest ? e.target.closest( '[data-fw-toggle="tab"]' ) : null;
		if ( ! trigger || ! trigger.closest( '.tabs-container' ) ) {
			return;
		}
		e.preventDefault();
		activate( trigger );
	}

	function onKeydown( e ) {
		var current = e.target.closest ? e.target.closest( '[data-fw-toggle="tab"]' ) : null;
		if ( ! current || ! current.closest( '.tabs-container' ) ) {
			return;
		}
		var nav = current.closest( '.nav, [role="tablist"]' );
		if ( ! nav ) {
			return;
		}
		var links = [].slice.call( nav.querySelectorAll( '.nav-link' ) );
		var idx   = links.indexOf( current );
		if ( idx === -1 ) {
			return;
		}

		var next = null;
		switch ( e.key ) {
			case 'ArrowRight':
			case 'ArrowDown':
				next = links[ ( idx + 1 ) % links.length ];
				break;
			case 'ArrowLeft':
			case 'ArrowUp':
				next = links[ ( idx - 1 + links.length ) % links.length ];
				break;
			case 'Home':
				next = links[ 0 ];
				break;
			case 'End':
				next = links[ links.length - 1 ];
				break;
			default:
				return;
		}
		if ( next ) {
			e.preventDefault();
			activate( next );
			next.focus();
		}
	}

	document.addEventListener( 'click', onClick );
	document.addEventListener( 'keydown', onKeydown );
} )();
