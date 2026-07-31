/**
 * UnysonPlus section background runtime — vanilla JS, no jQuery.
 *
 * Replaces the Formstone background stack (core.js / transition.js /
 * background.js and the deprecated jquery.fs.wallpaper.js). Renders the
 * poster image, HTML5 video (mp4/webm/ogg) and YouTube background layers
 * into every `.background-video` wrapper (section / container / flexbox),
 * reusing the original `fs-background-*` class names so the existing CSS
 * (background.css + section styles.css pointer-events rules) keeps working.
 *
 * Sizing: poster uses background-size:cover (the `fs-background-native`
 * path); HTML5 video uses CSS object-fit:cover; only the YouTube iframe
 * still needs the JS cover-math (an iframe can't object-fit its content).
 */
( function () {
	'use strict';

	var YT_REGEX = /(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/i;
	var EMBED_RATIO = 1.777777; // 16:9

	function parseOptions( el ) {
		var raw = el.getAttribute( 'data-background-options' ) || el.getAttribute( 'data-wallpaper-options' );
		if ( ! raw ) { return null; }
		try { return JSON.parse( raw ); } catch ( e ) { return null; }
	}

	function makeLayer( classes ) {
		var d = document.createElement( 'div' );
		d.className = 'fs-background-media fs-background-animated ' + classes;
		// The old JS sized every layer; give the div full cover so CSS can do it.
		d.style.left = '0';
		d.style.width = '100%';
		d.style.height = '100%';
		return d;
	}

	function fadeIn( layer ) {
		// Force a style flush so the opacity transition runs.
		void layer.offsetHeight;
		layer.style.opacity = '1';
	}

	/* ---- Poster / image layer (fs-background-native = background-size:cover) ---- */
	function addPoster( container, url ) {
		var layer = makeLayer( 'fs-background-image fs-background-native' );
		var img = new Image();
		img.onload = function () { fadeIn( layer ); };
		img.src = url;
		layer.style.backgroundImage = "url('" + url + "')";
		container.appendChild( layer );
		if ( img.complete ) { fadeIn( layer ); }
		return layer;
	}

	/* ---- HTML5 video layer ---- */
	function addVideo( container, opts ) {
		var layer = makeLayer( 'fs-background-video' );
		var video = document.createElement( 'video' );
		video.muted = opts.mute !== false;
		video.loop = !! opts.loop;
		video.setAttribute( 'playsinline', '' );
		if ( video.muted ) { video.setAttribute( 'muted', '' ); }
		video.style.width = '100%';
		video.style.height = '100%';
		video.style.objectFit = 'cover';

		[ 'webm', 'mp4', 'ogg' ].forEach( function ( type ) {
			if ( opts.source[ type ] ) {
				var s = document.createElement( 'source' );
				s.src = opts.source[ type ];
				s.type = 'video/' + type;
				video.appendChild( s );
			}
		} );

		video.addEventListener( 'loadedmetadata', function () {
			fadeIn( layer );
			if ( opts.autoPlay !== false ) {
				var p = video.play();
				if ( p && p.catch ) { p.catch( function () {} ); } // autoplay blocked → poster stays
			}
		} );

		layer.appendChild( video );
		container.appendChild( layer );
	}

	/* ---- YouTube layer (iframe API; needs JS cover-sizing) ---- */
	var ytApiRequested = false;
	var ytQueue = [];

	function whenYouTubeReady( cb ) {
		if ( window.YT && window.YT.Player ) { cb(); return; }
		ytQueue.push( cb );
		if ( ! ytApiRequested ) {
			ytApiRequested = true;
			// Chain any pre-existing handler (e.g. another plugin's).
			var prev = window.onYouTubeIframeAPIReady;
			window.onYouTubeIframeAPIReady = function () {
				if ( typeof prev === 'function' ) { prev(); }
				var q = ytQueue.slice();
				ytQueue = [];
				q.forEach( function ( fn ) { fn(); } );
			};
			if ( ! document.querySelector( "script[src*='youtube.com/iframe_api']" ) ) {
				var tag = document.createElement( 'script' );
				tag.src = 'https://www.youtube.com/iframe_api';
				document.head.appendChild( tag );
			}
		}
	}

	var ytGuid = 0;

	function coverSize( wrapper, layer ) {
		var w = wrapper.clientWidth, h = wrapper.clientHeight;
		var width = h * EMBED_RATIO, height = h;
		if ( width < w ) { width = w; height = w / EMBED_RATIO; }
		layer.style.width = Math.ceil( width ) + 'px';
		layer.style.height = Math.ceil( height ) + 'px';
		layer.style.left = ( -( width - w ) / 2 ) + 'px';
		layer.style.top = ( -( height - h ) / 2 ) + 'px';
	}

	function addYouTube( wrapper, container, opts, videoId ) {
		var layer = makeLayer( 'fs-background-embed' );
		var holderId = 'fs-bg-yt-' + ( ytGuid++ );
		var holder = document.createElement( 'div' );
		holder.id = holderId;
		layer.appendChild( holder );
		container.appendChild( layer );

		var resize = function () { coverSize( wrapper, layer ); };
		resize();
		window.addEventListener( 'resize', resize );

		whenYouTubeReady( function () {
			var playing = false;
			var player = new window.YT.Player( holderId, {
				videoId: videoId,
				playerVars: {
					controls: 0,
					rel: 0,
					showinfo: 0,
					wmode: 'transparent',
					enablejsapi: 1,
					version: 3,
					playerapiid: holderId,
					loop: opts.loop ? 1 : 0,
					autoplay: 1,
					mute: opts.mute !== false ? 1 : 0,
					playsinline: 1,
					origin: window.location.protocol + '//' + window.location.host
				},
				events: {
					onReady: function () {
						if ( opts.mute !== false ) { player.mute(); }
						if ( opts.autoPlay !== false ) { player.playVideo(); }
					},
					onStateChange: function ( e ) {
						if ( ! playing && e.data === window.YT.PlayerState.PLAYING ) {
							playing = true;
							if ( opts.autoPlay === false ) { player.pauseVideo(); }
							fadeIn( layer );
							resize();
						} else if ( opts.loop && playing && e.data === window.YT.PlayerState.ENDED ) {
							player.playVideo();
						}
						layer.classList.add( 'fs-background-ready' );
					}
				}
			} );
		} );
	}

	/* ---- Per-wrapper init ---- */
	function initWrapper( wrapper ) {
		if ( wrapper.classList.contains( 'fs-background-element' ) ) { return; } // already initialised
		var opts = parseOptions( wrapper );
		if ( ! opts || typeof opts !== 'object' || ! opts.source ) { return; }
		var source = opts.source;
		if ( typeof source === 'string' ) { source = { poster: source }; opts.source = source; }

		wrapper.classList.add( 'fs-background', 'fs-background-element' );

		var container = document.createElement( 'div' );
		container.className = 'fs-background-container';
		wrapper.appendChild( container );

		var ytMatch = ( typeof source.video === 'string' ) ? source.video.match( YT_REGEX ) : null;

		if ( ytMatch ) {
			var poster = source.poster || ( 'https://img.youtube.com/vi/' + ytMatch[ 1 ] + '/0.jpg' );
			addPoster( container, poster );
			addYouTube( wrapper, container, opts, ytMatch[ 1 ] );
		} else if ( source.mp4 || source.webm || source.ogg ) {
			if ( source.poster ) { addPoster( container, source.poster ); }
			addVideo( container, opts );
		} else if ( source.poster ) {
			addPoster( container, source.poster );
		}
	}

	function init() {
		Array.prototype.forEach.call( document.querySelectorAll( '.background-video' ), initWrapper );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}

} )();
