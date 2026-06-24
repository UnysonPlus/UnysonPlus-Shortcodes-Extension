/* global jQuery, fwScSettings */
( function ( $ ) {
	'use strict';

	var cfg  = window.fwScSettings || {};
	var i18n = cfg.i18n || {};

	var $list, $count, $notice;

	function post( action, data, done, always ) {
		data = data || {};
		data.action = action;
		data.nonce  = cfg.nonce;

		$.post( cfg.ajaxurl, data )
			.done( function ( res ) {
				done( res );
			} )
			.fail( function () {
				done( { success: false, data: { message: i18n.genericError } } );
			} )
			.always( function () {
				if ( always ) {
					always();
				}
			} );
	}

	function notify( message, isError ) {
		$notice
			.removeClass( 'fw-sc-notice-hidden notice-success notice-error' )
			.addClass( isError ? 'notice-error' : 'notice-success' )
			.html( '<p>' + message + '</p>' );
	}

	function sprintf2( tpl, a, b ) {
		return String( tpl ).replace( '%1$d', a ).replace( '%2$d', b );
	}

	function updateCount() {
		var total   = $list.find( '.fw-sc-toggle' ).length;
		var enabled = $list.find( '.fw-sc-toggle:checked' ).length;
		$count.text( sprintf2( $count.data( 'template' ), enabled, total ) );
	}

	function filterList( term ) {
		term = ( term || '' ).toLowerCase().trim();
		var visible = 0;

		$list.find( '.fw-sc-item' ).each( function () {
			var match = term === '' || $( this ).data( 'search' ).indexOf( term ) !== -1;
			$( this ).toggle( match );
			if ( match ) {
				visible++;
			}
		} );

		$( '#fw-sc-empty' ).toggleClass( 'fw-sc-notice-hidden', visible !== 0 );
	}

	function save() {
		var tags = $list.find( '.fw-sc-toggle:checked' ).map( function () {
			return this.value;
		} ).get();

		var $btn = $( '#fw-sc-save' ).prop( 'disabled', true ).text( i18n.saving );

		post( 'fw_ext_shortcodes_save', { tags: tags }, function ( res ) {
			if ( res && res.success ) {
				notify( i18n.saved, false );
			} else {
				notify( ( res && res.data && res.data.message ) || i18n.saveError, true );
			}
		}, function () {
			$btn.prop( 'disabled', false ).text( $btn.data( 'label' ) );
		} );
	}

	function buildRow( data ) {
		var badgeClass = 'fw-sc-badge-' + ( data.source || 'uploaded' );
		var $li = $( '<li class="fw-sc-item fw-sc-item-new"></li>' )
			.attr( 'data-tag', data.tag )
			.attr( 'data-search', ( data.title + ' ' + data.tag ).toLowerCase() );

		$li.append(
			$( '<label class="fw-sc-item-label"></label>' ).append(
				$( '<input type="checkbox" class="fw-sc-toggle" checked />' ).val( data.tag ),
				$( '<span class="fw-sc-icon"></span>' ),
				$( '<span class="fw-sc-meta"></span>' ).append(
					$( '<span class="fw-sc-title"></span>' ).text( data.title ),
					$( '<code class="fw-sc-tag"></code>' ).text( '[' + data.tag + ']' )
				)
			),
			$( '<span class="fw-sc-badge"></span>' ).addClass( badgeClass ).text( data.badge ),
			$( '<button type="button" class="button-link fw-sc-delete"></button>' )
				.attr( 'data-tag', data.tag )
				.text( i18n.delete || 'Delete' )
		);

		return $li;
	}

	function installZip() {
		var input = document.getElementById( 'fw-sc-zip' );
		if ( ! input.files || ! input.files.length ) {
			notify( i18n.chooseZip, true );
			return;
		}

		var fd = new FormData();
		fd.append( 'action', 'fw_ext_shortcodes_install_zip' );
		fd.append( 'nonce', cfg.nonce );
		fd.append( 'shortcode_zip', input.files[0] );

		var $btn = $( '#fw-sc-install-zip' ).prop( 'disabled', true ).text( i18n.installing );

		$.ajax( {
			url: cfg.ajaxurl,
			method: 'POST',
			data: fd,
			processData: false,
			contentType: false
		} ).done( function ( res ) {
			afterInstall( res, input );
		} ).fail( function () {
			notify( i18n.genericError, true );
		} ).always( function () {
			$btn.prop( 'disabled', false ).text( $btn.data( 'label' ) );
		} );
	}

	function installGithub() {
		var url = $.trim( $( '#fw-sc-github' ).val() );
		if ( ! url ) {
			notify( i18n.enterUrl, true );
			return;
		}

		var $btn = $( '#fw-sc-install-github' ).prop( 'disabled', true ).text( i18n.installing );

		post( 'fw_ext_shortcodes_install_github', { github_url: url }, function ( res ) {
			afterInstall( res, null );
			if ( res && res.success ) {
				$( '#fw-sc-github' ).val( '' );
			}
		}, function () {
			$btn.prop( 'disabled', false ).text( $btn.data( 'label' ) );
		} );
	}

	function afterInstall( res, fileInput ) {
		if ( ! res || ! res.success ) {
			notify( ( res && res.data && res.data.message ) || i18n.genericError, true );
			return;
		}

		if ( fileInput ) {
			fileInput.value = '';
		}

		$list.append( buildRow( res.data ) );
		updateCount();
		filterList( $( '#fw-sc-search' ).val() );
		notify( i18n.reloadHint, false );
	}

	function del( $btn ) {
		fw.confirm( i18n.confirmDelete, function () {

		var tag = $btn.data( 'tag' );
		$btn.prop( 'disabled', true );

		post( 'fw_ext_shortcodes_delete', { tag: tag }, function ( res ) {
			if ( res && res.success ) {
				$btn.closest( '.fw-sc-item' ).remove();
				updateCount();
			} else {
				$btn.prop( 'disabled', false );
				notify( ( res && res.data && res.data.message ) || i18n.genericError, true );
			}
		} );
		} );
	}

	$( function () {
		$list   = $( '#fw-sc-list' );
		$count  = $( '#fw-sc-count' );
		$notice = $( '#fw-sc-notice' );

		// Remember original button labels for restore after async.
		$( '#fw-sc-save, #fw-sc-install-zip, #fw-sc-install-github' ).each( function () {
			$( this ).data( 'label', $( this ).text() );
		} );

		$( '#fw-sc-search' ).on( 'input', function () {
			filterList( this.value );
		} );

		$( '#fw-sc-enable-all' ).on( 'click', function () {
			$list.find( '.fw-sc-item:visible .fw-sc-toggle' ).prop( 'checked', true );
			updateCount();
		} );

		$( '#fw-sc-disable-all' ).on( 'click', function () {
			$list.find( '.fw-sc-item:visible .fw-sc-toggle' ).prop( 'checked', false );
			updateCount();
		} );

		$list.on( 'change', '.fw-sc-toggle', updateCount );
		$list.on( 'click', '.fw-sc-delete', function () {
			del( $( this ) );
		} );

		$( '#fw-sc-save' ).on( 'click', save );
		$( '#fw-sc-install-zip' ).on( 'click', installZip );
		$( '#fw-sc-install-github' ).on( 'click', installGithub );
	} );
}( jQuery ) );
