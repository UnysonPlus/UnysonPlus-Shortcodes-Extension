---
type: shortcode
name: text-block
since: original Unyson
provides: leaf-shortcode
---

# Text Block

The simplest content shortcode — a single rich-text editor (TinyMCE,
425px tall). Use this when nothing else fits: prose paragraphs, formatted
lists, embedded media via TinyMCE buttons.

## Registration

No custom class file — leaf shortcode auto-instantiated. No item class.
**No `static.php`** — no frontend asset enqueue.

`config.php` declares a `title_template` that previews the text content
on the canvas.

## Options schema (atts)

Source of truth: `options.php`. Two tabs + Animations + Advanced.

### Tab: Content

**Not** wrapped in a group — single flat field.

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `text` | `wp-editor` (425px, TinyMCE, shortcodes enabled, wpautop on) | `''` | Body content — rich text |

### Tab: Styling

Wrapped in `group_colors` + `group_spacings` (both flatten).

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `text_color` | `sc_color_field_compact` (text) | — | Wrapper text color |
| `bg_color` | `sc_color_field_compact` (bg) | — | Wrapper background |
| `font_size_preset` | `sc_font_size_field` | — | Named size from theme presets |
| `spacing` | `sc_spacing_field` | — | Wrapper margin/padding |

### Tabs: Animations + Advanced

Standard.

## Rendering

`views/view.php` outputs a wrapper containing the `text` HTML rendered
with `wpautop` (auto-paragraphs) and shortcodes processed (nested
shortcodes inside text run).

## Pitfalls

1. **Nested shortcodes work** — unlike `[code-block]` which renders raw,
   text-block's `wp-editor` content has `shortcodes: true`, so nested
   `[button]`, `[notification]`, etc. are processed. Generators can
   embed shortcode tags inside the `text` value.
2. **`wpautop` is on** — newlines become `<p>` / `<br>` automatically.
   Generators producing pre-formatted HTML should know that empty lines
   convert to paragraph breaks.

## Verification

1. Drag Text Block → modal opens with TinyMCE editor.
2. Type some text → reload → renders with auto-paragraphs.
3. Embed a `[button link="#" label="Click"]` shortcode in the editor →
   reload → button renders inline within the text.

## Files

- `config.php`, `options.php`, `views/view.php`

No `static.php`, no JS, no item class, no images dir — most minimal leaf
shortcode in the codebase.
