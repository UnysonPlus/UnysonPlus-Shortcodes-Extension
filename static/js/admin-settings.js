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

	function installZip( confirmReplace ) {
		var input = document.getElementById( 'fw-sc-zip' );
		if ( ! input.files || ! input.files.length ) {
			notify( i18n.chooseZip, true );
			return;
		}

		var fd = new FormData();
		fd.append( 'action', 'fw_ext_shortcodes_install_zip' );
		fd.append( 'nonce', cfg.nonce );
		fd.append( 'shortcode_zip', input.files[0] );
		if ( confirmReplace ) { fd.append( 'confirm_replace', '1' ); }

		var $btn = $( '#fw-sc-install-zip' ).prop( 'disabled', true ).text( i18n.installing );

		$.ajax( {
			url: cfg.ajaxurl,
			method: 'POST',
			data: fd,
			processData: false,
			contentType: false
		} ).done( function ( res ) {
			afterInstall( res, input, function () { installZip( true ); } );
		} ).fail( function () {
			notify( i18n.genericError, true );
		} ).always( function () {
			$btn.prop( 'disabled', false ).text( $btn.data( 'label' ) );
		} );
	}

	function installGithub( confirmReplace ) {
		var url = ( $( '#fw-sc-github' ).val() || '' ).trim();
		if ( ! url ) {
			notify( i18n.enterUrl, true );
			return;
		}

		var $btn = $( '#fw-sc-install-github' ).prop( 'disabled', true ).text( i18n.installing );

		var data = { github_url: url };
		if ( confirmReplace ) { data.confirm_replace = 1; }

		post( 'fw_ext_shortcodes_install_github', data, function ( res ) {
			afterInstall( res, null, function () { installGithub( true ); } );
		}, function () {
			$btn.prop( 'disabled', false ).text( $btn.data( 'label' ) );
		} );
	}

	function afterInstall( res, fileInput, retryReplace ) {
		if ( ! res || ! res.success ) {
			notify( ( res && res.data && res.data.message ) || i18n.genericError, true );
			return;
		}

		// A design pack that would OVERWRITE an existing one — confirm (showing both
		// versions), then re-upload with the replace flag. Keep the file input intact
		// so the retry can re-send it.
		if ( res.data && res.data.needsConfirm ) {
			fw.confirm( res.data.confirmMessage, function () {
				if ( retryReplace ) { retryReplace(); }
			} );
			return;
		}

		if ( fileInput ) {
			fileInput.value = '';
		}
		$( '#fw-sc-github' ).val( '' );

		// Design pack installed/updated: no shortcode row (it lands in the element's
		// Design picker). Reflect the new version on its manager card if visible.
		if ( res.data && res.data.kind === 'design' ) {
			updateDesignCard( res.data );
			notify( res.data.message || i18n.reloadHint, false );
			return;
		}

		$list.append( buildRow( res.data ) );
		updateCount();
		filterList( $( '#fw-sc-search' ).val() );
		notify( i18n.reloadHint, false );
	}

	// Update a design pack's manager card (version text) in place after an update, so
	// no reload is needed. A brand-new pack's card only appears on the next reload.
	function updateDesignCard( data ) {
		if ( ! data || ! data.tag || ! data.key ) { return; }
		var $card = $( '.fw-sc-dcard[data-tag="' + data.tag + '"][data-key="' + data.key + '"]' );
		if ( ! $card.length ) { return; }
		var sub = [];
		if ( data.version ) { sub.push( 'v' + data.version ); }
		if ( data.author )  { sub.push( ( i18n.byAuthor || 'by %s' ).replace( '%s', data.author ) ); }
		var $sub = $card.find( '.fw-sc-dsub' );
		if ( ! $sub.length && sub.length ) {
			$sub = $( '<span class="fw-sc-dsub"></span>' ).appendTo( $card.find( '.fw-sc-dmeta' ) );
		}
		$sub.text( sub.join( ' · ' ) );
	}

	/* ------------------------------------------------------------------ *
	 * Tabs (Shortcodes | Design packs)
	 * ------------------------------------------------------------------ */

	function activateTab( name, updateHash ) {
		$( '.fw-sc-nav-tabs .nav-tab' ).each( function () {
			$( this ).toggleClass( 'nav-tab-active', $( this ).data( 'tab' ) === name );
		} );
		$( '.fw-sc-panel' ).each( function () {
			$( this ).toggleClass( 'fw-sc-panel-hidden', $( this ).data( 'panel' ) !== name );
		} );
		if ( updateHash !== false ) {
			if ( window.history && window.history.replaceState ) {
				window.history.replaceState( null, '', name === 'designs' ? '#designs' : '#' );
			}
		}
	}

	/* ------------------------------------------------------------------ *
	 * Design pack management
	 * ------------------------------------------------------------------ */

	function designToggle( $input ) {
		var $card   = $input.closest( '.fw-sc-dcard' );
		var enabled = $input.prop( 'checked' );

		post( 'fw_ext_shortcodes_design_toggle', {
			tag:     $card.data( 'tag' ),
			key:     $card.data( 'key' ),
			enabled: enabled ? 1 : 0
		}, function ( res ) {
			if ( res && res.success ) {
				$card.toggleClass( 'fw-sc-dcard-off', ! enabled );
				$card.find( '.fw-sc-dswitch-label' ).text( enabled ? ( i18n.enabledState || 'Enabled' ) : ( i18n.disabledState || 'Disabled' ) );
			} else {
				$input.prop( 'checked', ! enabled ); // revert
				notify( ( res && res.data && res.data.message ) || i18n.designToggleError, true );
			}
		} );
	}

	function refreshDesignsLink( tag ) {
		var packs = $( '#fw-sc-dgroup-' + tag ).find( '.fw-sc-dcard[data-origin="uploads"]' ).length;
		var $link = $( '.fw-sc-designs-link[data-tag="' + tag + '"]' );
		if ( packs > 0 ) {
			$link.text( ( i18n.designsLink || 'Designs (%d)' ).replace( '%d', packs ) );
		} else {
			$link.remove();
		}
	}

	function designDelete( $btn ) {
		fw.confirm( i18n.confirmDeleteDesign || i18n.confirmDelete, function () {
			var $card = $btn.closest( '.fw-sc-dcard' );
			var tag   = $card.data( 'tag' );
			$btn.prop( 'disabled', true );

			post( 'fw_ext_shortcodes_design_delete', {
				tag: tag,
				key: $card.data( 'key' )
			}, function ( res ) {
				if ( res && res.success ) {
					$card.remove();
					refreshDesignsLink( tag );
				} else {
					$btn.prop( 'disabled', false );
					notify( ( res && res.data && res.data.message ) || i18n.genericError, true );
				}
			} );
		} );
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
		$( '#fw-sc-install-zip' ).on( 'click', function () { installZip(); } );
		$( '#fw-sc-install-github' ).on( 'click', function () { installGithub(); } );

		// Tabs (native WP nav-tabs).
		$( '.fw-sc-nav-tabs .nav-tab' ).on( 'click', function ( e ) {
			e.preventDefault();
			activateTab( $( this ).data( 'tab' ) );
		} );

		// Native postbox collapse toggles on the Design packs tab.
		if ( window.postboxes && typeof window.postboxes.add_postbox_toggles === 'function' ) {
			window.postboxes.add_postbox_toggles( window.pagenow || '' );
		}

		// "Designs (N)" shortcut on a shortcode card → jump to its group.
		$( document ).on( 'click', '.fw-sc-designs-link', function ( e ) {
			e.preventDefault();
			var tag = $( this ).data( 'tag' );
			activateTab( 'designs' );
			var el = document.getElementById( 'fw-sc-dgroup-' + tag );
			if ( el ) {
				el.scrollIntoView( { behavior: 'smooth', block: 'start' } );
				$( el ).addClass( 'fw-sc-dgroup-flash' );
				setTimeout( function () { $( el ).removeClass( 'fw-sc-dgroup-flash' ); }, 1200 );
			}
		} );

		// Design pack actions.
		$( document ).on( 'change', '.fw-sc-dtoggle', function () {
			designToggle( $( this ) );
		} );
		$( document ).on( 'click', '.fw-sc-ddelete', function () {
			designDelete( $( this ) );
		} );

		// Deep-link: open the Design packs tab when the URL points at it.
		if ( /^#(designs|fw-sc-dgroup-)/.test( window.location.hash ) ) {
			activateTab( 'designs', false );
			var target = window.location.hash.indexOf( 'fw-sc-dgroup-' ) !== -1
				? document.getElementById( window.location.hash.slice( 1 ) )
				: null;
			if ( target ) {
				setTimeout( function () { target.scrollIntoView( { block: 'start' } ); }, 60 );
			}
		}
	} );
}( jQuery ) );
