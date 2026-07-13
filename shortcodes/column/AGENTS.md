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

`config.php` only sets the page-builder edit-modal: `'type' => 'column'`
(so `get_shortcode_builder_data()` bails early — no duplicate element /
"No Page Builder tab specified" warning) and `'popup_size' => 'medium'`
(read by `get_item_data()`).

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

Grouped into `group_layout` (content arrangement) + `group_sizing` (width / offset) +
`group_reorder` (reordering) — all borderless `group`s that flatten. Every Layout-tab att
is **whitelisted in `view.php`** before becoming a class name. **Content Alignment /
Content Vertical Alignment / Content Direction / Column Vertical Alignment are
`image-picker`s**; **Width Override / Offset are `popover`→`image-picker`s**; all are
wrapped in the **`responsive`** option type (`base` / `md` / `lg`) EXCEPT `content_direction`
(a single value). Thumbnails are inline data-URI SVGs generated in `options.php` (no asset
files) — `$col_bar_uri()` draws the width/offset fraction bars (one blue chosen column + gray
remainder, drawn in lowest terms on a 60-unit `shape-rendering="crispEdges"` track) and
`$align_uri()` / `$valign_uri()` / `$dir_uri()` draw the alignment/direction glyphs. The
shared `$pick()` helper renders the large hover preview at up to 3× the thumbnail height.
Values are the plain strings the view whitelists (`'default'` / `'none'` = unset sentinels,
ignored; `'auto'` = flex-fill).

**Responsive atts** store `array( 'base' => …, 'md' => …, 'lg' => … )` (a blank device
inherits the smaller one). Each also **falls back to the legacy scalar / per-device atts** in
`view.php` so columns saved before the merge render identically.

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `content_h` | `responsive` → `image-picker` | `{base:'default',md:'',lg:''}` | **Content Alignment** (the primary way to align a column's content). Choices `default`/`start`/`center`/`end`/`between`/`around`/`evenly`. Makes the content container `d-flex` and maps **axis-aware**: `align-items-*` in a column, `justify-content-*` in a row. Distribute (`between`/`around`/`evenly`) apply only on the main axis |
| `content_direction` | `image-picker` (`column` / `row`) | `column` | **Content Direction** — Stacked (`flex-column`) vs Inline (`flex-row` + `flex-wrap`) |
| `content_v` | `responsive` → `image-picker` | `{base:'default',md:'',lg:''}` | **Content Vertical Alignment**. Choices `default`/`center`/`end`/`between`. Column main axis → `justify-content-*` (also adds `h-100` to the container so there is height to align within); row cross axis → `align-items-*` |
| `full_height` | `switch` (`yes` / `no`) | `no` | **Full Height** — adds `h-100` to the inner content area for equal-height cards. **Adds the inner wrapper** when set |
| `content_gap` | `responsive` → `short-select` | `{base:'',md:'',lg:''}` | **Gap** between elements. Gap-Scale slugs (`sc_get_gap_select_choices`) → `sc-cgap{-bp}-{slug}` (→ `gap:var(--gap-{slug})`) |
| `align_self` | `responsive` → `image-picker` | `{base:'default',md:'',lg:''}` | **Column Vertical Alignment** vs row siblings. Choices `default`/`start`/`center`/`end` → `align-self{-bp}-{v}` |
| `col_width` | `responsive` → `popover`(`image-picker`) | `{base:'default',md:'',lg:''}` | **Width Override** per device. Choices `default`/`1`–`12`/`15`/`25`/`35`/`45` (twelfths + fifths)/`auto`. Base **replaces** the phone (xs) width token; `md`/`lg` add `fw-col-md-*`/`fw-col-lg-*`; `auto` = flex-fill `fw-col[-bp]`. Legacy `w_phone`/`w_tablet`/`w_desktop` read as fallback |
| `col_offset` | `responsive` → `popover`(`image-picker`) | `{base:'none',md:'',lg:''}` | **Offset** (indent) per device. Choices `none`/`1`–`11`/`15`/`25`/`35`/`45` → `fw-offset{-bp}-{v}`. Legacy `offset_phone`/`offset_tablet`/`offset_desktop` fallback |
| `content_order` | `responsive` → `switch` (`no` / `yes`) | `{base:'no',md:'',lg:''}` | **Reverse Order** — flips the stack/row via `flex-{dir}-reverse` per breakpoint (literal — no alignment compensation). Legacy select `all`/`mobile`/`tablet` migrates |
| `mobile_order` | `responsive` → `short-select` | `{base:'',md:'',lg:''}` | **Order** vs row siblings. Choices `''`/`first`/`1`–`12`/`last` → `fw-order{-bp}-{v}`. Legacy scalar migrates to `{base:v, md:'0'}` |

**Position & Z-Index are no longer column options** — they come from the shared Advanced-tab
`element_position` control, applied to the outer column as inline style by
`sc_build_wrapper_attr()`.

### Tab: Styling

Wrapped in `group_colors` + `group_border_effects` + `group_spacings` (all flatten).
**Typography fields intentionally absent** — columns are layout containers, and
typography on the wrapper would cascade to nested shortcodes (rarely desired). The
visual atts below land on the **inner card wrapper** and trigger its rendering.

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `bg_color` | `sc_color_field_compact` (bg) | — | Inner-wrapper background (Background Color). **Triggers inner-wrapper rendering** |
| `border_preset` | `border-style-picker` (Box Preset) | `''` | Reusable box style (border + corners + shadow + optional bg fill + hover), defined in Theme Settings → Components → Box Presets. Value is a `boxp-{name}` slug → `.boxp-{name}` class on the inner card. Replaces the old manual border / rounded / shadow fields |
| `spacing` | `spacing` (Margin & Padding) | — | Inner-wrapper margin/padding. **Triggers inner-wrapper rendering** |

**Legacy render-only atts** (removed from the editor, but still rendered by `view.php`
for columns saved before Box Preset): `border_sides` (`all`/`top`/`end`/`bottom`/`start` →
`border`/`border-{side}`), `border_color` (bootstrap color → `border-{color}`, only with a
side), `border_width` (`1`–`5` → `border-{N}`, only with a side), `rounded`
(`rounded-1`/`rounded`/`rounded-3`/`rounded-pill`/`rounded-circle`), `shadow`
(`shadow-sm`/`shadow`/`shadow-lg`) — all land on the inner card wrapper.

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
  picker's `frontend_class` with the `col_width` per-breakpoint overrides applied
  (legacy `w_*` fallback), then the outer layout utilities (`col_offset` →
  `fw-offset-*` / `fw-order-*` / `align-self-*`) are appended. These live on the outer
  column (the flex item/slot), alongside width/animation classes. **Position & Z-Index**
  arrive as an inline style via the shared Advanced `element_position` control (through
  `sc_build_wrapper_attr()`), not as column-specific classes.
- **Content alignment** (`d-flex` + axis-aware `justify-content-*` / `align-items-*`),
  **direction** (`flex-row`/`flex-column` [`-reverse`] + `flex-wrap`), and **gap**
  (`sc-cgap-*`) are routed onto whichever element directly holds the content — the inner
  wrapper if one exists, otherwise the outer column (so "Space Between" can see 2+ children).
- When `inner_class` is set OR any Styling / Box Preset / Full Height pick triggers it, wraps
  the content in an **inner card** `<div>` carrying `{inner_class}` + background + spacing +
  `.boxp-{name}` + the legacy `border*` / `rounded*` / `shadow*` + `h-100`.
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
   Animations / Advanced tabs below it (Layout now carries responsive Content
   Alignment / Vertical Alignment / Direction / Gap / Column Vertical Alignment +
   responsive Width Override / Offset / Reverse Order / Order). Don't be misled by the
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
