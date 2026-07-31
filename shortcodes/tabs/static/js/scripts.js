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

	// Hover activation — when the container opts in via data-fw-activate="hover".
	function onOver( e ) {
		var trigger = e.target.closest ? e.target.closest( '[data-fw-toggle="tab"]' ) : null;
		if ( ! trigger ) {
			return;
		}
		var container = trigger.closest( '.tabs-container' );
		if ( ! container || container.getAttribute( 'data-fw-activate' ) !== 'hover' ) {
			return;
		}
		if ( trigger.classList.contains( 'active' ) ) {
			return;
		}
		activate( trigger );
	}

	// Auto-rotate — cycle a container's tabs on data-fw-autoplay="<ms>". Pauses
	// while hovered/focused; skipped under prefers-reduced-motion.
	function setupAutoplay( container ) {
		var ms = parseInt( container.getAttribute( 'data-fw-autoplay' ), 10 );
		if ( ! ms || ms < 500 ) {
			return;
		}
		if ( window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) {
			return;
		}
		var nav = container.querySelector( '.nav, [role="tablist"]' );
		if ( ! nav ) {
			return;
		}
		var links = [].slice.call( nav.querySelectorAll( '.nav-link' ) );
		if ( links.length < 2 ) {
			return;
		}
		var paused = false;
		function tick() {
			if ( paused ) {
				return;
			}
			var cur = nav.querySelector( '.nav-link.active' ) || links[ 0 ];
			var idx = links.indexOf( cur );
			activate( links[ ( idx + 1 ) % links.length ] );
		}
		container.addEventListener( 'mouseenter', function () { paused = true; } );
		container.addEventListener( 'mouseleave', function () { paused = false; } );
		container.addEventListener( 'focusin', function () { paused = true; } );
		container.addEventListener( 'focusout', function () { paused = false; } );
		window.setInterval( tick, ms );
	}

	function initAutoplay() {
		[].slice.call( document.querySelectorAll( '.tabs-container[data-fw-autoplay]' ) ).forEach( function ( c ) {
			if ( c.__fwTabsAuto ) {
				return;
			}
			c.__fwTabsAuto = true;
			setupAutoplay( c );
		} );
	}

	document.addEventListener( 'click', onClick );
	document.addEventListener( 'keydown', onKeydown );
	document.addEventListener( 'mouseover', onOver );

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', initAutoplay );
	} else {
		initAutoplay();
	}
} )();
