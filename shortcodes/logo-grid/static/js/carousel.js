/**
 * Logo Grid — mounts the Splide logo carousel (config from data-splide).
 */
(function () {
	'use strict';
	function init() {
		if (typeof window.Splide === 'undefined') { return; }
		var nodes = document.querySelectorAll('.fw-lg__carousel:not(.is-initialized)');
		Array.prototype.forEach.call(nodes, function (el) {
			try { new window.Splide(el).mount(); el.classList.add('is-initialized'); } catch (e) {}
		});
	}
	if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', init); } else { init(); }
})();
