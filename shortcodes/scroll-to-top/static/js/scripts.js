/**
 * Scroll to Top & Reading Progress — page-scroll-linked.
 * One handler shared across all instances; uses rAF to throttle scroll work.
 */
(function () {
	'use strict';

	function init() {
		var roots = Array.prototype.slice.call(document.querySelectorAll('[data-stt]'));
		if (!roots.length) { return; }

		var insts = roots.map(function (root) {
			if (root.__sttReady) { return null; }
			root.__sttReady = true;
			var btn = root.querySelector('[data-stt-top]');
			var fill = root.querySelector('.fw-stt__progress-fill');
			var after = parseInt(root.getAttribute('data-after'), 10) || 300;
			if (btn) {
				btn.addEventListener('click', function () {
					window.scrollTo({ top: 0, behavior: 'smooth' });
				});
			}
			return { root: root, btn: btn, fill: fill, after: after };
		}).filter(Boolean);
		if (!insts.length) { return; }

		var ticking = false;
		function update() {
			ticking = false;
			var doc = document.documentElement;
			var scrollTop = window.pageYOffset || doc.scrollTop || 0;
			var max = (doc.scrollHeight - window.innerHeight);
			var pct = max > 0 ? Math.max(0, Math.min(1, scrollTop / max)) : 0;
			for (var i = 0; i < insts.length; i++) {
				var it = insts[i];
				if (it.fill) { it.fill.style.width = (pct * 100) + '%'; }
				if (it.btn) { it.root.classList.toggle('is-visible', scrollTop > it.after); }
			}
		}
		function onScroll() {
			if (!ticking) { ticking = true; window.requestAnimationFrame(update); }
		}
		window.addEventListener('scroll', onScroll, { passive: true });
		window.addEventListener('resize', onScroll, { passive: true });
		update();
	}

	if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', init); } else { init(); }
})();
