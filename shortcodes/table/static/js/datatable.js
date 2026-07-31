/**
 * Lightweight front-end enhancer for tabular tables.
 *
 * Adds client-side sorting, search/filter, pagination, length-change and an
 * info line to any <table class="fw-datatable"> — no external library. The
 * renderer (views/tabular.php) only adds this when the table opts in and has no
 * merged cells, so every body row is uniform. Vanilla JS — no jQuery.
 */
( function () {
	'use strict';

	var T = Object.assign( {
		search: 'Search:',
		searchPlaceholder: 'Type to filter…',
		show: 'Show',
		entries: 'entries',
		all: 'All',
		info: 'Showing _START_ to _END_ of _TOTAL_ entries',
		infoEmpty: 'No matching entries',
		prev: 'Prev',
		next: 'Next'
	}, window.fwTableL10n || {} );

	function text( cell ) {
		return ( cell ? ( cell.textContent || '' ) : '' ).replace( /\s+/g, ' ' ).trim();
	}

	function numericValue( s ) {
		// strip currency/grouping for numeric sort; return NaN if not numeric
		var cleaned = s.replace( /[^0-9.\-]/g, '' );
		if ( cleaned === '' || cleaned === '-' || cleaned === '.' ) { return NaN; }
		// avoid treating "1.2.3" style as number
		if ( ( cleaned.match( /\./g ) || [] ).length > 1 ) { return NaN; }
		return parseFloat( cleaned );
	}

	function el( html ) {
		var tpl = document.createElement( 'template' );
		tpl.innerHTML = html;
		return tpl.content.firstElementChild;
	}

	function init( table ) {
		if ( table._fwDt ) { return; }
		table._fwDt = 1;

		var opt = {
			sort: table.getAttribute( 'data-sort' ) === '1',
			search: table.getAttribute( 'data-search' ) === '1',
			paginate: table.getAttribute( 'data-paginate' ) === '1',
			pageLen: parseInt( table.getAttribute( 'data-page-length' ), 10 ) || 10,
			lengthChange: table.getAttribute( 'data-length-change' ) === '1',
			info: table.getAttribute( 'data-info' ) === '1'
		};

		var wrap = table.closest( '.table' ) || table.parentNode;

		var tbody = table.querySelector( ':scope > tbody' ) || table.tBodies[ 0 ];
		var rows = tbody ? Array.prototype.slice.call( tbody.children ).filter( function ( r ) { return r.tagName === 'TR'; } ) : [];

		var state = {
			q: '',
			sortCol: -1,
			dir: 1,
			page: 0,
			len: opt.paginate ? opt.pageLen : rows.length
		};

		// ---- chrome ----------------------------------------------------
		var top = el( '<div class="fw-dt-top"></div>' );
		var bottom = el( '<div class="fw-dt-bottom"></div>' );
		var lengthSel = null, search = null, info = null, pager = null;

		if ( opt.paginate && opt.lengthChange ) {
			var lens = [ 10, 25, 50, 100 ];
			if ( lens.indexOf( opt.pageLen ) === -1 ) { lens.push( opt.pageLen ); lens.sort( function ( a, b ) { return a - b; } ); }
			var optsHtml = lens.map( function ( n ) {
				return '<option value="' + n + '"' + ( n === opt.pageLen ? ' selected' : '' ) + '>' + n + '</option>';
			} ).join( '' ) + '<option value="-1">' + T.all + '</option>';
			lengthSel = el( '<label class="fw-dt-length">' + T.show + ' <select>' + optsHtml + '</select> ' + T.entries + '</label>' );
			top.appendChild( lengthSel );
		}

		if ( opt.search ) {
			search = el( '<label class="fw-dt-search">' + T.search + ' <input type="search" placeholder="' + T.searchPlaceholder + '"></label>' );
			top.appendChild( search );
		}

		if ( opt.info ) { info = el( '<div class="fw-dt-info"></div>' ); bottom.appendChild( info ); }
		if ( opt.paginate ) { pager = el( '<div class="fw-dt-pager"></div>' ); bottom.appendChild( pager ); }

		if ( top.children.length ) { wrap.parentNode.insertBefore( top, wrap ); }
		if ( bottom.children.length ) { wrap.parentNode.insertBefore( bottom, wrap.nextSibling ); }

		// ---- sorting headers -------------------------------------------
		var thead = table.querySelector( ':scope > thead' );
		var headerRow = null;
		if ( thead ) {
			var headerRows = thead.querySelectorAll( ':scope > tr' );
			headerRow = headerRows.length ? headerRows[ headerRows.length - 1 ] : null;
		}
		if ( opt.sort && headerRow ) {
			Array.prototype.forEach.call( headerRow.children, function ( th, i ) {
				th.classList.add( 'fw-dt-sortable' );
				th.setAttribute( 'tabindex', '0' );
				th.setAttribute( 'role', 'button' );
				function doSort() {
					if ( state.sortCol === i ) { state.dir = -state.dir; }
					else { state.sortCol = i; state.dir = 1; }
					state.page = 0;
					render();
				}
				th.addEventListener( 'click', doSort );
				th.addEventListener( 'keydown', function ( e ) {
					if ( e.keyCode === 13 || e.keyCode === 32 ) { e.preventDefault(); doSort(); }
				} );
			} );
		}

		// ---- events ----------------------------------------------------
		if ( search ) {
			search.querySelector( 'input' ).addEventListener( 'input', function () {
				state.q = this.value.toLowerCase();
				state.page = 0;
				render();
			} );
		}
		if ( lengthSel ) {
			lengthSel.querySelector( 'select' ).addEventListener( 'change', function () {
				var v = parseInt( this.value, 10 );
				state.len = v === -1 ? rows.length : v;
				state.page = 0;
				render();
			} );
		}

		function matches( row ) {
			if ( ! state.q ) { return true; }
			return ( row.textContent || '' ).toLowerCase().indexOf( state.q ) !== -1;
		}

		function compare( a, b ) {
			var av = text( a.cells[ state.sortCol ] ), bv = text( b.cells[ state.sortCol ] );
			var an = numericValue( av ), bn = numericValue( bv );
			var res;
			if ( ! isNaN( an ) && ! isNaN( bn ) ) { res = an - bn; }
			else { res = av.localeCompare( bv, undefined, { numeric: true, sensitivity: 'base' } ); }
			return res * state.dir;
		}

		function render() {
			var sorted = rows.slice();
			if ( state.sortCol >= 0 ) { sorted.sort( compare ); }
			// reorder DOM
			sorted.forEach( function ( r ) { tbody.appendChild( r ); } );

			if ( headerRow ) {
				Array.prototype.forEach.call( headerRow.children, function ( h ) {
					h.classList.remove( 'fw-dt-asc', 'fw-dt-desc' );
				} );
				if ( state.sortCol >= 0 && headerRow.children[ state.sortCol ] ) {
					headerRow.children[ state.sortCol ].classList.add( state.dir > 0 ? 'fw-dt-asc' : 'fw-dt-desc' );
				}
			}

			var visible = sorted.filter( matches );
			rows.forEach( function ( r ) { r.style.display = 'none'; } );

			var len = state.len || visible.length || 1;
			var pages = opt.paginate ? Math.max( 1, Math.ceil( visible.length / len ) ) : 1;
			if ( state.page >= pages ) { state.page = pages - 1; }
			if ( state.page < 0 ) { state.page = 0; }

			var start = opt.paginate ? state.page * len : 0;
			var end = opt.paginate ? Math.min( start + len, visible.length ) : visible.length;
			for ( var i = start; i < end; i++ ) { visible[ i ].style.display = ''; }

			if ( info ) {
				if ( ! visible.length ) {
					info.textContent = T.infoEmpty;
				} else {
					info.textContent = T.info.replace( '_START_', visible.length ? start + 1 : 0 )
						.replace( '_END_', end )
						.replace( '_TOTAL_', visible.length );
				}
			}

			if ( pager ) { renderPager( pages ); }
		}

		function renderPager( pages ) {
			pager.innerHTML = '';
			if ( pages <= 1 ) { return; }

			var btn = function ( label, page, opts ) {
				opts = opts || {};
				var b = el( '<button type="button" class="fw-dt-page"></button>' );
				b.innerHTML = label;
				if ( opts.active ) { b.classList.add( 'is-active' ); }
				if ( opts.disabled ) { b.disabled = true; b.classList.add( 'is-disabled' ); }
				else { b.addEventListener( 'click', function () { state.page = page; render(); } ); }
				return b;
			};

			pager.appendChild( btn( T.prev, state.page - 1, { disabled: state.page === 0 } ) );

			// windowed page numbers
			var from = Math.max( 0, state.page - 2 ), to = Math.min( pages - 1, state.page + 2 );
			if ( from > 0 ) { pager.appendChild( btn( '1', 0 ) ); if ( from > 1 ) { pager.appendChild( el( '<span class="fw-dt-ellipsis">…</span>' ) ); } }
			for ( var p = from; p <= to; p++ ) { pager.appendChild( btn( String( p + 1 ), p, { active: p === state.page } ) ); }
			if ( to < pages - 1 ) { if ( to < pages - 2 ) { pager.appendChild( el( '<span class="fw-dt-ellipsis">…</span>' ) ); } pager.appendChild( btn( String( pages ), pages - 1 ) ); }

			pager.appendChild( btn( T.next, state.page + 1, { disabled: state.page === pages - 1 } ) );
		}

		render();
	}

	function boot() {
		Array.prototype.forEach.call( document.querySelectorAll( 'table.fw-datatable' ), init );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', boot );
	} else {
		boot();
	}

}() );
