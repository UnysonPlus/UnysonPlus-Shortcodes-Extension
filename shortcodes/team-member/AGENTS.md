---
type: shortcode
name: team-member
since: original Unyson
provides: leaf-shortcode
---

# Team Member

A single team-member card: photo + name + job title + short description.
Use multiple instances inside a row/columns to build a team grid.

## Registration

No custom class file — leaf shortcode auto-instantiated. No item class.

`config.php` has no `title_template` — uses default summary.

## Options schema (atts)

Source of truth: `options.php`. Two tabs + Animations + Advanced.

### Tab: Content

**Not** wrapped in a group — fields are flat.

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `image` | `upload` | — | Team member's photo (WP attachment) |
| `name` | `text` | `''` | Person's name |
| `job` | `text` | `''` | Job title / role |
| `desc` | `textarea` | `''` | Short description (plain text or HTML — view renders raw) |

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

`views/view.php` outputs a card with an `<img>`, name (typically as
`<h*>`), job title, and description. Layout is typically vertical
(image on top, content below). For grid layouts, wrap multiple
team-member shortcodes in columns inside a row.

## Pitfalls

1. **No layout options** — name/job/desc render in fixed order. Use
   `[icon-box]` with `style: top-title` if you need more layout control,
   or wrap in custom CSS via `css_class`.
2. **No social links** — this is a bare-bones card. For social icons,
   embed icons in `desc` (renders raw HTML) or use a separate composite
   layout with icon shortcodes adjacent.
3. **`image` is a WP upload object** — `{ attachment_id, url }` shape,
   not a URL string.

## Verification

1. Drag Team Member → modal opens.
2. Upload an image, set name + job + desc → reload → card renders.
3. Wrap 3 team-members in a 3-column row → renders as a team grid.

## Files

- `config.php`, `options.php`, `static.php`, `views/view.php`
- `static/css/styles.css` (via static.php) — card layout
- `static/img/page_builder.png` — Layout Elements thumbnail

Standard leaf layout.
