/**
 * Modal / Popup — moves each overlay to <body> (so position:fixed can't be
 * clipped by a transformed ancestor), then handles open/close, Esc, overlay
 * click, body-scroll lock, focus return, and the optional open-on-load.
 */
(function () {
	'use strict';

	var openOverlay = null, lastFocus = null, scrollY = 0;

	function lock() {
		scrollY = window.scrollY || window.pageYOffset || 0;
		document.body.style.overflow = 'hidden';
	}
	function unlock() { document.body.style.overflow = ''; }

	function open(overlay, trigger) {
		if (openOverlay) { return; }
		openOverlay = overlay;
		lastFocus = trigger || document.activeElement;
		overlay.setAttribute('aria-hidden', 'false');
		lock();
		void overlay.offsetWidth;
		overlay.classList.add('is-open');
		var c = overlay.querySelector('.fw-mp__close');
		if (c) { c.focus(); }
	}

	function close() {
		if (!openOverlay) { return; }
		var ov = openOverlay;
		ov.classList.remove('is-open');
		ov.setAttribute('aria-hidden', 'true');
		unlock();
		openOverlay = null;
		if (lastFocus && lastFocus.focus) { lastFocus.focus(); }
	}

	function init(scope) {
		var roots = (scope && scope.querySelectorAll ? scope : document).querySelectorAll('.fw-mp');
		Array.prototype.forEach.call(roots, function (root) {
			if (root.__mpReady) { return; }
			root.__mpReady = true;
			var trigger = root.querySelector('.fw-mp__trigger');
			var overlay = root.querySelector('.fw-mp__overlay');
			if (!overlay) { return; }

			// Move overlay to <body>.
			document.body.appendChild(overlay);

			if (trigger) {
				trigger.addEventListener('click', function (e) { e.preventDefault(); open(overlay, trigger); });
			}
			overlay.querySelector('.fw-mp__close').addEventListener('click', close);
			overlay.addEventListener('click', function (e) {
				if (e.target === overlay && overlay.getAttribute('data-mp-close-overlay') === '1') { close(); }
			});

			if (overlay.getAttribute('data-mp-onload') === '1') {
				var delay = parseInt(overlay.getAttribute('data-mp-delay'), 10) || 0;
				window.setTimeout(function () { open(overlay, trigger); }, delay);
			}
		});
	}

	document.addEventListener('keydown', function (e) {
		if (!openOverlay) { return; }
		if (e.key === 'Escape') { close(); return; }
		// Simple focus trap.
		if (e.key === 'Tab') {
			var f = openOverlay.querySelectorAll('a[href],button:not([disabled]),input,select,textarea,[tabindex]:not([tabindex="-1"])');
			if (!f.length) { return; }
			var first = f[0], last = f[f.length - 1];
			if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
			else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
		}
	});

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
