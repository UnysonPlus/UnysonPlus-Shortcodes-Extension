/* global jQuery, upwShortcodesLibrary */
( function ( $ ) {
	'use strict';

	var DATA = window.upwShortcodesLibrary || {};
	var I18N = DATA.i18n || {};
	var items = DATA.items || [];

	var $grid, $search, $cat, $notice, $empty;

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

	function card( it ) {
		var installed = it.state === 'installed';
		var thumb = it.thumb_url
			? '<img class="upw-scl-card__thumb" src="' + esc( it.thumb_url ) + '" alt="" loading="lazy" />'
			: '<div class="upw-scl-card__thumb upw-scl-card__thumb--none"></div>';
		var btn = installed
			? '<button type="button" class="button upw-scl-card__btn" data-act="uninstall" data-slug="' + esc( it.slug ) + '">' + esc( I18N.remove || 'Remove' ) + '</button>'
			: '<button type="button" class="button button-primary upw-scl-card__btn" data-act="install" data-slug="' + esc( it.slug ) + '">' + esc( I18N.install || 'Install' ) + '</button>';
		var badge = installed ? '<span class="upw-scl-card__badge">' + esc( I18N.installed || 'Installed' ) + '</span>' : '';
		return '' +
			'<div class="upw-scl-card' + ( installed ? ' is-installed' : '' ) + '" data-slug="' + esc( it.slug ) + '" data-cat="' + esc( it.category ) + '">' +
				'<div class="upw-scl-card__media">' + thumb + badge + '</div>' +
				'<div class="upw-scl-card__body">' +
					'<h3 class="upw-scl-card__title">' + esc( it.title ) + '</h3>' +
					'<p class="upw-scl-card__desc">' + esc( it.description ) + '</p>' +
					'<div class="upw-scl-card__meta"><span>' + esc( it.category ) + '</span><span>v' + esc( it.version ) + '</span></div>' +
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
	}

	function post( scAction, slug, $btn ) {
		var busyLabel = scAction === 'install' ? ( I18N.installing || 'Installing…' ) : ( I18N.removing || 'Removing…' );
		if ( $btn ) { $btn.prop( 'disabled', true ).text( busyLabel ); }
		notice( '' );
		return $.post( DATA.ajaxUrl, {
			action: 'upw_sc_lib_manage',
			nonce: DATA.nonce,
			sc_action: scAction,
			slug: slug || ''
		} ).done( function ( res ) {
			if ( res && res.success && res.data ) {
				items = res.data.items || items;
				DATA.installed = res.data.installed || DATA.installed;
				renderCategories();
				render();
				if ( scAction === 'install' ) { notice( I18N.reload || 'Installed! Reload the Page Builder to use it.', 'success' ); }
			} else {
				notice( ( res && res.data && res.data.message ) || I18N.failed || 'Error', 'error' );
			}
		} ).fail( function () {
			notice( I18N.failed || 'Error', 'error' );
		} );
	}

	$( function () {
		$grid   = $( '#upw-scl-grid' );
		$search = $( '#upw-scl-search' );
		$cat    = $( '#upw-scl-category' );
		$notice = $( '#upw-scl-notice' );
		$empty  = $( '#upw-scl-empty' );

		renderCategories();
		render();
		if ( DATA.catalogOk === false ) { notice( I18N.offline || '', 'warning' ); }

		$search.on( 'input', render );
		$cat.on( 'change', render );

		$( document ).on( 'click', '.upw-scl-card__btn', function () {
			var $b = $( this );
			post( $b.data( 'act' ), $b.data( 'slug' ), $b );
		} );

		$( '.upw-scl__refresh' ).on( 'click', function () {
			var $b = $( this ).prop( 'disabled', true );
			post( 'refresh', '', null ).always( function () { $b.prop( 'disabled', false ); } );
		} );
	} );
}( jQuery ) );
