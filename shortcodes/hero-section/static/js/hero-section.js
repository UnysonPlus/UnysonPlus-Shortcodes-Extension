(function () {
	'use strict';

	var sections = [];
	var ticking = false;

	function collect() {
		sections = Array.prototype.slice.call(
			document.querySelectorAll('.fw-hero-section[data-parallax-strength]')
		).map(function (el) {
			var strength = parseFloat(el.getAttribute('data-parallax-strength'));
			if (!isFinite(strength) || strength <= 0) {
				return null;
			}
			var bg = el.querySelector('.fw-hero-section__bg');
			if (!bg) {
				return null;
			}
			return { el: el, bg: bg, strength: strength };
		}).filter(Boolean);
	}

	function update() {
		ticking = false;
		var viewportH = window.innerHeight;
		for (var i = 0; i < sections.length; i++) {
			var s = sections[i];
			var rect = s.el.getBoundingClientRect();
			// Skip if the section is far off-screen — saves transform work
			// when the page has many heroes stacked.
			if (rect.bottom < -200 || rect.top > viewportH + 200) {
				continue;
			}
			// Translate the background opposite to scroll, scaled by strength.
			// Anchor: midpoint of the section maps to 0 translate.
			var sectionCenter = rect.top + rect.height / 2;
			var viewportCenter = viewportH / 2;
			var offset = (viewportCenter - sectionCenter) * s.strength;
			s.bg.style.transform = 'translate3d(0,' + offset.toFixed(1) + 'px,0)';
		}
	}

	function onScroll() {
		if (!ticking) {
			ticking = true;
			window.requestAnimationFrame(update);
		}
	}

	function init() {
		collect();
		if (!sections.length) {
			return;
		}
		update();
		window.addEventListener('scroll', onScroll, { passive: true });
		window.addEventListener('resize', function () {
			collect();
			onScroll();
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
