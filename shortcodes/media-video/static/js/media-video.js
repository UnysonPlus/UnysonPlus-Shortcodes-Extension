/**
 * Media Video — front-end behaviours (dependency-free).
 *   1. Lazy-load facade: on click, swap the poster+play button for the real provider
 *      iframe (with autoplay), so the heavy embed only loads on demand.
 *   2. Reduced motion: pause self-hosted autoplay (background/hero) videos when the
 *      visitor prefers reduced motion.
 */
( function () {
	'use strict';

	// 1. Facade click → inject the iframe.
	document.addEventListener( 'click', function ( e ) {
		var facade = e.target.closest ? e.target.closest( '.video-facade' ) : null;
		if ( ! facade ) { return; }
		var src = facade.getAttribute( 'data-video-src' );
		if ( ! src ) { return; }
		src += ( src.indexOf( '?' ) === -1 ? '?' : '&' ) + 'autoplay=1';

		var iframe = document.createElement( 'iframe' );
		iframe.setAttribute( 'src', src );
		iframe.setAttribute( 'frameborder', '0' );
		iframe.setAttribute( 'allow', 'accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture; web-share' );
		iframe.setAttribute( 'allowfullscreen', '' );
		iframe.setAttribute( 'title', facade.getAttribute( 'aria-label' ) || 'Video' );
		iframe.style.width = '100%';
		iframe.style.height = '100%';

		if ( facade.replaceWith ) {
			facade.replaceWith( iframe );
		} else if ( facade.parentNode ) {
			facade.parentNode.replaceChild( iframe, facade );
		}
	}, false );

	// 2. Reduced motion: pause autoplay background videos.
	try {
		if ( window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) {
			var vids = document.querySelectorAll( 'video[data-upw-autoplay]' );
			for ( var i = 0; i < vids.length; i++ ) {
				vids[ i ].removeAttribute( 'autoplay' );
				vids[ i ].pause();
			}
		}
	} catch ( err ) {}
}() );
