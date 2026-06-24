---
type: shortcode
name: countdown (Countdown Timer)
provides: simple
---

# Countdown Timer

A **live countdown** to a target date & time — days / hours / minutes / seconds, ticking once
a second in the browser. Use it for launches, sales, event starts ("Spring Gala starts in
03 Days 14 Hours…"). A *simple* content shortcode (lives inside a column), auto-registered from
this folder; folder `countdown` → tag **`countdown`**. Each part (number, label) has its own
**Typography V2** font + preset-backed colour, matching the Animated Counter's conventions.

## Options schema (atts)

**Content tab** — `group_target`: `target`. `group_units`: `show_days` / `show_hours` /
`show_minutes` / `show_seconds`. `group_labels`: `label_days` / `label_hours` /
`label_minutes` / `label_seconds`. `group_complete`: `on_complete` / `complete_text`.

**Style tab** — `group_layout`: `alignment` / `box_preset`. `group_number`: `number_font` /
`number_color`. `group_label`: `label_font` / `label_color`.

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `target` | `datetime-picker` | `` | Target moment (string `Y/m/d H:i`), parsed in the **site timezone** → a UTC ms timestamp on `data-target`. Empty ⇒ the timer treats itself as finished. |
| `show_days` / `show_hours` / `show_minutes` / `show_seconds` | switch | `yes` | Which units to render. |
| `label_days` / `label_hours` / `label_minutes` / `label_seconds` | text | Days/Hours/Minutes/Seconds | Unit captions. |
| `on_complete` | select `message`/`zeros`/`hide` | `message` | What happens at zero. |
| `complete_text` | text | `This event has ended.` | Message shown when `on_complete = message`. |
| `alignment` | image-picker `left`/`center`/`right` (`sc_alignment_field`) | `left` | Block alignment. |
| `box_preset` | `border-style-picker` (`sc_get_border_preset_choices`) | `` | Wraps each unit in a **Box Preset** (`.boxp-{slug}` class — border/corners/shadow/fill/padding). None = plain numbers. |
| `number_font` / `label_font` | `typography-v2` | numbers 700/40-44, labels 600/13-16 | Per-part font; **Script + Color disabled** (`'components' => ['subset' => false, 'color' => false]`). |
| `number_color` / `label_color` | `predefined-colors-color-picker-compact` (`sc_color_field_compact`, kind `text`) | `` | Preset (→ `text-{slug}` class) or custom hex (→ inline `color`). |
| `tab_animation` | `sc_get_animation_fields()` | — | Entrance animation. |
| `advanced_settings` | `sc_get_advanced_tab()` | — | `css_id`, `css_class`, `custom_css`, `responsive_hide`, `custom_attrs`. |

## Rendering

`views/view.php` parses `target` with `date_create($target, wp_timezone())` → a UTC ms value on
`data-target` (so the deadline is absolute, not affected by the visitor's clock offset beyond
"now"). It emits `.fw-countdown.fw-countdown--{align}` →
`.fw-countdown__units` → one `.fw-countdown__unit[data-unit]` (with the chosen `.boxp-{slug}`
Box Preset class, if any) per enabled unit, each holding a
`.fw-countdown__num` + `.fw-countdown__label`. Per-part typography becomes inline CSS
(`fw_countdown_typography_css`, each declaration only when non-empty), colours resolve through
`sc_normalize_color_value()` (preset class or custom inline; a legacy flat hex string is
tolerated). Google fonts are enqueued per part. `scripts.js` ticks every second, padding
hours/minutes/seconds to two digits (days unpadded); at zero it follows `data-oncomplete`
(hide / show `.fw-countdown__done` / keep zeros). No-JS shows `--` placeholders.

## Pitfalls

- `target` is interpreted in the **site timezone**; an empty/invalid value yields `data-target=0`,
  which the JS treats as already finished (so it runs the completed branch immediately).
- Keep `*_color` empty to let the theme style the digits/labels.
- The unit's card look (border/corners/shadow/fill/padding) is entirely the **Box Preset**
  (`box_preset` → `.boxp-{slug}`, CSS generated in css-tokens). No `box_preset` = plain numbers.

## Files

- `config.php` — page-builder config (Content Elements tab) + `title_template`
- `options.php` — atts schema (`countdown_font_field` / `countdown_color_field` / `countdown_unit_switch`)
- `static.php` — frontend CSS + JS enqueues
- `views/view.php` — frontend HTML
- `static/js/scripts.js` (+ `.min`) — tick loop
- `static/css/styles.css` (+ `.min`) — styling
