---
type: shortcode
name: lottie
since: shortcodes 1.7.54
provides: leaf-shortcode
---

# Lottie Animation

Plays an animated Lottie vector (`.json`) via the bundled `lottie-web` (SVG/light
build). **Media Elements** tab.

## Options (atts)
- **Content**: `source` (`url|upload`), `lottie_url` (text), `lottie_file`
  (`upload`, `files_ext=json`).
- **Design**: `trigger` (`autoplay|viewport|hover|click`), `loop`,
  `reverse_hover` (rewind on hover-out), `speed` (0.25–2.5), `direction`
  (`forward|reverse`); layout: `max_width` (px → `--lt-max`), `alignment`.
- **Styling**: `spacing`.

## Rendering
`view.php` (`sc_lottie_render`) resolves the source (upload URL → `lottie_url`)
and emits `.fw-lottie[data-lottie]` carrying `data-src` / `data-trigger` /
`data-loop` / `data-speed` / `data-direction` / `data-reverse-hover`, wrapping an
empty `.fw-lottie__canvas`. `scripts.js` calls `lottie.loadAnimation` per element
(renderer `svg`), applies speed/direction on `DOMLoaded`, then wires the trigger:
autoplay; viewport (IntersectionObserver, plays on enter); hover (play on enter,
pause or rewind on leave); click (toggle play/pause).

## Library
`static/js/vendor/lottie_light.min.js` (lottie-web 5.12.2, SVG renderer only, ~168
KB) is **vendored** and enqueued only when the shortcode is on a page (handle
`lottie-web`). The source is filterable via `fw_shortcode_lottie_library_src` (swap
in a CDN or the full build if the canvas renderer is needed).

## Pitfalls
1. The file must be a **Bodymovin/Lottie `.json`** (not `.lottie` or GIF).
2. Light build is **SVG-only** — no canvas renderer.
3. `reverse_hover` only applies to the **hover** trigger.

## Files
`config.php`, `options.php`, `static.php`, `views/view.php`,
`static/css/styles.css`, `static/js/scripts.js`,
`static/js/vendor/lottie_light.min.js`, `static/img/page_builder.svg`.
