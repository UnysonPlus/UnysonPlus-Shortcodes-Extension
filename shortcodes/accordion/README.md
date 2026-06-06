<!-- last-updated: 2026-05-13 plugin-v2.7.54 -->

# Accordion shortcode

Collapsible content panels with a click-to-toggle header. Designed for FAQs, step-by-step guides, and grouped info sections.

## File map

```
accordion/
├── config.php              Page Builder registration + preview template
├── options.php             Admin form (4 tabs: Content, Layout, Behaviour, Advanced)
├── views/
│   └── view.php            Front-end renderer + numbering helpers
├── static.php              Enqueues styles.css + scripts.js
└── static/
    ├── css/styles.css      All visual styling (CSS-drawn icons + custom-icon hooks)
    ├── js/scripts.js       Click + keyboard handlers, slide animations
    └── img/                Builder palette icon + legacy plus/minus PNGs
```

---

## Overview

Use this shortcode whenever content is naturally split into **labelled sections that should start collapsed** (or partially collapsed). Common cases:

- FAQ pages (with Q1/Q2/Q3 numbering)
- Step-by-step guides (with `Step {n}` numbering)
- Pricing breakdowns / spec sheets
- Long-form articles where supporting detail can be hidden by default

The shortcode supports four visual styles for the toggle indicator (plus a "no icon" mode and a "Custom" escape hatch), optional item numbering with six placeholder tokens, single-open or multi-open behaviour, and keyboard accessibility out of the box.

---

## Admin walkthrough

### Content tab

| Field | Storage key | Type | What it does |
|---|---|---|---|
| **Tabs** | `tabs` | `addable-popup` | The accordion's items. Each item has a **Title** (`tab_title`, `text`) and **Content** (`tab_content`, `wp-editor`). Items are reorderable. No hard limit. |

The Page Builder canvas shows a stacked preview of each tab's title (bold) + content via the [config.php](config.php) `title_template`.

### Layout tab

| Field | Storage key | Type | Default | What it does |
|---|---|---|---|---|
| **Icon Style** | `icon_style` | `select` | `plus-minus` | Toggle indicator visual. Choices: `plus-minus` (+ / −), `plus-x` (+ / ×), `chevron` (›), `arrow` (▶), `none`, `custom`. |
| **Icon Position** | `icon_position` | `select` | `left` | Icon sits before (`left`) or after (`right`) the title text. |
| **Custom Closed-State Image** | `icon_closed_image` | `upload` | — | (Used only when Icon Style = Custom.) PNG / JPG / SVG. Overrides Closed-State Text. |
| **Custom Open-State Image** | `icon_open_image` | `upload` | — | (Used only when Icon Style = Custom.) PNG / JPG / SVG. Overrides Open-State Text. |
| **Custom Closed-State Text** | `icon_closed_text` | `short-text` | `+` | (Used only when Icon Style = Custom and no closed image uploaded.) Glyph or emoji shown when a panel is collapsed. Examples: `+` `▼` `▶` `👇` |
| **Custom Open-State Text** | `icon_open_text` | `short-text` | `−` | (Used only when Icon Style = Custom and no open image uploaded.) Glyph or emoji shown when a panel is expanded. Examples: `−` `▲` `▼` `👆` |
| **Item Numbering** | `numbering` / `numbering.style` | `multi-picker` | `none` | Prefix each title with a label. Choices: `none`, `decimal` (1, 2, 3), `decimal-leading-zero` (01, 02, 03), `lower-alpha` (a, b, c), `upper-alpha` (A, B, C), `lower-roman` (i, ii, iii), `upper-roman` (I, II, III), `q-prefix` (Q1, Q2, Q3), `custom`. Selecting `custom` reveals a Custom Template input. |
| ↳ **Custom Template** | `numbering.custom.template` | `text` | `Q{n}` | (Inside `custom`.) Template string with placeholder tokens — see [Numbering token reference](#numbering-token-reference) below. |
| **Start Number** | `numbering_start` | `short-text` | `1` | The number assigned to the first item. Use any integer to start elsewhere (e.g. `5` → first item becomes `Q5` / `e.` / `V`). Affects all numbering styles. |

### Behaviour tab

| Field | Storage key | Type | Default | What it does |
|---|---|---|---|---|
| **Initially Open** | `initially_open` | `select` | `first` | Which panel(s) start expanded on page load. Choices: `first`, `none`, `all`. (When `all` is paired with `multiple_open=no`, the JS auto-normalises to "first only" — see [Edge cases](#edge-cases--known-limits).) |
| **Collapsible** | `collapsible` | `switch` | `no` | When **yes**, the currently-open panel can be closed by clicking its header (without one having to remain open). When **no** (and `multiple_open=no`), at least one panel always stays open. |
| **Multiple Open** | `multiple_open` | `switch` | `no` | When **yes**, multiple panels can be expanded at once. When **no**, opening one closes the others. |

### Advanced tab

Inherits the shared advanced fields via `sc_get_advanced_tab()` — typically custom CSS class / ID, margin / padding, and entrance-animation controls. Whatever that helper returns is what shows here.

---

## Numbering token reference

When **Item Numbering = `custom`**, the Custom Template field accepts these tokens. They're rendered per item, with the item number computed as `Start Number + (item index)`.

| Token | Renders | Example with `Start = 1` | Example with `Start = 27` |
|---|---|---|---|
| `{n}` | Decimal integer | `1, 2, 3, …` | `27, 28, 29, …` |
| `{0n}` | Two-digit zero-padded for single digits | `01, 02, 03, …` | `27, 28, 29, …` |
| `{a}` | Lower alpha (Excel-style past `z`) | `a, b, c, …` | `aa, ab, ac, …` |
| `{A}` | Upper alpha (Excel-style past `Z`) | `A, B, C, …` | `AA, AB, AC, …` |
| `{i}` | Lower Roman | `i, ii, iii, …` | `xxvii, xxviii, …` |
| `{I}` | Upper Roman | `I, II, III, …` | `XXVII, XXVIII, …` |

Common template recipes: `Q{n}` → "Q1", `Step {n}` → "Step 1", `Section {I}.` → "Section I.", `Item {0n}` → "Item 01".

---

## Rendered HTML

```html
<div id="accordion-<uniqid>"
     class="accordion ac-<unique_id>
            accordion-icon-<style>           <!-- plus-minus | plus-x | chevron | arrow | none | custom -->
            accordion-icon-<position>        <!-- left | right -->
            accordion-has-numbering"         <!-- only when Item Numbering != none -->
     role="tablist"
     aria-multiselectable="true|false"
     data-icon-style="…"
     data-icon-position="…"
     data-initially-open="first|none|all"
     data-collapsible="true|false"
     data-multiple-open="true|false">

  <!-- per item: title + content wrapped in an .accordion-item div.
       The wrapper is what makes per-item spacing / styling possible and
       lets the JS resolve a clicked title's panel via .closest('.accordion-item'). -->
  <div class="accordion-item [mb-3]"> <!-- mb-* class from the Item Spacing option, on all items except the last -->

    <{h2|h3|h4|h5|h6} class="accordion-title [ui-state-active]"
        id="accordion-<id>-header-<i>"
        role="tab"
        aria-controls="accordion-<id>-panel-<i>"
        aria-expanded="true|false"
        tabindex="0">
      <!-- tag chosen via the new Layout-tab "Title Tag" select. Default h3. -->

      <span class="accordion-icon" aria-hidden="true">
        <!-- icon body is empty for plus-minus / plus-x / chevron / arrow / none
             (those are drawn purely by CSS pseudo-elements on .accordion-icon).
             For icon_style=custom, two child spans are emitted instead: -->
        <span class="accordion-icon-state-closed">
          <img src="…"> | …closed text/emoji…
        </span>
        <span class="accordion-icon-state-open">
          <img src="…"> | …open text/emoji…
        </span>
      </span>

      <!-- only when Item Numbering != none -->
      <span class="accordion-number" aria-hidden="true">Q3</span>

      <span class="accordion-title-text">Tab title text</span>
    </{h2|h3|h4|h5|h6}>

    <div class="accordion-content"
         id="accordion-<id>-panel-<i>"
         role="tabpanel"
         aria-labelledby="accordion-<id>-header-<i>"
         aria-hidden="true|false"
         style="display:none|block;">
      Tab content (run through do_shortcode())
    </div>

  </div>

</div>
```

> **Breaking change in 2.7.95:** title and content used to be **siblings** under `.accordion` (no `.accordion-item` wrapper). Custom CSS that relied on adjacent-sibling selectors like `.accordion-title + .accordion-content` will no longer match. Rewrite as descendant selectors under `.accordion-item`, e.g. `.accordion-item .accordion-content`.

DOM source order inside `<h3>` is always **icon → number → title-text**. Flex `order` rules on `.accordion-icon-left .accordion-icon` (`order: -1`) and `.accordion-icon-right .accordion-icon` (`order: 1`) shuffle the icon to either end. The number always stays glued to the title text.

---

## CSS class catalogue

Every selector in [`static/css/styles.css`](static/css/styles.css), grouped by role:

**Layout primitives**
- `.accordion` — wrapper, border, container reset
- `.accordion .accordion-item` — per-item grouping (one title + one panel). Transparent by default; receives the Item Spacing `mb-*` class on every item except the last
- `.accordion .accordion-item:first-child .accordion-title` — strips the top border on the first header
- `.accordion .accordion-title` — header, flex row, padding, cursor, bold
- `.accordion .accordion-title:focus` — focus outline for keyboard nav
- `.accordion .accordion-title.ui-state-active` — open state (removes header background)
- `.accordion .accordion-title-text` — flex-grow text label
- `.accordion .accordion-content` — panel padding
- `.accordion .accordion-content p:last-child` — kills the trailing `<p>` bottom margin

**Icon base + positioning**
- `.accordion .accordion-icon` — 16×16 inline-flex container (relaxed for custom)
- `.accordion-icon-none .accordion-icon` — hides the icon entirely
- `.accordion-icon-left .accordion-icon` — `order: -1`, margin-right
- `.accordion-icon-right .accordion-icon` — `order: 1`, margin-left

**Built-in icon styles** (CSS-drawn via `::before` / `::after`)
- `.accordion-icon-plus-minus .accordion-icon::before / ::after` — horizontal + vertical bars
- `.accordion-icon-plus-minus .ui-state-active .accordion-icon::after` — vertical bar rotates 90° and fades → minus
- `.accordion-icon-plus-x .accordion-icon::before / ::after` — same bars
- `.accordion-icon-plus-x .ui-state-active .accordion-icon::before / ::after` — both bars rotate 45° → ×
- `.accordion-icon-chevron .accordion-icon::before` — borders + rotate forming a `›`, flips up on active
- `.accordion-icon-arrow .accordion-icon::before` — CSS triangle, rotates 90° on active

**Custom icon hooks** (only present when `icon_style=custom`)
- `.accordion-icon-custom .accordion-icon` — relaxes the 16×16 box, lets emoji/images breathe
- `.accordion-icon-custom .accordion-icon-state-closed` — closed-state span (visible by default)
- `.accordion-icon-custom .accordion-icon-state-open` — open-state span (hidden by default)
- `.accordion-icon-custom .ui-state-active .accordion-icon-state-closed` — hidden when active
- `.accordion-icon-custom .ui-state-active .accordion-icon-state-open` — visible when active
- `.accordion-icon-custom img` — caps uploaded images at `max-height: 24px`, preserves aspect

**Numbering**
- `.accordion .accordion-number` — number chip: inline-flex, `min-width: 1.5em`, `margin-right: 12px`, `font-weight: 600`, `opacity: 0.7`, `color: inherit` (theme overrides naturally)
- `.accordion-has-numbering` (wrapper-level) — set when Item Numbering ≠ none, available for theme-side targeting

---

## JavaScript behaviour

Implementation in [`static/js/scripts.js`](static/js/scripts.js). One `jQuery(document).ready` block that binds per-`.accordion`-instance handlers.

**Bindings**
- `click` and `keydown` on `.accordion-title`
- Keyboard: accepts **Enter (13)** and **Space (32)** only — matches the `tabindex="0"` + `role="tab"` markup

**State source of truth**
- Read from wrapper `data-*` attributes: `data-multiple-open`, `data-collapsible`, `data-initially-open`

**Animation**
- `slideToggle(200)` / `slideDown(200)` / `slideUp(200)` (jQuery 200 ms slide). When the open/closed visual is a CSS-drawn icon, an additional `transition: transform 0.2s ease` runs on the pseudo-element.

**Open/close logic**
- *Single-open mode* (`multiple_open = no`): clicking an open header returns silently if `collapsible = no`; otherwise closes the panel. Clicking a closed header closes any currently-open one first, then opens the new one.
- *Multi-open mode* (`multiple_open = yes`): each header toggles independently. Guard prevents closing the *last* open panel when `collapsible = no` (`openCount <= 1` short-circuit).

**Accessibility sync**
- Toggling flips `.ui-state-active` on the header and keeps `aria-expanded` (on the header) + `aria-hidden` (on the panel) in lock-step.

**Open-on-load normalisation**
- If `data-initially-open="all"` but `data-multiple-open=false`, the JS closes everything except the first item on load (defensive, since the combo is self-conflicting).

---

## Examples

### 1. Basic FAQ — Q1 / Q2 / Q3, default icon

| Field | Value |
|---|---|
| Icon Style | `plus-minus` |
| Item Numbering → style | `q-prefix` |
| Start Number | `1` |
| Tabs | 3 items: "How do I claim?", "When do I withdraw?", "Is this regulated?" |

Renders:

```html
<div class="accordion ac-… accordion-icon-plus-minus accordion-icon-left accordion-has-numbering" role="tablist" aria-multiselectable="false">
  <h3 class="accordion-title ui-state-active" …>
    <span class="accordion-icon" aria-hidden="true"></span>
    <span class="accordion-number" aria-hidden="true">Q1</span>
    <span class="accordion-title-text">How do I claim?</span>
  </h3>
  …
</div>
```

### 2. Step-by-step — Custom emoji icons, "Step N" numbering, all closed on load

| Field | Value |
|---|---|
| Icon Style | `custom` |
| Custom Closed-State Text | `👇` |
| Custom Open-State Text | `👆` |
| Item Numbering → style | `custom` |
| Item Numbering → custom template | `Step {n}` |
| Start Number | `1` |
| Initially Open | `none` |

Renders (header for item 0):

```html
<h3 class="accordion-title" …>
  <span class="accordion-icon" aria-hidden="true">
    <span class="accordion-icon-state-closed">👇</span>
    <span class="accordion-icon-state-open">👆</span>
  </span>
  <span class="accordion-number" aria-hidden="true">Step 1</span>
  <span class="accordion-title-text">Pour the dry ingredients</span>
</h3>
```

### 3. Multi-open browse list — no numbering, multiple panels can be open

| Field | Value |
|---|---|
| Icon Style | `chevron` |
| Item Numbering → style | `none` |
| Multiple Open | `yes` |
| Collapsible | `yes` |
| Initially Open | `none` |

Renders a wrapper with `data-multiple-open="true"` and `aria-multiselectable="true"`. No `.accordion-number` spans. Headers can all be opened or all closed.

---

## Edge cases & known limits

- **`initially_open="all"` + `multiple_open="no"` is self-conflicting.** JS auto-normalises by closing everything except the first item on load. Set `multiple_open=yes` if you actually want all panels open at once.
- **Alpha numbering past Z.** Uses Excel-style: `a, b, …, z, aa, ab, …`. Same for upper-alpha (`A` … `Z`, `AA`, `AB` …). Implemented in [`fw_sc_accordion_int_to_alpha()`](views/view.php) in view.php.
- **Roman numerals < 1.** Roman has no zero or negative form, so the helpers clamp `n < 1` to `1` for alpha and Roman styles. Decimal styles accept any integer.
- **Custom icon with cleared text + no image.** Defensive defaults kick in: `+` and `−`. Prevents an empty `<span class="accordion-icon">` from rendering blank.
- **Image-icon height cap.** Uploaded images max-height at `24px` (preserves aspect via `width: auto`). Override in your theme if you need bigger.
- **SVG uploads.** Render inline if the host allows SVG uploads. If the SVG uses `fill="currentColor"`, it inherits the title-text colour (so it flips with theme dark mode automatically).
- **`tab_title` is `esc_html()`-escaped in view.php.** HTML inside titles renders as text, not markup. By design — keep titles to plain text.
- **Number is `aria-hidden="true"`.** Screen readers don't double-announce "Q-three Why focus…" — the title text is the actual content; the number is decoration.
- **Role choice.** Uses the WAI-ARIA *tabs* pattern (`role="tablist"` / `role="tab"` / `role="tabpanel"`). Modern ARIA-APG also accepts the *accordion* pattern (`role="button"` + `aria-controls`). Both are used in the wild; this one is unchanged for backward compatibility.

---

## Changelog

- **v2.7.54 (2026-05-13)** — Added `plus-x` icon style. Added Item Numbering field with eight built-in choices (`decimal`, `decimal-leading-zero`, `lower-alpha`, `upper-alpha`, `lower-roman`, `upper-roman`, `q-prefix`, `custom`) and a custom-template path supporting six placeholder tokens (`{n}`, `{0n}`, `{a}`, `{A}`, `{i}`, `{I}`). Added Start Number option (decimal default 1, any integer). Added `custom` icon style with per-state image upload + per-state text/emoji fallback (image wins when both supplied; `+` / `−` defensive defaults when neither). Added `accordion-has-numbering` wrapper class for theme-side targeting. Backward-compatible — pre-update accordions render byte-identical to before.
- **v2.7.x (earlier)** — Initial release with `plus-minus`, `chevron`, `arrow`, `none` icon styles; `left` / `right` icon position; `tabs` addable-popup content; behaviour switches `initially_open`, `collapsible`, `multiple_open`; jQuery-driven click + keyboard handlers; `role="tablist"` accessibility plumbing.
