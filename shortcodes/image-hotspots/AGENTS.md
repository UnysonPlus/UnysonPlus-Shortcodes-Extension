---
type: shortcode
name: image_hotspots
since: shortcodes 1.6.85
provides: leaf-shortcode
---

# Image Hotspots

An image with interactive pins; each pin reveals a tooltip card (title, text,
link). Four pin styles. **Media Elements** tab.

## Options (atts)
- **Content**: `image` (upload), `hotspots` (`addable-popup`). Per hotspot: `x`,
  `y` (sliders 0–100 %), `icon` (icon-v2, for the icon design), `title`, `text`,
  `link_label`, `link_url`, `link_target`.
- **Design**: `design` pin style (`pulse|dot|numbered|icon`), `trigger`
  (`hover|click`), `pin_size` (sm/md/lg), `rounded`.
- **Styling**: `pin_color`/`pop_bg`/`pop_color`/`accent_color` (custom hex →
  `--hs-pin` / `--hs-pop-bg` / `--hs-pop-color` / `--hs-accent`),
  `font_size_preset`, `spacing`.

## Rendering
`view.php` (`sc_hs_render`) emits `.fw-hs[--design / hover|click / --pin-*]` →
`.fw-hs__stage` (image) + a `.fw-hs__point` per hotspot positioned with inline
`left:x%;top:y%`. Each point has a `.fw-hs__pin` (number = 1-based index for the
numbered design; icon/+ for the icon design; bare for dot/pulse) and a
`.fw-hs__pop` tooltip card shown on `:hover`/`:focus-within` (hover) or `.is-open`
(click, via `scripts.js`). The tooltip sits above the pin and `scripts.js` adds
`.fw-hs--flip` to drop it below when it would clip the top of the viewport. Pulse
design animates `.fw-hs__ring` (reduced-motion gated).

## Files
`config.php`, `options.php`, `static.php`, `views/view.php`,
`views/parts/registry.php`, `static/css/styles.css`, `static/js/scripts.js`,
`static/img/page_builder.svg`, `static/img/design/<key>.svg`.
