---
type: shortcode
name: special-heading
since: original Unyson
provides: leaf-shortcode
---

# Special Heading

An **overline → title → subtitle** stack with a configurable semantic heading
tag. The go-to element for opening a section ("About Us" / "Our latest work" /
etc.). The optional overline is a small label above the title (e.g. a section
name like "FAQs"); the title's size comes from the heading tag (H1–H6) or an
optional display-size override; the subtitle sits below with its own size /
measure controls.

## Registration

No custom class file — leaf shortcode auto-instantiated. No item class.

`config.php` declares a `title_template` that previews the heading + a
subtitle div on the canvas, showing the actual heading-level styling.

## Options schema (atts)

Source of truth: `options.php`. Content + Layout + Styling tabs + Animations + Advanced.

### Tab: Content

Wrapped in `group_content` (flattens).

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `title` | `text` | — | Heading text |
| `overline` | `text` | — | Small label rendered **above** the title (e.g. "FAQs"). Empty = hidden |
| `subtitle` | `text` | — | Subtitle below the heading |
| `heading` | `select` (`h1` / `h2` / `h3` / `h4` / `h5` / `h6`) | `h2` | Semantic HTML tag for the title |

### Tab: Layout

Two groups. Alignment uses the **reusable** `sc_alignment_field()` helper (an
`image-picker` of L / C / R swatches from `static/img/alignment/`); map a stored value
to a Bootstrap `text-*` class with `sc_alignment_class()` (both in
`includes/shortcode-styling-helper.php`).

`group_alignment` — master + per-element (the per-element pickers carry an extra
`''` = **Inherit** swatch and default to it, i.e. follow the master):

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `alignment` | `image-picker` (`left` / `center` / `right`) | `left` | **Master** alignment for the whole heading. Supersedes the legacy `centered` switch (still honored: `centered:"yes"` → center when `alignment` unset) |
| `overline_align` | `image-picker` (`''` / `left` / `center` / `right`) | `''` | Overline alignment; `''` = inherit the master `alignment` |
| `title_align` | `image-picker` (`''` / `left` / `center` / `right`) | `''` | Title alignment; `''` = inherit |
| `subtitle_align` | `image-picker` (`''` / `left` / `center` / `right`) | `''` | Subtitle alignment; `''` = inherit |

`group_layout`:

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `overline_uppercase` | `switch` (`yes` / `no`) | `no` | Small letter-spaced uppercase kicker (`--kicker`). Independent of the marker / container |
| `overline_marker` | `select` (`''` / `rule` / `dot` / `lines` / `bar`) | `''` | Leading/flanking mark: `rule` line, `dot` ●, `lines` flanking both sides, `bar` vertical pipe (`--rule`/`--dot`/`--lines`/`--bar`) |
| `overline_marker_position` | `select` (`before` / `after`) | `before` | `after` → `--mark-after` (moves a single marker past the text). Ignored for `lines` / no marker |
| `overline_container` | `select` (`''` / `pill` / `pill-outline` / `underline`) | `''` | Shape around the label: `pill` tinted badge, `pill-outline` bordered badge, `underline` (`--pill`/`--pill-outline`/`--underline`). Pill tint / border follow the Overline Color |

> **Independent axes:** case, marker and container compose freely (e.g. a pill with no
> marker, a dot without a pill). **Legacy `overline_style`** (the pre-split preset:
> `uppercase`/`rule`/`uppercase-dot`/`pill`/… and older `kicker`/`kicker-rule`) is still
> read by the view and mapped to these axes, so headings saved before the split render
> unchanged — but don't emit `overline_style` in newly-authored trees.
| `element_spacing` | `select` (`''` / `tight` / `relaxed`) | `''` | Vertical rhythm between overline → title → subtitle. `''` = Normal (theme defaults); the others add `.heading--space-{tight\|relaxed}` to the wrapper |
| `block_max_width` | `unit-input` (units `px` / `%` / `rem` / `em` / `ch` / `vw`; default unit `px`) | `{value:'',unit:'px'}` | Constrain the whole heading block, e.g. `720px` / `50ch` (inline `max-width` on the wrapper; auto-centered when master `alignment:center`). Value is `{value,unit}` — legacy plain strings still resolve |

> **`centered` (legacy):** older saved content may carry `centered:"yes"`/`"no"`. The
> view reads it as a fallback when the master `alignment` is empty, so old headings keep
> centering. New content writes `alignment`. Don't emit `centered` in newly-authored trees.

### Tab: Styling

Two new groups. **No wrapper `font_size_preset`** — the title's size comes from the
`heading` tag (H1–H6) or `display_size`; the subtitle has its own `subtitle_size`.

`group_typography`:

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `display_size` | `select` (`''` / `display-1`…`display-6`) | `''` | Visually enlarge the title independently of its tag (Bootstrap `.display-*` class on the title). `''` = use the tag's own size |
| `subtitle_size` | `sc_font_size_field` | `''` | Named font-size preset applied to the subtitle only (value **is** the CSS class) |
| `subtitle_max_width` | `unit-input` (units `ch` / `px` / `rem` / `em` / `%` / `vw`; default unit `ch`) | `{value:'',unit:'ch'}` | Readability measure for the subtitle, e.g. `60ch` / `600px` (inline `max-width`; auto-centered when the subtitle is centered). Value is `{value,unit}` — legacy plain strings still resolve |

`group_colors`:

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `bg_color` | `sc_color_field_compact` (bg) | — | Wrapper background |
| `overline_color` | `sc_color_field_compact` (text) | — | Overline label color |
| `title_color` | `sc_color_field_compact` (text) | — | Title color |
| `subtitle_color` | `sc_color_field_compact` (text) | — | Subtitle color |

`group_spacings`:

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `spacing` | `sc_spacing_field` | — | Wrapper margin/padding |

### Tab: Advanced

Standard `sc_get_advanced_tab()` plus two extra fields specific to this
shortcode (both inside the `advanced_settings` group, which flattens):

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `overline_class` | `text` | — | Extra CSS class(es) added to the overline label |
| `title_class` | `text` | — | Extra CSS class(es) added to the title tag |
| `subtitle_class` | `text` | — | Extra CSS class(es) added to the subtitle |

### Tabs: Animations

Standard.

## Rendering

`views/view.php` outputs (in order) an optional `<div class="heading-overline …">`,
then `<h{n} class="heading-title …">{title}</h{n}>` where `{n}` comes from `heading`,
then `<div class="heading-subtitle …">{subtitle}</div>`. The three are wrapped in a
`<div class="heading heading-{tag} …">` **only when needed** (`sc_needs_wrapper()`, a
non-default `element_spacing`, or a `block_max_width`); otherwise they render bare.

**Alignment is per-element**, applied as a Bootstrap `text-*` class **on each element**
(not the wrapper): each element resolves its own `*_align` value, falling back to the
master `alignment` when empty (`sc_alignment_class()` maps `left→text-start`,
`center→text-center`, `right→text-end`). The `kicker-rule` overline is `display:flex`,
so its alignment class drives `justify-content` instead (CSS in `static/css/styles.css`).
`element_spacing` adds `.heading--space-{tight|relaxed}` to the wrapper; `block_max_width`
is an inline `max-width` on the wrapper (auto-centered when master `alignment:center`).

Per-element color picks (`overline_color` / `title_color` / `subtitle_color`) are pulled
off the wrapper with `sc_extract_styling_atts()` and applied to their own element (class
or inline hex). `display_size` adds a `.display-*` class to the title; `subtitle_size`'s
value IS the font-size class appended to the subtitle; `subtitle_max_width` is an inline
`max-width` on the subtitle.

**Overline markup & styling:** the text is wrapped in an inner
`<span class="heading-overline__label">`. The outer `.heading-overline` is a flex row whose
`justify-content` follows the overline alignment class; the inner label shrink-wraps and carries
the case / marker / container. The three Layout-tab axes add independent modifier classes on the
outer element — `--kicker` (uppercase), markers `--rule` / `--dot` / `--lines` / `--bar` (leading
line / dot / flanking lines / vertical bar via `::before`/`::after`, with `--mark-after` flipping a
single marker past the text via flex `order`), and containers `--pill` / `--pill-outline` /
`--underline`. The pill tint / outline-pill border use `color-mix(in srgb, currentColor …)` with an
`rgba()` / solid fallback, so a single **Overline Color** drives the marker, text and pill together.
CSS in `static/css/styles.css`.

## Pitfalls

1. **Use semantic `heading` correctly** — H1 should be once per page.
   For most page sections, H2 (default) is right; nested sub-sections
   use H3 / H4. Generators producing multiple special-headings on a page
   should not default everything to H1.
2. **Title size: tag vs `display_size`** — the tag drives size by default; use
   `display_size` (`.display-1`…`-6`) to render a semantically-correct tag (e.g. an `h2`)
   visually larger/smaller **without** changing the tag. Prefer this over a hand-rolled
   `title_class` for sizing.
3. **Alignment: master + inherit** — set the master `alignment` to align all three at
   once; set a per-element `*_align` only to override one line. `''` on a per-element
   picker means inherit. `alignment` supersedes the legacy `centered` (back-compat
   fallback only when `alignment` is unset; don't set both).
4. **`subtitle` / `overline` are plain text fields** — generators emitting HTML markup
   in them will see it rendered raw (`wp_kses_post`). Most usages should be plain text.
5. **Reuse `sc_alignment_field()` / `sc_alignment_class()`** for alignment in other
   shortcodes rather than hand-rolling a select — same swatches, same value vocabulary.

## Verification

1. Drag Special Heading → modal opens; enter title + subtitle.
2. Reload → renders as `<h2>Title</h2>` + subtitle, left-aligned.
3. Switch `heading: h1` → title renders larger (H1 styling).
4. Layout tab → master `alignment: center` → all three lines center; then set
   `title_align: left` → only the title goes left (others stay centered = inherit).
5. Set `overline: FAQs`, then exercise the three Layout-tab controls independently:
   toggle **Uppercase**; pick each **Marker** (Line / Dot / Lines / Bar) and flip **Marker
   Position** to Trailing; pick each **Container** (Pill / Outline pill / Underline).
   Confirm a **Pill with Marker = None** (no dot) works, and that Overline Color drives the
   marker, text and pill tint together. The overline still follows its alignment.
6. Set `display_size: display-3` on an `h2` → title is large but still `<h2>`.
7. Set `element_spacing: tight` / `relaxed` → vertical gaps shrink / grow.
8. Set `block_max_width: 720px` with `alignment: center` → the block is constrained
   and centered in its column.

## Files

- `config.php`, `options.php`, `static.php`, `views/view.php`
- `static/css/styles.css` (via static.php)
- `static/img/page_builder.png` — Layout Elements thumbnail

No JS, no item class — minimal leaf layout.
