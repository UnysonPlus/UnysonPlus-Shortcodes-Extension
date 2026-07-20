<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Theme Settings → Components → Background Patterns.
 *
 * A reusable library of CSS/HTML background patterns (paste in HTML + CSS).
 * Each entry becomes a `.pattern-{name}` you can apply to a Section, Container or the site
 * background (wired in a later step). JavaScript-driven backgrounds are NOT supported here —
 * those are the Animation Engine's job; this library is CSS + HTML only.
 *
 * Built on the shared `addable-box` option type (same model as Buttons → Sizes / Animations),
 * so add / remove / reorder + storage come for free. Each row carries the pasted HTML + CSS
 * and renders a **live, isolated `<iframe>` thumbnail** (the pattern's CSS/keyframes/SVG can't
 * leak into wp-admin) via the addable-box `template`.
 *
 * @var array $options Filled with the option schema (loaded via upw_ts_get_options()).
 */

// The per-row live preview: build an isolated <iframe srcdoc> from the row's own HTML + CSS.
// _.escape() entity-encodes the markup so it rides safely inside the single-quoted srcdoc
// attribute; the iframe then decodes + renders it in full isolation from the admin page.
$preview_template = <<<'TPL'
{{= "<span class='upw-pat-thumb'><iframe sandbox='allow-same-origin' scrolling='no' srcdoc='" + _.escape("<style>html,body{margin:0;width:100%;height:100%;overflow:hidden}</style><style>" + (o.css || "") + "</style>" + (o.html || "")) + "'></iframe></span><span class='upw-pat-title'>" + _.escape(o.pattern_name || "Pattern") + "</span>" }}
TPL;

$options = array();

// The Site Background Pattern picker's natural home is the UnysonPlus theme's
// General → Layout (next to Site Background). On ANY OTHER theme, surface it here so a
// site background pattern is still available. Same stored key either way, so the plugin's
// wp_footer render (unysonplus_render_site_background_pattern) picks it up regardless.
if ( ! in_array( 'unysonplus-theme', array( get_template(), get_stylesheet() ), true ) ) {
		$options['site_background_pattern'] = array(
			'type'    => 'multi-picker',
			'label'   => __( 'Site Background Pattern', 'fw' ),
			'desc'    => __( 'Draw one of the patterns below as a fixed, full-page background behind the whole site. It shows through wherever the theme content above it is transparent.', 'fw' ),
			'popover' => true,
			'value'   => array( 'pattern' => 'none' ),
			'picker'  => array(
				'pattern' => array(
					'type'    => 'image-picker',
					'label'   => false,
					'choices' => function_exists( 'unysonplus_pattern_imagepicker_choices' ) ? unysonplus_pattern_imagepicker_choices() : array( 'none' => array( 'label' => __( 'None', 'fw' ) ) ),
				),
			),
			'choices'      => array(),
			'show_borders' => false,
	);
}

	$options['background_patterns'] = array(
		'label'           => __( 'Background Patterns', 'fw' ),
		'type'            => 'addable-box',
		'value'           => function_exists( 'unysonplus_default_pattern_presets' ) ? unysonplus_default_pattern_presets() : array(),
		'desc'            => __( 'Paste the HTML + CSS of any CSS/HTML background pattern. Each becomes a reusable <code>.pattern-{name}</code> you\'ll be able to apply to a Section, Container or the site background. <strong>JavaScript-based patterns are not supported</strong> — use the Animation Engine for those. Each collapsed row shows a live preview; expand it to edit the code.', 'fw' ),
		'sortable'        => true,
		'box-duplicate'   => true,
		// Same compact multi-column preset grid the Color / Text Styles / Spacing tabs use
		// (`.fw-preset-2col` is a 3-column grid in theme-settings-presets.css). Rows collapse
		// to their preview + name by default (addable-box adds `closed`); expand to edit.
		'attr'            => array( 'class' => 'fw-preset-2col' ),
		'width'           => 'full',
		'add-button-text' => __( 'Add Pattern', 'fw' ),
		'box-options'     => array(
			'id'           => array( 'type' => 'unique' ),
			'pattern_name' => array(
				'label'           => __( 'Pattern Name', 'fw' ),
				'type'            => 'text',
				'value'           => '',
				'desc'            => __( 'Becomes the class suffix, e.g. Neon → <code>.pattern-neon</code>.', 'fw' ),
				'dynamic_content' => false,
			),
			'root_class'   => array(
				'label'           => __( 'Root Class (optional)', 'fw' ),
				'type'            => 'text',
				'value'           => '',
				'desc'            => __( 'The outermost class in your pasted HTML (e.g. <code>encrypted-neon-pattern</code>). Leave blank — Unyson+ auto-detects it when it scopes the pattern.', 'fw' ),
				'dynamic_content' => false,
			),
			'html'         => array(
				'label'       => __( 'HTML', 'fw' ),
				'type'        => 'code-editor',
				'mode'        => 'htmlmixed',
				'height'      => 150,
				'placeholder' => "<div class=\"my-pattern\">\n  <!-- pattern markup -->\n</div>",
				'desc'        => __( 'The pattern markup. Any inline <code>&lt;svg&gt;&lt;filter&gt;</code> can stay here — it is extracted and injected globally at render time.', 'fw' ),
			),
			'css'          => array(
				'label'       => __( 'CSS', 'fw' ),
				'type'        => 'code-editor',
				'mode'        => 'css',
				'height'      => 220,
				'placeholder' => ".my-pattern {\n  /* pattern styles */\n}",
				'desc'        => __( 'The pattern CSS. Keep the class names exactly as pasted — Unyson+ scopes them to this preset (and namespaces its <code>@keyframes</code>) automatically.', 'fw' ),
			),
		),
		'template'        => $preview_template,
);
