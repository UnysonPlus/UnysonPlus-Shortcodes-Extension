---
type: shortcode
name: blockquote
since: shortcodes 1.6.88
provides: leaf-shortcode
---

# Blockquote

A styled quote / pullquote with optional author + source, in six editorial
designs. **Content Elements** tab. (Distinct from `testimonials`, which is a
multi-person widget.)

## Options (atts)
- **Content**: `quote` (safe inline HTML), `author`, `role`, `source_url` (makes
  the author a link), `show_mark` (quote mark).
- **Design**: `design` (`classic|pullquote|card|markquote|minimal|bordered`),
  `align`, `max_width`.
- **Styling**: `quote_color`/`accent_color`/`author_color`/`bg_color` (custom hex
  → `--bq-quote` / `--bq-accent` / `--bq-author` / `--bq-bg`), `font_size_preset`,
  `spacing`.

## Rendering
`view.php` (`sc_bq_render`) emits a `<figure class="fw-bq fw-bq--design-*">` with a
`<blockquote>` (optional `.fw-bq__mark`, `.fw-bq__text` via `wp_kses` inline
subset) and a `<figcaption class="fw-bq__cite">` (author + role). Each design is a
CSS skin (left border / centered pullquote / boxed card / big background mark /
minimal italic / top-bottom rules).

## Files
`config.php`, `options.php`, `static.php`, `views/view.php`,
`views/parts/registry.php`, `static/css/styles.css`,
`static/img/page_builder.svg`, `static/img/design/<key>.svg`.
