/**
 * Social Share — opens network share links in a small popup window, and handles
 * the Copy-link button (Clipboard API with a fallback + a "Copied!" flash).
 */
(function () {
	'use strict';

	function copy(text) {
		if (navigator.clipboard && navigator.clipboard.writeText) {
			return navigator.clipboard.writeText(text);
		}
		return new Promise(function (resolve, reject) {
			try {
				var ta = document.createElement('textarea');
				ta.value = text;
				ta.setAttribute('readonly', '');
				ta.style.position = 'absolute';
				ta.style.left = '-9999px';
				document.body.appendChild(ta);
				ta.select();
				document.execCommand('copy');
				document.body.removeChild(ta);
				resolve();
			} catch (e) { reject(e); }
		});
	}

	function flash(btn) {
		btn.classList.add('is-copied');
		window.setTimeout(function () { btn.classList.remove('is-copied'); }, 1600);
	}

	document.addEventListener('click', function (e) {
		var btn = e.target.closest ? e.target.closest('.fw-ss__btn') : null;
		if (!btn) { return; }

		// Copy link.
		if (btn.getAttribute('data-ss-copy') === '1') {
			e.preventDefault();
			var url = btn.getAttribute('data-ss-url') || window.location.href;
			copy(url).then(function () { flash(btn); }).catch(function () {});
			return;
		}

		// Popup share window (skip modified clicks so "open in new tab" still works).
		if (btn.getAttribute('data-ss-window') === '1') {
			if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey || e.button !== 0) { return; }
			var href = btn.getAttribute('href');
			if (!href || href === '#') { return; }
			e.preventDefault();
			var w = 600, h = 540;
			var y = window.top.outerHeight / 2 + window.top.screenY - (h / 2);
			var x = window.top.outerWidth / 2 + window.top.screenX - (w / 2);
			var win = window.open(href, 'fw-share', 'scrollbars=yes,width=' + w + ',height=' + h + ',top=' + y + ',left=' + x);
			if (!win) { window.open(href, '_blank', 'noopener'); }
		}
	}, false);
})();
