# Tag List shortcode (`[tag_list]`)

Renders a list of short items as **pills / chips** or an **inline separated list**. Replaces the
anti-pattern of hand-coding `<span class="…pill">` inside a Text Block — the markup, styling and
links all live in one editable element with clean, semantic output.

## Input
- **`items`** (`textarea`) — **one item per line**. A bare line is a plain tag; `Label | URL`
  turns that tag into a link (`<a>`). Blank lines are ignored. Parsed in `views/view.php`
  (`explode('|', $line, 2)`).

## Options (atts)
| key | type | values | default | what it does |
|---|---|---|---|---|
| `items` | textarea | text, one per line | 5 sample lines | the tags (see above) |
| `design` | image-picker | `soft` / `outline` / `solid` / `subtle` / `line` | `soft` | the look; `line` = no pill, dot-separated text |
| `shape` | select | `pill` / `rounded` / `square` | `pill` | corner radius (ignored by `line`) |
| `size` | select | `sm` / `md` / `lg` | `md` | font-size + padding |
| `align` | select | `start` / `center` / `end` | `start` | `justify-content` of the row |
| `gap` | select | `sm` / `md` / `lg` | `sm` | space between tags |
| `marker` | select | `none` / `dot` | `none` | a leading dot before each tag (not for `line`) |
| `hover` | switch | `yes` / `no` | `no` | lift + accent on hover (mainly linked tags) |
| `tag_color` | compact color | preset slug **or** custom hex | empty | the tag colour (see below) |

Plus the standard **Styling** (spacing), **Animations** and **Advanced** (CSS class/id, custom CSS)
tabs.

## Colour model
`tag_color` is resolved in the view to a single CSS custom property **`--tl-color`** on the wrapper:
- a **Color Preset** → `var(--color-<slug>)` (so it tracks the brand; presets emit `--color-*`),
- a **custom hex/rgb** → the literal value,
- unset → CSS default `#5f6266` (neutral grey).

`styles.css` derives every design from `--tl-color` with `color-mix()` (soft = 13% tint, outline =
38% border, solid = full fill + white text, subtle = always neutral grey, line = plain coloured
text). One variable drives fill, border and text, so a single colour pick restyles the whole list.

## Output
```html
<div class="fw-taglist tl-soft tl-shape-pill tl-md tl-align-start tl-gap-sm" style="--tl-color:var(--color-green);">
  <span class="fw-tag"><span class="fw-tag__label">Layout</span></span>
  <a class="fw-tag" href="/path/"><span class="fw-tag__label">Media</span></a>
</div>
```
No inline-styled prose, no per-item classes — just `.fw-tag` (or `a.fw-tag`) with the design on the
wrapper. Auto-discovered by the shortcodes extension (folder + `config.php`); tag is `tag_list`.
