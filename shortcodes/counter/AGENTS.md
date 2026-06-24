---
type: shortcode
name: counter (Animated Counter)
since: 2.10.63
provides: simple
---

# Animated Counter

An **animated running number** that counts from a start value to a target when it
scrolls into view (IntersectionObserver). It's a focused *number* widget: the optional
**prefix** and **suffix** double as the inline left / right captions — there is **no label
and no icon**. Anything that should sit above or below the number is a separate
`text-block` / `special-heading` (more layout control, keeps this shortcode lean). Each of
the three parts — number, prefix, suffix — has its own **Typography V2** font control plus a
preset-backed **colour** picker. Use it for stat bands — "45,280", "96%", "$1.2M". A *simple*
content shortcode (lives inside a column), auto-registered from this folder. Display title is
**"Animated Counter"**; the tag stays **`counter`** (short, stable, what the Site Converter
emits). Every text field has `'dynamic_content' => false` (a literal-number widget).

## Options schema (atts)

**Content tab** — `group_value`: `number` / `start` / `prefix` / `suffix`. `group_format`:
`decimals` / `separator` / `duration` / `easing`.

**Style tab** — `group_layout`: `alignment`. `group_number`: `number_font` / `number_color`.
`group_prefix`: `prefix_font` / `prefix_color`. `group_suffix`: `suffix_font` / `suffix_color`.

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `number` | text | `100` | Target value; non-digits (commas) stripped for the count. |
| `start` | text | `0` | Value the count begins at. |
| `prefix` / `suffix` | text | `` | Inline text before / after the number (`$`, `+`, `%`, also the left/right caption). |
| `decimals` | select `0`–`3` | `0` | **Decimal places** (descriptive choices). |
| `separator` | select `yes`/`no` | `yes` | Thousands separator. |
| `duration` | text (ms) | `2000` | Count-up length (`0` = no animation). |
| `easing` | select `ease-out`/`linear`/`ease-in-out` | `ease-out` | Count easing. |
| `alignment` | image-picker `left`/`center`/`right` (`sc_alignment_field`) | `left` | Block alignment. |
| `number_font` / `prefix_font` / `suffix_font` | `typography-v2` | weight `700`; number `42/46`, prefix/suffix `24/28`; empty family | Per-part font, size, weight, spacing. **Script (subset) AND Color disabled** via `'components' => ['subset' => false, 'color' => false]` — colour is the separate `*_color` field. |
| `number_color` / `prefix_color` / `suffix_color` | `predefined-colors-color-picker-compact` (`sc_color_field_compact`) | `` | Preset-backed colour: a theme **Color Preset** (→ `text-{slug}` utility class) or a custom hex (→ inline `color`). |
| `tab_animation` | `sc_get_animation_fields()` | — | Entrance animation. |
| `advanced_settings` | `sc_get_advanced_tab()` | — | `css_id`, `css_class`, `custom_css`, `responsive_hide`, `custom_attrs`. |

A minimal atts set is fine — the view null-guards every key, so a generator (e.g. the Site
Converter) can emit just `number` / `suffix` + `unique_id`. Omitting the `*_font` / `*_color`
fields leaves family/colour empty so the **theme / Site-Converter accent CSS** styles the
number (`.sc-features .fw-counter__value`).

## Rendering

`views/view.php` outputs `.fw-counter.fw-counter--{align}` → `.fw-counter__main` →
`.fw-counter__value[data-target,data-start,data-duration,data-decimals,data-sep,data-easing]`
wrapping three spans: `.fw-counter__prefix`, `.fw-counter__num`, `.fw-counter__suffix`. Each
span is built by `fw_counter_part()`: its `*_font` (typography-v2) becomes inline CSS
(`fw_counter_typography_css`, each declaration only when non-empty), its `*_color` is resolved
by `sc_normalize_color_value(..,'text')` → a preset **utility class** or a custom inline
`color`. Google fonts are enqueued per-part (`fw_counter_enqueue_font`). No-JS shows the final
formatted number; `scripts.js` resets `.fw-counter__num` to `start` and eases to the target
with `requestAnimationFrame`, honoring `prefers-reduced-motion`.

## Pitfalls

- `number` may contain commas / a suffix in raw form; the view extracts the numeric target
  with `preg_replace('/[^0-9.\-]/','')` — keep the *visible* suffix in `suffix`.
- The animation runs once per element (`data-counted` guard), first time it's ≥40% in view.
- Typography fields always emit a px `size` (their default), so the `.8em` prefix/suffix CSS
  fallback only applies to generator-emitted counters that pass no `*_font`.
- Keep `*_color` empty (no preset / no custom) so converted-site accent CSS wins.

## Files

- `config.php` — page-builder config (Content Elements tab) + `title_template`
- `options.php` — atts schema (`counter_font_field` / `counter_color_field` helpers)
- `static.php` — frontend CSS + JS enqueues
- `views/view.php` — frontend HTML
- `static/js/scripts.js` (+ `.min`) — count-up animation
- `static/css/styles.css` (+ `.min`) — styling
