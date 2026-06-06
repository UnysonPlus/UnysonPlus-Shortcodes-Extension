---
type: shortcode
name: text-expander
since: Unyson+ fork
provides: leaf-shortcode
---

# Text Expander

An inline "read more / read less" toggle. Hide part of a sentence or
paragraph behind a Show More button. Two button-position pickers
(show + hide), optional paragraph-stitching merge mode, ellipsis suffix,
word/character count token in button labels, click-anywhere expansion,
and a native `<details>` mode for SEO/a11y.

## Registration

No custom class file — leaf shortcode auto-instantiated. No item class.

Shortcode tag: `text_expander` (hyphen in directory name converts to
underscore in tag).

`config.php` declares a `title_template` that previews visible content +
the hidden block on the canvas.

## Options schema (atts)

Source of truth: `options.php`. Three tabs + Animations + Advanced.

### Tab: Content

Two groups: `group_content` (the two editor fields) + `group_button`
(the two button labels). Both flatten.

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `visible_content` | `wp-editor` | — | Always-visible content. With Merge mode on, only the LAST paragraph stitches to hidden |
| `hidden_content` | `wp-editor` | — | Revealed on Show More click. With Merge mode on, only the FIRST paragraph stitches to visible |
| `btn_show` | `text` | `Show More` | Label when collapsed. Use `{count}` token to insert word/char count |
| `btn_hide` | `text` | `Show Less` | Label when expanded. Same `{count}` token |

### Tab: Layout

Wrapped in `group_layout` (flattens).

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `show_btn_position` | `image-picker` (`inline` / `block_left` / `block_center` / `block_right`) | `inline` | Show-More button placement |
| `hide_btn_position` | `image-picker` (`inherit` / `inline` / `block_left` / `block_center` / `block_right`) | `inherit` | Show-Less placement. `inherit` = match `show_btn_position` but place after expanded text |
| `merge_boundary` | `switch` (`yes` / `no`) | `no` | Stitch the last visible paragraph + first hidden paragraph into one `<p>` |
| `show_ellipsis` | `switch` (`yes` / `no`) | `no` | Append `…` via CSS `::after` while collapsed (a11y-safe — disappears on expand) |
| `count_mode` | `select` (`none` / `words` / `chars`) | `none` | Append word/char count to button labels via the `{count}` token |
| `click_anywhere` | `switch` (`yes` / `no`) | `no` | Make the entire visible region clickable — links/buttons inside still fire normally |
| `native_details` | `switch` (`yes` / `no`) | `no` | Render as `<details>`/`<summary>` instead of the custom widget. **Overrides button position, toggle icon, animation, click-anywhere, and count_mode** |

### Tab: Styling

Wrapped in `group_colors` + `group_options` (both flatten).

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `btn_color` | `color-picker` (hex / rgba) | — | Free-form color for both buttons. **Overridden by `btn_show_color` / `btn_hide_color` presets** when set |
| `visible_color` | `sc_color_field_compact` | — | Color preset for visible content paragraphs |
| `hidden_color` | `sc_color_field_compact` | — | Color preset for hidden content paragraphs |
| `btn_show_color` | `sc_color_field_compact` | — | Preset for Show More button. Overrides `btn_color` |
| `btn_hide_color` | `sc_color_field_compact` | — | Preset for Show Less button. Overrides `btn_color` |
| `toggle_icon` | `select` (`none` / `chevron` / `plus-minus`) | `none` | Icon next to button label. Chevron rotates 90° on expand. **Bypassed in native `<details>` mode** |
| `initially_open` | `switch` (`yes` / `no`) | `no` | Render expanded on first paint (server-side). Maps to `[open]` attribute in native mode |

### Tabs: Animations + Advanced

Standard.

## Rendering

`views/view.php` outputs either the custom widget (a wrapper with two
content regions + toggle button) or native `<details>` element per
`native_details`. `static/js/scripts.js` handles toggle, word/char
count, click-anywhere, and ellipsis hide/show in custom mode.

## Pitfalls

1. **Many fields are mutually-exclusive with `native_details: yes`** —
   button position, toggle icon, click-anywhere, count_mode are all
   bypassed. Generators producing native-details mode can omit these
   without consequence.
2. **`merge_boundary` only stitches paragraphs at the boundary** — only
   the LAST visible para + FIRST hidden para combine. Other paragraphs
   stay separate. AI generators producing single-paragraph visible/
   hidden content won't see the difference; multi-paragraph layouts
   need to plan around it.
3. **`{count}` token requires `count_mode` to be set** — without
   `count_mode: words` or `chars`, the token renders as literal text.
4. **`btn_color` is free-form, NOT preset** — accepts hex / rgba only;
   malformed values silently drop (no inline style emitted).
5. **Preset color fields override the free-form `btn_color`** — set
   either, not both. The presets win when both exist.

## Verification

1. Drag Text Expander → modal opens with two editor fields.
2. Fill visible + hidden content. Save → renders collapsed with "Show
   More" button.
3. Click Show More → reveals hidden content.
4. Set `merge_boundary: yes` → last visible para + first hidden para
   stitch into one paragraph on expand.
5. Set `count_mode: words` + `btn_show: "Read more ({count} words)"` →
   button shows actual word count.
6. Set `native_details: yes` → renders as `<details>` HTML element with
   browser's native disclosure triangle.

## Files

- `config.php`, `options.php`, `static.php`, `views/view.php`
- `static/js/scripts.js` — toggle, count, click-anywhere, ellipsis
- `static/css/styles.css` (via static.php)
- `static/img/btn-*.svg` — button-position picker thumbnails
- `static/img/page_builder.png` — Layout Elements thumbnail

Standard leaf layout.
