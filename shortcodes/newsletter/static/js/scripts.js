/**
 * Newsletter — AJAX submit to admin-ajax (action fw_newsletter_subscribe).
 * Shows the form's configured success / error message; disables the button while
 * submitting. Honeypot + nonce are sent with the form.
 */
(function () {
	'use strict';

	function submit(form) {
		var data = new FormData(form);
		data.append('action', 'fw_newsletter_subscribe');
		data.append('nonce', form.getAttribute('data-nonce') || '');

		var btn = form.querySelector('.fw-nl__btn');
		var msg = form.querySelector('.fw-nl__msg');
		var email = form.querySelector('.fw-nl__input--email');
		if (email && !email.value) { email.focus(); return; }

		if (btn) { btn.disabled = true; btn.classList.add('is-loading'); }
		if (msg) { msg.className = 'fw-nl__msg'; msg.textContent = ''; }

		fetch(form.getAttribute('data-ajax'), { method: 'POST', credentials: 'same-origin', body: data })
			.then(function (r) { return r.json(); })
			.then(function (res) {
				if (res && res.success) {
					form.classList.add('is-done');
					if (msg) { msg.className = 'fw-nl__msg is-success'; msg.textContent = form.getAttribute('data-success') || (res.data && res.data.message) || 'Thanks!'; }
					form.reset();
				} else {
					var m = (res && res.data && res.data.message) ? res.data.message : (form.getAttribute('data-error') || 'Error');
					if (msg) { msg.className = 'fw-nl__msg is-error'; msg.textContent = m; }
				}
			})
			.catch(function () {
				if (msg) { msg.className = 'fw-nl__msg is-error'; msg.textContent = form.getAttribute('data-error') || 'Error'; }
			})
			.then(function () {
				if (btn) { btn.disabled = false; btn.classList.remove('is-loading'); }
			});
	}

	document.addEventListener('submit', function (e) {
		var form = e.target;
		if (form && form.classList && form.classList.contains('fw-nl__form')) {
			e.preventDefault();
			submit(form);
		}
	}, false);
})();
