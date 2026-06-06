/**
 * Lightweight front-end enhancer for tabular tables.
 *
 * Adds client-side sorting, search/filter, pagination, length-change and an
 * info line to any <table class="fw-datatable"> — no external library. The
 * renderer (views/tabular.php) only adds this when the table opts in and has no
 * merged cells, so every body row is uniform.
 */
( function ( $ ) {
	'use strict';

	var T = $.extend( {
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

	function init( table ) {
		var $table = $( table );
		if ( $table.data( 'fwDt' ) ) { return; }
		$table.data( 'fwDt', 1 );

		var opt = {
			sort: $table.attr( 'data-sort' ) === '1',
			search: $table.attr( 'data-search' ) === '1',
			paginate: $table.attr( 'data-paginate' ) === '1',
			pageLen: parseInt( $table.attr( 'data-page-length' ), 10 ) || 10,
			lengthChange: $table.attr( 'data-length-change' ) === '1',
			info: $table.attr( 'data-info' ) === '1'
		};

		var $wrap = $table.closest( '.table' );
		if ( ! $wrap.length ) { $wrap = $table.parent(); }

		var $tbody = $table.children( 'tbody' ).first();
		var rows = $tbody.children( 'tr' ).get();

		var state = {
			q: '',
			sortCol: -1,
			dir: 1,
			page: 0,
			len: opt.paginate ? opt.pageLen : rows.length
		};

		// ---- chrome ----------------------------------------------------
		var $top = $( '<div class="fw-dt-top"></div>' );
		var $bottom = $( '<div class="fw-dt-bottom"></div>' );
		var $lengthSel, $search, $info, $pager;

		if ( opt.paginate && opt.lengthChange ) {
			var lens = [ 10, 25, 50, 100 ];
			if ( lens.indexOf( opt.pageLen ) === -1 ) { lens.push( opt.pageLen ); lens.sort( function ( a, b ) { return a - b; } ); }
			var optsHtml = lens.map( function ( n ) {
				return '<option value="' + n + '"' + ( n === opt.pageLen ? ' selected' : '' ) + '>' + n + '</option>';
			} ).join( '' ) + '<option value="-1">' + T.all + '</option>';
			$lengthSel = $( '<label class="fw-dt-length">' + T.show + ' <select>' + optsHtml + '</select> ' + T.entries + '</label>' );
			$top.append( $lengthSel );
		}

		if ( opt.search ) {
			$search = $( '<label class="fw-dt-search">' + T.search + ' <input type="search" placeholder="' + T.searchPlaceholder + '"></label>' );
			$top.append( $search );
		}

		if ( opt.info ) { $info = $( '<div class="fw-dt-info"></div>' ); $bottom.append( $info ); }
		if ( opt.paginate ) { $pager = $( '<div class="fw-dt-pager"></div>' ); $bottom.append( $pager ); }

		if ( $top.children().length ) { $wrap.before( $top ); }
		if ( $bottom.children().length ) { $wrap.after( $bottom ); }

		// ---- sorting headers -------------------------------------------
		var $headerRow = $table.children( 'thead' ).children( 'tr' ).last();
		if ( opt.sort && $headerRow.length ) {
			$headerRow.children( 'th, td' ).each( function ( i ) {
				var $th = $( this );
				$th.addClass( 'fw-dt-sortable' ).attr( 'tabindex', 0 ).attr( 'role', 'button' );
				function doSort() {
					if ( state.sortCol === i ) { state.dir = -state.dir; }
					else { state.sortCol = i; state.dir = 1; }
					state.page = 0;
					render();
				}
				$th.on( 'click', doSort );
				$th.on( 'keydown', function ( e ) {
					if ( e.keyCode === 13 || e.keyCode === 32 ) { e.preventDefault(); doSort(); }
				} );
			} );
		}

		// ---- events ----------------------------------------------------
		if ( $search ) {
			$search.find( 'input' ).on( 'input', function () {
				state.q = this.value.toLowerCase();
				state.page = 0;
				render();
			} );
		}
		if ( $lengthSel ) {
			$lengthSel.find( 'select' ).on( 'change', function () {
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
			$tbody.append( sorted );

			if ( $headerRow.length ) {
				$headerRow.children().removeClass( 'fw-dt-asc fw-dt-desc' );
				if ( state.sortCol >= 0 ) {
					$( $headerRow.children()[ state.sortCol ] ).addClass( state.dir > 0 ? 'fw-dt-asc' : 'fw-dt-desc' );
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

			if ( $info ) {
				if ( ! visible.length ) {
					$info.text( T.infoEmpty );
				} else {
					$info.text(
						T.info.replace( '_START_', visible.length ? start + 1 : 0 )
							.replace( '_END_', end )
							.replace( '_TOTAL_', visible.length )
					);
				}
			}

			if ( $pager ) { renderPager( pages ); }
		}

		function renderPager( pages ) {
			$pager.empty();
			if ( pages <= 1 ) { return; }

			var btn = function ( label, page, opts ) {
				opts = opts || {};
				var $b = $( '<button type="button" class="fw-dt-page"></button>' ).html( label );
				if ( opts.active ) { $b.addClass( 'is-active' ); }
				if ( opts.disabled ) { $b.prop( 'disabled', true ).addClass( 'is-disabled' ); }
				else { $b.on( 'click', function () { state.page = page; render(); } ); }
				return $b;
			};

			$pager.append( btn( T.prev, state.page - 1, { disabled: state.page === 0 } ) );

			// windowed page numbers
			var from = Math.max( 0, state.page - 2 ), to = Math.min( pages - 1, state.page + 2 );
			if ( from > 0 ) { $pager.append( btn( '1', 0 ) ); if ( from > 1 ) { $pager.append( $( '<span class="fw-dt-ellipsis">…</span>' ) ); } }
			for ( var p = from; p <= to; p++ ) { $pager.append( btn( String( p + 1 ), p, { active: p === state.page } ) ); }
			if ( to < pages - 1 ) { if ( to < pages - 2 ) { $pager.append( $( '<span class="fw-dt-ellipsis">…</span>' ) ); } $pager.append( btn( String( pages ), pages - 1 ) ); }

			$pager.append( btn( T.next, state.page + 1, { disabled: state.page === pages - 1 } ) );
		}

		render();
	}

	$( function () {
		$( 'table.fw-datatable' ).each( function () { init( this ); } );
	} );

}( jQuery ) );
