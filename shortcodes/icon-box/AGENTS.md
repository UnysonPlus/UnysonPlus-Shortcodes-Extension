---
type: shortcode
name: icon-box
since: original Unyson (substantially extended in the Unyson+ fork — badge shapes, six layouts, box link)
provides: leaf-shortcode
---

# Icon Box

A composite: icon + title + body content, with six layouts for how those
three pieces sit relative to each other, an optional decorative
"badge" background around the icon (7 shapes — solid / outline × square /
rounded / circle, plus none), and an optional box-wide clickable link
with SEO rel control.

## Registration

No custom class file — leaf shortcode auto-instantiated. No item class.

`config.php` declares an elaborate `title_template` that previews the
icon (custom emoji / custom upload / font icon), title, and trimmed body
on the canvas — so the page-builder item shows all three at a glance
without opening the modal.

## Options schema (atts)

Source of truth: `options.php`. Four tabs + Animations + Advanced.

### Tab: Content

Wrapped in `group_content` (flattens).

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `icon` | `icon-v2` (modal: medium) | — | Font icon / Lucide SVG / emoji / pasted-or-uploaded SVG — all from one picker. **Takes precedence over the legacy `custom_icon`** |
| `custom_icon` | `hidden` (retired) | — | Legacy emoji / inline-SVG field, now hidden. Only rendered as a **fallback** when `icon` is empty, so pre-picker saves still show. Not editable in the UI |
| `title` | `text` | — | Headline |
| `title_tag` | `select` (`h3` / `h4` / `h5` / `h6` / `span` / `p`) | `h3` | Semantic tag for the title |
| `content` | `wp-editor` (225px) | — | Body content — rich text |

### Tab: Layout

Wrapped in `group_layout` (flattens).

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `style` | `image-picker` (SVG layout swatches in `static/img/layout/`) | `top-title` | Icon position: `top-title` (above), `inline-left`/`inline-right` (beside title), `stack-left`/`stack-right` (beside title AND content), `between-title-content` (as a divider between title and body). Values unchanged — only the control is now visual |
| `icon_badge` | `image-picker` (`none` / `solid-square` / `solid-rounded` / `solid-circle` / `outline-square` / `outline-rounded` / `outline-circle`) | `none` | Decorative badge background/ring around the icon. Pair with `icon_badge_color` on Styling tab |
| `icon_align` | `image-picker` via `sc_alignment_field(inherit:true)` (`''` / `left` / `center` / `right`) | `''` (inherit) | Horizontal alignment of the icon. **Only takes effect when `style` is `top-title` or `between-title-content`** — inline/side layouts use flexbox. Legacy `start`/`end` still resolve |
| `title_align` | `image-picker` via `sc_alignment_field(inherit:true)` (`''` / `left` / `center` / `right`) | `''` (inherit) | Title text alignment — outputs a Bootstrap `text-*` utility class. Legacy `start`/`end` still resolve |
| `content_align` | `image-picker` via `sc_alignment_field(inherit:true)` (`''` / `left` / `center` / `right`) | `''` (inherit) | Body text alignment. Legacy `start`/`end` still resolve |
| `mobile_stack` | `switch` | `true` | Force icon to top on small screens regardless of `style` |

### Tab: Link & SEO

Wrapped in `group_link` (flattens).

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `box_link` | `text` | — | Optional URL. When set, the entire icon box becomes a clickable anchor |
| `link_target` | `switch` | `false` | Open in new tab |
| `link_rel` | `select` (`none` / `nofollow` / `sponsored`) | `sponsored` | `rel` attribute for SEO hints |

### Tab: Styling

Wrapped in `group_colors` + `group_spacings` (both flatten).

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `bg_color` | `sc_color_field_compact` (bg) | — | Wrapper background |
| `font_size_preset` | `sc_font_size_field` | — | Named size from theme presets |
| `title_color` | `sc_color_field_compact` | — | Title color |
| `content_color` | `sc_color_field_compact` | — | Body color |
| `icon_color` | `sc_color_field_compact` | — | Icon glyph color (font icons only) |
| `icon_badge_color` | `sc_color_field_compact` (bg) | — | Badge color — fill for solid shapes, border for outline shapes. **Ignored when `icon_badge === 'none'`** |
| `spacing` | `sc_spacing_field` | — | Wrapper margin/padding |

### Tabs: Animations + Advanced

Standard.

### Verified `atts` (real export)

One method tile from `newbingosites.net mockup/payment-method-section-6e4d05fe.json`
(plugin 2.8.49, `format_version` 2) — shared `animation`/`*_color`/`spacing` blocks
elided; see the page-builder format guide §3:

```json
{"icon":{"type":"none"},"custom_icon":"<svg viewBox=\"0 0 24 24\" …></svg>","title":"Payforit","title_tag":"span","content":"<p>Phone-bill deposits</p>","style":"stack-left","icon_badge":"solid-rounded","icon_align":"","title_align":"","content_align":"","mobile_stack":true,"box_link":"","link_target":false,"link_rel":"sponsored","bg_color":{"predefined":"","custom":""},"font_size_preset":"","title_color":{"predefined":"","custom":""},"content_color":{"predefined":"","custom":""},"icon_color":{"predefined":"text-white","custom":""},"icon_badge_color":{"predefined":"","custom":"#185fa5"},"spacing":{…},"animation":{…},"unique_id":"…","css_id":"","css_class":"method-tile","custom_css":"","responsive_hide":[],"custom_attrs":[]}
```

Non-obvious serialized shapes (the table values above are correct; these are how they
encode on the wire):

- **`icon` is an object, never a string** — even when unused it serializes as the
  `icon-v2` empty shape `{"type":"none"}`. A set `icon` renders and the legacy `custom_icon`
  is ignored; here `icon` is the empty `{"type":"none"}`, so the `custom_icon` fallback
  (inline SVG with its quotes backslash-escaped, or an emoji) is what renders (Pitfall 1).
- **`mobile_stack` and `link_target` are real JSON booleans** (`true` / `false`) — *not*
  the `"yes"`/`"no"` strings most other switches use. Emit booleans for these two.
- `icon_color.predefined` carries a `text-{slug}` preset class (`text-white` /
  `text-black`); `icon_badge_color.custom` carries a hex (`#185fa5`). Preset vs custom are
  mutually exclusive (set one, leave the other `""`) per the format guide §3.
- A row of equal-width tiles is authored as sibling `1_4` **columns**, each holding one
  `icon_box` leaf — the icon-box itself has no multi-column mode.

## Rendering

`views/view.php` (refer to file) composes a `<div class="icon-box__wrapper
icon-box--style-{style}">` wrapper, adding `icon-box--mobile-stack`,
`icon-box--linked` (when `box_link` set), `icon-box--no-content`, and
`icon-box--no-icon` as applicable. The badge shape class lives on the icon
`<span>` (`icon-box__icon--has-badge icon-box__icon--badge-{shape}`), and the
per-element alignment (`text-*`) / color classes go on the title / content /
icon nodes — not the wrapper. When `box_link` is set the wrapper stays a
`<div>` and an inner `<a class="icon-box__link">` wraps the box contents
(href + optional `target="_blank"` / `rel`, plus an `aria-label`).

Layout strategy is mostly CSS (flexbox direction switches), so generators
don't need to think about DOM structure changes per `style` — they just
emit the att.

## Pitfalls

1. **`icon` now wins; `custom_icon` is a hidden legacy fallback** — the
   unified `icon-v2` picker covers font / Lucide SVG / emoji / uploaded-or-
   pasted SVG, so the separate `custom_icon` field was retired to a `hidden`
   option. The view renders the picked `icon` first and only falls back to
   `custom_icon` when `icon` is empty (preserving pre-picker saves).
   Generators should emit `icon`; treat `custom_icon` as read-only legacy data.
2. **`icon_align` only applies to certain `style` values** — `top-title`
   and `between-title-content` honor it; `inline-*` and `stack-*` ignore
   it (flexbox positions the icon by direction). Set both freely; the
   ignored case just no-ops.
3. **`mobile_stack: true` is the default** — explicitly turn off if you
   want side layouts to persist on mobile (rarely desirable).
4. **`box_link` makes the whole wrapper clickable** — interactive
   children inside the wrapper (an embedded `[button]`, a link in the
   `content` editor) will have their click events captured by the outer
   anchor unless you handle propagation in the body. AI generators
   producing icon-boxes with `box_link` set should avoid nesting
   interactive elements inside `content`.
5. **`icon_badge_color` for outline badges sets BORDER color, not bg** —
   the field metadata says `kind: 'bg'` for the picker UI, but the view
   applies it as `border-color` on outline shapes. Same field key, two
   meanings depending on `icon_badge`.

## Verification

1. Drag Icon Box → modal opens; pick a font icon, set title + content.
2. Reload → renders with default `top-title` layout.
3. Switch `style: stack-left` → icon moves to left of title+content.
4. Set `icon_badge: solid-circle`, `icon_badge_color` → circular badge
   appears around the icon.
5. Switch `icon_badge: outline-rounded` → badge becomes a ring (border
   uses the same color value).
6. Set `box_link: https://example.com` → entire box becomes clickable;
   verify external link in new tab if `link_target: true`.
7. On a pre-picker box that still holds a `custom_icon` value, leave `icon`
   empty → reload → the legacy `custom_icon` renders as a fallback; then set
   the `icon` picker → the picked icon takes over.

## Files

- `config.php`, `options.php`, `static.php`, `views/view.php`
- `static/css/styles.css` (via static.php) — layout + badge shape CSS
- `static/img/layout/*.svg` — icon-position swatches for the `style` image-picker
- `static/img/badge/*.svg` — badge shape thumbnails for the image-picker
- `static/img/page_builder.svg` — Layout Elements thumbnail

No JS, no item class — leaf layout.
