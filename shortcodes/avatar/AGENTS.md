---
type: shortcode
name: avatar
since: shortcodes (new)
provides: leaf-shortcode
---

# Avatar

A user avatar element with two modes and an image-or-initials fallback:

- **Single** — one avatar, optional status dot, optional adjacent label
  (Name + Subtitle → a "user chip").
- **Group** — an overlapping row of avatars with a configurable overlap, a
  `max_visible` cap, and a "+N" counter (auto from hidden avatars, or a manual
  social-proof label like `2K+`).

Page-builder tab: **Components**. Leaf shortcode (no class file).

## Image sizing (important)

Sizing is driven by a single CSS custom property **`--av-size`** set inline on
the root from the **Size** slider (Design tab, px). The box, initials, status
dot, ring, and group overlap all derive from it (`calc(var(--av-size) * …)`),
so one number scales the whole component.

**Server-side crop, retina-ready.** For a media-library image the view calls
`fw_resize( attachment_id, size*2, size*2, true )` — WP's image editor crops a
sharp **square at 2× the display size** and caches it on disk (so a 48px avatar
ships a 96px file, not a 2000px original). The HTML `width`/`height` are set to
the 1× size (less CLS). An **external / hotlinked URL** can't be resized by
`fw_resize` (it only handles library files) so it is used as-is and sized down
by CSS. The 2× target is capped at 512px.

This mirrors `media-image`'s `fw_resize` approach (the correct one — physical
crop + cache, not browser downscaling) and adds the 2× retina step.

## Initials fallback

No image → initials. Derived from **Name** (`"Jane Lee" → "JL"`, single word →
first 2 chars) unless an explicit **Initials** override is given. Background:

- **Auto** (default) — a stable per-name color (`crc32(name)` indexes a fixed
  12-color palette), so each person in a group gets a distinct, repeatable hue.
- **Fixed** — uses the *Initials Background* / *Initials Text* colors from the
  Style tab.

## Options schema (atts)

Source of truth: `options.php`. Tabs: **Content / Design / Style / Animations /
Advanced**.

### Content — `mode_settings` multi-picker (picker id `mode`)

Brand-new shortcode → multi-picker used freely (no legacy migration risk). The
picker reveals ONLY the chosen mode's options:

- **single**: `image` (upload), `name`, `initials`, `subtitle`, `link`,
  `target` (switch `_blank`/`_self`), `status` (none/online/away/busy/offline).
- **group**: `people` (addable-popup: `image`,`name`,`initials`,`link`,`status`),
  `max_visible` (text, `0`/empty = all), `extra_count` (text, manual counter),
  `overlap` (slider 0–80 → fraction), `stack_order` (`first-on-top`/`last-on-top`).

Saved value shape: `mode_settings = { mode:'single'|'group', single:{…}, group:{…} }`.

### Design

`design` (image-picker, registry-driven), `shape` (circle/rounded/square),
`size` (slider px), `show_status` (master toggle), `show_label` (single chip),
`initials_color_mode` (auto/theme).

### Style

`ring_color`, `initials_bg`, `initials_color`, `label_color`, `counter_bg`,
`counter_color` (all `sc_color_field_compact`), `font_size_preset`, plus
`bg_color`/`spacing` folded by `sc_build_wrapper_attr`. Compact-picker values
are resolved to a concrete CSS color by `sc_avatar_css_color()` (custom hex
wins; known preset slugs map to hex) and emitted as `--av-*` custom properties
on the root.

## Designs (registry → CSS modifier `fw-avatar--<key>`)

`views/parts/registry.php` is the single source of truth (read by `options.php`
for the picker + `view.php` for the whitelisted modifier class). All five are
**pure CSS** — no per-design CSS file, the base `styles.css` covers them:

`plain`, `bordered` (solid ring, separates group overlap), `ring` (gapped
accent ring), `shadow` (soft drop shadow), `soft` (muted initials tint).

## Rendering

`views/view.php` → root `<div class="fw-avatar-sc fw-avatar--<design>
fw-avatar--shape-<shape> fw-avatar--mode-<mode>" style="--av-size:…">`. Base
class is **`fw-avatar-sc`** (NOT `avatar`) to avoid theme `.avatar` collisions.
`sc_avatar_face()` builds each `.fw-avatar` (img or initials + status dot,
optionally wrapped in `<a>`). Group adds `.fw-avatar-group` (negative
`margin-left` overlap via `--av-overlap`, per-item inline `z-index` for stack
order, hover-lift) + a `.fw-avatar__more` counter chip.

## Files

`config.php`, `options.php`, `static.php` (enqueues `styles.css` only — no JS),
`views/view.php`, `views/parts/registry.php`, `static/css/styles.css`,
`static/img/design/{plain,bordered,ring,shadow,soft}.svg`,
`static/img/page_builder.svg`.

## Pitfalls

1. **`image` is a WP upload object** (`{attachment_id,url}`), not a URL. Only an
   `attachment_id` triggers `fw_resize`; a bare `url` (hotlink) is used raw.
2. **`size` is px on the root as `--av-size`** — everything else is relative.
   Don't hard-code avatar dimensions in CSS; use `calc(var(--av-size)*…)`.
3. **Group counter** shows when `extra_count` is set OR hidden avatars exist
   (`count - max_visible`). Numeric `extra_count` gets a `+` prefix; non-numeric
   (e.g. `2K+`) is shown verbatim.
4. Status dots need BOTH the master `show_status` switch AND a per-person
   `status` value.
