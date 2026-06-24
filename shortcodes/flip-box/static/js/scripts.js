/**
 * Flip Box — click trigger. Hover/keyboard-focus flips are pure CSS; this only
 * wires the "On click / tap" trigger (toggle .is-flipped + aria-pressed).
 */
(function () {
	'use strict';

	function init() {
		var nodes = document.querySelectorAll('.fw-fb--click:not(.fw-fb-ready)');
		Array.prototype.forEach.call(nodes, function (el) {
			el.classList.add('fw-fb-ready');

			function toggle() {
				var on = el.classList.toggle('is-flipped');
				el.setAttribute('aria-pressed', on ? 'true' : 'false');
			}

			el.addEventListener('click', function (e) {
				// Let a real link on the back work without toggling back.
				if (e.target.closest('a')) { return; }
				toggle();
			});
			el.addEventListener('keydown', function (e) {
				if (e.key === 'Enter' || e.key === ' ' || e.key === 'Spacebar') {
					if (e.target.closest('a')) { return; }
					e.preventDefault();
					toggle();
				}
			});
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
