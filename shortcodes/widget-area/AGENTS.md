---
type: shortcode
name: widget-area
since: original Unyson
provides: leaf-shortcode + wp-sidebar-bridge
---

# Widget Area

Renders a WordPress sidebar (widget area) inside the page builder. Lets
users place classic-widget-driven content blocks (e.g. recent-posts
widgets, custom HTML widgets, search forms, navigation menus) wherever
they want in a page-builder layout.

The available sidebars are populated dynamically from
`$wp_registered_sidebars` — anything registered by the theme or other
plugins via `register_sidebar()` appears in the dropdown.

## Registration

`class-fw-shortcode-widget-area.php` declares `FW_Shortcode_Widget_Area
extends FW_Shortcode`. The class exists for ONE reason: a static helper
`get_sidebars()` that maps `$wp_registered_sidebars` to a `{ id => name }`
choice array for the `sidebar` select in `options.php`. No `_init()`
overrides, no hooks — it's effectively a leaf shortcode with a utility
class.

## Options schema (atts)

Source of truth: `options.php`. Two tabs + Animations + Advanced.

### Tab: Content

**Not** wrapped in a group — single field.

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `sidebar` | `select` (choices from `FW_Shortcode_Widget_Area::get_sidebars()`) | — | The sidebar ID to render. Choices are dynamic — populated from all registered sidebars at the time the modal opens |

### Tab: Styling

Wrapped in `group_colors` + `group_spacings` (both flatten).

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `text_color` | `sc_color_field_compact` (text) | — | Wrapper text color |
| `bg_color` | `sc_color_field_compact` (bg) | — | Wrapper background |
| `font_size_preset` | `sc_font_size_field` | — | Named size from theme presets |
| `spacing` | `sc_spacing_field` | — | Wrapper margin/padding |

### Tabs: Animations + Advanced

Standard.

## Rendering

`views/view.php` outputs a wrapper containing `dynamic_sidebar($atts['sidebar'])` —
WordPress's standard widget-area rendering. The widgets inside are
rendered by their own widget classes; this shortcode just provides the
host element.

## Pitfalls

1. **`sidebar` choices are dynamic per environment** — the same exported
   `.json` template loaded on a different site might reference a sidebar
   ID that doesn't exist on the destination. The view falls back to
   rendering nothing for missing sidebars. AI-generated cross-site
   templates should avoid widget-area shortcodes UNLESS the destination
   site is known to register the same sidebar IDs.
2. **Widget content lives outside the page-builder JSON** — saving a
   page that contains a `[widget-area]` shortcode doesn't save the
   widgets themselves; they're stored in WordPress's widget store
   (`option_widget_*`) and edited via WP Admin → Widgets. Templates
   sharing widget-areas only share the SLOT, not the contents.
3. **Sidebar registration timing** — `get_sidebars()` is called when the
   options modal opens; sidebars registered late (after `widgets_init`)
   might not appear. If a sidebar is missing from the dropdown, check
   the theme's registration timing.
4. **Empty sidebars render nothing** — if the selected sidebar has no
   widgets, the shortcode outputs an empty wrapper. Generators producing
   widget-area shortcodes should ensure the target sidebar has content
   on the destination site, or fall back to a `[text-block]` with the
   intended content inline.

## Verification

1. Drag Widget Area from Content Elements → modal opens with a sidebar
   dropdown.
2. Select a sidebar that has widgets → save → reload → widgets render
   inside the page-builder layout.
3. Select an empty sidebar → save → empty wrapper.
4. WP Admin → Widgets → add a widget to the sidebar → reload the page →
   new widget appears in the widget-area block.

## Files

- `class-fw-shortcode-widget-area.php` — class with `get_sidebars()`
  static helper
- `config.php`, `options.php`, `views/view.php`

**No `static.php`** — widgets self-enqueue their own assets when
rendered via `dynamic_sidebar()`.

Leaf shortcode with a thin utility class — the class exists purely for
the sidebar-choice helper, not for hooks.
