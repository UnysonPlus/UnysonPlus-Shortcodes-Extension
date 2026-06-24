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
			Array.prototype.forEach.call(root.querySelectorAll('.fw-hs__point.is-open'), function (o) { o.classList.remove('is-open'); });
			if (!open) { point.classList.add('is-open'); flip(point); }
			e.preventDefault();
			return;
		}
		if (!(e.target.closest && e.target.closest('.fw-hs__point.is-open'))) {
			Array.prototype.forEach.call(document.querySelectorAll('.fw-hs__point.is-open'), function (o) { o.classList.remove('is-open'); });
		}
	}, false);

	document.addEventListener('keydown', function (e) {
		if (e.key === 'Escape') {
			Array.prototype.forEach.call(document.querySelectorAll('.fw-hs__point.is-open'), function (o) { o.classList.remove('is-open'); });
		}
	});
})();
