---
type: shortcode
name: post_carousel
since: shortcodes 1.6.93
provides: leaf-shortcode
---

# Post Carousel

A Splide slider of posts (any post type) — featured image, title, excerpt, date,
meta and read-more — in three card designs. **Content Elements** tab.

## Options (atts)
- **Query**: `post_type` (public types, built in admin), `taxonomy` + `terms`
  (comma slugs), `number` (≤40), `orderby`, `order`.
- **Design**: `design` card (`standard|overlay|minimal`), `image_ratio`,
  `show_excerpt` + `excerpt_length`, `show_date`, `show_meta` (author/category),
  `readmore`. Carousel: `per_view` (1–4), `gap` (Gap-Scale slug), `autoplay`,
  `loop`, `arrows`, `dots`.
- **Styling**: `accent_color`/`card_bg`/`title_color`/`text_color` (custom hex →
  `--pc-*`), `font_size_preset`, `spacing`.

## Rendering
`view.php` (`sc_pc_render`) runs a `WP_Query` (with `wp_reset_postdata()`), builds
a `.splide.fw-pc__carousel[data-splide]` of `.fw-pc__card` slides. Standard =
image-top card; overlay = absolute image + scrim with the text at the bottom (no
excerpt); minimal = no image, accent left rail. Arrows/dots/loop auto-disable when
posts ≤ per_view. Splide is the vendored copy (carousel shortcode); `scripts.js`
mounts it. Gap via `var(--gap-<slug>)`.

## Files
`config.php`, `options.php`, `static.php`, `views/view.php`,
`views/parts/registry.php`, `static/css/styles.css`, `static/js/scripts.js`,
`static/img/page_builder.svg`, `static/img/design/<key>.svg`.
