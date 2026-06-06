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
catalog. The Content Elements tab's canonical "call to action" element.

## Registration

Button has no `class-fw-shortcode-button.php` file — leaf shortcode, default
`FW_Shortcode` auto-instantiated by Unyson's loader. No page-builder item
class needed; renders inside columns via the `[simple]` item path. Add a
class file only if custom registration hooks (AJAX, filters) are needed.

`config.php` declares a `title_template` of
`'<span class="fw-btn">{{= o.label }}</span>'` so the page-builder canvas
shows the button's label as the in-canvas item title.

## Options schema (atts)

Source of truth: `options.php`. Four tabs:

### Tab: Content

Wrapped in a `group_content` group (flattens on save).

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `label` | `text` | `Submit` | Button label text |
| `link` | `text` | `#` | Target URL (any href) |
| `target` | `switch` | `_self` | `_blank` (new window) or `_self` (same window) |
| `icon` | `icon-v2` | — | Icon class via the v2 icon picker (modal preview) |
| `icon_position` | `select` | `after` | `before` or `after` — relative to the label |

### Tab: Styling

Wrapped in `group_options` (most fields) and `group_spacings` (the spacing
field). Both flatten on save.

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `style` | `button-style-picker` | first preset from `sc_get_button_style_choices()` (typically `primary`) | Button style preset — drives all color/border/gradient. `allow_none: false` (a styleless button is rarely intended; use the `link` preset for text-only) |
| `size` | `button-style-picker` | (first from `sc_get_button_size_choices()`) | Button size preset — sourced from Theme Settings → Buttons → Sizes |
| `width.mode` | `multi-picker` (`''` / `w-100` / `custom`) | `''` (auto) | Width strategy: auto-fit, full-width, or custom |
| `width.custom.custom_width` | `unit-input` (px / % / rem / em / vw) | — | Only present when `width.mode === 'custom'`. Saved as `{ value, unit }` |
| `alignment` | `select` (`''` / `left` / `center` / `right`) | `''` (inherit) | Wraps in a `text-align: {value}` div when set. No effect when `width.mode === 'w-100'` |
| `state` | `select` (`''` / `active` / `disabled`) | `''` (normal) | Renders with the corresponding Bootstrap state class |
| `hover_animation` | `button-hover-animation` | (first from `sc_get_hover_animation_choices()`) | A `.btnfx-*` effect class added on top of the style preset — animates transform/shadow only, doesn't override colors |
| `spacing` | `sc_spacing_field` (mode: margin) | — | Wrapper margin only (padding comes from the size preset, not exposed here) |

### Tab: Animations

`sc_get_animation_fields()` — shared entry-animation set.

### Tab: Advanced

`sc_get_advanced_tab()` wrapped in `advanced_settings` group. Flattens to
top-level `$atts` (`css_id`, `css_class`, `responsive_hide`, etc.).

### Generator note

For AI-generated `_fw_template_export` payloads, an `atts` object for a
button should produce flat top-level keys for everything except the
`width` multi-picker (preserves its nested shape) and the
`icon` field (single string class). Example shape:

```json
{
  "label": "Get started",
  "link": "https://example.com/signup",
  "target": "_blank",
  "icon": "fas fa-arrow-right",
  "icon_position": "after",
  "style": "primary",
  "size": "lg",
  "width": { "mode": "", "custom": { "custom_width": { "value": "", "unit": "px" } } },
  "alignment": "center",
  "state": "",
  "hover_animation": "btnfx-shine",
  "spacing": { "all": "", "top": "", "right": "", "bottom": "", "left": "" },
  "css_id": "",
  "css_class": ""
}
```

Defaults are safe omissions — the page-builder fills them in from
`default_values` on load.

## Rendering

`views/view.php` (not read in this draft — refer to file for canonical
form) composes:

- An `<a>` (or `<button>` in some states) with classes:
  `btn btn-{style} btn-{size}` plus any width mode (`w-100`), state
  (`active` / `disabled`), and hover-animation class (`btnfx-*`).
- The label, with the icon prepended or appended based on `icon_position`.
- Wrapped in a `<div style="text-align: {alignment}">` when alignment is
  set and width is NOT `w-100`.
- Wrapper margin from the spacing field is applied as inline style or
  responsive utility class.

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
5. **The `state: 'disabled'` value** — drives a Bootstrap `.disabled`
   class but the `<a>` element is still clickable in the browser unless
   you also set `aria-disabled` + `pointer-events: none` in CSS. Check
   the view if "disabled" looks clickable on the front-end.

## Verification

1. Drag Button from Content Elements → renders as a primary button
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

- `config.php` — page-builder config (`Content Elements` tab, large
  popup, in-canvas `title_template`)
- `options.php` — 4 tabs (Content, Styling, Animations, Advanced)
- `static.php` — frontend CSS enqueue (`styles.css` + `hover-fx.css`)
- `views/view.php` — frontend HTML
- `static/css/styles.css` — base button styles
- `static/css/hover-fx.css` — `.btnfx-*` hover animations (also loaded
  in admin via the `hover_animation` option's `fx_css` config)
- `static/img/page_builder.png` — Layout Elements thumbnail

No JS, no admin-side asset, no item class — standard leaf layout.
