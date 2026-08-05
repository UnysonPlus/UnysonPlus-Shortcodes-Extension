---
type: shortcode
name: tabs
since: original Unyson (overhauled in Unyson+ — design registry, popover, a11y, mobile accordion)
provides: leaf-shortcode
---

# Tabs

Tabbed content with switchable panels. Self-contained: WAI-ARIA roles + vanilla JS
switching (no Bootstrap / jQuery). Design-capable via the shared design registry.

## Registration

No custom class file (leaf shortcode). `config.php` declares a `title_template`
previewing each tab's title + content on the canvas.

## Designs (registry)

`views/parts/registry.php` (SKIN shape: `key => { label, thumb }`). Seven built-in:
`underline` (default), `pills`, `segmented`, `boxed` (folder), `minimal`, `buttons`,
`popover` (floating panel). (An old `tabs`/"Bordered" style was dropped as a near-duplicate of `boxed`.) The style is chosen via a Design
**image-picker** (`fw_sc_design_picker_choices('tabs')`), resolved in `view.php` and
emitted as `tabs--design-<key>` + `design-<key>` on the wrapper. Because a
`views/parts/registry.php` exists, tabs auto-registers in the design-pack manager and
is skin-pack-extensible (add a design = one registry entry + a thumb `static/img/design/<key>.svg`
[+ optional `static/css/design/<key>.css`, auto-gated by `static.php`]). Legacy
`tab_style` values (tabs/pills/underline/segmented) match these keys → old instances render unchanged.

## Options schema (atts)

Source of truth: `options.php`.

### Content tab

| Att | Type | Default | Notes |
|-----|------|---------|-------|
| `tabs` | `addable-popup` | — | Per tab: `tab_title` (text), `tab_content` (`wp-editor`), `tab_image` (upload — media layout), `badge` (text pill), `icon` (icon — before the title), `disabled` (switch), `is_active` (switch — open on load; first wins). |
| `design` | `image-picker` | `underline` | The visual tab style (registry). Legacy: `tab_style`. |
| `tab_width` | select | `auto` | auto / fill (proportional) / equal (nav-justified). Legacy `justified`=yes → equal. |
| `deep_link` | switch | `no` | Open a tab from the URL `#hash` + update the hash on switch (stable with a CSS ID). |
| `remember` | switch | `no` | Re-open the last-viewed tab (localStorage, keyed by CSS ID). |
| `alignment` | select | `start` | start/center/end (horizontal). |
| `orientation` | select | `horizontal` | horizontal/vertical (sets `aria-orientation`). |
| `layout` | select | `content` | `content` panels, or `media` (tab list + switching image + caption). |
| `media_side` | select | `right` | Image side in the media layout. |
| `activate_on` | select | `click` | click/hover pointer activation. |
| `activation` | select | `automatic` | WAI-ARIA keyboard: `automatic` (panel on focus) / `manual` (Enter/Space). |
| `mobile` | select | `none` | `none` (wrap) / `accordion` (collapse) / `scroll` (horizontal). |
| `autoplay` + `autoplay_interval` | switch + slider | `no`, 5s | Auto-rotate (pauses on hover/focus, respects reduced-motion). |
| `fade` | switch | `no` | Cross-fade between panels. |

### Styling / Animations / Advanced

`group_colors` (text/bg/font-size + `tab_title_color`, `tab_content_color`) + `group_spacings`; standard Animations + Advanced.

## Rendering (`views/view.php`)

Shared markup builders `$render_nav()` / `$render_panes()` (de-duplicated) composed per
layout: horizontal (`nav` + `tab-content`), vertical (`.fw-col-3` nav + `.fw-col-9`
content), media (`.tabs-media__*` list + switching figure). Classes: wrapper
`tabs-container tabs--design-<key> design-<key>`; nav `nav nav-<key>` [+ alignment/justified];
buttons `.nav-link` with `role=tab`, `aria-selected`, `aria-controls`, and a
**server-rendered roving tabindex** (active `0`, others `-1`); panes `.tab-pane[.fade][.show active]`
`role=tabpanel tabindex=0 aria-labelledby`. Behaviour data-attrs on the wrapper:
`data-fw-activate` (hover), `data-fw-activation` (manual), `data-fw-autoplay` (ms),
`data-fw-mobile` (accordion/scroll).

## JS (`static/js/scripts.js`)

Document-delegated (works for injected tabs). Click / hover activation; orientation-aware
Arrow keys (skip disabled) + Home/End; manual vs automatic keyboard activation; auto-rotate;
mobile accordion (nests each pane inside its `<li>` at ≤767.98px, restores on resize). The
`.min.*` files were removed so `fw_min_uri` falls back to the full assets.

## Accessibility

Full WAI-ARIA Tabs pattern: tablist/tab/tabpanel, aria-selected/controls/labelledby,
aria-orientation on vertical, server + JS roving tabindex, arrow/Home/End keys, manual/automatic activation.

## Behaviour extras

- **Deep-link** (`deep_link`) — reads `#hash` on load to open a pane and `history.replaceState`s the
  hash on activation. Ids are STABLE when the element has a CSS ID (so links survive reloads); without
  one, ids are per-render (session-only). Autoplay does not rewrite the hash.
- **Remember** (`remember`) — stores the active tab index in `localStorage` (`fwTabs:<css_id>`), restored
  on load; a matching `#hash` wins over the remembered tab.
- **Sliding indicator** — the Underline design gets a JS-positioned `.nav-indicator` bar (repositioned on
  activate + resize); the nav gets `.has-indicator` so the static per-tab border is the no-JS fallback.
- **Tab Width** — `auto` / `fill` (`nav-fill`, proportional) / `equal` (`nav-justified`).
