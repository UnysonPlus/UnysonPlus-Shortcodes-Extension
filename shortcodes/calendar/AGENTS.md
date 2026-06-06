---
type: shortcode
name: calendar
since: original Unyson
provides: leaf-shortcode + data-provider-extensible
---

# Calendar

A daily / weekly / monthly calendar widget driven by a pluggable data
provider system. Default `custom` provider lets the user add events
inline (title + URL + date-range datetime picker). Third-party plugins
can register additional providers (e.g. Events Calendar, FullCalendar
integrations) via the `fw_shortcode_calendar_provider` filter.

## Registration

`class-fw-shortcode-calendar.php` declares
`FW_Shortcode_Calendar extends FW_Shortcode`. **Unlike most leaf
shortcodes**, this one has a custom class because it manages the data
provider registry: `apply_filters('fw_shortcode_calendar_provider', …)`
collects all providers at runtime; the options.php multi-picker
populates its choices from `_get_picker_dropdown_choices()` and
`_get_picker_choices()` on the class.

No `_init()` hooks beyond what `FW_Shortcode` provides — the class
exists purely to expose the data-provider API.

## Options schema (atts)

Source of truth: `options.php`. Two tabs + Animations + Advanced.

### Tab: Content

Wrapped in `group_content` (flattens).

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `data_provider.population_method` | `multi-picker.short-select` (`custom` + any filter-registered providers) | `custom` | Which provider supplies events |
| `data_provider.custom.custom_events` | `addable-popup` (sortable list) | — | Per-event entries (only when `population_method === 'custom'`) |
| `data_provider.custom.custom_events[].title` | `text` | — | Event title |
| `data_provider.custom.custom_events[].url` | `text` | — | Event detail URL |
| `data_provider.custom.custom_events[].calendar_date_range` | `datetime-range` (`{ from, to }`, 1970–2038, defaultTime 08:00 / 18:00) | `{ from: '', to: '' }` | Event start + end datetime |
| `template` | `short-select` (`day` / `week` / `month`) | `day` | Calendar view type |
| `first_week_day` | `short-select` (`1` Monday / `2` Sunday) | `1` | First column of the weekly grid |

### Tab: Styling

Wrapped in `group_colors` + `group_spacings` (both flatten).

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `text_color` | `sc_color_field_compact` (text) | — | Wrapper text color |
| `bg_color` | `sc_color_field_compact` (bg) | — | Wrapper background |
| `font_size_preset` | `sc_font_size_field` | — | Named size from theme presets |
| `heading_color` | `sc_color_field_compact` | — | Calendar heading (month/week title) |
| `buttons_color` | `sc_color_field_compact` | — | Prev / today / next navigation buttons |
| `spacing` | `sc_spacing_field` | — | Wrapper margin/padding |

### Tabs: Animations + Advanced

Standard.

## Rendering

`views/view.php` outputs the calendar shell, with events fed by the
selected data provider. `static/js/calendar.js` powers the grid (day /
week / month views) + navigation. `static/libs/jstimezonedetect/` is a
3rd-party library for timezone detection so events display in the
user's local time regardless of the saved timezone.

## Pitfalls

1. **`data_provider` is a multi-picker** — generators must preserve the
   nested shape. For default custom mode:
   `{ data_provider: { population_method: 'custom', custom: { custom_events: [...] } } }`.
2. **Date-range field is a structured value** — `{ from: 'YYYY-MM-DD HH:MM',
   to: '...' }` for each event. Generators producing calendar payloads
   must match this format. The range supports 1970-01-01 → 2038-01-19 (Y2K38).
3. **Default times** — when adding events programmatically without an
   explicit time, the picker defaults to `08:00` / `18:00` for start /
   end. Generators producing all-day events should set both to `00:00`
   for clarity.
4. **Third-party data providers** — when a plugin registers a provider
   via `fw_shortcode_calendar_provider`, its option shape lives in
   `data_provider.{provider_id}.…` and is opaque to this shortcode.
   Generators should default to `custom` unless they know the third-party
   provider's option shape.

## Verification

1. Drag Calendar → modal opens.
2. Add 2-3 events with date ranges → save → calendar renders with events.
3. Switch `template: month` → monthly grid.
4. Switch `first_week_day: 2` → Sunday becomes the first column.
5. Set `heading_color` + `buttons_color` → calendar chrome recolors.

## Files

- `class-fw-shortcode-calendar.php` — main class with data-provider API
- `config.php`, `options.php`, `static.php`, `views/view.php`
- `static/js/calendar.js` — grid renderer
- `static/js/scripts.js` — frontend init
- `static/libs/jstimezonedetect/jstz.js` + `jstz.min.js` — timezone
  detection
- `static/css/{styles,calendar}.css` (via static.php)
- `static/img/page_builder.png` — Layout Elements thumbnail

Custom class exists because of the data-provider extensibility API, not
because of registration hooks.
