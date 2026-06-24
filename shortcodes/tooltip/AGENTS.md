---
type: shortcode
name: tooltip
since: shortcodes 1.6.84
provides: leaf-shortcode
---

# Tooltip

An inline trigger (text / button / icon) that reveals a positioned tooltip on
hover-focus or click. Four positions (auto-flips on overflow), four themes.
**Content Elements** tab.

## Options (atts)
- **Content**: `trigger_type` (`text|button|icon`), `trigger_text`,
  `trigger_icon` (icon-v2; `?` fallback), `tip_title`, `tip_content` (basic HTML
  via `wp_kses_post`).
- **Design**: `design` theme (`dark|light|accent|gradient`), `position`
  (`top|right|bottom|left`), `event` (`hover|click`), `arrow` (switch),
  `max_width`.
- **Styling**: `tip_bg`/`tip_color`/`accent_color` (custom hex → `--tt-bg` /
  `--tt-color` / `--tt-accent`), `font_size_preset`, `spacing`.

## Rendering
`view.php` (`sc_tt_render`) emits `<span class="fw-tt fw-tt--pos-* fw-tt--hover|click
fw-tt--trig-*">` with a `<button class="fw-tt__trigger">` + `<span class="fw-tt__bubble"
role="tooltip" id>` (the trigger gets `aria-describedby`). The bubble is positioned
with CSS per `--pos-*` and shown on `:hover` / `:focus-within` (hover) or
`.is-open` (click, via `scripts.js`). `scripts.js` toggles click tooltips (closes
others, outside-click + Esc close) and flips the position class once if the bubble
would overflow the viewport. Show/hide transforms come from `--tt-hide` / `--tt-show`
set per position.

## Files
`config.php`, `options.php`, `static.php`, `views/view.php`,
`views/parts/registry.php`, `static/css/styles.css`, `static/js/scripts.js`,
`static/img/page_builder.svg`, `static/img/design/<key>.svg`.
