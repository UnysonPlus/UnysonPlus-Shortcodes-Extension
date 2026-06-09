---
type: shortcode
name: social_icons
provides: leaf
tab: Header/Footer Elements
---

# Social Icons

A row of social profile links. `theme_settings` delegates to the theme's own
`unysonplus_render_social_icons()` (so it matches the site); `manual` renders the
list defined in the shortcode. Leaf shortcode (`social-icons` → `[social_icons]`).

## Options schema (atts)

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `source` | select | `theme_settings` | `theme_settings` (delegate to theme) or `manual`. |
| `profiles` | addable-box | `[]` | Each: `icon` (icon-v2), `link` (text), `label` (text, SR). Used when `source=manual`. |
| `size` | select | `md` | `sm`/`md`/`lg` → `sc-social--{size}`. |
| + Advanced tab | group | — | `sc_get_advanced_tab()`. |

## Rendering

theme_settings → calls `unysonplus_render_social_icons()` if it exists (else
nothing). manual → `<ul class="sc-social sc-social--{size}">` of `<a>` with an
`<i class="{icon-class}">` + screen-reader label; external `target=_blank
rel="noopener noreferrer"`. Enqueues icon-v2 pack CSS when icons are present.

## Files
- `config.php`, `options.php`, `views/view.php`
