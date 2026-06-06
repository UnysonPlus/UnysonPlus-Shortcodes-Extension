---
type: shortcode
name: row
since: original Unyson
provides: page-builder-structural
---

# Row

A page-builder structural element representing one Bootstrap row of
columns. **Invisible in the editor** — the Layout Elements tab does NOT
show a "Row" thumbnail; rows are auto-synthesized by the page-builder
items corrector at save time, grouping adjacent column items into row
wrappers.

This is one of three **structural builders** (`section` / `row` / `column`)
that compose the page-builder's hierarchy:
```
section → row → column → leaf shortcodes
```

The items corrector enforces this hierarchy automatically. Users drag
sections (root-level), drag columns into sections (auto-wrapped in a
synthesized row), and drag leaf shortcodes into columns.

## Registration

`class-fw-shortcode-row.php` declares `FW_Shortcode_Row extends
FW_Shortcode`. The `_init()` method hooks ONE action:

| Hook | Purpose |
|------|---------|
| `fw_option_type_builder:page-builder:register_items` action → `_action_register_builder_item_types()` | Loads `Page_Builder_Row_Item` when the editor renders |

**No `fw_section_like_types` filter** — rows are NOT section-like. They
sit inside sections, not at root. They have no Layout Elements thumbnail
(`get_thumbnails_data()` returns an empty array).

**No `_filter_add_data` collect_shortcodes_data hook** — rows have no
options to expose to the frontend collector.

## Page-builder item class

`includes/page-builder-row-item/class-page-builder-row-item.php`
declares `Page_Builder_Row_Item extends Page_Builder_Item` (NOT
`Page_Builder_Section_Like_Item`):

| Override | Returns |
|----------|---------|
| `get_type()` | `'row'` |
| `enqueue_static()` | (empty — no admin / frontend assets to enqueue per-row) |
| `get_thumbnails_data()` | `[]` — no Layout Elements thumbnail; row isn't manually draggable |
| `get_value_from_attributes($attributes)` | Just sets `type: 'row'` on the attributes |
| `get_shortcode_data($atts)` | Returns `{ tag: 'row', atts: $atts }` |

The file-scope `FW_Option_Type_Builder::register_item_type('Page_Builder_Row_Item')`
call at the bottom registers it.

## Options schema (atts)

**No `options.php` file** — rows have no editable options. Their
existence is structural: they wrap columns inside a section.

The atts saved on a row are minimal — just the synthesized data needed
to round-trip through the items corrector. Generators producing
page-builder JSON should emit rows as:

```json
{
  "type": "row",
  "atts": {},
  "_items": [
    /* column items */
  ]
}
```

## Rendering

`views/view.php` outputs a Bootstrap `<div class="row">` containing
`$content` (the rendered columns inside).

`static.php` enqueues any frontend assets the row needs (typically just
Bootstrap utilities inherited from the theme; rows don't ship their own
CSS).

## Pitfalls

1. **Rows are synthesized, not user-placed** — the items corrector at
   save time scans each section's children and groups consecutive
   columns into rows. A user dragging a column directly into a section
   in the editor sees it placed loosely; on save, the corrector wraps it
   in a row.
2. **No Layout Elements thumbnail** — rows can't be manually dragged.
   Don't expect to see a "Row" entry in the page-builder header.
3. **Generators producing page-builder JSON must emit rows explicitly** —
   even though users don't create them, the saved JSON shape requires
   `[section [row [column ...]]]` nesting. If you emit `[section [column]]`,
   the items corrector will fix it on import — but emitting the correct
   shape makes the import deterministic.
4. **`Page_Builder_Row_Item` extends `Page_Builder_Item`, NOT
   `Page_Builder_Section_Like_Item`** — rows are NOT section-like (they
   don't live at root). Don't register a row variant via the section-
   like recipe; create a new structural item if you need a row variant.

## Verification

1. Drag a Section → drop a column into it → save.
2. Inspect the saved JSON — the section's `_items` contains a
   synthesized row whose `_items` contains the column.
3. Drag a second column adjacent to the first → save → both columns
   are grouped into the same synthesized row.
4. Drag a column to a row break (visually inline-block wrapping) → save
   → the corrector splits into two rows.

## Files

- `class-fw-shortcode-row.php` — main class (minimal `_init` hook)
- `static.php`, `views/view.php`
- `includes/page-builder-row-item/class-page-builder-row-item.php` —
  the structural item class

**No** `options.php`, no `config.php` (rows have no editable options
and don't appear in any Layout Elements tab).
