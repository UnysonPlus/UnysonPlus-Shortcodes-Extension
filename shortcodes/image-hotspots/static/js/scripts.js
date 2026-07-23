/**
 * Image Hotspots — click trigger (toggle a point's tooltip, close others, Esc /
 * outside-click close) and a flip so the tooltip never leaves the top of the
 * viewport. Hover tooltips are pure CSS; this only enhances them.
 */
(function () {
	'use strict';

	function flip(point) {
		var pop = point.querySelector('.fw-hs__pop');
		if (!pop) { return; }
		point.classList.remove('fw-hs--flip');
		var r = pop.getBoundingClientRect();
		if (r.top < 8) { point.classList.add('fw-hs--flip'); }
	}

	// Keep the pin's aria-expanded in sync with the point's open state.
	function setOpen(point, open) {
		point.classList.toggle('is-open', open);
		var pin = point.querySelector('.fw-hs__pin');
		if (pin && pin.hasAttribute('aria-expanded')) {
			pin.setAttribute('aria-expanded', open ? 'true' : 'false');
		}
	}

	function closeAll(scope) {
		Array.prototype.forEach.call(
			(scope || document).querySelectorAll('.fw-hs__point.is-open'),
			function (o) { setOpen(o, false); }
		);
	}

	document.addEventListener('mouseover', function (e) {
		var p = e.target.closest ? e.target.closest('.fw-hs--hover .fw-hs__point') : null;
		if (p) { flip(p); }
	}, true);

	document.addEventListener('click', function (e) {
		var pin = e.target.closest ? e.target.closest('.fw-hs--click .fw-hs__pin') : null;
		if (pin) {
			var point = pin.closest('.fw-hs__point');
			var root = pin.closest('.fw-hs');
			var open = point.classList.contains('is-open');
			closeAll(root);
			if (!open) { setOpen(point, true); flip(point); }
			e.preventDefault();
			return;
		}
		if (!(e.target.closest && e.target.closest('.fw-hs__point.is-open'))) {
			closeAll();
		}
	}, false);

	document.addEventListener('keydown', function (e) {
		if (e.key === 'Escape') {
			closeAll();
		}
	});
})();
