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

### Verified `atts` (real export)

From `image-test-section-ca2a301e.json` (2 images, plugin 2.10.26). Confirms the tables above
(no drift). Shared `spacing`/`animation` blocks + common keys per the page-builder playbook §3.
Pinned shapes:

- **`image`** = `{"attachment_id":"442","url":"//host/wp-content/uploads/2026/06/educator-3-….jpeg"}`
  — the `url` is protocol-relative. This is the reference to a **Media Library item**, so the
  conversion media phase closes here: import the source image via the Site Converter, then point
  `image` at its new `attachment_id` + `url`.
- **`width`/`height`** = `{"value":"299","unit":"px"}` (empty `value` ⇒ auto).
- **`bg_color`** = compact picker — `{"predefined":"bg-secondary","custom":""}` (a `bg-{slug}` preset)
  **or** `{"predefined":"","custom":"#cdbaba"}` (hex); mutually exclusive.
- **`link`** = URL or `""`; **`target`** = `_blank|_self`.
- Re-confirmed the §3 generals: `custom_css` uses the `selector` token
  (`"selector {\r\n\tmax-width: 800px;\r\n}"`), `spacing.advanced` is a `{lg:{margin,padding},md:{…}}`
  responsive-class map when set, `responsive_hide` = `{"hide-sm":true}`.

### Rendering vs the Media Library (hand-authoring templates)

When generating template JSON by hand (not converting a real export), two things bit us and are
worth pinning:

- **`url` alone renders; `attachment_id` is what puts it in the Media Library.** `view.php` uses
  `image.url` whenever `attachment_id` is empty (it only bails when *both* are empty), so an
  `image` of `{"attachment_id":"","url":"http://…/foo.svg"}` **does render** a plain
  `<img src="…">`. But with an empty `attachment_id` the file is **not a Media Library item** — it
  won't appear in the media grid, and you lose the responsive `srcset` / exact-crop path (those
  need a real attachment). So for a demo/template that should own its images: **first register the
  file as an attachment** (`wp_insert_attachment` + `_wp_attached_file` — this bypasses the SVG mime
  block for programmatic inserts) and set `image` to that `{attachment_id, url}`. Then the picker
  shows it and it lives in the library. (With `attachment_id` set, the view renders via
  `wp_get_attachment_image`, which for an SVG emits the `<img>` with the attachment's stored
  `width`/`height` HTML attrs — harmless; override with CSS if you need it to fill a box.)
- **Blank `width`/`height` = full/natural size, no inline sizing.** Leaving `width`/`height` as
  `{"value":"","unit":"px"}` emits no inline `style`/`width` attr, so the `<img>` is free to be sized
  by CSS (e.g. an `object-fit:cover` panel fill). Set px values only when you want an explicit,
  cropped/fixed size (both-px triggers `fw_resize` cropping). This is the right choice when a
  container (a Scrollytelling media panel, a cover hero) controls the size.

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
