/* Unyson+ Testimonials — mount Splide on each testimonials carousel. Options
   come from the element's data-splide JSON (built in view.php), which Splide
   reads natively on mount. Shares the bundled Splide library with the Carousel
   shortcode (no Bootstrap / caroufredsel). */
( function () {
	'use strict';

	function init( scope ) {
		if ( typeof window.Splide === 'undefined' ) {
			return;
		}
		var els = [].slice.call( ( scope || document ).querySelectorAll( '.testimonials-splide' ) );
		els.forEach( function ( el ) {
			// The Thumbnail Nav design mounts its own main slider (designs/thumbnav.js),
			// synced to its thumbnails; mounting it here as well would double it.
			if ( el.classList.contains( 'is-initialized' ) || el.splide || el.closest( '.ts-thumbnav' ) ) {
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

/* Read More (Card tab → Read More) — every design.
 *
 * The element prints its settings + (for the popup) every review as JSON in a
 * <script class="ts-more-data"> right after its wrapper. CSS already clamps the quotes
 * (data-ts-more + --ts-more-lines), so this only:
 *   - marks quotes that fit (.ts-more-fits, no fade) and adds a "Read more" button after
 *     the ones that are cut off (re-checked on resize and once web fonts have loaded);
 *   - 'expand': toggles the quote open in its card;
 *   - 'popup': opens one shared panel listing every review in full, scrolled to the one
 *     clicked. Carousels and marquees duplicate slides, so a quote is matched to its review
 *     by text, not by position.
 */
( function () {
	'use strict';

	var norm = function ( s ) { return String( s || '' ).replace( /\s+/g, ' ' ).trim(); };
	var esc  = function ( s ) { var d = document.createElement( 'div' ); d.textContent = s == null ? '' : String( s ); return d.innerHTML; };
	// Five stars filled to the exact rating (4.5 shows four and a half): CSS paints --ts-rv-fill.
	var stars = function ( n ) { return '<span class="ts-rv__fill" style="--ts-rv-fill:' + ( Math.max( 0, Math.min( 5, Number( n ) || 0 ) ) * 20 ) + '%">★★★★★</span>'; };
	var hasBtn = function ( q ) { var n = q.nextElementSibling; return !! ( n && n.classList && n.classList.contains( 'ts-more-btn' ) ); };

	var modal = null, list = null, lastFocus = null;

	function closeModal() {
		if ( ! modal || modal.hidden ) { return; }
		modal.classList.remove( 'is-open' );
		document.documentElement.classList.remove( 'ts-rv-lock' );
		if ( window.lenis && window.lenis.start ) { try { window.lenis.start(); } catch ( e ) {} }
		setTimeout( function () { modal.hidden = true; }, 250 );
		if ( lastFocus && lastFocus.focus ) { lastFocus.focus(); }
	}

	function openModal( data, index ) {
		var p = data.popup;
		if ( modal ) { modal.remove(); }
		var head = '<div class="ts-rv__head">' + ( p.logo ? '<img class="ts-rv__logo" src="' + esc( p.logo ) + '" alt="">' : '' )
			+ '<div><h2 class="ts-rv__title" id="ts-rv-title">' + esc( p.title ) + '</h2>'
			+ ( p.rating ? '<p class="ts-rv__meta"><b>' + Number( p.rating ).toFixed( 1 ) + '</b><span class="ts-rv__stars" aria-hidden="true">' + stars( p.rating ) + '</span><span>(' + esc( p.count_label ) + ')</span></p>' : '' )
			+ '</div></div>';
		var items = p.items.map( function ( it, i ) {
			var av = it.avatar ? '<img src="' + esc( it.avatar ) + '" alt="" loading="lazy">' : esc( ( it.name || '?' ).charAt( 0 ) );
			return '<article class="ts-rv__item" data-i="' + i + '">'
				+ '<div class="ts-rv__who"><span class="ts-rv__avatar">' + av + '</span><div><p class="ts-rv__name">' + esc( it.name ) + '</p>'
				+ ( it.sub ? '<p class="ts-rv__sub">' + esc( it.sub ) + '</p>' : '' ) + '</div></div>'
				+ ( it.rating ? '<div class="ts-rv__stars" role="img" aria-label="' + esc( 'Rated ' + it.rating + ' out of 5' ) + '">' + stars( it.rating ) + '</div>' : '' )
				+ '<div class="ts-rv__text">' + it.html + '</div></article>'; // html is sanitised server-side
		} ).join( '' );
		modal = document.createElement( 'div' );
		modal.className = 'ts-rv';
		modal.hidden = true;
		modal.innerHTML = '<div class="ts-rv__panel" role="dialog" aria-modal="true" aria-labelledby="ts-rv-title" data-lenis-prevent>'
			+ head
			+ '<button type="button" class="ts-rv__close" aria-label="' + esc( data.close ) + '"><svg width="16" height="16" viewBox="0 0 16 16" aria-hidden="true"><path d="M3 3l10 10M13 3L3 13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg></button>'
			+ '<div class="ts-rv__list" data-lenis-prevent>' + items + '</div>'
			+ ( p.link ? '<div class="ts-rv__foot"><a href="' + esc( p.link.url ) + '" target="_blank" rel="noopener noreferrer">' + esc( p.link.label ) + '</a></div>' : '' )
			+ '</div>';
		document.body.appendChild( modal );
		list = modal.querySelector( '.ts-rv__list' );
		modal.addEventListener( 'click', function ( e ) { if ( e.target === modal ) { closeModal(); } } );
		modal.querySelector( '.ts-rv__close' ).addEventListener( 'click', closeModal );
		modal.addEventListener( 'keydown', function ( e ) {
			if ( e.key === 'Escape' ) { e.preventDefault(); closeModal(); return; }
			if ( e.key !== 'Tab' ) { return; }
			var f = modal.querySelectorAll( 'button, a[href]' );
			var first = f[ 0 ], last = f[ f.length - 1 ];
			if ( e.shiftKey && document.activeElement === first ) { e.preventDefault(); last.focus(); }
			else if ( ! e.shiftKey && document.activeElement === last ) { e.preventDefault(); first.focus(); }
		} );

		lastFocus = document.activeElement;
		modal.hidden = false;
		document.documentElement.classList.add( 'ts-rv-lock' );
		if ( window.lenis && window.lenis.stop ) { try { window.lenis.stop(); } catch ( e ) {} }
		requestAnimationFrame( function () {
			modal.classList.add( 'is-open' );
			var t = list.querySelector( '[data-i="' + index + '"]' );
			if ( t ) {
				list.scrollTop = Math.max( 0, t.offsetTop - list.offsetTop - 6 );
				t.classList.add( 'is-target' );
				setTimeout( function () { t.classList.remove( 'is-target' ); }, 1600 );
			}
			modal.querySelector( '.ts-rv__close' ).focus();
		} );
	}

	function indexOf( data, quote ) {
		if ( ! data.popup ) { return 0; }
		var text = norm( quote.textContent );
		for ( var i = 0; i < data.popup.items.length; i++ ) {
			var key = norm( data.popup.items[ i ].key ).slice( 0, 40 );
			if ( key && text.slice( 0, key.length ) === key ) { return i; }
		}
		return 0;
	}

	/* Decide which quotes are long enough to need a "Read more" button.
	 *
	 * Done in three passes on purpose. The straightforward version - for each quote: drop the
	 * class, read its height, put the class back, insert a button - interleaves writes and reads,
	 * and a geometry read that follows a style write forces the browser to lay the page out again
	 * before it can answer. That is one full layout PER QUOTE; measured on a real page under 4x CPU
	 * throttling it was 8.5ms, the single largest item in that page's forced-layout budget.
	 *
	 * So: write all the class removals first, then take all the measurements together (only the
	 * first of them pays for a layout - nothing is written in between, so the rest are free), then
	 * make all the changes. One layout instead of N, and the end result is identical.
	 */
	function apply( wrap, data ) {
		var quotes = [];
		// Pass 1 - writes only. Measure with the clamp on: a quote marked "fits" before its slider
		// laid out (or at another width) must be able to become "cut" again.
		wrap.querySelectorAll( '.testimonial-quote' ).forEach( function ( q ) {
			if ( q.classList.contains( 'ts-more-open' ) ) { return; }
			q.classList.remove( 'ts-more-fits' );
			quotes.push( q );
		} );
		// Pass 2 - reads only. Nothing is written in between, so the whole set costs one layout.
		var measured = quotes.map( function ( q ) {
			var ch = q.clientHeight;
			return { q: q, hidden: ch === 0, cut: q.scrollHeight > ch + 2 };
		} );
		// Pass 3 - writes only.
		measured.forEach( function ( r ) {
			if ( r.hidden ) { return; } // hidden: re-checked later
			var q = r.q, cut = r.cut;
			q.classList.toggle( 'ts-more-fits', ! cut );
			if ( ! cut ) { if ( hasBtn( q ) ) { q.nextElementSibling.remove(); } return; }
			if ( hasBtn( q ) ) { return; }
			var b = document.createElement( 'button' );
			b.type = 'button';
			b.className = 'ts-more-btn';
			b.textContent = data.more;
			if ( data.mode === 'popup' ) {
				b.setAttribute( 'aria-haspopup', 'dialog' );
				b.addEventListener( 'click', function () { openModal( data, indexOf( data, q ) ); } );
			} else {
				b.setAttribute( 'aria-expanded', 'false' );
				b.addEventListener( 'click', function () {
					var open = q.classList.toggle( 'ts-more-open' );
					b.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
					b.textContent = open ? data.less : data.more;
				} );
			}
			q.insertAdjacentElement( 'afterend', b );
		} );
	}

	function initMore( scope ) {
		( scope || document ).querySelectorAll( 'script.ts-more-data' ).forEach( function ( s ) {
			var wrap = s.previousElementSibling;
			while ( wrap && ! wrap.hasAttribute( 'data-ts-more' ) ) { wrap = wrap.previousElementSibling; }
			if ( ! wrap ) { return; }
			var data;
			try { data = JSON.parse( s.textContent ); } catch ( e ) { return; }
			var run = function () { apply( wrap, data ); };
			run();
			if ( ! wrap.__tsMore ) {
				wrap.__tsMore = true;
				var t;
				window.addEventListener( 'resize', function () { clearTimeout( t ); t = setTimeout( run, 200 ); } );
				if ( document.fonts && document.fonts.ready ) { document.fonts.ready.then( run ); }
				wrap.addEventListener( 'click', function () { setTimeout( run, 450 ); } ); // carousel arrows reveal hidden slides
				setTimeout( run, 1200 );
			}
		} );
	}

	if ( document.readyState !== 'loading' ) { initMore(); } else { document.addEventListener( 'DOMContentLoaded', function () { initMore(); } ); }
	window.fwShortcodeInit = window.fwShortcodeInit || [];
	window.fwShortcodeInit.push( initMore );
} )();
