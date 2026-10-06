/**
 * Logo Grid — mounts the Splide logo carousel (config from data-splide).
 */
(function () {
	'use strict';
	function init(scope) {
		if (typeof window.Splide === 'undefined') { return; }
		var nodes = (scope || document).querySelectorAll('.fw-lg__carousel:not(.is-initialized)');
		Array.prototype.forEach.call(nodes, function (el) {
			try { new window.Splide(el).mount(); el.classList.add('is-initialized'); } catch (e) {}
		});
	}
	if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', function () { init(); }); } else { init(); }

	/* Re-init hook for surfaces that insert this element's markup after page load (the
	   block editor's preview, the Elementor editor's re-renders): they call every
	   window.fwShortcodeInit entry with the document or element holding the new markup.
	   init() skips what it has already initialised, so repeated calls are safe. */
	window.fwShortcodeInit = window.fwShortcodeInit || [];
	window.fwShortcodeInit.push(init);
})();
