/**
 * Section-Background fill — shared runtime (zero dependencies).
 *
 * Any element carrying `.sc-bg-fill` is turned into a full-bleed backdrop of its
 * nearest <section>: moved to be the Section's first child, stretched to cover it
 * (the CSS does inset:0), and the Section's own content is lifted above it.
 *
 * window.scBgFill(el) does the work and RETURNS the host element (so a caller that
 * needs the host — e.g. to bind pointer events to the whole Section — can use it).
 * Elements that self-manage add `data-sc-bg-managed` and call scBgFill themselves;
 * everything else is auto-initialised here on DOM ready.
 */
(function () {
	'use strict';
	if (typeof document === 'undefined' || window.scBgFill) { return; }

	function scBgFill(el) {
		if (!el || el.__scBgDone) { return el ? el.__scBgHost : null; }
		el.__scBgDone = true;

		// Prefer the full-bleed <section> so the backdrop breaks out of the boxed,
		// centered .fw-container; fall back to an inner container/row, then the parent.
		var host = el.closest('section, .fw-main-row')
			|| el.closest('.fw-container-fluid, .fw-container, .fw-row')
			|| el.parentElement;
		if (!host) { return null; }

		if (getComputedStyle(host).position === 'static') { host.style.position = 'relative'; }
		host.classList.add('sc-bg-host');
		if (host.firstChild !== el) { host.insertBefore(el, host.firstChild); }

		// Lift the Section's real content above the backdrop — but NOT a decorative
		// `.pattern-layer` (a Section Background Pattern), which is itself a backdrop and must
		// stay behind the fill (it keeps its own z-index:0). Lifting it would paint the pattern
		// over the fill (it sits after the fill in the DOM), hiding video / 3D-gallery backdrops.
		Array.prototype.forEach.call(host.children, function (ch) {
			if (ch === el || (ch.classList && ch.classList.contains('pattern-layer'))) { return; }
			if (getComputedStyle(ch).position === 'static') { ch.style.position = 'relative'; }
			if (!ch.style.zIndex) { ch.style.zIndex = '1'; }
		});

		el.__scBgHost = host;
		return host;
	}

	window.scBgFill = scBgFill;

	function init() {
		var nodes = document.querySelectorAll('.sc-bg-fill:not([data-sc-bg-managed])');
		Array.prototype.forEach.call(nodes, scBgFill);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
