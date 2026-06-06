---
type: shortcode
name: divider
since: original Unyson
provides: leaf-shortcode
---

# Divider

A visual separator — either a styled horizontal line (with optional inline
text or icon) or pure whitespace. Lives inside columns; not a page-builder
container. One of the simplest "Content Elements" tab entries.

## Registration

Divider has no `class-fw-shortcode-divider.php` file. Unyson's shortcode
loader auto-instantiates a default `FW_Shortcode` for any directory under
`shortcodes/` that has the standard layout (`config.php`, `options.php`,
`views/view.php`). This is the pattern for **leaf shortcodes** — anything
that's not section-like / row-like / column-like and doesn't need a custom
class.

No page-builder item class is needed either — leaf shortcodes are placed
inside columns and rendered via the `[simple]` page-builder item path, not
their own item class.

If you need to register lifecycle hooks (custom AJAX, filters, etc.), add
`class-fw-shortcode-divider.php` declaring `FW_Shortcode_Divider extends
FW_Shortcode` with an `_init()` method. The class file is optional only
when the shortcode has no custom hook requirements.

## Options schema (atts)

Source of truth: `options.php`. Five tabs:

### Tab: Content

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `style.ruler_type` | `multi-picker` (`line` / `space`) | `line` | Outer mode — visible line vs pure whitespace. The other fields in this table are gated on this. |
| `style.line.line_design` | `select` | (first option) | Visual style for the line: `std` (solid), `gradient` (fade), `ornament` (glyph), `shadow` (inner shadow) |
| `style.line.content_type` | `select` | `none` | Optional inline element: `none`, `text`, `icon` |
| `style.line.title` | `text` | — | Text shown inline (visible only when `content_type === 'text'`) |
| `style.line.icon` | `icon` | — | Icon class (visible only when `content_type === 'icon'`) |
| `style.line.alignment` | `select` | `center` | Inline content alignment when text/icon is set |
| `style.space.height` | `text` (px integer) | `50` | Whitespace height (visible only when `ruler_type === 'space'`) |

### Tab: Layout

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `margin_top` | `text` (px) | — | Top margin |
| `margin_bottom` | `text` (px) | — | Bottom margin |
| `width` | `text` (%) | `100` | Divider width as % of container |

### Tab: Styling

Grouped into `group_colors` and `group_spacings`. Both are visual-only group
containers — Unyson flattens grouped option values to top-level `$atts` keys
on save, so AI generators should produce flat keys, NOT nested
`group_colors.bg_color`. The view at `views/view.php` reads them flat (see
`sc_extract_styling_atts()` calls).

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `bg_color` | `sc_color_field_compact` (bg) | — | Wrapper background color (named preset or custom hex) |
| `line_color` | `sc_color_field_compact` | — | Line color (border / gradient). Falls back to currentColor |
| `icon_color` | `sc_color_field_compact` | — | Inline icon color |
| `divider_text_color` | `sc_color_field_compact` | — | Inline text color |
| `spacing` | `sc_spacing_field` | — | Wrapper margin/padding spacing object — `{ all, top, right, bottom, left }` |

### Tab: Animations

`sc_get_animation_fields()` — the shared animation set (entry animation,
delay, duration). Common across all shortcodes.

### Tab: Advanced

`sc_get_advanced_tab()` — the shared advanced fields (`css_id`, `css_class`,
`responsive_hide`, `responsive` legacy, etc.). Common across all shortcodes.
Wrapped in a `group` container which flattens to top-level `$atts` keys.

### Generator note

For AI-generated `_fw_template_export` payloads, an `atts` object for a
divider should produce flat top-level keys for everything except the
`style` multi-picker (which keeps its nested shape) and group containers
which auto-flatten. Example shape:

```json
{
  "style": {
    "ruler_type": "line",
    "line": {
      "line_design": "std",
      "content_type": "text",
      "title": "Section break",
      "alignment": "center"
    }
  },
  "margin_top": "16",
  "margin_bottom": "16",
  "width": "60",
  "line_color": { "preset": "primary" },
  "css_id": "",
  "css_class": ""
}
```

## Rendering

`views/view.php` builds three things:

1. **Class list** — `fw-divider` + `divider-{line_design}` (for line mode)
   or `divider-space` (for space mode), plus `has-content` +
   `alignment-{alignment}` when an inline element is set. Responsive-hide
   keys + `css_class` are merged in.
2. **Inline styles** — `width`, `margin-top`, `margin-bottom`, height
   (space mode), plus a single inline style fragment from the line color
   picker when a custom hex is used.
3. **Inner element** — only when `ruler_type === 'line'` AND
   `content_type !== 'none'`. An `<span class="divider-inner">` containing
   either a `<i>` icon or the title text. Per-element color classes /
   inline styles are attached here, separate from the wrapper's.

Frontend CSS is at `static/css/styles.css`; no JS. The output is a single
`<div>` (with optional inner `<span>`) — no wrapper helper from
`sc_build_wrapper_attr()`, so the global wrapper-attr filter does NOT
run for this shortcode. Be aware if you add wrapper-attr behavior anywhere
else and expect it to apply universally.

## Pitfalls

1. **Multi-picker shape** — the `style` option is a `multi-picker`, NOT
   flattened. Generators must preserve the nested `style.ruler_type` +
   `style.line.{…}` / `style.space.{…}` shape. Other options on the
   Content tab are NOT inside any group, so they ARE flat (just `style`
   itself is nested).
2. **Group containers flatten** — `group_colors`, `group_spacings`,
   `advanced_settings` are all `type: group`. Unyson's
   `fw_collect_options()` flattens them on save, so `$atts['bg_color']`
   (not `$atts['group_colors']['bg_color']`) is correct.
3. **No wrapper helper** — divider builds its own wrapper attributes
   manually instead of going through `sc_build_wrapper_attr()`. Any
   global filter that hooks the wrapper-attr pipeline won't see this
   shortcode's wrapper.
4. **Legacy `responsive` fallback** — old saves used a `responsive` key
   for responsive-hide classes; new saves use `responsive_hide`. The view
   merges both so old content keeps working.

## Verification

1. Drag Divider from Content Elements → renders an empty `<div
   class="fw-divider divider-std">`.
2. Edit options → set `content_type: text`, type a title → reload →
   inline title appears inside `.divider-inner`.
3. Edit options → switch `ruler_type: space`, set `height: 80` → renders
   as an 80px-tall empty `<div>`.
4. Save as template (Sections tab won't accept it — dividers are leaves,
   not section-like — but it can be part of a Full template).
5. Export the parent Full template → import → divider attrs round-trip.

## Files

- `config.php` — page-builder config (Content Elements tab, "Divider"
  title, small popup)
- `options.php` — 5 tabs (Content, Layout, Styling, Animations, Advanced)
- `static.php` — frontend CSS enqueue (`static/css/styles.css`)
- `views/view.php` — frontend HTML (single `<div>` with optional inner
  `<span>`)
- `static/css/styles.css` — line / space / ornament / shadow styles
- `static/img/page_builder.png` — Layout Elements thumbnail icon

No JS, no admin-side asset, no item class — minimal leaf-shortcode layout.
