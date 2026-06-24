/**
 * Tooltip — click trigger (toggle .is-open, close on outside click / Esc) and a
 * viewport-flip: if the bubble would overflow, swap the position class. Hover/
 * focus tooltips are pure CSS; this only enhances them.
 */
(function () {
	'use strict';

	var OPP = { top: 'bottom', bottom: 'top', left: 'right', right: 'left' };

	function flipIfNeeded(tt) {
		var bubble = tt.querySelector('.fw-tt__bubble');
		if (!bubble) { return; }
		// Measure regardless of visibility.
		var r = bubble.getBoundingClientRect();
		var pos = ['top', 'bottom', 'left', 'right'].filter(function (p) { return tt.classList.contains('fw-tt--pos-' + p); })[0];
		if (!pos) { return; }
		var pad = 8, vw = window.innerWidth, vh = window.innerHeight;
		var off = (pos === 'top' && r.top < pad) ||
			(pos === 'bottom' && r.bottom > vh - pad) ||
			(pos === 'left' && r.left < pad) ||
			(pos === 'right' && r.right > vw - pad);
		if (off && !tt.__flipped) {
			tt.classList.remove('fw-tt--pos-' + pos);
			tt.classList.add('fw-tt--pos-' + OPP[pos]);
			tt.__flipped = pos;
		}
	}

	// Hover/focus: flip check when shown.
	document.addEventListener('mouseover', function (e) {
		var tt = e.target.closest ? e.target.closest('.fw-tt--hover') : null;
		if (tt) { flipIfNeeded(tt); }
	}, true);
	document.addEventListener('focusin', function (e) {
		var tt = e.target.closest ? e.target.closest('.fw-tt--hover') : null;
		if (tt) { flipIfNeeded(tt); }
	}, true);

	// Click triggers.
	document.addEventListener('click', function (e) {
		var trig = e.target.closest ? e.target.closest('.fw-tt--click .fw-tt__trigger') : null;
		if (trig) {
			var tt = trig.closest('.fw-tt--click');
			e.preventDefault();
			var wasOpen = tt.classList.contains('is-open');
			// Close any other open ones.
			Array.prototype.forEach.call(document.querySelectorAll('.fw-tt--click.is-open'), function (o) { o.classList.remove('is-open'); });
			if (!wasOpen) { tt.classList.add('is-open'); flipIfNeeded(tt); }
			return;
		}
		// Click outside closes open click-tooltips.
		if (!(e.target.closest && e.target.closest('.fw-tt--click.is-open'))) {
			Array.prototype.forEach.call(document.querySelectorAll('.fw-tt--click.is-open'), function (o) { o.classList.remove('is-open'); });
		}
	}, false);

	document.addEventListener('keydown', function (e) {
		if (e.key === 'Escape') {
			Array.prototype.forEach.call(document.querySelectorAll('.fw-tt--click.is-open'), function (o) { o.classList.remove('is-open'); });
		}
	});
})();
