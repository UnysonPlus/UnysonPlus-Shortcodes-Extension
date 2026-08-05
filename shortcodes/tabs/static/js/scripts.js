/* Unyson+ Tabs — self-contained tab switching (no Bootstrap / jQuery).
 *
 * The view renders each tab trigger with `data-fw-toggle="tab"` +
 * `data-fw-target="#paneId"`, WAI-ARIA roles, and a server-rendered roving
 * tabindex. Delegated off the document so it also works for injected tabs.
 * Supports: click / hover activation, orientation-aware arrow keys, manual vs
 * automatic keyboard activation, disabled tabs, auto-rotate, and a mobile
 * collapse-to-accordion mode (data-fw-mobile="accordion").
 */
( function () {
	'use strict';

	var MOBILE = '(max-width: 767.98px)';

	function panesContainer( pane ) { return pane && pane.parentNode ? pane.parentNode : null; }

	function tablistOf( el ) { return el.closest( '.nav, [role="tablist"]' ); }

	function links( nav ) { return [].slice.call( nav.querySelectorAll( '.nav-link' ) ); }

	function isVertical( nav ) {
		return nav.getAttribute( 'aria-orientation' ) === 'vertical';
	}

	function containerOf( el ) { return el.closest( '.tabs-container' ); }

	// Slide the underline indicator under the active tab (horizontal underline design only).
	function positionIndicator( c ) {
		if ( ! c ) { return; }
		var nav = c.querySelector( '.nav-underline' );
		if ( ! nav || nav.classList.contains( 'flex-column' ) ) { return; }
		var ind = nav.querySelector( '.nav-indicator' );
		if ( ! ind ) {
			ind = document.createElement( 'span' );
			ind.className = 'nav-indicator';
			ind.setAttribute( 'aria-hidden', 'true' );
			nav.appendChild( ind );
			nav.classList.add( 'has-indicator' ); // suppresses the per-tab border fallback (CSS)
		}
		var active = nav.querySelector( '.nav-link.active' );
		if ( ! active ) { ind.style.opacity = '0'; return; }
		ind.style.opacity = '1';
		ind.style.width = active.offsetWidth + 'px';
		ind.style.transform = 'translateX(' + active.offsetLeft + 'px)';
	}

	// Point the popover caret at the ACTIVE tab (else the CSS default points at the first).
	function positionPopoverCaret( c ) {
		if ( ! c || ! c.classList.contains( 'tabs--design-popover' ) ) { return; }
		var content = c.querySelector( '.tab-content' );
		var active  = c.querySelector( '.nav-link.active' );
		if ( ! content || ! active ) { return; }
		var cr = content.getBoundingClientRect();
		var ar = active.getBoundingClientRect();
		if ( ! cr.width ) { return; }
		var x = ( ar.left + ar.width / 2 ) - cr.left - 6; // 6 = half the 12px caret
		x = Math.max( 8, Math.min( x, cr.width - 20 ) );  // keep within the card
		content.style.setProperty( '--fw-caret-x', x + 'px' );
	}

	// Persist the active tab (remember) + reflect it in the URL hash (deep-link).
	// Runs only on USER activation, not autoplay / restore (silent).
	function sideEffects( trigger ) {
		var c = containerOf( trigger );
		if ( ! c ) { return; }
		if ( c.getAttribute( 'data-fw-deeplink' ) && window.history && window.history.replaceState ) {
			var sel = trigger.getAttribute( 'data-fw-target' );
			if ( sel ) { window.history.replaceState( null, '', sel ); }
		}
		if ( c.getAttribute( 'data-fw-remember' ) && c.id && window.localStorage ) {
			var nav = tablistOf( trigger );
			if ( nav ) {
				try { window.localStorage.setItem( 'fwTabs:' + c.id, String( links( nav ).indexOf( trigger ) ) ); } catch ( e ) {}
			}
		}
	}

	function triggerForHash( c, hash ) {
		var found = null;
		[].slice.call( c.querySelectorAll( '[data-fw-toggle="tab"]' ) ).forEach( function ( t ) {
			if ( t.getAttribute( 'data-fw-target' ) === '#' + hash ) { found = t; }
		} );
		return found;
	}

	// On load: open the tab named in the URL #hash (deep-link), else the remembered tab.
	function restoreState() {
		var hash = window.location.hash ? window.location.hash.slice( 1 ) : '';
		if ( hash ) {
			var pane = document.getElementById( hash );
			if ( pane && pane.classList && pane.classList.contains( 'tab-pane' ) ) {
				var dc = pane.closest( '.tabs-container[data-fw-deeplink]' );
				if ( dc ) { var t = triggerForHash( dc, hash ); if ( t ) { activate( t, true ); } }
			}
		}
		[].slice.call( document.querySelectorAll( '.tabs-container[data-fw-remember]' ) ).forEach( function ( c ) {
			if ( ! c.id || ! window.localStorage ) { return; }
			if ( hash && document.getElementById( hash ) && c.contains( document.getElementById( hash ) ) ) { return; } // hash wins
			var v = null;
			try { v = window.localStorage.getItem( 'fwTabs:' + c.id ); } catch ( e ) {}
			if ( v === null ) { return; }
			var nav = c.querySelector( '[role="tablist"], .nav' );
			if ( ! nav ) { return; }
			var ls = links( nav ), idx = parseInt( v, 10 );
			if ( ls[ idx ] && ! ls[ idx ].classList.contains( 'disabled' ) ) { activate( ls[ idx ], true ); }
		} );
	}

	// Move focus + roving tabindex to a tab WITHOUT changing the panel (manual mode).
	function focusTab( trigger ) {
		var nav = tablistOf( trigger );
		if ( nav ) {
			links( nav ).forEach( function ( l ) { l.setAttribute( 'tabindex', '-1' ); } );
		}
		trigger.setAttribute( 'tabindex', '0' );
		trigger.focus();
	}

	function activate( trigger, silent ) {
		if ( trigger.classList.contains( 'disabled' ) ) { return; }
		var sel = trigger.getAttribute( 'data-fw-target' );
		if ( ! sel ) { return; }
		var pane = document.querySelector( sel );
		if ( ! pane ) { return; }

		var nav = tablistOf( trigger );
		if ( nav ) {
			links( nav ).forEach( function ( link ) {
				link.classList.remove( 'active' );
				link.setAttribute( 'aria-selected', 'false' );
				link.setAttribute( 'tabindex', '-1' );
			} );
		}
		trigger.classList.add( 'active' );
		trigger.setAttribute( 'aria-selected', 'true' );
		trigger.setAttribute( 'tabindex', '0' );

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
			window.requestAnimationFrame( function () { pane.classList.add( 'show' ); } );
		} else {
			pane.classList.add( 'show' );
		}

		positionIndicator( containerOf( trigger ) );
		positionPopoverCaret( containerOf( trigger ) );
		if ( ! silent ) { sideEffects( trigger ); }
	}

	function onClick( e ) {
		var trigger = e.target.closest ? e.target.closest( '[data-fw-toggle="tab"]' ) : null;
		if ( ! trigger || ! trigger.closest( '.tabs-container' ) ) { return; }
		e.preventDefault();
		activate( trigger );
	}

	function onKeydown( e ) {
		var current = e.target.closest ? e.target.closest( '[data-fw-toggle="tab"]' ) : null;
		var container = current && current.closest( '.tabs-container' );
		if ( ! current || ! container ) { return; }
		var nav = tablistOf( current );
		if ( ! nav ) { return; }
		var all = links( nav );
		// Enabled tabs only for arrow traversal.
		var enabled = all.filter( function ( l ) { return ! l.classList.contains( 'disabled' ); } );
		var idx = enabled.indexOf( current );
		if ( idx === -1 ) { idx = 0; }

		var vertical = isVertical( nav );
		var nextKey  = vertical ? 'ArrowDown' : 'ArrowRight';
		var prevKey  = vertical ? 'ArrowUp' : 'ArrowLeft';
		var manual   = container.getAttribute( 'data-fw-activation' ) === 'manual';
		var next = null;

		if ( e.key === nextKey ) {
			next = enabled[ ( idx + 1 ) % enabled.length ];
		} else if ( e.key === prevKey ) {
			next = enabled[ ( idx - 1 + enabled.length ) % enabled.length ];
		} else if ( e.key === 'Home' ) {
			next = enabled[ 0 ];
		} else if ( e.key === 'End' ) {
			next = enabled[ enabled.length - 1 ];
		} else if ( ( e.key === 'Enter' || e.key === ' ' || e.key === 'Spacebar' ) && manual ) {
			e.preventDefault();
			activate( current );
			return;
		} else {
			return;
		}

		if ( next ) {
			e.preventDefault();
			if ( manual ) { focusTab( next ); }   // move focus only; Enter/Space activates
			else { activate( next ); next.focus(); } // automatic: activate on focus
		}
	}

	// Hover activation — container opts in via data-fw-activate="hover".
	function onOver( e ) {
		var trigger = e.target.closest ? e.target.closest( '[data-fw-toggle="tab"]' ) : null;
		if ( ! trigger ) { return; }
		var container = trigger.closest( '.tabs-container' );
		if ( ! container || container.getAttribute( 'data-fw-activate' ) !== 'hover' ) { return; }
		if ( trigger.classList.contains( 'active' ) || trigger.classList.contains( 'disabled' ) ) { return; }
		activate( trigger );
	}

	/* --- Mobile collapse-to-accordion (data-fw-mobile="accordion") ---------- */
	function applyAccordion() {
		var mobile = window.matchMedia && window.matchMedia( MOBILE ).matches;
		[].slice.call( document.querySelectorAll( '.tabs-container[data-fw-mobile="accordion"]' ) ).forEach( function ( c ) {
			var nav     = c.querySelector( '[role="tablist"], .nav' );
			var content = c.querySelector( '.tab-content' );
			if ( ! nav || ! content ) { return; }
			if ( mobile && ! c.__fwAcc ) {
				c.__fwAcc = true;
				c.classList.add( 'tabs--is-accordion' );
				// Nest each pane INSIDE its trigger's <li> (after the button), forming
				// button→pane accordion pairs (li accepts flow content).
				links( nav ).forEach( function ( link ) {
					var sel  = link.getAttribute( 'data-fw-target' );
					var pane = sel && document.querySelector( sel );
					var li   = link.closest( '.nav-item' ) || link;
					if ( pane && li ) { li.appendChild( pane ); }
				} );
			} else if ( ! mobile && c.__fwAcc ) {
				c.__fwAcc = false;
				c.classList.remove( 'tabs--is-accordion' );
				[].slice.call( nav.querySelectorAll( '.tab-pane' ) ).forEach( function ( pane ) { content.appendChild( pane ); } );
			}
		} );
	}

	/* --- Auto-rotate -------------------------------------------------------- */
	function setupAutoplay( container ) {
		var ms = parseInt( container.getAttribute( 'data-fw-autoplay' ), 10 );
		if ( ! ms || ms < 500 ) { return; }
		if ( window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) { return; }
		var nav = container.querySelector( '.nav, [role="tablist"]' );
		if ( ! nav ) { return; }
		var ls = links( nav );
		if ( ls.length < 2 ) { return; }
		var paused = false;
		function tick() {
			if ( paused ) { return; }
			var cur = nav.querySelector( '.nav-link.active' ) || ls[ 0 ];
			var idx = ls.indexOf( cur );
			activate( ls[ ( idx + 1 ) % ls.length ], true );
		}
		container.addEventListener( 'mouseenter', function () { paused = true; } );
		container.addEventListener( 'mouseleave', function () { paused = false; } );
		container.addEventListener( 'focusin', function () { paused = true; } );
		container.addEventListener( 'focusout', function () { paused = false; } );
		window.setInterval( tick, ms );
	}

	function allContainers() { return [].slice.call( document.querySelectorAll( '.tabs-container' ) ); }

	function init() {
		[].slice.call( document.querySelectorAll( '.tabs-container[data-fw-autoplay]' ) ).forEach( function ( c ) {
			if ( c.__fwTabsAuto ) { return; }
			c.__fwTabsAuto = true;
			setupAutoplay( c );
		} );
		applyAccordion();
		restoreState();
		allContainers().forEach( positionIndicator ); // place sliding underline bars
		allContainers().forEach( positionPopoverCaret );
	}

	document.addEventListener( 'click', onClick );
	document.addEventListener( 'keydown', onKeydown );
	document.addEventListener( 'mouseover', onOver );

	var rT;
	window.addEventListener( 'resize', function () {
		window.clearTimeout( rT );
		rT = window.setTimeout( function () {
			applyAccordion();
			allContainers().forEach( positionIndicator );
			allContainers().forEach( positionPopoverCaret );
		}, 150 );
	} );

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
