---
type: shortcode
name: highlight_text
since: shortcodes 1.6.91
provides: leaf-shortcode
---

# Highlight Text

A short text styled with a typographic effect: marker highlight, gradient fill,
underline, outline, glow, or a drop-cap. **Content Elements** tab.

## Options (atts)
- **Content**: `text` (safe inline HTML), `tag` (`h1`–`h6`/`p`/`span`/`div`; use
  Paragraph for drop-cap).
- **Design**: `fx` effect (`marker|gradient|underline|outline|glow|dropcap`),
  `align`.
- **Styling**: `text_color` (→ `--hl-text`), `accent_color` (→ `--hl-accent`,
  marker/underline/glow/drop-cap), `accent2_color` (→ `--hl-accent2`, gradient
  end / outline stroke), `font_size_preset`, `spacing`.

## Rendering
`view.php` (`sc_hl_render`) renders the chosen tag (the wrapper) with
`fw-hl--fx-*` and a `.fw-hl__text` span carrying the effect: marker/underline use
`background-size` swipes with `box-decoration-break: clone` (wrap-safe); gradient
uses `background-clip: text` (solid fallback via `@supports`); outline uses
`-webkit-text-stroke`; glow uses `text-shadow`; dropcap uses `::first-letter`. The
text allows a safe inline subset via `wp_kses`.

## Files
`config.php`, `options.php`, `static.php`, `views/view.php`,
`views/parts/registry.php`, `static/css/styles.css`,
`static/img/page_builder.svg`, `static/img/design/<key>.svg`.
