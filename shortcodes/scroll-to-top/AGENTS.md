---
type: shortcode
name: scroll_to_top
since: shortcodes 1.7.47
provides: leaf-shortcode
---

# Scroll to Top & Progress

A back-to-top button and/or a reading-progress bar, both tied to page scroll.
**Interactive Elements** tab. Place **once per page**.

## Options (atts)
- **General**: `show_button` (switch), `show_progress` (switch); button group:
  `icon` (`icon-v2`, up-arrow fallback), `position` (`bottom-right|bottom-left`),
  `shape` (`circle|rounded|square`), `show_after` (px); progress group:
  `progress_position` (`top|bottom`), `progress_height` (px).
- **Styling**: `accent_color` (→ `--stt-accent`, button bg + bar fill),
  `icon_color` (→ `--stt-icon`), `button_size` (`sm|md|lg` → `--stt-size`).

## Rendering
`view.php` (`sc_stt_render`) emits a `.fw-stt[data-stt]` wrapper holding a
`position:fixed` `.fw-stt__progress > .fw-stt__progress-fill` and/or a fixed
`.fw-stt__btn[data-stt-top]`. `progress_height` → `--stt-prog-h`; `show_after`
→ `data-after`. `scripts.js` runs ONE shared scroll handler (rAF-throttled,
passive) across every instance: sets the fill width to
`scrollTop / (scrollHeight - innerHeight)` and toggles `.is-visible` on the
wrapper once scrolled past `data-after`; the button smooth-scrolls to top.

## Pitfalls
1. The button is **hidden until scrolled past `show_after`** — in the builder
   canvas it will look empty; that's expected.
2. Intended as a single per-page instance; multiple instances each work but their
   fixed elements overlap.

## Files
`config.php`, `options.php`, `static.php`, `views/view.php`,
`static/css/styles.css`, `static/js/scripts.js`, `static/img/page_builder.svg`.
