---
type: shortcode
name: testimonials
since: original Unyson (substantially extended in Unyson+ — Bootstrap 5 carousel, grid mode, single mode, rating stars, card-style options)
provides: leaf-shortcode
---

# Testimonials

A multi-item testimonial widget. Each entry has a quote, avatar, author
name + job + website, and a 0–5 star rating. Three layout modes (Bootstrap
carousel, grid, single first-item), six card style presets, three avatar
positions × three shapes × three sizes, and full Bootstrap 5 carousel
controls.

## Registration

No custom class file — leaf shortcode auto-instantiated. No item class.

`config.php` declares a `title_template` that previews every testimonial's
title (section heading) + quote + author on the canvas.

## Design system (swappable layouts) — REQUIRED reading before editing rendering

This shortcode uses a **registry-driven design dispatcher**. The renderer is split:

- **`views/designs/registry.php`** — the **single source of truth**. One entry per
  selectable design: `key => [ 'label', 'thumb' (svg), 'css' (or null), 'js' (or null) ]`.
  Three places read it: `options.php` (builds the image-picker `choices`),
  `views/view.php` (whitelists which `designs/<key>.php` to include), and `static.php`
  (conditionally enqueues the design's css/js). **Adding a design = one registry entry +
  `views/designs/<key>.php` + `static/css/designs/<key>.css` (+ optional
  `static/js/designs/<key>.js`) + `static/img/designs/<key>.svg`.** No other file changes.
- **`views/view.php`** — thin **dispatcher**. Does ALL shared data-prep (styling atts,
  `sc_get(...)` values, wrapper attr) then `include`s `designs/<design>.php`, which inherits
  every variable by scope. It resolves the design with a **default fallback**
  (`sc_get('design', $atts, 'default')` + `isset($registry[...])` + `file_exists`) — never
  `include` the raw saved value. It appends `design-<key>` to the wrapper class for CSS scoping.
- **`views/designs/<key>.php`** — one self-contained template per design. `default.php` is the
  original Slider/Grid/Single output verbatim. Others: `marquee`, `masonry`, `split`, `bubble`,
  `stacked`, `thumbnav`, `spotlight` (coverflow), `bento` (featured grid), `zigzag` (alternating
  rows), `pullquote` (oversized crossfade). `spotlight` and `pullquote` reuse the base
  `.testimonials-splide` mount (no own JS) — coverflow via `focus:center`+padding, pullquote via
  `type:fade`.
- **`static.php`** — base `styles.css` + `scripts.js` (Splide) always enqueue (cover Classic +
  shared avatars/ratings). Per-design css/js enqueue via the **per-instance**
  `fw_ext_shortcodes_enqueue_static:testimonials` action (which, unlike the body of static.php,
  receives the instance atts), reading the same registry. Shared per-item helpers live here:
  `sc_render_card`, `sc_render_rating`, `sc_testimonial_fields`.

### Design picker = a `multi-picker` keyed by an `image-picker` (option-gating)

The design is chosen by a **`multi-picker`** named **`design_settings`** whose picker sub-option
`design` is an **`image-picker`** (SVG thumbnail per design, choices built from the registry). Each
design's **choice** reveals ONLY that design's options, so the user never sees irrelevant controls.
Saved shape: `design_settings => { design: '<key>', '<key>': { …that design's options… } }`.

**The old Layout + Carousel tabs are folded into the picker choices:**
- `default` (Classic): `layout_type` (nested multi-picker carousel/grid/single; grid reveals
  `grid_columns` + `gutter`), `items_per_slide`, `card_style`, `avatar_position`, and the seven
  `carousel_*` options.
- `marquee`: `marquee_speed`, `marquee_direction`. `masonry`/`bubble`: `*_columns`.
- `split`/`spotlight`: the `carousel_*` subset. `thumbnav`/`pullquote`: `carousel_*` subset.
- `zigzag`: `zigzag_start` (which side the first photo sits on).
- `stacked`/`bento`: no design-specific options (intentionally omitted from `choices`).

**Cross-design** appearance stays top-level on the **Style** tab (no path change): `container_type`,
`text_align`, `avatar_shape`, `avatar_size`, `show_rating`, plus colors + spacing.

**Tabs (5):** Content · Design · Style · Animations · Advanced. (The near-identical "Style"/"Styling"
tabs were merged; "Layout"/"Carousel" folded into "Design".)

**Why `design_settings` (not `design`) is the multi-picker id — REQUIRED rationale:** leaf
shortcodes open their builder modal with **raw saved atts** (`simple` item:
`new fw.OptionsModal({ values: model.get('atts') })`), so a legacy **scalar** `design` value fed
into a multi-picker would hit `$value['design']`-on-a-string → blank `error:` modal (fatal on PHP 8).
Using a **new** option id means any legacy scalar `design` att is simply orphaned/ignored — **no
migration, no error.**

**Back-compat (frontend):** `view.php` resolves the design via
`sc_get('design_settings/design', $atts, sc_get('design', $atts, 'default'))`, and every option that
**moved into** the picker is read with a `$ts_dp('<new sub>', '<old flat path>', <default>)` helper
(new nested path → legacy flat path → default). So pre-existing saved instances keep rendering
correctly; only the **builder UI** shows defaults for those moved options until the instance is
re-opened and re-saved (cross-design options are unaffected — they never moved).

`split` reuses the base `.testimonials-splide` Splide mount (no extra JS). `thumbnav` ships its
own `designs/thumbnav.js` (syncs a main + avatar-thumbnail Splide). The rest are CSS-only and
gate continuous motion behind `prefers-reduced-motion`.

The Layout tab's Slider/Grid/Single + the whole Carousel tab apply to **Classic** (`default`);
`masonry`/`bubble` reuse the Grid Columns value for their column count; other designs bring their
own arrangement. Options are stored regardless of design and ignored by designs that don't use them.

## Options schema (atts)

Source of truth: `options.php`. Five tabs + Animations + Advanced.
Multiple groups are keyed simply `group` (NOT `group_content` etc.) — the
flatten-on-save behavior still works the same.

### Tab: Content

Wrapped in `group`.

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `title` | `text` | — | Section heading above the testimonials list |
| `testimonials` | `addable-popup` (sortable list) | — | Per-item array: `{ content, author_avatar, author_name, author_job, site_name, site_url, rating }` |
| `testimonials[].content` | `textarea` | — | The quote text |
| `testimonials[].author_avatar` | `upload` | — | Avatar image |
| `testimonials[].author_name` | `text` | — | Person's name (bold author line below the quote) |
| `testimonials[].author_job` | `text` | — | Job title or role (small, muted) |
| `testimonials[].site_name` | `text` | — | Anchor text for the source company/site link |
| `testimonials[].site_url` | `text` | — | External site URL. Adds `rel="nofollow" target="_blank"` |
| `testimonials[].rating` | `slider` (0–5, step 0.5) | `5` | Rating renders as Font Awesome stars (solid / half / regular) |

### Tab: Layout

Wrapped in `group`.

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `layout_type.layout_choice` | `multi-picker` (`carousel` / `grid` / `single`) | (no default — first option) | Overall presentation. **Multi-picker**, NOT a flat select |
| `layout_type.grid.grid_columns` | `select` (`row-cols-1` / `row-cols-2` / `row-cols-3` / `row-cols-4`) | `row-cols-3` | Only present when `layout_type.layout_choice === 'grid'` |
| `gutter` | `select` (`''` / `g-0` / `g-1` / `g-2` / `g-3` / `g-4` / `g-5`) | `''` (default) | Bootstrap `g-*` gutter utility between cards |
| `text_align` | `select` (`''` / `text-center` / `text-end`) | `''` (left) | Bootstrap text alignment utility |
| `container_type` | `select` (`''` / `container` / `container-fluid`) | `container` | Outer width wrapper. None = no Bootstrap container |
| `items_per_slide` | `select` (`1` / `2` / `3`) | `1` | Carousel-only: cards grouped per slide |

### Tab: Style

Wrapped in `group`.

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `card_style` | `select` | `''` (plain) | Bootstrap card preset: `''` (plain), `card card-body`, `card card-body border`, `card card-body shadow`, `card card-body bg-light`, `card card-body bg-dark text-light` |
| `avatar_position` | `select` (`top` / `left` / `right` / `none`) | `top` | Avatar placement relative to the quote |
| `avatar_shape` | `select` (`rounded-circle` / `rounded` / `rounded-0`) | `rounded-circle` | Avatar corner radius |
| `avatar_size` | `select` (`avatar-sm` / `avatar-md` / `avatar-lg`) | `avatar-md` | Avatar size class (custom CSS in the shortcode defines actual pixel sizes) |
| `show_rating` | `switch` (`yes` / `no`) | `yes` | Toggle star display |

### Tab: Carousel

Wrapped in `group`. **All carousel options take effect only when `layout_type.layout_choice === 'carousel'`** — they're stored regardless of mode but ignored by the view when mode is grid/single.

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `carousel_autoplay` | `switch` (`yes` / `no`) | `yes` | Cycle slides automatically (`data-bs-ride`) |
| `carousel_interval` | `text` (ms) | `5000` | Delay between auto slides (`data-bs-interval`) |
| `carousel_pause_hover` | `switch` (`yes` / `no`) | `yes` | Stop cycling while hovered (`data-bs-pause="hover"`) |
| `carousel_controls` | `switch` (`yes` / `no`) | `yes` | Show prev/next arrows |
| `carousel_indicators` | `switch` (`yes` / `no`) | `yes` | Show slide markers (dots / lines) |
| `carousel_indicator_style` | `select` (`none` / `dots` / `lines`) | `dots` | Indicator appearance |
| `carousel_wrap` | `switch` (`yes` / `no`) | `yes` | Loop from last to first (`data-bs-wrap`) |

### Tab: Styling

Wrapped in `group_colors` + `group_spacings` (both flatten).

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `text_color` | `sc_color_field_compact` (text) | — | Wrapper text color |
| `bg_color` | `sc_color_field_compact` (bg) | — | Wrapper background |
| `font_size_preset` | `sc_font_size_field` | — | Named size from theme presets |
| `title_color` | `sc_color_field_compact` | — | Section heading |
| `quote_color` | `sc_color_field_compact` | — | Blockquote body |
| `author_name_color` | `sc_color_field_compact` | — | Author name line |
| `author_job_color` | `sc_color_field_compact` | — | Author job/role line |
| `site_link_color` | `sc_color_field_compact` | — | Website name link |
| `spacing` | `sc_spacing_field` | — | Wrapper margin/padding |

### Tabs: Animations + Advanced

Standard.

## Rendering

`views/view.php` outputs:

- Optional outer `<div class="container | container-fluid">` per
  `container_type`
- A `<h2>{title}</h2>` if `title` is set
- The testimonials list — wrapped differently per `layout_type`:
  - `carousel` → Bootstrap 5 `<div id="..." class="carousel slide"
    data-bs-ride="...">` with `.carousel-inner`, `.carousel-item`s, and
    optional indicators / controls
  - `grid` → Bootstrap `<div class="row {grid_columns} {gutter}">` with
    each testimonial as a `<div class="col">`
  - `single` → only the first testimonial, no list wrapper
- Each testimonial renders a `<blockquote>` (with optional `<cite>` for
  author/job/site), an `<img>` avatar (positioned per `avatar_position`),
  and Font Awesome stars per `rating`

`static/js/jquery.carouFredSel-6.2.1-packed.js` is a legacy carousel
library — present in the folder but NOT used by the Bootstrap 5 carousel
mode. Likely vestigial from the original Unyson carousel implementation;
keep for back-compat with old saved instances.

## Pitfalls

1. **`layout_type` is a multi-picker, not flat** — generators must
   preserve the nested shape:
   `{ layout_choice: 'grid', grid: { grid_columns: 'row-cols-3' } }`. The
   `grid` sub-key only exists when `layout_choice === 'grid'`.
2. **All carousel options are stored always** — even when `layout_type`
   is `grid` or `single`, the carousel options are saved (with their
   defaults). They're inert in non-carousel modes.
3. **`rating` is 0–5 in 0.5 steps** — generators must clamp to this range.
   Half-step values render half-stars; integer values render full stars.
4. **`card_style` values are Bootstrap class concatenations** — `card
   card-body bg-light` is the literal string saved (with spaces). The view
   outputs it as the card's class attribute directly. Not just a single
   class name.
5. **`author_avatar` is a WP upload object, not a URL** — `{ attachment_id,
   url }` shape. Generators that produce URL-only data should construct
   the object: `{ attachment_id: 0, url: '<image-url>' }`.
6. **Legacy carousel library** —
   `static/js/jquery.carouFredSel-6.2.1-packed.js` is in the folder but
   not used by Bootstrap 5 carousel mode. Don't reference it in new code.

## Verification

1. Drag Testimonials → modal opens; add 3 testimonials with quotes +
   author names + avatars.
2. Reload → renders as a 1-per-slide carousel (default), autoplay on.
3. Switch `layout_type.layout_choice: grid`, set `grid.grid_columns:
   row-cols-3` → renders as a 3-column grid.
4. Switch `layout_type.layout_choice: single` → only the first
   testimonial appears.
5. Switch `card_style: card card-body shadow` → cards get the Bootstrap
   shadow.
6. Switch `avatar_shape: rounded-0` → square avatars.
7. Set `show_rating: no` → stars hidden.
8. Set `carousel_autoplay: no` → carousel waits for user navigation.
9. Switch `text_align: text-center` → all text centers inside cards.

## Files

- `config.php`, `options.php`, `static.php`, `views/view.php`
- `static/js/jquery.carouFredSel-6.2.1-packed.js` — legacy library
  (vestigial)
- `static/css/styles.css` (via static.php) — avatar sizes, card layout
- `static/img/page_builder.png` — Layout Elements thumbnail

Standard leaf layout. Bootstrap 5 carousel JS is loaded globally by the
theme; this shortcode just provides the markup with `data-bs-*`
attributes.
