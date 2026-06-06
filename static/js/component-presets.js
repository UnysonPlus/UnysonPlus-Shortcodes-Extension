/* Component Presets page — native WP nav-tab switching.
   All panels stay in the DOM (so a Save submits every tab); we just toggle which
   one is visible. Mirrors the classic WordPress settings-tab behaviour. */
( function ( $ ) {
	$( function () {
		var $wrap = $( '.fw-component-presets' );
		if ( ! $wrap.length ) { return; }

		function activate( tab ) {
			$wrap.find( '.fw-cp-tabs .nav-tab' ).each( function () {
				$( this ).toggleClass( 'nav-tab-active', $( this ).data( 'tab' ) === tab );
			} );
			$wrap.find( '.fw-cp-panel' ).each( function () {
				$( this ).toggleClass( 'is-active', this.id === 'panel-' + tab );
			} );

			// Tell the server which tab is active (used by "Reset This Tab" + to
			// return to the same tab after a Save/Reset redirect).
			$wrap.find( '.fw-cp-active-tab' ).val( tab );

			// Widgets measured while hidden (CodeMirror in the custom-CSS fields)
			// render at 0 height until refreshed once their panel is visible.
			$wrap.find( '#panel-' + tab + ' .CodeMirror' ).each( function () {
				if ( this.CodeMirror ) { this.CodeMirror.refresh(); }
			} );
		}

		$wrap.on( 'click', '.fw-cp-tabs .nav-tab', function ( e ) {
			e.preventDefault();
			var tab = $( this ).data( 'tab' );
			activate( tab );
			// Remember the tab across saves/reloads via the URL hash.
			if ( window.history && window.history.replaceState ) {
				window.history.replaceState( null, '', '#' + tab );
			}
		} );

		// Confirm before a destructive reset.
		$wrap.on( 'click', '.fw-cp-reset', function ( e ) {
			if ( ! window.confirm( 'Reset this tab to its default presets? Your changes on this tab will be lost.' ) ) {
				e.preventDefault();
			}
		} );
		$wrap.on( 'click', '.fw-cp-reset-all', function ( e ) {
			if ( ! window.confirm( 'Reset ALL component presets to their defaults? Every customization here will be lost.' ) ) {
				e.preventDefault();
			}
		} );

		// Restore the tab from the hash (e.g. after a Save/Reset redirect).
		var hash = ( window.location.hash || '' ).replace( /^#/, '' );
		if ( hash && $wrap.find( '#panel-' + hash ).length ) {
			activate( hash );
		}
	} );
}( jQuery ) );
