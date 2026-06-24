/**
 * Animated Heading — rotates the words in .fw-ah__word. Non-typewriter designs
 * swap the text + retrigger a CSS enter animation (.run); typewriter types and
 * deletes. Honors prefers-reduced-motion (plain word swap).
 */
(function () {
	'use strict';

	var REDUCED = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	var SPEED = {
		slow:   { hold: 2600, type: 110, del: 55 },
		normal: { hold: 1900, type: 75,  del: 40 },
		fast:   { hold: 1200, type: 45,  del: 25 }
	};

	function initOne(el) {
		if (el.__ahReady) { return; }
		el.__ahReady = true;

		var words;
		try { words = JSON.parse(el.getAttribute('data-ah-words') || '[]'); } catch (e) { words = []; }
		if (!Array.isArray(words) || !words.length) { return; }

		var word = el.querySelector('.fw-ah__word');
		if (!word) { return; }

		var anim = (el.className.match(/fw-ah--anim-([a-z]+)/) || [])[1] || 'fade';
		var sp = (el.className.match(/fw-ah--speed-([a-z]+)/) || [])[1] || 'normal';
		var P = SPEED[sp] || SPEED.normal;
		var idx = 0;

		if (anim === 'typewriter') {
			typewriter();
			return;
		}
		if (words.length < 2) { return; } // nothing to rotate

		function swap() {
			idx = (idx + 1) % words.length;
			word.textContent = words[idx];
			if (!REDUCED) {
				word.classList.remove('run');
				void word.offsetWidth;
				word.classList.add('run');
			}
			window.setTimeout(swap, P.hold);
		}
		if (!REDUCED) { word.classList.add('run'); }
		window.setTimeout(swap, P.hold);

		function typewriter() {
			if (REDUCED || words.length < 2) {
				word.textContent = words[0];
				if (words.length < 2) { return; }
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
					if (pos >= full.length) { deleting = true; return window.setTimeout(tick, P.hold); }
					return window.setTimeout(tick, P.type);
				}
				pos--;
				word.textContent = full.slice(0, pos);
				if (pos <= 0) { deleting = false; idx = (idx + 1) % words.length; return window.setTimeout(tick, P.type * 3); }
				return window.setTimeout(tick, P.del);
			}
			window.setTimeout(tick, P.hold / 2);
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
