---
type: shortcode
name: feature_list
since: shortcodes 1.6.89
provides: leaf-shortcode
---

# Feature List

An icon-led list (checklist / per-item icons / numbered / bullets / badge) with
optional sub-text and per-item links, 1–3 columns. **Content Elements** tab.

## Options (atts)
- **Content**: `items` (`addable-popup`). Per item: `text`, `subtext`, `icon`
  (icon-v2), `state` (`on`/`off` → check/cross + strike, checklist design only),
  `link_url`, `link_target`.
- **Design**: `design` marker style (`check|icon|numbered|bullet|badge`),
  `columns` (1–3), `dividers` (switch), `spacing_size` (sm/md/lg).
- **Styling**: `marker_color`/`text_color`/`sub_color` (custom hex → `--fl-marker`
  / `--fl-text` / `--fl-sub`), `font_size_preset`, `spacing`.

## Rendering
`view.php` (`sc_fl_render`) emits a `<ul class="fw-fl fw-fl--design-*">` (grid of
`--fl-cols` columns) of `<li class="fw-fl__item">`. The marker is an inline SVG
check/cross (checklist; `off` adds `.is-off` strike), the 1-based number, a dot,
or the per-item icon (icon/badge designs; badge boxes it). The body is a link when
`link_url` is set. Collapses to one column ≤768px.

## Files
`config.php`, `options.php`, `static.php`, `views/view.php`,
`views/parts/registry.php`, `static/css/styles.css`,
`static/img/page_builder.svg`, `static/img/design/<key>.svg`.
