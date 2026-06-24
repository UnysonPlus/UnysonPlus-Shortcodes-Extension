---
type: shortcode
name: comparison_table
since: shortcodes 1.7.46
provides: leaf-shortcode
---

# Comparison Table

A feature comparison matrix (plans across the top, feature rows down the side).
**Components** tab.

## Data model (important)
The matrix is two **1-D repeaters**, not a 2-D grid:
- `columns` (`addable-popup`): per plan — `name`, `price`, `badge`, `featured`
  (switch), `button_text`/`button_url`/`button_target`.
- `rows` (`addable-popup`): per feature — `is_heading` (switch → full-width group
  heading), `label`, `tooltip`, and **`values`** (a `textarea`: **one line per
  column, in column order**). Each line is a token: `yes`/`y`/`true`/`✓` → check,
  `no`/`n`/`false`/`✗` → cross, `-`/`dash`/`n/a` → dash, anything else → literal
  text (e.g. "Up to 5"). Missing lines render a dash. This keeps the 2-D data in
  a flat option set (Unyson has no native 2-D grid option).

## Options
- **Content**: `first_col_label`, `columns`, `rows` (above).
- **Design**: `style` (`bordered|striped|minimal`), `highlight_featured`,
  `sticky_header`, `center_cells`.
- **Styling**: `accent_color`/`header_bg`/`header_text`/`text_color`/`border_color`
  (→ `--ct-accent` / `--ct-head-bg` / `--ct-head-text` / `--ct-text` /
  `--ct-border`), `font_size_preset`, `spacing`.

## Rendering
`view.php` (`sc_ct_render`) builds a real `<table>` inside a horizontally
scrollable `.fw-ct__scroll`. `sc_ct_cell()` maps each token to a check `<svg>` /
cross `<svg>` / dash / text. Featured columns get `.is-featured` on every header
+ cell; heading rows are a `colspan` `<th>`. External plan buttons get
`target="_blank" rel="noopener noreferrer"`. CSS-only; no JS.

## Pitfalls
1. **Cell order = column order.** If you reorder columns, reorder each row's
   `values` lines to match.
2. Featured highlight + section-heading tint use `color-mix` with `@supports`
   fallbacks.

## Files
`config.php`, `options.php`, `static.php`, `views/view.php`,
`static/css/styles.css`, `static/img/page_builder.svg`.
