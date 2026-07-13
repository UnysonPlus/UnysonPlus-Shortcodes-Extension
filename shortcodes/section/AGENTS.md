---
type: shortcode
name: section
since: original Unyson (canonical section-like; `FW_Section_Like_Registry`'s built-in default)
provides: section-like
---

# Section

The **canonical built-in section** — root-level container that holds rows,
which in turn hold columns, which hold leaf shortcodes. Every full-page
template the page builder generates is one or more `[section]` (or
section-like) shortcodes at root, with their content auto-corrected into
proper row/column structure by the items corrector at save time.

This is the **reference implementation** for the section-like family. The
section-like variants (`hero_section`, future `parallax_section`, etc.)
extend the same `Page_Builder_Section_Like_Item` base class and share most
of the editor + storage + items-corrector behavior. See `../AGENTS.md` for
the recipe.

## Registration

`class-fw-shortcode-section.php` declares `FW_Shortcode_Section extends
FW_Shortcode` with `_init()` hooking:

| Hook | Purpose |
|------|---------|
| `fw_option_type_builder:page-builder:register_items` action → `_action_register_builder_item_types()` | Lazy `require` of `Page_Builder_Section_Item` when the editor renders |
| `fw_ext:shortcodes:collect_shortcodes_data` filter → `_filter_add_section_data()` | Exposes section's options metadata to the frontend collector |

**No `fw_section_like_types` filter hook is needed** for the built-in
`section` type — `FW_Section_Like_Registry` hardcodes `'section'` as its
default value:

```php
private static $types = array( 'section' );
```

So `section` is registered as section-like in every PHP request without any
filter being applied. Custom section-like shortcodes (hero_section etc.)
must add the filter; only the canonical built-in is exempt.

`Page_Builder_Section_Item` is the page-builder item class. It's `require`d
(not `require_once`d) into existence by the lazy action handler. The
file-scope `FW_Option_Type_Builder::register_item_type(...)` call works
because the action fires once per editor render. The item class extends
`Page_Builder_Section_Like_Item` (`section` was originally the only such
item; the base class was factored out to support custom variants).

## Options schema (atts)

Source of truth: `options.php`. Six tabs.

### Tab: Layout

Wrapped in `group_layout` (flattens on save).

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `variant` | `select` (`''` / `alt` / `light` / `dark`) | `''` (Default) | Named visual preset. Pairs with `background_color` — variant sets the overall theme, bg-color overrides for one-off colors |
| `is_fullwidth` | `switch` | (off) | Full-bleed (breaks out of container width) |
| `background_color` | `color-picker` | — | Legacy field from original Unyson — kept for backwards compatibility. Prefer `variant` or the preset `bg_color` on the Styling tab |
| `background_image` | `background-image` | — | Parallax background (with bleed / position / repeat sub-options) |
| `video` | `text` (URL) | — | Background video URL |

### Tab: Bleed Layout

A multi-picker gated by `bleed_layout.bleed_enabled` (`no` / `yes`). When
enabled, splits the section into a content side (with bg color) and a
full-bleed image side. Wrapped in `group_bleed_layout`.

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `bleed_layout.bleed_enabled` | `switch` (`no` / `yes`) | `no` | Master enable for bleed layout |
| `bleed_layout.yes.bleed_bg_color` | `color-picker` | — | Content side bg color (bleeds to viewport edge) |
| `bleed_layout.yes.bleed_image` | `upload` | — | Image filling the opposite side, bleeding to viewport edge |
| `bleed_layout.yes.bleed_image_position` | `select` (9 positions) | `center` | CSS `background-position` for the image |
| `bleed_layout.yes.bleed_image_side` | `select` (`right` / `left`) | `right` | Which side the image is on |
| `bleed_layout.yes.bleed_image_ratio` | `select` (11 ratios from `1-11` to `11-1`) | `5-7` | Image vs content width split using col-md-N pairs |
| `bleed_layout.yes.bleed_vertical_align` | `select` (`align-items-start` / `center` / `end`) | `align-items-center` | Content vertical alignment |
| `bleed_layout.yes.bleed_content_padding` | `select` (`0` / `2rem` / `3rem` / `5rem`) | `3rem` | Vertical content padding |
| `bleed_layout.yes.bleed_mobile_stacking` | `select` (`content-first` / `image-first`) | `content-first` | Mobile stacking order |

The `bleed_illustration` field is an `html-full` (no value) that renders
the visual reference diagram in the modal. It's metadata-only — no atts
contribution.

### Tab: Spacing & Style

Wrapped in `group_colors` + `group_spacings` (both flatten on save).

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `bg_color` | `sc_color_field_compact` (kind: bg) | — | Preset background color from theme palette OR custom hex via inline picker. Layers over the legacy `background_color` on the Layout tab |
| `padding_top` | `sc_spacing_field` (prefix: `pt`, `responsive`) | `{base,md,lg}` | Vertical padding above section content — per-device (Phone/Tablet/Desktop). Legacy scalar folds into `base` |
| `padding_bottom` | `sc_spacing_field` (prefix: `pb`, `responsive`) | `{base,md,lg}` | Vertical padding below — per-device. Legacy scalar folds into `base` |
| `gap` | `responsive` | `{base,md,lg}` | Override site-wide Default Gap for every Bootstrap row inside this section. Sets both horizontal + vertical gutter, per-device |
| `gap_x` | `responsive` | `{base,md,lg}` (inherit `gap`) | Horizontal-only gap override, per-device |
| `gap_y` | `responsive` | `{base,md,lg}` (inherit `gap`) | Vertical-only gap override, per-device |

### Tabs: Animations + Advanced

`sc_get_animation_fields()` + `sc_get_advanced_tab()`. Standard shared
shape.

### Generator note

For AI-generated `_fw_template_export` payloads (full-page templates), a
minimal Section can be just a wrapper around its `_items` array — no atts
needed since every field defaults to a usable value:

```json
{
  "type": "section",
  "atts": {},
  "_items": [
    /* row items, which contain column items, which contain leaf shortcodes */
  ]
}
```

For richer sections, generate the high-impact fields: `variant`,
`is_fullwidth`, `bg_color`, `padding_top`, `padding_bottom`,
`background_image`, plus the bleed-layout multi-picker if a split design
is desired.

## Rendering

`views/view.php` outputs the section wrapper with:

- `<section class="section section--{variant} {is_fullwidth class} ...">`
- Bg color / bg image / bg video layers
- The bleed-layout split-screen markup when enabled (a 2-column row inside
  the section with the bleed image filling one side via negative-margin
  trickery)
- Inner content (the rendered rows / columns / shortcodes) wrapped in a
  Bootstrap container (or NOT, when `is_fullwidth` is on)
- Gap modifier classes (`section--gap-{slug}`, `section--gap-x-{slug}`,
  `section--gap-y-{slug}`) that `framework/includes/css-tokens.php`
  translates into `--bs-gutter-x` / `--bs-gutter-y` overrides on every
  `.row` inside this section

Frontend assets:
- `static/css/styles.css` — section base styles
- `static/css/background.css` — bg-image / bg-video presentation
- `static/css/jquery.fs.wallpaper.css` — parallax library styles
- `static/js/scripts.js` — section JS entry
- `static/js/background.init.js` + `background.js` — bg media setup
- `static/js/transition.js` + `core.js` — additional UX
- `static/js/jquery.fs.wallpaper.js` (+ min) — parallax engine

## Editor integration

`includes/page-builder-section-item/class-page-builder-section-item.php`
declares `Page_Builder_Section_Item extends Page_Builder_Section_Like_Item`.
Its editor view template (the one rendered on the canvas) is shared with
all section-like variants via `section-like-factory.js` — it renders
`templateData.title` into `.column-title`, which the section sorter reads.

`includes/template-component/` declares the "Sections" template-component
class — the entry behind Templates → Sections in the dropdown. Save / load /
delete / export / import handlers + JS live here. **This is where the
fix for hero_section landed (framework 2.8.40):** the inner-type
validation now consults `apply_filters('fw_section_like_types',
array('section'))` so all section-like variants pass.

## Pitfalls

1. **`background_color` (legacy) vs `bg_color` (preset)** — both still
   exist for backwards compatibility. The view layers them: legacy
   `background_color` is applied first, preset `bg_color` (Styling tab)
   layers on top if set. AI generators should prefer `bg_color` for new
   content.
2. **Bleed layout is a multi-picker, not flat** — the sub-fields live
   under `bleed_layout.yes.{…}` only when `bleed_enabled === 'yes'`. The
   nested shape is preserved through save / load. Generators must
   reproduce the nesting.
3. **`gap` is a scale slug, not a px value** — emits modifier classes
   like `section--gap-3`. `framework/includes/css-tokens.php` writes the
   actual CSS custom properties. Don't try to emit raw pixel values
   here.
4. **The bleed-image `ratio` field uses Bootstrap `col-md-N` notation** —
   `5-7` means `col-md-5 + col-md-7`. Total always sums to 12. Generators
   must keep the sum invariant if they emit custom ratios.
5. **Section auto-correction** — the items corrector wraps non-section
   root items in synthetic `[section]` shortcodes on save. So if you
   place a `[text-block]` directly at root in the AI output, it'll be
   wrapped in a section anyway. Avoid this in generated content — emit
   explicit `[section]` wrappers so the structure is predictable.

## Verification

1. Drag Section from Layout Elements → renders an empty section on the
   canvas with the "Section" label.
2. Drop a Row into the section → drops successfully, the corrector
   auto-creates the row structure on save.
3. Edit options → set `variant: dark` → reload → section gets the dark
   variant CSS class.
4. Set a `background_image` → reload → parallax bg renders on the
   frontend.
5. Enable bleed layout → set `bleed_image_side: left`, `bleed_image_ratio:
   4-8` → reload → split-screen with image on left at col-md-4 + content
   at col-md-8.
6. Save as template → appears in Templates → Sections list.
7. Export the template → import on another install → byte-identical
   restoration.
8. Inside a Full template → save / load round-trips with all inner rows
   / columns intact.

## Files

- `class-fw-shortcode-section.php` — main shortcode class with `_init()`
  hooks
- `config.php` — page-builder config (Layout Elements tab, "Section"
  title)
- `options.php` — 6 tabs (Layout, Bleed Layout, Spacing & Style,
  Animations, Advanced)
- `static.php` — frontend CSS/JS enqueue
- `views/view.php` — frontend HTML
- `static/css/{styles, background, jquery.fs.wallpaper}.css` — frontend
  styles
- `static/js/{scripts, core, background, background.init, transition,
  jquery.fs.wallpaper, jquery.fs.wallpaper.min}.js` — frontend JS suite
- `includes/page-builder-section-item/class-page-builder-section-item.php` —
  page-builder item class (extends `Page_Builder_Section_Like_Item`)
- `includes/template-component/class-fw-ext-builder-templates-component-section.php` —
  Templates → Sections component (save / load / delete / export / import
  handlers); shared by ALL section-like variants
- `includes/template-component/init.php` — registers the template component
- `includes/template-component/scripts.js` — JS for the save-as-template
  control + per-row export icon + import button
- `includes/template-component/styles.css` — template-component admin
  styles

The template-component files are unique to `section/` — section-like
variants like `hero_section` reuse them by virtue of the
`fw_section_like_types` filter (Templates → Sections accepts all
section-like types, not just `section`). Don't duplicate this directory
in a new section-like shortcode's folder.
