---
type: shortcode
name: icon
since: original Unyson
provides: leaf-shortcode
---

# Icon

A single icon (from the icon-v2 picker) with optional title text underneath.
The simplest visual content element. For an icon with title AND body
content, use `[icon-box]` instead.

## Registration

No custom class file — leaf shortcode auto-instantiated. No item class.

`config.php` has no custom `title_template` — page-builder uses the
shortcode title and a default summary.

## Options schema (atts)

Source of truth: `options.php`. Two tabs + Animations + Advanced.

### Tab: Content

Wrapped in `group_content` (flattens).

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `icon` | `icon-v2` (modal: medium) | — | The icon, picked from the v2 icon library. Saved shape is `{ type: 'icon-font'\|'custom-upload', 'icon-class': 'fas fa-star', url: '…' }` |
| `title` | `text` | — | Optional title text below the icon |

### Tab: Styling

Wrapped in `group_colors` + `group_spacings` (both flatten).

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `bg_color` | `sc_color_field_compact` (bg) | — | Wrapper background |
| `title_color` | `sc_color_field_compact` | — | Title text color |
| `icon_color` | `sc_color_field_compact` | — | Icon glyph color (font icons only — custom uploads use their own image colors) |
| `spacing` | `sc_spacing_field` | — | Wrapper margin/padding |

### Tabs: Animations + Advanced

Standard.

## Rendering

`views/view.php` outputs a wrapper with the icon (either an `<i>` element
with the picked icon class, or an `<img>` for custom-upload icons), plus
the title text below if set.

## Pitfalls

1. **`icon` is a structured value, not a class string** — the icon-v2
   picker saves `{ type, 'icon-class', url }`, not just `'fas fa-star'`.
   AI generators must produce the full object shape.
2. **`icon_color` only applies to font icons** — custom-uploaded image
   icons render as `<img>` and ignore color CSS. Use a same-colored
   variant of the image instead.

## Verification

1. Drag Icon → opens icon-v2 picker. Pick one, save.
2. Reload → icon renders.
3. Add a title → renders below the icon.
4. Apply `icon_color` → font icon recolors. Try with a custom upload →
   color is ignored, image stays as-is.

## Files

- `config.php`, `options.php`, `static.php`, `views/view.php`
- `static/css/styles.css` (via static.php)
- `static/img/page_builder.png` — Layout Elements thumbnail

No JS, no item class — minimal leaf layout.
