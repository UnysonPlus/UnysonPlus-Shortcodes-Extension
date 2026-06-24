/**
 * Gallery — Showcase design. Clicking a thumbnail swaps the featured (stage)
 * image; clicking the stage (when the click action is "lightbox") opens the
 * lightbox at the active index by triggering the matching hidden source anchor,
 * so lightbox.js handles the rest with no coupling.
 */
(function () {
	'use strict';

	function initOne(root) {
		if (root.__fwShowcaseReady) { return; }
		root.__fwShowcaseReady = true;

		var stage = root.querySelector('.fw-gallery__stage');
		var stageImg = root.querySelector('.fw-gallery__stage-img');
		var stageCap = root.querySelector('.fw-gallery__stage-caption');
		var thumbs = Array.prototype.slice.call(root.querySelectorAll('.fw-gallery__thumb'));
		var sources = root.querySelectorAll('.fw-gallery__lb-sources a');
		var clickAction = root.getAttribute('data-click') || 'lightbox';
		var active = 0;

		if (!stage || !stageImg || !thumbs.length) { return; }

		function activate(index) {
			index = Math.max(0, Math.min(thumbs.length - 1, index));
			var thumb = thumbs[index];
			if (!thumb) { return; }
			active = index;

			stageImg.classList.add('is-swapping');
			var swap = function () {
				stageImg.src = thumb.getAttribute('data-src') || stageImg.src;
				if (thumb.getAttribute('data-srcset')) {
					stageImg.setAttribute('srcset', thumb.getAttribute('data-srcset'));
				} else {
					stageImg.removeAttribute('srcset');
				}
				var cap = thumb.getAttribute('data-caption') || '';
				stageImg.alt = cap;
				if (stageCap) {
					stageCap.textContent = cap;
					stageCap.style.display = cap ? '' : 'none';
				}
				// Keep a file/attachment stage link pointing at the active image.
				if (stage.tagName === 'A' && clickAction === 'file') {
					stage.setAttribute('href', thumb.getAttribute('data-full') || stage.getAttribute('href'));
				}
				stageImg.classList.remove('is-swapping');
			};
			// Swap after a tick so the fade-out is visible.
			if (stageImg.complete) {
				window.setTimeout(swap, 120);
			} else {
				swap();
			}

			thumbs.forEach(function (t, i) {
				t.classList.toggle('is-active', i === index);
				t.setAttribute('aria-selected', i === index ? 'true' : 'false');
			});
		}

		thumbs.forEach(function (thumb, i) {
			thumb.addEventListener('click', function () { activate(i); });
			thumb.addEventListener('keydown', function (e) {
				if (e.key === 'ArrowRight' || e.key === 'ArrowDown') {
					e.preventDefault();
					var n = (i + 1) % thumbs.length;
					thumbs[n].focus();
					activate(n);
				} else if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') {
					e.preventDefault();
					var p = (i - 1 + thumbs.length) % thumbs.length;
					thumbs[p].focus();
					activate(p);
				}
			});
		});

		// Stage click → open lightbox at the active index (only when the stage is
		// the lightbox trigger, i.e. a <button> + hidden sources are present).
		if (clickAction === 'lightbox' && stage.tagName === 'BUTTON' && sources.length) {
			stage.addEventListener('click', function () {
				var src = sources[active];
				if (src) { src.click(); }
			});
		}
	}

	function init() {
		var roots = document.querySelectorAll('[data-gallery-showcase]');
		Array.prototype.forEach.call(roots, initOne);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
