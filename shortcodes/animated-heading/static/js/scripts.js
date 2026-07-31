/**
 * Animated Heading — rotates the words in .fw-ah__word. Non-typewriter designs
 * swap the text + retrigger a CSS enter animation (.run); typewriter types and
 * deletes. Supports: loop forever / once (stop on last word), pause on hover,
 * randomized order. Honors prefers-reduced-motion (plain word swap).
 */
(function () {
	'use strict';

	var REDUCED = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	var SPEED = {
		slow:   { hold: 2600, type: 110, del: 55 },
		normal: { hold: 1900, type: 75,  del: 40 },
		fast:   { hold: 1200, type: 45,  del: 25 }
	};

	function shuffle(a) {
		for (var i = a.length - 1; i > 0; i--) {
			var j = Math.floor(Math.random() * (i + 1));
			var t = a[i]; a[i] = a[j]; a[j] = t;
		}
		return a;
	}

	function initOne(el) {
		if (el.__ahReady) { return; }
		el.__ahReady = true;

		var words;
		try { words = JSON.parse(el.getAttribute('data-ah-words') || '[]'); } catch (e) { words = []; }
		if (!Array.isArray(words) || !words.length) { return; }

		var word = el.querySelector('.fw-ah__word');
		if (!word) { return; }

		if (el.getAttribute('data-ah-random') === '1' && words.length > 1) {
			words = shuffle(words.slice());
			word.textContent = words[0]; // reflect the shuffled first word
		}

		var anim  = (el.className.match(/fw-ah--anim-([a-z0-9]+)/) || [])[1] || 'fade';
		var sp    = (el.className.match(/fw-ah--speed-([a-z]+)/) || [])[1] || 'normal';
		var P     = SPEED[sp] || SPEED.normal;
		var once  = el.getAttribute('data-ah-loop') === 'once';
		var idx   = 0;

		// --- pausable scheduler: re-arms the pending step on resume ---
		var timer = null, paused = false, resume = null;
		function sched(fn, delay) {
			resume = function () { sched(fn, delay); };
			if (paused) { return; }
			timer = window.setTimeout(fn, delay);
		}
		if (el.getAttribute('data-ah-pause') === '1') {
			el.addEventListener('mouseenter', function () {
				paused = true;
				if (timer) { window.clearTimeout(timer); timer = null; }
			});
			el.addEventListener('mouseleave', function () {
				if (!paused) { return; }
				paused = false;
				if (resume) { resume(); }
			});
		}

		if (anim === 'typewriter') {
			typewriter();
			return;
		}
		if (words.length < 2) { return; } // nothing to rotate

		function swap() {
			var last = idx >= words.length - 1;
			if (once && last) { return; } // stop on the final word
			idx = (idx + 1) % words.length;
			word.textContent = words[idx];
			if (!REDUCED) {
				word.classList.remove('run');
				void word.offsetWidth;
				word.classList.add('run');
			}
			if (once && idx >= words.length - 1) { return; } // just placed the last word
			sched(swap, P.hold);
		}
		if (!REDUCED) { word.classList.add('run'); }
		sched(swap, P.hold);

		function typewriter() {
			if (REDUCED || words.length < 2) {
				word.textContent = words[0];
				if (words.length < 2 || once) { return; }
				window.setInterval(function () { idx = (idx + 1) % words.length; word.textContent = words[idx]; }, P.hold);
				return;
			}
			var pos = 0, deleting = false;
			word.textContent = '';
			function tick() {
				var full = words[idx];
				if (!deleting) {
					pos++;
					word.textContent = full.slice(0, pos);
					if (pos >= full.length) {
						// Fully typed. Stop here if this is the last word in a play-once run.
						if (once && idx >= words.length - 1) { return; }
						deleting = true;
						return sched(tick, P.hold);
					}
					return sched(tick, P.type);
				}
				pos--;
				word.textContent = full.slice(0, pos);
				if (pos <= 0) { deleting = false; idx = (idx + 1) % words.length; return sched(tick, P.type * 3); }
				return sched(tick, P.del);
			}
			sched(tick, P.hold / 2);
		}
	}

	function init() {
		Array.prototype.forEach.call(document.querySelectorAll('[data-ah-words]'), initOne);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
