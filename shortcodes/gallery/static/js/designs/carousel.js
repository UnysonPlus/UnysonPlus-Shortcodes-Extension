/**
 * Gallery — shared Splide mount for the slider-based designs (Carousel,
 * Slideshow/Fade, Coverflow). Each such design's root element carries
 * [data-fw-splide] plus a data-splide config (Splide auto-reads data-splide), so
 * this just instantiates and mounts every not-yet-initialised one. Designs that
 * need a custom mount (Thumbnail Slider syncs two sliders) ship their own JS and
 * do NOT use [data-fw-splide].
 */
(function () {
	'use strict';

	function init(scope) {
		if (typeof window.Splide === 'undefined') {
			return;
		}
		var nodes = (scope || document).querySelectorAll('.fw-gallery [data-fw-splide]:not(.is-initialized)');
		Array.prototype.forEach.call(nodes, function (el) {
			try {
				new window.Splide(el).mount();
				el.classList.add('is-initialized');
			} catch (e) {
				// Leave the static markup in place if Splide fails to mount.
			}
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', function () { init(); });
	} else {
		init();
	}

	/* Re-init hook for surfaces that insert this element's markup after page load (the
	   block editor's preview, the Elementor editor's re-renders): they call every
	   window.fwShortcodeInit entry with the document or element holding the new markup.
	   init() skips what it has already initialised, so repeated calls are safe. */
	window.fwShortcodeInit = window.fwShortcodeInit || [];
	window.fwShortcodeInit.push(init);
})();
