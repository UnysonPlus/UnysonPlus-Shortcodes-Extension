/*
 * Posts shortcode — frontend behaviours.
 * Wires up:
 *   1. Slider arrow/dot navigation for .posts--mode-slider
 *   2. AJAX "Load more" button
 *   3. AJAX infinite scroll sentinel
 *   4. AJAX filter chips
 *
 * Each behaviour is feature-detected; the script is a no-op on pages without
 * a .posts wrapper.
 */
( function ( $ ) {
    'use strict';

    if ( typeof window === 'undefined' ) return;

    function initSlider( $wrap ) {
        var $grid = $wrap.find( '.posts__grid' );
        if ( ! $grid.length ) return;

        var arrowsPos = $wrap.attr( 'data-slider-arrows' ) || 'outside';
        var dotsPos   = $wrap.attr( 'data-slider-dots' )   || 'below';
        var autoplay  = $wrap.attr( 'data-slider-autoplay' ) === '1';
        var interval  = parseInt( $wrap.attr( 'data-slider-interval' ), 10 ) || 5000;

        /* Arrow buttons */
        if ( arrowsPos !== 'hidden' ) {
            var $prev = $( '<button type="button" class="posts__slider-arrow posts__slider-arrow--prev" aria-label="Previous">‹</button>' );
            var $next = $( '<button type="button" class="posts__slider-arrow posts__slider-arrow--next" aria-label="Next">›</button>' );
            $wrap.append( $prev ).append( $next );

            $prev.on( 'click', function () { $grid[ 0 ].scrollBy( { left: -$grid[ 0 ].clientWidth, behavior: 'smooth' } ); } );
            $next.on( 'click', function () { $grid[ 0 ].scrollBy( { left:  $grid[ 0 ].clientWidth, behavior: 'smooth' } ); } );
        }

        /* Dot indicators (one per "page" of slides) */
        if ( dotsPos !== 'hidden' ) {
            var $cards = $grid.find( '.posts__card' );
            var $dots  = $( '<div class="posts__slider-dots" role="tablist"></div>' );
            $cards.each( function ( i ) {
                var $d = $( '<button type="button" class="posts__slider-dot" aria-label="Slide ' + ( i + 1 ) + '"></button>' );
                $d.on( 'click', function () {
                    $cards[ i ].scrollIntoView( { behavior: 'smooth', inline: 'start', block: 'nearest' } );
                } );
                $dots.append( $d );
            } );
            $wrap.append( $dots );
        }

        /* Autoplay */
        if ( autoplay ) {
            var stopped = false;
            $wrap.on( 'mouseenter focusin', function () { stopped = true; } );
            $wrap.on( 'mouseleave focusout', function () { stopped = false; } );
            setInterval( function () {
                if ( stopped ) return;
                var atEnd = $grid[ 0 ].scrollLeft + $grid[ 0 ].clientWidth >= $grid[ 0 ].scrollWidth - 4;
                $grid[ 0 ].scrollBy( {
                    left: atEnd ? -$grid[ 0 ].scrollWidth : $grid[ 0 ].clientWidth,
                    behavior: 'smooth'
                } );
            }, interval );
        }
    }

    function initLoadMore( $wrap ) {
        var $btn = $wrap.find( '.posts__loadmore' );
        if ( ! $btn.length ) return;

        $btn.on( 'click', function () {
            var page    = parseInt( $btn.attr( 'data-page' ), 10 ) || 2;
            var maxPage = parseInt( $btn.attr( 'data-max-page' ), 10 ) || 1;
            if ( page > maxPage ) { $btn.prop( 'disabled', true ); return; }

            $btn.prop( 'disabled', true ).addClass( 'is-loading' );

            $.post( ( window.fwScPosts || {} ).ajaxUrl, {
                action: 'fw_sc_posts_loadmore',
                nonce:  ( window.fwScPosts || {} ).nonce,
                page:   page,
                wrap:   $wrap.attr( 'id' ) || ''
            } )
            .done( function ( html ) {
                if ( html ) $wrap.find( '.posts__grid' ).append( html );
                $btn.attr( 'data-page', page + 1 );
                if ( page + 1 > maxPage ) $btn.remove();
            } )
            .always( function () {
                $btn.prop( 'disabled', false ).removeClass( 'is-loading' );
            } );
        } );
    }

    function initInfinite( $wrap ) {
        var $sentinel = $wrap.find( '.posts__infinite-sentinel' );
        if ( ! $sentinel.length || typeof IntersectionObserver === 'undefined' ) return;

        var io = new IntersectionObserver( function ( entries ) {
            entries.forEach( function ( entry ) {
                if ( ! entry.isIntersecting ) return;
                var page    = parseInt( $sentinel.attr( 'data-page' ), 10 ) || 2;
                var maxPage = parseInt( $sentinel.attr( 'data-max-page' ), 10 ) || 1;
                if ( page > maxPage ) { io.disconnect(); return; }

                $.post( ( window.fwScPosts || {} ).ajaxUrl, {
                    action: 'fw_sc_posts_loadmore',
                    nonce:  ( window.fwScPosts || {} ).nonce,
                    page:   page,
                    wrap:   $wrap.attr( 'id' ) || ''
                } )
                .done( function ( html ) {
                    if ( html ) $wrap.find( '.posts__grid' ).append( html );
                    $sentinel.attr( 'data-page', page + 1 );
                    if ( page + 1 > maxPage ) { io.disconnect(); $sentinel.remove(); }
                } );
            } );
        }, { rootMargin: '200px' } );

        io.observe( $sentinel[ 0 ] );
    }

    function initFilters( $wrap ) {
        var $filters = $wrap.find( '.posts__filter' );
        if ( ! $filters.length ) return;

        $filters.on( 'click', function () {
            var $btn = $( this );
            $filters.removeClass( 'is-active' );
            $btn.addClass( 'is-active' );

            var term = $btn.attr( 'data-term' ) || '';
            $.post( ( window.fwScPosts || {} ).ajaxUrl, {
                action: 'fw_sc_posts_filter',
                nonce:  ( window.fwScPosts || {} ).nonce,
                term:   term,
                wrap:   $wrap.attr( 'id' ) || ''
            } )
            .done( function ( html ) {
                if ( typeof html === 'string' ) {
                    $wrap.find( '.posts__grid' ).html( html );
                }
            } );
        } );
    }

    $( function () {
        $( '.posts' ).each( function () {
            var $wrap = $( this );
            if ( $wrap.hasClass( 'posts--mode-slider' ) ) initSlider( $wrap );
            initLoadMore( $wrap );
            initInfinite( $wrap );
            if ( $wrap.hasClass( 'posts--has-filters' ) ) initFilters( $wrap );
        } );
    } );

} )( jQuery );
