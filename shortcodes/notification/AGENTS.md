---
type: shortcode
name: notification
since: original Unyson (extended in Unyson+ — Bootstrap 5 types, dismissible/auto-dismiss, custom icons, accent-border)
provides: leaf-shortcode
---

# Notification

A Bootstrap-style alert box. Eight color schemes (`primary` /
`secondary` / `success` / `info` / `warning` / `danger` / `light` /
`dark`), three border treatments (`filled` / `outline` / `accent-left`),
optional custom label, custom icon (library or emoji/SVG), inline vs
stacked layout, and optional dismiss / auto-dismiss behavior.

## Registration

No custom class file — leaf shortcode auto-instantiated. No item class.

`config.php` declares a `title_template` that previews `LABEL: message`
on the canvas (uppercase label).

## Options schema (atts)

Source of truth: `options.php`. Two tabs + Animations + Advanced.

### Tab: Content

**Not** wrapped in a group — options are flat top-level keys directly.

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `message` | `textarea` | `Message!` | Main alert body. Supports multi-line / HTML |
| `label_text` | `text` | — | Custom prefix label. Empty = use default for the type (e.g. "Success!", "Warning!") |
| `type` | `select` (`primary` / `secondary` / `success` / `info` / `warning` / `danger` / `light` / `dark`) | `info` | Color scheme + default label + default icon |
| `border_style` | `select` (`filled` / `outline` / `accent-left`) | `filled` | Visual treatment of the box border |
| `icon` | `icon-v2` (modal: medium) | — | Library icon (saved shape `{ type, 'icon-class', url }`). **Ignored if `custom_icon` below is filled** |
| `custom_icon` | `text` | — | Emoji or inline SVG. Overrides `icon` |
| `layout` | `select` (`inline` / `stacked`) | `inline` | `inline` = icon/label/message in one row; `stacked` = label above message |
| `dismissible` | `switch` | `false` | Show a close button (Bootstrap `.btn-close`) |
| `auto_dismiss` | `short-text` | `0` | Seconds before auto-close. `0` disables. **Only takes effect when `dismissible` is true** |

### Tab: Styling

Wrapped in `group_colors` + `group_spacings` (both flatten).

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `text_color` | `sc_color_field_compact` (text) | — | Wrapper text color |
| `bg_color` | `sc_color_field_compact` (bg) | — | Wrapper background |
| `font_size_preset` | `sc_font_size_field` | — | Named size from theme presets |
| `label_color` | `sc_color_field_compact` | — | Label only — overrides `text_color` |
| `message_color` | `sc_color_field_compact` | — | Message body only — overrides `text_color` |
| `icon_color` | `sc_color_field_compact` | — | Icon glyph (font icons inherit by default; custom emoji ignores this) |
| `spacing` | `sc_spacing_field` | — | Wrapper margin/padding |

### Tabs: Animations + Advanced

Standard.

## Rendering

`views/view.php` outputs a Bootstrap `<div class="alert alert-{type} ...">`
with optional `.alert-dismissible` + close button, an icon `<i>` or
custom-icon span, a `.notification-label` for the prefix, and the message
HTML. Layout direction is CSS-flex (`inline` = `flex-row`, `stacked` =
`flex-column`).

`static/js/notification.js` powers the dismiss behavior (Bootstrap's
built-in close + the custom auto-dismiss timer).

## Pitfalls

1. **`message` is `textarea`, but accepts HTML** — the view renders it
   raw. Generators can produce multi-line / `<strong>` / `<a>` markup,
   but should escape user-provided strings if they're untrusted.
2. **`custom_icon` overrides `icon`** — same pattern as `[icon-box]`.
3. **`auto_dismiss` requires `dismissible`** — setting `auto_dismiss: 5`
   alone does nothing; both must be on.
4. **`type` drives BOTH color AND default label/icon** — when
   `label_text` and `icon` are empty, the view picks sensible defaults
   per type ("Success!" / check-icon for `success`, etc.). Override
   either to get a custom label/icon.
5. **`border_style: accent-left` is a custom variant** — not a standard
   Bootstrap utility; renders a 4px colored left border. CSS lives in
   `static/css/styles.css`.

## Verification

1. Drag Notification → renders as an info-blue filled alert with
   "Message!" content.
2. Switch `type: warning` → yellow Bootstrap warning alert.
3. Switch `border_style: outline` → transparent bg, colored border.
4. Switch `border_style: accent-left` → only a left-side colored bar.
5. Set `dismissible: true` → close button appears.
6. Set `dismissible: true`, `auto_dismiss: 3` → alert auto-closes after
   3 seconds.
7. Set `custom_icon: ⚠️` → emoji replaces the default warning icon.
8. Switch `layout: stacked` → label moves to its own line above the
   message.

## Files

- `config.php`, `options.php`, `static.php`, `views/view.php`
- `static/js/notification.js` — dismiss + auto-dismiss timer
- `static/css/styles.css` (via static.php) — accent-left + custom-icon
  styles
- `static/img/page_builder.png` — Layout Elements thumbnail

Standard leaf layout. The only JS is for dismiss timing.
