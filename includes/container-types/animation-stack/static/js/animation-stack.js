/**
 * Animations-tab organizer — the "Add Animation" inserter + card stack.
 *
 * Pure event delegation on `document`: initial card visibility is server-rendered (a card is
 * `.is-hidden` when its picker sits on the "off" value), so no init/observer pass is needed —
 * the modal renders its cards ready, and these handlers wire the interactions.
 *
 * Add:    reveal the card, open its popover so the user picks an effect.
 * Remove: reset the card's picker <select> to its "off" value + fire the native `change` that
 *         drives the multi-picker (collapses the reveal, updates the popover summary, and — since
 *         save reads the DOM <select> value — persists "off"). No fragile internal APIs.
 */
(function ($) {
	'use strict';

	function filterTiles($catalog) {
		var cat = $catalog.find('.upw-anim-tab.is-on').data('cat') || 'all';
		var q = String($catalog.find('.upw-anim-search').val() || '').toLowerCase().trim();
		var terms = q ? q.split(/\s+/) : [];
		var shown = 0;

		$catalog.find('.upw-anim-tile').each(function () {
			var $t = $(this);
			if ($t.hasClass('is-added')) { return; } // already on the element — never in the grid
			var okCat = cat === 'all' || String($t.data('cat')) === String(cat);
			// Search the tile's visible text (module name + category) AND its style/effect keywords,
			// so e.g. "letter jump" surfaces Text Effect. All typed terms must be present.
			var hay = ($t.text() + ' ' + ($t.attr('data-keywords') || '')).toLowerCase();
			var okQ = terms.every(function (term) { return hay.indexOf(term) >= 0; });
			var show = okCat && okQ;
			$t.toggleClass('is-filtered', !show);
			if (show) { shown++; }
		});

		$catalog.find('.upw-anim-tiles-empty').toggleClass('is-hidden', shown > 0);
	}

	function updateEmpty($stack) {
		var any = $stack.find('.upw-anim-card:not(.is-hidden)').length > 0;
		$stack.find('.upw-anim-empty').toggleClass('is-hidden', any);
	}

	// Toggle the inserter panel.
	$(document).on('click', '.upw-anim-add', function (e) {
		e.preventDefault();
		var $catalog = $(this).siblings('.upw-anim-catalog');
		var open = $catalog.prop('hidden');
		$catalog.prop('hidden', !open);
		if (open) {
			filterTiles($catalog);
			$catalog.find('.upw-anim-search').trigger('focus');
		}
	});

	// Category tab.
	$(document).on('click', '.upw-anim-tab', function (e) {
		e.preventDefault();
		var $catalog = $(this).closest('.upw-anim-catalog');
		$catalog.find('.upw-anim-tab').removeClass('is-on');
		$(this).addClass('is-on');
		filterTiles($catalog);
	});

	// Search.
	$(document).on('input', '.upw-anim-search', function () {
		filterTiles($(this).closest('.upw-anim-catalog'));
	});

	// Add — reveal a card + open its popover picker.
	// Single-instance module: reveal its one card and mark the tile "added" (it leaves the grid).
	// Multi-instance module (data-multi): reveal the NEXT empty slot card and keep the tile in the
	// grid so more can be added; if every slot is used, do nothing.
	$(document).on('click', '.upw-anim-tile', function (e) {
		e.preventDefault();
		var $tile = $(this);
		var isMulti = $tile.is('[data-multi]');
		if (!isMulti && $tile.hasClass('is-added')) { return; }

		var id = $tile.data('target');
		var $stack = $tile.closest('.upw-anim-stack');

		var $card;
		if (isMulti) {
			$card = $stack.find('.upw-anim-card[data-anim-id="' + id + '"].is-hidden').first();
			if (!$card.length) { return; } // all slots in use
		} else {
			$card = $stack.find('.upw-anim-card[data-anim-id="' + id + '"]').first();
			$tile.addClass('is-added');
		}

		$tile.closest('.upw-anim-catalog').prop('hidden', true);

		// Inactive cards ship as empty placeholders (the heavy per-effect options aren't rendered up
		// front — that's what OOMed the builder). Fetch this card's options once, inject + init them,
		// then reveal & open. Already-rendered cards fall straight through.
		if ($card.attr('data-anim-lazy') === '1') {
			lazyLoadCard($card, function () { revealCard($card, $stack); });
		} else {
			revealCard($card, $stack);
		}
	});

	// Fetch one card's options HTML over AJAX, inject into its body, and initialise the FW option
	// types inside (so the multi-picker / image-picker become interactive). Guards against a double
	// fetch. On failure the card still reveals (empty) rather than trapping the user.
	function lazyLoadCard($card, done) {
		$card.removeAttr('data-anim-lazy').addClass('upw-anim-card-loading');
		var cfg = window.upwAnimStack || {};
		$.ajax({
			url: cfg.ajaxurl || window.ajaxurl,
			method: 'POST',
			data: { action: 'upw_anim_render_card', slot: $card.attr('data-anim-slot'), nonce: cfg.nonce || '' }
		}).done(function (res) {
			if (res && res.success && res.data && res.data.html) {
				$card.find('.upw-anim-card-body').html(res.data.html);
				if (window.fwEvents && typeof fwEvents.trigger === 'function') {
					fwEvents.trigger('fw:options:init', { $elements: $card });
				}
			}
		}).always(function () {
			$card.removeClass('upw-anim-card-loading');
			done();
		});
	}

	// Reveal a card and open its popover picker so the user picks an effect immediately.
	function revealCard($card, $stack) {
		$card.removeClass('is-hidden');
		updateEmpty($stack);

		// Best-effort: keep the image-picker tile highlight in sync with the <select> value.
		var $sel = $card.find('.picker-group select').first();
		if ($sel.length) {
			var picker = $sel.data('picker');
			if (picker && typeof picker.sync_picker_with_select === 'function') {
				try { picker.sync_picker_with_select(); } catch (err) {}
			}
		}

		var $pop = $card.find('.fw-mp-pop').first();
		if ($pop.length) { $pop.addClass('is-open'); }

		if ($card[0] && $card[0].scrollIntoView) {
			$card[0].scrollIntoView({ block: 'nearest' });
		}
	}

	// Remove — reset the picker to "off" and tuck the card away.
	$(document).on('click', '.upw-anim-card-remove', function (e) {
		e.preventDefault();
		e.stopPropagation();

		var $card = $(this).closest('.upw-anim-card');
		var off = String($card.data('anim-off'));
		var id = $card.data('anim-id');
		var $stack = $card.closest('.upw-anim-stack');

		var $sel = $card.find('.picker-group select').first();
		if ($sel.length) {
			$sel.val(off).trigger('change'); // drives the multi-picker reveal + summary + save value
		}

		$card.addClass('is-hidden');
		$card.find('.fw-mp-pop').removeClass('is-open');
		$stack.find('.upw-anim-tile[data-target="' + id + '"]').removeClass('is-added');
		updateEmpty($stack);
	});

})(jQuery);
