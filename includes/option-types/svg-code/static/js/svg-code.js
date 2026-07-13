/**
 * `svg-code` option type — backend behaviour.
 *
 * "Upload SVG file" reads the chosen .svg with FileReader (client-side) into the textarea, so the
 * file never hits the media library (no SVG-MIME block, no Safe-SVG plugin). Drag-and-drop of an
 * .svg onto the field does the same. A live preview mirrors whatever is in the textarea.
 * Re-inits for options rendered dynamically (modals / addable-popup) via fw:options:init.
 */
(function ($) {
	'use strict';

	function readFile(file, $ta, updatePreview) {
		if (!file) { return; }
		var reader = new FileReader();
		reader.onload = function (e) {
			$ta.val(String((e.target && e.target.result) || '')).trigger('change');
			updatePreview();
		};
		reader.readAsText(file);
	}

	function initOne() {
		var $w = $(this);
		if ($w.data('svgCodeInit')) { return; }
		$w.data('svgCodeInit', true);

		var $ta = $w.find('.fw-svg-code-input'),
			$file = $w.find('.fw-svg-code-file'),
			$btn = $w.find('.fw-svg-code-upload'),
			$prev = $w.find('.fw-svg-code-preview');

		function updatePreview() {
			var v = $ta.val() || '';
			var ok = v.toLowerCase().indexOf('<svg') !== -1;
			$prev.html(ok ? v : '').toggleClass('has-svg', ok);
		}

		$btn.on('click', function (e) { e.preventDefault(); $file.trigger('click'); });

		$file.on('change', function () {
			readFile(this.files && this.files[0], $ta, updatePreview);
			this.value = ''; // allow re-selecting the same file
		});

		$ta.on('input change', updatePreview);

		// Drag & drop an .svg straight onto the field.
		$w.on('dragover', function (e) { e.preventDefault(); $w.addClass('is-drag'); })
			.on('dragleave', function () { $w.removeClass('is-drag'); })
			.on('drop', function (e) {
				e.preventDefault();
				$w.removeClass('is-drag');
				var dt = e.originalEvent && e.originalEvent.dataTransfer;
				var f = dt && dt.files && dt.files[0];
				if (f && /svg/i.test((f.type || '') + ' ' + (f.name || ''))) {
					readFile(f, $ta, updatePreview);
				}
			});

		updatePreview();
	}

	function init(ctx) {
		$(ctx || document).find('.fw-svg-code').each(initOne);
	}

	$(function () { init(document); });

	if (window.fwEvents) {
		fwEvents.on('fw:options:init', function (data) {
			init(data && data.$elements ? data.$elements : document);
		});
	}
})(jQuery);
