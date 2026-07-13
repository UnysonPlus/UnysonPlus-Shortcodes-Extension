---
type: shortcode
name: notification
since: original Unyson (extended in Unyson+ — Bootstrap 5 types, dismissible/auto-dismiss, icon-v2 picker, accent-border, announcement bar / floating toast)
provides: leaf-shortcode
---

# Notification

A Bootstrap-style alert box. Eight color schemes (`primary` /
`secondary` / `success` / `info` / `warning` / `danger` / `light` /
`dark`), three border treatments (`filled` / `outline` / `accent-left`),
optional custom label, an `icon-v2` icon pick (font / Lucide SVG / emoji
/ upload), inline vs stacked layout, optional dismiss / auto-dismiss
behavior, and a `display_mode` that can pin the notice as a site-wide
announcement bar (top/bottom) or floating toast (with remembered
dismissal).

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
| `icon` | `icon-v2` (preview: medium, modal: medium) | — | Icon pick (font / Lucide SVG / emoji / upload; saved shape `{ type, 'icon-class', url }`). **Takes precedence over `custom_icon`.** Empty = per-type default FA icon |
| `custom_icon` | `hidden` (retired) | — | Legacy emoji / inline SVG string. Kept hidden so old values still render; used only as a fallback when `icon` is empty |
| `layout` | `select` (`inline` / `stacked`) | `inline` | `inline` = icon/label/message in one row; `stacked` = label above message |
| `dismissible` | `switch` | `false` | Show a close button (`<button class="alert__close">&times;</button>`) |
| `auto_dismiss` | `short-text` | `0` | Seconds before auto-close. `0` disables. **Only takes effect when `dismissible` is true** |
| `display_mode` | `select` (`inline` / `bar-top` / `bar-bottom` / `floating`) | `inline` | Non-inline modes pin the notice to the viewport (announcement bar or floating toast) and force a close button. Place ONE bar per page |
| `persist_dismiss` | `switch` (`yes` / `no`) | `no` | Only for pinned modes — remember the visitor's dismissal (localStorage). Keyed to message/label/mode, so editing the text re-shows it |

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

`views/view.php` outputs `<div class="alert alert-{type} …" role="alert">`
via `sc_build_wrapper_attr()` (base class `alert`, id prefix `al-`).
Modifier classes are only added when they differ from the old defaults:
`alert--stacked` (stacked layout), `alert--border-{outline|accent-left}`,
and for pinned modes `alert--pinned` + `alert--{bar-top|bar-bottom|floating}`.
`dismissible` adds `alert-dismissible fade show` and a
`<button class="alert__close">&times;</button>`.

Icon priority (helper `sc_notification_render_icon`): **`icon` (icon-v2
picked, via `sc_icon_render`) > `custom_icon` (legacy emoji/SVG, via
`sc_icon_custom_markup`) > per-type default Font Awesome icon**. The new
wrapped-icon DOM (`<span class="alert__icon">…</span>`) is used only when
`icon` or `custom_icon` is set OR the layout is `stacked`; otherwise the
legacy bare `<i class="… alert-icon">` DOM is emitted for byte-identical
output on old saved notifications.

Styling-tab per-element colors (`label_color` / `message_color` /
`icon_color`) are resolved with `sc_extract_styling_atts()` into preset
classes + inline hex styles applied to the label `<strong>`, the message
`<span>`/`.alert__message`, and the `.alert__icon` wrapper. `auto_dismiss`
emits a `data-auto-dismiss` attr; `persist_dismiss` emits a stable
`data-persist-key` (md5 of message/label/mode).

`static/js/notification.js` powers the dismiss behavior (close-button
click, the custom auto-dismiss timer, and the persist-key localStorage
memory for pinned modes).

## Pitfalls

1. **`message` is `textarea`, but accepts HTML** — the view renders it
   through `wp_kses_post()`. Generators can produce multi-line /
   `<strong>` / `<a>` markup, but disallowed tags are stripped.
2. **`icon` (icon-v2) OVERRIDES `custom_icon`, not the reverse** — the
   picked icon wins; `custom_icon` is a retired hidden field that only
   applies as a fallback when `icon` is empty (opposite of `[icon-box]`'s
   old precedence).
3. **`auto_dismiss` requires `dismissible`** — setting `auto_dismiss: 5`
   alone does nothing; both must be on.
4. **`type` drives BOTH color AND default label/icon** — when
   `label_text` and `icon` are empty, the view picks sensible defaults
   per type. Default labels: `success`→"Success!", `info`→"Information!",
   `warning`→"Warning!", `danger`→"Error!", all others→"Note!" (all
   filterable via `sc_notification_default_labels` /
   `sc_notification_default_icons`). Override either to get a custom
   label/icon.
5. **`border_style: accent-left` is a custom variant** — not a standard
   Bootstrap utility; renders a 4px colored left border. CSS lives in
   `static/css/styles.css`.
6. **Non-inline `display_mode` forces `dismissible`** — bar/floating
   modes always get a close button regardless of the `dismissible`
   switch. `persist_dismiss` only has effect in these pinned modes.

## Verification

1. Drag Notification → renders as an info-blue filled alert with
   "Message!" content.
2. Switch `type: warning` → yellow Bootstrap warning alert.
3. Switch `border_style: outline` → transparent bg, colored border.
4. Switch `border_style: accent-left` → only a left-side colored bar.
5. Set `dismissible: true` → close button appears.
6. Set `dismissible: true`, `auto_dismiss: 3` → alert auto-closes after
   3 seconds.
7. Pick an `icon` (emoji/SVG/font) → it replaces the default per-type icon.
8. Switch `layout: stacked` → label moves to its own line above the
   message.
9. Set `display_mode: bar-top` → notice pins to the top of the viewport
   with a forced close button; `persist_dismiss: yes` keeps it hidden on
   later visits after being closed.

## Files

- `config.php`, `options.php`, `static.php`, `views/view.php`
- `static/js/notification.js` — dismiss + auto-dismiss timer + persist memory
- `static/css/styles.css` (via static.php) — accent-left, icon wrapper,
  and pinned bar / floating toast styles
- `static/img/page_builder.svg` — Content Elements thumbnail

Standard leaf layout. The only JS is for dismiss timing.
