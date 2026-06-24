---
type: shortcode
name: text-block
since: original Unyson
provides: leaf-shortcode
---

# Text Block

The simplest content shortcode — a single rich-text editor (TinyMCE,
425px tall). Use this when nothing else fits: prose paragraphs, formatted
lists, embedded media via TinyMCE buttons.

## Registration

No custom class file — leaf shortcode auto-instantiated. No item class.
`static.php` enqueues `static/css/styles.css` (the layout/drop-cap classes;
text alignment uses the theme's Bootstrap `text-*` utilities, no CSS needed).

`config.php` declares a `title_template` that previews the text content
on the canvas.

## Options schema (atts)

Source of truth: `options.php`. Two tabs + Animations + Advanced.

### Tab: Content

**Not** wrapped in a group — single flat field.

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `text` | `wp-editor` (425px, TinyMCE, shortcodes enabled, wpautop on) | `''` | Body content — rich text |

### Tab: Styling

Wrapped in `group_colors` + `group_spacings` (both flatten).

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `text_color` | `sc_color_field_compact` (text) | — | Wrapper text color |
| `bg_color` | `sc_color_field_compact` (bg) | — | Wrapper background |
| `link_color` | `sc_color_field_compact` (text) | — | Link color → `--tb-link` CSS var (custom hex or resolved palette swatch), gated by a `tb-linkcolor` class so the theme link color is untouched unless one is picked |
| `font_size_preset` | `sc_font_size_field` | — | Named size from theme presets |
| `spacing` | `sc_spacing_field` | — | Wrapper margin/padding |

Plus a `group_layout` and a `group_dropcap`. Most emit CLASSES on the
`.text-block` wrapper (never inline styles on the prose); the few values that
can't be enumerated as presets (custom max-width, drop-cap size/font) emit a
targeted inline style instead:

| Att | Type | Emits | Notes |
|-----|------|-------|-------|
| `text_align` | `sc_alignment_field` (inherit/left/center/right) | `text-start` / `text-center` / `text-end` | Bootstrap utility — no shortcode CSS |
| `max_width` | **`multi-picker`** (`preset` select: full/narrow/read/medium/wide/**custom**) | preset → `tb-mw-*` class; **custom → inline `max-width:<unit-input>`** | Saved shape `{preset, custom:{custom_width:{value,unit}}}`. Custom uses a `unit-input` (px/rem/em/%/ch/vw) and forces a wrapper. Either way the block is centered. View tolerates a legacy bare-slug string |
| `columns` | `select` (1/2/3) | `tb-cols-2` / `tb-cols-3` | CSS columns; 1 col on mobile |
| `balance` | `switch` | `tb-balance` | `text-wrap: balance` |
| `line_height` | `select` (inherit/tight/snug/normal/relaxed/loose) | `tb-lh-*` | Line spacing |
| `para_spacing` | `select` (inherit/sm/md/lg) | `tb-pspace-*` | Gap between paragraphs |
| `lead` | `switch` | `tb-lead` | Enlarge the first paragraph (lead-in) |
| `link_underline` | `select` (inherit/always/hover/none) | `tb-links-*` | Link underline behavior |
| `dropcap` | **`multi-picker`** (switch picker `enabled`) | classes `tb-dropcap tb-dropcap--<style> tb-dropcap--gap-<g>` + **inline `font-size` on the cap span** | Saved shape `{enabled, yes:{dropcap_style,dropcap_font,dropcap_lines,dropcap_chars,dropcap_gap,dropcap_color}}`. `dropcap_color` (`sc_color_field_compact`) → `--fw-dropcap-accent` on the wrapper, recoloring the accent/boxed/outline caps per-block |

Drop-cap notes: `dropcap_lines` and `dropcap_chars` are **`number`** inputs
(`<input type="number">`, integer, min/max/step). The view wraps the first `dropcap_chars` LETTERS of the first
paragraph in `<span class="tb-dropcap__cap" style="--dc-lines:N;font-family:…">`
(counting across spaces, via `sc_text_block_dropcap_wrap()`). **Sizing is PURE
CSS — no JavaScript:** `styles.css` computes the cap font-size from the inline
`--dc-lines` (N) and the theme's `--fw-line` (body line-height, default 1.6; the
demos theme sets 1.65) so the cap's box spans exactly N lines:
`font-size: calc((--dc-lines × --fw-line − 0.15) / ratio × 1em)`. The per-style
`ratio` (0.85 dropped/accent, 1.03 boxed, 0.99 outline) cancels out of the line
count, so every style drops the same N; the −0.15 ε lands it a hair under N so
rounding never wraps an extra line. Outline uses an inset `box-shadow` ring (not
a fixed-px `border`) so its box stays proportional. `font-family` is the only
other inline bit (a single decorative glyph). For pixel-exact snapping on a
non-demos theme, set `--fw-line` to that theme's line-height. Style
+ distance stay classes. Styles: `dropped` (currentColor), `accent`/`outline`/`boxed`
use `var(--fw-dropcap-accent, #1a8f74)` (theme-overridable). Since `dropcap` is a
NEW multi-picker att (no legacy scalar saves ever existed), no editor-load
migrator is needed — existing text-blocks fall back to the `{enabled:'no'}` default.

### Tabs: Animations + Advanced

Standard.

## Rendering

`views/view.php` runs the `text` value through WordPress's own content
pipeline order — `do_shortcode( shortcode_unautop( wpautop( $text ) ) )` —
so blank-line paragraphs auto-format (even in the editor's Text/HTML mode)
while block-level nested shortcodes don't get wrapped in stray `<p>`. wpautop
is idempotent on the already-`<p>`-tagged HTML TinyMCE stores in Visual mode,
so existing blocks render unchanged. The wrapper is emitted only when
`sc_needs_wrapper()` (or a forced inline-style fragment) says so.

## Pitfalls

1. **Nested shortcodes work** — unlike `[code-block]` which renders raw,
   text-block's `wp-editor` content has `shortcodes: true`, so nested
   `[button]`, `[notification]`, etc. are processed. Generators can
   embed shortcode tags inside the `text` value.
2. **`wpautop` is on** — newlines become `<p>` / `<br>` automatically.
   Generators producing pre-formatted HTML should know that empty lines
   convert to paragraph breaks.
3. **GSAP-only still wraps** — a block whose only non-default setting is a
   GSAP scroll effect now forces a wrapper (the GSAP module hooks the
   `sc_needs_wrapper` filter), so the `data-upw-g*` attributes have a host
   element and the animation fires. Without it the wrapper-less block would
   silently not animate.

## Verification

1. Drag Text Block → modal opens with TinyMCE editor.
2. Type some text → reload → renders with auto-paragraphs.
3. Embed a `[button link="#" label="Click"]` shortcode in the editor →
   reload → button renders inline within the text.

## Files

- `config.php`, `options.php`, `views/view.php`, `static.php`
- `static/css/styles.css` (layout / drop-cap classes + the pure-CSS cap sizing)
- `static/img/options/dropcap-{dropped,accent,boxed,outline}.svg` (drop-cap
  style image-picker thumbnails)

No JavaScript, no item class.
