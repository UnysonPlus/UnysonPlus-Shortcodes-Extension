# Carousel / Slider shortcode

A flexible, touch-friendly carousel built on **Splide** (vendored, no jQuery). Slides are
configured fields (image + heading + text + button), supporting both **hero sliders**
(full-bleed background image with overlaid text) and **multi-slide carousels** (image/logo/
card strips).

## Files

- `config.php` — page-builder registration (Content Elements tab, large popup).
- `options.php` — Slides (`addable-popup`), Layout, Behavior, Style, Animations, Advanced.
- `static.php` — enqueues Splide (vendored) + the shortcode CSS/JS.
- `views/view.php` — renders the Splide markup; **all runtime options are serialized to the
  `.splide` element's `data-splide` JSON**, which Splide reads natively on mount.
- `static/js/scripts.js` — `new Splide(el).mount()` per `.fw-carousel .splide` (no config
  here — it lives in `data-splide`), plus `governAutoplay()`, which **pauses autoplay while the
  carousel is off-screen and suppresses it entirely under `prefers-reduced-motion`**. This is a
  bandwidth rule as much as a motion one: with `lazyLoad: 'nearby'` every advance FETCHES the next
  slide, so an autoplaying hero keeps pulling full-bleed photos long after the reader has scrolled
  past it. Measured on a real site: 1.6 MB of hero slides against 260 KB for every other image on
  the page, slides still arriving 10 s in while the reader was several screens down. A/B on a tall
  test page: scrolled away, 4/4 slides (~1200 KB) before, 2/4 (~600 KB) after; staying on the hero
  is unchanged at 4/4. Degrades to the old behaviour without `IntersectionObserver`.
- `static/css/styles.css` — slide content layout, bg/overlay, neutral arrow/dot skin
  (splide-core supplies only structural CSS).
- `static/vendor/splide.min.js`, `splide-core.min.css` — Splide v4.1.4, self-hosted. The
  vendor files are **already minified** — enqueue them directly (handle `splide`), do NOT run
  them through `fw_min_uri` (it would look for `splide.min.min.*`).

## Slide model

Each slide: `image`, `image_mode` (background = text overlaid hero style / inline = image
above text), `heading`, `text`, `button_label` + `button_link`, `link` (whole-slide click when
there's no button), `content_align`. Background-mode renders the image as an absolutely
positioned `<img class="fw-carousel__bg">` (NOT an inline CSS background) so the Pages
importer can re-point `<img src>` to imported attachments and the image lazy-loads.

## Options → Splide config (view.php)

`type` = `fade` (forces perPage 1) when Transition=Fade, else `loop`/`slide` from the Loop
switch. `perPage` + `breakpoints` {992: tablet, 576: mobile}. `gap`, `height`+`fixedHeight`,
`arrows`, `pagination`, `drag`, `autoplay`+`interval`, `speed`, `pauseOnHover`, `rewind` (when
not looping; with Fade, rewind = Loop, since Splide's fade type cannot loop). Update the mapping here if you add options.

`as_background` (shared `sc-bg-fill` convention, `includes/shortcode-background-helper.php`): adds
`sc-bg-fill fw-carousel--bg` to the wrapper + `sc_section_background_use()`, forces 1 per view, no
gap / fixed height / arrows / dots / drag / pause-on-hover, and `height:'100%'` so every Splide
layer fills the Section (styles.css). `slow_zoom` adds `fw-carousel--zoom`: the active slide's
`.fw-carousel__bg` scales 1 → 1.08 over `--fwc-zoom-dur` (interval + speed). The first slide's
background image is `fetchpriority="high"` in background mode; the rest lazy-load.

## Adding it from the converter

The Site Converter detects slider sections and emits this shortcode (see
`tools/design-capture/capture-extract.mjs` `detectSlider` → `to-pages.mjs` `carouselNode`).
It reads the initialized slide elements (Swiper/Owl/Slick/Splide/BS carousel, excluding loop
clones), maps each slide → `{ image, heading, text, button }`, and picks a layout heuristic:
image-only → logo strip (multi-per-view, no arrows/dots); heading+button+image → hero
(background mode, overlay); else a 1-up content slider. **Exception:** a hero whose background
is an absolute layer (bg-box) stays a verbatim code-block so its background survives — only
"plain" slider sections become carousels.

## Accessibility

Slides are `<div class="splide__slide">` inside `<div class="splide__list">`, NOT `<ul>`/`<li>`: Splide gives each
slide `role="group"` + `aria-roledescription="slide"`, and a group is not a valid child of a list, so a `<ul>` made
the accessibility tree malformed (Lighthouse "Agentic Browsing": "ARIA role should be appropriate for the element").
In Section-background mode the whole carousel carries `aria-hidden="true"`: it is a decorative backdrop, and the
Section's own heading and text are the content.

