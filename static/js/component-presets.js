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

			// The presets Save / Reset bar belongs to the library tabs only — hide it
			// on Export / Import, which has its own forms + buttons.
			$wrap.find( '.fw-cp-actions' ).toggle( tab !== 'tab_io' );

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
			if ( $( this ).data( 'fwConfirmed' ) ) { return; } // confirmed → let it submit
			e.preventDefault();
			var btn = this;
			fw.confirm( 'Reset this tab to its default presets? Your changes on this tab will be lost.', function () {
				$( btn ).data( 'fwConfirmed', true );
				btn.click(); // replay the native action, now past the guard
			} );
		} );
		$wrap.on( 'click', '.fw-cp-reset-all', function ( e ) {
			if ( $( this ).data( 'fwConfirmed' ) ) { return; } // confirmed → let it submit
			e.preventDefault();
			var btn = this;
			fw.confirm( 'Reset ALL component presets to their defaults? Every customization here will be lost.', function () {
				$( btn ).data( 'fwConfirmed', true );
				btn.click(); // replay the native action, now past the guard
			} );
		} );

		// Open the right tab on load: URL hash (after a Save/Reset/import redirect) →
		// the server-marked active tab (e.g. Export/Import after an upload) → the first
		// tab. activate() also syncs the Save/Reset bar visibility.
		var hash = ( window.location.hash || '' ).replace( /^#/, '' );
		var initial = ( hash && $wrap.find( '#panel-' + hash ).length )
			? hash
			: ( $wrap.find( '.fw-cp-tabs .nav-tab.nav-tab-active' ).data( 'tab' )
				|| $wrap.find( '.fw-cp-tabs .nav-tab' ).first().data( 'tab' ) );
		if ( initial ) { activate( initial ); }
	} );
}( jQuery ) );
