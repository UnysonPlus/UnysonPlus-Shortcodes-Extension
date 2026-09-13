# Shortcode de-bootstrapping — status tracker

Single source of truth for the "remove Bootstrap-style classes from shortcode
**output**" initiative (native CSS grid instead of `fw-row`/`fw-col`, self-contained
containers instead of `fw-container`, semantic classes instead of Bootstrap utility
classes like `img-fluid`/`mx-auto`/`d-flex`). Update this file whenever a shortcode is
converted or reviewed so we always know what's done vs. pending.

Last updated: 2026-09-10 (media-image + media-video de-bootstrapped)

## ✅ Done — grid / container converted to native CSS grid + self-contained wrappers

| Shortcode | What changed | Manifest |
|---|---|---|
| `image-content` | fw-row/fw-col → CSS grid; Margin option; bare-wrapper merge; self-contained styles.css | 1.14.78/79 |
| `tabs` | Media-panel + Vertical layouts → CSS grid | 1.14.80 |
| `testimonials` | Classic grid → CSS grid; container → `.testimonials-container`; default container None; grid-into-wrapper merge; dropped `design-default`; removed root margin; **all 11 designs demoed**; speech-bubble redesign; **all Bootstrap utility classes removed from card renderer** (see below) | 1.14.81 / 1.14.86 |
| `table` (pricing mode) | pricing `fw-col-sm-N` → CSS grid (`--pt-tracks`) | 1.14.83 |
| `bleed-section` | `fw-container`/`fw-row`/`fw-col-md` → CSS grid (`--bs-cols`) + `.bleed-section__container`; content padding | 1.14.84 |
| `gallery` | opt-in container → `.fw-gallery__container`; `.fw-gallery` width/min-width fix; **all 18 designs demoed**; flipcards inline-span bug fixed; **honeycomb rebuilt as a true interlocking hex layout** | 1.14.85 |
| `media-image` | `img-fluid` → self-contained `.media-image__img` (own tiny stylesheet); dropped the builder frontend-grid dependency | 1.14.90 |
| `media-video` | `mx-auto` → `.video-wrapper { margin-inline:auto }`; Bootstrap `.ratio*` → self-contained aspect-ratio box scoped to `.video-wrapper`; dropped the builder frontend-grid dependency | 1.14.90 |

## ✅ Done — Bootstrap utility classes removed from output

- `testimonials` — card renderer (`sc_render_card`) + Classic single/carousel: `img-fluid`,
  `mx-auto`, `text-center`, `flex-shrink-0`, `mb-3`, `fw-semibold`, `d-flex`, `w-100`,
  `flex-md-row`, `gap-3`, `flex-grow-1`, `mt-2` → semantic `.testimonial-*` classes. (1.14.86)

## ⬜ Pending — genuine remaining Bootstrap-style output

_None known in shortcode **output**._ (media-image / media-video done above.)

> Note (out of scope — different project): the **`unysonplus-theme`** header still emits
> `img-fluid` on the site logo (`.site-logo … img-fluid`). That's theme chrome, not shortcode
> output, so it's not part of this initiative — flagged here only so it isn't mistaken for a
> shortcode leak on a re-scan.

## ✅ DOM-depth optimizations (fewer wrapper divs)

- `media-video` — Bootstrap `.ratio ratio-16x9` inner box → the aspect box now rides the
  wrapper itself (`.video-ratiobox` + `data-ratio`) when the wrapper is BARE (`sc_wrapper_is_bare`),
  saving one `<div>`; a styled wrapper (padding/bg frame) keeps a nested box so the frame still
  shows. Also drops `mx-auto` (→ `margin-inline:auto`) and adds `loading="lazy"` to embed iframes. (1.14.91)
- `posts` — `.posts__layout-wrap` + `.posts__main` now emitted ONLY for left/right-sidebar
  filter layouts; without a sidebar the `.posts__grid` sits directly in `.posts` (was
  `.posts › layout-wrap › main › grid`, now `.posts › grid`). (1.14.87)

## 🚫 Intentionally NOT converted (the grid system itself)

`section`, `column`, `container`, `flexbox`, `row`, `masonry-section` — these ARE the
page-builder grid primitives; `fw-row`/`fw-col` are their layout foundation. Leave them.

## 📐 Demo-page bands must be FLEXBOX, not classic `section` (REQUIRED)

Every per-shortcode demo band uses the `fsec` / `fsecCols` helpers (a `flexbox` section) — NOT
`section()` (which renders the classic `.fw-container > .fw-row > .fw-col` bootstrap grid). A
multi-item row (e.g. image-box's uniform box sets) uses `fsecCols([ column('1_1',[sh]), col(...),
… ])` — a full-width heading column flex-wraps onto its own line, then the item columns wrap into a
row (the framework's `.fw-flexbox:has(> .fw-col-*)` grid CSS handles column widths + gutters).
Legit exceptions: `bleed-section` bands ARE the bleed_section element; `media-video` has ONE
`section` band for the "Use as Section Background" demo (the bg-fill runtime needs a real section).
Audit with: `node -e` over each `shortcodes/<slug>.json` counting band `type`s.

## 🔎 Design-system demos reviewed for "all designs + look right"

- Reviewed & fixed: `gallery` (18 designs), `testimonials` (11 designs), `image-box`
  (19 bands — ONE design per band shown as a UNIFORM set of matching boxes; mixing different
  designs in a row looked ragged/"messy", so each band is a single design like gallery).
  Demo uses `design_settings:{family,[family]:{sub}}` since a bare `design` scalar is dropped by
  normalization; set-item titles kept equal-length so content-driven designs (overlap panel)
  render uniform. Verified all render distinctly, per-design CSS loads, every band spread≈0, no
  overflow. NOTE: legacy flat keys `stacked-center`/`icon-feature`/`circle-side` are not separate
  picker designs — they're the stacked/side families + universal options.
- `posts` — demo now shows ALL 4 layout modes (grid/list/masonry/slider) + ALL 24 card styles
  (registry: standard, side/-left/-right, overlay, minimal, hero-split, alternating, gradient,
  listicle, newslist, editorial, polaroid, timeline, tile, circular, accent, cover, quote, postcard,
  badge, filmstrip, diagonal, glass), each in its own band. Rendered: 28 bands, 144 cards, no overflow.
  (Structure verified; a visual pass of each card style is still worth doing.)
- `before-after` — demo shows all 7 design skins (classic/circle/arrows/line/invisible/labeled/
  framed) + 3 behavior bands (vertical / hover / toggle). Demo-only (no shortcode bug); designs
  live under the `type` multi-picker (`type/comparison/design` etc.), NOT a flat `design` scalar
  (that's dropped by normalization — same gotcha as image-box).
- `accordion` — demo shows all 5 styles (bordered / separated / flush / filled / ghost via the
  `accordion_style` image-picker) + option bands (chevron icon, Q-prefix numbering, multiple-open +
  Expand/Collapse-all). 8 flexbox bands. Demo-only (no shortcode bug).
- NOT yet reviewed (have design systems — worth a demo pass like gallery got):
  `audio-player`, plus the many CSS-class design shortcodes
  (`feature-list`, `flip-box`, `image-hotspots`, `logo-grid`, `modal-popup`, `pricing-table`,
  `star-rating`, `steps`, `timeline`, `tooltip`, `video-popup`, `blockquote`, `avatar`, …).
  NOT multi-design (skip the all-designs pass): `carousel` (single slider + options),
  `map` (tile providers, not visual designs).

## How to re-scan (authoritative)

Class emissions can hide in PHP-built arrays, so grep both `class="..."` literals AND
array/concatenation tokens — do NOT rely on `class="..."` matching alone:

```
grep -rhnE "fw-row|fw-col|fw-container|row-cols|col-(sm|md|lg|xl)-|\bimg-fluid\b|\bmx-auto\b|\bd-flex\b|\bfw-semibold\b|\bflex-shrink-0\b|\bflex-md-row\b|\bflex-grow-1\b|\btext-md-start\b" \
  framework/extensions/shortcodes/shortcodes/<name>/views/ \
  framework/extensions/shortcodes/shortcodes/<name>/static.php | grep -vE "^\s*(//|\*)|/\*"
```
