/**
 * Gallery — Thumbnail Slider design. Mounts a main image slider + a thumbnail
 * nav slider and syncs them. Configs come from the root's data-thumbnav-main /
 * data-thumbnav-nav JSON (built in designs/thumbslider.php). Shares the bundled
 * Splide library with the other slider designs.
 */
(function () {
	'use strict';

	function parse(el, attr) {
		try { return JSON.parse(el.getAttribute(attr) || '{}'); }
		catch (e) { return {}; }
	}

	function init() {
		if (typeof window.Splide === 'undefined') { return; }
		var roots = document.querySelectorAll('.fw-gallery__tnav:not(.is-initialized)');
		Array.prototype.forEach.call(roots, function (root) {
			var mainEl = root.querySelector('.fw-gallery__tnav-main');
			var navEl = root.querySelector('.fw-gallery__tnav-nav');
			if (!mainEl || !navEl) { return; }
			try {
				var main = new window.Splide(mainEl, parse(root, 'data-thumbnav-main'));
				var nav = new window.Splide(navEl, parse(root, 'data-thumbnav-nav'));
				main.sync(nav);
				main.mount();
				nav.mount();
				root.classList.add('is-initialized');
			} catch (e) {
				// Leave the static markup if a slider fails to mount.
			}
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
