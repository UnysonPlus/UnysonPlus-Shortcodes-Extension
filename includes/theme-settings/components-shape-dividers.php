<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Theme Settings → Components → Shape Dividers.
 *
 * A reusable library of SVG edge shapes for the Section shortcode's Top / Bottom Shape Divider
 * pickers. Each row authors one shape with the shared `svg-code` option type — paste an
 * <svg>…</svg> (viewBox 0 0 1200 120, one <path>) OR upload a .svg (read client-side, no media
 * upload). The Section render pulls the <path> into its own trusted wrapper and applies the
 * per-instance Color / Height / Flip, so a shape here only needs its geometry.
 *
 * Built on `addable-box` (add / remove / reorder + storage for free), same as Background
 * Patterns. Stored theme-scoped under the `shape_dividers` key; the built-in four
 * (tilt/curve/wave/triangle) seed it and keep their slugs so existing sections keep resolving.
 *
 * @var array $options Filled with the option schema (loaded via upw_ts_get_options()).
 */

// Per-row live preview: the shape silhouette drawn edge-to-edge in an isolated, script-free
// iframe (the pasted SVG can't touch wp-admin). _.escape() entity-encodes it for the srcdoc.
$preview_template = <<<'TPL'
{{= "<span class='upw-pat-thumb upw-div-thumb'><iframe sandbox='' scrolling='no' srcdoc='" + _.escape("<style>html,body{margin:0;height:100%;display:flex;align-items:stretch;background:#f8fafc}svg{width:100%;height:100%;display:block;fill:#111827}</style>" + (o.svg_code || "")) + "'></iframe></span><span class='upw-pat-title'>" + _.escape(o.divider_name || "Divider") + "</span>" }}
TPL;

$options = array();

$options['shape_dividers'] = array(
	'label'           => __( 'Shape Dividers', 'fw' ),
	'type'            => 'addable-box',
	'value'           => function_exists( 'unysonplus_default_shape_divider_presets' ) ? unysonplus_default_shape_divider_presets() : array(),
	'desc'            => __( 'Paste an <code>&lt;svg&gt;</code> (or upload a .svg) for each edge shape — use the shape-divider convention <code>viewBox="0 0 1200 120"</code> with a single <code>&lt;path&gt;</code>. Each becomes a reusable divider you can pick for a Section\'s Top / Bottom edge (its Color / Height / Flip are set per Section). <strong>Scripts are stripped for safety.</strong> Each collapsed row shows a live preview; expand it to edit.', 'fw' ),
	'sortable'        => true,
	'box-duplicate'   => true,
	'attr'            => array( 'class' => 'fw-preset-2col' ),
	'width'           => 'full',
	'add-button-text' => __( 'Add Shape Divider', 'fw' ),
	'box-options'     => array(
		'id'           => array( 'type' => 'unique' ),
		'divider_name' => array(
			'label'           => __( 'Name', 'fw' ),
			'type'            => 'text',
			'value'           => '',
			'desc'            => __( 'Shown in the Section\'s Top / Bottom Shape Divider picker.', 'fw' ),
			'dynamic_content' => false,
		),
		'svg_code'     => array(
			'label'       => __( 'SVG', 'fw' ),
			'type'        => 'svg-code',
			'value'       => '',
			'placeholder' => '<svg viewBox="0 0 1200 120" preserveAspectRatio="none"><path d="…"/></svg>',
			'desc'        => __( 'Use <code>viewBox="0 0 1200 120"</code> and a single <code>&lt;path&gt;</code>; extra layers are ignored (the Section fills the shape with one colour).', 'fw' ),
		),
	),
	'template'        => $preview_template,
);
