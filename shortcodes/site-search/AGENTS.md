---
type: shortcode
name: site_search
provides: leaf
tab: Header/Footer Elements
---

# Search

A site search form. `inline-form` is always visible; `icon-toggle` shows a
magnifier button that reveals the form on click (see `static/js/site-search.js`).
Leaf shortcode (`site-search` → `[site_search]`).

## Options schema (atts)

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `style` | select | `inline-form` | `inline-form` or `icon-toggle`. |
| `placeholder` | text | `Search …` | Input placeholder. |
| + Advanced tab | group | — | `sc_get_advanced_tab()`. |

## Rendering

`<div class="sc-site-search sc-site-search--{style}">` wrapping a
`form.sc-search-form` (method=get, action=home). `icon-toggle` adds a
`.sc-search-toggle` button + hidden `.sc-search-panel`; the JS toggles `hidden` +
`aria-expanded`, closes on outside-click / Escape, focuses the field on open.

## Files
- `config.php`, `options.php`, `views/view.php`, `static.php`, `static/js/site-search.js`
