---
type: shortcode
name: menu_toggle
provides: leaf
tab: Header/Footer Elements
---

# Menu Toggle

A hamburger button that opens an off-canvas drawer. Emits the exact markup the
theme's `navigation.js` binds to (`.menu-toggle` + `aria-controls` +
`.menu-toggle__bar`), so a builder-authored off-canvas header reuses the theme
drawer (open/close/focus-trap/Escape) with zero extra JS. Leaf shortcode
(`menu-toggle` → `[menu_toggle]`).

## Options schema (atts)

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `target` | text | `primary-navigation-drawer` | `aria-controls` target — the drawer id to open. Default = theme's built-in drawer. |
| `label` | text | `Menu` | Screen-reader `aria-label`. |
| `icon_style` | select | `bars` | `bars` (≡) or `dots` (⋮) → `.menu-toggle__bar` / `.menu-toggle__dot`. |
| + Advanced tab | group | — | `sc_get_advanced_tab()`. |

## Rendering

`<button class="menu-toggle menu-toggle--{style}" aria-controls="{target}"
aria-expanded="false" aria-label="{label}">` with three bar/dot spans.

## Pitfalls

- For the drawer to actually open, a drawer with id `{target}` must exist on the
  page. With the default target, the theme's `template-parts/header-builder.php`
  emits `#primary-navigation-drawer` for the off-canvas header type (Stage 3).

## Files
- `config.php`, `options.php`, `views/view.php`
