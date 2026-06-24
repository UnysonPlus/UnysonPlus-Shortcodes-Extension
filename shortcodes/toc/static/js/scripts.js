/**
 * Table of Contents shortcode — frontend builder.
 *
 * For every `.sc-toc` on the page: resolve the scan scope, collect the chosen
 * heading levels, assign slug ids to them (preserving any existing id), build
 * a (optionally nested, optionally numbered) list of anchor links, and wire up
 * smooth-scroll-with-offset, scrollspy highlighting and the collapse toggle.
 *
 * Pure vanilla JS — no jQuery. Config is read entirely from the wrapper's
 * data-* attributes (emitted by views/view.php).
 */
(function () {
	'use strict';

	var HEADING_TAGS = 'h1,h2,h3,h4,h5,h6';

	/* --------------------------------------------------------------------- */
	/* helpers                                                               */
	/* --------------------------------------------------------------------- */

	function slugify( text ) {
		var slug = String( text ).toLowerCase().trim()
			.replace( /[\s ]+/g, '-' )      // whitespace → dash
			.replace( /[^a-z0-9\-_]+/g, '' )     // strip the rest
			.replace( /-+/g, '-' )               // collapse dashes
			.replace( /^-+|-+$/g, '' );          // trim dashes
		return slug || 'section';
	}

	function uniqueId( base, used ) {
		var id = base, i = 2;
		while ( used[ id ] || document.getElementById( id ) ) {
			id = base + '-' + i;
			i++;
		}
		used[ id ] = true;
		return id;
	}

	function toRoman( n ) {
		if ( n < 1 ) { return '' + n; }
		var map = [ [ 1000, 'M' ], [ 900, 'CM' ], [ 500, 'D' ], [ 400, 'CD' ],
			[ 100, 'C' ], [ 90, 'XC' ], [ 50, 'L' ], [ 40, 'XL' ],
			[ 10, 'X' ], [ 9, 'IX' ], [ 5, 'V' ], [ 4, 'IV' ], [ 1, 'I' ] ];
		var out = '';
		for ( var i = 0; i < map.length; i++ ) {
			while ( n >= map[ i ][ 0 ] ) { out += map[ i ][ 1 ]; n -= map[ i ][ 0 ]; }
		}
		return out;
	}

	function toAlpha( n ) {
		var out = '';
		while ( n > 0 ) {
			n--;
			out = String.fromCharCode( 65 + ( n % 26 ) ) + out;
			n = Math.floor( n / 26 );
		}
		return out;
	}

	function resolveScope( nav, mode, selector ) {
		if ( mode === 'custom' && selector ) {
			try {
				var custom = document.querySelector( selector );
				if ( custom ) { return custom; }
			} catch ( e ) { /* invalid selector → fall through */ }
		}

		if ( mode === 'page' ) { return document.body; }

		// Auto-detect the main content container.
		var candidates = [
			'.fw-main-content', '.entry-content', '.post-content',
			'article .container', 'main', 'article', '#content',
			'#primary', '.site-main', '.content-area'
		];
		for ( var i = 0; i < candidates.length; i++ ) {
			var el = document.querySelector( candidates[ i ] );
			if ( el && el.querySelector( HEADING_TAGS ) ) { return el; }
		}

		// Fallback: nearest ancestor of the TOC that actually holds headings.
		var p = nav.parentElement;
		while ( p && p !== document.body ) {
			if ( p.querySelector( HEADING_TAGS ) ) { return p; }
			p = p.parentElement;
		}
		return document.body;
	}

	function collectHeadings( scope, levels, skipLines ) {
		var wanted = {};
		levels.forEach( function ( l ) { wanted[ 'H' + l ] = true; } );

		var nodes = scope.querySelectorAll( HEADING_TAGS );
		var out = [];
		for ( var i = 0; i < nodes.length; i++ ) {
			var h = nodes[ i ];
			if ( ! wanted[ h.tagName ] ) { continue; }
			// Never list a heading that lives inside any TOC, page chrome, or
			// one explicitly opted out.
			if ( h.closest( '.sc-toc, header, footer, [data-toc-skip]' ) ) { continue; }
			var text = ( h.textContent || '' ).trim();
			if ( text === '' ) { continue; }
			if ( skipLines.length ) {
				var lower = text.toLowerCase(), skipped = false;
				for ( var s = 0; s < skipLines.length; s++ ) {
					if ( lower.indexOf( skipLines[ s ] ) !== -1 ) { skipped = true; break; }
				}
				if ( skipped ) { continue; }
			}
			out.push( { el: h, text: text, level: parseInt( h.tagName.substr( 1 ), 10 ) } );
		}
		return out;
	}

	/* Build a nesting tree from the flat heading list, ranked by the order of
	   the selected levels (so H2,H4 nests an H4 under an H2 even with H3 absent). */
	function buildTree( headings, levels ) {
		var rankOf = {};
		levels.slice().sort( function ( a, b ) { return a - b; } )
			.forEach( function ( l, idx ) { rankOf[ l ] = idx; } );

		var root = { children: [], rank: -1 };
		var stack = [ root ];
		headings.forEach( function ( h ) {
			var node = { heading: h, rank: rankOf[ h.level ], children: [] };
			while ( stack.length > 1 && stack[ stack.length - 1 ].rank >= node.rank ) {
				stack.pop();
			}
			stack[ stack.length - 1 ].children.push( node );
			stack.push( node );
		} );
		return root;
	}

	function formatNumber( index, scheme ) {
		switch ( scheme ) {
			case 'roman':       return toRoman( index );
			case 'upper_alpha': return toAlpha( index );
			default:            return '' + index;   // decimal
		}
	}

	/* --------------------------------------------------------------------- */
	/* rendering                                                             */
	/* --------------------------------------------------------------------- */

	function buildList( tree, cfg, ctx ) {
		var ul = document.createElement( 'ul' );
		ul.className = 'sc-toc__sublist';

		tree.children.forEach( function ( node, i ) {
			var li = document.createElement( 'li' );
			li.className = 'sc-toc__item';

			var a = document.createElement( 'a' );
			a.className = 'sc-toc__link';
			a.href = '#' + node.heading.id;
			a.setAttribute( 'data-target', node.heading.id );
			if ( cfg.nofollow ) { a.setAttribute( 'rel', 'nofollow' ); }

			// Numbering
			if ( cfg.numeration !== 'none' && cfg.numeration !== 'bullets' ) {
				var label;
				if ( ! cfg.hierarchical ) {
					// Flat list: a single running counter.
					ctx.flat++;
					label = formatNumber( ctx.flat, cfg.numeration === 'decimal' || cfg.numeration === 'decimal_nested' ? 'decimal' : cfg.numeration );
				} else if ( cfg.numeration === 'decimal_nested' ) {
					label = ( ctx.prefix ? ctx.prefix + '.' : '' ) + ( i + 1 );
				} else {
					label = formatNumber( i + 1, cfg.numeration );
				}
				var num = document.createElement( 'span' );
				num.className = 'sc-toc__num';
				num.textContent = label + cfg.suffix;
				a.appendChild( num );
			}

			var span = document.createElement( 'span' );
			span.className = 'sc-toc__text';
			span.textContent = node.heading.text;
			a.appendChild( span );

			li.appendChild( a );
			ctx.links.push( a );

			if ( node.children.length && cfg.hierarchical ) {
				var childPrefix = ctx.prefix;
				if ( cfg.numeration === 'decimal_nested' ) {
					childPrefix = ( ctx.prefix ? ctx.prefix + '.' : '' ) + ( i + 1 );
				}
				var childCtx = { links: ctx.links, prefix: childPrefix, flat: ctx.flat };
				li.appendChild( buildList( node, cfg, childCtx ) );
				ctx.flat = childCtx.flat;
			}

			ul.appendChild( li );
		} );

		return ul;
	}

	/* --------------------------------------------------------------------- */
	/* scroll + interactions                                                 */
	/* --------------------------------------------------------------------- */

	function jumpTo( id, cfg ) {
		var target = document.getElementById( id );
		if ( ! target ) { return; }
		var top = target.getBoundingClientRect().top + window.pageYOffset - cfg.offset;
		window.scrollTo( {
			top: Math.max( 0, top ),
			behavior: cfg.smooth ? 'smooth' : 'auto'
		} );
	}

	function wireScrollspy( headings, links, cfg ) {
		var byId = {};
		links.forEach( function ( a ) { byId[ a.getAttribute( 'data-target' ) ] = a; } );

		var ticking = false;
		function update() {
			ticking = false;
			var pos = window.pageYOffset + cfg.offset + 2;
			var activeId = headings.length ? headings[ 0 ].id : null;
			for ( var i = 0; i < headings.length; i++ ) {
				if ( headings[ i ].offsetTop <= pos ) {
					activeId = headings[ i ].id;
				} else {
					break;
				}
			}
			links.forEach( function ( a ) {
				a.classList.toggle( 'is-active', a.getAttribute( 'data-target' ) === activeId );
			} );
		}
		function onScroll() {
			if ( ! ticking ) {
				ticking = true;
				window.requestAnimationFrame( update );
			}
		}
		window.addEventListener( 'scroll', onScroll, { passive: true } );
		window.addEventListener( 'resize', onScroll, { passive: true } );
		update();
	}

	function wireToggle( nav ) {
		var btn = nav.querySelector( '.sc-toc__toggle' );
		if ( ! btn ) { return; }
		btn.addEventListener( 'click', function () {
			var collapsed = nav.classList.toggle( 'is-collapsed' );
			btn.setAttribute( 'aria-expanded', collapsed ? 'false' : 'true' );
			btn.textContent = collapsed
				? ( btn.getAttribute( 'data-label-show' ) || 'show' )
				: ( btn.getAttribute( 'data-label-hide' ) || 'hide' );
		} );
	}

	/* --------------------------------------------------------------------- */
	/* init                                                                  */
	/* --------------------------------------------------------------------- */

	function initOne( nav ) {
		if ( nav.getAttribute( 'data-toc-ready' ) === '1' ) { return; }

		var cfg = {
			scope:        nav.getAttribute( 'data-scope' ) || 'content',
			selector:     nav.getAttribute( 'data-selector' ) || '',
			levels:       ( nav.getAttribute( 'data-levels' ) || '2,3' ).split( ',' )
				.map( function ( s ) { return parseInt( s, 10 ); } )
				.filter( function ( n ) { return n >= 1 && n <= 6; } ),
			hierarchical: nav.getAttribute( 'data-hierarchical' ) === '1',
			min:          parseInt( nav.getAttribute( 'data-min' ) || '1', 10 ),
			numeration:   nav.getAttribute( 'data-numeration' ) || 'decimal_nested',
			suffix:       nav.getAttribute( 'data-suffix' ) || '',
			smooth:       nav.getAttribute( 'data-smooth' ) === '1',
			offset:       parseInt( nav.getAttribute( 'data-offset' ) || '0', 10 ) || 0,
			scrollspy:    nav.getAttribute( 'data-scrollspy' ) === '1',
			nofollow:     nav.getAttribute( 'data-nofollow' ) === '1'
		};
		if ( ! cfg.levels.length ) { cfg.levels = [ 2, 3 ]; }

		var skipLines = ( nav.getAttribute( 'data-skip' ) || '' ).split( /\r?\n/ )
			.map( function ( s ) { return s.trim().toLowerCase(); } )
			.filter( function ( s ) { return s !== ''; } );

		var scope = resolveScope( nav, cfg.scope, cfg.selector );
		var headings = collectHeadings( scope, cfg.levels, skipLines );

		var list = nav.querySelector( '.sc-toc__list' );

		if ( headings.length < cfg.min ) {
			// Not enough headings (yet). Hide, but leave un-"ready" so the
			// load-time re-scan can pick up late-injected content.
			nav.classList.add( 'sc-toc--empty' );
			nav.setAttribute( 'hidden', 'hidden' );
			return;
		}

		// Enough headings — commit. Mark ready so re-scans skip this one.
		nav.setAttribute( 'data-toc-ready', '1' );
		nav.removeAttribute( 'hidden' );
		nav.classList.remove( 'sc-toc--empty' );

		// Assign ids (preserve existing ones).
		var used = {};
		headings.forEach( function ( h ) {
			if ( h.el.id ) {
				used[ h.el.id ] = true;
				h.id = h.el.id;
			} else {
				h.id = uniqueId( slugify( h.text ), used );
				h.el.id = h.id;
			}
			// Cache absolute offset for scrollspy.
			h.offsetTop = h.el.getBoundingClientRect().top + window.pageYOffset;
		} );

		// Build the list.
		var tree = buildTree( headings, cfg.levels );
		var ctx  = { links: [], prefix: '', flat: 0 };
		var built = buildList( tree, cfg, ctx );
		built.classList.remove( 'sc-toc__sublist' );
		built.classList.add( 'sc-toc__list' );
		if ( list && list.className ) { built.className = list.className; }
		if ( list ) { nav.replaceChild( built, list ); }

		// Click → smooth jump with offset, keep the hash in the URL.
		built.addEventListener( 'click', function ( e ) {
			var a = e.target.closest( '.sc-toc__link' );
			if ( ! a ) { return; }
			e.preventDefault();
			var id = a.getAttribute( 'data-target' );
			jumpTo( id, cfg );
			if ( window.history && window.history.pushState ) {
				window.history.pushState( null, '', '#' + id );
			} else {
				window.location.hash = id;
			}
		} );

		if ( cfg.scrollspy ) {
			wireScrollspy( headings, ctx.links, cfg );
		}

		wireToggle( nav );

		// Honour a deep link to one of our headings on first load (offset-aware).
		if ( window.location.hash ) {
			var hashId = decodeURIComponent( window.location.hash.substring( 1 ) );
			if ( document.getElementById( hashId ) ) {
				window.setTimeout( function () { jumpTo( hashId, cfg ); }, 60 );
			}
		}
	}

	function init() {
		var navs = document.querySelectorAll( '.sc-toc' );
		for ( var i = 0; i < navs.length; i++ ) { initOne( navs[ i ] ); }
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}

	// Re-scan if other scripts inject content late (best-effort, debounced).
	window.addEventListener( 'load', function () {
		window.setTimeout( init, 0 );
	} );
})();
