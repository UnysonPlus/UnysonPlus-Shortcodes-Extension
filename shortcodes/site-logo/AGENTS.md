---
type: shortcode
name: site_logo
provides: leaf
tab: Header/Footer Elements
---

# Site Logo

Renders the site logo/title. Self-contained (no theme functions) so it works in
any theme: a custom uploaded image, else the Customizer custom logo, else the
site title text. Leaf shortcode (`site-logo` → `[site_logo]`).

## Options schema (atts)

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `source` | select | `site_identity` | `site_identity` (Customizer logo/title) or `custom`. |
| `custom_image` | upload | — | Used only when `source=custom`. |
| `link_home` | switch | `yes` | Wrap in a home link. |
| `max_height` | unit-input | `''` | Optional max image height (width auto). |
| `alignment` | select | `''` | `start`/`center`/`end` → Bootstrap `text-*`. |
| + Advanced tab | group | — | `sc_get_advanced_tab()`. |

## Rendering

`<div class="sc-site-logo …">` containing an `<img class="sc-site-logo__img">`
(linked when `link_home`) or a `.sc-site-logo__title` text fallback.

## Files
- `config.php`, `options.php`, `views/view.php`
