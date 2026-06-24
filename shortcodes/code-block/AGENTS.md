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
| `code` | `code-editor` (mode: `htmlmixed`, height: 500) | — | The HTML/CSS/JS. Output verbatim (runs) by default, or shown as code when `render_as_code` is ON. Syntax-highlighted in the modal |
| `render_as_code` | `switch` | `false` | When ON, the `code` is HTML-escaped + wrapped in a Prism-ready `<pre><code class="language-*">` so visitors **see** the markup instead of it rendering. The stored att stays raw HTML (editable), escaping happens at render time |
| `beautify` | `switch` | `true` | Auto re-indents the markup with clean tab spacing at render time (markup only). Normalizes minified/messy HTML; `<pre>`/`<script>`/`<style>`/`<svg>` bodies are protected from reflow. Turn OFF to keep the code exactly as typed. Only applies when `render_as_code` is ON |
| `code_language` | `select` (`auto`/`markup`/`css`/`javascript`/`php`/`json`/`bash`) | `auto` | Prism `language-*` class. **Auto-detect** sniffs the language from the code (markup/php/css/js/json). Only applies when `render_as_code` is ON |

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

When `render_as_code` is ON, the view (optionally) **beautifies** the markup,
then **escapes** the `code` (`htmlspecialchars`), encodes newlines as `&#10;`
(so wpautop/nl2br can't inject `<br>` inside the `<pre>`), and emits
`<pre class="language-<lang> code-block__pre"><code class="language-<lang>">…</code></pre>`
— Prism-ready. The markup also degrades gracefully to a plain monospaced
block if Prism isn't loaded. This is the supported way to **show** code to
visitors (docs / "here's the markup" examples): paste raw HTML, flip the
switch — no manual entity-escaping, and re-opening the modal shows editable
raw markup (not the entity soup that breaks if you hand-escape into `code`).

Render-time helpers (defined in `view.php`, `function_exists`-guarded):
- `sc_code_block_beautify_html()` — token-based HTML pretty-printer. Protects
  `<pre>/<textarea>/<script>/<style>/<svg>` from reflow, single-lines tag
  attributes, collapses inter-tag whitespace, then tab-indents via
  `sc_code_block_indent_html()` (block tags own a line; leaf tags p/li/h#/td/th/…
  keep inline content + closing tag on the same line; inline tags + SVG stay
  inline, preserving `viewBox` casing — no DOMDocument lowercasing).
- `sc_code_block_detect_language()` — heuristic sniffer for the `auto` choice
  (php if `<?php`, markup if it starts with `<`, then json / css / js).
Because beautification runs at RENDER time, the code view stays clean even
when a page is re-saved through the builder (which bakes raw `do_shortcode`
output) — the old "indent once in the importer" approach did not survive that.

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
