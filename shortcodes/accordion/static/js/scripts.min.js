( function () {
    'use strict';

    // WAI-ARIA accordion (disclosure) pattern: the interactive handle is the
    // .accordion-trigger <button> (carries aria-expanded, id, keyboard + focus).
    // The .ui-state-active styling class stays on the .accordion-title bar; the
    // panel is .accordion-content (role="region", aria-hidden).
    // Vanilla JS — jQuery slideDown/slideUp replaced by a height-transition helper.

    var SLIDE_MS = 200;

    // jQuery's slideDown/slideUp animate height AND vertical padding/margin
    // together — animating height alone makes the panel's padding pop in/out
    // instantly (a visible jump). So these helpers transition all five
    // box properties in lockstep, using border-box height so the numbers
    // stay consistent regardless of the element's own box-sizing.
    var SLIDE_PROPS = [ 'height', 'padding-top', 'padding-bottom', 'margin-top', 'margin-bottom' ];

    function slideCleanup( el ) {
        if ( el._slideTimer ) { clearTimeout( el._slideTimer ); el._slideTimer = null; }
        SLIDE_PROPS.forEach( function ( p ) { el.style.removeProperty( p ); } );
        el.style.removeProperty( 'overflow' );
        el.style.removeProperty( 'transition' );
        el.style.removeProperty( 'box-sizing' );
    }

    function slideTransition() {
        return SLIDE_PROPS.map( function ( p ) {
            return p + ' ' + SLIDE_MS + 'ms ease';
        } ).join( ', ' );
    }

    function slideDown( el ) {
        slideCleanup( el );
        el.style.removeProperty( 'display' );
        var display = window.getComputedStyle( el ).display;
        if ( display === 'none' ) { display = 'block'; }
        el.style.display = display;

        // Natural end values (rendered, border-box).
        var cs        = window.getComputedStyle( el );
        var targetH   = el.getBoundingClientRect().height;
        var targets   = {
            'height':         targetH + 'px',
            'padding-top':    cs.paddingTop,
            'padding-bottom': cs.paddingBottom,
            'margin-top':     cs.marginTop,
            'margin-bottom':  cs.marginBottom
        };

        // Collapse everything to 0, commit, then transition to the targets.
        el.style.boxSizing = 'border-box';
        el.style.overflow  = 'hidden';
        SLIDE_PROPS.forEach( function ( p ) { el.style.setProperty( p, '0px' ); } );
        void el.offsetHeight; // reflow so the 0 start is painted
        el.style.transition = slideTransition();
        SLIDE_PROPS.forEach( function ( p ) { el.style.setProperty( p, targets[ p ] ); } );

        el._slideTimer = setTimeout( function () {
            slideCleanup( el );
        }, SLIDE_MS );
    }

    function slideUp( el ) {
        slideCleanup( el );

        // Pin the current rendered values as the transition start point.
        var cs = window.getComputedStyle( el );
        el.style.boxSizing = 'border-box';
        el.style.overflow  = 'hidden';
        el.style.height          = el.getBoundingClientRect().height + 'px';
        el.style.paddingTop      = cs.paddingTop;
        el.style.paddingBottom   = cs.paddingBottom;
        el.style.marginTop       = cs.marginTop;
        el.style.marginBottom    = cs.marginBottom;
        void el.offsetHeight;
        el.style.transition = slideTransition();
        SLIDE_PROPS.forEach( function ( p ) { el.style.setProperty( p, '0px' ); } );

        el._slideTimer = setTimeout( function () {
            el.style.display = 'none';
            slideCleanup( el );
        }, SLIDE_MS );
    }

    function parts( trigger ) {
        var item = trigger.closest( '.accordion-item' );
        return {
            item:    item,
            bar:     item ? item.querySelector( '.accordion-title' )   : null,
            content: item ? item.querySelector( '.accordion-content' ) : null
        };
    }

    function isOpen( trigger ) {
        var p = parts( trigger );
        return !! ( p.bar && p.bar.classList.contains( 'ui-state-active' ) );
    }

    function openPanel( trigger, animate ) {
        var p = parts( trigger );
        if ( ! p.bar || ! p.content ) { return; }
        p.bar.classList.add( 'ui-state-active' );
        trigger.setAttribute( 'aria-expanded', 'true' );
        if ( animate === false ) {
            p.content.style.removeProperty( 'display' );
            if ( window.getComputedStyle( p.content ).display === 'none' ) {
                p.content.style.display = 'block';
            }
        } else {
            slideDown( p.content );
        }
        p.content.setAttribute( 'aria-hidden', 'false' );
    }

    function closePanel( trigger, animate ) {
        var p = parts( trigger );
        if ( ! p.bar || ! p.content ) { return; }
        p.bar.classList.remove( 'ui-state-active' );
        trigger.setAttribute( 'aria-expanded', 'false' );
        if ( animate === false ) {
            p.content.style.display = 'none';
        } else {
            slideUp( p.content );
        }
        p.content.setAttribute( 'aria-hidden', 'true' );
    }

    function initAccordion( accordion ) {
        var multipleOpen  = accordion.getAttribute( 'data-multiple-open' ) === 'true';
        var collapsible   = accordion.getAttribute( 'data-collapsible' ) === 'true';
        var initiallyOpen = accordion.getAttribute( 'data-initially-open' ) || 'first';
        var hashLinking   = accordion.getAttribute( 'data-hash-linking' ) === 'true';
        var triggers      = Array.prototype.slice.call( accordion.querySelectorAll( '.accordion-trigger' ) );

        function openTriggers() {
            return triggers.filter( isOpen );
        }

        // Single-open accordions can't honour "open all" — collapse all but the first.
        if ( initiallyOpen === 'all' && ! multipleOpen ) {
            triggers.slice( 1 ).forEach( function ( t ) { closePanel( t, false ); } );
        }

        // URL hash deep-linking. Match either a trigger id or a panel id.
        if ( hashLinking ) {
            var rawHash = ( window.location.hash || '' ).replace( /^#/, '' );
            if ( rawHash ) {
                var target  = accordion.querySelector( '#' + window.CSS.escape( rawHash ) );
                var trigger = null;
                if ( target && target.classList.contains( 'accordion-trigger' ) ) {
                    trigger = target;
                } else if ( target && target.classList.contains( 'accordion-content' ) ) {
                    var item = target.closest( '.accordion-item' );
                    trigger  = item ? item.querySelector( '.accordion-trigger' ) : null;
                }
                if ( trigger ) {
                    if ( ! multipleOpen ) {
                        openTriggers().forEach( function ( t ) { closePanel( t, false ); } );
                    }
                    openPanel( trigger, false );
                    setTimeout( function () {
                        var item = trigger.closest( '.accordion-item' );
                        if ( item ) { item.scrollIntoView( { behavior: 'smooth', block: 'start' } ); }
                    }, 50 );
                }
            }
        }

        // Expand-All / Collapse-All convenience buttons.
        Array.prototype.forEach.call( accordion.querySelectorAll( '[data-accordion-action="expand-all"]' ), function ( btn ) {
            btn.addEventListener( 'click', function ( e ) {
                e.preventDefault();
                triggers.forEach( function ( t ) { if ( ! isOpen( t ) ) { openPanel( t, true ); } } );
            } );
        } );
        Array.prototype.forEach.call( accordion.querySelectorAll( '[data-accordion-action="collapse-all"]' ), function ( btn ) {
            btn.addEventListener( 'click', function ( e ) {
                e.preventDefault();
                triggers.forEach( function ( t ) { if ( isOpen( t ) ) { closePanel( t, true ); } } );
            } );
        } );

        // Toggle. The trigger is a native <button>, so it fires 'click' on
        // Enter/Space itself — binding 'click' only avoids a double-toggle.
        triggers.forEach( function ( trigger ) {
            trigger.addEventListener( 'click', function () {
                var active = isOpen( trigger );

                if ( multipleOpen ) {
                    if ( active && ! collapsible && openTriggers().length <= 1 ) { return; }
                    if ( active ) { closePanel( trigger, true ); } else { openPanel( trigger, true ); }
                } else {
                    if ( active ) {
                        if ( ! collapsible ) { return; }
                        closePanel( trigger, true );
                    } else {
                        openTriggers().forEach( function ( t ) { closePanel( t, true ); } );
                        openPanel( trigger, true );
                    }
                }

                if ( hashLinking && isOpen( trigger ) && trigger.id ) {
                    history.replaceState( null, '', '#' + trigger.id );
                }
            } );
        } );
    }

    function init() {
        Array.prototype.forEach.call( document.querySelectorAll( '.accordion' ), initAccordion );
    }

    if ( document.readyState === 'loading' ) {
        document.addEventListener( 'DOMContentLoaded', init );
    } else {
        init();
    }

} )();
