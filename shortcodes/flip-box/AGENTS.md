---
type: shortcode
name: flip_box
since: shortcodes 1.6.80
provides: leaf-shortcode
---

# Flip Box

A two-sided 3D card that flips on hover or click. Front: icon + title + text (or a
background image). Back: title + text + button. Four flip directions, four
designs. Lives in **Content Elements**.

Leaf shortcode. Registry-driven Design picker + per-design CSS gating.

## Design system
`views/parts/registry.php` → `solid`, `gradient`, `outline`, `image` (the last
sets `front_image`, using the Front Background Image as a cover with a dark
overlay). Read by options.php / view.php (`fw-fb--design-<key>`) / static.php.

## Options (atts)
- **Content**: `front_icon` (icon-v2), `front_image` (upload, for the image
  design), `front_title`, `front_text`; `back_title`, `back_text`,
  `button_label`, `button_url`, `button_target`.
- **Design**: `design`, `flip_direction` (`left|right|up|down`, → `--fb-rot`),
  `trigger` (`hover|click`), `height` (slider px, def 300 → `--fb-h`), `rounded`.
- **Styling**: `front_bg`/`front_color`/`back_bg`/`back_color`/`accent_color`
  (custom hex → CSS vars `--fb-front-bg` etc.), `font_size_preset`, `spacing`.
- **Animations + Advanced**: standard.

## Rendering
`view.php` (`sc_fb_render`) emits `.fw-fb[--design / --dir-* / hover|click]` with
`--fb-h` + color vars, containing `.fw-fb__inner` (the 3D rotator) with
`.fw-fb__front` and `.fw-fb__back` faces. CSS flips on `:hover` / `:focus-within`
(hover trigger) or `.is-flipped` (click trigger, toggled by `scripts.js` which
also handles Enter/Space and ignores clicks on the back's link). The direction is
a single `--fb-rot` (rotateY/rotateX ±180deg) used by both the back's resting
transform and the inner's flipped transform. `prefers-reduced-motion` disables the
transition.

## Pitfalls
1. `trigger: click` adds `tabindex/role=button/aria-pressed` and is the right
   choice for touch (hover can't flip on touch). The back button still works —
   the JS skips toggling when the click/Enter originates on an `<a>`.
2. The `image` design needs the **Front Background Image** set; otherwise the
   front is just the front bg color.

## Files
`config.php`, `options.php`, `static.php`, `views/view.php`,
`views/parts/registry.php`, `static/css/styles.css`, `static/js/scripts.js`,
`static/img/page_builder.svg`, `static/img/design/<key>.svg`.
