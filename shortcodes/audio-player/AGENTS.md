---
type: shortcode
name: audio_player
since: shortcodes 1.7.43
provides: leaf-shortcode
---

# Audio Player

A custom HTML5 audio player for self-hosted or remote tracks — single track or
playlist, with cover art — in four designs. **Media Elements** tab.

## Options (atts)
- **Content**: `tracks` (`addable-popup`). Per track: `audio` (upload),
  `audio_url` (URL fallback), `title`, `artist`, `cover` (upload).
- **Design**: `design` (`classic|card|minimal|playlist`), `autoplay` (browsers
  usually block sound-autoplay), `loop`, `show_volume`, `show_download`,
  `rounded`.
- **Styling**: `accent_color`/`bg_color`/`text_color` (custom hex → `--ap-accent`
  / `--ap-bg` / `--ap-text`), `font_size_preset`, `spacing`.

## Rendering
`view.php` (`sc_ap_render`) resolves each track (file upload URL → `audio_url`
fallback) and emits `.fw-ap[data-ap]` with a hidden `<audio>`, a `.fw-ap__player`
(cover + info + controls: play/pause, prev/next when a playlist, seek bar with
buffered+played, time, volume, download) and an `<ol.fw-ap__list>` of tracks
(the data source — shown by the Playlist design, hidden otherwise). `scripts.js`
is a self-contained controller: one `<audio>` per player, reads the track `<li>`s,
wires play/seek (pointer + keyboard)/volume/mute, advances on `ended` (next, or
restart if `loop`), and swaps cover/title/artist/download per track. Play/pause
and volume/mute icons toggle via `.is-playing` / `.is-muted`. Single-track + loop
uses the native `loop` attribute.

## Pitfalls
1. Each track needs an **audio file or URL**; tracks without one are skipped.
2. **Autoplay with sound is blocked** by most browsers until the user interacts —
   the option attempts it and silently no-ops if blocked.
3. Per-track durations in the list are intentionally **not pre-fetched** (would
   download every file); the duration shows for the current track once it loads.

## Files
`config.php`, `options.php`, `static.php`, `views/view.php`,
`views/parts/registry.php`, `static/css/styles.css`, `static/js/scripts.js`,
`static/img/page_builder.svg`, `static/img/design/<key>.svg`.
