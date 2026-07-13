---
type: shortcode
name: call-to-action
since: original Unyson
provides: leaf-shortcode
---

# Call To Action

A title + rich-text message + button combo. Lighter than `[posts]` or
`[icon-box]` — just a focused promotional block.

## Registration

No custom class file — leaf shortcode auto-instantiated. No item class.

`config.php` declares a `title_template` that previews the title, a
trimmed message, and the button label on the canvas.

## Options schema (atts)

Source of truth: `options.php`. Content / Layout / Styling tabs + Animations + Advanced.

### Tab: Content

Wrapped in `group_content` (flattens).

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `title` | `text` | — | Headline above the message (can be blank) |
| `message` | `wp-editor` | — | Body content (TinyMCE, shortcodes enabled, `wpautop: true`, 425px editor) |
| `button_label` | `text` | `Click` | Button text |
| `button_link` | `text` | `#` | Button href |
| `button_target` | `switch` (`_blank` / `_self`) | `_self` | New window or same |

### Tab: Layout

Wrapped in `group_layout` (flattens).

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `column_split` | **`column-split`** (Content \| Button) | `3/4` | Saved as an `"n/d"` **fraction string** = the **content's** share of the row (button takes the rest). `denominator: 12`, but the `fractions` list mixes twelfths **and** fifths (`1/5`, `2/5`, `3/5`, `4/5`), so the divider snaps to both; `show_fraction: true`. Drives each side's `flex-grow`. `panes` label the two sides (Content / Button) with dashicons. Second adopter of the reusable `column-split` option type (after `image_content`) |

### Tab: Styling

Wrapped in `group_colors` + `group_spacings` (both flatten).

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `bg_color` | `sc_color_field_compact` (bg) | — | Wrapper background |
| `font_size_preset` | `sc_font_size_field` | — | Named size from theme presets |
| `title_color` | `sc_color_field_compact` | — | Title color |
| `message_color` | `sc_color_field_compact` | — | Message color. **Key is `message_color` for back-compat** with saved instances; the editor label is "Content Color" |
| `spacing` | `spacing` (label "Margin & Padding") | — | Wrapper margin/padding |

### Tabs: Animations + Advanced

Standard.

## Rendering

`views/view.php` outputs a flex wrapper (`.fw-call-to-action`) with `.fw-action-content`
(an `<h2>` title via `esc_html`, then a `<p class="fw-action-message">` message via
`wp_kses_post`) on the left and `.fw-action-btn` (an `<a class="btn btn-1"><span>…</span></a>`)
on the right. Each side's **`flex-grow` is set inline from `column_split`**: the view parses the
`"n/d"` fraction (content = `n`, button = `d - n`), so **any** denominator (twelfths or fifths)
works; a legacy bare-int span-out-of-12 is still tolerated (content = `N`, button = `12-N`).
The `.fw-action-btn` is only emitted when `button_label` is non-empty. Below `575.98px` the two
**stack** (the CSS resets `flex` to `0 0 auto`). The layout CSS lives in `static/css/styles.css`
(+ its **`.min`** sibling — served via `fw_min_uri()`, so edit both); `static.php` also enqueues
the `button` shortcode's CSS so the `.btn` styling is present.

## Pitfalls

1. **`message` is HTML, not plain text** — generators producing this att
   should emit valid HTML (`<p>`, `<a>`, etc.). The view sanitizes it with
   `wp_kses_post` (post-level HTML allowed) inside a single `<p>` wrapper.
2. **`message_color` key is legacy** — the user-facing label is "Content
   Color" but the att key stayed `message_color` for back-compat. Don't
   rename it.
3. **No button-style control** — the button uses a fixed theme style
   (typically primary). For more control, drag a separate `[button]`
   shortcode adjacent to a `[special-heading]` instead.

## Verification

1. Drag Call To Action → renders the placeholder text + a "Click" button.
2. Edit → set title, write message, change button label → reload.
3. Set `button_target: _blank` → click opens in new tab.
4. Set styling colors → text + bg colors apply.

## Files

- `config.php`, `options.php`, `static.php`, `views/view.php`
- `static/css/styles.css` (+ `.min`, via static.php — which also enqueues the `button` CSS)
- `static/img/page_builder.svg` — Layout Elements thumbnail

No JS, no item class — minimal leaf layout.
