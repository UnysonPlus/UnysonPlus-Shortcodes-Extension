---
type: shortcode
name: video_popup
since: shortcodes 1.6.83
provides: leaf-shortcode
---

# Video Popup

A poster image with a play button that opens a YouTube / Vimeo / self-hosted
video in a lightbox. Five play-button designs. **Media Elements** tab.

Leaf shortcode. Registry-driven design picker + per-design CSS gating.

## Options (atts)
- **Content**: `poster` (upload), `video_url` (YouTube/Vimeo page URL or direct
  .mp4/.webm), `play_label` (optional text beside the button), `caption`
  (a11y label).
- **Design**: `design` (`classic`/`pulse`/`outline`/`soft`/`minimal`), `ratio`
  (`original`/`ratio-16-9`/`4-3`/`1-1`/`21-9`), `play_size` (sm/md/lg),
  `rounded`, `overlay` (darken poster), `hover_zoom`.
- **Styling**: `accent_color` (→ `--vp-accent`), `icon_color` (`--vp-icon`),
  `overlay_color` (`--vp-overlay`), `label_color` (`--vp-label`),
  `font_size_preset`, `spacing`.
- **Animations + Advanced**: standard.

## Rendering
`view.php` (`sc_vp_render`) parses the URL → `[type, src]` (`sc_vp_parse`:
youtube id / vimeo id / file url) and emits a `<button class="fw-vp__trigger"
data-vp-type data-vp-src>` containing the poster + the play button. `scripts.js`
opens one shared `.fw-vp-lb` lightbox, injecting an autoplay `<iframe>`
(YouTube/Vimeo) or `<video controls autoplay>` (file); Esc / backdrop / × close
it and the media is cleared on close (stops playback). The `pulse` design
animates rings (`--vp` pulse keyframes), gated by `prefers-reduced-motion`.

## Files
`config.php`, `options.php`, `static.php`, `views/view.php`,
`views/parts/registry.php`, `static/css/styles.css`, `static/js/scripts.js`,
`static/img/page_builder.svg`, `static/img/design/<key>.svg`.
