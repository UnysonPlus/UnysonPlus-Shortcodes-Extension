/**
 * Element Designs manager (Theme Settings → Components → Element Designs).
 *
 * Hydrates the [data-upw-eldesigns] mount into a card grid grouped by shortcode,
 * over the uploads catalog (design-library.php) via the fw_design_lib_manage AJAX
 * endpoint. Import (upload JSON) · Edit (name + scoped CSS) · Delete · Export ·
 * Browse Library (P4). Config in window.upwElDesigns.
 */
( function ( $ ) {
	'use strict';

	var CFG = window.upwElDesigns || { items: [], enabled: [], ajaxUrl: '', nonce: '', l10n: {} };
	var L   = CFG.l10n || {};
	var items = CFG.items || [];

	function t( k, d ) { return ( L && L[ k ] ) ? L[ k ] : d; }
	function esc( s ) {
		return String( s == null ? '' : s ).replace( /[&<>"']/g, function ( c ) {
			return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ c ];
		} );
	}
	function notify( msg ) { window.alert( msg ); }

	function post( data, done ) {
		$.post( CFG.ajaxUrl, $.extend( { action: 'fw_design_lib_manage', nonce: CFG.nonce }, data ) )
			.done( function ( res ) {
				if ( res && res.success ) { items = ( res.data && res.data.items ) || items; done && done( true ); }
				else { notify( ( res && res.data && res.data.message ) || t( 'error', 'Something went wrong.' ) ); done && done( false ); }
			} )
			.fail( function () { notify( t( 'error', 'Something went wrong.' ) ); done && done( false ); } );
	}

	function groupByShortcode( list ) {
		var g = {};
		$.each( list, function ( _, it ) { ( g[ it.shortcode ] = g[ it.shortcode ] || [] ).push( it ); } );
		return g;
	}

	function tileHtml( it ) {
		var thumb = it.thumb
			? '<span class="upw-eld-tile__thumb" style="background-image:url(\'' + esc( it.thumb ) + '\')"></span>'
			: '<span class="upw-eld-tile__thumb upw-eld-tile__thumb--none"></span>';
		return '<div class="upw-eld-tile" data-sc="' + esc( it.shortcode ) + '" data-id="' + esc( it.id ) + '">' +
				thumb +
				'<div class="upw-eld-tile__body">' +
					'<span class="upw-eld-tile__name">' + esc( it.name ) + '</span>' +
					'<div class="upw-eld-tile__actions">' +
						'<button type="button" class="button-link" data-act="edit">' + esc( t( 'edit', 'Edit' ) ) + '</button>' +
						'<button type="button" class="button-link" data-act="export">' + esc( t( 'export', 'Export' ) ) + '</button>' +
						'<button type="button" class="button-link upw-eld-danger" data-act="delete">' + esc( t( 'delete', 'Delete' ) ) + '</button>' +
					'</div>' +
				'</div>' +
			'</div>';
	}

	function render( $mount ) {
		var g = groupByShortcode( items );
		var html = '' +
			'<div class="upw-eld-toolbar">' +
				'<button type="button" class="button button-primary" data-eld="import">' + esc( t( 'import', 'Import design…' ) ) + '</button>' +
				'<button type="button" class="button" data-eld="browse">' + esc( t( 'browse', 'Browse Library' ) ) + '</button>' +
				'<input type="file" accept=".json,application/json" hidden data-eld-file>' +
			'</div>';

		var scs = Object.keys( g ).sort();
		if ( ! scs.length ) {
			html += '<p class="upw-eld-empty">' + esc( t( 'empty', 'No designs yet. Import one, or use Export current design inside an element.' ) ) + '</p>';
		}
		$.each( scs, function ( _, sc ) {
			html += '<div class="upw-eld-group"><h3 class="upw-eld-group__title">' + esc( sc ) + '</h3><div class="upw-eld-grid">';
			$.each( g[ sc ], function ( _, it ) { html += tileHtml( it ); } );
			html += '</div></div>';
		} );

		$mount.html( html );
	}

	function openEdit( it, $tile ) {
		var css = ( it.atts && it.atts.custom_css ) ? it.atts.custom_css : '';
		var $form = $(
			'<div class="upw-eld-edit">' +
				'<label>' + esc( t( 'name', 'Name' ) ) + '<input type="text" class="widefat" data-f="name" value="' + esc( it.name ) + '"></label>' +
				'<label>' + esc( t( 'css', 'Scoped CSS (use "selector")' ) ) + '<textarea class="widefat" rows="6" data-f="css">' + esc( css ) + '</textarea></label>' +
				'<div class="upw-eld-edit__actions">' +
					'<button type="button" class="button button-primary" data-e="save">' + esc( t( 'save', 'Save' ) ) + '</button> ' +
					'<button type="button" class="button" data-e="cancel">' + esc( t( 'cancel', 'Cancel' ) ) + '</button>' +
				'</div>' +
			'</div>'
		);
		$tile.append( $form );
		$form.on( 'click', '[data-e="cancel"]', function () { $form.remove(); } );
		$form.on( 'click', '[data-e="save"]', function () {
			post( {
				design_action: 'update',
				shortcode: it.shortcode,
				slug: it.id,
				name: $form.find( '[data-f="name"]' ).val(),
				custom_css: $form.find( '[data-f="css"]' ).val()
			}, function ( ok ) { if ( ok ) { boot(); } } );
		} );
	}

	function exportItem( it ) {
		var atts = $.extend( {}, it.atts || {} );
		delete atts.unique_id; delete atts.sc_design_panel;
		var env  = { unysonplus_design: 1, shortcode: it.shortcode, name: it.name, atts: atts };
		var blob = new Blob( [ JSON.stringify( env, null, 2 ) ], { type: 'application/json' } );
		var url  = window.URL.createObjectURL( blob );
		var a    = document.createElement( 'a' );
		a.href = url; a.download = ( it.id || it.shortcode ) + '.json';
		document.body.appendChild( a ); a.click(); document.body.removeChild( a );
		window.setTimeout( function () { window.URL.revokeObjectURL( url ); }, 1000 );
	}

	var $mountGlobal;
	function boot() {
		if ( $mountGlobal ) { render( $mountGlobal ); }
	}

	// Find the mount, render it, and bind its handlers. Idempotent: returns true
	// once wired, so it can be retried until the (lazily-rendered) mount exists.
	function initManager() {
		if ( $mountGlobal ) { return true; }
		var $mount = $( '[data-upw-eldesigns]' ).first();
		if ( ! $mount.length ) { return false; }
		$mountGlobal = $mount;
		render( $mount );

		// Toolbar: import (file) + browse (P4).
		$mount.on( 'click', '[data-eld="import"]', function () { $mount.find( '[data-eld-file]' ).trigger( 'click' ); } );
		$mount.on( 'change', '[data-eld-file]', function () {
			var f = this.files && this.files[ 0 ]; this.value = '';
			if ( ! f ) { return; }
			var r = new FileReader();
			r.onload = function () {
				post( { design_action: 'import', json: r.result }, function ( ok ) { if ( ok ) { boot(); } } );
			};
			r.readAsText( f );
		} );
		$mount.on( 'click', '[data-eld="browse"]', function () {
			notify( t( 'browseSoon', 'The remote Design Library is coming soon.' ) );
		} );

		// Per-tile actions.
		$mount.on( 'click', '.upw-eld-tile [data-act]', function () {
			var $tile = $( this ).closest( '.upw-eld-tile' );
			var it = null, id = $tile.data( 'id' ), sc = String( $tile.data( 'sc' ) );
			$.each( items, function ( _, x ) { if ( x.id === id && x.shortcode === sc ) { it = x; } } );
			if ( ! it ) { return; }
			var act = $( this ).data( 'act' );
			if ( act === 'delete' ) {
				if ( window.confirm( t( 'confirmDelete', 'Delete this design?' ) ) ) {
					post( { design_action: 'delete', shortcode: it.shortcode, slug: it.id }, function ( ok ) { if ( ok ) { boot(); } } );
				}
			} else if ( act === 'export' ) {
				exportItem( it );
			} else if ( act === 'edit' ) {
				if ( ! $tile.find( '.upw-eld-edit' ).length ) { openEdit( it, $tile ); }
			}
		} );
		return true;
	}

	$( function () {
		if ( initManager() ) { return; }
		// The Components → Element Designs sub-tab renders its mount LAZILY (only when
		// that sub-tab is first opened), so it isn't in the DOM at DOMReady and the
		// one-shot init above finds nothing. Watch for the mount to appear, then wire
		// it once and stop observing. (Fixes the permanent "Loading designs…".)
		if ( typeof window.MutationObserver === 'function' ) {
			var mo = new window.MutationObserver( function () {
				if ( initManager() ) { mo.disconnect(); }
			} );
			mo.observe( document.body, { childList: true, subtree: true } );
		}
	} );
} )( jQuery );
