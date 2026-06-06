---
type: shortcode
name: tabs
since: original Unyson (extended in Unyson+ — Bootstrap 5.2+ underline style, vertical orientation, justified)
provides: leaf-shortcode
---

# Tabs

A horizontal or vertical tabbed-content widget. Each tab has a title and
a textarea body. Three Bootstrap visual variants (`tabs`, `pills`,
`underline`). Justified-width, alignment, vertical orientation, and fade
animation between tabs.

## Registration

No custom class file — leaf shortcode auto-instantiated. No item class.

`config.php` declares a `title_template` that previews every tab's title
+ content snippet on the canvas — similar to the accordion shortcode.

## Options schema (atts)

Source of truth: `options.php`. Two tabs + Animations + Advanced. **Note**:
Content-tab options are NOT wrapped in a group (they're flat at the
content-tab level), unlike most shortcodes which group their content
fields.

### Tab: Content

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `tabs` | `addable-popup` (sortable list) | — | Tab entries. Each: `{ tab_title, tab_content, is_active }` |
| `tabs[].tab_title` | `text` | — | Tab nav-button label |
| `tabs[].tab_content` | `textarea` | — | Tab panel body (plain text or HTML) |
| `tabs[].is_active` | `switch` (`yes` / `no`) | `no` | Which tab is open on first load. **If multiple are set to `yes`, the first one wins** |
| `tab_style` | `select` (`tabs` / `pills` / `underline`) | `tabs` | Bootstrap nav style |
| `justified` | `switch` (`yes` / `no`) | `no` | Stretch tab nav to fill container width |
| `alignment` | `select` (`start` / `center` / `end`) | `start` | Horizontal alignment of the tab nav |
| `orientation` | `select` (`horizontal` / `vertical`) | `horizontal` | Tabs above content (horizontal) or beside (vertical) |
| `fade` | `switch` (`yes` / `no`) | `no` | Fade transition between tab panels |

### Tab: Styling

Wrapped in `group_colors` + `group_spacings` (both flatten).

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `text_color` | `sc_color_field_compact` (text) | — | Wrapper text color |
| `bg_color` | `sc_color_field_compact` (bg) | — | Wrapper background |
| `font_size_preset` | `sc_font_size_field` | — | Named size from theme presets |
| `tab_title_color` | `sc_color_field_compact` | — | Tab nav-button text color |
| `tab_content_color` | `sc_color_field_compact` | — | Tab panel body text color |
| `spacing` | `sc_spacing_field` | — | Wrapper margin/padding |

### Tabs: Animations + Advanced

Standard.

## Rendering

`views/view.php` outputs a Bootstrap nav + tab-content structure:

```html
<ul class="nav nav-{tab_style} {justified ? 'nav-justified' : ''} justify-content-{alignment}">
  <li class="nav-item"><a class="nav-link {is_active ? 'active' : ''}" data-bs-toggle="tab" href="#tab-{i}">{tab_title}</a></li>
  ...
</ul>
<div class="tab-content">
  <div class="tab-pane {fade ? 'fade' : ''} {is_active ? 'show active' : ''}" id="tab-{i}">{tab_content}</div>
  ...
</div>
```

When `orientation: vertical`, the nav becomes a sidebar (Bootstrap
`flex-column` + `nav-pills`) beside the content.

`static/js/scripts.js` initializes Bootstrap's tab JS for any tabs that
need it.

## Pitfalls

1. **`tab_content` is `textarea`, not `wp-editor`** — unlike accordion's
   rich-text content, tabs use a plain textarea. HTML markup IS rendered
   (the view doesn't escape), but there's no TinyMCE editor to help author
   it. Generators can produce HTML strings freely.
2. **No `group` wrapping on content fields** — most shortcodes wrap their
   tab_content options in a `group_content` group; tabs does NOT. Atts
   like `tab_style`, `justified`, `alignment` are flat at the content-tab
   level (which is fine — `tab` is a layout type, not a saved value).
3. **`is_active` is per-item, not a global default** — set ONE tab item's
   `is_active: 'yes'` to control the initial open state. Setting none
   means no tab is initially active (Bootstrap then opens the first).
4. **`orientation: vertical` requires a wider wrapper** — vertical tabs
   take horizontal space for the nav sidebar, leaving less for content.
   Pair with a column that's wide enough.
5. **Bootstrap 5 dependency** — the `underline` style requires Bootstrap
   5.2+. Older Bootstrap themes won't render it correctly (falls back to
   the default tab style visually).

## Verification

1. Drag Tabs → modal opens; add 3 tab entries.
2. Reload → renders as horizontal tabs, no tab initially active.
3. Set one tab's `is_active: yes` → that tab opens by default.
4. Switch `tab_style: pills` → tabs render as Bootstrap pills.
5. Switch `tab_style: underline` (on Bootstrap 5.2+) → underline style.
6. Switch `orientation: vertical` → tabs sidebar beside content.
7. Set `fade: yes` → switching tabs fades the content panel.

## Files

- `config.php`, `options.php`, `static.php`, `views/view.php`
- `static/js/scripts.js` — Bootstrap tab JS init
- `static/css/styles.css` (via static.php) — extends Bootstrap defaults
- `static/img/page_builder.png` — Layout Elements thumbnail

Standard leaf layout.
