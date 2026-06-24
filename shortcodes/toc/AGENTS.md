---
type: shortcode
name: toc
since: shortcodes 1.6.65
provides: simple-content
---

# Table of Contents

`[toc]` renders an auto-generated, clickable outline of the page's headings.
It is a **simple Content Element** (like `special-heading`), NOT a section-like
container — it has no page-builder item class and registers purely through the
folder convention (config / options / static / views).

The distinctive part is that **the list is built client-side**: `view.php`
outputs only a configuration shell (`<nav class="sc-toc" data-…>` + an empty
`<ul>`), and `static/js/scripts.js` scans the resolved scope on the front end,
assigns slug `id`s to the matching headings (preserving any that already exist),
and builds the link list. This is deliberate — in the page builder, headings
come from many scattered shortcodes across separate sections, which a single
`the_content` PHP pass can't see reliably. JS scanning also gives smooth-scroll,
scrollspy and the collapse toggle for free.

## Registration

None beyond the simple-shortcode folder convention. No `class-fw-shortcode-toc.php`,
no `fw_section_like_types` filter, no page-builder item type. The shortcode tag
is `toc`.

## Options schema (atts)

| Att | Type | Default | Notes |
|-----|------|---------|-------|
| `title` | text | `Table of Contents` | Empty = no title. |
| `levels` | checkboxes | `{h2,h3}` | Heading tags to list. View flattens to `data-levels="2,3"`. |
| `hierarchical` | switch | `yes` | Nested vs flat list. |
| `min_headings` | text (int) | `2` | Hide entirely if fewer matching headings exist. |
| `numeration` | select | `decimal_nested` | `none` / `decimal_nested` / `decimal` / `roman` / `upper_alpha` / `bullets`. |
| `numeration_suffix` | select | `.` | `''` / `.` / `)`. Ignored for bullets / none. |
| `collapsible` | switch | `no` | Adds a Show/Hide toggle. |
| `collapsed_default` | switch | `no` | Start collapsed (needs `collapsible`). |
| `label_show` / `label_hide` | text | `show` / `hide` | Toggle labels. |
| `scope` | select | `content` | `content` (auto-detect) / `page` / `custom`. |
| `scope_selector` | text | `''` | CSS selector used only when `scope = custom`. |
| `skip_text` | textarea | `''` | One phrase per line; case-insensitive "contains" exclusion. |
| `smooth_scroll` | switch | `yes` | Animated jump. |
| `scroll_offset` | unit-input (px) | `0px` | Clearance for a sticky header. |
| `scrollspy` | switch | `yes` | Highlight the in-view heading's link. |
| `nofollow` | switch | `no` | `rel="nofollow"` on links. |
| `noindex` | switch | `no` | Wrap output in `<!--noindex-->`. |
| `width` | select | `full` | `full` / `auto` / `custom`. |
| `custom_width` | unit-input | `''` | Used only when `width = custom`. |
| `float` | select | `''` | `''` / `left` / `right`. |
| `sticky` | switch | `no` | `position: sticky`. |
| `sticky_offset` | unit-input | `20px` | Sticky `top` (→ `--sc-toc-sticky-top`). |
| `title_size` / `items_size` | font-size preset | `''` | `sc_font_size_field` classes. |
| `bg_color` / `border_color` / `title_color` / `link_color` / `link_hover_color` / `link_active_color` | compact color | `''` | Resolved to CSS vars on the wrapper. |
| `spacing` | spacing | — | Standard Margin & Padding. |
| + standard `tab_animation` and `tab_advanced` fields. |

## Rendering

`view.php` emits `<nav class="sc-toc …">` carrying every behavioural setting as
`data-*` attributes, plus an empty `<ul class="sc-toc__list">`. Color picks
(preset slug OR custom hex) are resolved to real color tokens and written as CSS
custom properties (`--sc-toc-link`, `--sc-toc-link-hover`, …) so the stylesheet
can drive `:hover` / `.is-active` states. The script replaces the empty `<ul>`
with the built tree, wires click-to-scroll (offset-aware), optional scrollspy,
and the collapse toggle, and honours a deep link (`#hash`) on first load.

## Pitfalls

- **No server-side list** — crawlers that don't run JS see an empty `<nav>`.
  That's the accepted trade-off for builder-wide heading discovery; the
  `nofollow` / `noindex` options exist for SEO control.
- **Color vars, not classes** — link hover/active need CSS, so all color fields
  resolve to a hex via `unysonplus_color_preset_slug_map()` (preset) or the
  sanitised custom value, emitted as inline CSS variables. A preset whose slug
  isn't in the live palette resolves to empty (falls back to the CSS default).
- **Scope auto-detect** is a best-effort selector list (`.entry-content`,
  `main`, `article`, …); use `scope = custom` + `scope_selector` when a theme's
  markup doesn't match.
- Headings inside `.sc-toc`, `header`, `footer`, or `[data-toc-skip]` are always
  excluded from the scan.

## Verification

1. Add a few Special Heading / Text Block H2s and H3s to a page, drop a Table of
   Contents above them, view the front end — the list builds and links jump.
2. Toggle Hierarchical / Numeration / Bullets — list structure + markers change.
3. Set a Scroll Offset and confirm clicked headings land below a sticky header.
4. Enable Highlight Active Heading and scroll — the current link highlights.
5. Set Minimum Headings above the count — the box disappears.

## Files

- `config.php` — page-builder tab/title/icon + editor `title_template`
- `options.php` — edit-modal fields (atts schema above)
- `static.php` — frontend CSS + JS enqueues (vanilla JS, no jQuery dep)
- `views/view.php` — config shell (`<nav data-…>` + empty list) + color→CSS-var resolution
- `static/css/styles.css` — box / list / states (CSS-variable driven)
- `static/js/scripts.js` — scan / id-assign / build / scroll / scrollspy / toggle
- `static/img/page_builder.svg` — 16×16 builder icon
