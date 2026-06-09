/* Site Search shortcode — icon-toggle reveal. */
( function () {
	'use strict';

	function closeAll( except ) {
		document.querySelectorAll( '.sc-site-search--icon-toggle .sc-search-toggle[aria-expanded="true"]' ).forEach( function ( btn ) {
			if ( btn === except ) {
				return;
			}
			var panel = btn.parentNode.querySelector( '.sc-search-panel' );
			btn.setAttribute( 'aria-expanded', 'false' );
			if ( panel ) {
				panel.hidden = true;
			}
		} );
	}

	document.addEventListener( 'click', function ( e ) {
		var btn = e.target.closest( '.sc-site-search--icon-toggle .sc-search-toggle' );
		if ( btn ) {
			e.preventDefault();
			var panel    = btn.parentNode.querySelector( '.sc-search-panel' );
			var willOpen = btn.getAttribute( 'aria-expanded' ) !== 'true';
			closeAll( btn );
			btn.setAttribute( 'aria-expanded', willOpen ? 'true' : 'false' );
			if ( panel ) {
				panel.hidden = ! willOpen;
				if ( willOpen ) {
					var field = panel.querySelector( '.sc-search-field' );
					if ( field ) {
						field.focus();
					}
				}
			}
			return;
		}

		// Click outside any open toggle search → close.
		if ( ! e.target.closest( '.sc-site-search--icon-toggle' ) ) {
			closeAll( null );
		}
	} );

	document.addEventListener( 'keydown', function ( e ) {
		if ( e.key === 'Escape' ) {
			closeAll( null );
		}
	} );
}() );
