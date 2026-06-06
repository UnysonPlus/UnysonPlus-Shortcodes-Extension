---
type: shortcode
name: media-image
since: original Unyson
provides: leaf-shortcode
---

# Image (Media)

A standalone image with optional link wrap and explicit width / height.
For an image alongside text content, use `[image-content]` instead. For
inline images inside prose, use the WP editor's image button inside a
`[text-block]`.

Page-builder tab: **Media Elements** (NOT Content Elements).

## Registration

No custom class file — leaf shortcode auto-instantiated. No item class.

`config.php` declares a `title_template` that previews the image at
thumbnail size on the canvas with `width` / `height` attributes applied.

## Options schema (atts)

Source of truth: `options.php`. Two tabs + Animations + Advanced. The
Content tab uses nested groups (`size` group + `image-link-group`),
both of which flatten on save.

### Tab: Content

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `image` | `upload` | — | Featured image (WP attachment — saved shape `{ attachment_id, url }`) |
| `width` | `unit-input` (`px`/`%`/`vw`/`rem`/`em`; inside `size` group) | `{value:300,unit:'px'}` | Image width. Compiled to a CSS length applied as inline `style`. When **both** width+height are `px` the source is cropped via `fw_resize`; the px value also becomes the HTML `width` attr (CLS). Non-px units only scale display |
| `height` | `unit-input` (`px`/`%`/`vh`/`rem`/`em`; inside `size` group) | `{value:200,unit:'px'}` | Image height. Same rules as width. Leave the number blank to let height follow the width |
| `link` | `text` (inside `image-link-group`) | — | URL to wrap the image in. Empty = no link |
| `target` | `switch` (`_blank` / `_self`; inside `image-link-group`) | `_self` | New window or same |

### Tab: Styling

Wrapped in `group_colors` + `group_spacings` (both flatten).

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `bg_color` | `sc_color_field_compact` (bg) | — | Wrapper background |
| `spacing` | `sc_spacing_field` | — | Wrapper margin/padding |

### Tabs: Animations + Advanced

Standard.

## Rendering

`views/view.php` outputs `<img>` (or `<a><img></a>` when `link` is set)
with the configured width/height.

**Conditional wrapper.** Like `[text-block]`, the view wraps the image in a
`<div>` **only when `sc_needs_wrapper($atts)` is true** — i.e. the Styling tab
(bg color / spacing), Animations, or an Advanced CSS id/class/custom-attr is set.
In that case the wrapper carries the base/unique/styling classes + inline style
(this is how a background color + padding renders a frame around the image), and
the `<img>` keeps only `img-fluid`. With no such atts, the bare `<img>` (or
`<a><img></a>`) carries the base/unique classes + `img-fluid` and the CSS ID
(ID on the `<a>` when linked, else on the `<img>`) — identical to legacy markup.

- `alt` comes from the attachment's `_wp_attachment_image_alt` meta and is
  left **empty** when unset (never the URL — that would hurt a11y/SEO).
- `<img>` always carries `loading="lazy"` + `decoding="async"`.
- Linked images opening a new tab (`target="_blank"`) get
  `rel="noopener noreferrer"`. The `href` is escaped with `esc_url()`
  (not `fw_html_tag`) so query-string `&` aren't double-encoded.

## Pitfalls

1. **`image` is a WP upload object, NOT a URL** — `{ attachment_id, url }`
   shape. Generators producing URL-only data should construct the object.
2. **`width` / `height` are `unit-input`s** (`array('value','unit')`) — the view
   applies them as inline CSS (`width`/`height`), so any unit works for display.
   Only when **both** are `px` does it `fw_resize`-crop the source and set the
   unitless HTML `width`/`height` attributes. Legacy bare-number saves are read as px.
3. **No `srcset` / responsive sizes** — the shortcode outputs a single
   `<img>` with the user's width/height. WordPress's automatic `srcset`
   generation may still kick in via filters, but no explicit responsive
   sizing is configurable from the shortcode.
4. **`size` and `image-link-group` are GROUP containers** — atts flatten
   to top-level keys (`width`, `height`, `link`, `target`), NOT nested
   under `size.width` etc.

## Verification

1. Drag Image from Media Elements → modal opens.
2. Upload an image, set width 600 / height 400 → reload → image renders
   at those dimensions.
3. Set `link: https://example.com`, `target: _blank` → image becomes a
   clickable anchor opening in new tab.

## Files

- `config.php`, `options.php`, `views/view.php`

**No `static.php`** — no frontend asset enqueue needed (image renders
via plain HTML).
