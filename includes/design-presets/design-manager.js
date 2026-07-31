/**
 * Element Designs — builder-side picker.
 *
 * The Presets tab (a native html-full panel) shows a tile grid of designs available
 * for THIS shortcode, sourced from window.scDesignManager.designs[shortcode] (localized
 * from the uploads catalog). Clicking a tile applies the design via the builder's proven
 * value path — item.set('atts', merged) + modal.set('values', merged, {silent:true}) —
 * so every option populates its tab and stays editable. "Export current design" saves the
 * element's current values as a shareable .json. Import / browse / edit / delete live in
 * Theme Settings → Components → Element Designs.
 */
( function ( $, fwe, _ ) {
	'use strict';

	var CFG = window.scDesignManager || { schema: 1, enabled: [], designs: {}, l10n: {} };
	var L   = CFG.l10n || {};

	function notify( msg, type ) {
		if ( window.fw && _.isFunction( window.fw.notify ) ) { window.fw.notify( msg, type || 'success' ); }
		else { window.alert( msg ); }
	}

	function esc( s ) {
		return String( s == null ? '' : s ).replace( /[&<>"']/g, function ( c ) {
			return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ c ];
		} );
	}

	// --- Merge a design's values into the element + refresh the open modal. ---
	function applyValues( atts, modal, item ) {
		if ( ! _.isObject( atts ) ) { return; }
		var cur    = _.clone( item.get( 'atts' ) || {} );
		var merged = _.clone( cur );
		_.each( atts, function ( val, key ) {
			if ( key === 'unique_id' ) { return; }          // never overwrite the instance id
			if ( _.has( cur, key ) ) { merged[ key ] = val; } // whitelist to this element's known options
		} );
		item.set( 'atts', merged );
		if ( modal && _.isFunction( modal.set ) ) { modal.set( 'values', _.clone( merged ), { silent: true } ); }
		notify( L.applied || 'Design applied.' );
	}

	// --- Export the element's current values as a design envelope (download). ---
	function exportDesign( item, shortcode ) {
		var atts = _.clone( item.get( 'atts' ) || {} );
		delete atts.unique_id;        // don't bake the instance id into a shareable design
		delete atts.sc_design_panel;  // the Presets-tab html option carries no real value
		var name = window.prompt( L.namePrompt || 'Name this design:', '' ) || '';
		var env  = { unysonplus_design: CFG.schema || 1, shortcode: shortcode, name: name, atts: atts };
		var slug = ( name || shortcode ).toString().toLowerCase().replace( /[^a-z0-9]+/g, '-' ).replace( /^-+|-+$/g, '' ) || shortcode;
		downloadJSON( env, slug + '.json' );
		notify( L.exported || 'Design exported.' );
	}

	function downloadJSON( obj, filename ) {
		var blob = new Blob( [ JSON.stringify( obj, null, 2 ) ], { type: 'application/json' } );
		var url  = window.URL.createObjectURL( blob );
		var a    = document.createElement( 'a' );
		a.href = url; a.download = filename;
		document.body.appendChild( a ); a.click(); document.body.removeChild( a );
		window.setTimeout( function () { window.URL.revokeObjectURL( url ); }, 1000 );
	}

	// --- Build the tile grid for a shortcode's designs. ---
	function renderGrid( $grid, designs ) {
		var html = '';
		_.each( designs, function ( d ) {
			var thumb = d.thumb
				? '<span class="sc-design-tile__thumb" style="background-image:url(\'' + esc( d.thumb ) + '\')"></span>'
				: '<span class="sc-design-tile__thumb sc-design-tile__thumb--none"></span>';
			html += '<button type="button" class="sc-design-tile" data-design-id="' + esc( d.id ) + '">' +
						thumb +
						'<span class="sc-design-tile__name">' + esc( d.name ) + '</span>' +
					'</button>';
		} );
		$grid.html( html );
	}

	// --- Wire the native Presets-tab panel (rendered by the html-full option). ---
	function wirePanel( data ) {
		try {
			var shortcode = data && data.shortcode;
			var modal     = data && data.modal;
			var item      = data && data.item;
			if ( ! modal || ! item ) { return; }

			var $frame = ( modal.frame && modal.frame.$el ) ? modal.frame.$el : null;
			if ( ! $frame || ! $frame.length ) { return; }
			var $panel = $frame.find( '.sc-design-panel' ).first();
			if ( ! $panel.length ) { return; }             // no Presets tab on this element
			if ( $panel.data( 'scWired' ) ) { return; }    // already wired (per rendered DOM)
			$panel.data( 'scWired', true );

			var designs = ( CFG.designs && CFG.designs[ shortcode ] ) ? CFG.designs[ shortcode ] : [];
			var $grid   = $panel.find( '[data-sc-design-grid]' );
			var $empty  = $panel.find( '[data-sc-design-empty]' );

			if ( designs.length ) {
				renderGrid( $grid, designs );
				$grid.show(); $empty.attr( 'hidden', true );
			} else {
				$grid.hide(); $empty.removeAttr( 'hidden' );
			}

			// Apply on tile click.
			$panel.on( 'click', '.sc-design-tile', function ( e ) {
				e.preventDefault();
				var id = $( this ).attr( 'data-design-id' );
				var d  = _.findWhere( designs, { id: id } );
				if ( d ) { applyValues( d.atts, modal, item ); }
			} );
			// Export current.
			$panel.on( 'click', '[data-act="export"]', function ( e ) {
				e.preventDefault();
				exportDesign( item, shortcode );
			} );
		} catch ( err ) {
			if ( window.console ) { window.console.error( '[sc-design] wirePanel', err ); }
		}
	}

	fwe.on(
		'fw:builder-type:page-builder:item-type:simple:options-modal:open ' +
		'fw:builder-type:page-builder:item-type:simple:options-modal:render',
		wirePanel
	);
} )( jQuery, fwEvents, _ );
