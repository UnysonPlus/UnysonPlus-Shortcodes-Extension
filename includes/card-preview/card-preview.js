/* global jQuery, _ */
/**
 * Shared card-rows LIVE PREVIEW engine.
 *
 * Draws a schematic wireframe of a card next to a Card Rows designer (an
 * addable-popup of { slots[], direction, justify, align }) and redraws it as the
 * rows/slots change. Reflects STRUCTURE — which slots, order, each row's direction
 * (inline / stacked), distribution and alignment — not real data. Pure client side.
 *
 * Used by any shortcode that renders a `[data-upwc-card-preview]` mount alongside a
 * `card_rows` addable-popup (e.g. wc_products, team_member). Event-independent: it
 * watches the DOM for the mount, finds the sibling Card Rows addable-popup, and reads
 * each row's JSON straight from its `.item` input. Redraws on the wrapper's bubbling
 * `change` (add / edit / delete / clone / sort) and on items-list mutations.
 */
( function ( $, _ ) {
	'use strict';
	if ( ! _ ) { return; }

	// Slot key → short recognizable placeholder label. Covers the common card slots
	// across shortcodes; an unknown key falls back to the key itself (neutral chip).
	var SLOT_LABEL = {
		media:        'Image',
		image:        'Image',
		title:        'Title',
		name:         'Full Name',
		job:          'Job Title',
		role:         'Role',
		excerpt:      'Short description text',
		desc:         'Short description text',
		bio:          'Short bio text',
		cats:         'Category',
		meta:         'Author · Date',
		readmore:     'Read more →',
		rating:       '★★★★☆',
		rating_count: '(12)',
		price:        '$29.00',
		cart:         'Add to Cart',
		button:       'Button',
		social:       'in  f  x',
		badges:       'SALE',
		wishlist:     '♥',
		quickview:    'Quick View',
		divider:      '———',
		// Testimonials slots
		avatar:       'Image',
		quote:        '“…testimonial…”',
		quotemark:    '❝',
		author:       'Name · Role',
		identity:     'Img · Name · Role',
		site:         'website.com'
	};
	var SLOT_KIND = {
		media: 'media', image: 'media', title: 'title', name: 'title', job: 'muted', role: 'muted',
		excerpt: 'excerpt', desc: 'excerpt', bio: 'excerpt', cats: 'ghost', meta: 'muted',
		readmore: 'ghost', rating: 'rating', rating_count: 'muted', price: 'price',
		cart: 'cart', button: 'cart', social: 'ghost', badges: 'badge', wishlist: 'heart',
		quickview: 'ghost', divider: 'divider',
		avatar: 'avatar', quote: 'excerpt', quotemark: 'ghost', author: 'title', identity: 'inline', site: 'ghost'
	};

	function chipEl( key ) {
		var kind  = SLOT_KIND[ key ] || 'other';
		var label = Object.prototype.hasOwnProperty.call(SLOT_LABEL, key) ? SLOT_LABEL[ key ] : String( key );
		return '<span class="upwc-cp-slot upwc-cp-slot--' + kind + '">' + fw.escapeHtml( label ) + '</span>';
	}
	function slotChip( key ) {
		// Composite "molecule" slots render as a mini-group so the preview mirrors the real card:
		// `author` = Name stacked over Role; `identity` = Image beside that stacked byline.
		if ( key === 'author' ) {
			return '<span class="upwc-cp-group upwc-cp-group--stack">' + chipEl( 'name' ) + chipEl( 'role' ) + '</span>';
		}
		if ( key === 'identity' ) {
			return '<span class="upwc-cp-group upwc-cp-group--inline">' + chipEl( 'avatar' )
				+ '<span class="upwc-cp-group upwc-cp-group--stack">' + chipEl( 'name' ) + chipEl( 'role' ) + '</span></span>';
		}
		return chipEl( key );
	}

	function rowHtml( row ) {
		var slots = ( row && Array.isArray( row.slots ) ) ? row.slots : [];
		var dir   = ( row && row.direction === 'stack' ) ? 'stack' : 'inline';
		var just  = ( row && row.justify ) ? String( row.justify ) : 'start';
		var align = ( row && row.align ) ? String( row.align ) : 'center';
		var chips = slots.length ? slots.map( slotChip ).join( '' ) : '<span class="upwc-cp-emptyrow">empty row</span>';
		return '<div class="upwc-cp-row upwc-cp-dir-' + dir + ' upwc-cp-just-' + just + ' upwc-cp-align-' + align + '">' + chips + '</div>';
	}

	function render( $mount, rows ) {
		if ( ! Array.isArray( rows ) || ! rows.length ) {
			$mount.html( '<div class="upwc-cp-card"><div class="upwc-cp-emptycard">Add a row to see the card.</div></div>' );
			return;
		}
		$mount.html( '<div class="upwc-cp-card">' + rows.map( rowHtml ).join( '' ) + '</div>' );
	}

	// Read the rows straight from the addable-popup items (each `.item` input holds
	// its row as JSON).
	function readRows( $wrapper ) {
		var rows = [];
		$wrapper.find( '.items-wrapper > .item' ).each( function () {
			var $inp = $( this ).find( 'input' ).first();
			if ( ! $inp.length ) { return; }
			try {
				var r = JSON.parse( $inp.val() );
				if ( r && typeof r === 'object' ) { rows.push( r ); }
			} catch ( e ) { /* skip a malformed row */ }
		} );
		return rows;
	}

	// The Card Rows addable-popup wrapper, scoped to the mount's form/modal. The mount
	// may name its rows option other than "card_rows" via data-cp-rows.
	function findWrapper( $mount ) {
		var rowsName = $mount.attr( 'data-cp-rows' ) || 'card_rows';
		var $scope   = $mount.closest( '.fw-modal, .fw-backend-options, form' );
		if ( ! $scope.length ) { $scope = $( document ); }
		var $aps   = $scope.find( '.fw-option-type-addable-popup' );
		var $named = $aps.filter( function () {
			return $( this ).find( 'input[name*="' + rowsName + '"]' ).length > 0;
		} ).first();
		return $named.length ? $named : $aps.first();
	}

	function wireMount( mountEl ) {
		var $mount = $( mountEl );
		if ( $mount.data( 'upwcWired' ) ) { return; }

		var $wrapper = findWrapper( $mount );
		if ( ! $wrapper.length ) { return; } // rows not in the DOM yet — try again later.

		$mount.data( 'upwcWired', true );

		var rerender = fw.debounce( function () { render( $mount, readRows( $wrapper ) ); }, 80 );
		rerender();

		$wrapper.closest( '.fw-modal, .fw-backend-options, form' ).on( 'change', rerender );
		var iw = $wrapper.find( '.items-wrapper' ).get( 0 );
		if ( iw && window.MutationObserver ) {
			new window.MutationObserver( rerender ).observe( iw, {
				childList: true, subtree: true, attributes: true, characterData: true
			} );
		}
	}

	function scan() {
		$( '[data-upwc-card-preview]' ).each( function () { wireMount( this ); } );
	}

	$( function () {
		scan();
		if ( window.MutationObserver ) {
			new window.MutationObserver( function () { scan(); } )
				.observe( document.body, { childList: true, subtree: true } );
		}
		var ticks = 0, iv = window.setInterval( function () {
			scan();
			if ( ++ticks > 40 ) { window.clearInterval( iv ); }
		}, 500 );
	} );
}( jQuery, window._ ) );
