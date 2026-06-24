---
type: shortcode
name: logo_grid
since: shortcodes 1.6.92
provides: leaf-shortcode
---

# Logo Grid

A grid / boxed grid / carousel / marquee of client / partner logos, with an
optional grayscale-to-color hover. **Content Elements** tab.

## Design system
`views/parts/registry.php` → `grid`, `boxed`, `carousel` (`splide => true`),
`marquee`. The Carousel design pulls in the vendored Splide + a tiny mount
(`static/js/carousel.js`) via the per-instance `static.php` hook; the others are
CSS-only.

## Options (atts)
- **Content**: `logos` (`addable-popup`): `image` (upload), `name` (alt/label),
  `link_url`, `link_target`.
- **Design**: `design` layout, `columns` (2–6; per-view for carousel), `gap`
  (Gap-Scale slug → `var(--gap-<slug>)`), `logo_height` (slider px → `--lg-h`),
  `grayscale` (switch), `autoplay` (carousel), `speed` (marquee/autoplay),
  `direction` (marquee).
- **Styling**: `box_bg` (boxed design → `--lg-box-bg`), `font_size_preset`,
  `spacing`.

## Rendering
`view.php` (`sc_lg_render`) filters logos with an image, then renders per design:
grid/boxed → `.fw-lg__grid` (CSS grid of `--lg-cols`); carousel → a Splide
`.fw-lg__carousel[data-splide]` (loop, autoplay, perPage = columns); marquee →
`.fw-lg__marquee` with the list rendered twice (the clone copy non-linked) and a
seamless `translateX(-50%)` loop, pause-on-hover, direction via
`animation-direction`. Each logo is `sc_lg_item()` (linked `<a>` or `<span>`).
Grayscale hover via `filter: grayscale()`. Logo max-height = `--lg-h`.

## Files
`config.php`, `options.php`, `static.php`, `views/view.php`,
`views/parts/registry.php`, `static/css/styles.css`, `static/js/carousel.js`,
`static/img/page_builder.svg`, `static/img/design/<key>.svg`.
