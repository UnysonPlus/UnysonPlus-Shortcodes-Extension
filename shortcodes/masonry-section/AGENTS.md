# `masonry_section` shortcode

A **section-like** Layout Element (sibling to `section` / `hero_section`) that arranges its
child columns in a **12-track CSS-grid tetris masonry**: columns keep their real widths and
later short columns tuck UP to fill vertical gaps. Modeled exactly on `../hero-section/`.

> **Watch out — placement is EXPLICIT, do not go back to `grid-auto-flow: dense`.** The real,
> root-cause bug (after a very long hunt): with `dense` auto-placement, when JS changes the items'
> row-spans, the browser keeps the placement it computed while the spans were still tiny and never
> re-flows the columns — so a tall `3/4` got stranded in row 2 (reads `1/4, 1/4, 3/4`) and only a
> drag (a full fresh render) fixed it. The fix is a **deterministic bin-packer** in BOTH
> `includes/.../static/js/scripts.js` (editor) and `static/js/masonry-section.js` (frontend):
> measure each column's height, then compute and write an **explicit `grid-column` AND `grid-row`
> start** (leftmost column-range with the smallest current top). No auto-flow, nothing to go stale.
> The earlier theories — "width class not ready" and "stale paint" — were red herrings caused by
> diagnosing the POST-drag (already-correct) state; the lesson: trace the BROKEN state and measure
> POSITION (`getBoundingClientRect().top`), not just width. The editor keeps a `forceRepaint()`
> (synchronous `display:none→grid` toggle, with the ResizeObserver suppressed via `roSuppressed`)
> as a belt-and-suspenders redraw; it's harmless but the bin-packer is what actually fixed it.
>
> Also note: the editor preview CSS/JS are versioned by the **file mtime** (see
> `Page_Builder_Section_Like_Item::enqueue_static`) — NOT the theme/manifest version, which hosts
> like WP Engine opcache, so bumping it never changed the `?ver` and stale editor assets were
> served for the entire saga. filemtime busts the `?ver` on every edit, opcache or not.

## How it wires up
- `class-fw-shortcode-masonry-section.php` — `_init()` adds the two registration hooks
  (`fw_option_type_builder:page-builder:register_items` + `fw_section_like_types`) and the
  shortcode-data collector, mirroring `FW_Shortcode_Hero_Section`. Icon is provided by URL
  via `locate_URI('/static/img/page_builder.svg')` (rendered as `<img src>`, like Section).
- `includes/page-builder-masonry_section-item/class-…-item.php` extends
  `Page_Builder_Section_Like_Item` (registry, no auto-wrap into `[section]`, inner correction,
  auto-enqueue of this item folder's `static/{css,js}` in the editor).
- `includes/.../static/js/scripts.js` registers the view via `window.createSectionLikeItem`.

## The layout
- Frontend: `views/view.php` outputs `<section class="masonry-section" style="--mc-gap">`.
  `static/css/masonry-section.css` makes the inner `.fw-row` a **12-track** grid with
  `grid-auto-flow: dense`; `static/js/masonry-section.js` sets per column `grid-column: span N`
  (N = its /12 width from the `fw-col-{bp}-N` class, mobile-first cascade base→sm→md→lg) and
  `grid-row-end: span K` (measured height). `dense` flow tucks a later short column up into the
  gap beside/under a taller one. The old `cols_lg/md/sm` options are removed. The **`gap` option
  defaults to "Use Default Gap" (empty)**: view.php only emits `--mc-gap` for an explicit size, so
  the CSS falls back to `var(--bs-gutter-x, 1.5rem)` — the same site/section gutter a standard
  `[section]` row uses, so masonry gaps match the rest of the site instead of hardcoding 1.5rem.
- Editor preview mirrors the front end's result but gets there differently: this item's
  `…/static/css/styles.css` makes the child `.builder-items` a 12-track dense grid AND maps each
  `fw-col-sm-N` class straight to `grid-column: span N` (widths are pure CSS — see the Watch-out
  above). Its `…/static/js/scripts.js` ONLY sets row spans (`grid-row-end` from measured height;
  items are `display:flow-root` for a true height), with staggered re-packs after DOM changes so
  heights settle after a drop. **flex-canvas is excluded** from masonry containers (it skips
  `.pb-section-like-masonry_section` — see `…/page-builder/…/static/js/flex-canvas.js`); otherwise
  its equal-height stretch + growing drop zone fight the grid.

## Conventions
- `'type' => 'masonry_section'` in `config.php` MUST match `get_type()` in the item class.
- Bump `framework/extensions/shortcodes/manifest.php` for any change here (+ the plugin
  version per the workspace rule). The factory edit lives in the `page-builder` extension.
