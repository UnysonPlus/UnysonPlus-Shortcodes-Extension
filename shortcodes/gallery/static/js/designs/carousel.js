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

	function init() {
		if (typeof window.Splide === 'undefined') {
			return;
		}
		var nodes = document.querySelectorAll('.fw-gallery [data-fw-splide]:not(.is-initialized)');
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
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
