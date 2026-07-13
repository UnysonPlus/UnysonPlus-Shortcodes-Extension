/**
 * easing-picker option type — a single SHARED palette opened by every trigger on the page.
 *
 * Each option renders only a light trigger (thumbnail + name). The 41-tile grid is built ONCE here
 * and reused, so N triggers cost N light buttons + one palette (not N×41 tiles). Selecting a tile
 * writes the key into the trigger's hidden `.upw-easing-value` input and fires `change` so Fw /
 * the multi-picker persists it.
 */
(function ($) {
	'use strict';

	var data = window.upwEasingData || { items: [], i18n: {} };
	var $palette = null;
	var $activeTrigger = null;

	// Register with Fw's options system so containers (multi-picker, modals, addable-*) can READ and
	// SET this option's value. Without a registered getValue, Fw can't collect the value when the
	// option sits inside a multi-picker reveal — which silently breaks the whole surrounding picker.
	if (window.fw && fw.options && fw.options.register) {
		fw.options.register('easing-picker', {
			startListeningForChanges: $.noop,
			getValue: function (optionDescriptor) {
				var input = optionDescriptor.el.querySelector('.upw-easing-value');
				return { value: input ? input.value : 'default', optionDescriptor: optionDescriptor };
			}
		});
	}

	function buildPalette() {
		if ($palette) { return $palette; }

		$palette = $('<div class="upw-easing-palette" role="listbox" aria-hidden="true"></div>');
		var $search = $('<input type="text" class="upw-easing-search" />')
			.attr('placeholder', (data.i18n && data.i18n.search) || 'Search easings…');
		$palette.append($('<div class="upw-easing-search-wrap"></div>').append($search));

		// Each group is a horizontal strip (label + its tiles in a row); the strips FLEX-WRAP to fill
		// the full palette width — dense, grouped, and no sparse gaps whatever the palette is sized to.
		var $scroll = $('<div class="upw-easing-scroll"></div>');
		var lastGroup = null, $card = null, $grid = null;
		$.each(data.items, function (i, it) {
			var group = it.group || '';
			if (group !== lastGroup || !$card) {
				lastGroup = group;
				$card = $('<div class="upw-easing-group-card"></div>');
				if (group) { $card.append($('<div class="upw-easing-group"></div>').text(group)); }
				$grid = $('<div class="upw-easing-tiles"></div>');
				$card.append($grid);
				$scroll.append($card);
			}
			var $tile = $('<button type="button" class="upw-easing-tile" role="option"></button>')
				.attr('data-key', it.key)
				.attr('data-search', ((it.label || '') + ' ' + group + ' ' + it.key).toLowerCase());
			$tile.append($('<img class="upw-easing-tile-img" alt="" aria-hidden="true" />').attr('src', it.svg));
			$tile.append($('<span class="upw-easing-tile-label"></span>').text(it.label || it.key));
			$grid.append($tile);
		});
		$palette.append($scroll);
		$('body').append($palette);

		// The palette is appended to <body>, i.e. OUTSIDE the module's multi-picker popover. That
		// popover closes on any mousedown outside itself, so a click in here would collapse it.
		// Stop palette mousedowns from reaching the document so the parent popover stays open.
		$palette.on('mousedown', function (e) { e.stopPropagation(); });

		// Filter as you type.
		$search.on('input', function () {
			var q = $.trim(this.value).toLowerCase();
			$palette.find('.upw-easing-tile').each(function () {
				this.style.display = (!q || this.getAttribute('data-search').indexOf(q) > -1) ? '' : 'none';
			});
			// Hide a whole group strip when all its tiles are filtered out.
			$palette.find('.upw-easing-group-card').each(function () {
				var $c = $(this);
				$c[0].style.display = $c.find('.upw-easing-tile').filter(function () { return this.style.display !== 'none'; }).length ? '' : 'none';
			});
		});

		// Pick a tile.
		$palette.on('mousedown', '.upw-easing-tile', function (e) {
			e.preventDefault();
			if (!$activeTrigger) { return; }
			select($activeTrigger, this.getAttribute('data-key'));
			close();
		});

		return $palette;
	}

	function select($trigger, key) {
		var $wrap = $trigger.closest('.upw-easing');
		var $input = $wrap.find('.upw-easing-value');
		if ($input.val() === key) { return; }
		$input.val(key);

		var it = null;
		for (var i = 0; i < data.items.length; i++) { if (data.items[i].key === key) { it = data.items[i]; break; } }
		$trigger.find('.upw-easing-name').text(it ? it.label : key);
		if (it) { $trigger.find('.upw-easing-thumb').attr('src', it.svg); }
		$wrap.attr('data-value', key);
		// Notify Fw the way containers listen for it (multi-picker reads via fw.options.on.change);
		// fall back to a plain change event if the Fw API isn't present.
		if (window.fw && fw.options && fw.options.trigger && fw.options.trigger.changeForEl) {
			fw.options.trigger.changeForEl($input[0], { value: key });
		} else {
			$input.trigger('change');
			if ($input[0]) { $input[0].dispatchEvent(new Event('change', { bubbles: true })); }
		}
	}

	function open($trigger) {
		var $p = buildPalette();
		$activeTrigger = $trigger;

		// Mark the current selection.
		var cur = $trigger.closest('.upw-easing').find('.upw-easing-value').val();
		$p.find('.upw-easing-tile').removeClass('is-active');
		$p.find('.upw-easing-tile[data-key="' + cur + '"]').addClass('is-active');

		// Position (fixed, viewport-relative) below the trigger, flipping up if no room.
		var r = $trigger[0].getBoundingClientRect();
		// Regular dropdown: match the trigger/container width, drop below it, scroll for more.
		$p.css({ visibility: 'hidden', display: 'flex', width: Math.round(r.width) + 'px' }).attr('aria-hidden', 'false');
		var ph = $p.outerHeight(), pw = $p.outerWidth();
		var top = r.bottom + 4;
		// Flip above the trigger if there isn't room below and there's more room above.
		if (top + ph > window.innerHeight - 8 && r.top - 4 - ph > 8) { top = r.top - 4 - ph; }
		top = Math.max(8, Math.min(top, window.innerHeight - ph - 8));
		var left = Math.max(8, Math.min(r.left, window.innerWidth - pw - 8));
		$p.css({ top: top + 'px', left: left + 'px', visibility: 'visible' });

		var $active = $p.find('.upw-easing-tile.is-active');
		if ($active.length) { $active[0].scrollIntoView({ block: 'nearest' }); }
		$p.find('.upw-easing-search').val('').trigger('input').focus();
	}

	function close() {
		if (!$palette) { return; }
		$palette.attr('aria-hidden', 'true').css('display', 'none');
		$activeTrigger = null;
	}

	$(document)
		.on('mousedown', '.upw-easing-trigger', function (e) {
			e.preventDefault();
			var $t = $(this);
			if ($activeTrigger && $activeTrigger[0] === this) { close(); return; }
			open($t);
		})
		.on('mousedown', function (e) {
			if (!$palette || $palette.attr('aria-hidden') === 'true') { return; }
			if ($(e.target).closest('.upw-easing-palette, .upw-easing-trigger').length) { return; }
			close();
		})
		.on('keydown', function (e) {
			if (e.key === 'Escape') { close(); }
		});

	$(window).on('resize', close);
	// Close on scroll of any ancestor (modal body, page) — but NOT when scrolling inside the palette
	// itself. Native capture catches scrolls on inner containers that don't bubble.
	window.addEventListener('scroll', function (e) {
		if (!$palette || $palette.attr('aria-hidden') === 'true') { return; }
		if (e.target && e.target.nodeType && $(e.target).closest('.upw-easing-palette').length) { return; }
		close();
	}, true);
})(jQuery);
