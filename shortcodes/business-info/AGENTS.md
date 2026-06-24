---
type: shortcode
name: business_info
since: shortcodes 1.6.96
provides: leaf-shortcode
---

# Business Info

Opening hours (with a live Open/Closed status computed from the site timezone)
plus contact details — address, phone, email, website, directions — in four
layouts. **Content Elements** tab.

## Options (atts)
- **Content**: `biz_name`; `hours` (`addable-popup`, defaults to Mon–Sun) per row:
  `day` (`mon`..`sun`), `closed` (switch), `open`/`close` (`HH:MM` 24h), `note`;
  `show_status`, `time_format` (`12`/`24`); contact: `address`, `phone`, `email`,
  `website`, `map_link`.
- **Design**: `design` (`table|card|split|compact`), `highlight_today`.
- **Styling**: `accent_color`/`card_bg`/`text_color` (custom hex → `--bi-*`),
  `font_size_preset`, `spacing`.

## Rendering
`view.php` (`sc_bi_render`) uses `current_datetime()` (site timezone) for today's
key (`mon`..`sun`) and the current minute. The status reads today's row: closed →
"Closed"; else open if `now ∈ [open, close)` (with overnight support when
`close < open`). Renders a status badge (pulsing dot when open), a `<table>` of
hours (`is-today` highlighted, `is-closed` flagged, optional per-day note), and a
contact block (`tel:` / `mailto:` / external links; the `table` design shows hours
only). `split` is a two-column grid; card/split are boxed. Times are formatted per
`time_format`.

## Pitfalls
1. Status is server-rendered, so it reflects page-render time (fine for cached
   pages at minute granularity). Overnight ranges (e.g. 20:00–02:00) are handled.
2. Days come from the `hours` repeater order — reorder/remove rows freely; the
   status still matches by `day` key, not position.

## Files
`config.php`, `options.php`, `static.php`, `views/view.php`,
`views/parts/registry.php`, `static/css/styles.css`,
`static/img/page_builder.svg`, `static/img/design/<key>.svg`.
