---
type: shortcode
name: image-content
since: Unyson+ fork (image-beside-text composite)
provides: leaf-shortcode
---

# Image Content

A two-column composite: image on one side, rich text content on the
other. Bootstrap grid handles the column split (with 11 ratio options
from `1-11` to `11-1`), vertical alignment, gap, and mobile stacking
order.

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
| `image_alt` | `text` | — | Override the alt text from the media library. Leave empty to use the attachment's alt |
| `image_link` | `text` | — | Optional href that wraps the image in an `<a>` |
| `image_link_target` | `switch` (`_blank` / `_self`) | `_self` | New window or same |

### Tab: Layout

Wrapped in `group_layout` (flattens).

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `layout` | `select` (`image-left` / `image-right`) | `image-left` | Which side the image is on |
| `column_ratio` | `image-picker` (11 ratios from `1-11` to `11-1`) | `4-8` | Image vs content width split — Bootstrap col-md-N pairs (always sums to 12) |
| `vertical_align` | `select` (`align-items-start` / `align-items-center` / `align-items-end`) | `align-items-center` | Vertical alignment of the two columns |
| `gap` | `select` (`g-0` / `g-3` / `g-4` / `g-5`) | `g-4` | Horizontal gap between columns — Bootstrap `g-*` utility |
| `mobile_order` | `select` (`image-first` / `content-first`) | `image-first` | Stacking order on mobile (≤ md breakpoint) |

### Tab: Styling

Wrapped in `group_options` + `group_colors` (both flatten).

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `image_fit` | `select` (`contain` / `cover`) | `contain` | How the image fills its column. `contain` preserves aspect; `cover` crops to fill |
| `image_radius` | `select` (`rounded-0` / `rounded-2` / `rounded-3` / `rounded-4` / `rounded-circle`) | `rounded-0` | Image border-radius — Bootstrap utility |
| `image_shadow` | `select` (`''` / `shadow-sm` / `shadow` / `shadow-lg`) | `''` | Image drop-shadow — Bootstrap utility |
| `content_color` | `sc_color_field_compact` | — | Body text color |

### Tabs: Animations + Advanced

Standard.

## Rendering

`views/view.php` outputs a Bootstrap `<div class="row align-items-* g-*">`
containing two columns (`<div class="col-md-{n}">` × 2) whose widths come
from `column_ratio`. Image column uses `<img class="rounded-* shadow-*">`
with `object-fit` from `image_fit`. Mobile column order is controlled
via Bootstrap's `order-*` utilities driven by `mobile_order`.

## Pitfalls

1. **No `static.php`** — this shortcode has no frontend asset enqueue;
   all layout uses inherited Bootstrap utilities. If you add custom CSS
   for this shortcode, you'll need to create `static.php`.
2. **`column_ratio` keys use Bootstrap col-md-N convention** — `4-8` means
   `col-md-4 + col-md-8`. Total always sums to 12. Generators must keep
   this invariant.
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
- `static/img/ratio-*.svg` — column-ratio thumbnails for the image-picker
- `static/img/page_builder.png` — Layout Elements thumbnail

No `static.php`, no JS, no item class — pure layout shortcode.
