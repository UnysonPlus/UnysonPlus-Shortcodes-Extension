---
type: shortcode
name: code-block
since: original Unyson
provides: leaf-shortcode
---

# Code Block

A raw HTML / CSS / JavaScript embed. Renders whatever code the user puts
in verbatim. Useful for one-off embeds (3rd-party widgets, custom forms,
inline scripts) without leaving the page builder.

## Registration

No custom class file — leaf shortcode auto-instantiated. No item class.

`config.php` declares a minimal `title_template` that previews the code
content on the canvas.

## Options schema (atts)

Source of truth: `options.php`. Two tabs + Animations + Advanced.

### Tab: Content

Wrapped in `group_content` (flattens).

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `code` | `code-editor` (mode: `htmlmixed`, height: 500) | — | The HTML/CSS/JS to render verbatim. Syntax-highlighted in the modal |

### Tab: Styling

Wrapped in `group_colors` + `group_spacings` (both flatten).

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `text_color` | `sc_color_field_compact` (text) | — | Wrapper text color (rarely needed — embedded code typically styles itself) |
| `bg_color` | `sc_color_field_compact` (bg) | — | Wrapper background |
| `font_size_preset` | `sc_font_size_field` | — | Named size from theme presets |
| `spacing` | `sc_spacing_field` | — | Wrapper margin/padding |

### Tabs: Animations + Advanced

Standard.

## Rendering

`views/view.php` outputs the `code` value as raw HTML inside the wrapper.
`wpautop` is bypassed for this shortcode (otherwise `<script>` blocks
would get paragraph-wrapped).

## Pitfalls

1. **Code is rendered raw, no sanitization** — the WP user role gating
   protects against non-editors injecting code, but the editor IS trusted
   to write working markup. AI generators should produce well-formed
   HTML; broken markup breaks the page.
2. **`<script>` tags execute on the page** — anything in the code editor
   that's executable JS runs in the visitor's browser. Powerful but
   dangerous; consider an `iframe` sandbox for 3rd-party widgets.
3. **No shortcode nesting** — square-bracket shortcodes inside the code
   editor are NOT executed (the value is passed through raw). To nest a
   shortcode visually, drag it as a separate page-builder item.

## Verification

1. Drag Code Block → empty wrapper renders.
2. Paste HTML (e.g. `<div style="background:red">Hi</div>`) → reload →
   red box appears.
3. Paste a `<script>alert('hi')</script>` → reload → alert fires (browser
   permitting).
4. Save as part of Full template → export → import → code round-trips.

## Files

- `config.php`, `options.php`, `static.php`, `views/view.php`
- `static/css/styles.css` (via static.php)
- `static/img/page_builder.png` — Layout Elements thumbnail

No JS (apart from the user's own embedded code), no item class — minimal
leaf layout.
