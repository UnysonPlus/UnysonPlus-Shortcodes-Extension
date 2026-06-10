---
type: shortcode
name: image-content
since: Unyson+ fork (image-beside-text composite)
provides: leaf-shortcode
---

# Image Content

An image-beside-text composite. Three layouts (image **left**, **right**, or
**top/stacked**); for the side-by-side ones a **Split slider** sets the image's
`col-md-N` span (content fills the rest), plus vertical alignment, gap, and mobile
stacking order. The go-to element for "feature row" bands — one atom instead of a
hand-built row → 2 columns → image + heading + text tree (which is why the
conversion contract prefers it for image+text bands).

## Registration

No custom class file — leaf shortcode auto-instantiated. No item class.
**No `static.php`** — this shortcode has no frontend asset enqueue (it
uses inherited Bootstrap utilities for layout, no custom CSS).

`config.php` declares a `title_template` that previews the image (as a
thumbnail) and content snippet on the canvas, swapping order based on
the `layout` choice.

## Options schema (atts)

Source of truth: `options.php`. Three tabs + Animations + Advanced.

### Tab: Content

Wrapped in `group_content` (flattens), with a nested `image_link_group`
group inside.

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `image` | `upload` | — | Featured image (WP attachment) |
| `content` | `wp-editor` (300px) | — | Text content alongside the image — rich text |
| `image_link` | `text` | — | Optional href that wraps the image in an `<a>` |
| `image_link_target` | `switch` (`_blank` / `_self`) | `_self` | New window or same |

### Tab: Layout

Organized into three border-less `group` containers (purely visual — groups flatten, so leaf ids
are unchanged): `group_layout_arrange` (Layout, Split) · `group_layout_align` (Vertical Align,
Content Align, Gap) · `group_layout_responsive` (Mobile Order, Stack Below, Stacked Image Width,
Stacked Image Align).

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `layout` | `image-picker` (`image-left` / `image-right` / `image-top`) | `image-left` | Image position. `image-left`/`image-right` = side by side (use `column_ratio`); **`image-top` = stacked** (image full-width above the content; `column_ratio`/`vertical_align`/`mobile_order` don't apply) |
| `column_ratio` | **`column-split`** — a draggable two-pane split bar (Image \| Content). Value = the image's `col-md-N` span (1–11 of 12); content fills `12-N`. **Same integer the old slider stored** (drop-in swap, no migration). Panes show the lowest-form fraction (e.g. `1/3`) | `4` | Image/content split. **Legacy:** old saves hold the image-picker string `"4-8"` — the view still reads that form |
| `vertical_align` | **`image-picker`** (`align-items-start` / `-center` / `-end`) | `align-items-center` | Vertical alignment of the two columns (side-by-side only). Visual top/center/bottom swatches; value is the `align-items-*` class applied to the row |
| `content_align` | **`image-picker`** via `sc_alignment_field()` (`left` / `center` / `right`) | `left` | Horizontal alignment of the content text → mapped to `text-start`/`-center`/`-end` by `sc_alignment_class()`. A centered/right block is auto-centered when `content_max_width` is set |
| `gap` | **`short-select`** — gap-scale slugs via `sc_get_gap_select_choices()` (`''` = Use Default Gap) | `4` | Column gutter, sourced from the **Gap Scale** presets (same vocabulary as `section`/`column`; respects a customized scale). Rendered as `g-{slug}` side-by-side and `gy-{slug}` on the stacked row. **Legacy:** old `g-4` full-class saves still read |
| `mobile_order` | `select` (`image-first` / `content-first`) | `image-first` | Stacking order below the breakpoint (side-by-side) |
| `breakpoint` | `select` (`sm` / `md` / `lg`) | `md` | Width at which the two go side by side; below it they stack. Drives `col-{bp}-N` + `order-{bp}-*`. Sits last among the common Layout controls |
| `stack_image_width` | `unit-input` (`px`/`%`/`rem`) | `{value:'',unit:'px'}` | **Image Top only** — cap the stacked image width (inline `max-width`); blank = full width |
| `stack_image_align` | `image-picker` via `sc_alignment_field()` (`left`/`center`/`right`) | `center` | **Image Top only** — positions the capped image (margin auto), once `stack_image_width` is set |

### Tab: Styling

Wrapped in `group_options` + `group_colors` (both flatten).

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `image_fit` | `select` (`contain` / `cover`) | `contain` | How the image fills its column. `contain` preserves aspect; `cover` crops to fill |
| `image_ratio` | `select` (`''` / `1x1` / `4x3` / `3x2` / `16x9` / `3x4`) | `''` | Force the image into a fixed aspect-ratio box (inline `aspect-ratio` + `object-fit` from `image_fit`). `''` = original proportions. Makes `cover` crop predictably without depending on the other column's height |
| `image_radius` | `select` (`rounded-0` / `rounded-2` / `rounded-3` / `rounded-4` / `rounded-circle`) | `rounded-0` | Image border-radius — Bootstrap utility |
| `image_shadow` | `select` (`''` / `shadow-sm` / `shadow` / `shadow-lg`) | `''` | Image drop-shadow — Bootstrap utility |
| `content_max_width` | `unit-input` (`ch`/`px`/`rem`/`em`/`%`) | `{value:'',unit:'ch'}` | Readability cap on the content (inline `max-width`); auto-centered when `content_align` is `center` (see the Layout tab) |
| `content_color` | `sc_color_field_compact` | — | Body text color |
| `content_bg` | `sc_color_field_compact` (bg) | — | Background behind the content — turns the text side into a tinted "card" panel (pair with padding). Extracted with `content_color` via `sc_extract_styling_atts` (name contains `bg` ⇒ treated as background) |
| `content_padding` | **`spacing`** composite, `mode: 'padding'` | — | Per-side inner padding of the content panel (all/top/right/bottom/left + responsive `advanced`), from the Spacing Scale presets. Margin subtree stays dormant. View flattens it with `sc_flatten_spacing_value()` onto the content column |

### Tabs: Animations + Advanced

Standard.

### Verified `atts` (real export)

From `image-content-section-f38c05cd.json` (`kind:section` → 1 column → one `image_content`, plugin
**2.10.31**). Confirms the tables above with **no drift**. Pinned shapes:

- **`column_ratio` = a plain integer** (seen `3` → image `1/4`) — the `column-split` option type stores
  the image's column span (1–11 of 12), exactly the int the old slider stored (so the swap needed no
  migration). The view still also reads the legacy `"4-8"` string.
- **`content_padding` = the full `spacing` composite** (padding mode) including the responsive
  `advanced` layer: `advanced.lg.padding` = `{"top":"pt-lg-8","right":"pe-lg-5","bottom":"pb-lg-4","left":"ps-lg-4"}`
  (per-side + per-breakpoint utility classes; empty subtrees `""` when unused, margin subtree dormant).
- **`content_bg`** = `{"predefined":"bg-accent","custom":""}` (a `bg-{slug}` preset) · **`content_color`**
  = `{"predefined":"text-secondary","custom":""}` (a `text-{slug}` preset); both compact, hex in `custom`.
- **`vertical_align`** = `align-items-end` · **`content_align`** / **`stack_image_align`** = `center`
  (sc_alignment_field: `left|center|right`) · **`gap`** = `"4"` (gap-scale slug) · **`breakpoint`** = `md`.
- **`stack_image_width`** / **`content_max_width`** = unit-input `{"value":"250","unit":"px"}` /
  `{"value":"50","unit":"ch"}` · **`image_fit`** = `contain` · **`image_ratio`** = `""` (original) ·
  **`image_radius`** = `rounded-3` · **`image_shadow`** = `shadow`.
- **`image`** = `{"attachment_id":"436","url":"//host/wp-content/uploads/…jpeg"}` (protocol-relative;
  Media-Library reference) · **`content`** = rich `<p>…</p>` HTML · **`image_link`** + `image_link_target` = URL + `_blank`.
- §3 generals confirmed: `custom_css` uses the `selector` token (`"selector {\r\n\tfont-style:italic;\r\n}"`),
  `responsive_hide` = `{"hide-sm":true}`, `css_id`/`css_class` plain strings, `custom_attrs` = `[]`.

## Rendering

**Side-by-side** (`image-left`/`image-right`): a Bootstrap `<div class="row align-items-* g-{slug}">`
with two `<div class="col-12 col-{bp}-{n}">` columns (widths from the `column_ratio` slider, stack
point from `breakpoint`); mobile order via `order-{bp}-*` from `mobile_order`. **Stacked**
(`image-top`): the same gap mechanism — a `.image-content__stack.row.gy-{slug}` with two `col-12`
(image then content), so one gap-scale value drives both layouts; the image can be capped +
positioned via `stack_image_width`/`stack_image_align`. The `<img>` uses `img-fluid` +
`rounded-*`/`shadow-*`, an inline `aspect-ratio` + `object-fit` when `image_ratio` is set (else
`object-fit:cover;height:100%` for `image_fit:cover`), and always carries **`loading="lazy"` +
`decoding="async"`**; a `target="_blank"` link gets `rel="noopener noreferrer"`. **Alt** is read
solely from the attachment's `_wp_attachment_image_alt` (Media Library) — there is no per-instance
alt override (matching `media_image`).

## Pitfalls

1. **No `static.php`** — this shortcode has no frontend asset enqueue;
   all layout uses inherited Bootstrap utilities. If you add custom CSS
   for this shortcode, you'll need to create `static.php`.
2. **`column_ratio` is now a `slider` int** (1–11 = the image's `col-md-N` span;
   content gets `12 − N`). The view is **backward-compatible** with the old
   image-picker string `"4-8"` (split on `-`), so existing items keep working —
   no value migration needed (the slider degrades gracefully on the old value,
   unlike a multi-picker, which is why a multi-picker was **not** used for a leaf
   shortcode). Columns also carry a base `col-12` so they stack cleanly below `md`.
3. **`image_fit: cover` requires fixed height** — without a parent height
   constraint, `object-fit: cover` doesn't visually crop because the image
   has no constrained size. Pair with a column whose height is set by
   the longer text content side OR add a min-height via CSS.
4. **`gap` values are Bootstrap utilities, not pixel values** — `g-4`
   means Bootstrap's 4-step gap, not 4px. Generators producing this att
   must emit the literal Bootstrap class string.

## Verification

1. Drag Image Content → upload an image, type some content.
2. Reload → renders image-left, content-right at 4-8 ratio, centered
   vertically.
3. Switch `layout: image-right` → image moves to right.
4. Switch `column_ratio: 6-6` → 50/50 split.
5. Set `image_radius: rounded-circle` → image renders as a circle.
6. View on mobile → columns stack with `mobile_order` set order.
7. Set `image_link` → image becomes a clickable anchor.

## Files

- `config.php`, `options.php`, `views/view.php`
- `static/img/layout-image-{left,right,top}.svg` — layout-picker swatches
- `static/img/valign-{top,center,bottom}.svg` — vertical-alignment picker swatches
- *(content alignment uses the shared `shortcodes/static/img/alignment/` swatches via `sc_alignment_field()`)*
- `static/img/ratio-*.svg` — legacy (column-ratio image-picker); unused since the split became a slider, kept for back-compat reference
- `static/img/page_builder.png` — Layout Elements thumbnail

No `static.php`, no JS, no item class — pure layout shortcode.
