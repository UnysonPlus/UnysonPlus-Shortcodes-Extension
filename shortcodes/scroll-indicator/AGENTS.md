---
type: shortcode
name: scroll-indicator
since: 1.11.34
provides: leaf-shortcode
---

# Scroll Indicator

A hero **"scroll to descend" cue** — a small label + an animated chevron that
smooth-scrolls to the next section on click. The common landing-page affordance at
the bottom of a full-height hero. Tag: `scroll_indicator`. Page-builder tab:
**Content Elements**.

## Registration

No class file — a leaf shortcode auto-registered by the folder scan (`config.php`
present). `config.php`'s `title_template` previews the label + glyph in the canvas.

## Options (atts)

Source of truth: `options.php`.

### Content — `group_content`
| Att | Type | Default | Notes |
|-----|------|---------|-------|
| `text` | `text` | "Scroll to descend" | The cue label. Empty → icon-only. |
| `icon` | `icon-v2` | (none) | The glyph. **Left as None → a default `lucide/chevron-down`** (resolved in the view). |
| `target` | `text` | — | On-page anchor to smooth-scroll to (e.g. `#mission`). **Empty → scroll down ~90% of the viewport.** |

### Design — `group_design`
| Att | Type | Default | Choices |
|-----|------|---------|---------|
| `layout` | `select` | `stacked` | `stacked` (label above icon) / `stacked-reverse` (icon above label) / `inline` / `icon-only` |
| `animation` | `select` | `bounce` | `bounce` / `pulse` / `nudge` / `none` (the icon animates; label stays put) |

### Styling — `group_colors` + `group_spacings`
`text_color`, `icon_color` (compact color presets), `icon_size` (unit-input — scales font
icons AND inline SVGs, the `.sc-scroll-cue__icon svg` is `1em`), `spacing`.

## Rendering (`views/view.php`)

Outputs `<div wrapper><a class="sc-scroll-cue sc-scroll-cue--{layout}
sc-scroll-cue--anim-{animation}" href="{target|#}" [data-scroll-down]>` + a
`.sc-scroll-cue__label` and a `.sc-scroll-cue__icon`. **Label is always first in the DOM**
(accessible order); CSS `flex-direction` controls the visual order (so
`stacked-reverse` = `column-reverse`). No target → `href="#"` + `data-scroll-down`.

## Frontend assets (`static.php` + `static/`)

`css/styles.css` — flex layouts + the `sc-scroll-bounce` / `-pulse` / `-nudge` keyframes
(paused under `prefers-reduced-motion`). `js/scripts.js` — a delegated click handler:
an `#anchor` `scrollIntoView({behavior:'smooth'})`; a no-target cue scrolls the viewport
down ~90%.

## Verification

1. Drop it under a full-height hero, set `target` to the next section's `#id` → clicking
   smooth-scrolls there; the chevron bounces.
2. Empty target → clicking scrolls down one screen.
3. `icon-only` hides the label; `stacked-reverse` puts the icon on top.
4. Set Icon Size → the chevron (or a font icon) resizes.

## Files

- `config.php`, `options.php`, `views/view.php`
- `static.php` + `static/css/styles.css` + `static/js/scripts.js`
