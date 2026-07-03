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

## Design system (future-proof)
`views/parts/registry.php` is the single source of truth → `solid`, `elevated`, `minimal`,
`outline`, `gradient`, `glass`, `dark`, `neumorph`. **To add a design:** add a registry entry +
a swatch (`static/img/design/<key>.svg`) + the CSS (`.fw-fb--design-<key>` in styles.css, or an
auto-gated `static/css/design/<key>.css`). It appears in the picker automatically.

The Design picker is a **popover `multi-picker`** (`options.php` → `design_settings`, picker id
`skin`), so a design can reveal its OWN options via the multi-picker `choices` (keyed by design
slug → read in view.php as `design_settings/{slug}/{id}`); the common controls (Effect, Trigger,
Parallax…) stay in their own sections. **view.php** resolves the design from
`design_settings/skin`, falling back to the legacy scalar `design` att (boxes saved before the
popover conversion), then emits `fw-fb--design-<key>`. static.php's per-design CSS gating reads
the same new-key-then-legacy path.

**Both faces** take a
Background Image that shows on ANY design (not just `image`): when `front_image` /
`back_image` is set, view.php emits `--fb-front-image` / `--fb-back-image` + the
`fw-fb--has-front-image` / `fw-fb--has-back-image` class, and the CSS paints it as a
cover on that face with a dark legibility overlay.

## Options (atts)
- **Content**: `front_icon` (icon-v2), `front_title`, `front_text`; `back_icon`
  (icon-v2), `back_title`, `back_text`, `button_label`, `button_url`, `button_target`.
- **Design**: `design_settings` (popover multi-picker → `skin`; legacy scalar `design`
  fallback), `flip_direction` (popover: `left|right|up|down|diagonal` flips → `--fb-rot`, or
  `fade|zoom|slide-*` reveals → `fw-fb--mode-reveal`), `trigger` (`hover|click|both`),
  `parallax` (content-depth translateZ), `flip_speed`/`flip_easing` (→ `--fb-speed`/`--fb-ease`),
  `height` (→ `--fb-h`), `rounded` (popover → `--fb-radius`).
- **Styling**: `front_bg`/`front_image`/`front_color`, `back_bg`/`back_image`/`back_color`
  (colors: custom hex → CSS vars `--fb-front-bg` etc.; images → `--fb-*-image`),
  `font_size_preset`; **`button_style` + `button_size`** (`button-style-picker`, from
  `sc_get_button_style_choices()` / `_size_` — the same Theme Settings → Buttons presets
  the `[button]` shortcode uses; rendered as `<a class="btn btn-{preset} btn-{size}
  fw-fb__btn">`); `spacing`. (The old `accent_color` "Button Color" was removed.)
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
