/**
 * Tabular table editor (Unyson `table` option type, tabular mode).
 *
 * The whole UI is built here from a JSON model that mirrors the db shape decoded
 * by FW_Option_Type_Table::get_value_from_json(). Every change writes the model
 * back into the hidden <textarea.fw-tabular-json> that Unyson serializes.
 *
 * Model shape:
 *   {
 *     header_options: { header_rows:int, footer_rows:int },
 *     cols:    [ { name, align, width } ],
 *     content: [ [ { textarea, colspan, rowspan, merged } ] ]
 *   }
 *
 * Parsing/serialization helpers live in import-export.js (window.FwTabularIO).
 */
( function ( $ ) {

	var IO = window.FwTabularIO || {};
	var L = ( window.localizeTabularTable && localizeTabularTable.l10n ) || {};
	var MAX_COLS = ( window.localizeTabularTable && parseInt( localizeTabularTable.maxCols, 10 ) ) || 50;

	function t( key, fallback ) {
		return L[ key ] || fallback;
	}

	function colLabel( n ) {
		var s = '';
		n++;
		while ( n > 0 ) {
			var m = ( n - 1 ) % 26;
			s = String.fromCharCode( 65 + m ) + s;
			n = Math.floor( ( n - 1 ) / 26 );
		}
		return s;
	}

	function escAttr( s ) {
		return String( s == null ? '' : s ).replace( /"/g, '&quot;' );
	}

	function blankCell() {
		return { textarea: '', colspan: 1, rowspan: 1, merged: false };
	}

	function blankRow( ncol ) {
		var row = [];
		for ( var i = 0; i < ncol; i++ ) { row.push( blankCell() ); }
		return row;
	}

	function intval( v, def ) {
		v = parseInt( v, 10 );
		return isNaN( v ) ? ( def || 0 ) : v;
	}

	// ---------------------------------------------------------------------

	function TabularEditor( $editor ) {
		var self = this;

		this.$editor = $editor;
		this.$json = $editor.closest( '.fw-tabular' ).find( '.fw-tabular-json' );
		this.model = this.normalize( this.parse() );
		this.sel = null;       // {r1,c1,r2,c2} selection rectangle
		this.anchor = null;    // {r,c} last clicked cell

		this.$menu = $( '<div class="fw-tabular-menu" style="display:none"></div>' ).appendTo( document.body );

		this.bind();
		this.render();
		this.sync();

		$( document ).on( 'mousedown.fwTabular', function ( e ) {
			if ( ! self.$menu[ 0 ].contains( e.target ) &&
				! $( e.target ).closest( '.fw-tt-menu-btn, .fw-tt-toolbar-menu' ).length ) {
				self.closeMenu();
			}
		} );
	}

	TabularEditor.prototype.parse = function () {
		try { return JSON.parse( this.$json.val() ); } catch ( e ) { return {}; }
	};

	TabularEditor.prototype.normalize = function ( model ) {
		model = model || {};

		var header = model.header_options || {};
		header.header_rows = Math.max( 0, intval( header.header_rows, 0 ) );
		header.footer_rows = Math.max( 0, intval( header.footer_rows, 0 ) );

		var cols = ( Array.isArray( model.cols ) && model.cols.length ) ? model.cols : [ {}, {}, {} ];
		cols = cols.map( function ( c ) {
			c = c || {};
			return {
				name: c.name || 'default-col',
				align: ( [ '', 'left', 'center', 'right', 'justify' ].indexOf( c.align ) !== -1 ) ? c.align : '',
				width: c.width || ''
			};
		} );
		var ncol = cols.length;

		var content = ( Array.isArray( model.content ) && model.content.length ) ? model.content : [ [], [], [] ];
		content = content.map( function ( row ) {
			row = Array.isArray( row ) ? row : [];
			var out = [];
			for ( var i = 0; i < ncol; i++ ) {
				var cell = row[ i ] || {};
				out.push( {
					textarea: typeof cell.textarea === 'string' ? cell.textarea : '',
					colspan: Math.max( 1, intval( cell.colspan, 1 ) ),
					rowspan: Math.max( 1, intval( cell.rowspan, 1 ) ),
					merged: !! cell.merged
				} );
			}
			return out;
		} );

		var nrow = content.length;
		if ( header.header_rows > nrow ) { header.header_rows = nrow; }
		if ( header.footer_rows > nrow - header.header_rows ) {
			header.footer_rows = Math.max( 0, nrow - header.header_rows );
		}

		return { header_options: header, cols: cols, content: content };
	};

	TabularEditor.prototype.sync = function () {
		this.$json.val( JSON.stringify( this.model ) ).trigger( 'change' );
	};

	/**
	 * Derive the `merged` flag of every cell from the colspan/rowspan of origin
	 * cells. Keeps the model self-consistent after any structural change so we
	 * never render an overlapping / dangling merge.
	 */
	TabularEditor.prototype.recomputeMerges = function () {
		var m = this.model, nrow = m.content.length, ncol = m.cols.length, r, c;

		for ( r = 0; r < nrow; r++ ) {
			for ( c = 0; c < ncol; c++ ) { m.content[ r ][ c ].merged = false; }
		}
		for ( r = 0; r < nrow; r++ ) {
			for ( c = 0; c < ncol; c++ ) {
				var cell = m.content[ r ][ c ];
				if ( cell.merged ) { continue; } // covered by an earlier origin
				cell.colspan = Math.min( Math.max( 1, cell.colspan ), ncol - c );
				cell.rowspan = Math.min( Math.max( 1, cell.rowspan ), nrow - r );
				if ( cell.colspan === 1 && cell.rowspan === 1 ) { continue; }
				for ( var rr = r; rr < r + cell.rowspan; rr++ ) {
					for ( var cc = c; cc < c + cell.colspan; cc++ ) {
						if ( rr === r && cc === c ) { continue; }
						var cov = m.content[ rr ][ cc ];
						cov.merged = true; cov.colspan = 1; cov.rowspan = 1;
					}
				}
			}
		}
	};

	// --- rendering -------------------------------------------------------

	TabularEditor.prototype.toolbarHtml = function () {
		var h = this.model.header_options;
		return '' +
			'<div class="fw-tabular-toolbar">' +
				'<div class="fw-tabular-toolbar-group">' +
					'<button type="button" class="button" data-act="add-row"><span class="dashicons dashicons-plus-alt2"></span> ' + t( 'row', 'Row' ) + '</button>' +
					'<button type="button" class="button" data-act="add-col"><span class="dashicons dashicons-plus-alt2"></span> ' + t( 'col', 'Column' ) + '</button>' +
					'<span class="fw-tabular-toolbar-divider"></span>' +
					'<button type="button" class="button" data-act="merge" title="' + escAttr( t( 'mergeHint', 'Select cells (Shift+click) then merge' ) ) + '"><span class="dashicons dashicons-editor-table"></span> ' + t( 'merge', 'Merge' ) + '</button>' +
					'<button type="button" class="button" data-act="unmerge">' + t( 'unmerge', 'Unmerge' ) + '</button>' +
					'<span class="fw-tabular-toolbar-divider"></span>' +
					'<button type="button" class="button fw-tt-toolbar-menu" data-menu="import"><span class="dashicons dashicons-upload"></span> ' + t( 'import', 'Import' ) + ' <span class="dashicons dashicons-arrow-down-alt2"></span></button>' +
					'<button type="button" class="button fw-tt-toolbar-menu" data-menu="export"><span class="dashicons dashicons-download"></span> ' + t( 'export', 'Export' ) + ' <span class="dashicons dashicons-arrow-down-alt2"></span></button>' +
				'</div>' +
				'<div class="fw-tabular-toolbar-group fw-tabular-toolbar-right">' +
					'<label class="fw-tabular-num">' + t( 'headerRows', 'Header rows' ) +
						' <input type="number" min="0" class="fw-tt-header-rows" value="' + h.header_rows + '"></label>' +
					'<label class="fw-tabular-num">' + t( 'footerRows', 'Footer rows' ) +
						' <input type="number" min="0" class="fw-tt-footer-rows" value="' + h.footer_rows + '"></label>' +
				'</div>' +
			'</div>';
	};

	TabularEditor.prototype.render = function () {
		this.recomputeMerges();

		var m = this.model,
			nrow = m.content.length,
			ncol = m.cols.length,
			hr = m.header_options.header_rows,
			fr = m.header_options.footer_rows,
			h = this.toolbarHtml();

		h += '<div class="fw-tabular-scroll"><table class="fw-tabular-grid"><thead><tr class="fw-tt-colctrl">';
		h += '<th class="fw-tt-corner"></th>';
		for ( var c = 0; c < ncol; c++ ) {
			var alignBadge = m.cols[ c ].align ? ' fw-tt-aligned-' + m.cols[ c ].align : '';
			h += '<th class="fw-tt-colhead' + alignBadge + '" data-col="' + c + '">' +
				'<span class="fw-tt-colletter">' + colLabel( c ) + '</span>' +
				'<button type="button" class="fw-tt-menu-btn" data-menu="col" data-col="' + c + '" title="' + escAttr( t( 'col', 'Column' ) ) + '"><span class="dashicons dashicons-arrow-down-alt2"></span></button>' +
				'</th>';
		}
		h += '</tr></thead><tbody>';

		for ( var r = 0; r < nrow; r++ ) {
			var cls = [];
			if ( r < hr ) { cls.push( 'fw-tt-header' ); }
			if ( fr > 0 && r >= nrow - fr ) { cls.push( 'fw-tt-footer' ); }

			h += '<tr class="' + cls.join( ' ' ) + '" data-row="' + r + '">';
			h += '<th class="fw-tt-rowctrl">' +
				'<span class="fw-tt-gripper dashicons dashicons-menu" title="' + escAttr( t( 'row', 'Row' ) ) + '"></span>' +
				'<span class="fw-tt-rownum">' + ( r + 1 ) + '</span>' +
				'<button type="button" class="fw-tt-menu-btn" data-menu="row" data-row="' + r + '" title="' + escAttr( t( 'row', 'Row' ) ) + '"><span class="dashicons dashicons-arrow-down-alt2"></span></button>' +
				'</th>';

			for ( var c2 = 0; c2 < ncol; c2++ ) {
				var cell = m.content[ r ][ c2 ];
				if ( cell.merged ) { continue; }
				var align = m.cols[ c2 ].align,
					st = align ? ' style="text-align:' + align + '"' : '',
					span = ( cell.colspan > 1 ? ' colspan="' + cell.colspan + '"' : '' ) +
						( cell.rowspan > 1 ? ' rowspan="' + cell.rowspan + '"' : '' );
				h += '<td class="fw-tt-cell" data-row="' + r + '" data-col="' + c2 + '"' + span + st +
					' contenteditable="true">' + cell.textarea + '</td>';
			}
			h += '</tr>';
		}
		h += '</tbody></table></div>';

		this.$editor.html( h ).addClass( 'fw-tabular-ready' );
		this.initSortable();
	};

	TabularEditor.prototype.initSortable = function () {
		var self = this, $tbody = this.$editor.find( 'tbody' );
		try { $tbody.sortable( 'destroy' ); } catch ( e ) {}

		$tbody.sortable( {
			items: '> tr',
			handle: '.fw-tt-gripper',
			axis: 'y',
			cursor: 'grabbing',
			helper: function ( e, tr ) {
				var $helper = tr.clone();
				tr.children().each( function ( i ) {
					$( $helper.children()[ i ] ).width( $( this ).width() );
				} );
				return $helper;
			},
			stop: function () {
				var order = $tbody.find( '> tr' ).map( function () {
					return intval( $( this ).attr( 'data-row' ), 0 );
				} ).get();
				self.model.content = order.map( function ( i ) { return self.model.content[ i ]; } );
				self.afterStructural();
			}
		} );
	};

	// --- popover menu ----------------------------------------------------

	TabularEditor.prototype.menuItems = function ( kind, idx ) {
		if ( kind === 'col' ) {
			var align = this.model.cols[ idx ].align || 'left';
			return [
				{ act: 'ins-col-left', icon: 'arrow-left-alt2', label: t( 'insertLeft', 'Insert column left' ) },
				{ act: 'ins-col-right', icon: 'arrow-right-alt2', label: t( 'insertRight', 'Insert column right' ) },
				{ act: 'dup-col', icon: 'admin-page', label: t( 'duplicate', 'Duplicate' ) },
				{ act: 'move-col-left', icon: 'arrow-left', label: t( 'moveLeft', 'Move left' ) },
				{ act: 'move-col-right', icon: 'arrow-right', label: t( 'moveRight', 'Move right' ) },
				{ sep: true },
				{ act: 'align-left', icon: 'editor-alignleft', label: t( 'alignLeft', 'Align left' ), checked: align === 'left' },
				{ act: 'align-center', icon: 'editor-aligncenter', label: t( 'alignCenter', 'Align center' ), checked: align === 'center' },
				{ act: 'align-right', icon: 'editor-alignright', label: t( 'alignRight', 'Align right' ), checked: align === 'right' },
				{ sep: true },
				{ act: 'del-col', icon: 'trash', label: t( 'delete', 'Delete' ), danger: true }
			];
		}
		if ( kind === 'row' ) {
			return [
				{ act: 'ins-row-above', icon: 'arrow-up-alt2', label: t( 'insertAbove', 'Insert row above' ) },
				{ act: 'ins-row-below', icon: 'arrow-down-alt2', label: t( 'insertBelow', 'Insert row below' ) },
				{ act: 'dup-row', icon: 'admin-page', label: t( 'duplicate', 'Duplicate' ) },
				{ act: 'move-row-up', icon: 'arrow-up', label: t( 'moveUp', 'Move up' ) },
				{ act: 'move-row-down', icon: 'arrow-down', label: t( 'moveDown', 'Move down' ) },
				{ sep: true },
				{ act: 'del-row', icon: 'trash', label: t( 'delete', 'Delete' ), danger: true }
			];
		}
		if ( kind === 'import' ) {
			return [
				{ act: 'import-html', icon: 'editor-paste-text', label: t( 'pasteHtml', 'Paste HTML / Word table' ) },
				{ act: 'import-csv', icon: 'media-spreadsheet', label: t( 'uploadCsv', 'Upload CSV' ) }
			];
		}
		// export
		return [
			{ act: 'export-csv', icon: 'media-spreadsheet', label: t( 'downloadCsv', 'Download CSV' ) },
			{ act: 'export-copy', icon: 'admin-page', label: t( 'copyClipboard', 'Copy to clipboard' ) }
		];
	};

	TabularEditor.prototype.openMenu = function ( kind, idx, $btn ) {
		var items = this.menuItems( kind, idx );
		var html = items.map( function ( it ) {
			if ( it.sep ) { return '<div class="fw-tabular-menu-sep"></div>'; }
			return '<button type="button" class="fw-tabular-menu-item' +
				( it.danger ? ' is-danger' : '' ) + ( it.checked ? ' is-checked' : '' ) + '" data-act="' + it.act + '">' +
				'<span class="dashicons dashicons-' + it.icon + '"></span>' +
				'<span class="fw-tabular-menu-label">' + it.label + '</span>' +
				( it.checked ? '<span class="dashicons dashicons-yes fw-tabular-menu-check"></span>' : '' ) +
				'</button>';
		} ).join( '' );

		this.menuCtx = { kind: kind, idx: idx };
		this.$menu.html( html ).show();

		var rect = $btn[ 0 ].getBoundingClientRect(),
			top = rect.bottom + window.pageYOffset + 2,
			left = rect.left + window.pageXOffset,
			mw = this.$menu.outerWidth();

		if ( left + mw > window.pageXOffset + document.documentElement.clientWidth - 8 ) {
			left = rect.right + window.pageXOffset - mw;
		}
		this.$menu.css( { top: top, left: Math.max( 8, left ) } );
	};

	TabularEditor.prototype.closeMenu = function () {
		this.$menu.hide().empty();
		this.menuCtx = null;
	};

	// --- structural operations ------------------------------------------

	TabularEditor.prototype.insertColAt = function ( idx ) {
		if ( this.model.cols.length >= MAX_COLS ) { return; }
		this.model.cols.splice( idx, 0, { name: 'default-col', align: '', width: '' } );
		this.model.content.forEach( function ( row ) { row.splice( idx, 0, blankCell() ); } );
		this.afterStructural();
	};

	TabularEditor.prototype.deleteCol = function ( idx ) {
		if ( this.model.cols.length <= 1 ) { fw.notify( t( 'cantDeleteLast', 'A table needs at least one row and one column.' ), 'warning' ); return; }
		this.model.cols.splice( idx, 1 );
		this.model.content.forEach( function ( row ) { row.splice( idx, 1 ); } );
		this.afterStructural();
	};

	TabularEditor.prototype.moveCol = function ( idx, dir ) {
		var j = idx + dir;
		if ( j < 0 || j >= this.model.cols.length ) { return; }
		var tmp = this.model.cols[ idx ]; this.model.cols[ idx ] = this.model.cols[ j ]; this.model.cols[ j ] = tmp;
		this.model.content.forEach( function ( row ) {
			var cv = row[ idx ]; row[ idx ] = row[ j ]; row[ j ] = cv;
		} );
		this.afterStructural();
	};

	TabularEditor.prototype.dupCol = function ( idx ) {
		if ( this.model.cols.length >= MAX_COLS ) { return; }
		this.model.cols.splice( idx + 1, 0, $.extend( {}, this.model.cols[ idx ] ) );
		this.model.content.forEach( function ( row ) {
			row.splice( idx + 1, 0, $.extend( {}, row[ idx ] ) );
		} );
		this.afterStructural();
	};

	TabularEditor.prototype.setColAlign = function ( idx, align ) {
		this.model.cols[ idx ].align = ( this.model.cols[ idx ].align === align ) ? '' : align;
		this.afterStructural();
	};

	TabularEditor.prototype.insertRowAt = function ( idx ) {
		this.model.content.splice( idx, 0, blankRow( this.model.cols.length ) );
		this.afterStructural();
	};

	TabularEditor.prototype.deleteRow = function ( idx ) {
		if ( this.model.content.length <= 1 ) { fw.notify( t( 'cantDeleteLast', 'A table needs at least one row and one column.' ), 'warning' ); return; }
		this.model.content.splice( idx, 1 );
		this.clampHeaderFooter();
		this.afterStructural();
	};

	TabularEditor.prototype.moveRow = function ( idx, dir ) {
		var j = idx + dir;
		if ( j < 0 || j >= this.model.content.length ) { return; }
		var tmp = this.model.content[ idx ]; this.model.content[ idx ] = this.model.content[ j ]; this.model.content[ j ] = tmp;
		this.afterStructural();
	};

	TabularEditor.prototype.dupRow = function ( idx ) {
		var copy = this.model.content[ idx ].map( function ( cell ) { return $.extend( {}, cell ); } );
		this.model.content.splice( idx + 1, 0, copy );
		this.afterStructural();
	};

	TabularEditor.prototype.clampHeaderFooter = function () {
		var nrow = this.model.content.length, h = this.model.header_options;
		if ( h.header_rows > nrow ) { h.header_rows = nrow; }
		if ( h.footer_rows > nrow - h.header_rows ) { h.footer_rows = Math.max( 0, nrow - h.header_rows ); }
	};

	TabularEditor.prototype.afterStructural = function () {
		this.sel = null;
		this.closeMenu();
		this.render();
		this.sync();
	};

	// --- selection + merge ----------------------------------------------

	TabularEditor.prototype.computeRect = function ( a, b ) {
		var m = this.model,
			r1 = Math.min( a.r, b.r ), r2 = Math.max( a.r, b.r ),
			c1 = Math.min( a.c, b.c ), c2 = Math.max( a.c, b.c ),
			changed = true;

		while ( changed ) {
			changed = false;
			for ( var r = r1; r <= r2; r++ ) {
				for ( var c = c1; c <= c2; c++ ) {
					var cell = m.content[ r ] && m.content[ r ][ c ];
					if ( ! cell || cell.merged ) { continue; }
					if ( r + cell.rowspan - 1 > r2 ) { r2 = r + cell.rowspan - 1; changed = true; }
					if ( c + cell.colspan - 1 > c2 ) { c2 = c + cell.colspan - 1; changed = true; }
				}
			}
		}
		return { r1: r1, c1: c1, r2: r2, c2: c2 };
	};

	TabularEditor.prototype.paintSelection = function () {
		var s = this.sel;
		this.$editor.find( '.fw-tt-cell' ).each( function () {
			var $c = $( this );
			if ( ! s ) { $c.removeClass( 'fw-tt-selected' ); return; }
			var r = intval( $c.attr( 'data-row' ) ), c = intval( $c.attr( 'data-col' ) );
			$c.toggleClass( 'fw-tt-selected', r >= s.r1 && r <= s.r2 && c >= s.c1 && c <= s.c2 );
		} );
	};

	TabularEditor.prototype.mergeSelection = function () {
		var s = this.sel, m = this.model;
		if ( ! s || ( s.r1 === s.r2 && s.c1 === s.c2 ) ) {
			this.toast( t( 'selectMerge', 'Shift+click to select 2+ cells, then Merge.' ) );
			return;
		}
		var texts = [], r, c;
		for ( r = s.r1; r <= s.r2; r++ ) {
			for ( c = s.c1; c <= s.c2; c++ ) {
				var txt = ( m.content[ r ][ c ].textarea || '' ).trim();
				if ( ! m.content[ r ][ c ].merged && txt ) { texts.push( txt ); }
			}
		}
		for ( r = s.r1; r <= s.r2; r++ ) {
			for ( c = s.c1; c <= s.c2; c++ ) {
				var cl = m.content[ r ][ c ];
				cl.textarea = ''; cl.colspan = 1; cl.rowspan = 1; cl.merged = false;
			}
		}
		var origin = m.content[ s.r1 ][ s.c1 ];
		origin.textarea = texts.join( '<br>' );
		origin.colspan = s.c2 - s.c1 + 1;
		origin.rowspan = s.r2 - s.r1 + 1;
		this.afterStructural();
	};

	TabularEditor.prototype.unmergeSelection = function () {
		var m = this.model, s = this.sel, did = false;
		function apply( r, c ) {
			var cell = m.content[ r ][ c ];
			if ( cell.colspan > 1 || cell.rowspan > 1 ) { cell.colspan = 1; cell.rowspan = 1; did = true; }
		}
		if ( s ) {
			for ( var r = s.r1; r <= s.r2; r++ ) { for ( var c = s.c1; c <= s.c2; c++ ) { apply( r, c ); } }
		} else if ( this.anchor ) {
			apply( this.anchor.r, this.anchor.c );
		}
		if ( did ) { this.afterStructural(); }
		else { this.toast( t( 'noMerge', 'No merged cell selected.' ) ); }
	};

	// --- import / export -------------------------------------------------

	TabularEditor.prototype.ensureCols = function ( n ) {
		while ( this.model.cols.length < n && this.model.cols.length < MAX_COLS ) {
			this.model.cols.push( { name: 'default-col', align: '', width: '' } );
			this.model.content.forEach( function ( row ) { row.push( blankCell() ); } );
		}
	};

	TabularEditor.prototype.ensureRows = function ( n ) {
		while ( this.model.content.length < n ) {
			this.model.content.push( blankRow( this.model.cols.length ) );
		}
	};

	TabularEditor.prototype.fillGridAt = function ( r, c, grid, asText ) {
		var w = 0;
		grid.forEach( function ( g ) { if ( g.length > w ) { w = g.length; } } );
		this.ensureCols( Math.min( c + w, MAX_COLS ) );
		this.ensureRows( r + grid.length );

		var esc = asText ? IO.escapeHtml : function ( v ) { return v; };
		var ncol = this.model.cols.length;

		for ( var gi = 0; gi < grid.length; gi++ ) {
			for ( var gj = 0; gj < grid[ gi ].length; gj++ ) {
				var tc = c + gj;
				if ( tc >= ncol ) { continue; }
				var cell = this.model.content[ r + gi ][ tc ];
				cell.textarea = esc( grid[ gi ][ gj ] != null ? grid[ gi ][ gj ] : '' );
				cell.colspan = 1; cell.rowspan = 1; cell.merged = false;
			}
		}
		this.afterStructural();
	};

	TabularEditor.prototype.applyImport = function ( grid, headerRows, asText ) {
		if ( ! grid || ! grid.length ) { this.toast( t( 'noTableFound', 'No table content detected.' ) ); return; }

		var ncol = 0;
		grid.forEach( function ( r ) { if ( r.length > ncol ) { ncol = r.length; } } );
		ncol = Math.max( 1, Math.min( ncol, MAX_COLS ) );

		var cols = [];
		for ( var i = 0; i < ncol; i++ ) { cols.push( { name: 'default-col', align: '', width: '' } ); }

		var esc = asText ? IO.escapeHtml : function ( v ) { return v; };
		var content = grid.map( function ( row ) {
			var line = [];
			for ( var c = 0; c < ncol; c++ ) {
				line.push( { textarea: esc( row[ c ] != null ? row[ c ] : '' ), colspan: 1, rowspan: 1, merged: false } );
			}
			return line;
		} );

		this.model.cols = cols;
		this.model.content = content;
		this.model.header_options.header_rows = Math.min( Math.max( 0, headerRows || 0 ), content.length );
		this.model.header_options.footer_rows = 0;
		this.afterStructural();
		this.toast( t( 'imported', 'Imported %d rows.' ).replace( '%d', content.length ) );
	};

	TabularEditor.prototype.exportCsv = function () {
		IO.downloadCSV( IO.toCSV( this.model ), 'table.csv' );
	};

	TabularEditor.prototype.copyClipboard = function () {
		var self = this, tsv = IO.toTSV( this.model );
		function done() { self.toast( t( 'copied', 'Copied to clipboard.' ) ); }
		if ( navigator.clipboard && navigator.clipboard.writeText ) {
			navigator.clipboard.writeText( tsv ).then( done, function () { self.copyFallback( tsv, done ); } );
		} else {
			this.copyFallback( tsv, done );
		}
	};

	TabularEditor.prototype.copyFallback = function ( text, done ) {
		var $ta = $( '<textarea style="position:fixed;left:-9999px;top:0"></textarea>' ).val( text ).appendTo( document.body );
		$ta[ 0 ].select();
		try { document.execCommand( 'copy' ); done(); } catch ( e ) {}
		$ta.remove();
	};

	// --- modal -----------------------------------------------------------

	TabularEditor.prototype.showModal = function ( opts ) {
		var $ov = $( '<div class="fw-tabular-modal-ov"></div>' );
		var $box = $( '<div class="fw-tabular-modal"></div>' );
		$box.append( '<div class="fw-tabular-modal-head"><span>' + opts.title + '</span><button type="button" class="fw-tabular-modal-x dashicons dashicons-no-alt"></button></div>' );
		$box.append( $( '<div class="fw-tabular-modal-body"></div>' ).append( opts.body ) );

		var $foot = $( '<div class="fw-tabular-modal-foot"></div>' );
		var $cancel = $( '<button type="button" class="button">' + t( 'cancel', 'Cancel' ) + '</button>' );
		var $ok = $( '<button type="button" class="button button-primary">' + ( opts.confirm || t( 'import', 'Import' ) ) + '</button>' );
		$foot.append( $cancel, $ok );
		$box.append( $foot );
		$ov.append( $box ).appendTo( document.body );

		function close() { $ov.remove(); }
		$cancel.on( 'click', close );
		$box.find( '.fw-tabular-modal-x' ).on( 'click', close );
		$ov.on( 'mousedown', function ( e ) { if ( e.target === $ov[ 0 ] ) { close(); } } );
		$ok.on( 'click', function () { opts.onConfirm( close ); } );
		if ( opts.onShow ) { opts.onShow(); }
	};

	TabularEditor.prototype.importHtmlModal = function () {
		var self = this;
		var $box = $( '<div class="fw-tt-paste-box" contenteditable="true" data-ph="' + escAttr( t( 'pastePlaceholder', 'Paste your table here…' ) ) + '"></div>' );
		var note = $( '<p class="fw-tt-modal-note">' + t( 'pasteHtmlNote', 'Paste a table copied from Word, Google Docs, Excel or a web page, then click Import. Existing content is replaced.' ) + '</p>' );

		this.showModal( {
			title: t( 'pasteHtml', 'Paste HTML / Word table' ),
			body: $( '<div></div>' ).append( note, $box ),
			confirm: t( 'import', 'Import' ),
			onShow: function () { setTimeout( function () { $box.focus(); }, 60 ); },
			onConfirm: function ( close ) {
				var html = $box.html(), text = $box[ 0 ].innerText || '';
				var parsed = IO.parseHtmlTable( html );
				if ( parsed ) { self.applyImport( parsed.grid, parsed.headerRows, false ); close(); return; }
				var p = IO.parsePasted( html, text );
				if ( p ) { self.applyImport( p.grid, p.headerRows, p.asText ); close(); return; }
				var lines = text.split( /\r?\n/ ).filter( function ( l ) { return l.trim() !== ''; } );
				if ( lines.length ) { self.applyImport( lines.map( function ( l ) { return [ l ]; } ), 0, true ); close(); return; }
				self.toast( t( 'noTableFound', 'No table content detected.' ) );
			}
		} );
	};

	TabularEditor.prototype.importCsvModal = function () {
		var self = this;
		var $file = $( '<input type="file" class="fw-tt-file" accept=".csv,.tsv,.txt,text/csv">' );
		var $hdr = $( '<label class="fw-tt-modal-check"><input type="checkbox" checked> ' + t( 'firstRowHeader', 'First row is a header' ) + '</label>' );
		var note = $( '<p class="fw-tt-modal-note">' + t( 'csvNote', 'Choose a .csv exported from Excel or Google Sheets. The delimiter is auto-detected.' ) + '</p>' );

		this.showModal( {
			title: t( 'uploadCsv', 'Upload CSV' ),
			body: $( '<div></div>' ).append( note, $file, $hdr ),
			confirm: t( 'import', 'Import' ),
			onConfirm: function ( close ) {
				var f = $file[ 0 ].files && $file[ 0 ].files[ 0 ];
				if ( ! f ) { self.toast( t( 'chooseFile', 'Choose a file first.' ) ); return; }
				var reader = new FileReader();
				reader.onload = function () {
					var text = String( reader.result || '' );
					var grid = IO.parseDelimited( text, IO.detectDelimiter( text ) );
					self.applyImport( grid, $hdr.find( 'input' ).is( ':checked' ) ? 1 : 0, true );
					close();
				};
				reader.readAsText( f );
			}
		} );
	};

	// --- toast -----------------------------------------------------------

	TabularEditor.prototype.toast = function ( msg ) {
		var $t = $( '<div class="fw-tabular-toast"></div>' ).text( msg ).appendTo( document.body );
		setTimeout( function () { $t.addClass( 'show' ); }, 10 );
		setTimeout( function () { $t.removeClass( 'show' ); setTimeout( function () { $t.remove(); }, 300 ); }, 1700 );
	};

	// --- event binding ---------------------------------------------------

	TabularEditor.prototype.bind = function () {
		var self = this, $e = this.$editor;

		// live cell edits
		$e.on( 'input', '.fw-tt-cell', function () {
			var r = intval( $( this ).attr( 'data-row' ) ), c = intval( $( this ).attr( 'data-col' ) );
			if ( self.model.content[ r ] && self.model.content[ r ][ c ] ) {
				self.model.content[ r ][ c ].textarea = this.innerHTML;
				self.sync();
			}
		} );

		// cell selection (shift+click) for merge
		$e.on( 'mousedown', '.fw-tt-cell', function ( e ) {
			var r = intval( $( this ).attr( 'data-row' ) ), c = intval( $( this ).attr( 'data-col' ) );
			if ( e.shiftKey && self.anchor ) {
				e.preventDefault();
				self.sel = self.computeRect( self.anchor, { r: r, c: c } );
				self.paintSelection();
				if ( document.activeElement && document.activeElement.blur ) { document.activeElement.blur(); }
			} else {
				self.anchor = { r: r, c: c };
				if ( self.sel ) { self.sel = null; self.paintSelection(); }
			}
		} );

		// keyboard: Tab navigation, Enter -> <br>
		$e.on( 'keydown', '.fw-tt-cell', function ( e ) {
			if ( e.keyCode === 9 ) {
				e.preventDefault();
				var $cells = $e.find( '.fw-tt-cell' ), i = $cells.index( this ),
					next = $cells.eq( i + ( e.shiftKey ? -1 : 1 ) );
				if ( ! next.length ) { next = $cells.eq( e.shiftKey ? $cells.length - 1 : 0 ); }
				self.focusCell( next );
			} else if ( e.keyCode === 13 && ! e.shiftKey ) {
				e.preventDefault();
				document.execCommand( 'insertLineBreak' );
			}
		} );

		// smart paste (Excel / Sheets TSV, or HTML table) into the grid
		$e.on( 'paste', '.fw-tt-cell', function ( e ) {
			var cd = e.originalEvent.clipboardData || window.clipboardData;
			if ( ! cd || ! IO.parsePasted ) { return; }
			var parsed = IO.parsePasted( cd.getData( 'text/html' ), cd.getData( 'text/plain' ) );
			if ( parsed ) {
				e.preventDefault();
				self.fillGridAt( intval( $( this ).attr( 'data-row' ) ), intval( $( this ).attr( 'data-col' ) ), parsed.grid, parsed.asText );
			}
		} );

		// toolbar buttons
		$e.on( 'click', '[data-act="add-row"]', function ( e ) { e.preventDefault(); self.insertRowAt( self.model.content.length ); } );
		$e.on( 'click', '[data-act="add-col"]', function ( e ) { e.preventDefault(); self.insertColAt( self.model.cols.length ); } );
		$e.on( 'click', '[data-act="merge"]', function ( e ) { e.preventDefault(); self.mergeSelection(); } );
		$e.on( 'click', '[data-act="unmerge"]', function ( e ) { e.preventDefault(); self.unmergeSelection(); } );

		// header / footer counts
		$e.on( 'change', '.fw-tt-header-rows', function () {
			self.model.header_options.header_rows = Math.min( Math.max( 0, intval( this.value ) ), self.model.content.length );
			self.clampHeaderFooter();
			self.afterStructural();
		} );
		$e.on( 'change', '.fw-tt-footer-rows', function () {
			self.model.header_options.footer_rows = Math.max( 0, intval( this.value ) );
			self.clampHeaderFooter();
			self.afterStructural();
		} );

		// grid ▾ menus + toolbar import/export ▾
		$e.on( 'click', '.fw-tt-menu-btn, .fw-tt-toolbar-menu', function ( e ) {
			e.preventDefault();
			e.stopPropagation();
			var $btn = $( this ), kind = $btn.attr( 'data-menu' ),
				idx = intval( $btn.attr( kind === 'col' ? 'data-col' : 'data-row' ), 0 );
			if ( self.menuCtx && self.menuCtx.kind === kind && self.menuCtx.idx === idx && self.$menu.is( ':visible' ) ) {
				self.closeMenu();
			} else {
				self.openMenu( kind, idx, $btn );
			}
		} );

		// menu item dispatch
		this.$menu.on( 'click', '.fw-tabular-menu-item', function ( e ) {
			e.preventDefault();
			if ( ! self.menuCtx ) { return; }
			var act = $( this ).attr( 'data-act' ), i = self.menuCtx.idx;
			self.closeMenu();
			switch ( act ) {
				case 'ins-col-left': self.insertColAt( i ); break;
				case 'ins-col-right': self.insertColAt( i + 1 ); break;
				case 'dup-col': self.dupCol( i ); break;
				case 'move-col-left': self.moveCol( i, -1 ); break;
				case 'move-col-right': self.moveCol( i, 1 ); break;
				case 'align-left': self.setColAlign( i, 'left' ); break;
				case 'align-center': self.setColAlign( i, 'center' ); break;
				case 'align-right': self.setColAlign( i, 'right' ); break;
				case 'del-col': self.deleteCol( i ); break;
				case 'ins-row-above': self.insertRowAt( i ); break;
				case 'ins-row-below': self.insertRowAt( i + 1 ); break;
				case 'dup-row': self.dupRow( i ); break;
				case 'move-row-up': self.moveRow( i, -1 ); break;
				case 'move-row-down': self.moveRow( i, 1 ); break;
				case 'del-row': self.deleteRow( i ); break;
				case 'import-html': self.importHtmlModal(); break;
				case 'import-csv': self.importCsvModal(); break;
				case 'export-csv': self.exportCsv(); break;
				case 'export-copy': self.copyClipboard(); break;
			}
		} );
	};

	TabularEditor.prototype.focusCell = function ( $cell ) {
		if ( ! $cell || ! $cell.length ) { return; }
		var el = $cell[ 0 ];
		el.focus();
		var range = document.createRange();
		range.selectNodeContents( el );
		range.collapse( false );
		var sel = window.getSelection();
		sel.removeAllRanges();
		sel.addRange( range );
	};

	// --- purpose toggle (tabular <-> pricing) ----------------------------

	function applyPurpose( $root ) {
		var p = $root.find( '.fw-table-purpose-bar select' ).val() || 'tabular';
		$root.attr( 'data-table-purpose', p );
		$root.children( '.fw-table-editor-tabular' ).toggleClass( 'fw-table-editor-active', p !== 'pricing' );
		$root.children( '.fw-table-editor-pricing' ).toggleClass( 'fw-table-editor-active', p === 'pricing' );
	}

	$( document ).on( 'change', '.fw-option-type-table .fw-table-purpose-bar select', function () {
		applyPurpose( $( this ).closest( '.fw-option-type-table' ) );
	} );

	// --- boot ------------------------------------------------------------

	fwEvents.on( 'fw:options:init', function ( data ) {
		data.$elements.find( '.fw-option-type-table' ).each( function () { applyPurpose( $( this ) ); } );

		data.$elements.find( '.fw-tabular-editor:not(.fw-tabular-initialized)' ).each( function () {
			new TabularEditor( $( this ) );
		} ).addClass( 'fw-tabular-initialized' );
	} );

}( jQuery ) );
