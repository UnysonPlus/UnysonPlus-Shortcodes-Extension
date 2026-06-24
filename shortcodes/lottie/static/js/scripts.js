/**
 * Lottie Animation shortcode — initialises each [data-lottie] with lottie-web,
 * wiring the chosen trigger (autoplay / viewport / hover / click).
 */
(function () {
	'use strict';

	function initOne(root) {
		if (root.__ltReady || typeof window.lottie === 'undefined') { return; }
		root.__ltReady = true;

		var canvas = root.querySelector('.fw-lottie__canvas') || root;
		var src = root.getAttribute('data-src');
		if (!src) { return; }

		var trigger = root.getAttribute('data-trigger') || 'autoplay';
		var loop = root.getAttribute('data-loop') === '1';
		var speed = parseFloat(root.getAttribute('data-speed')) || 1;
		var dir = parseInt(root.getAttribute('data-direction'), 10) || 1;
		var revHover = root.getAttribute('data-reverse-hover') === '1';

		var anim;
		try {
			anim = window.lottie.loadAnimation({
				container: canvas,
				renderer: 'svg',
				loop: loop,
				autoplay: trigger === 'autoplay',
				path: src
			});
		} catch (e) { return; }

		anim.addEventListener('DOMLoaded', function () {
			anim.setSpeed(speed);
			anim.setDirection(dir);
		});

		if (trigger === 'viewport') {
			if ('IntersectionObserver' in window) {
				var io = new IntersectionObserver(function (entries) {
					entries.forEach(function (en) {
						if (en.isIntersecting) {
							anim.setDirection(dir);
							anim.play();
							if (!loop) { io.unobserve(root); }
						} else if (loop) {
							anim.pause();
						}
					});
				}, { threshold: 0.25 });
				io.observe(root);
			} else {
				anim.play();
			}
		} else if (trigger === 'hover') {
			root.style.cursor = 'pointer';
			root.addEventListener('mouseenter', function () { anim.setDirection(dir); anim.play(); });
			root.addEventListener('mouseleave', function () {
				if (revHover) { anim.setDirection(-dir); anim.play(); }
				else { anim.pause(); }
			});
		} else if (trigger === 'click') {
			root.style.cursor = 'pointer';
			root.addEventListener('click', function () {
				if (anim.isPaused) { anim.setDirection(dir); anim.play(); }
				else { anim.pause(); }
			});
		}
	}

	function init() {
		Array.prototype.forEach.call(document.querySelectorAll('[data-lottie]'), initOne);
	}
	if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', init); } else { init(); }
})();
