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

Source of truth: `options.php`. Four tabs (Layout, Styling, Animations,
Advanced). The editor modal is `medium` (`config.php`'s `'popup_size' =>
'medium'`).

### Tab: Layout

Wrapped in `group_layout` (flattens on save).

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `variant` | `select` | `''` (Default) | A **Section Style** preset slug. Choices come from `unysonplus_section_style_choices()` (Theme Settings → Components → Section Styles), so user-defined styles appear automatically; falls back to `'' / alt / light / dark` if the getter is unavailable. Pairs with the Styling-tab Background |
| `is_fullwidth` | `switch` | (off) | On: the bg spans edge-to-edge (`fw-container-fluid`), content stays in the container. Off: the whole section is constrained (`fw-container`) |
| `container_width` | `multi-picker` (picker id `preset`) | `{ preset: 'inherit' }` | Constrain THIS section's content band narrower than the global Container Width (General → Layout). `preset` ∈ `inherit` / `narrow` (768px) / `medium` (896px) / `wide` (1024px) / `custom`. `custom` reveals `custom_width` (`unit-input`, units `px/rem/%/vw`, default `{value:900, unit:px}`) under `container_width.custom.custom_width`. `inherit` = no override. The view applies `max-width` + `margin:auto` inline on the `.fw-container`. Use for CTAs/prose/forms narrower than the page (source `mx-auto max-w-{3xl..6xl}`); the converter reads each section's `max-w-*` and sets this instead of per-element max-widths |
| `min_height` | `multi-picker` (picker id `preset`) | `{ preset: 'auto' }` | Minimum section height. `preset` ∈ `auto` / `40vh` / `60vh` / `80vh` / `100vh` / `custom`. `custom` reveals `custom_height` (`unit-input`, units `px/%/vh/vw/rem/em`, default `{value:600, unit:px}`) under `min_height.custom.custom_height`. `auto` emits no min-height |
| `column_halign` | `responsive` → `image-picker` | `{ base:'default', md:'', lg:'' }` | Columns Horizontal Alignment: `default` / `center` / `right` / `between` / `around` / `evenly`, per device (blank device inherits smaller) |
| `column_valign` | `image-picker` | `stretch` | Columns Vertical Alignment: `stretch` (Default / Stretched) / `top` / `center` / `bottom`. Most visible with a Min Height set. The renderer also tolerates the old key `content_valign` as a fallback |
| `reverse_columns` | `responsive` → `switch` (`no`/`yes`) | `{ base:'no', md:'', lg:'' }` | Column Order — reverse per device. Legacy scalar `all` / `tablet` / `mobile` migrates in the view |

### Tab: Styling

Three groups: `group_background`, `group_dividers`, `group_spacings`
(all flatten on save).

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `background` | `background-pro` | — | Stacked color / gradient / image / video background layers. **Replaces** the old separate `background_color` / `background_image` / `video` / `bg_color` fields — legacy sections are migrated in the view (see `includes/migration.php`) |
| `divider_top` | `multi-picker` (picker id `shape`) | `{ shape: 'none' }` | Top Shape Divider. `shape` ∈ `none` / `tilt` / `curve` / `wave` / `triangle`. Any non-`none` shape reveals sub-fields under `divider_top.{shape}.{…}` (see divider sub-fields below) |
| `divider_bottom` | `multi-picker` (picker id `shape`) | `{ shape: 'none' }` | Bottom Shape Divider — same shape choices + sub-fields, under `divider_bottom.{shape}.{…}` |
| `padding_top` | `sc_spacing_field` (prefix `pt`, `responsive`) | `{base,md,lg}` | Vertical padding above content, per device (blank device inherits smaller). Legacy scalar folds into `base` |
| `padding_bottom` | `sc_spacing_field` (prefix `pb`, `responsive`) | `{base,md,lg}` | Vertical padding below content, per device |
| `gap` | `responsive` → `short-select` | `{ base:'', md:'', lg:'' }` | Override the site-wide Default Gap (both H + V column gutter) for every Bootstrap row in this section, per device. Choices from `sc_get_gap_select_choices()`; empty = inherit |
| `gap_x` | `responsive` → `short-select` | `{ base:'', md:'', lg:'' }` | Horizontal-only gap override, per device. Only bites once `gap` is set |
| `gap_y` | `responsive` → `short-select` | `{ base:'', md:'', lg:'' }` | Vertical-only gap override, per device. Only bites once `gap` is set |

**Shape-divider sub-fields** (each revealed shape carries the same three,
stored under `divider_{top,bottom}.{shape}`):

| Sub-att | Type | Default | Description |
|---------|------|---------|-------------|
| `color` | `sc_color_field_compact` (kind `bg`) | — | Divider fill. Compact preset picker → `{predefined, custom}`; resolved to `var(--color-{slug})` or the custom hex (falls back to `#ffffff`) |
| `height` | `unit-input` (units `px/vh/%`) | `{value:'100', unit:'px'}` | Height of the divider band |
| `flip` | `switch` (`yes`/`no`) | `no` | Flip the shape horizontally (`scaleX(-1)`) |

### Tabs: Animations + Advanced

`sc_get_animation_fields()` (under tab `tab_animation`) +
`sc_get_advanced_tab()` (wrapped in `advanced_settings` group under
`tab_advanced`). Standard shared shape.

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
`is_fullwidth`, `min_height`, `column_valign`, `padding_top`,
`padding_bottom`, and the `background` (background-pro) value.

## Rendering

`views/view.php` outputs the section wrapper (`sc_build_wrapper_attr()`
base attrs + extra classes / inline style) with:

- `<section class="section section--{variant} ...">` — `variant` is
  sanitized to a css-safe slug and validated against
  `unysonplus_section_style_preset_slug_map()` (falls back to
  `alt / light / dark`); an unknown slug is dropped
- **Background** via `sc_bg_pro_style()` / `sc_bg_pro_video_attr()` on the
  `background` value — or, when that's empty, on the value synthesized by
  `section_migrate_legacy_background( $atts )` from the legacy
  `background_color` / `background_image` / `video` / `bg_color` atts. A
  video bg adds the `background-video` class + data-attrs
- **Min height + vertical alignment**: `min_height` resolves to a
  `min-height` inline style (preset string or `custom_height`
  `{value}{unit}`; tolerates a legacy plain string). `column_valign` =
  `stretch` adds `section--valign-stretch` (only with a min-height); `top`
  / `center` / `bottom` emit `display:flex;flex-direction:column;justify-content:{flex-start|center|flex-end};`
- **Columns horizontal alignment**: `column_halign` per-device → modifier
  classes `section--cols-{v}` / `section--cols-{md|lg}-{v}` (styles.css)
- **Reverse order**: `reverse_columns` per-device → `section--rev` +
  `section--rev-{md|lg}-{on|off}` overrides
- **Gap** modifier classes `section--gap[-md|-lg]-{slug}`,
  `section--gap-x[-md|-lg]-{slug}`, `section--gap-y[-md|-lg]-{slug}` that
  `framework/includes/css-tokens.php` turns into `--bs-gutter-x` /
  `--bs-gutter-y` overrides on every `.row` inside this section
- **Shape dividers**: hardcoded SVG paths (`tilt`/`curve`/`wave`/`triangle`)
  emitted as `.sc-shape-divider--{top|bottom}` before the content; top is
  rotated 180°, `flip` mirrors it. Adds `section--has-divider`
- **Inner content** (rendered rows / columns / shortcodes) wrapped in a
  `fw-container` (or `fw-container-fluid` when `is_fullwidth`). **Exception:**
  when `atts['has_inner_containers']` is set (the items-corrector lifted the
  section's own columns into a default `.fw-container` and kept Container
  elements as siblings), the content is rendered directly with **no** extra
  wrapper to avoid nesting

Frontend assets (enqueued by `static.php`, cache-busted with the
shortcodes-extension version; the formstone stack is used from v1.3.9,
the `jquery.fs.wallpaper` path is the pre-1.3.9 fallback):
- `static/css/styles.css` — section base styles
- `static/css/background.css` — bg-video presentation (formstone)
- `static/css/jquery.fs.wallpaper.css` — pre-1.3.9 fallback styles
- `static/js/core.js` + `transition.js` + `background.js` +
  `background.init.js` — formstone background stack (v1.3.9+)
- `static/js/scripts.js` + `jquery.fs.wallpaper(.min).js` — deprecated
  pre-1.3.9 fallback engine

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

1. **Background is one `background-pro` value, not separate fields** — the
   old `background_color` / `background_image` / `video` / `bg_color` atts
   are gone from `options.php`. They're only read by
   `section_migrate_legacy_background()` (in `includes/migration.php`) so
   pre-existing sections still render / pre-fill the new control until
   re-saved. New content should emit the `background` value directly.
2. **`min_height` / `divider_*` are multi-pickers, not flat** — the saved
   shape is `{ preset: … }` (+ `custom.custom_height` for `min_height`) and
   `{ shape: … }` (+ `{shape}.{color,height,flip}` for dividers). A legacy
   plain-string `min_height` throws in the modal unless migrated — the JS
   migrator (`scripts.js` `migrateMinHeight`, mirroring
   `section_migrate_min_height`) fixes it on editor load; the view tolerates
   the legacy scalar. Generators must reproduce the nesting.
3. **`gap` / `gap_x` / `gap_y` are per-device scale slugs, not px values** —
   `{ base, md, lg }` of slugs (e.g. `'3'`), emitting modifier classes like
   `section--gap-3` / `section--gap-md-3`. `framework/includes/css-tokens.php`
   writes the actual CSS custom properties. Don't emit raw pixel values.
   `gap_x` / `gap_y` only take effect once `gap` is set.
4. **`variant` is a Section Style slug validated at render** — an unknown
   slug (not in `unysonplus_section_style_preset_slug_map()`) is dropped, so
   only registered style slugs render a `section--{variant}` class.
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
3. Edit options → set `variant: dark` → reload → section gets the
   `section--dark` CSS class.
4. Set a `background` image/video layer → reload → the bg renders on the
   frontend (image or looping muted video).
5. Set `min_height: 100vh` + `column_valign: center` → reload → full-screen
   section with the columns block vertically centred; add a
   `divider_bottom` shape → an SVG edge renders at the bottom.
6. Save as template → appears in Templates → Sections list.
7. Export the template → import on another install → byte-identical
   restoration.
8. Inside a Full template → save / load round-trips with all inner rows
   / columns intact.

## Files

- `class-fw-shortcode-section.php` — main shortcode class with `_init()`
  hooks
- `config.php` — page-builder config (Layout Elements tab, "Section"
  title, `popup_size: medium`)
- `options.php` — 4 tabs (Layout, Styling, Animations, Advanced)
- `includes/migration.php` — legacy background → `background-pro`
  (`section_migrate_legacy_background`) + legacy string → multi-picker
  min-height (`section_migrate_min_height`) migration helpers, shared by
  the view + the page-builder item
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
