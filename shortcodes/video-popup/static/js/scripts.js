/**
 * Video Popup — opens the video in a lightbox (one shared overlay). Supports
 * YouTube, Vimeo (iframe with autoplay) and self-hosted files (<video>).
 */
(function () {
	'use strict';

	var overlay = null, frame = null, lastFocus = null;

	function build() {
		if (overlay) { return; }
		overlay = document.createElement('div');
		overlay.className = 'fw-vp-lb';
		overlay.setAttribute('role', 'dialog');
		overlay.setAttribute('aria-modal', 'true');
		overlay.innerHTML =
			'<div class="fw-vp-lb__frame"></div>' +
			'<button type="button" class="fw-vp-lb__close" aria-label="Close">' +
			'<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg></button>';
		document.body.appendChild(overlay);
		frame = overlay.querySelector('.fw-vp-lb__frame');
		overlay.querySelector('.fw-vp-lb__close').addEventListener('click', close);
		overlay.addEventListener('click', function (e) { if (e.target === overlay) { close(); } });
	}

	function media(type, src) {
		if (type === 'youtube') {
			return '<iframe src="https://www.youtube.com/embed/' + encodeURIComponent(src) +
				'?autoplay=1&rel=0&playsinline=1" allow="autoplay; fullscreen; encrypted-media" allowfullscreen></iframe>';
		}
		if (type === 'vimeo') {
			return '<iframe src="https://player.vimeo.com/video/' + encodeURIComponent(src) +
				'?autoplay=1" allow="autoplay; fullscreen" allowfullscreen></iframe>';
		}
		return '<video src="' + encodeURI(src) + '" controls autoplay playsinline></video>';
	}

	function open(type, src) {
		if (!src) { return; }
		build();
		lastFocus = document.activeElement;
		frame.innerHTML = media(type, src);
		document.body.style.overflow = 'hidden';
		void overlay.offsetWidth;
		overlay.classList.add('is-open');
		document.addEventListener('keydown', onKey, true);
		var c = overlay.querySelector('.fw-vp-lb__close');
		if (c) { c.focus(); }
	}

	function close() {
		if (!overlay || !overlay.classList.contains('is-open')) { return; }
		overlay.classList.remove('is-open');
		document.body.style.overflow = '';
		document.removeEventListener('keydown', onKey, true);
		window.setTimeout(function () { if (frame) { frame.innerHTML = ''; } }, 280);
		if (lastFocus && lastFocus.focus) { lastFocus.focus(); }
	}

	function onKey(e) { if (e.key === 'Escape') { close(); } }

	document.addEventListener('click', function (e) {
		var trig = e.target.closest ? e.target.closest('.fw-vp__trigger') : null;
		if (!trig) { return; }
		var src = trig.getAttribute('data-vp-src');
		if (!src) { return; }
		e.preventDefault();
		open(trig.getAttribute('data-vp-type') || 'file', src);
	}, false);
})();
