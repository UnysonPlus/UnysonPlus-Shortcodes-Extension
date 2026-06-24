---
type: shortcode
name: social_share
since: shortcodes 1.6.82
provides: leaf-shortcode
---

# Social Share

Share-to buttons (Facebook, X/Twitter, LinkedIn, Pinterest, WhatsApp, Telegram,
Reddit, Email, Copy link) in five styles × three shapes × three sizes. Lives in
**Content Elements**. Distinct from `social-icons` (which are profile links).

Leaf shortcode. Registry-driven Style picker + per-design CSS gating.

## Sources of truth
- `views/parts/networks.php` — the network catalog: `key => { label, color, icon
  (inline SVG, currentColor), url (sprintf %1$s=url, %2$s=title, '' for copy),
  window (popup?) }`. Both options.php (multi-select choices) and view.php read it.
- `views/parts/registry.php` — button-style designs: `brand`, `mono`, `outline`,
  `soft`, `text`.

## Options (atts)
- **Content**: `title` (heading), `networks` (`multi-select`, ordered),
  `share_source` (`current`/`custom`), `custom_url`, `share_text` (blank = page
  title).
- **Design**: `design` (style), `shape` (`circle|rounded|square`), `size`
  (`sm|md|lg`), `show_label` (switch; forced for `text`), `layout`
  (`inline|stacked`), `align`.
- **Styling**: `custom_color` (one color for all buttons, → per-button
  `--ss-color`), `icon_color` (→ `--ss-icon`, for minimal/outline),
  `font_size_preset`, `spacing`.
- **Animations + Advanced**: standard.

## Rendering
`view.php` (`sc_ss_render`) resolves the share URL (`get_permalink()` →
REQUEST_URI fallback, or the custom URL) and title (share_text → post title →
site name), URL-encodes both, then for each selected network (saved order) emits
`<a class="fw-ss__btn fw-ss__btn--<key>" style="--ss-color:<brand>" …>` with the
icon (+ optional label). The brand color rides in `--ss-color`; each design uses
it as background/border/text. `data-ss-window` networks open via `scripts.js`
(centered popup; modified clicks pass through); `data-ss-copy` (Copy link) uses
the Clipboard API with a textarea fallback + a "Copied!" flash. Email is a plain
`mailto:`. The `soft` design uses `color-mix()` with an rgba fallback.

## Pitfalls
1. `networks` is a `multi-select` (array of keys); render order = saved order.
   Unknown keys are filtered out.
2. The WhatsApp URL template uses `%%20` so it survives `sprintf` as a literal
   `%20` between text and URL.
3. Share URL defaults to the CURRENT page — preview/editor may show the editor
   URL; it resolves correctly on the front-end.

## Files
`config.php`, `options.php`, `static.php`, `views/view.php`,
`views/parts/{registry,networks}.php`, `static/css/styles.css`,
`static/js/scripts.js`, `static/img/page_builder.svg`,
`static/img/design/<key>.svg`.
