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
