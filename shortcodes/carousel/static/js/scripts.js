/* Unyson+ Carousel — mount Splide on each carousel. Options come from the element's
   data-splide JSON (built in view.php), which Splide reads natively on mount. */
( function () {
	'use strict';

	/* Keep autoplay from running where it is unwanted or wasteful.
	 *
	 * An autoplaying carousel does not just animate: because slides after the first are
	 * lazy-loaded (`lazyLoad: 'nearby'` in view.php), every advance FETCHES the next
	 * slide's image. A five-slide hero therefore keeps pulling full-bleed photos for
	 * minutes, including long after the visitor has scrolled past it — measured on a real
	 * site at 1.6 MB of hero slides against 260 KB for every other image on the page,
	 * still arriving 10 seconds in while the reader was several screens down looking at
	 * content images that were queued behind them.
	 *
	 * Two rules, both strictly better than always-on:
	 *  - Off-screen carousels do not advance. Nobody is watching, so the animation is
	 *    invisible and the downloads are pure waste. Advancing resumes on return.
	 *  - A visitor who asked for reduced motion gets no autoplay at all (WCAG 2.2.2:
	 *    moving content should be stoppable). They can still use arrows, dots and drag.
	 *
	 * Everything degrades safely: without IntersectionObserver the carousel behaves
	 * exactly as before, and a carousel that was never autoplaying is left alone.
	 */
	function governAutoplay( splide, el ) {
		var auto = splide.Components && splide.Components.Autoplay;
		if ( ! auto || ! splide.options.autoplay ) {
			return;
		}

		var visible = true;
		var reduce  = window.matchMedia ? window.matchMedia( '( prefers-reduced-motion: reduce )' ) : null;
		var allowed = function () {
			return ! ( reduce && reduce.matches );
		};
		var apply = function () {
			if ( visible && allowed() ) {
				auto.play();
			} else {
				auto.pause();
			}
		};

		apply();

		if ( reduce && reduce.addEventListener ) {
			reduce.addEventListener( 'change', apply );
		}

		if ( ! window.IntersectionObserver ) {
			return;
		}
		new window.IntersectionObserver( function ( entries ) {
			for ( var i = 0; i < entries.length; i++ ) {
				visible = entries[ i ].isIntersecting;
			}
			apply();
		} ).observe( el );
	}

	/* Load slide images just before they are shown.
	 *
	 * Slides after the first are rendered by fw_image_tag() with their src/srcset parked
	 * in data-fw-lazy-src / data-fw-lazy-srcset, on the <img> AND on every <source> of a
	 * per-device <picture>. Splide's own lazy loader only knows plain <img> tags, and a
	 * browser picks a <picture>'s source when the <img> starts loading, so the sources
	 * must be restored FIRST, then the <img>. The visible slides load at once, the next
	 * one after the page has loaded, the others when the slider moves or is touched;
	 * clones of a looping slider are loaded with their original.
	 */
	function loadPicture( slide ) {
		var nodes = slide.querySelectorAll( 'source[data-fw-lazy-srcset], img[data-fw-lazy-srcset], img[data-fw-lazy-src]' );
		for ( var i = 0; i < nodes.length; i++ ) {
			var n = nodes[ i ];
			if ( n.tagName === 'SOURCE' ) {
				n.setAttribute( 'srcset', n.getAttribute( 'data-fw-lazy-srcset' ) );
				n.removeAttribute( 'data-fw-lazy-srcset' );
			}
		}
		for ( var j = 0; j < nodes.length; j++ ) {
			var m = nodes[ j ];
			if ( m.tagName !== 'IMG' ) {
				continue;
			}
			if ( m.hasAttribute( 'data-fw-lazy-srcset' ) ) {
				m.setAttribute( 'srcset', m.getAttribute( 'data-fw-lazy-srcset' ) );
				m.removeAttribute( 'data-fw-lazy-srcset' );
			}
			if ( m.hasAttribute( 'data-fw-lazy-src' ) ) {
				m.setAttribute( 'src', m.getAttribute( 'data-fw-lazy-src' ) );
				m.removeAttribute( 'data-fw-lazy-src' );
			}
			m.removeAttribute( 'loading' ); // it is about to be shown
		}
	}

	function lazyPictures( splide, el ) {
		var Slides = splide.Components && splide.Components.Slides;
		if ( ! Slides ) {
			return;
		}
		var total = splide.length || 1;
		var per   = ( splide.options && splide.options.perPage ) || 1;
		// Load the slides within `ahead` of `index` (both directions when `back`).
		var load = function ( index, ahead, back ) {
			Slides.forEach( function ( Slide ) {
				var i = Slide.slideIndex > -1 ? Slide.slideIndex : Slide.index;
				var fwd = ( ( i - index ) % total + total ) % total; // steps forward, wrapping
				var bwd = ( total - fwd ) % total;                   // steps back, wrapping
				if ( fwd <= ahead || ( back && bwd <= ahead ) ) {
					loadPicture( Slide.slide );
				}
			} );
		};
		// The slides on screen now load at once (normally already in the HTML).
		load( splide.index || 0, per - 1, false );
		// The rest wait. Fetching the neighbours as soon as the slider mounted made
		// them compete with the first screen for bandwidth - on a phone, two or three
		// ~120 KB photos downloading while the hero itself was still arriving. Now
		// only the next one is fetched, once the page has loaded and the browser is
		// idle, so autoplay's first move finds it ready...
		var warmNext = function () {
			var go = function () { load( splide.index || 0, per, false ); };
			if ( window.requestIdleCallback ) { window.requestIdleCallback( go, { timeout: 3000 } ); } else { setTimeout( go, 600 ); }
		};
		if ( document.readyState === 'complete' ) { warmNext(); } else { window.addEventListener( 'load', warmNext, { once: true } ); }
		// ...a slider someone reaches for (touch, mouse, keyboard) gets both
		// neighbours before the arrow or swipe lands...
		var wake = function () {
			load( splide.index || 0, per, true );
			[ 'pointerdown', 'touchstart', 'focusin', 'mouseenter' ].forEach( function ( t ) { el.removeEventListener( t, wake ); } );
		};
		[ 'pointerdown', 'touchstart', 'focusin', 'mouseenter' ].forEach( function ( t ) { el.addEventListener( t, wake, { passive: true } ); } );
		// ...and every move loads the slide it goes to plus the ones beside it.
		splide.on( 'move', function ( newIndex ) { load( newIndex, per, true ); } );
	}

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
				var splide = new window.Splide( el );
				splide.mount();
				lazyPictures( splide, el );
				governAutoplay( splide, el );
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
