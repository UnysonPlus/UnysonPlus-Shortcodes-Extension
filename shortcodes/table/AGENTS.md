---
type: shortcode
name: table
since: original Unyson
provides: leaf-shortcode + custom-option-types
---

# Table

A spreadsheet-style table editor. Supports two render modes — `tabular`
(generic data table) and `pricing` (pricing table with optional button
rows). Uses two custom Unyson option types — `fw-option-type-table`
(the spreadsheet UI) and `fw-option-type-textarea-cell` (multi-line
cell editor) — both registered by this shortcode's class.

## Two editors behind one option type (read this first)

`fw-option-type-table` now renders **two** editors, toggled by the
`table_purpose` selector (`views/view.php` is the orchestrator):

- **`tabular` → new JSON-backed editor** (`views/tabular-editor.php` +
  `static/js/tabular-editor.js` + `static/js/import-export.js`). The whole
  UI is built client-side from a JSON model and written back into a single
  hidden `<textarea name="…[__json]">`. Features: inline-editable cells,
  toolbar, drag-reorder rows, per-row/col popover menus (insert / duplicate /
  move / delete), per-column alignment, header/footer row counts, **merge /
  unmerge** (colspan/rowspan), and **import/export** (paste HTML/Word, upload
  CSV, paste Excel/Sheets TSV onto the grid, download CSV, copy TSV).
- **`pricing` → legacy grid editor** (`views/pricing-editor.php`, the original
  cell-by-cell grid + `static/js/scripts.js`). Untouched: it still embeds
  `button` / `switch` / `popup` option types per cell and serializes through
  Unyson the old way.

`FW_Option_Type_Table::_get_value_from_input()` branches: if `__json` is present
and the selected purpose is **not** `pricing`, it calls `get_value_from_json()`
(decode + `wp_kses` sanitize each cell against `allowed_cell_html()`); otherwise
it runs the original pricing parser. Both produce the **same** `{header_options,
cols, rows, content}` db shape, so old tables and the renderer are unaffected.

## Registration

`class-fw-shortcode-table.php` declares `FW_Shortcode_Table extends
FW_Shortcode`. The `_init()` method hooks:

| Hook | Purpose |
|------|---------|
| `fw_option_types_init` action → `_action_load_option_type()` | Loads `FW_Option_Type_Table` + `FW_Option_Type_Textarea_Cell` classes |
| `fw_ext_shortcodes_enqueue_static:table` action → `_action_enqueue_buttons()` | When a table contains button rows, enqueues the `[button]` shortcode's static assets so buttons inside cells render correctly |

The two custom option types live in:
- `includes/fw-option-type-table/class-fw-option-type-table.php`
- `includes/fw-option-type-textarea-cell/class-fw-option-type-textarea-cell.php`

They're shortcode-scoped (defined here, not in the framework's option-
type registry) because Unyson shortcodes don't have a built-in
`includes/` autoload — the class file registers itself when required.

## Options schema (atts)

Source of truth: `options.php`. Two tabs + Animations + Advanced.

### Tab: Content

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `table` | `table` (custom option type) | — | The entire table data structure |
| `table.header_options.table_purpose` | (inside the table option) | `tabular` | Render mode: `tabular` (data table) or `pricing` (pricing table). **Selects which view file is used** — `views/{table_purpose}.php` |
| `table.header_options.{…}` | (inside the table option) | — | Other header-level configuration (column widths, sorting, etc.) |
| `table.rows[]` | (inside the table option) | — | Row metadata. Each row has a `name` field; `name === 'button-row'` triggers button-shortcode rendering for that row |
| `table.content[][]` | (inside the table option) | — | Cell content. 2D array indexed by `[row][col]` |
| `table.content[row][col].textarea` | (string, sanitized HTML) | — | Tabular cell content (inline allowlist) |
| `table.content[row][col].colspan/rowspan/merged` | (int/int/bool) | 1/1/false | Cell merge state (tabular). `merged` cells are skipped by the renderer |
| `table.cols[i].align` / `.width` | (string) | — | Per-column alignment (`left/center/right`) and width (tabular) |
| `table.header_options.header_rows` / `footer_rows` | (int) | 1 / 0 | How many leading/trailing rows become `<thead>` / `<tfoot>` (tabular) |
| `table.content[row][col].button` | (object) | — | When a cell holds a button, the value is a structured button-shortcode atts object (auto-rendered by `_action_enqueue_buttons`) |

The full `table` field shape is opaque from the outside — it's managed
by the `fw-option-type-table` custom option's UI (spreadsheet editor).
AI generators producing table payloads should work from a reference
exported `.json` rather than constructing the shape manually.

### Tab: Table Options (tabular tables)

Top-level atts (read directly by `views/tabular.php`). Display group:
`style_striped`, `style_hover`, `style_bordered`, `style_condensed`,
`sticky_header` (switches), `caption` (text) + `caption_position`
(`top`/`bottom`). Visitor group (drives `static/js/datatable.js`):
`enable_sort`, `enable_search`, `enable_pagination`, `pagination_length`,
`enable_length_change`, `enable_info`. The enhancer is enqueued **only** when
sort/search/paginate is on **and** the table has no merged cells.

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

`_render()` reads `atts.table.header_options.table_purpose` to pick the
view:
- `views/tabular.php` — semantic data table: real `<thead>`/`<tbody>`/
  `<tfoot>` from header/footer row counts, per-column align + width
  (`<colgroup>`), colspan/rowspan, optional caption, display modifier classes,
  and (when opted in, no merges) `class="fw-datatable"` + `data-*` config that
  `static/js/datatable.js` upgrades into sort/search/paginate. Back-compat:
  if `header_options.header_rows` is absent it derives header count from leading
  `rows[i].name === 'heading-row'`.
- `views/pricing.php` — pricing layout with featured-column treatment,
  call-to-action button rows (unchanged).

For button rows (`row.name === 'button-row'`), the `_action_enqueue_buttons`
hook enqueues each `[button]`'s static assets so their CSS / JS works.

## Pitfalls

1. **`table` field is opaque** — the custom `fw-option-type-table`
   manages its own complex shape. AI generators should not synthesize
   `table` atts from scratch; reference a real exported `.json` for the
   shape.
2. **Two render modes, selected by data** — `table_purpose` inside the
   data determines which view runs. If you want a pricing table,
   generators must set `table.header_options.table_purpose: 'pricing'`,
   not pick a separate shortcode.
3. **Embedded buttons trigger button asset enqueue** — when generating
   a pricing table with buttons, ensure `[button]` shortcode is also
   registered. The `_action_enqueue_buttons` callback fetches the
   button shortcode instance via `fw_ext('shortcodes')->get_shortcode('button')`;
   if you've replaced the default button shortcode, filter
   `fw:ext:shortcodes:table:button-shortcode-name`.
4. **Custom option types declared via `require_once`** — the option
   types load via `_action_load_option_type` on the `fw_option_types_init`
   action. If you fork the shortcode, keep the action hook intact or the
   spreadsheet editor breaks.

## Verification

1. Drag Table → modal opens with the spreadsheet editor.
2. Fill cells → save → renders as a data table (default `tabular` mode).
3. Switch `table.header_options.table_purpose: 'pricing'` (via the
   spreadsheet header UI) → re-renders using the pricing view.
4. Add a button row → embedded button renders with proper styles.

## Files

- `class-fw-shortcode-table.php` — main class + custom option-type loader
- `config.php`, `options.php` (now includes the **Table Options** tab), `static.php`
- `views/tabular.php` — semantic data table renderer (thead/tfoot/align/merge/caption)
- `views/pricing.php` — pricing table renderer (unchanged)
- `static/js/datatable.js` — dependency-free front-end sort/search/paginate enhancer
- `static/css/styles.css`, `static/img/page_builder.png`
- `includes/fw-option-type-table/class-fw-option-type-table.php` —
  option-type class (JSON serialization + `build_editor_model()` /
  `get_value_from_json()` / `allowed_cell_html()`)
- `includes/fw-option-type-table/views/view.php` — orchestrator (purpose toggle)
- `includes/fw-option-type-table/views/tabular-editor.php` — new editor mount + JSON seed
- `includes/fw-option-type-table/views/pricing-editor.php` — extracted legacy grid
- `includes/fw-option-type-table/views/{cell-head,cell-worksheet}-template.php` —
  legacy (pricing) cell templates
- `includes/fw-option-type-table/static/js/tabular-editor.js` — new editor UI
- `includes/fw-option-type-table/static/js/import-export.js` — pure parsers
  (`window.FwTabularIO`: CSV/TSV, HTML/Word table, inline sanitizer, CSV/TSV export)
- `includes/fw-option-type-table/static/js/scripts.js` — legacy (pricing) editor JS
- `includes/fw-option-type-table/static/css/{default,extended,tabular-editor}.css`
- `includes/fw-option-type-textarea-cell/…` — multi-line cell editor (pricing)

Custom class + custom option types — the most structurally complex
leaf shortcode in the codebase.
