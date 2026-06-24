---
type: shortcode
name: pricing_table
since: shortcodes 1.6.79
provides: leaf-shortcode
---

# Pricing Table

Comparable pricing plans (cards in a responsive grid) with icon, name, subtitle,
price (currency + amount + period), a feature list, a "featured" highlight, a
ribbon/badge and a CTA button. Six card designs. Lives in **Content Elements**.

Leaf shortcode (no class file). Registry-driven Design picker + per-design CSS
gating, modeled on image-box.

## Design system
`views/parts/registry.php` → designs (`classic`, `modern`, `minimal`, `gradient`,
`dark`, `outline`). Read by options.php (picker), view.php (`fw-pt--design-<key>`
class), static.php (auto-gates `static/css/design/<key>.css` — none ship, base
covers all).

## Options (atts)
- **Content**: `title` (optional heading) + `plans` (`addable-popup`). Per plan:
  `plan_title`, `icon` (icon-v2), `subtitle`, `currency`, `price`, `period`,
  `features` (textarea, one per line; a line starting `-`/`!` = unavailable,
  crossed out), `featured` (switch), `ribbon`, `button_label`, `button_url`,
  `button_target`.
- **Design**: `design` (picker, def classic), `columns` (2–5, def 3), `gap`
  (Gap-Scale slug → `var(--gap-<slug>)`, def 4), `featured_raise` (switch, scales
  featured up on desktop), `align` (alignment field, def center).
- **Styling**: `accent_color` (featured/price/button), `bg_color`, `card_bg`,
  `title_color`, `price_color`, `text_color`, `font_size_preset`, `spacing`.
  Per-element colors honor a CUSTOM hex → CSS var (`--pt-accent`, `--pt-card-bg`,
  `--pt-title`, `--pt-price`, `--pt-text`); presets fall back to defaults.
- **Animations + Advanced**: standard.

## Rendering
`view.php` (`sc_pt_render`) emits `.fw-pt[ --design / --cols / align ]` → optional
`<h3>` + `.fw-pt__grid` of `.fw-pt__plan` cards (`.is-featured` highlighted). Each
card: head (icon + name + subtitle), price (currency/amount/period), `<ul>`
features (`.is-off` crossed out), CTA `.fw-pt__btn`. Columns via `--pt-cols`; gap
via `--pt-gap`. Responsive: 2-up ≤992px, 1-up ≤576px.

## Files
`config.php`, `options.php`, `static.php`, `views/view.php`,
`views/parts/registry.php`, `static/css/styles.css`,
`static/img/page_builder.svg`, `static/img/design/<key>.svg`.
