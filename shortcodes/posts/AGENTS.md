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
then renders results through one of seven card layouts (`standard`,
`side-left`, `side-right`, `overlay`, `minimal`, `hero-split`,
`alternating`) inside one of four layout modes (`grid`, `list`,
`masonry`, `slider`). Supports AJAX pagination / infinite scroll / live
filters, plus transient-cached HTML output.

## Registration

No `class-fw-shortcode-posts.php` file — leaf shortcode, auto-instantiated
by Unyson's loader. No page-builder item class. The complexity is in the
options + view + frontend JS — the registration surface is trivial.

`config.php` declares a rich `title_template` that previews the queried
post type, count, and chosen card style on the canvas item header (no
need to open the modal to remember what each Posts shortcode shows).

## Options schema (atts)

Source of truth: `options.php`. Five tabs — Query, Layout & Positioning,
Elements, Navigation & Cache, plus the shared Styling / Animations /
Advanced trio. This shortcode has the largest option surface in the
codebase — ~70 atts. The table below groups them by tab + functional
purpose.

### Tab: Query

Drives the underlying `WP_Query`.

| Att | Type | Default | Description |
|-----|------|---------|-------------|
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

### Tab: Layout & Positioning

The shape of the grid + each card.

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `layout_mode` | `select` (`grid` / `list` / `masonry` / `slider`) | `grid` | Top-level container behavior |
| `card_style` | `select` | `standard` | Card internals: `standard` (image top), `side-left` / `side-right` (image beside), `overlay` (content over image), `minimal` (no image), `hero-split` (1st post 2× with overlay), `alternating` (zig-zag) |
| `image_width_ratio` | `select` (`30-70` / `40-60` / `50-50` / `60-40`) | `40-60` | Image vs content split — only for side / alternating / hero card styles |
| `image_vertical_align` | `select` (`top` / `center` / `stretch`) | `stretch` | Image alignment in side layouts |
| `content_vertical_align` | `select` (`top` / `center` / `bottom` / `space-between`) | `top` | Content alignment in side layouts |
| `columns_desktop` | `select` (`1`–`6`) | `3` | Grid columns at desktop |
| `columns_tablet` | `select` (`1`–`4`) | `2` | Grid columns at tablet |
| `columns_mobile` | `select` (`1` / `2`) | `1` | Grid columns at mobile |
| `mobile_layout_override` | `select` (`inherit` / `standard` / `side-left` / `minimal`) | `inherit` | Force a different card style at ≤ 782px |
| `column_gap` | `short-text` | `24` | Horizontal grid gap (px) |
| `row_gap` | `short-text` | `32` | Vertical grid gap (px) |
| `card_padding` | `select` (`none` / `compact` / `regular` / `spacious`) | `regular` | Card internal padding density |
| `equal_height` | `switch` | `yes` | Force equal heights across a row |
| `image_size` | `select` (WP image sizes) | `medium_large` | Which registered image size to enqueue |
| `image_ratio` | `select` (`ratio-16-9` / `4-3` / `3-2` / `1-1` / `2-3` / `auto`) | `ratio-16-9` | CSS aspect-ratio crop |
| `fallback_image_url` | `text` | — | Used when a post has no featured image. Empty = hide image on those cards |
| `featured_treatment` | `select` (`none` / `first-post-2x` / `first-post-hero`) | `none` | Special handling for the first post |
| `text_align` | `select` (`left` / `center` / `right`) | `left` | Card text alignment |

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
| `readmore_style` | `select` (`button` / `text-link` / `arrow-only`) | `text-link` | Read-more visual style |
| `readmore_text` | `text` | `Read more` | Read-more label |

### Tab: Navigation & Cache

Pagination, AJAX filters, slider controls, output caching.

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `pagination_type` | `select` (`none` / `numeric` / `prev_next` / `ajax_loadmore` / `infinite`) | `none` | Pagination strategy |
| `pagination_position` | `select` (`below-grid` / `above-grid` / `both`) | `below-grid` | Where pagination renders |
| `pagination_align` | `select` (`left` / `center` / `right`) | `center` | Pagination alignment |
| `live_filters` | `switch` | `no` | Show AJAX category-chip filter bar |
| `filters_position` | `select` (`above-grid` / `left-sidebar` / `right-sidebar`) | `above-grid` | Where filter bar renders |
| `slider_arrows_position` | `select` (`inside` / `outside` / `above` / `hidden`) | `outside` | Slider arrow placement — active when `layout_mode === 'slider'` |
| `slider_dots_position` | `select` (`below` / `overlay-bottom` / `hidden`) | `below` | Slider dots placement |
| `slider_autoplay` | `switch` | `no` | Slider autoplay |
| `slider_interval` | `short-text` | `5000` | Autoplay interval (ms) |
| `slider_loop` | `switch` | `yes` | Slider wrap-around |
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
of four template parts under `views/parts/`:

- `card-standard.php` — image on top, content below
- `card-side.php` — image beside content (handles both `side-left` /
  `side-right` via a flex direction class)
- `card-overlay.php` — content layered over the image
- `card-minimal.php` — no image, text-only

The `hero-split` and `alternating` card styles compose these parts at the
list-iteration level — e.g. `hero-split` renders the first post via
`card-overlay.php` at 2× column-span, then the rest via `card-standard.php`.

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
   caching is keyed only on the atts, not on the current page query
   parameters — so caching an AJAX-paginated grid will serve the same
   page 1 always. Disable `cache_output` when using AJAX pagination
   or live filters.
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
- `views/parts/card-standard.php` — image-top card
- `views/parts/card-side.php` — image-beside card (both `side-left` /
  `side-right`)
- `views/parts/card-overlay.php` — overlay card
- `views/parts/card-minimal.php` — text-only card
- `static/css/styles.css` — all layout-mode + card-style + meta-layout
  CSS
- `static/js/scripts.js` — slider, AJAX pagination, live filters,
  reading-time calc
- `static/img/page_builder.png` — Layout Elements thumbnail

No item class, no admin-side asset, no custom registration — the
complexity lives entirely in the options + view + frontend JS.
