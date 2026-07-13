---
type: shortcode
name: media-video
since: original Unyson (rewritten 1.11.16 — self-hosted + facade)
provides: leaf-shortcode
---

# Video (Media)

Show a video two ways, chosen by a **Video Source** picker (Content tab):

1. **Embed** — paste a public YouTube / Vimeo / oEmbed page URL; WordPress oEmbed
   turns it into a player `<iframe>`. (The original behaviour.)
2. **Self-hosted** — upload / link an MP4 (+ optional WebM); renders a real HTML5
   `<video>`. **This is the ONLY way to get a muted, looping, autoplaying
   background / hero clip** — an embed can't do that.

Page-builder tab: **Media Elements** (NOT Content Elements).

> **Never paste a raw `<video>` (or a provider iframe) into a `text_block` to show a
> video** — that bloats the DOM and undercuts the clean-markup pitch. Use this element
> in self-hosted (file) or embed (URL) mode. The Site Converter now maps a source
> `<video>` / provider `<iframe>` to this element for the same reason (see the demo
> conversion playbook).

## Registration

No custom class file — leaf shortcode auto-instantiated. No item class.

`config.php`'s `title_template` branches on the source type for the canvas preview:
self-hosted → a `<video>` (or the poster `<img>` if only a poster is set); embed →
the same YouTube / Vimeo / Dailymotion URL-parse-to-iframe as before. Both compute
`max-width` from the `width` unit-input and the aspect from `ratio`.

## Options schema (atts)

Source of truth: `options.php`. Content + Styling + Animations + Advanced.

### Tab: Content — `group_content` (flattens)

A **`source_type` multi-picker** (inline; `'label' => false` on the top level, label on
the `source` picker per the inline-multi-picker rule). Picker `source` = a `select`
(`embed` default / `self_hosted`). `choices` reveal per source:

**`embed`:**

| Att | Type | Default | Notes |
|-----|------|---------|-------|
| `url` | `text` | — | Public oEmbed page URL (frontend uses WP oEmbed → any provider) |
| `youtube_nocookie` | `switch` | `no` | Route YouTube through `youtube-nocookie.com` (no cookies until play) |
| `lazy_facade` | `switch` | `no` | Show a poster + play button; load the heavy iframe only on click |
| `poster` | `upload` (images) | — | Facade still; falls back to the YouTube thumbnail |

**`self_hosted`:**

| Att | Type | Default | Notes |
|-----|------|---------|-------|
| `video_file` | `upload` | — | MP4 (H.264, widest support) |
| `video_webm` | `upload` | — | Optional smaller WebM source (offered first) |
| `video_url` | `text` | — | External CDN file URL, used only if no upload |
| `poster` | `upload` (images) | — | Still shown before load/play |
| `autoplay` | `switch` | `no` | Forces `muted` (browsers require it) + tags `data-upw-autoplay` |
| `muted` | `switch` | `no` | — |
| `loop` | `switch` | `no` | Seamless background/hero clips |
| `controls` | `switch` | `yes` | Off for a clean background video |
| `playsinline` | `switch` | `yes` | iOS inline play (don't force fullscreen) |
| `preload` | `select` | `metadata` | `metadata` / `auto` / `none` |
| `object_fit` | `select` | `contain` | `contain` (letterbox) / `cover` (fill+crop) |

**Shared (both sources):** `width` (`unit-input`, default `{600,px}` → wrapper
`max-width`) and `ratio` (`select`: `16x9`/`4x3`/`1x1`/`21x9`/`9x16`/`3x4`).

**Saved multi-picker shape:** `source_type = { source, embed:{…}, self_hosted:{…} }`.
**Legacy** instances (a bare `url`, no `source_type`) resolve as an **embed**
automatically — the view falls back to `$atts['url']`.

### Tab: Styling — `group_colors` + `group_spacings`

`bg_color` (`sc_color_field_compact` bg) + `spacing`.

## Rendering (`views/view.php`)

- **Self-hosted:** a real `<video class="video-el">` with `<source webm>` + `<source
  mp4>` (video_url is the external fallback), the playback switches, `object-fit`, and
  `poster`. Autoplay always forces `muted`. Autoplay videos get `data-upw-autoplay="1"`.
- **Embed:** WP oEmbed (`$wp_embed->run_shortcode('[embed]…[/embed]')`). No-cookie via
  `str_replace` on the embed HTML. Lazy facade: `preg_match` the iframe `src`, render a
  `<button class="video-facade" data-video-src>` with the poster (or YouTube thumbnail)
  — the JS injects the real iframe (autoplay) on click.
- Wrapper: `sc_build_wrapper_attr()` + `video-wrapper shortcode-container mx-auto`,
  `max-width` appended (not overwriting bg), wrapped in a `.ratio.ratio-{r}` box.

## Frontend assets (`static.php` + `static/`)

`static.php` enqueues (front end only): `fw-ext-builder-frontend-grid` (the `.ratio*`
classes), `css/media-video.css` (facade styling + the wrapper-width fix), and
`js/media-video.js` (facade click-to-load + pause `data-upw-autoplay` videos on
`prefers-reduced-motion`).

## Pitfalls (READ — these bit us)

1. **`.ratio` collapse in a shrink-to-fit / centered flex column.** The `<video>` is
   `position:absolute` inside the `.ratio` box, so `.ratio` has NO in-flow content. If
   the wrapper has no definite width, it collapses to **0×0** (and `padding-top:%` of a
   0-width box is 0) — the video silently *disappears*. Fixed by `.video-wrapper {
   width:100% }` in `media-video.css`. A raw `<video>` never had this (it has intrinsic
   size); the native element does. **Never remove that rule.**
2. **The aspect var is `--fw-aspect-ratio`, NOT `--bs-aspect-ratio`.** `frontend-grid.css`
   defines `.ratio::before { padding-top: var(--fw-aspect-ratio, 56.25%) }` and
   `.ratio-1x1{--fw-aspect-ratio:100%}` etc. To fine-tune a child-theme aspect, override
   `--fw-aspect-ratio` (e.g. `105%` for a ≈0.95 near-square). `--bs-aspect-ratio` is a
   no-op here.
3. **Headless Chromium can't decode H.264 MP4** — a self-hosted `.mp4` shows
   `readyState:0 / networkState:3 / videoWidth:0` and won't play in Playwright/Puppeteer,
   even though the file is 200. The **poster still renders**, which is enough to verify
   layout. A real browser (Chrome/Edge/Safari) plays it. Don't chase this as a bug.
4. **Frontend supports all oEmbed providers; the canvas preview only parses 3**
   (YouTube/Vimeo/Dailymotion). A Wistia/Twitch URL embeds fine on the frontend but shows
   raw in the builder preview.
5. **Portrait ratios** (`9x16`, `3x4`) rely on `frontend-grid.css` defining those
   `--fw-aspect-ratio` classes (it does).

## Verification

1. Content → Video Source **Self-hosted** → pick an MP4 + poster, autoplay+loop → the
   hero clip plays muted/looping; the box is correctly sized (not 0-height).
2. Video Source **Embed** → paste a YouTube URL → iframe renders; toggle **Privacy** →
   `youtube-nocookie.com`; toggle **Lazy-load** → poster + play button, iframe loads on
   click.
3. `ratio: 9x16` → portrait; `width: 800` → wrapper max-width 800.

## Files

- `config.php` (source-branching `title_template`), `options.php`, `views/view.php`
- `static.php` + `static/css/media-video.css` + `static/js/media-video.js`
