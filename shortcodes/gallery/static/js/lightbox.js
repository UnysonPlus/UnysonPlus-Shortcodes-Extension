/**
 * Gallery shortcode — self-contained lightbox (zero dependencies).
 *
 * Any element with [data-fw-lightbox="<group>"] opens the lightbox on click.
 * Slides are collected from every element sharing the same group value, so each
 * gallery instance (unique group id) navigates only its own images.
 *
 * NAVIGATION-PROOFING: on some sites the click event arriving at our handler is
 * NOT cancelable (a "fast click"/synthetic-click layer), so the browser follows
 * the anchor's href no matter what preventDefault does. We therefore NEUTRALISE
 * the trigger anchors as soon as JS runs — the real URL is moved to data-fw-full
 * and the href is removed, so there is simply nothing to navigate to. The
 * lightbox reads data-fw-full; non-JS visitors keep the href (it is only stripped
 * when JS is present). role="button" + Enter/Space keep it keyboard-accessible.
 */
(function () {
	'use strict';

	if (typeof document === 'undefined' || window.__fwGalleryLightbox) {
		return;
	}
	window.__fwGalleryLightbox = true;

	/* --------------------------- anchor neutralising ------------------------ */
	function neutralize(a) {
		if (!a || a.nodeType !== 1) { return; }
		if (a.hasAttribute('href')) {
			if (!a.hasAttribute('data-fw-full')) {
				a.setAttribute('data-fw-full', a.getAttribute('href'));
			}
			a.removeAttribute('href');
			if (!a.getAttribute('role')) { a.setAttribute('role', 'button'); }
			if (!a.hasAttribute('tabindex')) { a.setAttribute('tabindex', '0'); }
		}
	}
	function neutralizeAll() {
		var nodes = document.querySelectorAll('[data-fw-lightbox][href]');
		Array.prototype.forEach.call(nodes, neutralize);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', neutralizeAll);
	} else {
		neutralizeAll();
	}

	// Just-in-time neutralising for anchors added after load (AJAX galleries) —
	// these pointer events fire before the click, so the href is gone in time.
	function neutralizeFromEvent(e) {
		var a = e.target && e.target.closest ? e.target.closest('[data-fw-lightbox]') : null;
		if (a) { neutralize(a); }
	}
	document.addEventListener('mouseover', neutralizeFromEvent, true);
	document.addEventListener('touchstart', neutralizeFromEvent, { capture: true, passive: true });
	document.addEventListener('focusin', neutralizeFromEvent, true);

	/* --------------------------- lightbox internals ------------------------- */
	var REDUCED = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	var overlay = null;
	var imgEl, captionEl, counterEl, spinnerEl, stageEl;
	var slides = [];
	var current = 0;
	var lastFocus = null;
	var preloaded = {};

	var ICONS = {
		close: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>',
		prev: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 5l-7 7 7 7"/></svg>',
		next: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 5l7 7-7 7"/></svg>'
	};

	function build() {
		if (overlay) { return; }
		overlay = document.createElement('div');
		overlay.className = 'fw-lightbox';
		overlay.setAttribute('role', 'dialog');
		overlay.setAttribute('aria-modal', 'true');
		overlay.setAttribute('aria-label', 'Image gallery');
		overlay.innerHTML =
			'<div class="fw-lightbox__counter" aria-live="polite"></div>' +
			'<button type="button" class="fw-lightbox__btn fw-lightbox__close" aria-label="Close (Esc)">' + ICONS.close + '</button>' +
			'<button type="button" class="fw-lightbox__btn fw-lightbox__prev" aria-label="Previous image">' + ICONS.prev + '</button>' +
			'<button type="button" class="fw-lightbox__btn fw-lightbox__next" aria-label="Next image">' + ICONS.next + '</button>' +
			'<figure class="fw-lightbox__stage">' +
			'<span class="fw-lightbox__spinner" aria-hidden="true"></span>' +
			'<img class="fw-lightbox__img" alt="" draggable="false" />' +
			'<figcaption class="fw-lightbox__caption"></figcaption>' +
			'</figure>';

		document.body.appendChild(overlay);

		imgEl = overlay.querySelector('.fw-lightbox__img');
		captionEl = overlay.querySelector('.fw-lightbox__caption');
		counterEl = overlay.querySelector('.fw-lightbox__counter');
		spinnerEl = overlay.querySelector('.fw-lightbox__spinner');
		stageEl = overlay.querySelector('.fw-lightbox__stage');

		overlay.querySelector('.fw-lightbox__close').addEventListener('click', close);
		overlay.querySelector('.fw-lightbox__prev').addEventListener('click', function (e) { e.stopPropagation(); step(-1); });
		overlay.querySelector('.fw-lightbox__next').addEventListener('click', function (e) { e.stopPropagation(); step(1); });

		overlay.addEventListener('click', function (e) {
			if (e.target === overlay || e.target === stageEl) { close(); }
		});
		imgEl.addEventListener('click', function (e) { e.stopPropagation(); if (slides.length > 1) { step(1); } });

		imgEl.addEventListener('load', function () {
			overlay.classList.remove('is-loading');
			imgEl.classList.add('is-ready');
		});
		imgEl.addEventListener('error', function () { overlay.classList.remove('is-loading'); });

		bindSwipe();
	}

	function bindSwipe() {
		var startX = 0, startY = 0, tracking = false;
		overlay.addEventListener('touchstart', function (e) {
			if (e.touches.length !== 1) { return; }
			tracking = true; startX = e.touches[0].clientX; startY = e.touches[0].clientY;
		}, { passive: true });
		overlay.addEventListener('touchend', function (e) {
			if (!tracking) { return; }
			tracking = false;
			var t = e.changedTouches[0];
			var dx = t.clientX - startX, dy = t.clientY - startY;
			if (Math.abs(dx) > 45 && Math.abs(dx) > Math.abs(dy)) { step(dx < 0 ? 1 : -1); }
			else if (dy > 90 && Math.abs(dy) > Math.abs(dx)) { close(); }
		}, { passive: true });
	}

	function preload(index) {
		if (!slides[index]) { return; }
		var src = slides[index].src;
		if (preloaded[src]) { return; }
		var im = new Image(); im.src = src; preloaded[src] = true;
	}

	function show(index, dir) {
		if (!slides.length) { return; }
		current = (index + slides.length) % slides.length;
		var slide = slides[current];

		imgEl.classList.remove('is-ready');
		overlay.classList.add('is-loading');

		if (!REDUCED && dir) {
			imgEl.style.transition = 'none';
			imgEl.style.transform = 'translateX(' + (dir > 0 ? 24 : -24) + 'px)';
			void imgEl.offsetWidth;
			imgEl.style.transition = '';
			imgEl.style.transform = '';
		}

		imgEl.src = slide.src;
		imgEl.alt = slide.caption || '';

		if (imgEl.complete && imgEl.naturalWidth) {
			overlay.classList.remove('is-loading');
			imgEl.classList.add('is-ready');
		}

		if (slide.caption) { captionEl.textContent = slide.caption; captionEl.hidden = false; }
		else { captionEl.textContent = ''; captionEl.hidden = true; }

		counterEl.textContent = (current + 1) + ' / ' + slides.length;
		overlay.classList.toggle('is-single', slides.length < 2);

		preload(current + 1);
		preload(current - 1);
	}

	function step(dir) { show(current + dir, dir); }

	function lockScroll() {
		var sbw = window.innerWidth - document.documentElement.clientWidth;
		document.body.style.overflow = 'hidden';
		if (sbw > 0) { document.body.style.paddingRight = sbw + 'px'; }
	}
	function unlockScroll() {
		document.body.style.overflow = '';
		document.body.style.paddingRight = '';
	}

	function open(list, index) {
		build();
		slides = list;
		lastFocus = document.activeElement;
		show(index || 0, 0);
		lockScroll();
		void overlay.offsetWidth;
		overlay.classList.add('is-open');
		document.addEventListener('keydown', onKey, true);
		var closeBtn = overlay.querySelector('.fw-lightbox__close');
		if (closeBtn) { closeBtn.focus(); }
	}

	function close() {
		if (!overlay || !overlay.classList.contains('is-open')) { return; }
		overlay.classList.remove('is-open');
		unlockScroll();
		document.removeEventListener('keydown', onKey, true);
		window.setTimeout(function () {
			if (!overlay.classList.contains('is-open')) {
				imgEl.removeAttribute('src');
				imgEl.classList.remove('is-ready');
			}
		}, REDUCED ? 0 : 280);
		if (lastFocus && typeof lastFocus.focus === 'function') { lastFocus.focus(); }
	}

	function onKey(e) {
		if (!overlay || !overlay.classList.contains('is-open')) { return; }
		switch (e.key) {
			case 'Escape': e.preventDefault(); close(); break;
			case 'ArrowLeft': e.preventDefault(); step(-1); break;
			case 'ArrowRight': e.preventDefault(); step(1); break;
			case 'Home': e.preventDefault(); show(0, -1); break;
			case 'End': e.preventDefault(); show(slides.length - 1, 1); break;
			case 'Tab': e.preventDefault(); break;
		}
	}

	function collectGroup(group, clickedEl) {
		var nodes = document.querySelectorAll('[data-fw-lightbox="' + cssEscape(group) + '"]');
		var list = [], startIndex = 0;
		Array.prototype.forEach.call(nodes, function (node) {
			var href = node.getAttribute('data-fw-full') || node.getAttribute('href') || node.getAttribute('data-full');
			if (!href) { return; }
			if (node === clickedEl) { startIndex = list.length; }
			list.push({ src: href, caption: node.getAttribute('data-fw-caption') || '' });
		});
		return { list: list, index: startIndex };
	}

	function cssEscape(value) {
		if (window.CSS && window.CSS.escape) { return window.CSS.escape(value); }
		return String(value).replace(/["\\\]]/g, '\\$&');
	}

	function openFrom(trigger) {
		var group = trigger.getAttribute('data-fw-lightbox');
		if (group === null) { return; }
		var data = collectGroup(group, trigger);
		if (data.list.length) { open(data.list, data.index); }
	}

	/* ------------------------------- the open hook -------------------------- */
	// Bound at WINDOW capture (earliest possible) + DOCUMENT capture as backup,
	// with stopImmediatePropagation so we beat theme/plugin handlers too.
	function onTriggerClick(e) {
		var trigger = e.target && e.target.closest ? e.target.closest('[data-fw-lightbox]') : null;
		if (!trigger) { return; }
		if (e.__fwGalleryHandled) { return; }
		if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey || (typeof e.button === 'number' && e.button !== 0)) { return; }

		e.__fwGalleryHandled = true;
		neutralize(trigger); // belt-and-suspenders: ensure no href remains
		if (e.cancelable) { e.preventDefault(); }
		e.stopPropagation();
		if (e.stopImmediatePropagation) { e.stopImmediatePropagation(); }
		openFrom(trigger);
	}

	function onTriggerKey(e) {
		if (e.key !== 'Enter' && e.key !== ' ' && e.key !== 'Spacebar') { return; }
		var trigger = e.target && e.target.closest ? e.target.closest('[data-fw-lightbox]') : null;
		if (!trigger) { return; }
		e.preventDefault();
		openFrom(trigger);
	}

	window.addEventListener('click', onTriggerClick, true);
	document.addEventListener('click', onTriggerClick, true);
	document.addEventListener('keydown', onTriggerKey, true);
})();
