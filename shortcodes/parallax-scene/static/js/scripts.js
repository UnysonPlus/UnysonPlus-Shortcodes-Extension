/* Parallax Scene runtime — self-contained (no Animation Engine dependency).
 *  - Entrance: reveal each layer's .fw-ps-enter (with its data-delay) when the scene scrolls into view.
 *  - Parallax: translate each .fw-ps-move by depth × scene-progress (scroll) and/or pointer.
 *  Honours prefers-reduced-motion (skips parallax; CSS freezes sway + entrance).
 */
(function () {
	'use strict';
	var reduce = window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches;

	function init(scene) {
		if (scene._ps) { return; }
		scene._ps = true;
		var source    = scene.getAttribute('data-source') || 'scroll';
		var intensity = parseFloat(scene.getAttribute('data-intensity')) || 60;
		var moves     = [].slice.call(scene.querySelectorAll('.fw-ps-move'));
		var enters    = [].slice.call(scene.querySelectorAll('.fw-ps-enter'));

		// Entrance.
		if ('IntersectionObserver' in window) {
			var io = new IntersectionObserver(function (es) {
				es.forEach(function (e) {
					if (!e.isIntersecting) { return; }
					var el = e.target, d = parseFloat(el.getAttribute('data-delay')) || 0;
					el.style.transitionDelay = d + 'ms';
					el.classList.add('is-in');
					io.unobserve(el);
				});
			}, { threshold: 0, rootMargin: '0px 0px -8% 0px' });
			enters.forEach(function (el) { io.observe(el); });
		} else {
			enters.forEach(function (el) { el.classList.add('is-in'); });
		}

		if (reduce || source === 'none' || !moves.length) { return; }

		// Parallax.
		var mx = 0, my = 0, ticking = false;
		function apply() {
			var r = scene.getBoundingClientRect(), vh = window.innerHeight || 1;
			// progress: +1 when the scene sits below centre, -1 when above (0 when centred).
			var prog = ((r.top + r.height / 2) - vh / 2) / (vh / 2 + r.height / 2);
			for (var i = 0; i < moves.length; i++) {
				var depth = (parseFloat(moves[i].getAttribute('data-depth')) || 0) / 100;
				var tx = 0, ty = 0;
				if (source === 'scroll' || source === 'both') { ty += -prog * intensity * depth; }
				if (source === 'pointer' || source === 'both') { tx += mx * intensity * depth * 0.5; ty += my * intensity * depth * 0.5; }
				moves[i].style.transform = 'translate(' + tx.toFixed(2) + 'px,' + ty.toFixed(2) + 'px)';
			}
		}
		function schedule() { if (!ticking) { ticking = true; requestAnimationFrame(function () { apply(); ticking = false; }); } }
		addEventListener('scroll', schedule, { passive: true });
		addEventListener('resize', schedule, { passive: true });
		if (source === 'pointer' || source === 'both') {
			addEventListener('pointermove', function (e) {
				mx = (e.clientX / (window.innerWidth || 1) - 0.5) * 2;
				my = (e.clientY / (window.innerHeight || 1) - 0.5) * 2;
				schedule();
			}, { passive: true });
		}
		apply();
	}

	function boot() { [].slice.call(document.querySelectorAll('.fw-parallax-scene')).forEach(init); }
	if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', boot); } else { boot(); }
	window.fwParallaxSceneRescan = boot;
})();
