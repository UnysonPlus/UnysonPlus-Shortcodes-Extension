/**
 * Audio Player — a custom HTML5 audio controller (single track or playlist).
 * One <audio> per player; the track list (.fw-ap__track) is the data source.
 */
(function () {
	'use strict';

	function fmt(s) {
		if (!isFinite(s) || s < 0) { return '0:00'; }
		var m = Math.floor(s / 60), sec = Math.floor(s % 60);
		return m + ':' + (sec < 10 ? '0' : '') + sec;
	}

	function initOne(root) {
		if (root.__apReady) { return; }
		root.__apReady = true;

		var audio = root.querySelector('.fw-ap__audio');
		if (!audio) { return; }
		var tracks = Array.prototype.map.call(root.querySelectorAll('.fw-ap__track'), function (li) {
			return { el: li, src: li.getAttribute('data-src'), title: li.getAttribute('data-title') || '', artist: li.getAttribute('data-artist') || '', cover: li.getAttribute('data-cover') || '' };
		});
		if (!tracks.length) { return; }

		var $ = function (s) { return root.querySelector(s); };
		var playBtn = $('.fw-ap__play'), prevBtn = $('.fw-ap__prev'), nextBtn = $('.fw-ap__next');
		var played = $('.fw-ap__played'), buffered = $('.fw-ap__buffered'), handle = $('.fw-ap__handle'), bar = $('.fw-ap__progress');
		var curT = $('.fw-ap__cur'), durT = $('.fw-ap__dur');
		var vol = $('.fw-ap__vol'), volBtn = $('.fw-ap__volbtn');
		var titleEl = $('.fw-ap__title'), artistEl = $('.fw-ap__artist'), coverWrap = $('.fw-ap__cover'), coverImg = $('.fw-ap__cover-img'), dl = $('.fw-ap__dl');
		var loop = root.getAttribute('data-loop') === '1';
		var index = 0;

		function load(i, autoplay) {
			index = (i + tracks.length) % tracks.length;
			var t = tracks[index];
			audio.src = t.src;
			if (titleEl) { titleEl.textContent = t.title; }
			if (artistEl) { artistEl.textContent = t.artist; }
			if (coverImg) { coverImg.src = t.cover || ''; }
			if (coverWrap) { coverWrap.setAttribute('data-empty', t.cover ? '0' : '1'); }
			if (dl) { dl.setAttribute('href', t.src); }
			tracks.forEach(function (tr, n) { tr.el.classList.toggle('is-active', n === index); });
			if (played) { played.style.width = '0%'; }
			if (handle) { handle.style.left = '0%'; }
			if (autoplay) { audio.play().catch(function () {}); }
		}

		function setPlaying(on) {
			root.classList.toggle('is-playing', on);
			if (playBtn) { playBtn.setAttribute('aria-label', on ? 'Pause' : 'Play'); }
		}

		// Controls
		if (playBtn) { playBtn.addEventListener('click', function () { if (audio.paused) { audio.play().catch(function(){}); } else { audio.pause(); } }); }
		if (prevBtn) { prevBtn.addEventListener('click', function () { load(index - 1, true); }); }
		if (nextBtn) { nextBtn.addEventListener('click', function () { load(index + 1, true); }); }
		tracks.forEach(function (t, i) { t.el.addEventListener('click', function () { load(i, true); }); });

		audio.addEventListener('play', function () { setPlaying(true); });
		audio.addEventListener('pause', function () { setPlaying(false); });
		audio.addEventListener('loadedmetadata', function () { if (durT) { durT.textContent = fmt(audio.duration); } });
		audio.addEventListener('timeupdate', function () {
			var pct = audio.duration ? (audio.currentTime / audio.duration) * 100 : 0;
			if (played) { played.style.width = pct + '%'; }
			if (handle) { handle.style.left = pct + '%'; }
			if (curT) { curT.textContent = fmt(audio.currentTime); }
			if (bar) { bar.setAttribute('aria-valuenow', Math.round(pct)); }
		});
		audio.addEventListener('progress', function () {
			try {
				if (audio.buffered.length && audio.duration && buffered) {
					buffered.style.width = (audio.buffered.end(audio.buffered.length - 1) / audio.duration) * 100 + '%';
				}
			} catch (e) {}
		});
		audio.addEventListener('ended', function () {
			if (tracks.length > 1) {
				if (index < tracks.length - 1) { load(index + 1, true); }
				else if (loop) { load(0, true); }
			}
		});

		// Seek
		function seekTo(clientX) {
			var r = bar.getBoundingClientRect();
			var pct = Math.max(0, Math.min(1, (clientX - r.left) / r.width));
			if (audio.duration) { audio.currentTime = pct * audio.duration; }
		}
		if (bar) {
			var dragging = false;
			bar.addEventListener('pointerdown', function (e) { dragging = true; seekTo(e.clientX); try { bar.setPointerCapture(e.pointerId); } catch (x) {} });
			bar.addEventListener('pointermove', function (e) { if (dragging) { seekTo(e.clientX); } });
			bar.addEventListener('pointerup', function () { dragging = false; });
			bar.addEventListener('keydown', function (e) {
				if (!audio.duration) { return; }
				if (e.key === 'ArrowRight') { audio.currentTime = Math.min(audio.duration, audio.currentTime + 5); e.preventDefault(); }
				else if (e.key === 'ArrowLeft') { audio.currentTime = Math.max(0, audio.currentTime - 5); e.preventDefault(); }
			});
		}

		// Volume
		if (vol) { vol.addEventListener('input', function () { audio.volume = parseFloat(vol.value); audio.muted = audio.volume === 0; root.classList.toggle('is-muted', audio.muted); }); }
		if (volBtn) { volBtn.addEventListener('click', function () { audio.muted = !audio.muted; root.classList.toggle('is-muted', audio.muted); if (vol) { vol.value = audio.muted ? 0 : (audio.volume || 1); } }); }

		load(0, false);
		if (root.getAttribute('data-autoplay') === '1') { audio.play().catch(function () {}); }
	}

	function init() {
		Array.prototype.forEach.call(document.querySelectorAll('[data-ap]'), initOne);
	}
	if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', init); } else { init(); }
})();
