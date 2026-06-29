/**
 * Color Presets — open/close fix (Theme Settings).
 *
 * In the Theme Settings nested-tab context the framework's native postbox-toggle
 * binding (backend-options.js addPostboxToggles) does NOT attach to the Color Presets
 * boxes — unlike Typography / Spacing, which toggle fine. (Diagnosed via the tracer:
 * clicking a color box never flips its `.closed` state, though the body is rendered.)
 *
 * This binds a SCOPED toggle for the color preset list only (#fw-option-theme_colors),
 * deferred one tick so the native handler wins when present: we toggle only if the
 * box state did not change on its own — so this never double-toggles, and it can't
 * affect any other preset list.
 */
( function () {
	'use strict';

	var SCOPE = '#fw-option-theme_colors .postbox-header .hndle, #fw-option-theme_colors .postbox-header .handlediv';

	document.addEventListener( 'click', function ( e ) {
		var hndle = e.target.closest ? e.target.closest( SCOPE ) : null;
		if ( ! hndle ) {
			return;
		}
		var box = hndle.closest( '.fw-postbox' );
		if ( ! box ) {
			return;
		}

		var wasClosed = box.classList.contains( 'closed' );

		// Let any native toggle run first; step in only if nothing changed.
		setTimeout( function () {
			if ( box.classList.contains( 'closed' ) !== wasClosed ) {
				return; // native handler already toggled it
			}
			box.classList.toggle( 'closed' );
			var isOpen = ! box.classList.contains( 'closed' );
			if ( window.jQuery ) {
				window.jQuery( box ).trigger( 'fw:box:' + ( isOpen ? 'open' : 'close' ) );
				window.jQuery( box ).trigger( 'fw:box:toggle-closed', { isClosed: ! isOpen } );
			}
		}, 0 );
	}, false );
}() );
