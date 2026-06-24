---
type: shortcode
name: author_box
since: shortcodes 1.6.94
provides: leaf-shortcode
---

# Author Box

An author / profile box — avatar, name, bio, social links and a "view all posts"
link — for the current post author, a chosen user, or fully custom content. Four
designs. **Content Elements** tab.

## Sources of truth
- `views/parts/socials.php` — profile-link icon catalog (`key => {label, icon}`);
  read by options.php (network select) + view.php (rendering).
- `views/parts/registry.php` — designs (`card|centered|banner|minimal`).

## Options (atts)
- **Content**: `source` (`current|user|custom`), `user_id` (select), overrides
  `name`/`role`/`bio`/`avatar` (upload), `socials` (`addable-popup`: network + url).
- **Design**: `design`, `avatar_shape` (`circle|rounded|square`), `avatar_size`
  (slider px → `--ab-avatar`), `show_posts` (author-archive link; skipped for
  custom).
- **Styling**: `accent_color`/`card_bg`/`name_color`/`text_color` (custom hex →
  `--ab-*`), `font_size_preset`, `spacing`.

## Rendering
`view.php` (`sc_ab_render`) resolves the author: `current` → the post's
`post_author` (or a queried author archive); `user` → `user_id`; `custom` →
overrides only. Name/bio/avatar fall back to the user's data (`get_userdata`,
`get_the_author_meta('description')`, `get_avatar_url`); overrides win. Renders
`.fw-ab[--design / --avatar-*]` with avatar, name (linked to the author archive
when a real user), role, bio, socials (from the catalog; email → `mailto:`), and
the posts link. Card/banner/centered are boxed; minimal is bare; responsive stack
≤576px.

## Files
`config.php`, `options.php`, `static.php`, `views/view.php`,
`views/parts/{registry,socials}.php`, `static/css/styles.css`,
`static/img/page_builder.svg`, `static/img/design/<key>.svg`.
