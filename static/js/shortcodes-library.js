/* global jQuery, upwShortcodesLibrary */
( function ( $ ) {
	'use strict';

	var DATA = window.upwShortcodesLibrary || {};
	var I18N = DATA.i18n || {};
	var items = DATA.items || [];

	var $grid, $search, $cat, $notice, $empty, $updates, $updateAll;

	function esc( s ) {
		return String( s == null ? '' : s ).replace( /[&<>"']/g, function ( c ) {
			return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ c ];
		} );
	}

	function notice( msg, kind ) {
		if ( ! msg ) { $notice.attr( 'hidden', true ).empty(); return; }
		$notice.removeAttr( 'hidden' )
			.attr( 'class', 'upw-scl__notice upw-scl__notice--' + ( kind || 'info' ) )
			.text( msg );
	}

	function categories() {
		var set = {};
		items.forEach( function ( it ) { if ( it.category ) { set[ it.category ] = true; } } );
		return Object.keys( set ).sort();
	}

	function renderCategories() {
		var opts = '<option value="">' + esc( I18N.allCats || 'All categories' ) + '</option>';
		categories().forEach( function ( c ) { opts += '<option value="' + esc( c ) + '">' + esc( c ) + '</option>'; } );
		$cat.html( opts );
	}

	// Slugs with a newer version available.
	function updateSlugs() {
		return items.filter( function ( it ) { return it.state === 'update'; } )
			.map( function ( it ) { return it.slug; } );
	}

	// Show/hide the "N updates" badge + "Update all" button in the toolbar.
	function renderUpdatesBadge() {
		var n = updateSlugs().length;
		if ( n > 0 ) {
			$updates.text( n + ' ' + ( n === 1 ? ( I18N.update || 'Update' ) : ( ( I18N.update || 'Update' ) + 's' ) ) )
				.removeAttr( 'hidden' );
			$updateAll.removeAttr( 'hidden' );
		} else {
			$updates.attr( 'hidden', true ).empty();
			$updateAll.attr( 'hidden', true );
		}
	}

	function card( it ) {
		var isUpdate    = it.state === 'update';
		var isInstalled = it.state === 'installed' || isUpdate;
		var thumb = it.thumb_url
			? '<img class="upw-scl-card__thumb" src="' + esc( it.thumb_url ) + '" alt="" loading="lazy" />'
			: '<div class="upw-scl-card__thumb upw-scl-card__thumb--none"></div>';

		var btn;
		if ( isUpdate ) {
			btn = '<button type="button" class="button button-primary upw-scl-card__btn" data-act="update" data-slug="' + esc( it.slug ) + '">' + esc( I18N.update || 'Update' ) + '</button>' +
				'<button type="button" class="button upw-scl-card__btn upw-scl-card__btn--sub" data-act="uninstall" data-slug="' + esc( it.slug ) + '">' + esc( I18N.remove || 'Remove' ) + '</button>';
		} else if ( isInstalled ) {
			btn = '<button type="button" class="button upw-scl-card__btn" data-act="uninstall" data-slug="' + esc( it.slug ) + '">' + esc( I18N.remove || 'Remove' ) + '</button>';
		} else {
			btn = '<button type="button" class="button button-primary upw-scl-card__btn" data-act="install" data-slug="' + esc( it.slug ) + '">' + esc( I18N.install || 'Install' ) + '</button>';
		}

		var badge = '';
		if ( isUpdate ) {
			badge = '<span class="upw-scl-card__badge upw-scl-card__badge--update">' + esc( I18N.updateReady || 'Update available' ) + '</span>';
		} else if ( isInstalled ) {
			badge = '<span class="upw-scl-card__badge">' + esc( I18N.installed || 'Installed' ) + '</span>';
		}

		// Version line: on an update, show "vINSTALLED → vNEW"; otherwise just the catalog version.
		var versionText = isUpdate && it.installed_version
			? 'v' + esc( it.installed_version ) + ' → v' + esc( it.version )
			: 'v' + esc( it.version );

		var cls = 'upw-scl-card' + ( isUpdate ? ' has-update' : ( isInstalled ? ' is-installed' : '' ) );
		return '' +
			'<div class="' + cls + '" data-slug="' + esc( it.slug ) + '" data-cat="' + esc( it.category ) + '">' +
				'<div class="upw-scl-card__media">' + thumb + badge + '</div>' +
				'<div class="upw-scl-card__body">' +
					'<h3 class="upw-scl-card__title">' + esc( it.title ) + '</h3>' +
					'<p class="upw-scl-card__desc">' + esc( it.description ) + '</p>' +
					'<div class="upw-scl-card__meta"><span>' + esc( it.category ) + '</span><span class="upw-scl-card__ver">' + versionText + '</span></div>' +
					'<div class="upw-scl-card__actions">' + btn + '</div>' +
				'</div>' +
			'</div>';
	}

	function render() {
		var q = ( $search.val() || '' ).toLowerCase().trim();
		var cat = $cat.val() || '';
		var shown = items.filter( function ( it ) {
			if ( cat && it.category !== cat ) { return false; }
			if ( q && ( it.title + ' ' + it.description ).toLowerCase().indexOf( q ) === -1 ) { return false; }
			return true;
		} );
		$grid.html( shown.map( card ).join( '' ) );
		$empty.attr( 'hidden', shown.length > 0 );
		renderUpdatesBadge();
	}

	// Apply the server response (fresh items/installed/updates) and re-render.
	function apply( res ) {
		if ( res && res.success && res.data ) {
			items = res.data.items || items;
			DATA.installed = res.data.installed || DATA.installed;
			DATA.updates = res.data.updates || 0;
			renderCategories();
			render();
			return true;
		}
		notice( ( res && res.data && res.data.message ) || I18N.failed || 'Error', 'error' );
		return false;
	}

	function busyLabel( scAction ) {
		if ( scAction === 'install' ) { return I18N.installing || 'Installing…'; }
		if ( scAction === 'update' )  { return I18N.updating || 'Updating…'; }
		return I18N.removing || 'Removing…';
	}

	function post( scAction, slug, $btn ) {
		if ( $btn ) { $btn.prop( 'disabled', true ).text( busyLabel( scAction ) ); }
		notice( '' );
		return $.post( DATA.ajaxUrl, {
			action: 'upw_sc_lib_manage',
			nonce: DATA.nonce,
			sc_action: scAction,
			slug: slug || ''
		} ).done( function ( res ) {
			if ( apply( res ) ) {
				if ( scAction === 'install' ) { notice( I18N.reload || 'Installed! Reload the Page Builder to use it.', 'success' ); }
				else if ( scAction === 'update' ) { notice( I18N.updatedMsg || 'Updated! Reload the Page Builder to use the new version.', 'success' ); }
			}
		} ).fail( function () {
			notice( I18N.failed || 'Error', 'error' );
		} );
	}

	// Update every outdated shortcode, one after another.
	function updateAll() {
		var slugs = updateSlugs();
		if ( ! slugs.length ) { return; }
		$updateAll.prop( 'disabled', true ).text( I18N.updating || 'Updating…' );
		notice( '' );
		( function next( i ) {
			if ( i >= slugs.length ) {
				$updateAll.prop( 'disabled', false ).text( I18N.update ? ( I18N.update + ' all' ) : 'Update all' );
				notice( I18N.updatedMsg || 'Updated! Reload the Page Builder to use the new version.', 'success' );
				return;
			}
			$.post( DATA.ajaxUrl, {
				action: 'upw_sc_lib_manage',
				nonce: DATA.nonce,
				sc_action: 'update',
				slug: slugs[ i ]
			} ).done( function ( res ) { apply( res ); } ).always( function () { next( i + 1 ); } );
		}( 0 ) );
	}

	$( function () {
		$grid      = $( '#upw-scl-grid' );
		$search    = $( '#upw-scl-search' );
		$cat       = $( '#upw-scl-category' );
		$notice    = $( '#upw-scl-notice' );
		$empty     = $( '#upw-scl-empty' );
		$updates   = $( '#upw-scl-updates' );
		$updateAll = $( '#upw-scl-update-all' );

		renderCategories();
		render();
		if ( DATA.catalogOk === false ) { notice( I18N.offline || '', 'warning' ); }

		$search.on( 'input', render );
		$cat.on( 'change', render );

		$( document ).on( 'click', '.upw-scl-card__btn', function () {
			var $b = $( this );
			post( $b.data( 'act' ), $b.data( 'slug' ), $b );
		} );

		$updateAll.on( 'click', updateAll );

		$( '.upw-scl__refresh' ).on( 'click', function () {
			var $b = $( this ).prop( 'disabled', true );
			post( 'refresh', '', null ).always( function () { $b.prop( 'disabled', false ); } );
		} );
	} );
}( jQuery ) );
