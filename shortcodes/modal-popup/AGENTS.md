---
type: shortcode
name: modal_popup
since: shortcodes 1.6.87
provides: leaf-shortcode
---

# Modal / Popup

A trigger (button / text / icon / image) that opens custom content in a modal —
centered card, side drawer (left/right) or fullscreen. **Content Elements** tab.

## Options (atts)
- **Content**: `trigger_type` (`button|text|icon|image`), `trigger_label`,
  `trigger_icon` (icon-v2), `trigger_image` (upload), `modal_title`,
  `modal_content` (HTML via `wp_kses_post` + `wpautop`).
- **Design**: `design` (`center|drawer-right|drawer-left|fullscreen`), `size`
  (sm/md/lg — centered only), `animation` (`fade|zoom|slide`), `open_on_load`
  (+ `open_delay` ms), `close_overlay`.
- **Styling**: `accent_color` (trigger → `--mp-accent`), `overlay_color`
  (`--mp-overlay`), `modal_bg` (`--mp-bg`), `modal_color` (`--mp-color`),
  `font_size_preset`, `spacing` (applies to the trigger wrapper).

## Rendering
`view.php` (`sc_mp_render`) emits `.fw-mp` with a `<button class="fw-mp__trigger"
aria-controls=id>` and a `.fw-mp__overlay#id[role=dialog]` (design/size/anim
classes + `data-mp-close-overlay` / `data-mp-onload` / `data-mp-delay`).
`scripts.js` **moves each overlay to `<body>`** on init (so `position:fixed`
isn't clipped by a transformed/animated ancestor), then opens on trigger click
(or after the delay when `open_on_load`), with Esc / close-button / overlay-click
(if enabled) close, body-scroll lock, a focus trap, and focus return to the
trigger. The dialog's resting transform per `--anim-*` / drawer is cleared by
`.is-open`. Color vars on the overlay are duplicated there (it lives in body, away
from the trigger wrapper). Reduced-motion neutralizes the transforms.

## Pitfalls
1. The overlay is reparented to `<body>` — its color CSS vars are set on the
   overlay element itself (not inherited from the `.fw-mp` wrapper).
2. `modal_content` is sanitized with `wp_kses_post`; it holds HTML, not nested
   shortcodes.

## Files
`config.php`, `options.php`, `static.php`, `views/view.php`,
`views/parts/registry.php`, `static/css/styles.css`, `static/js/scripts.js`,
`static/img/page_builder.svg`, `static/img/design/<key>.svg`.
