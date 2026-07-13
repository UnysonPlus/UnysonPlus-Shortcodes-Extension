---
type: shortcode
name: logo_grid
since: shortcodes 1.6.92
provides: leaf-shortcode
---

# Logo Grid

A grid / boxed grid / carousel / marquee of client / partner logos, with an
optional grayscale-to-color hover. **Media Elements** tab.

## Design system
`views/parts/registry.php` → `grid`, `boxed`, `carousel` (`splide => true`),
`marquee`. The Carousel design pulls in the vendored Splide + a tiny mount
(`static/js/carousel.js`) via the per-instance `static.php` hook; the others are
CSS-only.

## Options (atts)
- **Content**: `logos` (`addable-popup`): `image` (upload), `svg` (raw inline
  `<svg>` markup — sanitised on output, takes priority over `image`, recolour via
  `fill="currentColor"`), `name` (alt / label text), `no_label` (switch — hide
  THIS logo's text label when Show Names is on), `link_url`, `link_target`
  (switch → `_blank`/`_self`).
- **Design**: `design` layout, `columns` (2–6; per-view for carousel), `gap`
  (Gap-Scale slug → `var(--gap-<slug>)`), `logo_height` (slider px → `--lg-h`),
  `grayscale` (switch), `show_labels` (switch — render each Name as a visible
  label beside the mark), `autoplay` (carousel), `speed` (marquee/autoplay),
  `direction` (marquee).
- **Styling**: `text_color` (compact color preset; applied to the wrapper by the
  Styling-tab helper — colors name labels + inline SVG marks using
  `fill="currentColor"`), `box_bg` (compact color preset, boxed design → inline
  `--lg-box-bg` from its `custom` hex), `font_size_preset`, `spacing`.

## Rendering
`view.php` (`sc_lg_render`) keeps logos that have an image, inline SVG, or a name
(empty → an admin/AJAX placeholder, nothing on the frontend), then renders per
design: grid/boxed → `.fw-lg__grid` (CSS grid of `--lg-cols`); carousel → a Splide
`.splide.fw-lg__carousel[data-splide]` (loop, autoplay, perPage = columns, drag
only when logos > columns, responsive breakpoints at 992/576); marquee →
`.fw-lg__marquee` with the list rendered twice (the clone copy non-linked) and a
seamless loop (`--lg-dur` scaled by count × speed), pause-on-hover, direction via
the `--left`/`--right` modifier. Each logo is `sc_lg_item()` (linked `<a>` or
`<span>`): the mark is inline sanitised SVG (`sc_icon_sanitize_svg`, priority) or
an `<img loading="lazy">`, plus an optional `.fw-lg__label` name (when Show Names
is on and the logo isn't `no_label`). Grayscale hover via `filter: grayscale()`.
Logo max-height = `--lg-h` (clamped 16–200px in view; slider caps at 120).

## Files
`config.php`, `options.php`, `static.php`, `views/view.php`,
`views/parts/registry.php`, `static/css/styles.css`,
`static/css/design/<key>.css` (per-design, enqueued by `static.php` when present),
`static/js/carousel.js`, `static/img/page_builder.svg`,
`static/img/design/<key>.svg`.
