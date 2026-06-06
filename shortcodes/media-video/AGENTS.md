---
type: shortcode
name: media-video
since: original Unyson
provides: leaf-shortcode
---

# Video (Media)

Embed a video from YouTube, Vimeo, or Dailymotion by pasting a public
URL. The shortcode auto-detects the host and converts the URL to the
correct embed `<iframe src>`. Six aspect-ratio presets (including
portrait).

Page-builder tab: **Media Elements** (NOT Content Elements).

## Registration

No custom class file — leaf shortcode auto-instantiated. No item class.

`config.php` declares a `title_template` with significant inline
JavaScript that performs the YouTube / Vimeo / Dailymotion URL parsing
and aspect-ratio math on the canvas so the preview renders the actual
embed. The same logic should exist in `views/view.php` for the frontend.

## Options schema (atts)

Source of truth: `options.php`. Two tabs + Animations + Advanced.

### Tab: Content

All Content fields are wrapped in a single flattening `group` (`group_content`),
so the group key does **not** appear in att paths.

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `url` | `text` (`dynamic_content` off) | — | Public video URL — frontend uses WP oEmbed (any provider), so far more than the three the canvas preview parses |
| `width` | `unit-input` (`px`/`%`/`vw`/`rem`/`em`) | `{value:600,unit:'px'}` | Max width. Saved as `array('value','unit')`; the view compiles it to a CSS length (`FW_Option_Type_Unit_Input::to_string`) and applies it as `max-width` on the wrapper. Legacy bare-number saves migrate to `<n>px`. Height follows `ratio` |
| `ratio` | `select` (`16x9` / `4x3` / `1x1` / `21x9` / `9x16` / `3x4`) | `16x9` | Aspect ratio. Last two are portrait orientations |

### Tab: Styling

Wrapped in `group_colors` + `group_spacings` (both flatten).

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| `bg_color` | `sc_color_field_compact` (bg) | — | Wrapper background |
| `spacing` | `sc_spacing_field` | — | Wrapper margin/padding |

### Tabs: Animations + Advanced

Standard.

## Rendering

`views/view.php` delegates to **WordPress oEmbed**
(`$wp_embed->run_shortcode('[embed]' . $url . '[/embed]')`) — it does **not**
hand-parse the URL. So the frontend embeds any provider WP's oEmbed supports
(YouTube, Vimeo, Dailymotion, TikTok, SoundCloud, Spotify, …), and WP returns the
final `<iframe>` (already sanitized; WP 5.7+ adds `loading="lazy"`). The view wraps
that in a Bootstrap responsive `.ratio.ratio-{r}` box and sets `max-width:{width}px;
margin:auto` on the wrapper. Empty / unembeddable URLs render nothing.

The wrapper carries the Styling-tab background color + spacing
(`sc_build_wrapper_attr()`); the view **appends** `max-width` to that inline style
rather than overwriting it, so a custom bg color survives (visible where padding
exists). Structural classes `video-wrapper shortcode-container mx-auto` are appended
if missing.

**Canvas preview vs frontend mismatch:** `config.php`'s `title_template` hand-parses
only YouTube / Vimeo / Dailymotion (client-side JS can't call WP oEmbed). So the
*editor preview* is limited to those three (other hosts show the raw URL), while the
*frontend* embeds the full oEmbed provider list. The preview's ID extraction strips
query strings and handles `youtu.be` / `/shorts/` / `/live/`.

## Pitfalls

1. **Frontend supports all oEmbed providers; the canvas preview only previews 3.**
   A Wistia/Twitch/etc. URL embeds fine on the frontend but shows as plain text in
   the builder preview. Self-hosted `.mp4` files are not oEmbed — paste a provider
   page URL, not a raw media file or an iframe embed snippet.
2. **`width` is max-width, not fixed** — the wrapper gets `max-width:{width}px` and
   is centered (`mx-auto`); the embed is responsive within it.
3. **Aspect ratio uses Bootstrap `.ratio`** — default Bootstrap ships
   `ratio-1x1/4x3/16x9/21x9`; the portrait `ratio-9x16` and `ratio-3x4` rely on
   theme CSS defining those `--bs-aspect-ratio` custom classes.

## Verification

1. Drag Video from Media Elements → modal opens.
2. Paste a YouTube watch URL → reload → embedded YouTube iframe renders.
3. Switch to Vimeo URL → renders as Vimeo embed.
4. Switch `ratio: 9x16` → iframe renders portrait.
5. Set `width: 800` → iframe max-width is 800px.

## Files

- `config.php` (with URL-parsing JS in `title_template`)
- `options.php`, `views/view.php`

**No `static.php`** — no frontend asset enqueue (just an iframe + inline
styles).
