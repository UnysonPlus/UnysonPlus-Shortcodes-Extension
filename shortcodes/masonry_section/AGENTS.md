# `masonry_section` shortcode

A **section-like** Layout Element (sibling to `section` / `hero_section`) that arranges its
child columns in a **left-to-right CSS-grid masonry**: items keep source order reading
across and pack vertically to fill gaps. Modeled exactly on `../hero_section/`.

## How it wires up
- `class-fw-shortcode-masonry_section.php` — `_init()` adds the two registration hooks
  (`fw_option_type_builder:page-builder:register_items` + `fw_section_like_types`) and the
  shortcode-data collector, mirroring `FW_Shortcode_Hero_Section`. Icon is provided by URL
  via `locate_URI('/static/img/page_builder.svg')` (rendered as `<img src>`, like Section).
- `includes/page-builder-masonry_section-item/class-…-item.php` extends
  `Page_Builder_Section_Like_Item` (registry, no auto-wrap into `[section]`, inner correction,
  auto-enqueue of this item folder's `static/{css,js}` in the editor).
- `includes/.../static/js/scripts.js` registers the view via `window.createSectionLikeItem`.

## The masonry layout
- Frontend: `views/view.php` outputs `<section class="masonry-section" style="--mc-lg/md/sm/gap">`.
  `static/css/masonry-section.css` turns the inner `.fw-row` into a CSS grid; `static/js/
  masonry-section.js` sets each child's `grid-row-end: span N` from its measured height
  (ResizeObserver + window load/resize). Items are `align-self:start` so measuring is stable.
- Column widths (1/3, 2/3…) are **ignored** inside a masonry section — the grid sets uniform
  widths from the responsive column count (Layout tab: cols_lg / cols_md / cols_sm + gap).
- Editor preview: the section-like factory tags the item `.pb-section-like-masonry_section`
  and copies `cols_*` onto the child `.builder-items` as `--mc-*`; this item's
  `…/static/css/styles.css` renders that container as a uniform N-column grid (overriding the
  flex-canvas flex). Full row-span packing is frontend-only.

## Conventions
- `'type' => 'masonry_section'` in `config.php` MUST match `get_type()` in the item class.
- Bump `framework/extensions/shortcodes/manifest.php` for any change here (+ the plugin
  version per the workspace rule). The factory edit lives in the `page-builder` extension.
