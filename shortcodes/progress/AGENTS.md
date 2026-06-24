# Progress shortcode

Skill / progress indicators in three styles — **horizontal bars**, **circular rings**, and
**semi-circle gauges**. Each item fills (and optionally counts its % up) when scrolled into view
(IntersectionObserver). No dependencies. Respects `prefers-reduced-motion` (final state, no anim).

## Files
- `config.php` — page-builder registration (Content Elements, tag `progress`).
- `options.php`
  - **Bars tab** — `group_layout` holds the `layout` **multi-picker** (`bar` | `circle` | `gauge`;
    circle/gauge reveal size, thickness, and per-row column count). `group_bars` is the
    `addable-popup` list: label, percent slider, optional `icon` (icon-v2), per-bar color.
  - **Style tab** — height (bars), value position (beside label / inside the bar), rounded,
    striped (bars), show %, animate on scroll, count-up number, gap; colors: fill, gradient
    second color, track, label.
  - **Animations** + **Advanced** (css id/class, responsive hide).
- `views/view.php` — branches on `layout.type`:
  - `bar` → `.fw-progress__item > head + track > fill` (fill carries `data-width`; starts at 0
    when Animate is on, JS sets it on scroll). % can render inside the fill.
  - `circle` / `gauge` → inline SVG ring/arc. The fill stroke carries `data-offset` (final
    `stroke-dashoffset`); starts empty when Animate is on. `.fw-progress__center` overlays the
    icon + %; `.fw-progress__caption` is the label below.
  - Colors resolve via `sc_normalize_color_value` (preset class / custom inline) for bars, and via
    the local `fw_progress_raw_color()` (maps preset slugs back to hex through
    `unysonplus_color_preset_slug_map()`) for SVG strokes & gradients. Per-bar color overrides the
    section Fill. A Gradient second color builds a `linear-gradient` (bars) / `<linearGradient>`
    (svg). Height via `--fwp-h`, gap via `--fwp-gap`, columns via `--fwp-cols`.
- `static/js/scripts.js` — IntersectionObserver fills bars (width) and rings/gauges
  (stroke-dashoffset), and animates count-up `[data-count]` numbers. Honors reduced-motion / no-IO
  by jumping to the final state.
- `static/css/styles.css` — bar track/fill, rounded, striped; circle/gauge grid; center overlay;
  reduced-motion guard. **Keep `styles.min.css` / `scripts.min.js` in sync** (production serves the
  min via `fw_min_uri`).

## Notes
- Adding `layout` was additive — legacy saved items have no `layout` key, so `view.php` defaults to
  `bar`; all new options default to the prior behavior. No editor migration needed (no existing
  scalar was re-typed into the multi-picker).
- Circle/gauge stroke colours need a real colour; presets are supported because they're resolved to
  hex. If neither preset nor custom is set, the CSS default (`#2563eb`) applies.
- The Site Converter does NOT decompose captured skill sections into this shortcode (that would
  break the verbatim section layout) — the generated theme animates captured `.progress-bar` markup
  in place instead.
