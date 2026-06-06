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

	// 2) Masonry packing preview in the canvas — mirror the frontend engine so the
	//    editor matches the live page. Column items render asynchronously and their
	//    heights settle AFTER they're inserted (no DOM mutation fires for that), so
	//    a MutationObserver alone packs with stale/short heights. A ResizeObserver
	//    on each column item catches the height settling and re-packs — exactly what
	//    the frontend (masonry-section.js) does.
	var ROW_UNIT = 1; // px — must match `grid-auto-rows` in styles.css

	function isMasonryContainer(node) {
		return node && node.classList && node.classList.contains('builder-items') &&
			node.parentNode && node.parentNode.classList &&
			node.parentNode.classList.contains('pb-section-like-masonry_section');
	}

	function masonryContainers() {
		return document.querySelectorAll('.pb-section-like-masonry_section > .builder-items');
	}

	function layoutContainer(container) {
		if (!container) {
			return;
		}
		container.classList.add('fw-masonry-ready');

		if (window.getComputedStyle(container).display !== 'grid') {
			return;
		}

		var items = container.children, i, item, mb, h;

		// Reset spans so each item reports its natural (un-stretched) height.
		for (i = 0; i < items.length; i++) {
			items[i].style.gridRowEnd = '';
		}
		// Measure + assign spans. Use the larger of the layout box and scrollHeight:
		// the builder's column markup uses floated inner panels, so the item box can
		// collapse (rect ~11px) while the real content overflows (scrollHeight ~334px).
		// scrollHeight reflects the true content height regardless.
		for (i = 0; i < items.length; i++) {
			item = items[i];
			mb = parseFloat(window.getComputedStyle(item).marginBottom) || 0;
			h = Math.max(item.getBoundingClientRect().height, item.scrollHeight) + mb;
			item.style.gridRowEnd = 'span ' + Math.max(1, Math.ceil(h / ROW_UNIT));
		}
	}

	function layoutAll() {
		var cs = masonryContainers();
		for (var i = 0; i < cs.length; i++) {
			layoutContainer(cs[i]);
		}
	}

	// Re-pack the owning masonry container when one of its column items changes
	// size (content rendering, editing, image load, breakpoint reflow).
	var ro = (typeof ResizeObserver !== 'undefined')
		? new ResizeObserver(function (entries) {
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

	var timer = null;
	function schedule() {
		if (timer) {
			window.clearTimeout(timer);
		}
		timer = window.setTimeout(function () {
			layoutAll();
			observeItems();
		}, 80);
	}

	function init() {
		var root = document.querySelector('.fw-option-type-builder');
		if (!root) {
			window.setTimeout(init, 300);
			return;
		}

		layoutAll();
		observeItems();

		// Structural changes (items added/removed/reordered, options edited). We
		// watch childList/characterData only — never attributes — so our own
		// grid-row-end writes don't retrigger the observer.
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
