---
type: shortcode
name: newsletter
since: shortcodes 1.6.95
provides: leaf-shortcode (with class file for the AJAX endpoint)
---

# Newsletter

An AJAX email-signup form, wired to the site's mail (the Mailer extension's SMTP
via `wp_mail`) and to a hook for list integrations. Three designs. **Content
Elements** tab.

## Registration
`class-fw-shortcode-newsletter.php` (`FW_Shortcode_Newsletter`) registers the
`wp_ajax(_nopriv)_fw_newsletter_subscribe` endpoint in `_init()` — which runs
every request (shortcodes are instantiated on init:11), so the endpoint exists
even when the form isn't on the page that's being submitted from.

## Security
The notification recipient is ALWAYS `get_option('admin_email')` (filter
`fw_newsletter_recipient`) — never a client-supplied address — so it can't be an
open mail relay. A nonce (`fw_newsletter`) + a honeypot field (`fw_hp`) guard the
endpoint. Integrations hook `fw_newsletter_subscribe` (or return a `WP_Error`
from `fw_newsletter_subscribe_result` to show a failure; set `fw_newsletter_handled`
true to suppress the admin email).

## Options (atts)
- **Content**: `title`, `description`, `show_name` (+ `name_placeholder`),
  `email_placeholder`, `button_label`, `consent_text`, `success_message`,
  `error_message`, `list_id` (passed to the hook).
- **Design**: `design` (`inline|stacked|boxed`), `align`, `rounded`
  (`rounded-0|rounded|pill`).
- **Styling**: `accent_color` (button), `field_bg`, `bg_color` (boxed),
  `text_color` (→ `--nl-*`), `font_size_preset`, `spacing`.

## Rendering
`view.php` (`sc_nl_render`) outputs a `<form class="fw-nl__form" data-ajax
data-nonce data-success data-error>` with the email (+ optional name) inputs, a
submit button, hidden `list`/`source`/`fw_hp` fields, optional consent text, and a
`.fw-nl__msg` live region. `scripts.js` posts via `fetch` to admin-ajax and shows
the configured success/error message (button shows a spinner while submitting).

## Files
`config.php`, `options.php`, `static.php`, `class-fw-shortcode-newsletter.php`,
`views/view.php`, `views/parts/registry.php`, `static/css/styles.css`,
`static/js/scripts.js`, `static/img/page_builder.svg`, `static/img/design/<key>.svg`.
