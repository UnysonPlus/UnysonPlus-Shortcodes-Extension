(function (fwe) {
	'use strict';

	// 1) Register the section-like editor view/model for this type.
	fwe.on('fw-builder:' + 'page-builder' + ':register-items', function (builder) {
		var ItemClass = window.createSectionLikeItem(builder, {
			type: 'masonry_section',
			dataGlobalName: 'page_builder_item_type_masonry_section_data'
		});

		builder.registerItemClass(ItemClass);
	});

	// 2) Masonry packing preview in the canvas.
	//
	//    We place every column EXPLICITLY with a deterministic bin-packer (compute
	//    each column's grid-column AND grid-row start ourselves). We do NOT rely on
	//    `grid-auto-flow: dense`: it mis-places items when their row-spans are
	//    changed via JS — it keeps the placement it computed while the spans were
	//    still tiny and never re-flows, which stranded the 3/4 in row 2 until a drag
	//    forced a fresh render. Explicit placement has nothing to go stale.
	var ROW_UNIT = 1; // px — must match `grid-auto-rows` in styles.css
	var COLS = 12;

	function isMasonryContainer(node) {
		return node && node.classList && node.classList.contains('builder-items') &&
			node.parentNode && node.parentNode.classList &&
			node.parentNode.classList.contains('pb-section-like-masonry_section');
	}

	function masonryContainers() {
		return document.querySelectorAll('.pb-section-like-masonry_section > .builder-items');
	}

	// True content height (item is flow-root; scrollHeight only as a collapse guard).
	function itemHeight(item) {
		var h = item.getBoundingClientRect().height;
		if (h < 5) { h = item.scrollHeight; }
		return h + (parseFloat(window.getComputedStyle(item).marginBottom) || 0);
	}

	// Column span from the width class (fw-col-sm-N). Default 12 if absent.
	function colSpan(el) {
		for (var n = COLS; n >= 1; n--) {
			if (el.classList.contains('fw-col-sm-' + n)) { return n; }
		}
		return COLS;
	}

	function layoutContainer(container) {
		if (!container || window.getComputedStyle(container).display !== 'grid') {
			return;
		}
		container.classList.add('fw-masonry-ready');

		var items = container.children, i, k;

		// Phase 1 — set each column's WIDTH (span, auto start) so it measures its
		// height at the real width.
		var spans = [];
		for (i = 0; i < items.length; i++) {
			spans[i] = Math.max(1, Math.min(COLS, colSpan(items[i])));
			items[i].style.gridColumn = 'span ' + spans[i];
			items[i].style.gridRow = '';
		}

		// Phase 2 — measure heights (at the real width) → row spans.
		var rowSpans = [];
		for (i = 0; i < items.length; i++) {
			rowSpans[i] = Math.max(1, Math.ceil(itemHeight(items[i]) / ROW_UNIT));
		}

		// Phase 3 — explicit bin-pack: place each column at the leftmost column-range
		// with the smallest current top (a standard masonry/tetris pack), writing an
		// exact grid-column AND grid-row start. Deterministic; no auto-flow.
		var colH = [];
		for (i = 0; i < COLS; i++) { colH[i] = 0; }

		for (i = 0; i < items.length; i++) {
			var span = spans[i];
			var rowSpan = rowSpans[i];

			var bestCol = 0, bestY = Infinity;
			for (var c = 0; c + span <= COLS; c++) {
				var y = 0;
				for (k = c; k < c + span; k++) { if (colH[k] > y) { y = colH[k]; } }
				if (y < bestY) { bestY = y; bestCol = c; }
			}

			items[i].style.gridColumn = (bestCol + 1) + ' / span ' + span;
			items[i].style.gridRow = (bestY + 1) + ' / span ' + rowSpan;

			var top = bestY + rowSpan;
			for (k = bestCol; k < bestCol + span; k++) { colH[k] = top; }
		}

		forceRepaint(container);
	}

	// Force the canvas to redraw after placement. Chrome can keep a stale paint of
	// the grid when item placement is set via JS; a synchronous `display` none→grid
	// toggle forces a full re-layout + re-raster (what a drag does). It's
	// synchronous so the `none` state is never painted (no flicker). The toggle
	// resizes items, which would fire the ResizeObserver and loop, so we suppress
	// the observer across it (and for two frames after, until its notifications
	// drain).
	var roSuppressed = false;
	function forceRepaint(container) {
		roSuppressed = true;
		var inline = container.style.display;
		container.style.display = 'none';
		void container.offsetHeight;          // reflow while hidden
		container.style.display = inline;     // '' → back to CSS `display:grid !important`
		void container.offsetHeight;          // reflow + repaint the restored grid
		window.requestAnimationFrame(function () {
			window.requestAnimationFrame(function () {
				roSuppressed = false;
			});
		});
	}

	function layoutAll() {
		var cs = masonryContainers();
		for (var i = 0; i < cs.length; i++) {
			layoutContainer(cs[i]);
		}
	}

	// Re-pack the owning container when a column changes size (content rendering,
	// editing, image load, a width-class change resizing it, breakpoint reflow).
	var ro = (typeof ResizeObserver !== 'undefined')
		? new ResizeObserver(function (entries) {
			if (roSuppressed) { return; } // ignore the resize our own repaint causes
			var seen = [];
			for (var i = 0; i < entries.length; i++) {
				var container = entries[i].target.parentNode;
				if (isMasonryContainer(container) && seen.indexOf(container) === -1) {
					seen.push(container);
				}
			}
			for (var j = 0; j < seen.length; j++) {
				layoutContainer(seen[j]);
			}
		})
		: null;

	function observeItems() {
		if (!ro) {
			return;
		}
		var cs = masonryContainers();
		for (var i = 0; i < cs.length; i++) {
			var items = cs[i].children;
			for (var j = 0; j < items.length; j++) {
				ro.observe(items[j]); // observing an already-observed node is a no-op
			}
		}
	}

	function packNow() {
		layoutAll();
		observeItems();
	}

	var timer = null;
	function schedule() {
		if (timer) {
			window.clearTimeout(timer);
		}
		timer = window.setTimeout(function () {
			packNow();
			// After a drop/re-render the column heights settle a few ticks later;
			// re-pack so the placement catches up.
			window.setTimeout(packNow, 250);
			window.setTimeout(packNow, 700);
		}, 60);
	}

	function init() {
		var root = document.querySelector('.fw-option-type-builder');
		if (!root) {
			window.setTimeout(init, 300);
			return;
		}

		packNow();
		[150, 400, 900].forEach(function (d) {
			window.setTimeout(packNow, d);
		});

		if (typeof MutationObserver !== 'undefined') {
			new MutationObserver(schedule).observe(root, {
				childList: true,
				subtree: true,
				characterData: true
			});
		}

		window.addEventListener('resize', schedule);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})(fwEvents);
