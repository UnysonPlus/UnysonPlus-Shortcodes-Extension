---
type: shortcode
name: before_after
since: shortcodes 1.6.75
provides: leaf-shortcode
---

# Before / After

An interactive before/after image comparison slider. Two images are stacked; the
top ("before") layer is clipped to reveal the bottom ("after") layer, and a
draggable handle moves the boundary. Drag / hover / click-toggle interaction,
horizontal or vertical, with six handle "designs", labels, an intro sweep, and
full keyboard accessibility. Lives in the **Media Elements** page-builder tab.

This is a **leaf** (simple) shortcode — no class file, no page-builder item
class. Modeled on `image-box`: registry-driven Design picker + per-design CSS
gating, comprehensive Styling tab.

## Design system

`views/parts/registry.php` is the single source of truth. Each entry is a visual
**skin** (handle + label look): `key => [ label, thumb, force_labels? ]`. Three
readers: `options.php` (image-picker choices), `views/view.php` (adds the
`fw-bac--design-<key>` class + reads `force_labels`), `static.php` (auto-gates
`static/css/design/<key>.css` if present — none ship; the base `styles.css`
covers every skin).

Unlike image-box, **all designs share ONE structure and ONE JS engine** — the
design only changes CSS. The comparison's **behaviour** (orientation,
interaction, start position, ratio, …) is set by cross-design options on the
Design tab, so any skin works with any behaviour.

Designs: `classic` (round knob + chevrons, default), `circle` (large translucent
knob), `arrows` (chevrons only, no ring), `line` (minimal line + grip dot),
`labeled` (forces Before/After badges), `framed` (card chrome, forces labels).

## Options schema (atts)

Source of truth: `options.php`. Tabs: Content · Design · Styling · Animations · Advanced.

### Tab: Content
| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `before_image` | `upload` | — | Before image (left/top). `{attachment_id,url}` |
| `after_image` | `upload` | — | After image (right/bottom). `{attachment_id,url}` |
| `show_labels` | `switch` (`yes`/`no`) | `yes` | Show side labels (labeled/framed force on) |
| `before_label` | `text` | `Before` | Left/top label |
| `after_label` | `text` | `After` | Right/bottom label |

### Tab: Design
| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `design` | `image-picker` (registry) | `classic` | Handle/label skin |
| `orientation` | `select` (`horizontal`/`vertical`) | `horizontal` | Divider + reveal direction |
| `interaction` | `select` (`drag`/`hover`/`toggle`) | `drag` | Drag handle / follow cursor / click-crossfade |
| `start_position` | `slider` (0–100) | `50` | Initial divider position (% before) |
| `auto_intro` | `switch` | `yes` | One-time sweep when scrolled into view (not for toggle) |
| `ratio` | `select` (`original`/`ratio-1-1`/`ratio-4-3`/`ratio-3-2`/`ratio-16-9`/`ratio-3-4`/`ratio-2-3`) | `ratio-16-9` | Crop both images |
| `max_width` | `text` | `''` | e.g. `800px` / `80%`; blank = full width (centered when set) |
| `rounded` | `select` (`rounded-0`/`rounded`/`rounded-lg`) | `rounded` | Corner radius |
| `handle_size` | `select` (`sm`/`md`/`lg`) | `md` | Knob size (`--bac-knob`) |

### Tab: Styling
`bg_color` (wrapper), `font_size_preset`, and four **custom-color-only** vars
(preset picks fall back to defaults): `divider_color` → `--bac-divider`,
`handle_color` → `--bac-handle`, `handle_icon_color` → `--bac-handle-icon`,
`label_bg` → `--bac-label-bg`, `label_text` → `--bac-label-text`. Plus `spacing`.

### Tabs: Animations + Advanced — standard.

## Rendering

`view.php` (`sc_bac_render`) resolves both images (full-size URL + alt from the
library), bails (or shows an editor hint) if either is missing, then emits:
`.fw-bac[role=slider]` with `data-orientation/interaction/start/auto` and a
`--bac-pos` CSS var, containing `.fw-bac__media` → a hidden `.fw-bac__sizer` img
(establishes height for the `original` ratio), the `.fw-bac__after` and clipped
`.fw-bac__before` layers, the `.fw-bac__handle` (line + knob + chevrons), and the
labels. `scripts.js` updates `--bac-pos` from pointer/keyboard, handles all three
interactions + both orientations, and runs the intro sweep via rAF +
IntersectionObserver. The reveal is `clip-path: inset(...)` on the before layer.

## Pitfalls

1. **Two images required** — both `before_image` and `after_image` must be set or
   the front-end renders nothing (editor shows a hint). Use same-size images.
2. **`--bac-pos` is the single source of truth** for the reveal AND the handle
   position (CSS keeps them in sync). JS only sets that one property + aria.
3. **Custom colors only** — the divider/handle/label color fields honor a CUSTOM
   hex (→ CSS var); a preset pick falls back to the stylesheet default (same
   pattern as image-box accent/overlay). `bg_color` is the normal wrapper styling.
4. **Toggle interaction** removes the clip (full before), crossfades opacity on
   `.is-after`, and hides the handle — `auto_intro` is ignored for it.
5. **`original` ratio** relies on the in-flow `.fw-bac__sizer` img for height; the
   fixed ratios hide it and size `.fw-bac__media` via `aspect-ratio`.

## Verification

1. Drag **Before / After** (Media Elements) → modal; set a Before + After image; save.
2. Front-end: drag the handle → reveal moves; release anywhere. Keyboard: focus
   the slider, ← → (Shift = bigger steps), Home/End.
3. Design → `vertical` orientation → divider is horizontal, drag up/down.
4. Interaction → `hover` → reveal follows the cursor; → `toggle` → click crossfades.
5. `auto_intro` on → a sweep plays once when it scrolls into view (stops on touch).
6. Styling → set a custom Divider/Handle/Label color → vars apply.

## Files

- `config.php` — page-builder config (Media Elements tab, canvas `title_template`)
- `options.php` — atts schema
- `static.php` — base CSS/JS enqueue + per-design CSS gating hook
- `views/view.php` — render (`sc_bac_render`)
- `views/parts/registry.php` — design (skin) source of truth
- `static/css/styles.css` — base + all skins
- `static/js/scripts.js` — comparison engine
- `static/img/page_builder.svg` — 16×16 Media Elements tile icon
- `static/img/design/<key>.svg` — 88×64 design-picker thumbnails
