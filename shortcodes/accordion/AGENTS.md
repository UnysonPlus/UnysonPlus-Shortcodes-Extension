---
type: shortcode
name: accordion
since: original Unyson (heavily extended in the Unyson+ fork — icon styles, numbering, hash deep-linking)
provides: leaf-shortcode
---

# Accordion

A vertically-stacked collapsible-panel widget. Each item has a title and a
WP editor body; items can be opened individually, all at once, or none.
Supports six built-in icon styles plus a custom-icon mode (image OR
emoji/text per state), six numbering schemes plus a custom template, URL
hash deep-linking, and Expand/Collapse-All convenience buttons.

## Registration

No `class-fw-shortcode-accordion.php` — leaf shortcode auto-instantiated by
the loader. No page-builder item class. Add a custom class only if you
need hook lifecycle.

`config.php` declares a `title_template` that previews every item's title +
content snippet on the canvas, so the page-builder item shows the full
accordion outline without opening the modal.

## Options schema (atts)

Source of truth: `options.php`. Five functional tabs + Animations +
Advanced.

### Tab: Content

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `tabs` | `addable-popup` (sortable list) | — | The accordion items. Each entry is `{ tab_title, tab_content, is_open }` |
| `tabs[].tab_title` | `text` | — | Item heading |
| `tabs[].tab_content` | `wp-editor` | — | Item body — rich text |
| `tabs[].is_open` | `switch` (`yes` / `no`) | `no` | Per-item override of `initially_open` — render THIS item already expanded on first load |

### Tab: Layout

Wrapped in `group_layout` (flattens on save).

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `title_tag` | `select` (`h2`–`h6`) | `h3` | Semantic tag for every item title. Match the page outline |
| `icon_style` | `select` (`plus-minus` / `plus-x` / `chevron` / `arrow` / `none` / `custom`) | `plus-minus` | Toggle indicator visual |
| `icon_position` | `select` (`left` / `right`) | `left` | Icon placement in the title bar |
| `icon_closed_image` | `upload` | — | Custom closed-state image (PNG/JPG/SVG). Only when `icon_style === 'custom'`. Overrides `icon_closed_text` |
| `icon_open_image` | `upload` | — | Custom open-state image. Same gating |
| `icon_closed_text` | `short-text` | `+` | Custom closed-state text/emoji. Used when `icon_style === 'custom'` and no image is set |
| `icon_open_text` | `short-text` | `−` | Custom open-state text/emoji |
| `numbering.style` | `multi-picker` (`none` / `decimal` / `decimal-leading-zero` / `lower-alpha` / `upper-alpha` / `lower-roman` / `upper-roman` / `q-prefix` / `custom`) | `none` | Item number prefix scheme |
| `numbering.custom.template` | `text` | `Q{n}` | Custom prefix template, only when `numbering.style === 'custom'`. Tokens: `{n}`, `{0n}`, `{a}`/`{A}`, `{i}`/`{I}` |
| `numbering_start` | `short-text` | `1` | First item's number |
| `item_spacing` | `sc_spacing_field` (prefix: `mb`) | — | Vertical gap between items |
| `title_alignment` | `select` (`left` / `center` / `right`) | `left` | Horizontal alignment of the title row |

### Tab: Behaviour

Wrapped in `group_layout` (same group key as Layout — both flatten to the same top-level atts).

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `initially_open` | `select` (`first` / `none` / `all`) | `first` | Which panels are expanded on page load |
| `collapsible` | `switch` (`yes` / `no`) | `no` | Allow all panels to be closed simultaneously |
| `multiple_open` | `switch` (`yes` / `no`) | `no` | Allow multiple panels open at once |
| `hash_linking` | `switch` (`yes` / `no`) | `yes` | Auto-open the panel matching the URL hash + update hash on toggle |
| `show_expand_collapse_all` | `switch` (`yes` / `no`) | `no` | Render two convenience buttons above the accordion |

### Tab: Styling

Wrapped in `group_colors` + `group_spacings` (both flatten).

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `font_size_preset` | `sc_font_size_field` | — | Named size from theme presets |
| `tab_title_color` | `sc_color_field_compact` | — | All item titles |
| `title_bg_color` | `sc_color_field_compact` (bg) | — | All item title bars |
| `tab_content_color` | `sc_color_field_compact` | — | All item bodies |
| `content_bg_color` | `sc_color_field_compact` (bg) | — | All item body panels |
| `icon_closed_color` | `sc_color_field_compact` | — | Toggle icon when collapsed |
| `icon_open_color` | `sc_color_field_compact` | — | Toggle icon when expanded — falls back to `icon_closed_color` |
| `spacing` | `sc_spacing_field` | — | Wrapper margin/padding |

### Tabs: Animations + Advanced

Standard `sc_get_animation_fields()` + `sc_get_advanced_tab()`.

### Verified `atts` (real export, framework 2.8.40)

Atom is `accordion`; it's a leaf (`{"type":"simple","shortcode":"accordion","atts":{…},"_items":[]}`).
Shared blocks (`animation`, `*_color`, `spacing`, the common `unique_id`/`css_id`/`css_class`/
`responsive_hide`/`custom_attrs` keys) are documented once in the page-builder playbook
(`../../extensions/page-builder/AGENTS.md` §3). Accordion-specific shape:

```json
{
  "tabs": [
    {"tab_title":"How do we score each site?","tab_content":"<p>…</p>","is_open":"yes"},
    {"tab_title":"Is it safe?","tab_content":"<p>…</p>","is_open":"no"}
  ],
  "title_tag":"h3","icon_style":"plus-minus","icon_position":"right",
  "icon_closed_image":"","icon_open_image":"","icon_closed_text":"+","icon_open_text":"−",
  "numbering":{"style":"none","custom":{"template":"Q{n}"}},"numbering_start":"1",
  "item_spacing":"","title_alignment":"left","initially_open":"first","collapsible":"no",
  "multiple_open":"no","hash_linking":"yes","show_expand_collapse_all":"no"
}
```

Notes: `tab_content` is HTML (`<p>…</p>`); `is_open` is per-item `"yes"`/`"no"` (string, not bool);
the open-icon glyph is the Unicode minus `−` (U+2212), not a hyphen.

## Rendering

`views/view.php` (refer to file) outputs:
- Wrapper `<div class="fw-accordion ...">` with state classes
- Optional Expand/Collapse-All buttons
- For each `tabs[]` item: a heading element (per `title_tag`) with the
  toggle icon, optional number prefix, and title text, plus a content
  panel containing the rendered `tab_content` HTML

`static/js/scripts.js` powers toggle behavior — clicks, keyboard
navigation, URL hash sync, multi-open vs single-open enforcement, and
auto-expand from hash on page load.

## Pitfalls

1. **Two `group_layout` groups** — both the Layout AND Behaviour tabs wrap
   options in a group keyed `group_layout`. Unyson flattens group
   containers, so the keys merge at top level. If two groups named the same
   thing contain the same option key, the second wins. Currently safe (no
   key collisions) but be careful when adding fields.
2. **Custom icon priority** — when `icon_style === 'custom'`, the per-state
   `icon_closed_image` / `icon_open_image` upload wins; `icon_closed_text` /
   `icon_open_text` is the fallback.
3. **Hash-linking uses an auto-generated ID** — each panel's hash anchor is
   computed from the wrapper's ID + panel index. Setting `css_id` on the
   shortcode changes the hash. Don't rely on these hashes for permanent
   external links.

## Verification

1. Drag Accordion from Content Elements → modal opens; add 3 items.
2. Save → reload → renders 3 collapsed panels with the first expanded
   (default `initially_open: first`).
3. Switch `icon_style: chevron` → chevron icons replace `+/−`.
4. Switch `numbering.style: q-prefix` → titles prefix `Q1`, `Q2`, `Q3`.
5. Set `multiple_open: yes` → can expand multiple panels.
6. Set `hash_linking: yes` → click a panel; URL hash updates; reload page
   with that hash → that panel auto-expands.

## Files

- `config.php`, `options.php`, `static.php`, `views/view.php`
- `static/js/scripts.js` — toggle behavior + hash sync
- `static/css/styles.css` (via static.php)
- `static/img/page_builder.png` — Layout Elements thumbnail

No item class, no admin-side asset, no custom class file — standard leaf
layout.
