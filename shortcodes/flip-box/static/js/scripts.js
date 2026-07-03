/**
 * Flip Box runtime.
 *  - Click / "Hover + click" trigger: toggle .is-flipped (+ aria-pressed). Hover flips CSS.
 *  - Front flip button (.fw-fb__flip-btn): flips the card to the back (works on any trigger).
 * (Parallax is pure CSS — content depth via translateZ — so no JS needed for it.)
 */
(function () {
	'use strict';

	function toggle(card) {
		var on = card.classList.toggle('is-flipped');
		card.setAttribute('aria-pressed', on ? 'true' : 'false');
	}

	function initClick() {
		var nodes = document.querySelectorAll(
			'.fw-fb--click:not(.fw-fb-click-ready), .fw-fb--both:not(.fw-fb-click-ready)'
		);
		Array.prototype.forEach.call(nodes, function (el) {
			el.classList.add('fw-fb-click-ready');
			el.addEventListener('click', function (e) {
				if (e.target.closest('a, .fw-fb__flip-btn')) { return; } // handled elsewhere
				toggle(el);
			});
			el.addEventListener('keydown', function (e) {
				if (e.key === 'Enter' || e.key === ' ' || e.key === 'Spacebar') {
					if (e.target.closest('a, button')) { return; }
					e.preventDefault();
					toggle(el);
				}
			});
		});
	}

	function initFlipBtn() {
		var btns = document.querySelectorAll('.fw-fb__flip-btn:not(.fw-fb-fbtn-ready)');
		Array.prototype.forEach.call(btns, function (btn) {
			btn.classList.add('fw-fb-fbtn-ready');
			btn.addEventListener('click', function () {
				var card = btn.closest('.fw-fb');
				if (card) { toggle(card); }
			});
		});
	}

	function init() { initClick(); initFlipBtn(); }

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
