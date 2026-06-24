/**
 * Calendar — dependency-free month navigation. The initial month is rendered
 * server-side (SEO-friendly); this re-renders the grid on prev/next/today.
 * Mirrors sc_cal_render_grid() in view.php.
 */
(function () {
	'use strict';

	function pad( n ) { return ( n < 10 ? '0' : '' ) + n; }

	function initOne( root ) {
		if ( root.__calReady ) { return; }
		root.__calReady = true;

		var events, wd, mo;
		try {
			events = JSON.parse( root.getAttribute( 'data-events' ) || '[]' );
			wd     = JSON.parse( root.getAttribute( 'data-wd' ) || '[]' );
			mo     = JSON.parse( root.getAttribute( 'data-mo' ) || '[]' );
		} catch ( e ) { return; }

		var firstMon = root.getAttribute( 'data-first-week' ) !== 'sun';
		var today    = root.getAttribute( 'data-today' ) || '';
		var moreTxt  = root.getAttribute( 'data-more' ) || 'more';
		var evLabel  = root.getAttribute( 'data-event-label' ) || 'Event';
		var year     = parseInt( root.getAttribute( 'data-year' ), 10 );
		var month    = parseInt( root.getAttribute( 'data-month' ), 10 ); // 1-based

		var monthEl = root.querySelector( '[data-cal-month]' );
		var titleEl = root.querySelector( '[data-cal-title]' );
		if ( ! monthEl ) { return; }

		function parse( s ) { var p = String( s ).split( '-' ); return Date.UTC( +p[0], +p[1] - 1, +p[2] ); }
		function key( ts ) { var d = new Date( ts ); return d.getUTCFullYear() + '-' + pad( d.getUTCMonth() + 1 ) + '-' + pad( d.getUTCDate() ); }
		function esc( s ) { var d = document.createElement( 'div' ); d.textContent = s == null ? '' : s; return d.innerHTML; }

		// Per-day index (multi-day events span each day in their range).
		var byDay = {};
		events.forEach( function ( ev ) {
			var cur = parse( ev.start ), end = parse( ev.end || ev.start ), guard = 0;
			while ( cur <= end && guard < 400 ) { ( byDay[ key( cur ) ] = byDay[ key( cur ) ] || [] ).push( ev ); cur += 86400000; guard++; }
		} );

		function renderGrid( y, m ) { // m = 1-based
			var firstDow = new Date( Date.UTC( y, m - 1, 1 ) ).getUTCDay(); // 0=Sun
			var offset   = firstMon ? ( ( firstDow + 6 ) % 7 ) : firstDow;
			var startTs  = Date.UTC( y, m - 1, 1 - offset );

			var html = '<div class="fw-cal__weekdays">';
			for ( var i = 0; i < 7; i++ ) {
				var dow = firstMon ? ( ( i + 1 ) % 7 ) : i;
				html += '<div class="fw-cal__wd">' + esc( wd[ dow ] || '' ) + '</div>';
			}
			html += '</div><div class="fw-cal__grid">';

			for ( var cell = 0; cell < 42; cell++ ) {
				var ts   = startTs + cell * 86400000;
				var dt   = new Date( ts );
				var k    = key( ts );
				var dnum = dt.getUTCDate();
				var dow2 = dt.getUTCDay();
				var cls  = 'fw-cal__cell';
				if ( dt.getUTCMonth() + 1 !== m ) { cls += ' is-out'; }
				if ( k === today ) { cls += ' is-today'; }
				if ( dow2 === 0 || dow2 === 6 ) { cls += ' is-weekend'; }
				var evs = byDay[ k ] || [];
				if ( evs.length ) { cls += ' has-events'; }

				html += '<div class="' + cls + '" data-date="' + k + '"><span class="fw-cal__num">' + dnum + '</span>';
				if ( evs.length ) {
					html += '<div class="fw-cal__events">';
					evs.slice( 0, 3 ).forEach( function ( ev ) {
						var open  = ev.url ? '<a href="' + esc( ev.url ) + '"' : '<span';
						var close = ev.url ? '</a>' : '</span>';
						var tip   = ( ev.time ? ev.time + ' · ' : '' ) + ( ev.title || '' );
						html += open + ' class="fw-cal__ev fw-cal__ev--' + esc( ev.color || 'blue' ) + '" title="' + esc( tip ) + '">'
							+ ( ev.time ? '<span class="fw-cal__ev-time">' + esc( ev.time ) + '</span>' : '' )
							+ '<span class="fw-cal__ev-title">' + esc( ev.title || evLabel ) + '</span>' + close;
					} );
					if ( evs.length > 3 ) { html += '<span class="fw-cal__more">+' + ( evs.length - 3 ) + ' ' + esc( moreTxt ) + '</span>'; }
					html += '</div>';
				}
				html += '</div>';
			}
			monthEl.innerHTML = html + '</div>';
			if ( titleEl ) { titleEl.textContent = ( mo[ m - 1 ] || '' ) + ' ' + y; }
		}

		function go( delta ) {
			month += delta;
			while ( month > 12 ) { month -= 12; year++; }
			while ( month < 1 ) { month += 12; year--; }
			renderGrid( year, month );
		}

		root.addEventListener( 'click', function ( e ) {
			var btn = e.target.closest ? e.target.closest( '[data-cal-nav]' ) : null;
			if ( ! btn || ! root.contains( btn ) ) { return; }
			var nav = btn.getAttribute( 'data-cal-nav' );
			if ( nav === 'prev' ) { go( -1 ); }
			else if ( nav === 'next' ) { go( 1 ); }
			else if ( nav === 'today' ) {
				var p = ( today || '' ).split( '-' );
				if ( p.length === 3 ) { year = +p[0]; month = +p[1]; renderGrid( year, month ); }
			}
		} );
	}

	function init() { Array.prototype.forEach.call( document.querySelectorAll( '.fw-cal[data-events]' ), initOne ); }
	if ( document.readyState === 'loading' ) { document.addEventListener( 'DOMContentLoaded', init ); } else { init(); }
})();
