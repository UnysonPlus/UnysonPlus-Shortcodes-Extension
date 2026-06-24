---
type: shortcode
name: timeline
since: shortcodes 1.6.81
provides: leaf-shortcode
---

# Timeline

A sequence of milestones (date, title, text, marker icon, image, link). Four
layouts: vertical alternating, vertical left, vertical right, and horizontal
(scrolling). Lives in **Content Elements**.

Leaf shortcode. Registry-driven Layout picker + per-design CSS gating.

## Design system
`views/parts/registry.php` → `alternating`, `left`, `right`, `horizontal`. Read by
options.php (picker), view.php (`fw-tl--design-<key>`), static.php.

## Options (atts)
- **Content**: `title` + `items` (`addable-popup`). Per item: `date`, `title`,
  `text`, `icon` (icon-v2), `image` (upload), `link_label`, `link_url`,
  `link_target`.
- **Design**: `design` (layout), `marker` (`dot|icon|number`), `card_style`
  (`card|outline|plain`).
- **Styling**: `accent_color` (line + markers), `line_color`, `card_bg`,
  `date_color`, `title_color`, `text_color` (custom hex → CSS vars `--tl-*`),
  `font_size_preset`, `spacing`.
- **Animations + Advanced**: standard.

## Rendering
`view.php` (`sc_tl_render`) emits `.fw-tl[--design / --marker-* / --card-*]` →
optional `<h3>` + `.fw-tl__track` of `.fw-tl__item`s, each a `.fw-tl__marker`
(dot / number = 1-based index / icon) + `.fw-tl__content > .fw-tl__card`
(image, date, title, text, link). The vertical line is a `::before` on
`.fw-tl__track`; `marker=number` uses the PHP index. Alternating uses
`:nth-child(odd|even)` to place items left/right of the centre line and collapses
to a left rail ≤768px. Horizontal is a `scroll-snap` flex row with items above
(odd) / below (even) the centre line.

## Files
`config.php`, `options.php`, `static.php`, `views/view.php`,
`views/parts/registry.php`, `static/css/styles.css`,
`static/img/page_builder.svg`, `static/img/design/<key>.svg`.
