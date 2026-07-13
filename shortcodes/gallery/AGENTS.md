---
type: shortcode
name: gallery
since: shortcodes 1.4.86
provides: leaf-shortcode
---

# Gallery

A flexible, multi-design image gallery. Images are picked with the `multi-upload`
(images-only) option type; captions / alt / titles are read from the Media
Library. Eighteen self-contained designs (Grid, Masonry, Justified Rows, Metro /
Bento, Carousel, Polaroid, Showcase, Cards, Slideshow/Fade, Thumbnail Slider,
Coverflow, Marquee/Ticker, Filmstrip, Spotlight, Honeycomb, Image Accordion,
Flip Cards, Stack/Banners) plus a dependency-free lightbox shared by all of them.
Lives in the **Media Elements** page-builder tab.

The slider designs (Carousel, Slideshow, Coverflow) share one Splide mount
(`designs/carousel.js`, triggered by `data-fw-splide` on the root); Thumbnail
Slider syncs two Splides via its own `designs/thumbnail-slider.js`. Shared Splide
arrow/pagination chrome lives in `styles.css` (scoped to `.fw-gallery`).

This is a **leaf** (simple) shortcode — no class file, no page-builder item
class. It is NOT section-like (compare the recipe at `../AGENTS.md`, which is for
section-like containers). It mirrors the `testimonials` / `posts` registry-driven
design architecture.

## Design system (swappable layouts) — REQUIRED reading before editing rendering

Registry-driven dispatcher, identical in shape to `testimonials`:

- **`views/designs/registry.php`** — single source of truth. One entry per design:
  `key => [ 'label', 'thumb' (svg), 'css', 'js', 'splide' (bool) ]`. Three readers:
  `options.php` (image-picker choices), `views/view.php` (whitelists which
  `designs/<key>.php` to include), `static.php` (conditionally enqueues the
  design css/js + Splide when `splide` is true). **Adding a design = one registry
  entry + `views/designs/<key>.php` + `static/css/designs/<key>.css` (+ optional
  `static/js/designs/<key>.js`) + `static/img/designs/<key>.svg`.**
- **`views/view.php`** — thin dispatcher. Does all shared data-prep (normalizes
  `images` into render-ready items via `sc_gallery_get_items()`, resolves the
  design with a `grid` fallback, builds the wrapper attr, assigns a unique
  lightbox group id, assembles `$tile_args`) then `include`s `designs/<design>.php`.
- **`designs/<key>.php`** — one self-contained template per design. Grid / Masonry
  / Justified / Metro / Polaroid reuse the shared `sc_gallery_render_tile()`;
  Carousel wraps tiles in a Splide track; Showcase hand-builds a stage + thumbnail
  strip and hidden lightbox source anchors.
- **`static.php`** — base `styles.css` + `lightbox.js` ALWAYS enqueue (cover the
  default Grid + the shared lightbox). Per-design css/js (and Splide for Carousel)
  enqueue per-instance via the `fw_ext_shortcodes_enqueue_static:gallery` action,
  which receives the instance atts. Also defines all the shared `sc_gallery_*`
  render helpers.

### Design picker = a `multi-picker` keyed by an `image-picker`

The design is chosen by an **inline** (non-popover) **`multi-picker`** named
**`design_settings`** whose picker sub-option `design` is an **`image-picker`**. Each design's
choice reveals ONLY that design's options. **Inline label rule:** the top-level multi-picker is
`'label' => false, 'desc' => false` and the user-visible label sits on the picker sub-option
(`design => [ 'label' => __('Design'), … ]`) — do NOT move it onto the top level (that mis-spaces
an inline picker). Saved shape:
`design_settings => { design: '<key>', '<key>': { …that design's options… } }`.
`design_settings` is a dedicated id (never a legacy scalar) so opening the modal
with raw saved atts can't hit `$value['design']`-on-a-string (the blank-`error:`
modal trap). Default `{ design: 'grid' }`.

Cross-design appearance (lightbox / captions / corners / colors / spacing) stays
**top-level** on the Style tab.

## Options schema (atts)

Source of truth: `options.php`. Tabs: Content · Design · Style · Animations · Advanced.

### Tab: Content (wrapped in `group`)

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `title` | `text` | — | Optional heading above the gallery |
| `images` | `multi-upload` (images_only) | `[]` | The gallery images. Saved value = array of `{ attachment_id, url }`. Captions/alt/titles come from the Media Library |

### Tab: Design (`design_settings` multi-picker, in `group`)

`design_settings.design` ∈ `grid | masonry | justified | metro | carousel | polaroid | showcase | cards | slideshow | thumbslider | coverflow | marquee | filmstrip | spotlight | honeycomb | accordion | flipcards | stack` (default `grid`).

Per-design revealed options (saved at `design_settings/<design>/<sub>`):

`gap` everywhere is a **Gap Scale slug** (`sc_get_gap_select_choices()`, e.g. `3` = 1rem; `''`/"None" = 0), default `3`. The view resolves it with `sc_gallery_gap_css()` → `var(--gap-<slug>, 1rem)` for CSS layouts (stays live with Spacing → Gap Scale presets), and `sc_gallery_gap_size()` → a concrete length for Splide's JS `gap` (carousel only).

`columns` is a **desktop-only** control — tablet & phone counts are FIXED automatically by the `$g_cols` reader in `view.php` (phone = 1; tablet = `count-1` for 5–6 cols, else `min(count,2)`). There are **no `columns_tablet` / `columns_mobile` builder fields** anymore — legacy saves of those siblings are still honoured if present. Two shapes:
  - **grid** uses a `multi-picker`: a "Columns" `select` (1–6) is the picker; picking N (only N = 2, 3, 4 or 6 — not 1 or 5) reveals a **Column Ratio** `split-slider` LOCKED to N panes for unequal / featured widths (clean twelfths — 1/2, 1/3, 1/4, 1/6; always starts equal). Saved `columns => { count:'N', 'N':{ col_ratio:[{ w, name }…] } }`. `$g_cols` also tolerates a legacy scalar count.
  - every other column design uses a plain "Columns" `select` (1–6), saved as the scalar count string. Carousel/coverflow/filmstrip `per_view` and spotlight's thumbnail `columns` are the same plain 1–6 select.

- **grid**: `columns` (multi-picker: count 1–6 def 3 + optional `col_ratio`), `gap` (Gap-Scale slug, def `3`), `ratio` (`1-1|4-3|3-2|16-9|3-4|original`, def `4-3`).
- **masonry**: `columns` (select 1–6, def 3), `gap`.
- **justified**: `row_height` (slider 120–420 px, def 220), `gap`.
- **metro**: `columns` (select, def 4), `gap`.
- **carousel**: `per_view` (select 1–6, def 3), `ratio` (def `4-3`), `gap`, `carousel_autoplay` (def no), `carousel_interval` (ms, def 4000), `carousel_pause_hover` (def yes), `carousel_loop` (def yes), `carousel_arrows` (def yes), `carousel_dots` (def yes).
- **polaroid**: `columns` (select, def 4), `gap`, `tilt` (switch, def yes).
- **showcase**: `ratio` (def `4-3`), `thumb_position` (`bottom|left|right`, def bottom), `gap`.
- **cards**: `columns` (select, def 3), `gap`, `ratio` (def `4-3`). Forces caption-below.
- **slideshow**: `ratio` (def `4-3`), `ken_burns` (switch, def yes), `carousel_*` (autoplay def yes, interval def 5000, pause_hover, loop, arrows, dots). Splide `type:fade`, perPage 1.
- **thumbslider**: `ratio` (def `4-3`), `carousel_autoplay` (def no), `carousel_interval`, `carousel_pause_hover`, `carousel_loop`, `carousel_arrows` (no dots). Two synced Splides (main + nav).
- **coverflow**: `per_view` (select, def 3), `ratio` (def `4-3`), `gap`, `carousel_*` (autoplay def no, interval, pause_hover, loop def yes, arrows, dots). Splide `focus:center`; falloff via CSS `.is-active`.
- **marquee**: `row_height` (slider 100–360 px, def 180), `marquee_speed` (`slow|normal|fast`, def normal), `marquee_direction` (`left|right`, def left), `gap`. CSS-only; items rendered twice (the clone copy is `click_action:none`).
- **filmstrip**: `per_view` (select, def 3), `ratio` (def `4-3`), `gap`. CSS scroll-snap, no library.
- **spotlight**: `feature_side` (`left|right`, def left), `columns` (thumb grid select, def 3), `gap`. First image large + rest in a grid.
- **honeycomb**: `columns` (select, def 4), `gap`. Hexagon clip-path tiles.
- **accordion**: `row_height` (slider 180–560 px, def 340), `gap`. Flex panels that expand on hover; stacks on mobile.
- **flipcards**: `columns` (select, def 3), `gap`, `ratio` (def `4-3`). Hover-flip 3D; the WHOLE card is the lightbox anchor (custom front/back markup, not the shared tile).
- **stack**: `banner_ratio` (`16-9|2-1|21-9|3-1|4-1|original`, def `21-9`), `gap`. Full-width stacked strips.

### Tab: Style (top-level, cross-design)

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `container_type` | `select` (`''`/`container`/`container-fluid`) | `''` | Outer width wrapper |
| `click_action` | `select` (`lightbox`/`file`/`attachment`/`none`) | `lightbox` | What an image click does |
| `captions` | `select` (`none`/`hover`/`below`) | `none` | Per-image caption display |
| `caption_source` | `select` (`caption`/`title`/`alt`/`description`) | `caption` | Which Media Library field feeds the caption + lightbox caption |
| `rounded` | `select` (`rounded-0`/`rounded`/`rounded-lg`/`rounded-circle`) | `rounded` | Image corner radius |
| `hover_zoom` | `switch` (`yes`/`no`) | `yes` | Scale image on hover |
| `text_color` / `bg_color` / `font_size_preset` | compact color / font-size | — | Wrapper typography |
| `title_color` / `caption_color` | compact color | — | Per-element color (extracted off the wrapper) |
| `spacing` | `spacing` composite | — | Margin & padding |

### Tabs: Animations + Advanced — standard (`sc_get_animation_fields()` / `sc_get_advanced_tab()`).

## Rendering

`view.php` normalizes `images` → items (`sc_gallery_get_items()` resolves real
URLs, dimensions, srcset/sizes, alt/caption/title/description, and the full-size
source for the lightbox from the attachment id — output never trusts the stored
url). It assigns a unique `lightbox_group` (`wp_unique_id('fw-gal-')`) so each
instance's prev/next is scoped to itself, then dispatches to the design template.

`sc_gallery_render_tile()` is the shared unit: a `<figure class="fw-gallery__item">`
wrapping the image in the click target (`lightbox` → `<a data-fw-lightbox=group
href=full data-fw-caption>`; `file`/`attachment` → plain anchor; `none` → `<span>`)
plus an optional hover-overlay or below-image caption.

`lightbox.js` is dependency-free: a delegated click handler opens any
`[data-fw-lightbox]`, collecting all sources sharing that group value into the
slide set; supports keyboard (Esc / ← → / Home / End), swipe, backdrop-close,
counter, spinner, and `prefers-reduced-motion`. The **Showcase** design reuses it
by `click()`-ing a hidden source anchor — no API coupling.

Justified rows are computed **server-side** from each image's real aspect ratio
(`--ar` per item + flexbox grow) — no JS, no layout shift. Metro squares use the
`1fr` auto-rows + padding-bottom pseudo trick with nth-child spans.

## Pitfalls

1. **`images` saved shape** — `multi-upload` stores `[{ attachment_id, url }, …]`.
   `sc_gallery_get_items()` re-resolves everything from `attachment_id`; the stored
   `url` is only a last-resort fallback for id-less (imported) entries. The canvas
   `title_template` guards `typeof img === 'object'` before reading `img.url`.
2. **Lightbox group must be unique per instance** — it is (`wp_unique_id`). Don't
   hardcode it or two galleries on a page will merge their prev/next sets.
3. **Carousel needs Splide** — only enqueued when an instance picks the carousel
   design (registry `'splide' => true`). Reuses the vendored copy under the
   `carousel` shortcode; do not re-vendor it.
4. **Showcase hidden sources** — the lightbox group is built ONLY from the hidden
   `.fw-gallery__lb-sources a` anchors (rendered only when `click_action ===
   'lightbox'`). The visible stage is a `<button>` that forwards to the active
   source. Don't give the stage its own `data-fw-lightbox` or images double up.
5. **`design_settings` not `design`** — the multi-picker id is deliberately a new
   key so a legacy scalar can't feed it (blank-modal trap). Frontend resolves via
   `design_settings/design` → legacy `design` → `grid`.

## Verification

1. Drag **Gallery** (Media Elements tab) → modal opens; add 6+ images via the
   multi-upload picker; save. Canvas shows a thumbnail strip preview.
2. Frontend: default renders a 3-col Grid; clicking an image opens the lightbox;
   ← → / swipe navigate; Esc / backdrop close.
3. Design → Masonry → staggered natural-height columns. Justified → equal-height
   rows that fill the width. Metro → bento with 2× featured cells.
4. Design → Carousel → Splide slider with arrows/dots; lightbox still works.
5. Design → Polaroid → tilted framed cards that straighten on hover.
6. Design → Showcase → big stage + thumb strip; clicking a thumb swaps the stage;
   clicking the stage opens the lightbox at that image; try `thumb_position` left/right.
7. Style → `click_action: none` → images are not clickable. `captions: below` +
   `caption_source: title` → titles render under each image.

## Files

- `config.php` — page-builder config (Media Elements tab, canvas `title_template`)
- `options.php` — atts schema (Content / Design picker / Style / Animations / Advanced)
- `static.php` — base + per-design enqueues; shared `sc_gallery_*` render helpers
- `views/view.php` — dispatcher (data-prep → include design)
- `views/designs/registry.php` — design source of truth
- `views/designs/{grid,masonry,justified,metro,carousel,polaroid,showcase,cards,slideshow,thumbslider,coverflow,marquee,filmstrip,spotlight,honeycomb,accordion,flipcards,stack}.php`
- `static/css/styles.css` — base (item, captions, hover, lightbox, shared Splide chrome)
- `static/css/designs/<key>.css` — per-design layout
- `static/js/lightbox.js` — dependency-free lightbox (always enqueued)
- `static/js/designs/carousel.js` — shared Splide mount (Carousel / Slideshow / Coverflow)
- `static/js/designs/thumbnail-slider.js` — main+nav synced Splides (Thumbnail Slider)
- `static/js/designs/showcase.js` — stage/thumbnail interaction
- `static/img/page_builder.svg` — 16×16 Media Elements tile icon
- `static/img/designs/<key>.svg` — 120×90 design-picker thumbnails
