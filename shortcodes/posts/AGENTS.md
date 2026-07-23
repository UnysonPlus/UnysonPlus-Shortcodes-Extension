---
type: shortcode
name: posts
since: original Unyson (heavily extended in the Unyson+ fork — modes, AJAX, slider)
provides: leaf-shortcode
---

# Posts

The data-driven shortcode. Queries the WordPress post DB with full
`WP_Query`-equivalent control (post type, taxonomy filters, includes /
excludes, authors, date range, sticky handling, custom field sorting),
then renders results through one of **twenty-three registry-driven card
designs** (`standard`, `side-left`, `side-right`, `overlay`, `minimal`,
`hero-split`, `alternating`, `gradient`, `listicle`, `newslist`,
`editorial`, `polaroid`, `timeline`, `tile`, `circular`, `accent`,
`cover`, `quote`, `postcard`, `badge`, `filmstrip`, `diagonal`, `glass`)
inside one of four layout modes (`grid`, `list`, `masonry`, `slider`).
Each design's CSS loads **only when that design is used** (per-instance
enqueue keyed on `static/css/card/<style>.css`); the base `styles.css`
carries only the shared/structural CSS. Supports AJAX pagination / infinite scroll / live filters,
plus transient-cached HTML output.

## AJAX endpoints (Load More / Infinite Scroll / Live Filters)

`class-fw-shortcode-posts.php` (`FW_Shortcode_Posts`) registers the front-end
admin-ajax endpoints. Its `_init()` runs on **every** request (the shortcodes
loader instantiates each `FW_Shortcode_*` on init), so the endpoints exist even
though `static.php` / `view.php` only load when a Posts block is on a page — the
newsletter shortcode uses the same pattern.

- Actions: `fw_sc_posts_loadmore` (Load More + Infinite Scroll) and
  `fw_sc_posts_filter` (Live Filters); both `priv` + `nopriv`.
- **Instance recovery:** on render, `sc_posts_render()` gives an AJAX-enabled
  instance a **stable wrapper `id`** (`ps-<hash>`, or the user's CSS ID) and
  stores its resolved atts in a transient `sc_posts_ax_<id>`. The JS posts back
  only that id (+ `page` / `term`) — the client never sends atts, so there's
  nothing to tamper with. `scripts.js` reads `$wrap.attr('id')`.
- **Rendering:** both endpoints reuse `sc_posts_render_cards( $atts, $posts, $start_index )`
  (the shared card-loop helper, also used by the full render) so all three paths
  emit identical markup. `$start_index` keeps first-post treatments (hero-split /
  featured / zig-zag) on the true first post across paginated appends.
- **Include-safety:** `view.php`'s executable tail is guarded by `isset( $atts )`
  so the AJAX class can `require_once` it purely for its `sc_posts_*` helpers.
- **JS is loaded on demand:** `static.php` only *registers* `scripts.js`; the
  per-instance enqueue hook enqueues + localizes it **only** for instances using
  a JS behaviour (slider / AJAX pagination / filters). A static grid ships no JS.

## Card-design registry (extensible) — read before editing rendering

Card styles are a **registry** at `views/parts/registry.php` — the single
source of truth. Each entry maps a card-style key → `{ label, thumb (svg),
part, first_style?, alternate?, needs_ratio? }`. Three places read it:
`options.php` (builds the **Card Style** image-picker choices), `view.php`
(`sc_posts_render_card` dispatches to `parts/card-<part>.php`; the main loop
reads `first_style`/`alternate` for composition), and the thumbnails under
`static/img/card/`. **Adding a card design = one registry entry +
`views/parts/card-<part>.php` + `static/img/card/<thumb>.svg`** (+ any CSS).
`needs_ratio` reveals the image-ratio / vertical-align sub-options in the
picker; `first_style` makes the first post use a different style (hero-split);
`alternate` flips side-left/right per row (zig-zag).

## Options UX — pickers + option-gating (since the Design rework)

The options are organised around **image-picker multi-pickers** that reveal
only the chosen value's sub-options (mirrors the Testimonials Design picker). The **`card`** and
**`design`** (layout) pickers run in **popover mode** (`'popover' => true`): their visible label
is on the **top-level** multi-picker and the inner picker sub-option is `'label' => false` — keep
it that way (moving the label back onto the picker changes how the popover renders). The saved
value shapes are unchanged by the popover.

- **`design`** (new id) — Layout mode image-picker (`grid`/`list`/`masonry`/
  `slider`). Reveals columns/gaps/equal-height/featured for grid, columns/gaps
  for masonry, row-gap for list, and the five **slider** controls for slider
  (the slider options moved here out of the Navigation tab).
- **`card`** (new id) — Card Style image-picker (choices from the registry).
  `needs_ratio` styles reveal image ratio + vertical aligns.
- **`pagination`** (new id) — Pagination image-picker; numeric/prev_next reveal
  position + align, load-more reveals align.
- **`readmore`** (new id) — Read-More select-picker; the **Button** choice
  reveals `readmore_btn_style` + `readmore_btn_size` (reusing the theme button
  preset helpers `sc_get_button_*_choices()` — no duplicated Button options).

**Back-compat:** these are **new option ids** (legacy scalar `layout_mode` /
`card_style` / `pagination_type` / `readmore_style` are never fed into a
multi-picker → no blank-modal). `view.php`'s `sc_posts_normalize_atts()`
resolves every moved option back to its original flat key (new nested path →
legacy flat path → default), so existing saved instances render unchanged; the
builder shows the moved options at defaults until the instance is re-saved.
Every tab is wrapped in `group` containers.

## Registration

No `class-fw-shortcode-posts.php` file — leaf shortcode, auto-instantiated
by Unyson's loader. No page-builder item class. The complexity is in the
options + view + frontend JS — the registration surface is trivial.

`config.php` declares a rich `title_template` that previews the queried
post type, count, and chosen card style on the canvas item header (no
need to open the modal to remember what each Posts shortcode shows).

## Options schema (atts)

Source of truth: `options.php`. Five tabs — Query, Design, Elements,
Navigation & Cache, plus the shared Styling / Animations / Advanced trio.
This shortcode has the largest option surface in the codebase — ~70 atts.
The table below groups them by tab + functional purpose.

### Tab: Query

Drives the underlying `WP_Query`.

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `use_current_query` | `switch` | `no` | Use the posts of the page being viewed (archive / category / tag / search / blog index) instead of a custom query — ideal for an archive Body Template. When `yes`, every other Query-tab option is ignored and the view reuses `$GLOBALS['wp_query']` (only outside admin / singular, and only if it has posts) |
| `post_type` | `select` (dynamic) | `post` | Source post type. Choices auto-populated from `get_post_types(['public' => true])`, attachment excluded |
| `taxonomy_filter` | `text` | — | `taxonomy:term-slug,term-slug` filter (e.g. `category:news,tech`, `post_tag:featured`). Empty = no filter |
| `taxonomy_relation` | `radio` (`IN` / `AND` / `NOT IN`) | `IN` | Match any term (OR), all terms (AND), or exclude these terms |
| `include_ids` | `text` | — | Comma-separated post IDs to cherry-pick — **overrides taxonomy filter** when present |
| `exclude_ids` | `text` | — | Comma-separated IDs to hide |
| `author_ids` | `text` | — | Comma-separated user IDs. Empty = all authors |
| `date_range` | `select` (`any` / `last_7` / `last_30` / `last_90` / `this_year`) | `any` | Published-date window |
| `posts_per_page` | `short-text` | `6` | `-1` shows all matching |
| `offset` | `short-text` | `0` | Skip N posts from query start |
| `orderby` | `select` (`date` / `modified` / `title` / `rand` / `comment_count` / `menu_order` / `meta_value_num`) | `date` | Sort key |
| `meta_key` | `text` | — | Required when `orderby === 'meta_value_num'` |
| `order` | `radio` (`DESC` / `ASC`) | `DESC` | Sort direction |
| `exclude_current` | `switch` | `yes` | Hide the post being viewed from its own grid (useful for "Related Posts") |
| `sticky_handling` | `select` (`default` / `pin_top` / `ignore` / `only`) | `default` | Sticky-post strategy |

### Tab: Design

The shape of the grid + each card. (Titled **Design** in `options.php`; the
`design` Layout picker + `card` Card-Style picker live here, along with the
grid/masonry/slider sub-controls each reveals.) The `layout_mode` /
`card_style` / column / gap / slider atts below are the **flat keys** the view
reads after `sc_posts_normalize_atts()` resolves the picker paths.

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `layout_mode` | `select` (`grid` / `list` / `masonry` / `slider`) | `grid` | Top-level container behavior (stored as the `design/mode` picker) |
| `card_style` | `select` | `standard` | Card internals — one of the **23 registry designs** (`views/parts/registry.php`): `standard`, `side-left`, `side-right`, `overlay`, `minimal`, `hero-split` (1st post 2× overlay), `alternating` (zig-zag), plus `gradient`, `listicle`, `newslist`, `editorial`, `polaroid`, `timeline`, `tile`, `circular`, `accent`, `cover`, `quote`, `postcard`, `badge`, `filmstrip`, `diagonal`, `glass`. Stored as the `card/style` picker |
| `image_width_ratio` | `select` (`30-70` / `40-60` / `50-50` / `60-40`) | `40-60` | Image vs content split — revealed only for `needs_ratio` card styles (side / alternating / hero) |
| `image_vertical_align` | `select` (`top` / `center` / `stretch`) | `stretch` | Image alignment in side layouts (revealed for side / alternating, not hero) |
| `content_vertical_align` | `select` (`top` / `center` / `bottom` / `space-between`) | `top` | Content alignment in side layouts (revealed for side / alternating, not hero) |
| `columns_desktop` | merged Columns `multi-picker` (grid) / `select` (masonry) — `1`–`6` | `3` | Desktop card count. **Only the desktop count is exposed** in the builder; tablet & phone auto-derive in the view (see below). Grid stores `{ count:'N', 'N':{ col_ratio:[…] } }`; masonry stores a scalar count. Legacy scalar `columns_desktop` still resolves |
| `columns_tablet` | derived | `2` | **Auto-derived**, not a builder field: `min(N,2)`, or `N-1` for 5+ columns |
| `columns_mobile` | derived | `1` | **Auto-derived** — always `1` |
| `col_ratio` | `split-slider` (grid only; cols 2/3/4/6 — not 1 or 5) | equal split | Optional per-column widths for a featured / dominant card. Saved as `[{ w, name }, …]` inside `columns_desktop['N']['col_ratio']`. The view emits an explicit `--posts-grid-tpl` (fr units) only when the widths are meaningfully unequal (max−min > 2); equal splits fall back to the plain `repeat()` grid. Tablet / phone stay equal |
| `mobile_layout_override` | `select` (`inherit` / `standard` / `side-left` / `minimal`) | `inherit` | Force a different card style at ≤ 782px |
| `column_gap` | `select` (Gap Scale presets) | `''` (Use Default Gap) | Horizontal grid gap. Choices from `sc_get_gap_select_choices()` (theme Gap Scale presets, like Section/Row/Column). Empty = base default. Legacy numeric px saves still resolve in `view.php` |
| `row_gap` | `select` (Gap Scale presets) | `''` (Use Default Gap) | Vertical grid gap. Same preset choices + legacy-px back-compat |
| `card_padding` | `select` (`none` / `compact` / `regular` / `spacious`) | `regular` | Card internal padding density |
| `equal_height` | `switch` | `yes` | Force equal heights across a row (grid only) |
| `image_size` | `select` (WP image sizes) | `medium_large` | Which registered image size to enqueue |
| `image_ratio` | `select` (`ratio-16-9` / `4-3` / `3-2` / `1-1` / `2-3` / `auto`) | `ratio-16-9` | CSS aspect-ratio crop |
| `fallback_image_url` | `text` | — | Used when a post has no featured image. Empty = hide image on those cards |
| `featured_treatment` | `select` (`none` / `first-post-2x` / `first-post-hero`) | `none` | Special handling for the first post (grid only) |
| `text_align` | `select` (`left` / `center` / `right`) | `left` | Card text alignment |
| `slider_arrows_position` | `select` (`inside` / `outside` / `above` / `hidden`) | `outside` | Slider arrow placement — revealed by the `slider` Layout (stored under `design/slider/`) |
| `slider_dots_position` | `select` (`below` / `overlay-bottom` / `hidden`) | `below` | Slider dots placement |
| `slider_autoplay` | `switch` | `no` | Slider autoplay |
| `slider_interval` | `short-text` | `5000` | Autoplay interval (ms) |
| `slider_loop` | `switch` | `yes` | Slider wrap-around |

### Tab: Elements

Per-block visibility, order, and positioning inside each card.

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `element_order` | `addable-box` (sortable, limit 6) | image, cats, title, meta, excerpt, readmore (all `enabled: yes`) | Drag-to-reorder list of card blocks. Each entry: `{ slug, enabled }`. `slug` choices: `image`, `cats`, `title`, `meta`, `excerpt`, `readmore`. Image position INSIDE side/overlay cards is governed by `card_style`, NOT this list |
| `title_tag` | `select` (`h2` / `h3` / `h4` / `h5` / `div`) | `h3` | Title HTML tag |
| `cat_position` | `select` | `above-title` | Where category chips render: `above-title`, `below-title`, `in-meta`, or one of four `image-overlay-{corner}` positions |
| `cat_taxonomy` | `text` | `category` | Which taxonomy the chips display |
| `cat_max` | `short-text` | `2` | Max chips per card |
| `meta_items` | `checkboxes` (`date`, `author`, `comments`, `reading_time`) | `date: true, author: true` | Which meta items appear in the meta bar |
| `meta_layout` | `select` (`inline-dot` / `inline-pipe` / `inline-icons` / `stacked`) | `inline-dot` | Meta bar visual style |
| `date_format` | `select` (`wp` / `relative` / `long` / `short`) | `wp` | Date display format |
| `excerpt_source` | `select` (`auto` / `excerpt` / `content`) | `auto` | Where to pull excerpt from |
| `excerpt_length` | `short-text` | `25` | Word limit |
| `excerpt_suffix` | `short-text` | `…` | Suffix after trimmed excerpt |
| `readmore_style` | `select` (`button` / `text-link` / `arrow-only`) | `text-link` | Read-more visual style (stored as the `readmore/style` picker). The `button` choice reveals the two button sub-options below |
| `readmore_btn_style` | `select` (theme button color presets) | `''` | Button color — revealed under the `button` read-more choice; choices from `sc_get_button_style_choices()`. Stored at `readmore/button/readmore_btn_style` |
| `readmore_btn_size` | `select` (theme button size presets) | `''` | Button size — revealed under the `button` choice; choices from `sc_get_button_size_choices()`. Stored at `readmore/button/readmore_btn_size` |
| `readmore_text` | `text` | `Read more` | Read-more label (ignored for the `arrow-only` style) |

### Tab: Navigation & Cache

Pagination, AJAX filters, output caching. (The **slider** controls used to
live here but moved to the Design tab's `slider` Layout picker — see the Design
table above.)

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `pagination_type` | `image-picker` (`none` / `numeric` / `prev_next` / `ajax_loadmore` / `infinite`) | `none` | Pagination strategy (stored as the `pagination/type` picker; `numeric` / `prev_next` reveal position + align, `ajax_loadmore` reveals align only) |
| `pagination_position` | `select` (`below-grid` / `above-grid` / `both`) | `below-grid` | Where pagination renders |
| `pagination_align` | `select` (`left` / `center` / `right`) | `center` | Pagination alignment |
| `live_filters` | `switch` | `no` | Show AJAX category-chip filter bar |
| `filters_position` | `select` (`above-grid` / `left-sidebar` / `right-sidebar`) | `above-grid` | Where filter bar renders |
| `cache_output` | `switch` | `no` | Cache rendered HTML in a WP transient. Auto-flushed when any post is saved |
| `cache_hours` | `select` (`1` / `6` / `12` / `24`) | `12` | Cache lifespan |
| `no_results_text` | `text` | `Sorry, no posts matched your criteria.` | Empty-state message |

### Tab: Styling

Same shared structure as other shortcodes — `group_colors` (text_color,
bg_color, font_size_preset) + `group_spacings` (spacing field). Group
containers flatten on save.

### Tabs: Animations + Advanced

`sc_get_animation_fields()` + `sc_get_advanced_tab()`. Standard shared
shape.

### Generator note

For AI-generated `_fw_template_export` payloads, the Posts shortcode is
the heaviest in the system. Defaults are forgiving — every option has a
sensible default, so an AI generator can produce a minimal payload like:

```json
{
  "post_type": "post",
  "posts_per_page": "9",
  "layout_mode": "grid",
  "card_style": "standard",
  "columns_desktop": "3",
  "columns_tablet": "2",
  "columns_mobile": "1",
  "element_order": [
    { "slug": "image",    "enabled": "yes" },
    { "slug": "cats",     "enabled": "yes" },
    { "slug": "title",    "enabled": "yes" },
    { "slug": "meta",     "enabled": "yes" },
    { "slug": "excerpt",  "enabled": "yes" },
    { "slug": "readmore", "enabled": "yes" }
  ]
}
```

…and rely on `default_values` to fill the rest. For prompt-driven
generation, focus the LLM on the high-impact fields: `post_type`,
`posts_per_page`, `layout_mode`, `card_style`, `columns_*`,
`element_order` (which blocks shown / hidden / in what order),
`pagination_type`.

## Rendering

`views/view.php` is the orchestrator; per-card markup is delegated to one
of the **20 template parts** under `views/parts/` (registry-driven —
`sc_posts_render_card()` reads the registry's `part` and includes
`card-<part>.php`). The **23 registry designs** map onto these 20 part files
(some designs share a part — e.g. `side-left`/`side-right` both use
`card-side.php`; `alternating` reuses it too). The core structural parts are:

- `card-standard.php` — image on top, content below
- `card-side.php` — image beside content (handles both `side-left` /
  `side-right` via a flex direction class)
- `card-overlay.php` — content layered over the image
- `card-minimal.php` — no image, text-only

…plus one part per new design (`card-gradient.php`, `card-listicle.php`,
`card-newslist.php`, `card-editorial.php`, `card-polaroid.php`,
`card-timeline.php`, `card-tile.php`, `card-circular.php`, `card-accent.php`,
`card-cover.php`, `card-quote.php`, `card-postcard.php`, `card-badge.php`,
`card-filmstrip.php`, `card-diagonal.php`, `card-glass.php`). Parts are located
via `sc_posts_locate_part()` (child theme → parent theme → bundled override
resolution).

The `hero-split` and `alternating` card styles compose parts at the
list-iteration level — `hero-split` uses `first_style => overlay` for the first
post (rendered via the standard part otherwise); `alternating` flips the
effective style between `side-left` / `side-right` per row. The
`featured_treatment` first-post treatments (`first-post-2x` /
`first-post-hero`) splice extra classes / override the first card's style at
the same level.

Frontend JS at `static/js/scripts.js` powers the slider (when
`layout_mode === 'slider'`), AJAX pagination (`ajax_loadmore` / `infinite`),
the live-filter bar, and any reading-time computation.

`static/css/styles.css` carries the grid / list / masonry / slider layout
rules + card-style variants + meta-bar layouts.

## Pitfalls

1. **`include_ids` overrides taxonomy filter** — both shouldn't typically
   be set together. If you do, includes win. Document this in any AI prompt
   so the generator doesn't conflict the two.
2. **`element_order` is sortable + filterable** — the `enabled` flag is a
   `switch` string (`yes` / `no`, not boolean). Generators must emit
   strings.
3. **`card_style` interacts with `featured_treatment`** —
   `first-post-2x` and `first-post-hero` both override the global
   `card_style` for the first post in the list. Composing these gives
   `hero-split`-equivalent behavior even when `card_style` itself is
   `standard`.
4. **Slider, AJAX pagination, and cache are mutually compatible BUT**
   the transient key is `md5(atts) . '|' . paged` — it keys on the
   numeric `paged` query var but NOT on AJAX / live-filter request
   parameters, so a cached AJAX-paginated or filtered grid serves stale
   HTML. Disable `cache_output` when using AJAX pagination or live filters
   (the `cache_output` desc says the same).
5. **`fallback_image_url` is a string URL, not a WP upload** — the option
   is `type: text`, not `type: upload`. Generators should produce a plain
   URL string, not the `{ attachment_id, url }` shape WP uploads use.
6. **`meta_items` is a `checkboxes` map** — saved shape is
   `{ date: true, author: true, comments: false, ... }`. Missing keys are
   treated as `false`. Generators should emit the full map with explicit
   booleans, or accept that omissions default to false.
7. **Sticky `pin_top` is a re-sort, not a WP-Query flag** — the view
   pulls sticky posts to the top of the result set after the query runs.
   If you also set `orderby: rand`, the sticky posts still come first
   followed by random ordering of the rest.

## Verification

1. Drag Posts from Content Elements → renders as a 3-column grid of the
   site's 6 most recent posts (all defaults).
2. Edit → switch `layout_mode: slider` + set `slider_autoplay: yes` →
   reload → slider auto-rotates.
3. Switch `card_style: overlay` → cards render with content layered over
   the featured image.
4. Set `pagination_type: ajax_loadmore` → "Load More" button appears
   below the grid; clicking it appends posts without reload.
5. Set `live_filters: yes` → category chips appear above the grid;
   clicking one filters posts via AJAX.
6. Toggle `cache_output: yes` → save a post → confirm cached HTML
   refreshes automatically.
7. Save as template (Sections tab won't accept it — leaf — but works in
   Full templates) → export → import → all atts round-trip.

## Files

- `config.php` — page-builder config (Content Elements tab, large popup,
  preview `title_template` summarizing the query)
- `options.php` — 5 functional tabs + Styling + Animations + Advanced
- `static.php` — frontend CSS + JS enqueue
- `views/view.php` — orchestrator; iterates posts and dispatches to a
  card template part
- `views/parts/registry.php` — card-design registry (single source of
  truth; read by `options.php`, `view.php`, and the thumbnails)
- `views/parts/card-standard.php` — image-top card
- `views/parts/card-side.php` — image-beside card (both `side-left` /
  `side-right`)
- `views/parts/card-overlay.php` — overlay card
- `views/parts/card-minimal.php` — text-only card
- `views/parts/card-<design>.php` — one part per new registry design
  (gradient, listicle, newslist, editorial, polaroid, timeline, tile,
  circular, accent, cover, quote, postcard, badge, filmstrip, diagonal,
  glass)
- `static/css/styles.css` — shared / structural layout-mode + core
  card-style + meta-layout CSS
- `static/css/card/<key>.css` — per-design CSS, enqueued ONLY for
  instances using that design (auto-detected by name in `static.php`)
- `static/js/scripts.js` — slider, AJAX pagination, live filters,
  reading-time calc
- `static/img/page_builder.png` — Layout Elements thumbnail

No item class, no admin-side asset, no custom registration — the
complexity lives entirely in the options + view + frontend JS.
