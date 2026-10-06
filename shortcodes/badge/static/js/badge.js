/* Announcement Pill — dismissible behaviour. A pill with data-ap-dismiss="<key>" can be closed via its
   × button; the choice is remembered per-browser in localStorage so it stays hidden on repeat visits. */
( function () {
	'use strict';
	var KEY = 'fw_ap_dismissed';

	function read() {
		try { return JSON.parse( window.localStorage.getItem( KEY ) || '{}' ) || {}; }
		catch ( e ) { return {}; }
	}
	function write( obj ) {
		try { window.localStorage.setItem( KEY, JSON.stringify( obj ) ); } catch ( e ) {}
	}

	function init( scope ) {
		var dismissed = read();
		var pills = ( scope || document ).querySelectorAll( '[data-ap-dismiss]' );
		Array.prototype.forEach.call( pills, function ( el ) {
			if ( el.__fwBadgeReady ) { return; }
			el.__fwBadgeReady = true;
			var id = el.getAttribute( 'data-ap-dismiss' );
			if ( ! id ) { return; }
			if ( dismissed[ id ] ) { el.style.display = 'none'; return; }
			var btn = el.querySelector( '.ap-pill__close' );
			if ( ! btn ) { return; }
			btn.addEventListener( 'click', function ( e ) {
				e.preventDefault();
				e.stopPropagation();
				var d = read();
				d[ id ] = 1;
				write( d );
				el.style.display = 'none';
			} );
		} );
	}

	if ( document.readyState !== 'loading' ) { init(); }
	else { document.addEventListener( 'DOMContentLoaded', function () { init(); } ); }

	/* Re-init hook for surfaces that insert this element's markup after page load (the
	   block editor's preview, the Elementor editor's re-renders): they call every
	   window.fwShortcodeInit entry with the document or element holding the new markup.
	   init() skips what it has already initialised, so repeated calls are safe. */
	window.fwShortcodeInit = window.fwShortcodeInit || [];
	window.fwShortcodeInit.push( init );
} )();
