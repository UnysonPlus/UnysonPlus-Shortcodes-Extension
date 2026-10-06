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

	function initClick(scope) {
		var nodes = (scope || document).querySelectorAll(
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

	function initFlipBtn(scope) {
		var btns = (scope || document).querySelectorAll('.fw-fb__flip-btn:not(.fw-fb-fbtn-ready)');
		Array.prototype.forEach.call(btns, function (btn) {
			btn.classList.add('fw-fb-fbtn-ready');
			btn.addEventListener('click', function () {
				var card = btn.closest('.fw-fb');
				if (card) { toggle(card); }
			});
		});
	}

	function init(scope) { initClick(scope); initFlipBtn(scope); }

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
