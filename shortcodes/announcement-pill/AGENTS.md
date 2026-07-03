# Announcement Pill shortcode (`announcement_pill`)

A compact rounded **pill / badge**: an optional leading marker (dot / pulse dot / icon), an optional
**sub-tag** ("New"), a **message**, an optional **trailing icon**, and an optional **link**. Built for
"what's new" hero chips, status badges and eyebrow labels. Clean DOM — every utility class lives in
`static/css/styles.css`, keyed off the wrapper's `ap-*` modifier classes.

## Files
- `config.php` — page-builder card (title/desc/tab) + canvas `title_template` (tag chip + message).
- `options.php` — six tabs: **Content**, **Design**, **Styling**, **Link & SEO**, **Animations**, **Advanced**.
  The Design `style` is an `image-picker` with inline SVG thumbnails (no asset files).
- `views/view.php` — `sc_announce_render()`. Resolves colours → CSS vars, builds the semantic markup,
  enqueues only the icon-v2 pack(s) the chosen icons use.
- `static/css/styles.css` — `.fw-announce` (outer, alignment) + `.ap-pill` (the pill) + all variants.
- `static/js/announcement-pill.js` — dismissible behaviour (localStorage, per `data-ap-dismiss` key).
- `static.php` — enqueues the CSS + JS (versioned by the shortcodes extension manifest).

## Atts
| key | type | notes |
|---|---|---|
| `tag_text` | text | the leading sub-tag ("New"); empty = omit |
| `message` | text | the main pill text |
| `link` | text | optional URL — makes the pill clickable |
| `leading` | select | `none` / `dot` / `pulse` (live) / `icon` |
| `leading_icon` / `trailing_icon` | icon-v2 | used per `leading`, and after the message |
| `style` | image-picker | `soft` `outline` `solid` `subtle` `ghost` `gradient` `glass` |
| `shape` | select | `pill` / `rounded` / `square` |
| `size` | select | `sm` / `md` / `lg` |
| `align` | select | `start` / `center` / `end` |
| `tag_style` | select | `filled` / `soft` / `outline` / `none` |
| `hover` | select | `none` / `lift` / `glow` / `slide` (trailing icon) |
| `pill_color` / `text_color` / `tag_color` | color (compact) | → `--ap-color` / `--ap-text` / `--ap-tag-color` |
| `gradient_from` / `gradient_to` | color (compact) | gradient style → `--ap-grad-from` / `--ap-grad-to` |
| `spacing` | spacing | margin positions the pill; padding usually left to Size |
| `link_target` | select | `auto` (new tab for external) / `_self` / `_blank` |
| `rel_nofollow` / `rel_sponsored` / `rel_ugc` | switch | appended to `rel` (external links always get `noopener noreferrer`) |
| `aria_label` | text | accessible name when the visible text is terse |
| `title_attr` | text | native tooltip |
| `dismissible` + `dismiss_id` | switch + text | × button; remembered per-browser (needs a key) |
| `schema_enable` + `schema_name` + `schema_date` | switch + text | optional `SpecialAnnouncement` JSON-LD (default off) |

## SEO / accessibility
- Renders **`<a href>`** when linked (crawlable, real anchor text — WCAG 2.4.4), **`<span>`** otherwise —
  never `role="alert"`.
- External-link auto-detect (host vs `home_url()`) → `target="_blank"` + `noopener noreferrer`; plus the
  optional `nofollow` / `sponsored` / `ugc` toggles.
- Decorative parts (dot, leading/trailing icons) carry `aria-hidden="true"`.
- Dismiss × is a real `<button>` sibling (never nested inside the `<a>`), so the markup stays valid.

## Site Converter
Hero pills (`inline-flex … rounded-full` with an inner "New" tag) are mapped here by the converter's
`announcement_pill` recognizer (priority 76, above the verbatim `badge`) + the `register_builder` →
`n_announcement_pill()` pair. If extraction fails it falls back to the verbatim `badge` recognizer.
