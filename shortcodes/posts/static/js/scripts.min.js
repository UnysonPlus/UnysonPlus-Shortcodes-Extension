/*
 * Posts shortcode — frontend behaviours.
 * Wires up:
 *   1. Slider arrow/dot navigation for .posts--mode-slider
 *   2. AJAX "Load more" button
 *   3. AJAX infinite scroll sentinel
 *   4. AJAX filter chips
 *
 * Each behaviour is feature-detected; the script is a no-op on pages without
 * a .posts wrapper. Vanilla JS — no jQuery dependency.
 */
( function () {
    'use strict';

    if ( typeof window === 'undefined' ) return;

    /* Form-encoded POST helper matching jQuery's $.post payload shape. */
    function ajaxPost( data ) {
        var cfg = window.fwScPosts || {};
        return fetch( cfg.ajaxUrl || '', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
            body: new URLSearchParams( data ).toString()
        } ).then( function ( res ) {
            if ( ! res.ok ) throw new Error( 'HTTP ' + res.status );
            return res.text();
        } );
    }

    function createEl( html ) {
        var tpl = document.createElement( 'template' );
        tpl.innerHTML = html;
        return tpl.content.firstElementChild;
    }

    function initSlider( wrap ) {
        var grid = wrap.querySelector( '.posts__grid' );
        if ( ! grid ) return;

        var arrowsPos = wrap.getAttribute( 'data-slider-arrows' ) || 'outside';
        var dotsPos   = wrap.getAttribute( 'data-slider-dots' )   || 'below';
        var autoplay  = wrap.getAttribute( 'data-slider-autoplay' ) === '1';
        var interval  = parseInt( wrap.getAttribute( 'data-slider-interval' ), 10 ) || 5000;

        /* Arrow buttons */
        if ( arrowsPos !== 'hidden' ) {
            var prev = createEl( '<button type="button" class="posts__slider-arrow posts__slider-arrow--prev" aria-label="Previous">‹</button>' );
            var next = createEl( '<button type="button" class="posts__slider-arrow posts__slider-arrow--next" aria-label="Next">›</button>' );
            wrap.appendChild( prev );
            wrap.appendChild( next );

            prev.addEventListener( 'click', function () { grid.scrollBy( { left: -grid.clientWidth, behavior: 'smooth' } ); } );
            next.addEventListener( 'click', function () { grid.scrollBy( { left:  grid.clientWidth, behavior: 'smooth' } ); } );
        }

        /* Dot indicators (one per "page" of slides) */
        if ( dotsPos !== 'hidden' ) {
            var cards = grid.querySelectorAll( '.posts__card' );
            var dots  = createEl( '<div class="posts__slider-dots" role="tablist"></div>' );
            Array.prototype.forEach.call( cards, function ( card, i ) {
                var d = createEl( '<button type="button" class="posts__slider-dot" aria-label="Slide ' + ( i + 1 ) + '"></button>' );
                d.addEventListener( 'click', function () {
                    card.scrollIntoView( { behavior: 'smooth', inline: 'start', block: 'nearest' } );
                } );
                dots.appendChild( d );
            } );
            wrap.appendChild( dots );
        }

        /* Autoplay */
        if ( autoplay ) {
            var stopped = false;
            [ 'mouseenter', 'focusin' ].forEach( function ( ev ) {
                wrap.addEventListener( ev, function () { stopped = true; } );
            } );
            [ 'mouseleave', 'focusout' ].forEach( function ( ev ) {
                wrap.addEventListener( ev, function () { stopped = false; } );
            } );
            setInterval( function () {
                if ( stopped ) return;
                var atEnd = grid.scrollLeft + grid.clientWidth >= grid.scrollWidth - 4;
                grid.scrollBy( {
                    left: atEnd ? -grid.scrollWidth : grid.clientWidth,
                    behavior: 'smooth'
                } );
            }, interval );
        }
    }

    function appendHtml( grid, html ) {
        var tpl = document.createElement( 'template' );
        tpl.innerHTML = html;
        grid.appendChild( tpl.content );
    }

    function initLoadMore( wrap ) {
        var btn = wrap.querySelector( '.posts__loadmore' );
        if ( ! btn ) return;

        btn.addEventListener( 'click', function () {
            var page    = parseInt( btn.getAttribute( 'data-page' ), 10 ) || 2;
            var maxPage = parseInt( btn.getAttribute( 'data-max-page' ), 10 ) || 1;
            if ( page > maxPage ) { btn.disabled = true; return; }

            btn.disabled = true;
            btn.classList.add( 'is-loading' );

            ajaxPost( {
                action: 'fw_sc_posts_loadmore',
                nonce:  ( window.fwScPosts || {} ).nonce || '',
                page:   page,
                wrap:   wrap.id || ''
            } )
            .then( function ( html ) {
                var grid = wrap.querySelector( '.posts__grid' );
                if ( html && grid ) appendHtml( grid, html );
                btn.setAttribute( 'data-page', page + 1 );
                if ( page + 1 > maxPage ) btn.remove();
            } )
            .catch( function () {} )
            .then( function () {
                if ( btn.isConnected ) {
                    btn.disabled = false;
                    btn.classList.remove( 'is-loading' );
                }
            } );
        } );
    }

    function initInfinite( wrap ) {
        var sentinel = wrap.querySelector( '.posts__infinite-sentinel' );
        if ( ! sentinel || typeof IntersectionObserver === 'undefined' ) return;

        var io = new IntersectionObserver( function ( entries ) {
            entries.forEach( function ( entry ) {
                if ( ! entry.isIntersecting ) return;
                var page    = parseInt( sentinel.getAttribute( 'data-page' ), 10 ) || 2;
                var maxPage = parseInt( sentinel.getAttribute( 'data-max-page' ), 10 ) || 1;
                if ( page > maxPage ) { io.disconnect(); return; }

                ajaxPost( {
                    action: 'fw_sc_posts_loadmore',
                    nonce:  ( window.fwScPosts || {} ).nonce || '',
                    page:   page,
                    wrap:   wrap.id || ''
                } )
                .then( function ( html ) {
                    var grid = wrap.querySelector( '.posts__grid' );
                    if ( html && grid ) appendHtml( grid, html );
                    sentinel.setAttribute( 'data-page', page + 1 );
                    if ( page + 1 > maxPage ) { io.disconnect(); sentinel.remove(); }
                } )
                .catch( function () {} );
            } );
        }, { rootMargin: '200px' } );

        io.observe( sentinel );
    }

    function initFilters( wrap ) {
        var filters = wrap.querySelectorAll( '.posts__filter' );
        if ( ! filters.length ) return;

        Array.prototype.forEach.call( filters, function ( btn ) {
            btn.addEventListener( 'click', function () {
                Array.prototype.forEach.call( filters, function ( f ) {
                    f.classList.remove( 'is-active' );
                    f.setAttribute( 'aria-pressed', 'false' );
                } );
                btn.classList.add( 'is-active' );
                btn.setAttribute( 'aria-pressed', 'true' );

                var term = btn.getAttribute( 'data-term' ) || '';
                ajaxPost( {
                    action: 'fw_sc_posts_filter',
                    nonce:  ( window.fwScPosts || {} ).nonce || '',
                    term:   term,
                    wrap:   wrap.id || ''
                } )
                .then( function ( html ) {
                    var grid = wrap.querySelector( '.posts__grid' );
                    if ( typeof html === 'string' && grid ) {
                        grid.innerHTML = html;
                    }
                } )
                .catch( function () {} );
            } );
        } );
    }

    function init() {
        Array.prototype.forEach.call( document.querySelectorAll( '.posts' ), function ( wrap ) {
            if ( wrap.classList.contains( 'posts--mode-slider' ) ) initSlider( wrap );
            initLoadMore( wrap );
            initInfinite( wrap );
            if ( wrap.classList.contains( 'posts--has-filters' ) ) initFilters( wrap );
        } );
    }

    if ( document.readyState === 'loading' ) {
        document.addEventListener( 'DOMContentLoaded', init );
    } else {
        init();
    }

} )();
