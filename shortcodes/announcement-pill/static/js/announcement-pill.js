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

	function init() {
		var dismissed = read();
		var pills = document.querySelectorAll( '[data-ap-dismiss]' );
		Array.prototype.forEach.call( pills, function ( el ) {
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
	else { document.addEventListener( 'DOMContentLoaded', init ); }
} )();
