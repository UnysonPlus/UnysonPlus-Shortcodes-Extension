---
type: shortcode
name: nav_menu
provides: leaf
tab: Header/Footer Elements
---

# Navigation Menu

Renders a WordPress menu (by theme location or by specific menu) inside a
`<nav>`. Built for the Header/Footer builder but works in any page content.
Reuses the theme's `.primary-menu` + `.menu-item-has-children` class contract so
the theme's `navigation.js` (desktop dropdowns, accordion toggles, off-canvas
drawer) drives builder-authored menus with no extra JS.

This is a **leaf** shortcode (no class / no page-builder item type) — auto-
discovered by folder name (`nav-menu` → `[nav_menu]`).

## Options schema (atts)

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `menu_source` | multi-picker | `location` | `type` = `location` → `menu_location` (select); `type` = `menu` → `menu_id` (select). |
| `orientation` | select | `horizontal` | `horizontal` / `vertical` → class `primary-menu--{orientation}`. |
| `submenu_style` | select | `dropdown` | `dropdown` / `mega` / `accordion` → class `submenu-{style}` (+ `has-mega` for mega). |
| `depth` | select | `0` | wp_nav_menu depth (0 = all). |
| `alignment` | select | `''` | `start`/`center`/`end`/`justified` → `nav-align-{x}`. |
| + Advanced tab | group | — | `sc_get_advanced_tab()` (css id/class/custom css/attrs/responsive). |

## Rendering

`wp_nav_menu()` with `container=false`, `menu_class="primary-menu primary-menu--{orientation} submenu-{style} …"`,
wrapped in `<nav class="sc-nav …" aria-label="Menu">`. Returns nothing when the
chosen location/menu is empty or missing (`fallback_cb=false`).

## Pitfalls

- The builder can't restrict its palette per CPT; the header/footer builder shows
  all elements. The `header-footer-builder` extension trims that via the
  `fw_ext_shortcodes_disable_shortcodes` filter on the preset edit screens.
- `mega`/`accordion` add class hooks only; their full CSS lands with the header
  types (Stage 3 / later).

## Files
- `config.php` — page-builder tab/title (tab = Header/Footer Elements)
- `options.php` — atts schema
- `views/view.php` — frontend HTML
