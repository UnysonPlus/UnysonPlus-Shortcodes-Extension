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

## Design system — 5 FAMILIES (popover multi-picker) → flat design keys

The Design control is a **popover `multi-picker`** (att id **`design_settings`**) of **5 layout
families**: **Stacked / Side / Overlay / Card / Frame**. (A flip look is intentionally NOT here —
the dedicated `flip-box` shortcode owns that.) Picking a family reveals only that
family's **variation** sub-options in the popover panel. At render time the variations **collapse
back to one flat design key** (the same 21 keys as before), so the 7 PHP parts and the
`.imgbox--design-<key>` CSS are reused unchanged.

**Saved value shape** (a NEW key — never a legacy scalar, so an old string can't feed the
multi-picker and trip the illegal-string-offset "blank error:" modal trap):

```php
design_settings => [
    'family'  => 'overlay',
    'overlay' => [ 'reveal' => 'fade', 'overlay_color' => [...], 'overlay_opacity' => '60' ],
]
```

**Back-compat:** a legacy scalar `design="overlay-slide"` still resolves — `resolve.php` falls
back to the flat `design` att when `design_settings` is absent, using the registry `legacy` map.

### Files & readers

- `views/parts/registry.php` — single source of truth. Returns `families` (popover tiles),
  `designs` (flat key → `part` + `content_over_image`/`hover_reveal` flags — the render target),
  and `legacy` (old flat key → family + variation values).
- `views/parts/resolve.php` — `sc_imgbox_resolve_design($atts, $registry)` and
  `sc_imgbox_family_to_key($family, $sub, $registry)`. **Shared by view.php AND static.php** so
  both derive the same flat key. Add per-family collapse rules here.
- `options.php` — builds the family image-picker (from `families`) + per-family variation
  `choices`. `popover => true`; **label on the TOP level**; picker sub-option `family` has
  `label => false` (popover convention — see `animation-engine/modules/cursor/cursor.php`).
- `views/view.php` — calls the resolver, dispatches to `parts/box-<part>.php`, emits
  `imgbox--design-<flatkey>` etc. Reads `media_width` / `overlay_color` / `overlay_opacity` from
  the resolved family sub-values (`$design_sub`), legacy top-level as fallback.
- `static.php` — resolves the flat key via the shared resolver, auto-gates
  `static/css/design/<key>.css`.

### How to add a look

- **A variation of an existing family** (front-end only): add a `choices[<family>]` sub-option in
  `options.php` and a collapse rule in `resolve.php`. If it maps to an existing flat key, done.
- **A genuinely new appearance** (e.g. a new magazine overlay): add a `designs` entry (part +
  flags), reference it from an Overlay `reveal` choice, and drop `static/css/design/<key>.css`
  (auto-gated). The Overlay resolver already treats an unknown `reveal` value as a direct flat key
  if it exists in `designs`.

Axes that compose on top of the family:
- **Design family + variation** (`design_settings`).
- **Image Size** (`image_size` short-select, universal) — full / large / medium / small / xsmall; shrinks
  + centres the media on image-top families (`imgbox--size-*`). Side uses Media Width; overlay fills.
- **Image Mask** (`image_mask` multi-picker, universal, 22 shapes + None) — clip the media to a
  shape (`imgbox--mask-*`): border-radius (rounded / rounded-xl / circle / squircle / arch / leaf),
  clip-path (hexagon / diamond / triangle / pentagon / star / chevron / octagon / shield), SVG mask
  (heart / flower / brush / water-splash / grunge-frame / blob-1 / blob-2). Shape masks
  force a square crop. Data-URI SVG masks live inline in the CSS; the two complex ones
  (`water-splash`, `grunge-frame`) reference `static/img/mask/src-<key>.svg`. To add one: a choice
  in the `image_mask` picker + a `.imgbox--mask-<key> .imgbox__media` rule + a thumbnail
  `static/img/mask/<key>.svg`.
  - **`custom`** tile → the multi-picker reveals three inputs: `custom_svg` (paste inline `<svg>` or
    an .svg URL), `custom_upload` (SVG from Media Library), `custom_clip` (a raw CSS clip-path).
    Priority: SVG source → clip-path. `view.php` sanitizes (`sc_imgbox_sanitize_svg` /
    `sc_imgbox_sanitize_clip`) and emits `--imgbox-mask` (→ `.imgbox--maskcustom-svg`) or
    `--imgbox-clip` (→ `.imgbox--maskcustom-clip`). Inline SVG is only ever placed in a CSS
    `mask-image` data-URI (secure static — no script exec); we strip `<script>`/`on*` anyway.
    Custom keeps the crop ratio. **`image_mask` is now a multi-picker** (`{ mask: '<key>',
    custom: { custom_svg, custom_upload, custom_clip } }`); a legacy scalar still resolves.
  This replaces the old `circle-side` and `icon-feature`
  designs** (= Circle mask [+ Small size]); those keys remain only as legacy aliases.
- **Hover Effect** (`hover_effect` select) — zoom / grayscale / shine / lift / tilt / blur (CSS,
  `imgbox--fx-*`), layered on ANY design.
- **Link Behavior** (`link_behavior` select) — none / url / lightbox / video.

### Families → variations → flat keys

| Family | Variations | Flat keys |
|---|---|---|
| Stacked | `stacking` (image/heading/text order: img-title-text / title-img-text / title-text-img / text-img-title) | `stacked` |
| Side | `image_side`, `panel`, `media_width` | `side-left`, `side-right`, `split-panel` |
| Overlay | `reveal` (scrim/cover/**overlap**/bar/fade/slide/center/frame + new keys), `overlay_color`, `overlay_opacity` | `overlay-scrim`, `editorial-cover`, `overlay-offset`, `caption-bar`, `overlay-fade`, `overlay-slide`, `overlay-center`, `overlay-frame` |
| Card | `style` (card/caption-below) | `card`, `caption-below` |
| Frame | `style` (polaroid/postcard/badge/photo-stack) | those 4 |

Alignment (incl. the old Stacked "centered") lives on the universal **Content Alignment**
(`imgbox--is-*`). Legacy keys `stacked-center`, `icon-feature`, `circle-side` stay in `designs` +
CSS for back-compat but have no picker path.

Parts: `stacked`, `side`, `overlay`, `overlap`, `card`, `frame`, `split`. `split` (image + solid
accent panel) and `overlap` (content panel overlaps the image edge via a CSS-grid overlap — a
sibling of the media, not inside the figure, so it can extend beyond the image) are the structural
parts; the rest are CSS variants. The split back-panel colours from `--imgbox-accent`.

## Options schema (atts)

| Att | Type | Default | Notes |
|-----|------|---------|-------|
| `image` | upload | — | The image. `{url, attachment_id}`. |
| `image_alt` | text | '' | Alt override; else Media Library alt. |
| `subtitle` | text | '' | Eyebrow line above the title. |
| `title` | text | '' | Main heading. |
| `title_tag` | select | `h3` | h2–h6 / span / p. |
| `text` | wp-editor | '' | Body text (wpautop + wp_kses_post). |
| `icon` | icon-v2 | — | Optional icon. Takes precedence over `custom_icon`. |
| `custom_icon` | hidden | '' | Retired/legacy (emoji / inline SVG). Rendered only as a fallback when the `icon` picker is empty. |
| `button_style` | select | `none` | none / button / link / arrow. |
| `button_label` | text | Read More | Button text. |
| `design_settings` | multi-picker (popover) | `{ family: 'stacked' }` | Family + variation values (see table above). Legacy `design` scalar still resolves. `media_width` (Side), `overlay_color` / `overlay_opacity` (Overlay) live INSIDE this. |
| `image_ratio` | select | `ratio-4-3` | original / 1-1 / 4-3 / 3-2 / 16-9 / 3-4 / 2-3. |
| `image_size` | short-select | `full` | Universal. full/large/medium/small/xsmall → `imgbox--size-*` (image-top families). |
| `image_mask` | multi-picker | `{ mask: 'none' }` | Universal. `mask` = shape key → `imgbox--mask-*`; `custom` reveals `custom_svg` / `custom_upload` / `custom_clip` → `imgbox--maskcustom-svg|clip` + `--imgbox-mask` / `--imgbox-clip`. |
| `content_align` | image-picker | inherit | left/center/right (`sc_alignment_field`). |
| `hover_effect` | select | `zoom-in` | none / zoom-in / zoom-out / grayscale / blur / shine / lift / tilt. |
| `transition_speed` | select | `normal` | fast / normal / slow. |
| `link_behavior` | select | `none` | none / url / lightbox / video. |
| `link_url` | text | '' | URL for url / video behaviors. |
| `link_target` | switch | `_self` | New tab (url behavior). |
| `bg_color`, `title_color`, `subtitle_color`, `content_color`, `icon_color`, `accent_color`, `font_size_preset`, `spacing` | styling | — | Standard styling tab (`sc_*` helpers). `accent_color` custom hex → `--imgbox-accent`. |
| `+ animation + advanced` | — | — | `sc_get_animation_fields()` / `sc_get_advanced_tab()`. |

## Rendering

`views/view.php` builds the reusable fragments (image, icon, eyebrow, title,
text, button), resolves the link/lightbox shell, then `include`s the dispatched
`parts/box-<part>.php`, which only arranges those fragments. Wrapper carries
`imgbox imgbox--design-<key> imgbox--part-<part> imgbox--ratio-<r> imgbox--fx-<fx> imgbox--speed-<speed>`
(plus conditional `imgbox--is-* / --content-over / --hover-reveal / --linked / --no-image /
--size-<s> / --mask-<m> / --stack-<order> / --maskcustom-svg|clip`)
plus `--imgbox-media-w / --imgbox-ov-opacity / --imgbox-accent / --imgbox-ov-color / --imgbox-mask / --imgbox-clip`
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
2. **Flat design keys are non-empty strings** — the resolver output is the CSS class suffix
   (`imgbox--design-<key>`) AND the per-design CSS filename. Family/variation values collapse to
   these via `resolve.php`; keep the two in sync (a new `designs` key needs a collapse rule).
3. **No nested anchors** — when `link_behavior` makes the box clickable, the
   button is emitted as a `<span>`, not an `<a>`.
4. **Per-design CSS auto-gates by filename** — adding `static/css/design/<key>.css`
   makes it load only for instances using that design; no enqueue list.

## Files

- `config.php` — page-builder config (Media Elements tab, title template).
- `options.php` — edit-modal fields (atts schema); Design picker built from the registry.
- `static.php` — base CSS/JS enqueue + per-design CSS auto-gate hook.
- `views/view.php` — fragment builder + dispatcher (calls the resolver).
- `views/parts/registry.php` — families + flat designs + legacy map (single source of truth).
- `views/parts/resolve.php` — family+variation → flat design key (shared by view.php + static.php).
- `views/parts/box-{stacked,side,overlay,overlap,card,frame,split}.php` — layout parts.
- `static/css/styles.css` — base/structural + hover-effect + lightbox CSS.
- `static/js/scripts.js` — lightbox.
- `static/img/page_builder.svg` — 16×16 item icon.
- `static/img/design/*.svg` — design-picker thumbnails.
