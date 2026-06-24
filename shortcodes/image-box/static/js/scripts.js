/* Image Box — minimal dependency-free lightbox for the image / video link
   behaviors. Boxes with data-imgbox-lightbox="image|video" open here; every
   other behavior is a normal anchor and is untouched. */
( function () {
    'use strict';

    var overlay = null;

    function buildOverlay() {
        if ( overlay ) { return overlay; }
        overlay = document.createElement( 'div' );
        overlay.className = 'imgbox-lb';
        overlay.setAttribute( 'role', 'dialog' );
        overlay.setAttribute( 'aria-modal', 'true' );
        overlay.innerHTML =
            '<button type="button" class="imgbox-lb__close" aria-label="Close">×</button>' +
            '<div class="imgbox-lb__stage"></div>';
        document.body.appendChild( overlay );

        overlay.addEventListener( 'click', function ( e ) {
            if ( e.target === overlay || e.target.classList.contains( 'imgbox-lb__close' ) ) {
                close();
            }
        } );
        return overlay;
    }

    function close() {
        if ( ! overlay ) { return; }
        overlay.classList.remove( 'is-open' );
        var stage = overlay.querySelector( '.imgbox-lb__stage' );
        if ( stage ) { stage.innerHTML = ''; }
        document.documentElement.style.removeProperty( 'overflow' );
    }

    function videoEmbed( url ) {
        var yt = url.match( /(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([\w-]{11})/ );
        if ( yt ) {
            return '<iframe src="https://www.youtube.com/embed/' + yt[ 1 ] + '?autoplay=1" allow="autoplay; encrypted-media; fullscreen" allowfullscreen frameborder="0"></iframe>';
        }
        var vm = url.match( /vimeo\.com\/(?:video\/)?(\d+)/ );
        if ( vm ) {
            return '<iframe src="https://player.vimeo.com/video/' + vm[ 1 ] + '?autoplay=1" allow="autoplay; fullscreen" allowfullscreen frameborder="0"></iframe>';
        }
        if ( /\.(mp4|webm|ogg)(\?|$)/i.test( url ) ) {
            return '<video src="' + url + '" controls autoplay playsinline></video>';
        }
        // Fallback: treat as an embeddable page URL.
        return '<iframe src="' + url + '" allow="autoplay; fullscreen" allowfullscreen frameborder="0"></iframe>';
    }

    function open( type, url ) {
        if ( ! url ) { return; }
        var o = buildOverlay();
        var stage = o.querySelector( '.imgbox-lb__stage' );
        stage.innerHTML = type === 'video'
            ? videoEmbed( url )
            : '<img src="' + url + '" alt="">';
        document.documentElement.style.overflow = 'hidden';
        o.classList.add( 'is-open' );
    }

    document.addEventListener( 'click', function ( e ) {
        var trigger = e.target.closest ? e.target.closest( '[data-imgbox-lightbox]' ) : null;
        if ( ! trigger ) { return; }
        e.preventDefault();
        open( trigger.getAttribute( 'data-imgbox-lightbox' ), trigger.getAttribute( 'href' ) );
    } );

    document.addEventListener( 'keydown', function ( e ) {
        if ( e.key === 'Escape' ) { close(); }
    } );
}() );
