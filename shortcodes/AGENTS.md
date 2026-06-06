---
type: guide
name: custom-section-recipe
audience: AI agents creating new section-like shortcodes
last-verified-against: framework 2.8.40 / shortcodes 1.4.85
---

# Recipe: create a new section-like shortcode

This guide walks through creating a custom **section-like shortcode** (e.g.
`parallax_section`, `cta_section`, `pricing_section`) under
`framework/extensions/shortcodes/shortcodes/`. A section-like shortcode behaves
identically to the built-in `[section]` in the page builder — it lives at root,
holds rows, and gets the same UI treatment (controls, save-as-template, sort
sections, items corrector).

By following this recipe, the new shortcode will automatically be picked up by:

- **Section sorter** dropdown in the page-builder header
- **Save-as-Template** UI on the canvas + Templates → Sections list
- **Export / Import** (`.json` round-trip) of the saved section template
- **Items corrector** (root-level placement, `_items` recursion as a section)
- The shared **`section-like-factory.js`** editor view template
- **Full template** save / load (the section participates in any full-page
  template)

**You only need to write files inside your new `shortcodes/{your_type}/`
folder.** Do not modify any file outside it. All registration happens through
two hooks documented below in Step 2.

## Companion AGENTS.md files

Each shortcode directory under this one has its own `AGENTS.md` that documents
the concrete shape of THAT shortcode (its options/atts, rendering, pitfalls).
After you create a new shortcode, write a sibling `AGENTS.md` in its folder
following the structure described at the bottom of this guide. The worked
example is `hero_section/AGENTS.md`.

## Step 1 — folder structure

Mirror the canonical reference implementation at `hero_section/`:

```
shortcodes/{your_type}/
├── AGENTS.md                                          ← per-shortcode doc (see template at end)
├── class-fw-shortcode-{your_type}.php                 ← main shortcode class
├── config.php                                          ← shortcode config (page-builder tab/title/icon)
├── options.php                                         ← admin options shown in the section's edit modal
├── static.php                                          ← frontend CSS/JS enqueues
├── views/view.php                                      ← frontend HTML template
├── static/                                             ← (optional) frontend + editor admin assets
│   ├── css/{your_type}.css
│   ├── js/{your_type}.js
│   └── img/page_builder.svg                            ← (optional) custom icon for the Layout Elements thumbnail
└── includes/
    └── page-builder-{your_type}-item/
        ├── class-page-builder-{your_type}-item.php    ← page-builder item class
        └── static/                                     ← (optional) admin-side assets
            ├── css/styles.css
            └── js/scripts.js
```

Substitution conventions throughout this guide:

- `{your_type}` → snake_case slug (e.g. `parallax_section`)
- `{YourType}` → `Studly_Case` class-name fragment (e.g. `Parallax_Section`)
- `{Your Type Label}` → human-readable display name (e.g. `Parallax Section`)
- `{One-line description}` → short description shown in the Layout Elements
  thumbnail tooltip

## Step 2 — `class-fw-shortcode-{your_type}.php`

```php
<?php if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}

class FW_Shortcode_{YourType} extends FW_Shortcode {

    /**
     * @internal
     */
    public function _init() {
        // LAZY registration: loads the page-builder item class when the editor
        // page renders. Fires once per editor admin page.
        add_action(
            'fw_option_type_builder:page-builder:register_items',
            array( $this, '_action_register_builder_item_types' )
        );

        // EAGER registration: tells every admin-ajax handler + the items
        // corrector + any other code that asks "is this type section-like?"
        // that the answer for {your_type} is yes. CRITICAL — without this,
        // saving as a Section template, importing a section .json file, and
        // post-save items correction all silently mis-handle this type
        // because the admin-ajax request lifecycle never fires the
        // `register_items` action above. The hero_section type had exactly
        // this bug before framework 2.8.40.
        add_filter( 'fw_section_like_types', array( $this, '_filter_register_section_like' ) );

        // Expose this shortcode's data (options schema, defaults, title
        // template, l10n) to the frontend collector that the
        // `fw_ext('shortcodes')` extension uses to assemble the page-builder
        // editor's per-type data bundle.
        add_filter(
            'fw_ext:shortcodes:collect_shortcodes_data',
            array( $this, '_filter_add_data' )
        );
    }

    /**
     * @internal
     */
    public function _filter_register_section_like( $types ) {
        if ( is_array( $types ) && ! in_array( '{your_type}', $types, true ) ) {
            $types[] = '{your_type}';
        }
        return $types;
    }

    /**
     * @internal
     */
    public function _filter_add_data( $structure ) {
        $structure['{your_type}'] = $this->get_item_data();
        return $structure;
    }

    /**
     * @internal
     */
    public function _action_register_builder_item_types() {
        if ( fw_ext( 'page-builder' ) ) {
            require $this->get_declared_path(
                '/includes/page-builder-{your_type}-item/class-page-builder-{your_type}-item.php'
            );
        }
    }

    public function get_shortcode_config() {
        $config = $this->get_config( 'page_builder' );

        // Prefer a custom icon shipped with this shortcode; fall back to the
        // built-in section's icon so the thumbnail isn't blank if you haven't
        // designed one yet.
        $icon = $this->locate_path( '/static/img/page_builder.svg' );
        if ( ! $icon ) {
            $section = fw_ext( 'shortcodes' )->get_shortcode( 'section' );
            if ( $section ) {
                $icon = $section->locate_path( '/static/img/page_builder.svg' );
            }
        }
        if ( $icon && file_exists( $icon ) ) {
            $icon = file_get_contents( $icon );
        }

        return array_merge(
            array(
                'tab'            => __( 'Layout Elements', 'fw' ),
                'title'          => __( '{Your Type Label}', 'fw' ),
                'description'    => __( '{One-line description}', 'fw' ),
                'title_template' => null,
                'icon'           => $icon,
            ),
            ( is_array( $config ) ? $config : array() )
        );
    }

    public function get_item_data() {
        $data    = array();
        $options = $this->get_options();

        if ( $options ) {
            fw()->backend->enqueue_options_static( $options );
            $data['options']        = $this->transform_options( $options );
            $data['default_values'] = fw_get_options_values_from_input( $options, array() );
        }

        $config = $this->get_shortcode_config();
        if ( isset( $config['popup_size'] ) ) {
            $data['popup_size'] = $config['popup_size'];
        }
        if ( isset( $config['popup_header_elements'] ) ) {
            $data['header_elements'] = $config['popup_header_elements'];
        }

        $data['title']          = $config['title'];
        $data['title_template'] = $config['title_template'];

        $data['l10n'] = array(
            'edit'      => __( 'Edit', 'fw' ),
            'duplicate' => __( 'Duplicate', 'fw' ),
            'remove'    => __( 'Remove', 'fw' ),
            'collapse'  => __( 'Collapse', 'fw' ),
        );

        $data['tag'] = '{your_type}';

        return $data;
    }

    private function transform_options( $options ) {
        $transformed = array();
        foreach ( $options as $id => $option ) {
            if ( is_int( $id ) ) {
                $transformed[] = $option;
            } else {
                $transformed[] = array( $id => $option );
            }
        }
        return $transformed;
    }
}
```

## Step 3 — `includes/page-builder-{your_type}-item/class-page-builder-{your_type}-item.php`

```php
<?php if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}

class Page_Builder_{YourType}_Item extends Page_Builder_Section_Like_Item {

    public function get_type() {
        return '{your_type}';
    }
}

FW_Option_Type_Builder::register_item_type( 'Page_Builder_{YourType}_Item' );
```

Extending `Page_Builder_Section_Like_Item` (defined at
`framework/extensions/shortcodes/extensions/page-builder/includes/page-builder/includes/item-types/class-page-builder-section-like-item.php`)
gives you for free:

- `FW_Section_Like_Registry::register($type)` on the editor-render path
- Items-corrector opt-outs so this type isn't auto-wrapped in `[section]`
- `_items` recursion so inner rows/columns/simples still get corrected
- The shared `.column-title` slot the section-sorter reads to label rows
- Save-as-template UI on the canvas (via `section-like-factory.js` triggering
  `fw:page-builder:shortcode:section:controls`)
- Hierarchy guards (columns can land inside, simples can't land at root)

The `register_item_type()` call at file scope is fine because the action
handler `require`s this file rather than `require_once`-ing it AND the action
only fires once per editor render.

## Step 4 — `config.php` / `options.php` / `static.php` / `views/view.php`

These follow standard Unyson shortcode conventions. Use `hero_section/` as the
worked example and `shortcodes/section/` as the canonical built-in. Key points:

- **`config.php`** — returns the `$shortcode->get_config()` data. For a
  section-like shortcode this is mostly inherited from the parent class; you
  typically only need a `page_builder` sub-config block if you want to
  override the tab / title / description here instead of in
  `get_shortcode_config()`.
- **`options.php`** — defines fields shown in the section's edit modal. Each
  becomes part of the `atts` array stored on the canvas and serialized into
  the page builder JSON. **The shape of `options.php` IS the schema an AI
  generator must match when producing `atts` for this shortcode** — document
  it in the per-shortcode `AGENTS.md`.
- **`static.php`** — frontend asset enqueues. Use
  `fw_ext('shortcodes')->get_uri(...)` for URI resolution and
  `manifest->get_version()` for cache-busting. See `hero_section/static.php`.
- **`views/view.php`** — frontend HTML template. The variables `$atts` and
  `$content` are in scope. `$content` is the rendered inner rows / columns /
  simples after the items corrector and shortcode-rendering pass. Output a
  semantic outer wrapper (`<section>` or `<div>`) plus whatever shortcode-
  specific decoration you need (parallax background, etc.).

## What you DO NOT need to do

- ❌ Touch `section-like-factory.js`, `FW_Section_Like_Registry`,
  `Page_Builder_Section_Like_Item`, the section template-component, the items
  corrector, the section sorter, or the templates feature. All of them
  discover your type through the two hooks above.
- ❌ Add a save-as-template icon, a sort-sections row, an import/export
  handler, or hierarchy validation. Auto-wired.
- ❌ Modify any file outside `shortcodes/{your_type}/`.
- ❌ Register the page-builder JS or CSS — the parent class's `enqueue_static()`
  auto-locates `/includes/page-builder-{your_type}-item/static/{css,js}/...`
  if those files exist.

## Verification checklist

After deploy + hard refresh on the admin post-edit page:

1. **Layout Elements tab** shows your new section type's thumbnail with the
   configured title + icon (or the section fallback icon).
2. **Drag the thumbnail** into the canvas — drops at root, gets a
   section-styled wrapper with the `.column-title` showing your label.
3. **Hover the section** — controls bar shows: edit, duplicate, save-as-
   template (the download icon), delete, collapse.
4. **Click save-as-template** — modal opens, type a name, save. Open
   Templates → Sections; the entry appears.
5. **Drag a Text Block** into the section — drops successfully; the inner
   column / row structure auto-corrects on save.
6. **Sort Sections dropdown** — your section is listed by its display label
   (read from `.column-title`).
7. **Export → Import round-trip** — click the per-row download icon on the
   saved template, re-import the `.json`, restored intact.
8. **Frontend** — view the post on the front-end; your shortcode renders
   with its inner items.

If steps 1–8 all pass, the shortcode is wired correctly.

## Common pitfalls

1. **Forgetting the eager `fw_section_like_types` filter** — the most-bitten
   trap. Without it, your type is only registered on the editor-render
   lifecycle. Admin-ajax handlers (template save, import, items corrector
   running on `wp_insert_post`) won't see it as section-like and will reject
   or mishandle it. This bit `hero_section` before framework 2.8.40 — saving
   as a Section template returned spinner-forever because the inner-type
   strict-check failed in the ajax handler. **Add the filter.**
2. **`require` vs `require_once`** — the page-builder item file at
   `includes/page-builder-{your_type}-item/class-page-builder-{your_type}-item.php`
   is `require`d (not `require_once`d) inside the action handler. The
   file-scope `register_item_type()` call works because the action fires once
   per request. If you wire the file in from somewhere else (don't), use
   `require_once` to avoid a redeclaration fatal.
3. **Shortcode tag ≠ page-builder item type** — by default
   `Page_Builder_Section_Like_Item::get_shortcode_slug()` returns
   `$this->get_type()`. If your shortcode's tag differs from the item type
   (rare), override `get_shortcode_slug()` in the item class.
4. **Self-contained** — don't import PHP from another shortcode's directory.
   Copying patterns from `hero_section` is fine; runtime dependencies on
   another shortcode's classes create load-order coupling. If you need a
   shared helper, extract it under `framework/helpers/` or as a base class
   in `framework/extensions/shortcodes/`.
5. **The atts schema is the AI contract** — document `options.php` in the
   per-shortcode `AGENTS.md`. AI-generated page-builder JSON (the export /
   import format) must produce `atts` that match this schema. Stale docs →
   AI generates invalid atts → server rejects on import.

## Reference implementation

`hero_section/` is the canonical worked example of this recipe. Its
`AGENTS.md` is the per-shortcode counterpart to this guide and shows the
expected structure for new shortcode `AGENTS.md` files.

## Per-shortcode AGENTS.md template

When you create a new section-like shortcode, write a sibling `AGENTS.md`
in its folder following this structure:

```markdown
---
type: shortcode
name: {your_type}
since: {plugin version when added}
provides: section-like
---

# {Your Type Label}

{One-paragraph description of what this shortcode renders + what makes it
distinct from `[section]`.}

This shortcode follows the section-like recipe at `../AGENTS.md`.

## Registration

{Document the hooks in this shortcode's `_init()`. Usually identical to the
recipe — call out any deviations.}

## Options schema (atts)

{Table of options from `options.php` with type, default, description. This
is the AI contract — keep it accurate.}

| Att | Type | Default | Description |
|-----|------|---------|-------------|
| ... | ... | ... | ... |

## Rendering

{Describe what `views/view.php` outputs, the CSS classes used, any
frontend JS behavior.}

## Pitfalls

{Anything specific to this shortcode that's surprising or easy to get wrong.}

## Verification

{Quick test recipe specific to this shortcode beyond the generic checklist
in `../AGENTS.md`.}

## Files

- `class-fw-shortcode-{your_type}.php` — main shortcode class
- `config.php` — page-builder config
- `options.php` — section edit-modal fields (atts schema)
- `static.php` — frontend asset enqueues
- `views/view.php` — frontend HTML
- ... etc.
```
