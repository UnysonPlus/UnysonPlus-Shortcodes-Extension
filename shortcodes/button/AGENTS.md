---
type: shortcode
name: button
since: original Unyson (heavily modified in the Unyson+ fork)
provides: leaf-shortcode
---

# Button

Renders a styled link / button with a preset-driven look (sourced from
Theme Settings → General → Buttons), optional icon, width/alignment
controls, button state (normal/active/disabled), and a hover-animation
catalog. The Components tab's canonical "call to action" element.

## Registration

Button has no `class-fw-shortcode-button.php` file — leaf shortcode, default
`FW_Shortcode` auto-instantiated by Unyson's loader. No page-builder item
class needed; renders inside columns via the `[simple]` item path. Add a
class file only if custom registration hooks (AJAX, filters) are needed.

`config.php` (`tab` = **Components**, `popup_size` = `large`) declares a rich
`title_template` that renders the label as a filled button-chip in the
page-builder canvas — blue background, corner radius echoing the chosen
`shape` — with the icon drawn inline (svg `markup` resized to 14px, or a
font-icon `<i>` / emoji / uploaded `<img>`) before or after the label per
`icon_position`, so a Button reads as a button in the builder. It's a builder
affordance only and does NOT mirror the frontend skin.

## Options schema (atts)

Source of truth: `options.php`. Four tabs:

### Tab: Content

Wrapped in a `group_content` group (flattens on save).

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `label` | `text` | `Submit` | Button label — **accepts inline HTML** (e.g. a trailing `<svg>`) |
| `link` | `text` | `#` | Target URL (any href) |
| `target` | `switch` | `_self` | `_blank` (new window) or `_self` (same window) |
| `icon` | `icon-v2` | `{"type":"none"}` | Icon — an **object** (`{type:"none"}` / `{type:"icon-font",…}` / `{type:"custom-upload","attachment-id","url"}`), **never a plain string**. See verified snippet below |
| `icon_position` | `select` | `after` | `before` or `after` — relative to the label |

### Tab: Styling

Wrapped in `group_options` (most fields) and `group_spacings` (the spacing
field). Both flatten on save.

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `style` | `button-style-picker` | first preset from `sc_get_button_style_choices()` (typically `primary`) | Button style preset — drives all color/border/gradient. `allow_none: false` (a styleless button is rarely intended; use the `link` preset for text-only) |
| `size` | `button-style-picker` | (first from `sc_get_button_size_choices()`) | Button size preset — sourced from Theme Settings → Buttons → Sizes |
| `shape` | `image-picker` | `default` (keep the Size radius) | Corner rounding OVERRIDE — `pill` / `rounded` / `square` emit `.btn-shape-{v}` which beats the Size preset's radius (see `static/css/styles.css`); the `default` choice adds no class. Choices are picker tiles from `static/img/shapes/*.svg`. Decouples "pill" from "large" |
| `width.mode` | `multi-picker` (`''` / `w-100` / `custom`) | `''` (auto) | Width strategy: auto-fit, full-width, or custom |
| `width.custom.custom_width` | `unit-input` (px / % / rem / em / vw) | — | Only present when `width.mode === 'custom'`. Saved as `{ value, unit }` |
| `alignment` | `select` (`''` / `left` / `center` / `right`) | `''` (inherit) | Wraps in a `<div class="sc-btn-align" style="text-align: {value}">` when set to a non-empty value. No visual effect when `width.mode === 'w-100'` |
| `state` | `select` (`''` / `active` / `disabled`) | `''` (normal) | Renders with the corresponding Bootstrap state class |
| `hover_animation` | `button-hover-animation` | (first from `sc_get_hover_animation_choices()`) | A `.btnfx-*` effect class added on top of the style preset — animates transform/shadow only, doesn't override colors |
| `spacing` | `sc_spacing_field` (mode: margin) | — | Wrapper margin only (padding comes from the size preset, not exposed here) |

### Tab: Animations

`sc_get_animation_fields()` — shared entry-animation set.

### Tab: Advanced

`sc_get_advanced_tab()` wrapped in `advanced_settings` group. Flattens to
top-level `$atts` (`css_id`, `css_class`, `responsive_hide`, etc.).

### Verified `atts` (real export)

From `button-test-section-6f2a34c9.json` (5 buttons, plugin 2.10.26). The shared
`animation`/`spacing` blocks + common keys live in the page-builder playbook
(`../../extensions/page-builder/AGENTS.md` §3). Button-specific serialized shapes:

- **`label` accepts inline HTML** — e.g. text followed by an inline `<svg>…</svg>` (button[3]).
- **`icon` is an OBJECT (icon-v2), never a plain string.** Three forms seen:
  - none — `{"type":"none"}`
  - font icon — `{"type":"icon-font","icon-class":"dashicons dashicons-arrow-right","icon-class-without-root":"dashicons-arrow-right","pack-name":"dashicons","pack-css-uri":"…/dashicons.min.css"}`
  - uploaded image — `{"type":"custom-upload","attachment-id":"453","url":"https://…/img.jpeg"}`
- **`style`** = a button **color-preset class** — `btn-{slug}` / `btn-{slug}-outline` / `btn-gradient` (seen: `btn-success`, `btn-gradient`, `btn-warning`, `btn-warning-outline`). **`size`** = `btn-{xs|sm|md|lg|xl}`.
- **`width`** = `{"mode":"w-100"|"custom"|"","custom":{"custom_width":{"value":"80","unit":"%"}}}` (custom_width only meaningful when `mode:"custom"`).
- **`target`** = `_blank|_self` · **`icon_position`** = `before|after` · **`alignment`** = `left|center|right|""`.
- **`state`** = `active|disabled` (`""` = normal). **`hover_animation`** = a `.btnfx-*` class: built-in (`btnfx-glow`/`btnfx-meet`/`btnfx-sweep`) or custom `btnfx-c-{slug}` (e.g. `btnfx-c-pulse-ring`).
- **`spacing.advanced`** is a **breakpoint map when set** (general — every `spacing` field): `{"lg":{"margin":{"top":"mt-lg-7","right":"me-lg-8","bottom":"mb-lg-8","left":"ms-lg-5"},"padding":{…}},"md":{…}}` — responsive spacing utility classes; `[]` when unused.
- **`custom_attrs`** populated = `[{"name":"aria-label","value":"test"}]`; **`responsive_hide`** populated = `{"hide-xs":true}` (both general — §3).

Minimal generated `atts` (defaults safe to omit — the builder fills them from `default_values`):

```json
{"label":"Get started","link":"https://example.com/signup","target":"_blank","icon":{"type":"none"},"icon_position":"after","style":"btn-primary","size":"btn-lg","width":{"mode":"","custom":{"custom_width":{"value":"","unit":"px"}}},"alignment":"center","state":"","hover_animation":"","spacing":{"margin":{"all":"","top":"","right":"","bottom":"","left":""},"padding":{"all":"","top":"","right":"","bottom":"","left":""},"advanced":[]},"unique_id":"<32hex>","css_id":"","css_class":"","custom_css":"","responsive_hide":[],"custom_attrs":[]}
```

## Rendering

`views/view.php` composes:

- Always an `<a>` element (never a `<button>`) with classes:
  `btn btn-{style} btn-{size}` plus `btn-shape-{pill|rounded|square}` (when
  shape overrides), any width mode (`w-100`), state (`active` / `disabled`),
  hover-animation class (`btnfx-*`, guarded by `/^btnfx-[a-z0-9-]+$/`), a
  per-element unique class `btn-{hex}`, and `has-icon` when an icon renders.
- The label — run through `sc_button_kses_label()` (a widened `wp_kses` that
  permits an inline `<svg>` + basic inline formatting so a pasted-SVG label
  renders, while stripping scripts/handlers) — with the icon (via
  `sc_icon_render()`) prepended or appended based on `icon_position`.
- `state: 'disabled'` also emits `aria-disabled="true" tabindex="-1"` on the
  `<a>` (plus the `.disabled` class).
- `width.mode === 'custom'` → an inline `width: {value}{unit}` style
  (`FW_Option_Type_Unit_Input::to_string`) appended to the `<a>`'s style.
- Wrapped in a `<div class="sc-btn-align" style="text-align: {alignment}">`
  when alignment is set to left/center/right.
- Wrapper margin from the `spacing` field auto-applies as `m-*`/`p-*` utility
  classes via the `sc_apply_styling_classes` filter (padding comes from the
  Size preset). `static.php` also enqueues only the `icon-v2` pack the chosen
  icon needs.

Frontend CSS: `static/css/styles.css` for the base button, plus
`static/css/hover-fx.css` for the `.btnfx-*` effect classes. The hover-fx
file is also loaded in the admin options form (via the `fx_css` config on
the `hover_animation` field) so previews animate inside the modal.

No frontend JS — animations are pure CSS.

## Pitfalls

1. **Style picker defaults to the first preset, not "None"** — explicitly
   set via `allow_none: false` because a styleless button rarely renders
   correctly. If you want a text-only button, use the `link` preset.
2. **Width is a multi-picker** — the `width.custom.custom_width` sub-field
   only exists when `width.mode === 'custom'`. Generators must preserve
   the nested shape (won't be flattened by Unyson's group-collector).
3. **Alignment is inert under full-width** — the view branches: when
   `width.mode === 'w-100'`, the alignment wrapper is skipped. AI
   generators don't need to special-case this (set both freely; the view
   handles it), but a docs reader debugging "alignment doesn't work" should
   know.
4. **Size presets ride a primary base for preview** — the option's
   `preview_base: 'btn btn-primary'` is admin-only; in the rendered
   output the actual chosen `style` preset's classes apply, NOT
   `btn-primary`.
5. **The `state: 'disabled'` value** — adds the `.disabled` class AND
   emits `aria-disabled="true" tabindex="-1"` on the `<a>` (the element
   is still an anchor, so `pointer-events: none` in `styles.css` is what
   actually blocks the click). It's a visual/AT state, not a security gate.

## Verification

1. Drag Button from Components → renders as a primary button
   labeled "Submit" linking to `#`.
2. Edit options → set label, link, icon (after) → reload → button shows
   text + icon after.
3. Switch `style` preset → button takes the new preset's colors.
4. Set `width.mode: w-100` → button spans the column.
5. Set `width.mode: custom`, `custom_width: { value: 240, unit: 'px' }` →
   button is exactly 240px wide.
6. Set `hover_animation: btnfx-shine` (or similar) → hovering the front-
   end button animates without color change.
7. Save as template (Sections tab won't accept it — leaf, not
   section-like — but works in Full templates).

## Files

- `config.php` — page-builder config (`Components` tab, large
  popup, rich in-canvas button-chip `title_template`)
- `options.php` — 4 tabs (Content, Styling, Animations, Advanced)
- `static.php` — frontend CSS enqueue (`styles.css` + `hover-fx.css`)
- `views/view.php` — frontend HTML
- `static/css/styles.css` — base button styles
- `static/css/hover-fx.css` — `.btnfx-*` hover animations (also loaded
  in admin via the `hover_animation` option's `fx_css` config)
- `static/img/shapes/{default,pill,rounded,square}.svg` — the `shape`
  image-picker tiles
- `static/img/page_builder.svg` — Components thumbnail

No JS, no admin-side asset, no item class — standard leaf layout.
