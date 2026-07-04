---
type: shortcode
name: before_after
since: shortcodes 1.6.75
provides: leaf-shortcode
---

# Before / After

An interactive two-image element with **two types**, chosen by an inline
image-picker multi-picker (`type`) at the top of the Design tab (Content holds
only the two images):

- **`comparison`** (default) — the classic before/after **slider**. Two images are
  stacked; the top ("before") layer is clipped to reveal the bottom ("after")
  layer, and a draggable handle moves the boundary. Drag / hover / click-toggle
  interaction, horizontal or vertical, with seven handle "designs" (incl. an
  **Invisible** design with no line/handle for a chrome-less cursor wipe), labels,
  an intro sweep, and full keyboard accessibility.
- **`spotlight`** — a cursor-following **circular reveal** (the "Lithos" effect).
  Same two stacked layers, but the top "before" layer carries a soft **inverse
  radial mask** that follows the pointer: a circular hole under the cursor reveals
  the "after" beneath. Radius + edge softness + smooth-follow are options.

**Use as Section Background** (`as_background`, Design tab) applies to **both**
types via the reusable **`sc_section_background_*` helper**
(`includes/shortcode-background-helper.php` + shared `static/{js/sc-bg-fill.js,css/sc-bg-fill.css}`):
the element gets the shared `.sc-bg-fill` class and `window.scBgFill()` moves it to be
the backdrop of its nearest `<section>` (full-bleed), lifting the Section's content on
top. Great for a hero whose backdrop wipes (invisible + Follow-the-cursor) or reveals
(spotlight) under the cursor.

Lives in the **Media Elements** page-builder tab. This is a **leaf** (simple)
shortcode — no class file, no page-builder item class. Modeled on `image-box`:
registry-driven Design picker + per-design CSS gating, comprehensive Styling tab.

## Type multi-picker (INLINE, not popover)

`type` is an **inline** image-picker multi-picker per the canonical convention:
top-level `label`/`desc` are `false`, the user-visible label lives on the `type`
picker sub-option, `show_borders => false`, default via the top-level
`'value' => [ 'type' => 'comparison' ]`. Its `choices` reveal each type's
sub-options (grouped), so the **saved shape is nested**:
`atts['type'] = [ 'type' => 'comparison'|'spotlight', 'comparison' => [ … ],
'spotlight' => [ … ] ]`. `view.php` reads them with slash paths, e.g.
`sc_get('type/comparison/design', …)` and `sc_get('type/spotlight/spotlight_radius', …)`.
The two `type` tiles are `static/img/design/type-comparison.svg` / `type-spotlight.svg`.
No migration exists (the shortcode had no live usage when types were added).

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
`invisible` (no line / no handle — a chrome-less wipe, pairs with hover/background),
`labeled` (forces Before/After badges), `framed` (card chrome, forces labels).

## Options schema (atts)

Source of truth: `options.php`. Tabs: Content · Design · Styling · Animations · Advanced.

### Tab: Content
| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `before_image` | `upload` | — | Base image (Comparison: left/top; Spotlight: shown normally). `{attachment_id,url}` |
| `after_image` | `upload` | — | Revealed image (Comparison: right/bottom; Spotlight: under the pointer). `{attachment_id,url}` |

### Tab: Design
| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `type/type` | `image-picker` (multi-picker) | `comparison` | **Type**: `comparison` slider or `spotlight` reveal |

**`type/comparison/*`** (revealed when `type=comparison`):
| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `design` | `image-picker` (registry) | `classic` | Handle/label skin |
| `orientation` | `select` (`horizontal`/`vertical`) | `horizontal` | Divider + reveal direction |
| `interaction` | `select` (`drag`/`hover`/`toggle`) | `drag` | Drag handle / follow cursor / click-crossfade |
| `start_position` | `slider` (0–100) | `50` | Initial divider position (% before) |
| `auto_intro` | `switch` | `yes` | One-time sweep when scrolled into view (not for toggle) |
| `handle_size` | `select` (`sm`/`md`/`lg`) | `md` | Knob size (`--bac-knob`) |
| `show_labels` | `switch` (`yes`/`no`) | `yes` | Show side labels (labeled/framed force on) |
| `before_label` / `after_label` | `text` | `Before` / `After` | Side labels |

**`type/spotlight/*`** (revealed when `type=spotlight`):
| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `spotlight_radius` | `slider` (60–700 px) | `240` | Circle radius → `--sr-r` |
| `spotlight_softness` | `slider` (0–95 %) | `55` | Edge feather → `--sr-in` (`100 − softness`) |
| `smooth_follow` | `switch` | `yes` | Lerp the spotlight toward the pointer (rAF) |
| `reveal_on_load` | `switch` | `yes` | Idle-rest the spotlight at centre (discoverable + touch) |

**Shared** (Design tab, below the Type picker — TOP-LEVEL atts, both types):
| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `as_background` | `switch` (`yes`/`no`) | `no` | Fill the parent `<section>` & lift its content on top (via `sc_section_background_field()`). Applies to either type. |
| `ratio` | `select` (`original`/`ratio-1-1`/`ratio-4-3`/`ratio-3-2`/`ratio-16-9`/`ratio-3-4`/`ratio-2-3`) | `ratio-16-9` | Crop both images. **Ignored in background mode.** |
| `max_width` | `text` | `''` | e.g. `800px` / `80%`; blank = full width. **Ignored in background mode.** |
| `rounded` | `select` (`rounded-0`/`rounded`/`rounded-lg`) | `rounded` | Corner radius. **Ignored in background mode.** |

### Tab: Styling
`bg_color` (wrapper), `font_size_preset`, and four **custom-color-only** vars
(preset picks fall back to defaults): `divider_color` → `--bac-divider`,
`handle_color` → `--bac-handle`, `handle_icon_color` → `--bac-handle-icon`,
`label_bg` → `--bac-label-bg`, `label_text` → `--bac-label-text`. Plus `spacing`.

### Tabs: Animations + Advanced — standard.

## Rendering

`view.php` (`sc_bac_render`) resolves both images + shared framing, reads
`type/type`, then dispatches to **`sc_bac_render_comparison`** or
**`sc_bac_render_spotlight`**.

**Spotlight** emits `.fw-bac.fw-bac--spotlight[role=img][data-bac-spot]` with
`data-bg/smooth/idle` and `--sr-r` / `--sr-in` CSS vars, containing the same
`.fw-bac__media` → sizer + `.fw-bac__after` + `.fw-bac__before` layers (no handle,
no labels). CSS masks the **before** layer with an inverse
`radial-gradient(circle var(--sr-r) at var(--sr-x) var(--sr-y), transparent var(--sr-in), #000 100%)`;
`scripts.js` `initSpot()` feeds `--sr-x/--sr-y` (px, relative to the media) from
`pointermove` (lerped via rAF when `smooth`). `.is-away` drops the mask (before
fully shown) when the pointer leaves and idle-reveal is off.

**Comparison** background mode: `initOne()` reads `data-bg`, resolves the host via
the shared `window.scBgFill(el)`, and binds its hover / drag / toggle listeners to
that host (the whole Section) instead of the media — so a *cursor-follow* wipe (esp.
with the Invisible design) drives from anywhere in the Section. The reveal geometry
still uses the media rect (which now covers the Section).

**Background mode (both types)** uses the reusable **`sc_section_background_*`
helper**, NOT any local code: the view adds the shared `.sc-bg-fill` class +
`data-sc-bg-managed` and calls `sc_section_background_use()` (which on-demand
enqueues `sc-bg-fill.js` + `.css` in `wp_footer`). `window.scBgFill(el)` targets the
full-bleed **`<section>`** first (breaking out of the boxed `.fw-container`), marks
it `.sc-bg-host` (position:relative), moves the element in as the first child, and
lifts the Section's other children (`z-index:1`); it RETURNS the host so a
self-managing element (before/after) can bind pointer events to it. `data-sc-bg-managed`
tells the shared auto-init to skip the element (before/after inits it itself). The
shared `.sc-bg-fill` CSS uses `!important` on the layout props so the fill wins over
`.fw-bac{position:relative}` regardless of stylesheet load order.

**Comparison** (`sc_bac_render_comparison`) emits the original slider markup:
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
6. **Spotlight masks the BEFORE layer, not the after** — the "before" is on top and
   an *inverse* radial hole reveals the "after" beneath. So Before = what shows
   normally, After = revealed. `--sr-in` is `100 − softness` (0% softness = hard
   edge). The mask is dropped (`.is-away` / `mask:none`) when idle-reveal is off and
   the pointer is away — else the whole "before" would carry a permanent centre hole.
7. **Background mode needs Section height** — the fill is `position:absolute;inset:0`,
   so if the parent has no min-height it collapses. Options `help` says to give the
   Section a min-height. `ratio`/`max_width`/`rounded` are ignored in bg mode.
8. **Nested atts** — Comparison/Spotlight options live under `type/<type>/…`; always
   read them with the slash path, never at the top level (only images + the shared
   `as_background`/`ratio`/`max_width`/`rounded` + Styling colors are top-level).
9. **Background is the SHARED helper, not local code** — don't reintroduce a private
   `liftBackground`/`fw-bac-bg-host`; both are gone. Add `.sc-bg-fill` + call
   `sc_section_background_use()`, and (if the element needs the host) `window.scBgFill()`.
   Any other shortcode adopts a Section background the same way: `sc_section_background_field()`
   for the option + `.sc-bg-fill` class + `sc_section_background_use()`, and let the shared
   auto-init handle it (no `data-sc-bg-managed`, no JS).

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
- `views/view.php` — render (`sc_bac_render` → `sc_bac_render_comparison` / `sc_bac_render_spotlight`)
- `views/parts/registry.php` — design (skin) source of truth
- `static/css/styles.css` — base + all skins + spotlight mask + background fill
- `static/js/scripts.js` — comparison engine (`initOne`) + spotlight engine (`initSpot`); background via shared `window.scBgFill`
- (shared, extension-level) `includes/shortcode-background-helper.php` + `static/js/sc-bg-fill.js` + `static/css/sc-bg-fill.css` — the reusable Section-Background helper this element uses
- `static/img/page_builder.svg` — 16×16 Media Elements tile icon
- `static/img/design/<key>.svg` — 88×64 design-picker thumbnails
- `static/img/design/type-comparison.svg` / `type-spotlight.svg` — 88×64 Type multi-picker tiles
