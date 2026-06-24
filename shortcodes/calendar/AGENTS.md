---
type: shortcode
name: calendar
since: shortcodes 1.7.69 (modern rewrite)
provides: leaf-shortcode
---

# Calendar

A clean, **dependency-free** events calendar: a **server-rendered month grid**
with vanilla-JS month navigation, an optional upcoming-events list, and five
designs. **Content Elements** tab.

> Rewritten from the ground up (shortcodes 1.7.68). The old implementation was a
> client-side `bootstrap-calendar` engine that pulled in **Bootstrap 3 + jQuery +
> Underscore + jstimezonedetect** (~6,600 lines, class-file shortcode with AJAX).
> All of that is **gone** — it's now a leaf shortcode with one CSS + one tiny JS
> file. No jQuery, no Underscore, no Bootstrap, no AJAX.

## Options (atts)
- **Content**: `events` (`addable-popup`) — per event: `title`, `date`
  (`date-picker`), `end_date` (optional, multi-day), `time` (display string),
  `all_day` (switch), `url`, `color` (`blue|green|amber|red|purple|teal`).
- **Design**: `design` (`classic|minimal|cards|bordered|dark`), `start_week`
  (`mon|sun`), `show_list` (upcoming list), `list_limit`.
- **Styling**: `accent_color` (→ `--cal-accent`), `text_color` (→ `--cal-text`),
  `font_size_preset`, `spacing`.

## Rendering
`view.php` (`sc_cal_render`) normalises events (each date → `Y-m-d`, multi-day
events span every day in range), then **server-renders the current month**
(`sc_cal_render_grid`, in the site timezone/locale) so the markup is crawlable
and there's no flash. The root `.fw-cal` carries `data-events` (JSON),
`data-first-week`, `data-year/-month/-today`, and localized `data-wd` / `data-mo`
name arrays. `scripts.js` is a small vanilla renderer that **mirrors
`sc_cal_render_grid`** and re-draws the grid on prev/next/today only (the initial
month stays server-rendered). Optional upcoming list (`show_list`) is also
server-rendered.

### Legacy data fallback
Calendars saved under the OLD shape (`data_provider/custom/custom_events` with a
`calendar_date_range` of timestamps) are still read by `sc_cal_events()`, so
existing calendars keep their events after the rewrite.

## Designs
CSS skins keyed by `.fw-cal--design-<key>`: classic (bordered grid), minimal
(borderless), cards (floating day cards), bordered (accent header band + strong
grid), dark.

## Pitfalls
1. The grid always opens on the **current month** (site timezone). Events outside
   it appear when the visitor navigates (or in the upcoming list).
2. Event colour tints use `color-mix` with `@supports` fallbacks.
3. On ≤640px the event chips collapse to coloured dots (titles hidden) to fit.

## Files
`config.php`, `options.php`, `static.php`, `views/view.php`,
`views/parts/registry.php`, `static/css/styles.css`, `static/js/scripts.js`,
`static/img/page_builder.svg`, `static/img/design/<key>.svg`.
