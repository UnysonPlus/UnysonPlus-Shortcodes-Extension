---
type: shortcode
name: call-to-action
since: original Unyson
provides: leaf-shortcode
---

# Call To Action

A title + rich-text message + button combo. Lighter than `[posts]` or
`[icon-box]` — just a focused promotional block.

## Registration

No custom class file — leaf shortcode auto-instantiated. No item class.

`config.php` declares a `title_template` that previews the title, a
trimmed message, and the button label on the canvas.

## Options schema (atts)

Source of truth: `options.php`. Three tabs + Animations + Advanced.

### Tab: Content

Wrapped in `group_content` (flattens).

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `title` | `text` | — | Headline above the message (can be blank) |
| `message` | `wp-editor` | — | Body content (TinyMCE, shortcodes enabled, `wpautop: true`, 425px editor) |
| `button_label` | `text` | `Click` | Button text |
| `button_link` | `text` | `#` | Button href |
| `button_target` | `switch` (`_blank` / `_self`) | `_self` | New window or same |

### Tab: Styling

Wrapped in `group_colors` + `group_spacings` (both flatten).

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `bg_color` | `sc_color_field_compact` (bg) | — | Wrapper background |
| `font_size_preset` | `sc_font_size_field` | — | Named size from theme presets |
| `title_color` | `sc_color_field_compact` | — | Title color |
| `message_color` | `sc_color_field_compact` | — | Message color. **Key is `message_color` for back-compat** with saved instances; the editor label is "Content Color" |
| `spacing` | `sc_spacing_field` | — | Wrapper margin/padding |

### Tabs: Animations + Advanced

Standard.

## Rendering

`views/view.php` outputs a wrapper containing the title, message HTML
(rendered with `wpautop`/shortcodes), and a button anchor. Layout is
typically two-column flex (message left, button right) — check the view
for canonical structure.

## Pitfalls

1. **`message` is HTML, not plain text** — generators producing this att
   should emit valid HTML (`<p>`, `<a>`, etc.). The view renders it raw.
2. **`message_color` key is legacy** — the user-facing label is "Content
   Color" but the att key stayed `message_color` for back-compat. Don't
   rename it.
3. **No button-style control** — the button uses a fixed theme style
   (typically primary). For more control, drag a separate `[button]`
   shortcode adjacent to a `[special-heading]` instead.

## Verification

1. Drag Call To Action → renders the placeholder text + a "Click" button.
2. Edit → set title, write message, change button label → reload.
3. Set `button_target: _blank` → click opens in new tab.
4. Set styling colors → text + bg colors apply.

## Files

- `config.php`, `options.php`, `static.php`, `views/view.php`
- `static/css/styles.css` (via static.php)
- `static/img/page_builder.png` — Layout Elements thumbnail

No JS, no item class — minimal leaf layout.
