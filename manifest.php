<?php if (!defined('FW')) die('Forbidden');

$manifest = array();

$manifest['name']        = __( 'Shortcodes', 'fw' );
$manifest['slug']        = 'unysonplus-shortcodes';
$manifest['description'] = __( 
	'This extension adds a powerful drag & drop shortcode system. Use it to insert styled content elements anywhere on your site.', 
	'fw' 
);

$manifest['version']     = '1.6.12';
$manifest['display']     = false;
$manifest['standalone']  = true;

// Requirements
$manifest['requirements'] = array(
	'extensions' => array(
		'builder' => array(),
	),
);

// Repository Info
$manifest['github_update'] = 'UnysonPlus/UnysonPlus-Shortcodes-Extension';
$manifest['github_repo']   = 'https://github.com/UnysonPlus/UnysonPlus-Shortcodes-Extension';
$manifest['github_branch'] = 'master';

// Author Info
$manifest['author']     = 'UnysonPlus';
$manifest['author_uri'] = 'https://www.lastimosa.com.ph/unysonplus';

// Meta
$manifest['license']      = 'GPL-2.0-or-later';
$manifest['text_domain']  = 'fw';
$manifest['requires_php'] = '7.4';
$manifest['requires_wp']  = '5.8';

/**
 * Changelog
 * -----------------------------------------------------------------------------
 * 1.6.7 - Image-Content shortcode upgrades. The Layout control is now a visual
 *          image-picker and gains a new "Image Top" stacked layout (image full
 *          width above the content) alongside Left / Right. The image-vs-content
 *          split is now a drag Slider on the familiar 1-12 column scale (the handle
 *          is capped at 11 via ion.rangeSlider from_max so the content always keeps
 *          at least one column; shown as "N / 12") instead of the 11-swatch picker;
 *          the view still reads legacy "4-8" saves so existing blocks keep working. The rendered image now carries
 *          loading="lazy" + decoding="async" (parity with media_image) and a
 *          noopener rel on new-tab links, and the columns gained a base col-12 so
 *          they stack cleanly on mobile. Added Layout-tab controls: Content
 *          Alignment (a visual L/C/R image-picker via sc_alignment_field), Content
 *          Max Width (a readability measure in ch/px/etc., auto-centred when the
 *          content is centred), and a "Stack Below" breakpoint (sm/md/lg) making the
 *          side-by-side collapse point configurable (was hardcoded to md). Vertical
 *          Alignment also became a visual image-picker (top/center/bottom swatches).
 *          The Gap option now draws from the Gap Scale presets (via
 *          sc_get_gap_select_choices, with a "Use Default Gap" inherit option) so it
 *          shares one vocabulary with section/column and respects a customized scale
 *          — rendered as g-{slug} side by side and gy-{slug} on the stacked row
 *          (legacy g-4 saves still read). Added an Image Aspect Ratio (1:1 / 4:3 /
 *          3:2 / 16:9 / 3:4) for predictable cropping, and Stacked Image Max Width +
 *          Alignment so the Image-Top layout is not forced full-bleed. Added a
 *          Content Background + per-side Content Padding (the spacing composite in
 *          padding mode — all/top/right/bottom/left + responsive, from the Spacing
 *          Scale presets) so the text side can read as a tinted "card" panel, and the
 *          Layout tab is now organized into three border-less groups (arrange /
 *          align / responsive). Removed the per-instance Image Alt Text field — alt
 *          now comes solely from the Media Library (matching media_image; one source
 *          of truth), with the view's _wp_attachment_image_alt fallback unchanged.
 *          Note: a leaf shortcode has no editor-load
 *          JS hook to migrate a value-shape change, so the layout intentionally
 *          stayed an image-picker (a multi-picker would have broken existing items).
 *
 * 1.5.91 - New "Header/Footer Elements" builder tab with five navigation/site-chrome
 *          shortcodes: Navigation Menu ([nav_menu]), Site Logo ([site_logo]),
 *          Search ([site_search]), Social Icons ([social_icons]) and Menu Toggle
 *          ([menu_toggle]). These are the building blocks for authoring headers and
 *          footers with the visual page builder (the Header & Footer Builder
 *          extension), but work in normal page content too. [nav_menu] reuses the
 *          theme's `.primary-menu` / `.menu-item-has-children` markup contract so the
 *          theme's navigation JS (dropdowns, accordion, off-canvas drawer) drives
 *          builder-authored menus unchanged; [menu_toggle] emits the theme's
 *          `.menu-toggle` + `aria-controls` markup so it opens the existing drawer with
 *          no extra JS. The tab name comes from each shortcode's config.php `tab` key.
 *
 * 1.5.52 - New Shortcodes settings page (wp-admin → Unyson → Shortcodes). It lists
 *          every discovered shortcode (core + user-installed) with an enable/disable
 *          checkbox, a live search filter, Enable-all / Disable-all, an enabled/total
 *          count, and a source badge (Core / Uploaded / GitHub). Disabling is wired
 *          through the existing fw_ext_shortcodes_disable_shortcodes filter (the new
 *          FW_Extension_Shortcodes::_filter_disabled_from_settings() turns the saved
 *          "enabled_shortcodes" set into a disabled list), so a turned-off shortcode
 *          is never registered with WordPress, the page builder, or the editor data.
 *          The page can also INSTALL new shortcodes from a .zip upload or a GitHub
 *          repository URL: the archive is unzipped, validated (must contain config.php
 *          and views/view.php, sane folder name, no tag collision), and moved into a
 *          new update-safe uploads directory (wp-content/uploads/unysonplus-shortcodes/,
 *          via FW_Extension_Shortcodes::get_user_shortcodes_dir()) which the loader now
 *          scans alongside the bundled folder. User-installed shortcodes get a Delete
 *          button (core ones can only be disabled). All AJAX actions are gated by
 *          manage_options + nonce (and super-admin on multisite). New files:
 *          includes/class-fw-shortcodes-settings-page.php, static/js/admin-settings.js,
 *          static/css/admin-settings.css.
 *
 * 1.5.44 - Column: Styling → Border now uses a "Border Preset" picker. The old
 *          manual Border / Color / Width / Rounded / Shadow selects are replaced by
 *          a single dropdown sourced from the new Border Presets (Theme Settings →
 *          General → Borders) via sc_get_border_preset_choices(); the chosen preset
 *          applies a `.colb-{name}` class (with a Hover state) to the column's inner
 *          card wrapper. Columns saved with the old manual fields still render them
 *          (view.php back-compat). New helper sc_get_border_preset_choices().
 *
 * 1.5.43 - Column: Mobile Order now goes 1–12 (a row can hold twelve 1/12 columns),
 *          up from 1–5 — needs the extended .fw-order-6..12 base utilities in
 *          builder/frontend-grid.css. Mobile Order and Position are now compact
 *          "short-select" dropdowns instead of full-width selects.
 *
 * 1.5.41 - Column Layout options cleanup. Dropped the rarely-used "Width — Large
 *          (xl, ≥1200px)" override (Phone / Tablet / Desktop remain — they cover
 *          the real responsive cases; the xl token was removed from view.php too).
 *          Every Layout field's description is now a short one-liner with the
 *          detail moved to the "?" help tooltip (widths, offsets, alignment,
 *          position). Z-Index is now a proper numeric input (number option type)
 *          instead of a text field. Breakpoint tags moved out of the Tablet/Desktop
 *          labels into the help text.
 *
 * 1.5.39 - Column content alignment: fixed + tidied.
 *          • Space Between (and Middle/Bottom) now work when an Inner Wrapper is
 *            present (Inner Wrapper Class / Border / Background / Full Height). The
 *            content-align flex used to sit on the OUTER column, where the wrapper
 *            was its single flex child — so Space Between had nothing to distribute.
 *            view.php now routes the flex onto the element that directly holds the
 *            content (the inner wrapper when present, else the outer column) and
 *            adds h-100 so vertical alignment has room to work.
 *          • Content Vertical Align: dropped the redundant "Top" choice and relabeled
 *            the (no-flex) default as "Top / Default" — Default already meant top.
 *          • Added/clarified help notes on Column/Content Vertical & Horizontal Align
 *            (incl. that Horizontal Align only moves non-full-width content) and the
 *            Tablet/Desktop Offset pickers. Alignment thumbnails set to 50px (40px
 *            for Horizontal).
 *
 * 1.5.36 - Cache-busting fix: the column page-builder item's editor CSS/JS were
 *          versioned by the THEME's version, so plugin-side edits to them never
 *          busted the browser/CDN cache — stale builder scripts kept loading after
 *          a plugin update (this is why the 1.5.35 preview fix appeared to "not
 *          work"). They're now versioned by the shortcodes extension version, so
 *          plugin bumps cache-bust correctly.
 *
 * 1.5.35 - Column editor preview no longer applies CONTENT VERTICAL alignment in
 *          the canvas (it still applies on the published page). Pairs with the
 *          page-builder empty-column drop fix (page-builder 1.6.24): once an empty
 *          column fills its equalized height, a "Space Between"/"Bottom" content
 *          alignment would spread the elements apart in the editor, making the
 *          canvas awkward to edit. The editor now always stacks elements at the
 *          top (horizontal content alignment is still previewed); vertical content
 *          alignment renders on the live page as before.
 *
 * 1.5.33 - Column + Masonry editor previews are now device-aware, driven by the
 *          new builder device toggle (page-builder 1.6.19). The column item's
 *          canvas preview resolves Width/Offset for the active device
 *          (window.fwPbDevice): Phone → w_phone (else full-width), Tablet →
 *          w_tablet (else base), Desktop → w_desktop → w_tablet → base; it also
 *          re-previews on the `fw:builder:device-preview` event. The Masonry
 *          Section's editor grid switches to its Tablet/Phone column count under
 *          the `fw-device-md`/`fw-device-sm` classes. Editor-only.
 *
 * 1.5.29 - New "Masonry Section" Layout Element. A section-like shortcode
 *          (sibling to Section / Hero Section, with its own icon) that arranges
 *          its child columns in a left-to-right CSS-grid masonry: items keep
 *          source order reading across and pack vertically to fill gaps, so short
 *          columns tuck up beside taller ones (Pinterest-style) instead of leaving
 *          the gaps a normal column grid produces. Responsive column count
 *          (Desktop/Tablet/Phone) + gap in the Layout tab; column widths are
 *          uniform inside it (the grid controls width). Frontend uses CSS Grid +
 *          a small dependency-free JS that sets each item's grid-row-end from its
 *          measured height (ResizeObserver-driven, re-packs on resize/image load).
 *          The editor canvas previews the same masonry packing (the item's editor
 *          JS runs the row-span engine on the canvas, re-packing on any change via
 *          a MutationObserver). Pairs with the page-builder factory change
 *          (page-builder 1.6.18) that tags section-like items per type.
 *
 * 1.5.26 - Column: responsive layout now previews in the page-builder canvas. The
 *          width overrides, offset, column self-alignment and content alignment used
 *          to apply only on the live page; the editor canvas ignored them. The column
 *          item view now mirrors them on the canvas via inline styles (Desktop-lg
 *          preview, falling back lg → md → phone → base), updating live as options
 *          change. Inline styles avoid relying on Bootstrap/frontend-grid utilities
 *          (absent in admin); a property is only set when its option is set (resetting
 *          otherwise), so columns with no responsive settings are unchanged. Editor-only
 *          — the frontend (view.php) is untouched. Vertical alignment still needs the
 *          column to have height to show, same as on the frontend.
 *
 * 1.5.21 - Column: Width/Offset pickers collapsed into popovers (declutter). The four
 *          Width and three Offset image-pickers were a wall of thumbnails in the modal.
 *          Each is now wrapped in the new `popover` option type (framework 2.8.66): a
 *          compact field showing the current selection (e.g. "1/2 (Half)", "None") that
 *          expands the thumbnail grid only when clicked. The picker is the single inner
 *          option, so the value passes straight through — the saved data and the view
 *          (fw_akg('w_phone', …) → 'fw-col-6' etc.) are unchanged. Alignment pickers,
 *          with only 4–5 tiles, stay inline.
 *
 * 1.5.20 - Column: Width/Offset back to (crisp) image-pickers; 2x hover preview.
 *          Restored the visual SVG picker for the responsive Width (×4) and Offset
 *          (×3) fields, now drawn on the same crisp 60-unit / crispEdges grid as the
 *          builder thumbnails (the 12-cell bars no longer blur). Every column image-
 *          picker (width, offset, and the alignment glyphs) now renders its large
 *          hover preview at 2x the thumbnail height via the shared $pick() helper.
 *          Values/keys are unchanged, so the view and saved data are unaffected.
 *
 * 1.5.19 - Builder icons: crisp width thumbnails + a Section icon. The column-width
 *          SVGs are redrawn on a 60-unit grid (divisible by 2/3/4/5/6/12 so every bar
 *          lands on a whole pixel) with shape-rendering="crispEdges", fixing the blur
 *          on the many-bar icons (1/12, 5/12, 7/12). The Section element gains an SVG
 *          icon — a thick-outline 1/1 box (gray outline, #f8fdff fill) — and the
 *          section item now loads its icon by URL (<img src>) like the column
 *          thumbnails instead of inlining file contents (which relied on builder
 *          inline-icon support).
 *
 * 1.5.18 - Column: page-builder width thumbnails are now crisp SVGs. The Layout
 *          Elements column-width icons (1/1, 1/2, 1/3 … 11/12) change from PNGs to
 *          inline-styled SVGs that scale sharply on any display. Each redraws the
 *          original design — the column as one bar plus the remainder split into
 *          1/denominator bars — in the same gray. The item icon still resolves via
 *          locate_URI('/thumbnails/{key}.svg'); the old PNGs were removed.
 *
 * 1.5.17 - Column: Width/Offset back to compact dropdowns with descriptive labels.
 *          The thumbnail grids for the responsive Width (×4) and Offset (×3) fields
 *          were cluttered and their hover preview was redundant, so they return to
 *          plain select dropdowns — but each label now spells out the fraction, the
 *          common name, and the exact emitted class, e.g. "1/2 (Half) — fw-col-md-6"
 *          (per-breakpoint: fw-col- / fw-col-md- / fw-col-lg- / fw-col-xl-; offsets
 *          fw-offset[-bp]-N). The Alignment fields keep their compact SVG image-
 *          pickers. Defaults: Width = Default (inherit), Offset = None. Values are
 *          unchanged, so rendering is unaffected.
 *
 * 1.5.16 - Column: visual fraction/alignment pickers. The responsive Width (×4) and
 *          Offset (×3) selects, and the Column/Content vertical-align + Content
 *          horizontal-align selects, are now image-pickers with little SVG thumbnails
 *          — a 12-cell column bar (chosen cells highlighted, fraction baked in) for
 *          width/offset, and text-align / vertical-box glyphs for alignment. The
 *          thumbnails are generated inline as data-URI SVGs in options.php, so no
 *          asset files ship. Picker values are the same strings the view already
 *          whitelists (`default`/`none` are the unset sentinels), so rendering is
 *          unchanged. Also removed the rarely-used md/lg Order fields — Mobile Order
 *          is the only column order control now (existing saved values are ignored).
 *
 * 1.5.15 - Column: responsive layout + visual controls. The Column element gains a
 *          batch of Bootstrap-backed options. Layout tab: per-breakpoint width
 *          overrides (Phone/Tablet/Desktop/Large, layered on the width picker which
 *          stays the small/default width), offset (phone/md/lg), order at md/lg
 *          (complementing the existing mobile order), column self vertical-alignment
 *          (align-self-*), content alignment (makes the column d-flex flex-column with
 *          justify-content/align-items), and position + z-index (sticky also gets
 *          top-0). Styling tab: border (sides/color/width), rounded corners, and box
 *          shadow, which land on the inner card wrapper alongside background + spacing.
 *          All values are whitelisted in the view and map to existing theme/Bootstrap
 *          utilities (fw-col/fw-offset/fw-order in frontend-grid.css, plus Bootstrap
 *          align/position/border/shadow) — no new CSS/JS. Layout utilities go on the
 *          outer column (the flex item); visual styling on the inner wrapper. Columns
 *          left at Default emit nothing, so existing pages are unchanged.
 *
 * 1.5.14 - Column: per-column mobile ordering. A new "Mobile Order" select on the
 *          Column's Layout tab (Default / First / 1–5 / Last) lets a column be
 *          reordered on phones (< 576px) without touching its order on larger
 *          screens. The row is a flexbox grid, so the view emits `fw-order-{v}` on the
 *          outer column (the flex item) plus `fw-order-sm-0` to reset to the natural
 *          authoring order from `sm` up. Reuses the existing fw-order-* utilities in
 *          builder/static/css/frontend-grid.css — no new CSS/JS. Columns left at
 *          Default emit nothing (unchanged markup). The desktop editor canvas does not
 *          preview the mobile order.
 *
 * 1.5.13 - Image (media-image): Width & Height are now unit-inputs. Both fields gain
 *          a unit picker (px / % / vw|vh / rem / em). They apply as inline CSS so any
 *          unit scales the display; when BOTH are px the source is still cropped via
 *          fw_resize and the unitless px values populate the HTML width/height attrs
 *          (for CLS). Legacy bare-number saves are read as px, so existing images are
 *          unaffected. The canvas preview applies the dimensions via inline style.
 *
 * 1.5.12 - Video (media-video): Max Width is now a unit-input. The field changes
 *          from a plain pixel number to a number + unit picker (px / % / vw / rem /
 *          em), so a video can be capped at e.g. 80%. Saved as array('value','unit')
 *          and compiled to a CSS length applied as the wrapper's max-width; the canvas
 *          preview now derives aspect-ratio from the ratio directly (unit-independent).
 *          Legacy bare-number saves auto-migrate to "<n>px". The Content tab fields are
 *          also wrapped in a single flattening group (att paths unchanged).
 *
 * 1.5.9 - Map: Map Style is now a provider multi-picker; Content tab grouped. The
 *          OpenStreetMap "Map Style" field becomes a nested multi-picker keyed by
 *          provider (OSM, CARTO, OpenTopoMap, CyclOSM, HOT, Esri, Stadia,
 *          Thunderforest, MapTiler) — picking one reveals only that provider's style
 *          variant select and, for keyed providers, its single API-key field, instead
 *          of showing all three key fields at once. PHP resolve_osm_style() maps
 *          provider+variant back to the existing OSM_TILES id, so the front-end JS is
 *          unchanged; a legacy flat style id still resolves. The Content tab fields
 *          are also wrapped in a single flattening group (att paths unchanged).
 *
 * 1.5.8 - Map: height is now a unit-input. The Map Height field changes from a
 *          plain pixel number to a number + unit picker (px / vh / % / rem / em),
 *          so a map can be sized to e.g. 50vh. Saved as array('value','unit') and
 *          compiled to a CSS length server-side; the front-end applies it via
 *          .css('height', …) so non-pixel units work. Legacy bare-number saves are
 *          auto-migrated to "<n>px", so existing maps are unaffected.
 *
 * 1.5.7 - Map: multiple free tile styles + restructured engine picker. The "Map
 *          Engine" select becomes a multi-picker — choosing OpenStreetMap vs Google
 *          now reveals only that engine's fields. The OpenStreetMap engine gains a
 *          "Map Style" dropdown covering many providers: keyless ones (OSM Standard,
 *          CARTO Positron/Dark/Voyager, OpenTopoMap, CyclOSM, Humanitarian/HOT, and
 *          Esri World Imagery for satellite) plus key-required ones (Stadia/Stamen,
 *          Thunderforest, MapTiler) wired to three site-wide API-key fields, each
 *          with a signup link. Tile URLs/attribution live in a JS registry
 *          (OSM_TILES in static/js/scripts.js); a keyed style with no key falls back
 *          to keyless OSM Standard. All free providers require visible attribution
 *          (kept in each layer) and have fair-use limits. Google's Map Type / API
 *          Key now live under the picker's Google branch. Existing maps default to
 *          OSM Standard.
 *
 * 1.5.6 - Image (media-image): Styling & Animations tabs now actually apply. The
 *          element previously emitted a bare <img> and silently discarded its
 *          Background Color, Margin & Padding, and animation picks (there was no
 *          element to hang them on). The view now renders a wrapping <div> when —
 *          and only when — sc_needs_wrapper() reports a wrapper-affecting att is
 *          set (styling, animation, CSS id/class, custom attrs), carrying the
 *          base/unique/styling classes + inline style; the <img> keeps img-fluid.
 *          Images with no such atts still render as a plain <img> (or <a><img></a>),
 *          so existing markup is unchanged. Padding on the new wrapper is what makes
 *          a background color visible as a frame around the image.
 *
 * 1.5.4 - Map: selectable map engine. The [map] element gains a "Map Engine"
 *          option — OpenStreetMap (the new default, free, no API key or billing
 *          required) or Google Maps (still needs a key). The front-end renders
 *          with Leaflet for OpenStreetMap and the Google Maps JS API for Google;
 *          the chosen library is loaded per-element via data-map-engine, so a page
 *          only pulls in what its maps actually use. Leaflet ships from CDN
 *          (pinned to 1.9.4). The pin data shape (lat/lng/title/url/description) is
 *          unchanged, so existing maps keep working when switched between engines.
 *          Editor-side pin picking without a Google key is handled by the framework
 *          map option type (see framework changelog).
 *
 * 1.4.98 - Special Heading: overline style split into independent controls. The
 *          single "Overline Style" preset picker is replaced on the Layout tab by
 *          three composable controls — an Uppercase switch, a Marker select (None /
 *          Line / Dot / Lines-both-sides / Vertical bar) with a Leading/Trailing
 *          Position, and a Container select (None / Pill / Outline pill / Underline).
 *          This decouples the looks that were previously bundled, so combinations
 *          the presets couldn't express now work — e.g. a pill with no dot, or an
 *          underline without uppercase. New looks added: vertical-bar marker, outline
 *          pill, and trailing marker position. The pill tint / outline border still
 *          auto-derive from the Overline Color via color-mix. The retired
 *          `overline_style` preset values (including legacy kicker / kicker-rule) are
 *          still resolved by the view and mapped onto the new axes, so existing
 *          headings render unchanged. Paired with framework 2.8.59.
 *
 * 1.4.97 - Icon Box: visual layout + alignment pickers. "Icon Position" becomes an
 *          image-picker with a small diagram of each of the six layouts (icon above /
 *          inline / to the side / as a divider) instead of a text dropdown, and the
 *          Icon / Title / Content alignment selects are replaced by the shared
 *          sc_alignment_field() image-picker (Left / Center / Right + Inherit) used by
 *          Special Heading. Stored values are unchanged for the layouts; the alignment
 *          fields now save left/center/right but the view still resolves the legacy
 *          start/end values, so existing icon boxes render identically. Paired with
 *          framework 2.8.58.
 *
 * 1.4.96 - Special Heading: overline format presets. The Overline Style control
 *          becomes a visual image-picker offering curated looks beyond the original
 *          dash — a leading dot, lines flanking the label, an underline accent, and
 *          a tinted "pill / badge" container (the "● FIND YOUR MATCH" look), each
 *          with normal or uppercase case. The overline text is now wrapped in a
 *          .heading-overline__label span: the outer element is a flex row that
 *          carries alignment (justify-content) while the inner label shrink-wraps and
 *          carries the case / marker / pill, so a pill or underline hugs the text and
 *          aligns correctly. The pill background auto-tints from the Overline Color
 *          via color-mix (one colour drives the dot, text and pill), with an rgba
 *          fallback. Existing values — including legacy kicker / kicker-rule — still
 *          resolve, so saved headings are unaffected. Paired with framework 2.8.57.
 *
 * 1.4.91 - Special Heading: Layout tab + reusable alignment field. Overline Style
 *          and Alignment move out of Content into a dedicated Layout tab, which
 *          also adds per-element alignment (Overline / Title / Subtitle) on top of
 *          a master Alignment — each per-element picker defaults to "Inherit" and
 *          falls back to the master. Alignment now uses a new shared image-picker
 *          built by sc_alignment_field() (swatches in static/img/alignment/, mapped
 *          to text-* utilities by sc_alignment_class()) so other shortcodes can
 *          adopt the same control instead of hand-rolling a select. Two more layout
 *          controls land in the tab: Element Spacing (Tight / Normal / Relaxed
 *          vertical rhythm) and Heading Max Width (constrain the block to a readable
 *          measure, auto-centered when centered). Alignment is now emitted per
 *          element rather than on the wrapper. Paired with framework 2.8.51.
 *
 * 1.4.90 - Special Heading: editorial controls. The shortcode gains an optional
 *          "Overline" label rendered above the title (with Plain / Kicker /
 *          Kicker-plus-rule styling and its own color + advanced class) for the
 *          common section-label pattern (e.g. a small "FAQs" over a headline).
 *          The old Centered yes/no switch is replaced by a 3-way Alignment select
 *          (Left / Center / Right) — back-compatible: content saved with the
 *          legacy `centered:"yes"` still centers when `alignment` is unset. New
 *          typography controls decouple presentation from semantics: a Title
 *          Display Size (Bootstrap display-1..6) renders a tag like h2 visually
 *          larger without changing it, the subtitle gets its own font-size preset,
 *          and a Subtitle Max Width constrains long descriptions to a readable
 *          measure. Render order is now overline, title, subtitle. Paired with
 *          framework 2.8.50.
 *
 * 1.4.87 - Per-element Custom CSS field. Every shortcode's Advanced tab now has a
 *          "Custom CSS" code editor (shared sc_get_advanced_tab()). Authors write
 *          rules using the `selector` keyword, which resolves to the element's
 *          prefix-independent `.u{hash}` scope class — added to the wrapper only
 *          when the field is non-empty (sc_element_scope_class() in
 *          shortcode-build-helper.php, the single source of truth shared with the
 *          per-page CSS aggregator). Because the value lives in the builder JSON it
 *          travels with template export/import automatically and renders wherever
 *          the element is placed, so reusable section/column templates no longer
 *          need their CSS pasted into Theme Settings or per-page fields. The
 *          section/column export envelopes are bumped to format_version 2 to mark
 *          that styling now ships inside the template. Paired with framework 2.8.46.
 *
 * 1.4.83 - Page-Builder Templates: Section + Column import / export. Both
 *          template kinds get a per-row Export download icon and a
 *          top-of-list "Import…" button matching the Full Templates UI.
 *          On import, the inner JSON is validated against the
 *          component's shortcode type (`section` / `column`) — the same
 *          check the existing save handlers run — so a column file can't
 *          be smuggled into the Sections list and vice versa. The two
 *          new AJAX actions per component
 *          (`wp_ajax_fw_builder_templates_{section,column}_export` and
 *          `_import`) follow the existing capability-only gate these
 *          components use (no nonce — matches their save / load / delete
 *          handlers). Paired with framework 2.8.38 + builder ext 1.2.43.
 *
 * 1.4.79 - Drag-helper drift hunt round 2 (see framework 2.8.26). The
 *          1.4.78 diagnostic — disabling the three new section-sorter /
 *          section-like-factory admin assets — did NOT eliminate the
 *          cursor-vs-helper drift, so those files are not the cause.
 *          Restored their enqueues in
 *          `extensions/page-builder/includes/page-builder/class-fw-option-type-page-builder.php`.
 *          The page-builder sub-extension's `static/css/styles.css` now
 *          has its CURRENT-vs-OLD additions commented out (see
 *          page-builder 1.6.10): `border-radius: .25rem;` and
 *          `margin: .25rem;` on `.fw-option-type-builder.fw-option-type-page-builder
 *          .builder-item-type` — the latter being the prime suspect because
 *          `.builder-item-type` is the source element jQuery UI clones as
 *          the drag helper, and a non-zero margin on the source shifts the
 *          cursor-to-helper offset every time the helper crosses into a
 *          connected sortable (per-column `.builder-items` sortables).
 *
 * 1.4.78 - TEMPORARY DIAGNOSTIC for the residual page-builder drag-helper drift
 *          (see framework 2.8.25). The three admin enqueues added earlier this
 *          session in the page-builder option type
 *          (section-like-factory.js, section-sorter.js, section-sorter.css)
 *          are commented out so the user can verify whether disabling them
 *          eliminates the cursor-vs-helper drift seen when crossing columns.
 *          Sort Sections UI will be hidden from the page-builder header while
 *          this is active. The PHP registry / base classes stay loaded so
 *          saved [hero_section] markup keeps parsing. Restore in the next
 *          patch once we know which side the regression is on.
 *
 * 1.4.77 - Button Style now defaults to the first preset (Primary) and no longer
 *          offers a "None" option (allow_none => false on the picker) - a styleless
 *          button is rarely intended; use the Link preset for text-only. The default
 *          tracks whichever preset is listed first. Existing buttons saved without a
 *          style still render unchanged (bare .btn); the editor shows the picker
 *          placeholder so a style can be chosen. Needs framework 2.8.17.
 *
 * 1.4.76 - Added a Button Alignment option (Styling tab): Default / Left / Center /
 *          Right. The view wraps the button in a text-align div only when a non-
 *          default alignment is set (default and existing buttons stay wrapper-free).
 *          Inline text-align keeps it independent of Bootstrap version. No effect when
 *          Width is Full Width, as noted in the field description.
 *
 * 1.4.75 - Button shortcode Width is now a multi-picker (Select), so the Custom Width
 *          field only appears when "Custom" is chosen (previously it always showed).
 *          Saved shape is now width => [ mode => ''|w-100|custom, custom => [
 *          custom_width => {value,unit} ] ]; the view reads the nested value and keeps
 *          back-compat with older saves (flat string width + separate custom_width,
 *          and the legacy block value).
 *
 * 1.4.74 - Fixed the new Fill / Ripple / Split / Shade-sweep effects not animating
 *          (preview and live). They were built with background-image + background-size
 *          growth, which didn't render reliably on the .btn (Bootstrap drives the
 *          button background via CSS variables). Rebuilt them as translucent dark
 *          ::before overlays revealed by transform - the same proven technique as the
 *          other effects - so they now work on solid, outline AND gradient presets.
 *          The overlay sits above the background and dims the label slightly (.2) as
 *          the fill passes, which reads as the button filling.
 *
 * 1.4.73 - Added 13 more built-in button hover effects (ported from the demo set,
 *          adapted to be universal): Offset shadow, Liquid blob, Fill (slide right /
 *          slide up / from center / center vertical / diagonal), Ripple, Split wipe,
 *          Shade sweep, Border corners, Border lines-meet, and Neon glow - bringing
 *          the fixed menu to 35. Fills / ripple / split / sweep darken via a
 *          background-image overlay that sits behind the label (no markup change), so
 *          they work on solid and outline presets without assuming a color; they are
 *          intentional no-ops on gradient presets (which own background-image).
 *          Corners / lines-meet / neon follow the button's own color via currentColor;
 *          offset and blob are motion-only and work on any preset. Excluded the
 *          markup-dependent demo effects (label swap, icon slide-in/through) and
 *          gradient-shift / color-swap (they fight the preset system). Loaded from the
 *          shared hover-fx.css as before.
 *
 * 1.4.72 - Theme Settings → Buttons → Hover Animations rows now show a real primary
 *          button preview that plays the effect on hover. New admin_head emitter
 *          sc_emit_button_hover_animation_preview_css() replays each saved row's CSS
 *          with {{BTN}} -> .btnfx-preview-{id} and {{ANIM}} -> a per-id keyframes
 *          name (same scrub as the front-end generator), keyed by the box id like
 *          the Sizes preview. Needs framework 2.8.16 (which fixes the custom-animation
 *          CSS not generating).
 *
 * 1.4.71 - The Button shortcode's Hover Animation dropdown now also lists the user's
 *          Custom Hover Animations (Theme Settings to Buttons to Hover Animations) as
 *          `btnfx-c-{slug}` entries after the built-ins. sc_get_hover_animation_choices()
 *          appends them via the shared slug map, skipping empty entries; the generated
 *          CSS (from css-tokens) makes them preview in the grid and work on the front
 *          end. Needs framework 2.8.15.
 *
 * 1.4.70 - Button hover effects: fixed two that appeared broken, and added eight
 *          more. Glow pulse used a white halo that vanished on light backgrounds -
 *          it now pulses a soft two-tone halo AND a slight scale, so it reads on any
 *          background. Letter spacing did nothing when a preset set a font
 *          letter-spacing (emitted !important by css-tokens) - the hover value is
 *          now !important so it wins. New effects (all motion-only, preset-agnostic):
 *          Underline (center-out, uses the text colour), Skew, Rotate, Pop, Bounce,
 *          Float, Heartbeat, Shake - bringing the menu to 22. The disabled-button and
 *          reduce-motion guards now also cover ::after (for Underline).
 *
 * 1.4.69 - The Button shortcode's Hover Animation field now uses the dedicated
 *          `button-hover-animation` option type instead of button-style-picker — a
 *          dropdown with the effects in a 3-column grid of buttons that animate on
 *          hover. This fixes previews never animating (the shared picker's per-field
 *          effect CSS never enqueued, because option-type instances are singletons).
 *          The field passes its hover-fx.css URL via `fx_css`; the saved value is
 *          still a btnfx-* class appended to the button (view.php / hover-fx.css /
 *          static.php unchanged). Needs framework 2.8.14.
 *
 * 1.4.68 - Button Size picker previews now ride on a primary button
 *          (preview_base 'btn btn-primary'), so each size option shows a real
 *          coloured button at that size instead of a background-less .btn. Combined
 *          with framework 2.8.13, every Style/Size row now reads the preset name on
 *          a properly-styled, uniform-width preview button. Needs framework 2.8.13.
 *
 * 1.4.67 - The Button shortcode's Hover Animation field now passes its hover-fx.css
 *          URL to the picker via the new `demo_css` config, so the dropdown previews
 *          actually animate inside the page-builder options modal (previously the
 *          effect CSS only loaded with the rendered button). Needs framework 2.8.12.
 *
 * 1.4.66 - Button shortcode gained a Hover Animation field (Styling tab, under
 *          Button Size). It's a button-style-picker in demo_hover mode, so each
 *          option previews a real button that plays the effect on hover; the saved
 *          value is a CSS class (btnfx-*) appended to the button. The effects ship
 *          in a new static stylesheet (button/static/css/hover-fx.css, enqueued via
 *          static.php on front end + builder preview) and are MOTION-ONLY (transform
 *          / shadow / radius / text), so they layer over any button style — solid,
 *          outline, or gradient — without touching its colors, and the preset's own
 *          hover colors still apply. 14 effects: Lift, Grow, Shine, Glow, Ring, Long
 *          shadow, Pill morph, Letter spacing, Tilt, 3D push, Inset, Jelly, Wobble,
 *          Glitch. New sc_get_hover_animation_choices() helper supplies the list;
 *          disabled buttons and reduce-motion users get no animation. Needs
 *          framework 2.8.11 for the picker's demo_hover mode.
 *
 * 1.4.65 - Button shortcode Style and Size fields switched from plain <select>
 *          to the new `button-style-picker` option type — a dropdown that
 *          previews each choice as a real button (live colors / outline / size).
 *          Same saved value (btn-{slug}), so view.php is unchanged. The old
 *          select-tinting admin emitters (sc_emit_button_*_select_admin_css) now
 *          target selects that no longer exist and are harmless no-ops. Pairs
 *          with framework 2.8.02.
 *
 * 1.4.64 - Button Style dropdown now stores readable name-based class values
 *          (btn-primary, btn-primary-outline) instead of btn-{numeric-id}, via
 *          the shared unysonplus_button_preset_slug_map(). The option value, the
 *          generated CSS class and the dropdown color-preview all use the slug.
 *          Re-pick the Style on buttons saved before this. Pairs with framework
 *          2.7.164.
 *
 * 1.4.63 - sc_needs_wrapper(): no longer always-true. The color compact-picker
 *          atts are always a non-empty array even when unset, so the wrapper
 *          check tripped on every Text Block / Special Heading. Colors now use
 *          sc_normalize_color_value(); added an Animations-enabled check. A Text
 *          Block with no styling now renders unwrapped. Pairs with framework
 *          2.7.163.
 *
 * 1.4.62 - Text Block shortcode: restored the Styling tab (color / font size /
 *          spacing) that was removed in 1.4.57 by mistake. The view already
 *          renders unwrapped when no styling is set (sc_needs_wrapper), so the
 *          tab can stay. Pairs with framework 2.7.162.
 *
 * 1.4.61 - Button shortcode: icon centering fix that actually applies. The view
 *          adds a `has-icon` class to the button; CSS centers the icon via
 *          `.btn.has-icon` flex layout instead of a :has() selector (which
 *          no-op'd on some browsers, making the earlier 1.4.59/1.4.60 edits look
 *          ineffective). Pairs with framework 2.7.161.
 *
 * 1.4.60 - Button shortcode: icon vertical centering fix - icons inherit the
 *          button line-height (not line-height:1, which dropped them low in
 *          1.4.59) so icon + label baselines coincide. Pairs with framework
 *          2.7.160.
 *
 * 1.4.59 - Button shortcode: icons are now vertically centered cleanly. A button
 *          containing an icon lays out as an inline-flex row (align-items:center)
 *          with gap for icon/label spacing, replacing the slightly-low
 *          vertical-align approach. Pairs with framework 2.7.159.
 *
 * 1.4.58 - Button shortcode: the Styling-tab spacing field is now MARGIN only
 *          (was Margin & Padding). Its padding classes collided with the Size
 *          preset's padding on the same element; padding now belongs to the Size
 *          preset. Pairs with framework 2.7.158.
 *
 * 1.4.57 - Text Block shortcode: removed its Styling tab (color / font size /
 *          spacing). Content is styled in the WP editor; the separate styling
 *          layer was redundant. No view change (only wraps when a CSS ID/Class
 *          is set). Pairs with framework 2.7.157.
 *
 * 1.4.56 - Button shortcode: enqueue icon-v2 pack CSS on the front end when an
 *          icon is used (fixes Linecons/Entypo/Linearicons/Typicons/Unycon not
 *          showing); button stylesheet is now actually enqueued and normalizes
 *          icon sizing/line-height across packs (Dashicons no longer stuck at
 *          20px). The Style dropdown preview emitter was rewritten to read the
 *          nested button-preset states shape (so each option previews again).
 *          Pairs with framework 2.7.156.
 *
 * 1.4.55 - Button shortcode Styling tab: removed the redundant Outline Style
 *          picker and the Link/Label/Icon Color overrides (the Button Style
 *          preset now owns colors per state; outline presets are normal Style
 *          choices; icons inherit the text color). Added a Margin & Padding
 *          (spacing) field that auto-applies utility classes to the wrapper.
 *          Old saved outline/color atts are ignored gracefully. Pairs with
 *          framework 2.7.154.
 *
 * 1.4.54 - Button shortcode: replaced the binary "Full Width" switch with a
 *          "Button Width" select - Auto (fit content) / Full Width / Custom -
 *          backed by a Custom Width unit-input (px/%/rem/em/vw) emitted as an
 *          inline width. Back-compatible: buttons saved with the old `block`
 *          value still render full-width. Pairs with framework 2.7.153.
 *
 * 1.4.53 - Icon Box "Icon Badge" (Layout tab) converted from a select dropdown
 *          to an image-picker so users see an SVG thumbnail of each badge shape
 *          (None / Solid|Outline × Square|Rounded|Circle). Seven new SVGs in
 *          icon-box/static/img/badge/; choices built via
 *          fw_ext('shortcodes')->get_declared_URI(...) mirroring the
 *          image-content column-ratio picker. Choice keys/slugs are unchanged,
 *          and image-picker stores the plain slug (like select), so view.php
 *          and styles.css are untouched and existing saved boxes keep their
 *          badge. Hover tooltips carry the original text labels. Pairs with
 *          framework 2.7.138.
 *
 * 1.4.52 - Styling tab reorganized into option groups across all 24 shortcodes.
 *          Each tab_styling now wraps its colour presets + Font Size preset in
 *          a "Colors" group (`group_colors`) and its spacing field(s) in a
 *          "Spacings" group (`group_spacings`). Section's column-gap + padding
 *          controls go to the Spacings group; bg_color to Colors. The three
 *          shortcodes with non-colour/non-spacing styling fields keep them in a
 *          dedicated group: button → `group_options` (style/outline/size/
 *          block/state), image-content → `group_options` (image fit/radius/
 *          shadow), text-expander → `group_options` (toggle icon / initially
 *          open). `group` is a visual-only container in the framework
 *          (renders a `<div class="fw-backend-options-group">` with no value
 *          nesting), so all option keys remain flat — no view.php or storage
 *          changes, existing saved content renders identically. Pairs with
 *          framework 2.7.137.
 *
 * 1.4.51 - Icon Box: renamed "Icon Fill" → "Icon Badge" (the term covers both
 *          the solid and outline variants, where "fill" only fit the solid
 *          ones). Full rename across options.php (keys icon_fill → icon_badge,
 *          icon_fill_color → icon_badge_color; labels + descriptions),
 *          views/view.php (internal vars + emitted classes), and
 *          static/css/styles.css selectors (.icon-box__icon--has-fill →
 *          --has-badge, --fill-{variant} → --badge-{variant}). Choice values
 *          and the custom-icon SVG `fill` attributes are unchanged. A
 *          clearly-marked TEMP MIGRATION block in view.php falls back to the
 *          legacy icon_fill / icon_fill_color values so existing boxes keep
 *          their badge until re-saved (removable afterwards). Also updated a
 *          stale `icon_fill_color` mention in the sc_extract_styling_atts
 *          docblock (includes/shortcode-styling-helper.php) to icon_badge_color.
 *          Pairs with framework 2.7.136.
 *
 * 1.4.50 - Icon Box: added Icon / Title / Content alignment selects to the
 *          Layout tab. Each outputs a Bootstrap text utility class
 *          (text-start / text-center / text-end); the "Default" choice emits
 *          nothing so saved boxes render exactly as before. Icon alignment is
 *          only meaningful for the block layouts ("Icon above title" and
 *          "Icon between title and content"), as noted in the field help —
 *          inline / side positions lay the icon out with flexbox. View wraps
 *          the icon in a full-width `.icon-box__icon-align` block (top-title)
 *          or tags `.icon-box__divider` (between layout) so the inline-flex
 *          icon obeys text-align; title / content classes are appended to the
 *          respective elements. styles.css: removed `align-items: center` from
 *          the two block layouts (children now stretch full-width so their
 *          text-* classes win; default stays centered via inherited
 *          text-align), turned `.icon-box__divider` into a block, and added
 *          scoped `.icon-box__wrapper .text-{start,center,end}` fallbacks
 *          because the frontend grid CSS doesn't carry Bootstrap text
 *          utilities. Pairs with framework 2.7.135.
 *
 * 1.4.49 - Inlined every shortcode's Styling tab; removed the
 *          `sc_get_styling_fields()` aggregator. The aggregator bundled
 *          text_color / bg_color / font_size_preset / spacing into two
 *          `group` wrappers and only allowed per-shortcode tweaks through
 *          `skip` / `extras` / `compact_colors` args — awkward indirection
 *          for a tab that genuinely differs per shortcode. Each of the 20
 *          shortcodes that used it (text-block, code-block, widget-area,
 *          table, team-member, posts, map, media-image, media-video,
 *          column, accordion, calendar, call-to-action, divider, icon,
 *          icon-box, notification, special-heading, tabs, testimonials)
 *          now declares its Styling-tab `options` array literally in its
 *          own options.php, so the fields can be edited directly. The
 *          per-field builders (`sc_color_field_compact()`,
 *          `sc_font_size_field()`, the `'type' => 'spacing'` composite,
 *          `sc_styling_help_text()`) are kept and composed inline — they
 *          pull choices from the live palette, so expanding them would
 *          have duplicated the palette in every file.
 *          The two visual `group` wrappers (Typography & Colors / Spacing)
 *          are GONE — fields now sit flat directly under the tab. Unyson
 *          groups already flattened to top-level att keys, so saved values
 *          are unchanged and existing pages render identically; only the
 *          two section headers disappear from the editor.
 *          Also dropped the wrapper-level Text Color from `[special-heading]`
 *          (per request) — its Title Color + Subtitle Color already cover
 *          every visible text element. `sc_get_styling_fields()` itself is
 *          deleted from shortcode-styling-helper.php; `sc_styling_att_keys()`,
 *          `sc_apply_styling_classes()`, and `sc_needs_wrapper()` are
 *          untouched (filter-side, key-list driven). The 4 shortcodes that
 *          already hand-built their Styling tab (button, image-content,
 *          text-expander, section) were not affected.
 *
 * 1.4.48 - Fix: `[icon-box]` Icon Fill Color (Styling tab) now emits a
 *          utility class on the `.icon-box__icon` span for preset picks
 *          instead of an inline `style="background-color:#…"`. The
 *          previous behaviour — preserved from before the compact-
 *          picker migration — resolved every pick (preset OR custom) to
 *          a hex via `unysonplus_color_preset_slug_map()` and emitted
 *          inline style unconditionally. That looked correct in the
 *          preview but was strictly worse for theming (no `bg-{slug}` /
 *          `text-{slug}` class on the element means cascade overrides
 *          via stylesheets are impossible, and the dynamic-CSS preset
 *          machinery has nothing to hook onto). New split:
 *           - preset pick on a SOLID variant   → `bg-{slug}` class
 *           - preset pick on an OUTLINE variant → `border border-{slug}
 *             text-{slug}` classes (Bootstrap utilities — border-{slug}
 *             paints the ring, text-{slug} paints `currentColor` so the
 *             inner SVG / font-icon picks up the same tone)
 *           - custom-hex pick → inline style unchanged (no class can
 *             express an arbitrary hex):
 *               solid   → `background-color:#hex`
 *               outline → `border-color:#hex; color:#hex`
 *          Saved values unchanged. Existing icon-box instances with
 *          preset picks now render the utility class instead of the
 *          inline style — a behaviour change visible in the DOM but
 *          intentional (and the visible appearance is identical when
 *          the theme defines the utility class as expected).
 *
 * 1.4.47 - Bulk rollout of the `predefined-colors-color-picker-compact`
 *          option type to every remaining shortcode's Styling tab. After
 *          text-block (1.4.45) and icon-box (1.4.46) had proved the
 *          pattern, the rest of the catalogue follows the same recipe:
 *          a one-line `compact_colors => true` flag on
 *          `sc_get_styling_fields()` for the wrapper Text / Background
 *          Color fields, plus `sc_color_field()` → `sc_color_field_compact()`
 *          for every named per-element color in the `extras` array.
 *          Per-shortcode summary:
 *           - Options-only (no view edits): `[code-block]`, `[widget-area]`,
 *             `[table]`, `[team-member]`, `[posts]`, `[map]`, `[media-image]`,
 *             `[media-video]`, `[section]` (the inline `bg_color` field).
 *           - Options + view (also threads custom-hex inline styles onto
 *             inner elements via the new `sc_extract_styling_atts()`
 *             helper from 2.7.130): `[column]`, `[button]`, `[divider]`,
 *             `[icon]`, `[call-to-action]`, `[special-heading]`,
 *             `[notification]`, `[image-content]`, `[calendar]`, `[tabs]`,
 *             `[accordion]`, `[testimonials]`, `[text-expander]`.
 *          Saved values across every shortcode stay class-string compatible:
 *          the preset half of the new picker stores the same
 *          `text-{slug}` / `bg-{slug}` strings the legacy `<select>`
 *          produced, so existing instances render unchanged. Editors now
 *          get a custom color picker alongside the presets on every
 *          color field; the option type's `_render`/`_get_value_from_input`
 *          legacy-string back-compat shim coerces pre-migration string
 *          values into the new `{predefined, custom}` array on first load
 *          + save without manual data migration.
 *          Special cases:
 *           - `[accordion]` icon-state colors (icon_closed_color /
 *             icon_open_color) route through CSS custom properties on the
 *             wrapper. Refactored to use the picked hex directly when a
 *             custom-color is picked (skipping the `var(--color-{slug})`
 *             theme-palette lookup that only makes sense for preset slugs).
 *           - `[testimonials]` per-card colors (quote, author name, author
 *             job, site link) flow through `sc_render_card()` in
 *             `static.php` — extended to accept the new `*_color_style`
 *             args alongside the existing `*_color_class` args, both
 *             threaded into the same inner-element markup.
 *           - `[calendar]` background-color routes through scripts.js
 *             (applied to the dynamically-rendered calendar box). That
 *             pathway still only carries a class, not an inline style;
 *             custom-hex bg picks on calendar silently degrade — preset
 *             picks work as before.
 *           - `[text-expander]` Visible/Hidden colors are threaded into
 *             per-paragraph `<p>` tokens via a class-only helper; custom-
 *             hex picks for those two fields are accepted but not yet
 *             emitted onto the tokens. Preset picks continue to work
 *             unchanged. Tokenizer refactor deferred.
 *
 * 1.4.46 - `[icon-box]` Styling tab — every color field migrated to the new
 *          `predefined-colors-color-picker-compact` option type. Five
 *          fields in total: the wrapper-level Background Color (via the
 *          `compact_colors => true` flag on `sc_get_styling_fields()`),
 *          plus the four per-element pickers (`title_color`,
 *          `content_color`, `icon_color`, `icon_fill_color`) now built
 *          with `sc_color_field_compact()` directly. Editors get a
 *          compact preset dropdown + inline custom color picker on the
 *          same row for each field. Saved values stay class-string
 *          compatible — the preset half keeps the same `text-{slug}` /
 *          `bg-{slug}` shape the legacy `<select>` produced, so
 *          existing icon-box instances render unchanged.
 *          View-side updates:
 *           - Inner-element extractions (title / content / icon) now use
 *             the new `sc_extract_styling_atts()` helper, which returns
 *             both the class list AND any inline-style fragment, so a
 *             custom-hex pick actually paints the title / content / icon
 *             with an inline `color: …` style instead of silently
 *             dropping the custom half the way `sc_extract_styling_classes()`
 *             does.
 *           - `icon_fill_color` resolution refactored to accept both
 *             saved shapes (legacy string `'bg-primary'` OR new array
 *             `{predefined: 'bg-primary', custom: '#abc123'}`). When the
 *             user picks a custom hex it's used directly; otherwise the
 *             preset slug is looked up in
 *             `unysonplus_color_preset_slug_map()` as before. The
 *             resulting hex still gets emitted as inline
 *             `background-color` for solid fills / `border-color + color`
 *             for outline fills.
 *           - The icon's per-element Icon Color custom-hex (when used)
 *             and Icon Fill custom-hex (when used) are merged into a
 *             single inline `style="…"` on the icon span so we don't
 *             emit two `style` attributes on one tag.
 *
 * 1.4.45 - `[text-block]` Styling tab — Text Color and Background Color
 *          now use the new `predefined-colors-color-picker-compact`
 *          option type (compact preset dropdown + inline custom color
 *          picker) in place of the legacy plain `<select>`. Opted in
 *          via the new `compact_colors => true` flag on
 *          `sc_get_styling_fields()`. Saved values stay class-string
 *          compatible: the preset half stores `text-{slug}` /
 *          `bg-{slug}` exactly like the old select did, so existing
 *          text-block instances render unchanged. Editors get a custom
 *          color picker alongside the presets — picking a custom hex
 *          now emits an inline `style="color: …"` / `style="background:
 *          …"` on the wrapper instead of needing the Advanced tab's
 *          CSS Class field. The `sc_build_wrapper_attr` filter pathway
 *          (`sc_apply_styling_classes()`) handles both shapes via the
 *          new `sc_normalize_color_value()` helper, so the text-block
 *          view itself stays unchanged. First shortcode in the rollout
 *          — other shortcodes flip the same flag one at a time.
 *
 * 1.4.44 - Renamed `includes/shortccode-helpers.php` (double `c`) to the
 *          correctly-spelled `includes/shortcode-helpers.php`. Pure
 *          cosmetic — the file is loaded via the framework's
 *          `include_extension_directory_all_locations()` glob over
 *          `extensions/shortcodes/includes/*.php`, so no `require_once`
 *          / autoload / manifest entry referenced the typo'd filename
 *          and renaming changes zero runtime behavior. Function
 *          definitions inside (`sc_get_option()` and the deprecated
 *          `c_get_option()`) are byte-identical; every existing caller
 *          of `sc_get_option()` continues to resolve. Pairs with the
 *          framework-side bump documented in plugin manifest 2.7.128.
 *
 * 1.4.43 - `[icon-box]` gains an Icon Fill option (Layout tab) and an Icon
 *          Fill Color picker (Styling tab). Seven Icon Fill variants — None
 *          plus Solid / Outline × Square / Rounded / Circle — let editors
 *          wrap the icon in a coloured chip without resorting to a custom
 *          CSS class. The view normalises the picked colour preset slug
 *          (stripping the `bg-` prefix produced by
 *          `sc_color_field( kind=bg )`), looks up the hex via
 *          `unysonplus_color_preset_slug_map()`, and emits it as an inline
 *          style on the `.icon-box__icon` span — `background-color` for
 *          solid variants, `border-color` + `color` for outline variants
 *          (the latter so a font-icon / SVG with `stroke="currentColor"`
 *          picks up the ring tone instead of staying the default green).
 *          A new `sc_iconbox_render_icon_container()` parameter accepts a
 *          pre-built attribute fragment (with caller-side escaping) so the
 *          inline style can be threaded through without breaking the
 *          existing 3-arg call shape. Shape geometry ships in the icon-box's
 *          own static.css under `.icon-box__icon--has-fill` /
 *          `.icon-box__icon--fill-{variant}` modifier classes — no
 *          theme-side CSS needed.
 *
 * 1.4.42 - `[section]` Spacing & Style tab gains three new fields — Gap,
 *          Gap X (override), Gap Y (override) — letting editors override the
 *          site-wide Default Gap (Theme Settings → General → Spacing → Gaps)
 *          for every Bootstrap row inside that section. Fields are
 *          short-selects pulling from the new `sc_get_gap_select_choices()`
 *          helper added to `shortcode-styling-helper.php`, which reads
 *          `unysonplus_get_gap_scale()` so they stay in sync with the
 *          site-wide Gap Scale. Empty values inherit (per-axis X/Y inherit
 *          from the section's Gap; Gap inherits from Theme Settings'
 *          Default Gap; Theme Settings' Default Gap inheriting blank leaves
 *          Bootstrap's stock 1.5rem-horizontal / 0-vertical alone). The
 *          section view appends matching modifier classes
 *          (`section--gap-{slug}`, `section--gap-x-{slug}`,
 *          `section--gap-y-{slug}`) to its wrapper — the CSS rules that
 *          turn those into actual `--bs-gutter-x` / `--bs-gutter-y`
 *          overrides ship from `framework/includes/css-tokens.php`
 *          (plugin 2.7.120). The view sanitises the slug before
 *          appending so a tampered POST can't inject arbitrary class
 *          tokens.
 *
 * 1.4.41 - Pair with plugin 2.7.118's "make the spacing option type
 *          self-contained" refactor. The option type itself no longer
 *          calls into `sc_get_spacing_select_choices()` — it generates
 *          dropdown choices from its own internal scale and exposes the
 *          `fw_option_type_spacing_scale` filter as the override hook.
 *          `shortcode-styling-helper.php` now hooks that filter and
 *          returns `unysonplus_get_spacing_scale()` when available, so
 *          editing the scale in Theme Settings → General → Spacing still
 *          propagates to every Margin & Padding dropdown — but the
 *          coupling is now explicit and one-directional (helper → option
 *          type via filter), not the other way around.
 *          `sc_get_spacing_select_choices()` and `sc_spacing_field()` are
 *          unchanged — section's `padding_top` / `padding_bottom` and
 *          accordion's `item_spacing` keep using them.
 *
 * 1.4.40 - Fix `[column]` losing its spacing after the 1.4.39 / plugin-2.7.116
 *          switch to the composite `spacing` option type. Column is the only
 *          shortcode that pushes styling-tab classes onto its INNER div
 *          (the outer is the Bootstrap grid slot — only the width class
 *          belongs there), and it did that by listing the legacy flat keys
 *          in a `sc_extract_styling_classes()` call. Those keys no longer
 *          exist; the spacing now lives under one nested `$atts['spacing']`.
 *          Added `sc_extract_spacing_classes( &$atts )` to
 *          `shortcode-styling-helper.php` — a mirror of
 *          `sc_extract_styling_classes` that flattens the nested spacing
 *          att and unsets it from `$atts` so the wrapper-class filter
 *          doesn't re-apply the same classes to the outer column.
 *          `column/views/view.php` updated to use both helpers together
 *          (one for `bg_color`, one for spacing). All other shortcodes
 *          that use `sc_get_styling_fields()` were already correct — they
 *          put picks on the outer wrapper, and `sc_apply_styling_classes`
 *          already knows the nested shape.
 *
 * 1.4.39 - `sc_get_styling_fields()` now emits a single `spacing` composite
 *          option (the new option type added to the framework in plugin
 *          2.7.116) instead of two nested `group_margins` / `group_paddings`
 *          containers holding 10 individual short-select fields. The new
 *          widget renders a plus-cross layout (Margin on the left, Padding
 *          on the right, all-sides above each `+`) — the same dropdown
 *          choices and Bootstrap utility class output as before, but
 *          consolidated into one reusable control with a `mode` attribute
 *          and per-folder README. The wrapper-class filter
 *          `sc_apply_styling_classes` and `sc_needs_wrapper` learned to
 *          flatten the new nested `spacing` att, and both still honour the
 *          legacy flat keys (`margin`, `margin_top`, `padding_bottom`, …)
 *          — saved posts pre-dating this change continue to render their
 *          classes correctly. Two new helpers exposed for view-side use:
 *          `sc_flatten_spacing_value( $spacing )` returns the flat class
 *          list, and `sc_spacing_has_value( $spacing )` checks for any
 *          non-empty leaf (used by `sc_needs_wrapper` because `! empty()`
 *          on the default value tree is misleading — every slot is keyed
 *          but empty). `sc_spacing_field()` and `sc_get_spacing_select_choices()`
 *          stay in place for narrow single-slot callers (section's
 *          `padding_top` / `padding_bottom`, accordion's `item_spacing`)
 *          — those don't benefit from the plus-cross layout. The `extras`
 *          group keys `group_margins` and `group_paddings` are replaced by
 *          `group_spacing`; the legacy keys are unused by any shipping
 *          shortcode and the field-builder API documents the new key.
 *
 * 1.4.38 - `[section]` Styling tab trimmed to three fields: preset Background
 *          Color (`bg-{slug}`), Top Spacing (`pt-{n}`), Bottom Spacing
 *          (`pb-{n}`). The previous `sc_get_styling_fields()` set (text color,
 *          font size, all-sides + per-side margins and paddings) was noise
 *          for a page-level layout block — the Section Variant on the Layout
 *          tab already handles light/dark text and sections normally stack
 *          edge-to-edge without per-side margins. Tab renamed to "Spacing &
 *          Style". Field keys stay as the standard `bg_color` / `padding_top`
 *          / `padding_bottom` so saved values on existing sections continue
 *          to emit their classes via the `sc_apply_styling_classes` filter;
 *          removed-field atts (text_color, font_size_preset, margin*, padding,
 *          padding_start, padding_end) are silently ignored. The Layout tab's
 *          legacy Unyson `background_color` color-picker is untouched, with
 *          a new `help` attribute pointing editors toward Section Variant
 *          and the preset Background Color for theme-aware choices.
 *
 * 1.4.37 - `[section]` gains a Section Variant select (Default / Alt /
 *          Light / Dark) at the top of the Layout tab. Picking a non-Default
 *          option appends `section--{slug}` to the wrapper. CSS defaults
 *          ship in `section/static/css/styles.css`:
 *          alt = `#f7f7f7`, light = white bg + dark text, dark = `#1a1a1a`
 *          + light text + readable blue links. Every default is wrapped
 *          in a `var(--color-section-*, fallback)` lookup so themes can
 *          override the palette site-wide on `:root` without forking
 *          shortcode CSS. The Background Color picker still wins on top
 *          of any variant (inline style attribute beats class). Existing
 *          sections without a variant are unchanged.
 *
 *          Also restructured `section/options.php`: Layout and Bleed Layout
 *          tabs are now wrapped in `group_*` containers (matching the
 *          Advanced tab) and use modern `[]` array syntax throughout.
 *
 * 1.4.36 - `[accordion]` cascade interval tightened from 500 ms to 200 ms
 *          per item. Tighter sequential reveal that still reads as a
 *          cascade. Math: `base_delay + 0.2s × index`.
 *
 * 1.4.35 - `[accordion]` cascade fix: items were animating simultaneously
 *          because the delay was set via `--animate-delay` CSS variable
 *          which Animate.css v4 only honours through its
 *          `.animate__delay-Ns` utility classes (verified by grep against
 *          `static/css/animate.min.css`). Plain `.animate__animated`
 *          ignores the variable. Switched to setting `animation-delay`
 *          + `-webkit-animation-delay` directly inline on each
 *          `.accordion-item`, bypassing the variable entirely. Same
 *          cascade math (base + 0.5s × index), now actually applied.
 *          Also removed the "Stagger Item Reveal" switch + its pure-CSS
 *          keyframe (the cascade now happens automatically when the
 *          Animations tab is enabled, so the standalone switch was
 *          redundant).
 *
 * 1.4.34 - `[accordion]` cascades the Animations-tab effect across items
 *          automatically. When an entry animation is picked in the
 *          Animations tab, the view strips `sc-anim-pending` +
 *          `data-sc-anim` + `--animate-delay` off the wrapper and
 *          attaches them to each `.accordion-item` with a per-item
 *          `--animate-delay` of `(user delay) + 0.5s × index`. Each
 *          item triggers its own intersection-driven reveal via the
 *          shared `sc-animations.js`, producing a 500 ms cascade.
 *          The Stagger Item Reveal switch (pure-CSS reveal) is now
 *          gated to the case when no Animations effect is set — it
 *          would conflict with the cascade otherwise.
 *
 * 1.4.33 - `[column]` new "Full Height" switch in a new Layout tab.
 *          Adds Bootstrap `h-100` to the inner wrapper (consistent with
 *          1.4.32's routing) so styled cards stretch to the column's
 *          row-height — the standard equal-height-cards pattern. If
 *          Full Height is on but no other Styling pick is set, the
 *          inner div is force-created with just `h-100`.
 *
 */
