/**
 * Before / After comparison slider engine (zero dependencies).
 *
 * For each [data-bac]: drives drag / hover / toggle interaction in both
 * orientations, keyboard accessibility (role="slider"), and an optional intro
 * sweep when the slider scrolls into view. The reveal is a single CSS custom
 * property (--bac-pos, a percentage) that clips the top "before" layer.
 */
(function () {
	'use strict';

	if (typeof document === 'undefined') { return; }

	var REDUCED = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	function clamp(v, a, b) { return Math.max(a, Math.min(b, v)); }

	function initOne(el) {
		if (el.__bacReady) { return; }
		el.__bacReady = true;

		var media = el.querySelector('.fw-bac__media') || el;
		var vertical = el.getAttribute('data-orientation') === 'vertical';
		var mode = el.getAttribute('data-interaction') || 'drag';
		var start = parseFloat(el.getAttribute('data-start'));
		if (isNaN(start)) { start = 50; }

		// Background mode: fill the Section (shared runtime) and drive the reveal from
		// the whole Section. The reveal geometry still uses the media rect (which now
		// covers the Section), so posFromPoint is unchanged.
		var asBg = el.getAttribute('data-bg') === '1';
		var surface = (asBg && window.scBgFill) ? (window.scBgFill(el) || media) : media;

		var interacted = false;

		function setPos(p) {
			p = clamp(p, 0, 100);
			el.style.setProperty('--bac-pos', p + '%');
			el.setAttribute('aria-valuenow', Math.round(p));
			return p;
		}
		function posFromPoint(clientX, clientY) {
			var r = media.getBoundingClientRect();
			if (vertical) {
				return r.height ? (clientY - r.top) / r.height * 100 : 50;
			}
			return r.width ? (clientX - r.left) / r.width * 100 : 50;
		}
		function moveFromEvent(e) {
			var x = e.clientX, y = e.clientY;
			if ((x == null || x === undefined) && e.touches && e.touches[0]) {
				x = e.touches[0].clientX; y = e.touches[0].clientY;
			}
			if (x == null) { return; }
			setPos(posFromPoint(x, y));
		}
		function markInteracted() { interacted = true; }

		setPos(start);

		/* --- Interaction modes --- */
		if (mode === 'toggle') {
			var toggle = function () { el.classList.toggle('is-after'); markInteracted(); };
			surface.addEventListener('click', toggle);
			el.addEventListener('keydown', function (e) {
				if (e.key === 'Enter' || e.key === ' ' || e.key === 'Spacebar') { e.preventDefault(); toggle(); }
			});
		} else if (mode === 'hover') {
			surface.addEventListener('pointermove', function (e) { markInteracted(); moveFromEvent(e); });
			surface.addEventListener('pointerdown', function (e) { markInteracted(); moveFromEvent(e); });
			// Touch: allow dragging a finger across to reveal.
			surface.addEventListener('touchmove', function (e) { markInteracted(); moveFromEvent(e); }, { passive: true });
		} else { // drag
			var dragging = false;
			surface.addEventListener('pointerdown', function (e) {
				dragging = true;
				markInteracted();
				el.classList.add('is-dragging');
				if (surface.setPointerCapture && e.pointerId != null) {
					try { surface.setPointerCapture(e.pointerId); } catch (err) {}
				}
				moveFromEvent(e);
				e.preventDefault();
			});
			window.addEventListener('pointermove', function (e) { if (dragging) { moveFromEvent(e); } });
			window.addEventListener('pointerup', function () {
				if (!dragging) { return; }
				dragging = false;
				el.classList.remove('is-dragging');
			});
			// Touch fallback for browsers without Pointer Events.
			if (!('PointerEvent' in window)) {
				surface.addEventListener('touchstart', function (e) { dragging = true; markInteracted(); el.classList.add('is-dragging'); moveFromEvent(e); }, { passive: true });
				window.addEventListener('touchmove', function (e) { if (dragging) { moveFromEvent(e); } }, { passive: true });
				window.addEventListener('touchend', function () { dragging = false; el.classList.remove('is-dragging'); });
			}
		}

		/* --- Keyboard (drag / hover) --- */
		if (mode !== 'toggle') {
			el.addEventListener('keydown', function (e) {
				var cur = parseFloat(el.getAttribute('aria-valuenow'));
				if (isNaN(cur)) { cur = start; }
				var step = e.shiftKey ? 10 : 2;
				var handled = true;
				switch (e.key) {
					case 'ArrowLeft': case 'ArrowDown': markInteracted(); setPos(cur - step); break;
					case 'ArrowRight': case 'ArrowUp': markInteracted(); setPos(cur + step); break;
					case 'Home': markInteracted(); setPos(0); break;
					case 'End': markInteracted(); setPos(100); break;
					default: handled = false;
				}
				if (handled) { e.preventDefault(); }
			});
		}

		/* --- Intro sweep --- */
		if (el.getAttribute('data-auto') === '1' && mode !== 'toggle' && !REDUCED) {
			var played = false;
			var play = function () {
				if (played || interacted) { return; }
				played = true;
				var steps = [
					{ to: clamp(start + 38, 8, 92), dur: 520 },
					{ to: clamp(start - 38, 8, 92), dur: 700 },
					{ to: start, dur: 620 }
				];
				var i = 0;
				function run() {
					if (interacted || i >= steps.length) { return; }
					var from = parseFloat(el.getAttribute('aria-valuenow')) || start;
					var s = steps[i++];
					var t0 = null;
					function frame(ts) {
						if (interacted) { return; }
						if (t0 === null) { t0 = ts; }
						var k = clamp((ts - t0) / s.dur, 0, 1);
						var e2 = 1 - Math.pow(1 - k, 3); // easeOutCubic
						setPos(from + (s.to - from) * e2);
						if (k < 1) { requestAnimationFrame(frame); }
						else { run(); }
					}
					requestAnimationFrame(frame);
				}
				run();
			};

			if ('IntersectionObserver' in window) {
				var io = new IntersectionObserver(function (entries) {
					entries.forEach(function (en) {
						if (en.isIntersecting) { play(); io.disconnect(); }
					});
				}, { threshold: 0.4 });
				io.observe(el);
			} else {
				play();
			}
		}
	}

	/* =====================================================================
	 * SPOTLIGHT type — a soft circular reveal that follows the cursor.
	 * The top ".fw-bac__before" layer carries an inverse radial mask; we just
	 * feed it --sr-x / --sr-y (px, relative to the media). Optional smooth
	 * follow (lerp) and an optional background mode that fills the Section.
	 * =================================================================== */

	function initSpot(el) {
		if (el.__bacReady) { return; }
		el.__bacReady = true;

		var media  = el.querySelector('.fw-bac__media') || el;
		var asBg   = el.getAttribute('data-bg') === '1';
		var smooth = el.getAttribute('data-smooth') === '1' && !REDUCED;
		var idle   = el.getAttribute('data-idle') === '1';

		// Background mode fills the Section via the shared runtime (window.scBgFill),
		// which returns the host so we can drive the spotlight from the whole Section.
		var host = (asBg && window.scBgFill) ? (window.scBgFill(el) || media) : media;

		var tx = null, ty = null, cx = null, cy = null, raf = null, armed = false;

		function rect() { return media.getBoundingClientRect(); }
		function apply(x, y) {
			el.style.setProperty('--sr-x', x + 'px');
			el.style.setProperty('--sr-y', y + 'px');
		}
		function centre() { var r = rect(); return [ r.width / 2, r.height / 2 ]; }

		function frame() {
			raf = null;
			if (tx == null) { return; }
			cx += (tx - cx) * 0.18;
			cy += (ty - cy) * 0.18;
			apply(cx, cy);
			if (Math.abs(tx - cx) > 0.4 || Math.abs(ty - cy) > 0.4) {
				raf = requestAnimationFrame(frame);
			} else {
				apply(tx, ty);
			}
		}
		function moveTo(x, y) {
			tx = x; ty = y;
			if (cx == null) { cx = x; cy = y; }
			if (smooth) { if (!raf) { raf = requestAnimationFrame(frame); } }
			else { cx = x; cy = y; apply(x, y); }
		}
		function fromEvent(e) {
			var px = e.clientX, py = e.clientY;
			if ((px == null) && e.touches && e.touches[0]) { px = e.touches[0].clientX; py = e.touches[0].clientY; }
			if (px == null) { return; }
			var r = rect();
			moveTo(px - r.left, py - r.top);
		}
		function armOn() { if (!armed) { armed = true; el.classList.remove('is-away'); } }
		function armOff() {
			armed = false;
			if (idle) { var c = centre(); moveTo(c[0], c[1]); }
			else { el.classList.add('is-away'); }
		}

		// Initial resting state.
		var c0 = centre();
		cx = c0[0]; cy = c0[1];
		if (idle) { apply(cx, cy); } else { el.classList.add('is-away'); }

		host.addEventListener('pointerenter', armOn);
		host.addEventListener('pointermove', function (e) { armOn(); fromEvent(e); });
		host.addEventListener('pointerleave', armOff);
		host.addEventListener('touchmove', function (e) { armOn(); fromEvent(e); }, { passive: true });
		host.addEventListener('touchend', armOff);
	}

	function init() {
		var nodes = document.querySelectorAll('[data-bac]');
		Array.prototype.forEach.call(nodes, initOne);
		var spots = document.querySelectorAll('[data-bac-spot]');
		Array.prototype.forEach.call(spots, initSpot);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
