---
type: shortcode
name: map
since: original Unyson
provides: leaf-shortcode + data-provider-extensible
---

# Map

A Google Map widget with the same pluggable data-provider system as
`[calendar]`. Default provider lets the user add pin entries. The
shortcode is registered programmatically rather than via `config.php`
(the file exists but its contents are commented out).

## Registration

`class-fw-shortcode-map.php` declares `FW_Shortcode_Map extends
FW_Shortcode`. Like `[calendar]`, the class exists primarily to expose
the data-provider API (`fw_shortcode_map_provider` filter +
`_get_picker_dropdown_choices()` + `_get_picker_choices()`).

`config.php` exists but its contents are **commented out** — page-builder
registration likely happens elsewhere (the parent shortcodes extension
registers the page-builder tab/title via the class's `get_shortcode_config()`
return, but for this shortcode that's not customized — the registration
probably comes from a shared default).

If you need to set the page-builder tab/title/description for this
shortcode, uncomment `config.php` and edit the `$cfg['page_builder']`
array. Otherwise it relies on defaults.

## Options schema (atts)

Source of truth: `options.php`. Two tabs + Animations + Advanced.

### Tab: Content

All Content fields are wrapped in a single flattening `group` (`group_content`),
so the group key does **not** appear in att paths (groups are transparent on save).

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `data_provider.population_method` | `multi-picker.select` (`custom` + any filter-registered providers) | `custom` | Which provider supplies map pins |
| `data_provider.custom.{…}` | provider-specific | — | Per-provider data shape; opaque from this AGENTS.md |
| `map_engine.engine` | `multi-picker.select` (`osm` / `google`) | `osm` | Which mapping library renders the map. `osm` = Leaflet + OpenStreetMap (free); `google` = Google Maps JS API (needs key + billing). The picker reveals per-engine sub-fields. |
| `map_engine.osm.osm_style.provider` | `multi-picker.select` (`osm`/`carto`/`opentopomap`/`cyclosm`/`hot`/`esri`/`stadia`/`thunderforest`/`maptiler`) | `osm` | OSM tile **provider**. A nested multi-picker: picking a provider reveals only its variant select (CARTO/Stadia/Thunderforest/MapTiler) and its key field (keyed providers). Keyless single-style providers reveal nothing. |
| `map_engine.osm.osm_style.{carto,stadia,thunderforest,maptiler}.{…}_variant` | `select` | first variant | Per-provider style variant. **Choice values are the `OSM_TILES` ids** (e.g. `carto_dark`, `stamen_toner`). PHP `resolve_osm_style()` maps provider+variant → the style id emitted as `data-osm-style`. |
| `map_engine.osm.osm_style.{stadia,thunderforest,maptiler}.{provider}_key` | `text` (fw-storage `wp-option`) | — | Site-wide provider API key, shown only under its provider group. Read in PHP via `get_option('unysonplus:{provider}-key')`, emitted as `data-{provider}-key`. A keyed style with no key falls back to keyless OSM Standard. |
| `map_engine.google.gmap-key` | `gmap-key` (or `text` for legacy framework < 2.5.7) | — | Google Maps API key. Stored as a WP option (`fw-option-types:gmap-key`), shared site-wide. Also drives the admin picker's Google-vs-OSM auto-detect. |
| `map_engine.google.map_type` | `select` (`roadmap` / `terrain` / `satellite` / `hybrid`) | `roadmap` | Google tile style (Google engine only). |
| `map_height` | `unit-input` (`px`/`vh`/`%`/`rem`/`em`) | `{value:'',unit:'px'}` | Map height. Saved as `array('value','unit')`; PHP compiles it to a CSS length (`FW_Option_Type_Unit_Input::to_string` → `"400px"`/`"50vh"`) and emits `data-map-height`, which the JS applies via `.css('height', …)`. Legacy bare-number saves are migrated to `<n>px`. Engine-agnostic (top-level, outside the picker). |
| `disable_scrolling` | `switch` (`false` / `true`, inverted labels) | `false` | Prevent zoom on scroll until user clicks the map. **Labels are inverted** — `left-choice: false` is labeled "Yes" (enable scrolling), `right-choice: true` is labeled "No" |

### Tab: Styling

Wrapped in `group_colors` + `group_spacings` (both flatten).

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `bg_color` | `sc_color_field_compact` (bg) | — | Wrapper background |
| `spacing` | `sc_spacing_field` | — | Wrapper margin/padding |

### Tabs: Animations + Advanced

Standard.

## Rendering

`views/view.php` outputs a map container; `static/js/scripts.js`
initializes Google Maps with the API key and renders pins per the data
provider.

**Class contract (keep in sync across 3 files):** the wrapper carries the
`map` base class (from `base_class`) and holds the `data-*` map config; the
inner canvas is `.fw-map-canvas`. `scripts.js` selects `.map` wrappers (that
contain a `.fw-map-canvas`) and `styles.css` targets `.fw-map-canvas`. If you
rename either class, change it in the view, the JS selectors, **and** the CSS —
a past mismatch (`.fw-map`/`.fw-map-canvas` in JS vs `map`/`.map-canvas` in the
view) silently broke front-end rendering entirely.

The map lazy-initializes via `IntersectionObserver` when scrolled near the
viewport. Each wrapper carries `data-map-engine` (`osm` | `google`); the script
waits for the matching global (`L` for Leaflet, `google.maps` for Google) before
rendering, since the library is enqueued per-render.

**OSM tile styles.** For the `osm` engine the wrapper also carries
`data-osm-style` plus `data-{stadia,thunderforest,maptiler}-key`. `scripts.js`
holds the `OSM_TILES` registry (URL template + attribution + zoom per style) and
`buildTileLayer(cfg)` picks the entry, substitutes `{KEY}` for keyed providers,
and falls back to keyless `standard` when a key-required style has no key. To add
a provider: add one `OSM_TILES` entry **and** one `osm_style` choice in
`options.php` (and, if keyed, a key field + `data-*-key`). Keep attribution in the
layer — these free tiles require it and have fair-use limits.

**Two engines, one script.** `scripts.js` has parallel `initLeaflet()` /
`initGoogle()` paths that share the info-window `_.template`, `calculateCenter()`,
and the lazy-load wrapper. The PHP side (`class-fw-shortcode-map.php
::enqueue_map_engine()`) enqueues only the chosen library: Leaflet 1.9.4 from CDN
for `osm`, or the Google Maps JS API (with `loading=async` + the shared key) for
`google`. `static.php` enqueues only the engine-agnostic CSS + `scripts.js`.
Scroll-zoom: Leaflet `scrollWheelZoom: !disable`; Google `gestureHandling`
(`cooperative` when disabled, else `greedy`) — never the deprecated `scrollwheel`.

**Admin pin picker engine is independent and auto-detected.** The "Add/Edit
Location" popup uses the framework `map` option type, which renders with Google
when a `gmap-key` exists and **falls back to Leaflet + free Nominatim search when
it doesn't** (`FW_Option_Type_Map::_enqueue_static()` →
`includes/option-types/map/static/js/scripts-osm.js`). So pins can be added with
zero Google setup. Both pickers write the identical value shape (lat/lng +
address fields).

## Pitfalls

1. **Google Maps API key required** — without `gmap-key`, the map fails
   silently (or shows Google's "for development purposes only" watermark).
   The key is stored as a WP option, shared across all map shortcodes.
2. **`gmap-key` storage location** — for framework ≥ 2.5.7, uses the
   built-in `gmap-key` option type which manages the key globally. For
   older framework, falls back to a `text` field bound to
   `wp-option:fw-option-types:gmap-key`. Generators should write to the
   WP option once, not per shortcode.
3. **`disable_scrolling` switch values are inverted vs labels** —
   `value: false` shows label "Yes" (scrolling enabled = scroll-zoom
   works). Read the labels, not the values, when reasoning about user
   intent.
4. **`config.php` is commented out** — page-builder registration may
   come from a default elsewhere. If the Map thumbnail doesn't appear in
   the Layout Elements tab, uncomment and edit `config.php`.
5. **`data_provider` is a multi-picker** — same shape as Calendar.

## Verification

1. Set the Google Maps API key in Theme Settings or via the gmap-key
   field.
2. Drag Map → modal opens.
3. Add pins via the data provider.
4. Set `map_height` to `400px` → map renders 400px tall; set `50vh` → half the viewport height.
5. Set `disable_scrolling` to the "No" labeled option → scroll over the
   map zooms it.
6. Switch `map_type: satellite` → map switches to satellite tiles.

## Files

- `class-fw-shortcode-map.php` — main class with data-provider API
- `config.php` (commented-out — uncomment if customizing page-builder
  registration)
- `options.php`, `static.php`, `views/view.php`
- `static/js/scripts.js` — Google Maps init
- `static/css/styles.css` (via static.php)
- `static/img/page_builder.png` — Layout Elements thumbnail

Custom class exists because of the data-provider extensibility API.
