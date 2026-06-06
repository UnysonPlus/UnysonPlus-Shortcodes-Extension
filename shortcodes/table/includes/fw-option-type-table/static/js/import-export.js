/**
 * Pure import / export helpers for the tabular table editor.
 * No DOM/editor state here — just parsing + serialization, exposed on
 * window.FwTabularIO so tabular-editor.js can stay focused on UI.
 */
( function ( window ) {

	// Inline HTML kept from pasted / imported content. Mirrors the server-side
	// wp_kses allowlist in FW_Option_Type_Table::allowed_cell_html().
	var ALLOWED = {
		A: [ 'href', 'title', 'target', 'rel' ],
		STRONG: [], B: [], EM: [], I: [], U: [], S: [],
		BR: [], SPAN: [ 'style' ], SUP: [], SUB: [], SMALL: [], CODE: []
	};

	function escapeHtml( s ) {
		return String( s == null ? '' : s )
			.replace( /&/g, '&amp;' )
			.replace( /</g, '&lt;' )
			.replace( />/g, '&gt;' );
	}

	/**
	 * Reduce arbitrary pasted HTML to the inline allowlist. Unknown tags are
	 * unwrapped (their text kept); scripts/styles/event handlers dropped.
	 */
	function sanitizeInline( html ) {
		var doc = new DOMParser().parseFromString( '<div id="r">' + html + '</div>', 'text/html' );
		var root = doc.getElementById( 'r' );

		( function clean( node ) {
			var kids = Array.prototype.slice.call( node.childNodes );
			kids.forEach( function ( ch ) {
				if ( ch.nodeType === 3 ) { return; } // text node
				if ( ch.nodeType !== 1 ) { ch.parentNode.removeChild( ch ); return; }

				var tag = ch.tagName;

				if ( tag === 'SCRIPT' || tag === 'STYLE' ) { ch.parentNode.removeChild( ch ); return; }
				if ( tag === 'BR' ) { return; }

				if ( ! ALLOWED.hasOwnProperty( tag ) ) {
					// keep contents, drop the wrapper
					clean( ch );
					while ( ch.firstChild ) { node.insertBefore( ch.firstChild, ch ); }
					node.removeChild( ch );
					return;
				}

				Array.prototype.slice.call( ch.attributes ).forEach( function ( a ) {
					var name = a.name.toLowerCase();
					if ( /^on/.test( name ) || ALLOWED[ tag ].indexOf( name ) === -1 ) {
						ch.removeAttribute( a.name );
					}
				} );
				// strip javascript: hrefs
				if ( tag === 'A' && /^\s*javascript:/i.test( ch.getAttribute( 'href' ) || '' ) ) {
					ch.removeAttribute( 'href' );
				}
				clean( ch );
			} );
		} )( root );

		return root.innerHTML.replace( / /g, ' ' ).trim();
	}

	/** Plain-text content of a cell HTML string (for CSV/TSV export). */
	function cellText( html ) {
		var d = document.createElement( 'div' );
		d.innerHTML = String( html == null ? '' : html ).replace( /<br\s*\/?>/gi, '\n' );
		return ( d.textContent || '' ).replace( / /g, ' ' );
	}

	/** Guess the most likely delimiter from the first line. */
	function detectDelimiter( text ) {
		var line = text.split( /\r?\n/ )[ 0 ] || '';
		var counts = { ',': 0, ';': 0, '\t': 0, '|': 0 };
		var inQ = false;
		for ( var i = 0; i < line.length; i++ ) {
			var ch = line[ i ];
			if ( ch === '"' ) { inQ = ! inQ; }
			else if ( ! inQ && counts.hasOwnProperty( ch ) ) { counts[ ch ]++; }
		}
		var best = ',', max = -1;
		for ( var d in counts ) {
			if ( counts.hasOwnProperty( d ) && counts[ d ] > max ) { max = counts[ d ]; best = d; }
		}
		return best;
	}

	/**
	 * RFC-4180-ish delimited parser (handles quoted fields, "" escapes, newlines
	 * inside quotes). Returns a 2D array of PLAIN-TEXT strings.
	 */
	function parseDelimited( text, delim ) {
		text = String( text ).replace( /\r\n/g, '\n' ).replace( /\r/g, '\n' );
		var rows = [], row = [], field = '', i = 0, inQ = false;

		while ( i < text.length ) {
			var ch = text[ i ];
			if ( inQ ) {
				if ( ch === '"' ) {
					if ( text[ i + 1 ] === '"' ) { field += '"'; i += 2; continue; }
					inQ = false; i++; continue;
				}
				field += ch; i++; continue;
			}
			if ( ch === '"' ) { inQ = true; i++; continue; }
			if ( ch === delim ) { row.push( field ); field = ''; i++; continue; }
			if ( ch === '\n' ) { row.push( field ); rows.push( row ); row = []; field = ''; i++; continue; }
			field += ch; i++;
		}
		if ( field !== '' || row.length ) { row.push( field ); rows.push( row ); }

		// drop a single trailing empty row (file ending in newline)
		if ( rows.length > 1 ) {
			var last = rows[ rows.length - 1 ];
			if ( last.length === 1 && last[ 0 ] === '' ) { rows.pop(); }
		}
		return rows;
	}

	/**
	 * Parse the first <table> out of an HTML string (Word / Docs / web page).
	 * colspan is expanded into empty placeholder cells; rowspan is flattened
	 * (kept on the originating row only) to keep import predictable.
	 * Returns { grid: [[html,…]], headerRows:int } or null.
	 */
	function parseHtmlTable( html ) {
		var doc = new DOMParser().parseFromString( html, 'text/html' );
		var table = doc.querySelector( 'table' );
		if ( ! table ) { return null; }

		var trs = Array.prototype.slice.call( table.querySelectorAll( 'tr' ) );
		if ( ! trs.length ) { return null; }

		var rowsInfo = [];
		trs.forEach( function ( tr ) {
			var cells = [], allTh = true, hasCell = false;
			Array.prototype.slice.call( tr.children ).forEach( function ( td ) {
				if ( td.tagName !== 'TD' && td.tagName !== 'TH' ) { return; }
				hasCell = true;
				if ( td.tagName !== 'TH' ) { allTh = false; }
				var colspan = parseInt( td.getAttribute( 'colspan' ) || '1', 10 ) || 1;
				cells.push( sanitizeInline( td.innerHTML ) );
				for ( var k = 1; k < colspan; k++ ) { cells.push( '' ); }
			} );
			if ( ! hasCell ) { return; }
			var inThead = !! ( tr.closest && tr.closest( 'thead' ) );
			rowsInfo.push( { cells: cells, header: inThead || allTh } );
		} );

		if ( ! rowsInfo.length ) { return null; }

		var ncol = 0;
		rowsInfo.forEach( function ( ri ) { if ( ri.cells.length > ncol ) { ncol = ri.cells.length; } } );

		var grid = rowsInfo.map( function ( ri ) {
			while ( ri.cells.length < ncol ) { ri.cells.push( '' ); }
			return ri.cells;
		} );

		var headerRows = 0;
		for ( var r = 0; r < rowsInfo.length; r++ ) {
			if ( rowsInfo[ r ].header ) { headerRows++; } else { break; }
		}

		return { grid: grid, headerRows: headerRows };
	}

	/**
	 * Smart paste: detect whether clipboard payload is a multi-cell table
	 * (HTML table or tab/newline-delimited text). Returns { grid, headerRows,
	 * asText } or null when it's just a single value (let the browser handle it).
	 */
	function parsePasted( html, text ) {
		if ( html && /<table[\s>]/i.test( html ) ) {
			var parsed = parseHtmlTable( html );
			if ( parsed && ( parsed.grid.length > 1 || ( parsed.grid[ 0 ] && parsed.grid[ 0 ].length > 1 ) ) ) {
				parsed.asText = false;
				return parsed;
			}
		}
		if ( text && ( text.indexOf( '\t' ) !== -1 || /\n/.test( text.trim() ) ) ) {
			var delim = text.indexOf( '\t' ) !== -1 ? '\t' : detectDelimiter( text );
			var grid = parseDelimited( text, delim );
			if ( grid.length > 1 || ( grid[ 0 ] && grid[ 0 ].length > 1 ) ) {
				return { grid: grid, headerRows: 0, asText: true };
			}
		}
		return null;
	}

	function csvField( v ) {
		return /[",\n]/.test( v ) ? '"' + v.replace( /"/g, '""' ) + '"' : v;
	}

	function toCSV( model ) {
		return model.content.map( function ( row ) {
			return row.map( function ( cell ) {
				return csvField( cell.merged ? '' : cellText( cell.textarea ).replace( /\n/g, ' ' ).trim() );
			} ).join( ',' );
		} ).join( '\r\n' );
	}

	function toTSV( model ) {
		return model.content.map( function ( row ) {
			return row.map( function ( cell ) {
				return cell.merged ? '' : cellText( cell.textarea ).replace( /[\t\n]/g, ' ' ).trim();
			} ).join( '\t' );
		} ).join( '\n' );
	}

	function downloadCSV( csv, filename ) {
		var blob = new Blob( [ '﻿' + csv ], { type: 'text/csv;charset=utf-8;' } );
		var url = URL.createObjectURL( blob );
		var a = document.createElement( 'a' );
		a.href = url;
		a.download = filename || 'table.csv';
		document.body.appendChild( a );
		a.click();
		document.body.removeChild( a );
		setTimeout( function () { URL.revokeObjectURL( url ); }, 1000 );
	}

	window.FwTabularIO = {
		escapeHtml: escapeHtml,
		sanitizeInline: sanitizeInline,
		cellText: cellText,
		detectDelimiter: detectDelimiter,
		parseDelimited: parseDelimited,
		parseHtmlTable: parseHtmlTable,
		parsePasted: parsePasted,
		toCSV: toCSV,
		toTSV: toTSV,
		downloadCSV: downloadCSV
	};

}( window ) );
