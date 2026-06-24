---
type: shortcode
name: steps
since: shortcodes 1.7.45
provides: leaf-shortcode
---

# Steps / Process

A numbered steps / process flow in five designs. **Components** tab.

## Options (atts)
- **Content**: `steps` (`addable-popup`) — per step `title`, `content`
  (`textarea`, `do_shortcode`+`wpautop`), `icon` (`icon-v2`), `number`
  (label override, defaults to position).
- **Design**: `design` (`horizontal|vertical|alternating|cards|circles`),
  `marker` (`number|icon|none`), `marker_shape` (`circle|rounded|square`),
  `connector` (`solid|dashed|none`), `title_tag`.
- **Styling**: `accent_color`/`marker_text_color`/`title_color`/`text_color`
  (custom hex → `--st-accent` / `--st-marker-text` / `--st-title` / `--st-text`),
  `font_size_preset`, `spacing`.

## Rendering
`view.php` (`sc_steps_render`) emits an `<ol.fw-steps>` (`fw-steps--design-*`,
`--marker-*`, `--shape-*`, `--connector-*`) with one `<li.fw-steps__item>` per
step: a `.fw-steps__connector` (CSS line, hidden on the first/last as needed), a
`.fw-steps__marker` (number or `icon-v2`), and a `.fw-steps__body`
(title + text). Layout is **pure CSS** per design — Horizontal = flex row with a
connector behind markers; Vertical = a left timeline; Alternating = a centre line
with items zig-zagging; Cards = a flex row of cards; Circles = big gradient
number circles. No JS. Markers carry the step number from PHP (so the `number`
override works); icon mode uses `sc_steps_icon` (`icon-font` `<i>` /
`custom-upload` `<img>`). Collapses to a single vertical column under 720px.

## Pitfalls
1. `connector` only affects Horizontal / Vertical / Alternating (Cards/Circles
   have no line).
2. The Circles gradient uses `color-mix`; an `@supports` fallback keeps a solid
   accent on old browsers.

## Files
`config.php`, `options.php`, `static.php`, `views/view.php`,
`views/parts/registry.php`, `static/css/styles.css`,
`static/img/page_builder.svg`, `static/img/design/<key>.svg`.
