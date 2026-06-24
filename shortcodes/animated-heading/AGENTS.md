---
type: shortcode
name: animated_heading
since: shortcodes 1.6.86
provides: leaf-shortcode
---

# Animated Heading

A heading with a static before/after and a rotating set of words, animated with
one of six effects (typewriter / fade / slide / flip / zoom / clip). **Content
Elements** tab.

## Options (atts)
- **Content**: `before_text`, `words` (textarea, one per line), `after_text`,
  `tag` (`h1`–`h6`/`p`/`div`).
- **Design**: `anim` (the registry effect), `speed` (`slow|normal|fast`),
  `highlight` (`none|color|underline|marker` for the rotating word), `align`.
- **Styling**: `text_color` (→ `--ah-text`), `accent_color` (→ `--ah-accent`,
  the highlight), `font_size_preset`, `spacing`.

## Rendering
`view.php` (`sc_ah_render`) renders the chosen tag (the wrapper IS the heading)
with `data-ah-words` (JSON) and `fw-ah--anim-* / --speed-* / --hl-*` classes,
containing optional `.fw-ah__static` before/after and a `.fw-ah__rotate >
.fw-ah__word` (+ a caret used only by typewriter). `scripts.js` rotates the word:
typewriter types/deletes; the others swap the text and retrigger a per-design CSS
enter `@keyframes` by toggling `.run`. Speed maps to hold/type/delete timings;
`prefers-reduced-motion` falls back to a plain word swap (and a static typewriter
caret). Single-word lists don't rotate.

## Files
`config.php`, `options.php`, `static.php`, `views/view.php`,
`views/parts/registry.php`, `static/css/styles.css`, `static/js/scripts.js`,
`static/img/page_builder.svg`, `static/img/design/<key>.svg`.
