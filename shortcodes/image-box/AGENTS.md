---
type: shortcode
name: image_box
since: shortcodes 1.6.71
provides: simple (leaf) shortcode
tab: Media Elements
---

# Image Box

A single image paired with an eyebrow, title, text, optional icon and a
call-to-action, rendered in one of many **designs** (hover overlays, captions,
cards, frames and structural layouts). It is the Elementor-style "Image Box":
you drop one per builder column and pick a design. It is NOT a gallery — it
renders one image. For a multi-image grid use the `gallery` shortcode.

This is a **simple/leaf** shortcode (like `media-image`), not section-like — it
has no class file and no page-builder item; the shortcodes loader auto-discovers
the folder via `config.php` and registers the tag `image_box`.

## Registry-driven designs (single source of truth)

`views/parts/registry.php` is the catalog. Each entry = one selectable design:

```php
'overlay-fade' => [
    'label'              => __( 'Overlay — fade in on hover', 'fw' ),
    'thumb'              => 'overlay-fade.svg',   // static/img/design/<thumb>
    'part'               => 'overlay',            // views/parts/box-<part>.php
    'content_over_image' => true,                 // text sits ON the image
    'hover_reveal'       => true,                 // hidden until hover
],
```

Three readers: `options.php` (builds the Design image-picker `choices`),
`views/view.php` (dispatches to `parts/box-<part>.php`), `static.php` (auto-gates
`static/css/design/<key>.css` if present).

**Adding a design = one registry entry + a thumbnail `static/img/design/<key>.svg`
+ (reuse or add) a `views/parts/box-<part>.php` + (optional) per-design CSS
`static/css/design/<key>.css`.** No other file changes.

Three independent axes compose instead of multiplying:
- **Design** (registry) — structure / overlay / caption / card / frame look.
- **Hover Effect** (`hover_effect` select) — zoom / grayscale / shine / lift /
  tilt / blur, layered on ANY design (pure CSS, keyed off `imgbox--fx-*`).
- **Link Behavior** (`link_behavior` select) — none / url / lightbox / video.

### Designs shipped (21)

`stacked`, `stacked-center`, `icon-feature`, `side-left`, `side-right`,
`overlay-fade`, `overlay-slide`, `overlay-center`, `overlay-frame`,
`overlay-scrim`, `card`, `caption-below`, `caption-bar`, `polaroid`,
`postcard`, `badge`, `circle-side`, `split-panel`, `photo-stack`,
`editorial-cover`, `flip-card`. Parts: `stacked`, `side`, `overlay`, `card`,
`frame`, `split`, `flip`.

`split` (image + solid accent content panel, equal height) and `flip` (image
front that 3D-flips on hover to a colour back panel) are the two parts with
their own structure; the rest of the new designs are CSS-only variants of
existing parts (`circle-side`→side, `photo-stack`→frame, `editorial-cover`→
overlay). Split / flip back-panels colour from `--imgbox-accent`.

## Options schema (atts)

| Att | Type | Default | Notes |
|-----|------|---------|-------|
| `image` | upload | — | The image. `{url, attachment_id}`. |
| `image_alt` | text | '' | Alt override; else Media Library alt. |
| `subtitle` | text | '' | Eyebrow line above the title. |
| `title` | text | '' | Main heading. |
| `title_tag` | select | `h3` | h2–h6 / span / p. |
| `text` | textarea | '' | Body text (wpautop + wp_kses_post). |
| `icon` | icon-v2 | — | Optional icon. |
| `custom_icon` | text | '' | Emoji / inline SVG; overrides `icon`. |
| `button_style` | select | `none` | none / button / link / arrow. |
| `button_label` | text | Read More | Button text. |
| `design` | image-picker | `stacked` | Registry key. |
| `image_ratio` | select | `ratio-4-3` | original / 1-1 / 4-3 / 3-2 / 16-9 / 3-4 / 2-3. |
| `media_width` | select | `50` | Side designs only: 33/40/50/60 (%). |
| `content_align` | image-picker | inherit | left/center/right (`sc_alignment_field`). |
| `hover_effect` | select | `zoom-in` | none / zoom-in / zoom-out / grayscale / blur / shine / lift / tilt. |
| `transition_speed` | select | `normal` | fast / normal / slow. |
| `overlay_color` | color-compact | — | Scrim tint (custom hex → `--imgbox-ov-color`). |
| `overlay_opacity` | select | `60` | 0–90 (%). |
| `link_behavior` | select | `none` | none / url / lightbox / video. |
| `link_url` | text | '' | URL for url / video behaviors. |
| `link_target` | switch | `_self` | New tab (url behavior). |
| `bg_color`, `title_color`, `subtitle_color`, `content_color`, `icon_color`, `accent_color`, `font_size_preset`, `spacing` | styling | — | Standard styling tab (`sc_*` helpers). `accent_color` custom hex → `--imgbox-accent`. |
| `+ animation + advanced` | — | — | `sc_get_animation_fields()` / `sc_get_advanced_tab()`. |

## Rendering

`views/view.php` builds the reusable fragments (image, icon, eyebrow, title,
text, button), resolves the link/lightbox shell, then `include`s the dispatched
`parts/box-<part>.php`, which only arranges those fragments. Wrapper carries
`imgbox imgbox--design-<key> imgbox--part-<part> imgbox--ratio-<r> imgbox--fx-<fx>`
plus `--imgbox-media-w / --imgbox-ov-opacity / --imgbox-accent / --imgbox-ov-color`
CSS vars. Per-element colors use `sc_extract_styling_atts` (preset class OR
custom-hex inline). The whole inner content becomes an `<a>` when the box is a
link; the button then renders as a `<span>` (never nest anchors).

`static/js/scripts.js` is a dependency-free lightbox for the `lightbox`/`video`
behaviors (YouTube / Vimeo / mp4 / fallback iframe); every other behavior is a
plain anchor.

## Pitfalls

1. **Self-contained** — do NOT call another shortcode's `sc_<name>_*` functions.
   The icon renderer mirrors `icon-box` but is re-declared locally as
   `sc_imgbox_icon_markup`. Shared helpers in `includes/` (`sc_get`,
   `sc_build_wrapper_attr`, `sc_extract_styling_atts`, `sc_*_field`) are fine.
2. **Picker choice keys are non-empty strings** — they are the saved `design`
   value AND the CSS class suffix AND the per-design CSS filename.
3. **No nested anchors** — when `link_behavior` makes the box clickable, the
   button is emitted as a `<span>`, not an `<a>`.
4. **Per-design CSS auto-gates by filename** — adding `static/css/design/<key>.css`
   makes it load only for instances using that design; no enqueue list.

## Files

- `config.php` — page-builder config (Media Elements tab, title template).
- `options.php` — edit-modal fields (atts schema); Design picker built from the registry.
- `static.php` — base CSS/JS enqueue + per-design CSS auto-gate hook.
- `views/view.php` — fragment builder + dispatcher.
- `views/parts/registry.php` — design catalog (single source of truth).
- `views/parts/box-{stacked,side,overlay,card,frame,split,flip}.php` — layout parts.
- `static/css/styles.css` — base/structural + hover-effect + lightbox CSS.
- `static/js/scripts.js` — lightbox.
- `static/img/page_builder.svg` — 16×16 item icon.
- `static/img/design/*.svg` — design-picker thumbnails.
