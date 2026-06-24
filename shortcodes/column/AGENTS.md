---
type: shortcode
name: column
since: original Unyson (extended in Unyson+ — inner wrapper class, full-height switch, dedicated template-component)
provides: page-builder-structural
---

# Column

A page-builder structural element representing one Bootstrap column
inside a row. **Manually draggable** from the Layout Elements tab (with
width-picker thumbnails for 1/12 through 12/12). Holds leaf shortcodes
inside; rows wrap groups of columns automatically.

One of three structural builders (`section` / `row` / `column`):
```
section → row → column → leaf shortcodes
```

Has its **own** Templates dropdown tab — Templates → Columns — so users
can save column structures with their inner content (e.g. a stylized
quote column reused across pages). Section-style save / load / delete /
export / import live in `includes/template-component/`.

## Registration

`class-fw-shortcode-column.php` declares `FW_Shortcode_Column extends
FW_Shortcode`. The `_init()` method hooks:

| Hook | Purpose |
|------|---------|
| `fw_option_type_builder:page-builder:register_items` action → `_action_register_builder_item_types()` | Loads `Page_Builder_Column_Item` when the editor renders |
| `fw_ext:shortcodes:collect_shortcodes_data` filter → `_filter_add_column_data()` | Exposes column's options + item widths + `restrictedTypes: ['column']` (blocks column-into-column **drag-drop** in the editor only — the data model / corrector / renderer support nesting; see Pitfall 4) to the frontend collector |

The class also exposes `get_item_data()` for the page-builder item data
bundle — same shape as `section`'s but adds:
- `restrictedTypes: ['column']` — enforces "columns cannot contain
  columns" (hierarchy guard)
- `item_widths` from `fw_ext_builder_get_item_widths_for_js('column')` —
  the 1/12 → 12/12 width-picker thumbnails shown in the Layout Elements
  tab

The Templates component (`includes/template-component/…`) is unique to
this shortcode — it's how the "Columns" tab in the Templates dropdown
works. Mirror structure to `section/includes/template-component/`.

## Page-builder item class

`includes/page-builder-column-item/class-page-builder-column-item.php`
declares `Page_Builder_Column_Item extends Page_Builder_Item`. It owns:

- The column-item editor view template (per `scripts.js` in the same
  directory) — renders the canvas item with width fraction "1/3"-style
  label, controls (edit / duplicate / delete / collapse), and inner
  `.builder-items` for children
- Inner-wrapper rendering: when any Styling-tab pick is made OR
  `inner_class` is set, the view emits an inner `<div>` around the
  column content; otherwise the column is a bare Bootstrap `col-{N}`

## Options schema (atts)

Source of truth: `options.php`. Two tabs + Animations + Advanced.
**Most of `options.php` is commented out** — the original Unyson column
had Layout / Spacing / Display / Background / Border / Text / Effects /
Position tabs (8 tabs!). The Unyson+ fork trimmed to a minimal active set
to keep column editing focused.

### Tab: Layout

All Layout-tab atts below are **whitelisted in `view.php`** before becoming class
names, and (except where noted) land on the **outer** column (the flex item/slot).
**Width / Offset / Alignment are all `image-picker`s** with inline data-URI SVG
thumbnails generated in `options.php` (no asset files) — built by `$col_bar_uri()`
(a 12-cell bar on a 60-unit grid + `shape-rendering="crispEdges"` so the many-cell
bars stay sharp) and `$align_uri()` / `$valign_uri()` for the alignment glyphs. The
shared `$pick()` helper sets the **large hover preview to 2× the thumbnail height**.
In all cases the value is the same plain string the view whitelists (`'default'` /
`'none'` = unset sentinels, ignored; `'auto'` = flex-fill).

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `full_height` | `switch` (`yes` / `no`) | `no` | Stretch the inner content area to full row height (`h-100`). **Adds the inner wrapper** when set |
| `mobile_order` | `select` (`''`/`first`/`1`–`5`/`last`) | `''` | Phone (`<576px`) order → `fw-order-{v} fw-order-sm-0` (reorders while stacked, resets from `sm` up). **The only order field** — md/lg order was removed as rarely used |
| `w_phone` / `w_tablet` / `w_desktop` / `w_large` | `image-picker` (`default`/`1`–`12`/`auto`) | `default` | **Responsive width overrides** layered on the width picker (= the `sm`/default width). Emit `fw-col-{N}` (phone, replaces the base xs token) / `fw-col-md-` / `fw-col-lg-` / `fw-col-xl-`. `default` = inherit; `auto` = `fw-col[-bp]` (flex-fill, not a fixed fraction) |
| `offset_phone` / `offset_tablet` / `offset_desktop` | `image-picker` (`none`/`1`–`11`) | `none` | Indent → `fw-offset-{N}` / `fw-offset-md-` / `fw-offset-lg-` |
| `align_self` | `image-picker` (`default`/`start`/`center`/`end`/`stretch`) | `default` | Column's own vertical alignment vs siblings → `align-self-{v}` |
| `content_v` / `content_h` | `image-picker` (`default`/`start`/`center`/`end`[/`between` for v]) | `default` | Content alignment — when either set, the outer column becomes `d-flex flex-column` + `justify-content-{v}` (vertical) / `align-items-{h}` (horizontal). Vertical needs column height (Full Height or a taller sibling) |
| `position` | `select` (`static`/`relative`/`absolute`/`sticky`/`fixed`) | `''` | `position-{v}` on the outer column; `sticky` also adds `top-0`. `absolute`/`fixed` leave the grid flow |
| `z_index` | `short-text` (number) | `''` | Inline `z-index` on the outer column (effective only with a Position) |

### Tab: Styling

Wrapped in `group_colors` + `group_spacings` + `group_border_effects` (all flatten).
**Typography fields intentionally absent** — columns are layout containers, and
typography on the wrapper would cascade to nested shortcodes (rarely desired). The
visual atts below land on the **inner card wrapper** and trigger its rendering.

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `bg_color` | `sc_color_field_compact` (bg) | — | Inner-wrapper background. **Triggers inner-wrapper rendering** |
| `spacing` | `sc_spacing_field` | — | Inner-wrapper margin/padding. **Triggers inner-wrapper rendering** |
| `border_sides` / `border_color` / `border_width` | `select` | `''` | `border`/`border-{side}` + optional `border-{color}` / `border-{1-5}` (color/width apply only with a side set) |
| `rounded` | `select` | `''` | `rounded-1` / `rounded` / `rounded-3` / `rounded-pill` / `rounded-circle` |
| `shadow` | `select` | `''` | `shadow-sm` / `shadow` / `shadow-lg` |

### Tabs: Animations + Advanced

Standard. **Advanced has one custom field** injected via the closure in
`options.php`:

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `inner_class` | `text` | — | Optional class(es) for the inner `<div>` wrapper. **Setting this triggers inner-wrapper rendering** even when no Styling pick is made |

The closure in `options.php` re-fetches `sc_get_advanced_tab()` and
injects `inner_class` into the `group_css` group right after `css_class`,
preserving option order (PHP arrays preserve insertion order).

## Rendering

`views/view.php`:
- Outputs the **outer** Bootstrap grid column. Its width is rebuilt from the
  picker's `frontend_class` with the `w_*` per-breakpoint overrides applied, then
  the layout utilities (offset / `fw-order-*` / `align-self-*` / `d-flex flex-column`
  content alignment / `position-*` + inline `z-index`) are appended. These all live
  on the outer column (the flex item/slot), alongside width/animation classes.
- When `inner_class` is set OR any Styling/border/effect pick triggers it, wraps the
  content in an **inner card** `<div>` carrying `{inner_class}` + background + spacing
  + `border*` / `rounded*` / `shadow*`.
- Otherwise the content sits directly inside the column wrapper.
- All new utility values are reused theme/Bootstrap classes (no new CSS). The desktop
  editor canvas does not preview the responsive (per-breakpoint) behavior.

## Pitfalls

1. **Inner-wrapper auto-injection** — the inner `<div>` only renders
   when the column "needs" it (Styling picks made OR `inner_class` set
   OR `full_height: yes`). Generators should know this — emitting
   `bg_color` automatically wraps content in an inner div.
2. **A large commented-out legacy block remains at the top of `options.php`** —
   the original 8-tab Unyson column. The **active** set is the Layout / Styling /
   Animations / Advanced tabs below it (Layout now also carries responsive width,
   offset, breakpoint order, alignment, and position). Don't be misled by the
   commented blocks — edit the active arrays.
3. **No typography in Styling** — text color / font size / weight aren't
   on the column. Set those on the inner leaf shortcodes (text-block,
   special-heading) instead.
4. **`restrictedTypes` is an EDITOR-only guard, not a data-model limit** —
   defined on the class, exposed via `get_item_data()`, it only stops
   column-into-column **drag-and-drop** in the editor UI. The data model,
   the items-corrector (`correct_nested_columns()` synthesizes an inner
   `.fw-row` around child columns at **any depth**), and the renderer all
   handle column-in-column fine. So **imported / hand-authored / converter-
   emitted JSON may nest columns** (a `7_12` column whose `_items` are four
   `1_2` columns is valid and renders correctly). Don't read this guard as
   "never emit nested columns" — that's the mistake that flattens a
   Bootstrap `.col > .row > .col` sub-grid into one column with one leaf.
5. **Width is part of `Page_Builder_Column_Item`, not options.php** —
   the width fraction (1/12 through 12/12) is set via the column item's
   width-picker UI in the editor, not as an `atts` field in
   `options.php`. The page-builder item data exposes
   `item_widths` from `fw_ext_builder_get_item_widths_for_js('column')`.

## Verification

1. Drag a Column from Layout Elements → drops with default width
   (typically 1/3 or auto).
2. Click the width picker on the column item → switch to 1/2 → column
   resizes.
3. Drag a Text Block inside → leaf nests under the column.
4. Try to drag another Column inside → rejected (restrictedTypes guard).
5. Set `bg_color` → reload → inner-wrapper div is auto-injected with
   the bg class.
6. Set `inner_class: 'p-4 border'` → reload → inner-wrapper has those
   classes.
7. Save a column as a Column template → appears in Templates → Columns
   list.
8. Export → import → column atts + width + inner content round-trip.

## Files

- `class-fw-shortcode-column.php` — main class with hooks +
  `get_item_data()`
- `options.php`, `static.php`, `views/view.php`
- `includes/page-builder-column-item/class-page-builder-column-item.php` —
  item class
- `includes/template-component/class-fw-ext-builder-templates-component-column.php` —
  Templates → Columns component (save / load / delete / export / import
  handlers)
- `includes/template-component/init.php` — registers the template
  component
- `includes/template-component/scripts.js` — save-as-template UI + per-
  row export / import buttons (mirrors section's)
- `static/css/styles.css` (via static.php)
- `static/img/page_builder.png` — Layout Elements thumbnail

Structural shortcode — class file + item class + dedicated template
component. The Layout Elements tab's width-picker thumbnails are
generated dynamically from `item_widths`.
