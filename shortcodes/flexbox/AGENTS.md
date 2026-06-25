# Flexbox — the Theme Builder's "Structure" primitive

The **Flexbox** is a self-contained, nestable semantic flex container (renders a
chosen tag: `div`/`section`/`header`/`main`/`article`/`aside`/`footer`/`nav`). It is
the layout primitive used to build **Header / Body / Footer** parts in the Theme
Builder (the seven per-tag palette tiles in the **Structure** tab).

## Why it lives HERE (core `shortcodes`), not in the `theme-builder` extension

It is conceptually tied to the Theme Builder, but it is **deliberately kept in the
core shortcodes extension**. The reasons (settled after a design discussion):

1. **It is a page-builder element, structurally identical to every other one**
   (`config.php` / `options.php` / `views/view.php` / `static.php` /
   `includes/page-builder-flexbox-item/`). The shortcodes loader auto-discovers
   `shortcodes/shortcodes/*`; an element elsewhere needs bespoke registration.
2. **It depends on the shortcodes infrastructure**, not the reverse:
   `sc_build_wrapper_attr()`, the gap scale, border presets, alignment helpers, the
   section-item canvas CSS it reuses, and the page-builder item-type system.
3. **Graceful degradation (decisive).** `theme-builder` is **optional / download-only**;
   `shortcodes` is a **core, always-present** dependency (`theme-builder` *requires*
   it). A rendering primitive must survive the optional layer being absent — if the
   flexbox lived in `theme-builder`, deactivating it would delete the shortcode and
   any page/part using one would render as raw `[flexbox]…` text.
4. **The isolation is palette-only and reversible** — see below. The shortcode still
   renders everywhere on the front end; it is merely hidden from non-TB palettes.
5. **Consistency** with the **Dynamic Content** elements (`post_title`,
   `post_content`, …), which are isolated to the Theme Builder by the *same*
   mechanism yet also live in `shortcodes/shortcodes/`. The Theme Builder *isolates*
   these elements; it does not *own* them.

## Where the Theme-Builder isolation lives (the other half)

The palette isolation is **admin-only** and filterable — it does NOT touch the front
end (so existing pages/templates with a flexbox always render):

- `UnysonPlus-Theme-Builder-Extension/hooks.php` →
  `_filter_fw_theme_builder_structure_elements_scope()` (filter
  `fw_ext_shortcodes_disable_shortcodes`; override list via
  `fw_theme_builder_structure_elements`).

To re-expose the flexbox on normal pages/posts, drop `'flexbox'` from that filter.

## Folder map

- `config.php` / `options.php` / `views/view.php` / `static.php` — the shortcode.
- `includes/page-builder-flexbox-item/` — the page-builder item type (canvas CSS/JS,
  per-tag thumbnails + their SVG icons in `static/img/tiles/`).
- `static/css/styles.css` — front-end layout helper (row children stack vs. size).
