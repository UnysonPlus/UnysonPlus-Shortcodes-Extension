---
type: shortcode
name: hero_section
since: framework 2.8.x (early in the section-like lineage); 2.8.40 added the eager `fw_section_like_types` filter hook
provides: section-like
---

# Hero Section

A section-like shortcode that renders a full-bleed hero area with a
parallax background image and an optional color overlay. Behaves identically
to the built-in `[section]` in the page-builder canvas (root-level
container, holds rows, edits via the same modal-style options) — the
distinguishing piece is the rendered HTML, which adds a parallax-aware
background container and exposes hero-specific options (parallax strength,
overlay color, viewport-height presets).

This shortcode is the canonical reference implementation of the section-like
recipe at `../AGENTS.md`. When creating new section-like shortcodes, model
the structure here.

## Registration

`class-fw-shortcode-hero_section.php` hooks two things in `_init()`:

| Hook | Purpose | Lifecycle |
|------|---------|-----------|
| `fw_option_type_builder:page-builder:register_items` action → `_action_register_builder_item_types()` | Lazy `require` of the page-builder item class file when the editor renders | Fires once per editor admin-page render |
| `fw_section_like_types` filter → `_filter_register_section_like()` | Eager registration of `hero_section` as section-like for EVERY PHP request | Fires whenever any code applies the filter — including admin-ajax handlers, items corrector during post save, etc. |

Plus one more filter to expose the shortcode's data to the frontend collector:

| Hook | Purpose |
|------|---------|
| `fw_ext:shortcodes:collect_shortcodes_data` filter → `_filter_add_hero_section_data()` | Adds `hero_section`'s options metadata to the frontend bundle so edit-from-frontend works |

The page-builder item class `Page_Builder_Hero_Section_Item` lives in
`includes/page-builder-hero_section-item/class-page-builder-hero-section-item.php`.
It's `require`d (not `require_once`d) into existence by the lazy action
handler above; the file-scope
`FW_Option_Type_Builder::register_item_type('Page_Builder_Hero_Section_Item')`
call works because the action fires exactly once per editor render.

`Page_Builder_Hero_Section_Item` extends `Page_Builder_Section_Like_Item`
(defined at
`framework/extensions/shortcodes/extensions/page-builder/includes/page-builder/includes/item-types/class-page-builder-section-like-item.php`)
and overrides only `get_type()` to return `'hero_section'`. That base class
handles `FW_Section_Like_Registry::register()`, the items-corrector
opt-outs (`disable-builder-item-correction:hero_section`,
`manual-builder-item-correction:hero_section`), the `_items` recursion as a
section, and the shared editor static-asset enqueue scope.

## Options schema (atts)

Source of truth: `options.php` (always check the file for the current
shape — the table below is a snapshot for AI generators / documentation
consumers).

Three tabs in the edit modal:

### Tab: Layout

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `is_fullwidth` | `switch` (`yes` / `no`) | `yes` | Full-width hero (breaks out of any container width constraint) |
| `min_height` | `select` | `60vh` | Vertical space the hero occupies — choices: `40vh` (Compact), `60vh` (Medium), `80vh` (Tall), `100vh` (Full Viewport) |
| `content_vertical_align` | `select` | `center` | Flexbox `align-items` for inner content — choices: `flex-start` (Top), `center`, `flex-end` (Bottom) |

### Tab: Background

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `background_image` | `upload` | — | Image used for the parallax effect (WP attachment) |
| `parallax_strength` | `slider` (0–1, step 0.05) | `0.4` | How much the background moves relative to scroll. `0` = static, `1` = full speed |
| `overlay_color` | `color-picker` (rgba) | `rgba(0,0,0,0.35)` | Color drawn on top of the background image — use transparency |
| `background_color` | `color-picker` | `''` | Fallback background when no image is set |

### Tab: Advanced

Wraps the shared `sc_get_advanced_tab()` group (CSS class, CSS ID, custom
CSS, animation, visibility settings) under an `advanced_settings` group key.
The exact fields depend on what `sc_get_advanced_tab()` returns at runtime
(it's a helper from the shortcodes extension); standard fields include
`css_id`, `css_class`, `animation`, `visibility`.

### Producing valid `atts` for an AI generator

For AI-generated `_fw_template_export` payloads, an `atts` object for a
`hero_section` should be shaped like:

```json
{
  "is_fullwidth": "yes",
  "min_height": "60vh",
  "content_vertical_align": "center",
  "background_image": { "attachment_id": 0, "url": "…" },
  "parallax_strength": 0.4,
  "overlay_color": "rgba(0,0,0,0.35)",
  "background_color": "",
  "advanced_settings": { /* sc_get_advanced_tab() fields */ }
}
```

Defaults are safe omissions — the page-builder fills them in from
`default_values` on load.

## Rendering

`views/view.php` outputs the frontend HTML. The view receives `$atts`
(decoded options) and `$content` (already-rendered inner rows / columns /
simples after the items corrector + shortcode-rendering pass). Output
structure (approximate — check `views/view.php` for the canonical form):

```html
<section class="fw-hero-section{ if !is_fullwidth: ' container' }"
         style="min-height: {min_height};
                {if background_color: 'background-color: {background_color};'}">
  <div class="fw-hero-section__bg"
       style="background-image: url({background_image.url});"
       data-parallax-strength="{parallax_strength}">
    <div class="fw-hero-section__overlay"
         style="background-color: {overlay_color};"></div>
  </div>
  <div class="fw-hero-section__content"
       style="align-items: {content_vertical_align};">
    {$content}  <!-- rendered rows / columns / simples -->
  </div>
</section>
```

The parallax effect is implemented by `static/js/hero-section.js`, which
attaches a `scroll` listener and translates `.fw-hero-section__bg` by a
function of scroll position × `data-parallax-strength`. No editor-side JS
is needed beyond what `Page_Builder_Section_Like_Item::enqueue_static()`
auto-enqueues.

## Pitfalls

1. **Frontend collector dependency** —
   `_filter_add_hero_section_data()` adds this shortcode's data to
   `fw_ext:shortcodes:collect_shortcodes_data`. Without it, the frontend
   bundle doesn't include the shortcode's options metadata and
   edit-from-frontend won't work. Keep the filter hooked.
2. **Eager filter is mandatory** —
   `_filter_register_section_like()` on `fw_section_like_types` is what
   makes admin-ajax (template save / import) recognize `hero_section`. The
   lazy `register_items` action alone is not enough; see `../AGENTS.md`
   "Common pitfalls" #1.
3. **Icon fallback** — `get_shortcode_config()` looks for
   `/static/img/page_builder.svg` in this shortcode's folder; if missing,
   falls back to the built-in `[section]` icon. If you want the Layout
   Elements thumbnail to look distinct, ship a real icon at that path.
4. **`column-title` is what the section-sorter reads** — the rendered
   editor view uses the shared `section-like-factory.js` template which
   puts `templateData.title` into `.column-title`. The section-sorter
   reads `.column-title` for its row label. So `get_shortcode_config()`'s
   `title` field should be user-friendly (`Hero Section`, not
   `hero_section`).
5. **`min_height` is a `vh` string, not a number** — choices like `'60vh'`
   include the unit. The view inlines it directly into the
   `style="min-height: ..."` attribute. Don't strip the unit.

## Verification

Generic section-like verification from `../AGENTS.md` applies. Hero-section
specifics:

1. Drag a Hero Section into the canvas → renders with `min_height: 60vh`,
   centered content alignment, the default dark overlay (rgba 0,0,0,0.35).
2. Edit options → set a `background_image` → save → reload → image renders
   with the configured parallax strength.
3. Scroll the front-end page where the hero is placed — the background
   shifts at the configured speed; setting `parallax_strength: 0` should
   produce a static background.
4. Save as Section template → confirm appears in Templates → Sections list
   (this path was broken before framework 2.8.40 — the eager filter fixed
   it).
5. Export → Import the saved template → restored Hero Section is byte-
   identical (same atts, same `created` timestamp).
6. Inside Full Template save / load → hero_section round-trips with its
   inner rows / columns intact.

## Files

- `class-fw-shortcode-hero_section.php` — main shortcode class
- `config.php` — page-builder config (tab, title, description, popup
  metadata)
- `options.php` — section edit-modal fields (the atts schema documented
  above)
- `static.php` — frontend CSS/JS enqueue
- `views/view.php` — frontend HTML template
- `static/css/hero-section.css` — frontend styles
- `static/js/hero-section.js` — frontend parallax scroll handler
- `includes/page-builder-hero_section-item/class-page-builder-hero-section-item.php` —
  page-builder item class
