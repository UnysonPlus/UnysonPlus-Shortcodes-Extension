---
type: shortcode
name: star_rating
since: shortcodes 1.6.90
provides: leaf-shortcode
---

# Star Rating

A rating display with partial (half / decimal) fill — stars, hearts, circles or a
bar — plus an optional label, value and count text. **Components** tab.

## Options (atts)
- **Content**: `rating` (slider 0–10 step 0.5; clamped to `max`), `max` (`5`/`10`), `label`,
  `show_value` (switch → "4.5/5"), `count_text`.
- **Design**: `design` symbol (`star|heart|circle|bar`), `size` (xs/sm/md/lg/xl →
  `--sr-size`), `align`.
- **Styling**: `fill_color`/`empty_color`/`text_color` — compact color-preset pickers
  (`sc_color_field_compact`; `fill_color`/`empty_color` are `kind=bg`, `text_color` is
  `kind=text`), saved as `{predefined, custom}`. `view.php` applies **only** the `custom`
  hex → `--sr-fill` / `--sr-empty` / `--sr-text` (the preset choice is not consumed). Plus
  `font_size_preset` (`sc_font_size_field()`) and `spacing`.

## Rendering
`view.php` (`sc_sr_render`) outputs `.fw-sr[role=img,aria-label]`. For symbol
designs it draws `max` `.fw-sr__sym` units; each has a full empty SVG plus a
`.fw-sr__sym-fill` overlay whose inline `width:%` reveals the fractional fill
(`clamp(rating-(i-1),0,1)`). The `bar` design renders a single track + `.fw-sr__bar`
sized to `rating/max`. Symbol size is the em-based `--sr-size`. The rating slider
spans 0–10 and is clamped to `max`, so both 5- and 10-scales work.

## Files
`config.php`, `options.php`, `static.php`, `views/view.php`,
`views/parts/registry.php`, `static/css/styles.css`,
`static/img/page_builder.svg`, `static/img/design/<key>.svg`.
