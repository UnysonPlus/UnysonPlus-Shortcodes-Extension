<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Theme Settings → Components → Image Styles.
 *
 * A reusable library of image treatments applied to any element's image as a scoped
 * `.imgs-{slug}` class: crop/aspect, corner radius / circle, clip + SVG masks, CSS
 * filters (incl. a blend-mode duotone tint), and a legibility scrim. Built on the
 * shared `addable-box` option type (same model as Background Patterns / Buttons →
 * Sizes), so add / remove / reorder + storage come for free.
 *
 * Token-bundle architecture: each preset emits ONLY a set of CSS custom properties
 * (generated in css-tokens.php); one shared `.imgs-wrap` base rule consumes them.
 * Curated fields cover ~95%; anything exotic goes in an element's Custom CSS.
 * Animated hover is the Animation Engine's Hover module, not here.
 *
 * @var array $options       Filled with the option schema (loaded via upw_ts_get_options()).
 * @var array $color_choices slug => array( label, color ) from the Color Presets (passed in).
 */

$imgs_color = function ( $label, $desc ) {
	if ( function_exists( 'sc_color_field_compact' ) ) {
		return sc_color_field_compact( array( 'label' => $label, 'desc' => $desc, 'kind' => 'bg' ) );
	}
	return array( 'type' => 'color-picker', 'label' => $label, 'desc' => $desc, 'value' => '' );
};

// Live preview: an isolated <iframe> with a CSS-gradient "photo" stand-in (no external
// request — CSP-safe) that reflects the row's radius / aspect / filter / clip / scrim.
// SVG masks (arch/blob) and duotone are approximated in the thumbnail; the real render
// uses the full token bundle.
$preview_template = <<<'TPL'
{{
	var o = obj || {};
	var m = (o.mask && o.mask.mask) || 'none';
	var radiusMap = { rounded:'14px', 'rounded-xl':'28px', circle:'50%', squircle:'28%', arch:'50% 50% 8px 8px / 62% 62% 8px 8px', leaf:'0 50% 0 50% / 0 50% 0 50%' };
	var clipMap = { diagonal:'polygon(0 0,100% 0,100% 85%,0 100%)', hexagon:'polygon(50% 0,100% 25%,100% 75%,50% 100%,0 75%,0 25%)', diamond:'polygon(50% 0,100% 50%,50% 100%,0 50%)', triangle:'polygon(50% 2%,100% 100%,0 100%)', pentagon:'polygon(50% 0,100% 38%,82% 100%,18% 100%,0 38%)', star:'polygon(50% 0,61% 35%,98% 35%,68% 57%,79% 91%,50% 70%,21% 91%,32% 57%,2% 35%,39% 35%)', chevron:'polygon(0 0,75% 0,100% 50%,75% 100%,0 100%,25% 50%)', octagon:'polygon(30% 0,70% 0,100% 30%,100% 70%,70% 100%,30% 100%,0 70%,0 30%)', shield:'polygon(0 0,100% 0,100% 62%,50% 100%,0 62%)' };
	var svgApprox = { heart:'42% 42% 0 42% / 55% 55% 0 55%', 'blob-1':'42% 58% 60% 40% / 55% 45% 55% 45%', 'blob-2':'60% 40% 55% 45% / 45% 55% 40% 60%', flower:'50%', brush:'30% 8% 30% 8%', 'water-splash':'40%', 'grunge-frame':'6px' };
	var squareSet = { circle:1, squircle:1, leaf:1, hexagon:1, diamond:1, triangle:1, pentagon:1, star:1, chevron:1, octagon:1, shield:1, heart:1, 'blob-1':1, 'blob-2':1, flower:1 };
	var r = radiusMap[m] || (m === 'none' ? (o.radius || '0') : (svgApprox[m] || '0'));
	var clip = clipMap[m] || 'none';
	var ar = squareSet[m] ? '1/1' : ({ '1-1':'1/1','4-3':'4/3','3-4':'3/4','16-9':'16/9','3-2':'3/2' }[o.aspect] || '4/3');
	var fmap = { grayscale:'grayscale(1)', sepia:'sepia(.7)', contrast:'contrast(1.4)', saturate:'saturate(1.8)', blur:'blur(2px)', duotone:'grayscale(1) contrast(1.05)' };
	var f = fmap[o.filter] || 'none';
	var scrimC = (o.scrim_color && (o.scrim_color.custom || o.scrim_color.predefined)) ? (o.scrim_color.custom || '#0b0b0f') : '#0b0b0f';
	var scrim = { top:'linear-gradient(0deg,transparent 55%,'+scrimC+')', bottom:'linear-gradient(180deg,transparent 55%,'+scrimC+')', radial:'radial-gradient(120% 100% at 50% 100%,'+scrimC+',transparent 60%)' }[o.scrim] || 'transparent';
	var duoC = (o.duo_color && o.duo_color.custom) ? o.duo_color.custom : '';
	var photo = "linear-gradient(135deg,#8ea2c4,#c9b3d6 45%,#e7c9a3)";
	var css = ".ph{position:relative;width:100%;aspect-ratio:"+ar+";border-radius:"+r+";overflow:hidden;background:"+photo+";filter:"+f+";clip-path:"+clip+"}"
		+ ".sc{position:absolute;inset:0;background:"+scrim+"}"
		+ (o.filter==='duotone'&&duoC ? ".du{position:absolute;inset:0;background:"+duoC+";mix-blend-mode:color}" : ".du{display:none}");
	var html = "<div class='ph'><div class='du'></div><div class='sc'></div></div>";
}}{{= "<span class='upw-imgs-thumb'><iframe sandbox scrolling='no' srcdoc='" + _.escape("<style>html,body{margin:0;padding:6px;box-sizing:border-box}"+css+"</style>"+html) + "'></iframe></span><span class='upw-imgs-title'>" + _.escape(o.style_name || 'Image Style') + "</span>" }}
TPL;

$options = array(
	'image_styles' => array(
		'label'           => __( 'Image Styles', 'fw' ),
		'type'            => 'addable-box',
		'value'           => function_exists( 'unysonplus_default_image_style_presets' ) ? unysonplus_default_image_style_presets() : array(),
		'desc'            => __( 'Reusable image treatments. Each becomes a <code>.imgs-{name}</code> you pick on any element with an image (Styling → Image Style) — crop, corners, mask, filter and a legibility scrim. Advanced one-offs go in the element\'s <strong>Custom CSS</strong>. Animated hover lives in the Animation Engine.', 'fw' ),
		'sortable'        => true,
		'box-duplicate'   => true,
		'attr'            => array( 'class' => 'fw-preset-2col' ),
		'width'           => 'full',
		'add-button-text' => __( 'Add Image Style', 'fw' ),
		'box-options'     => array(
			'id'         => array( 'type' => 'unique' ),
			'style_name' => array(
				'label'           => __( 'Style Name', 'fw' ),
				'type'            => 'text',
				'value'           => '',
				'desc'            => __( 'Becomes the class suffix, e.g. Portrait Card → <code>.imgs-portrait-card</code>.', 'fw' ),
				'dynamic_content' => false,
			),
			'aspect' => array(
				'label'   => __( 'Aspect Ratio', 'fw' ),
				'type'    => 'select',
				'value'   => 'auto',
				'choices' => array(
					'auto' => __( 'Auto (native)', 'fw' ),
					'1-1'  => __( 'Square 1:1', 'fw' ),
					'4-3'  => __( 'Landscape 4:3', 'fw' ),
					'3-2'  => __( 'Landscape 3:2', 'fw' ),
					'16-9' => __( 'Wide 16:9', 'fw' ),
					'3-4'  => __( 'Portrait 3:4', 'fw' ),
				),
				'desc'    => __( 'Crops the image to this ratio (object-fit: cover). Auto keeps the native ratio.', 'fw' ),
			),
			'radius' => array(
				'label'       => __( 'Corner Radius', 'fw' ),
				'type'        => 'text',
				'value'       => '',
				'placeholder' => '12px',
				'desc'        => __( 'A CSS length (e.g. <code>12px</code>, <code>1rem</code>) for simple rounded corners. Used when Shape / Mask is “None”; picking a Mask shape overrides it.', 'fw' ),
			),
			// Shape / Mask — a popover image-picker of shape thumbnails (the same visual
			// grid as the Image Box shortcode, from the shared mask library), with a
			// "Custom" reveal for an inline SVG / URL / clip-path. Popover keeps the row
			// compact; per the multi-picker rules the label sits on the TOP level.
			'mask' => array(
				'type'         => 'multi-picker',
				'label'        => __( 'Shape / Mask', 'fw' ),
				'desc'         => __( 'Clip the image to a shape — rounded / circle / arch, geometric (hexagon, star, diamond, …) or organic (heart, blob, leaf, …). Shape masks force a square crop. Pick “Custom” to supply your own SVG or clip-path.', 'fw' ),
				'popover'      => true,
				'show_borders' => false,
				'picker'       => array(
					'mask' => array(
						'type'    => 'image-picker',
						'label'   => false,
						'value'   => 'none',
						'choices' => function_exists( 'sc_image_mask_imagepicker_choices' ) ? sc_image_mask_imagepicker_choices() : array( 'none' => array( 'label' => __( 'None', 'fw' ) ) ),
						'search'  => true,
					),
				),
				'value'        => array( 'mask' => 'none' ),
				'choices'      => array(
					'custom' => array(
						'custom_svg' => array(
							'type'  => 'textarea',
							'label' => __( 'Inline SVG or SVG URL', 'fw' ),
							'desc'  => __( 'Paste inline <code>&lt;svg&gt;</code> (a filled shape on a transparent background) OR a URL to a hosted <code>.svg</code>.', 'fw' ),
						),
						'custom_clip' => array(
							'type'        => 'text',
							'label'       => __( 'Or a CSS clip-path', 'fw' ),
							'value'       => '',
							'placeholder' => 'polygon(50% 0, 100% 100%, 0 100%)',
							'desc'        => __( 'Advanced: a raw CSS clip-path (used if the SVG field above is empty).', 'fw' ),
						),
					),
				),
			),
			'filter' => array(
				'label'   => __( 'Filter', 'fw' ),
				'type'    => 'select',
				'value'   => 'none',
				'choices' => array(
					'none'      => __( 'None', 'fw' ),
					'grayscale' => __( 'Grayscale', 'fw' ),
					'sepia'     => __( 'Sepia', 'fw' ),
					'contrast'  => __( 'High Contrast', 'fw' ),
					'saturate'  => __( 'Vivid (saturate)', 'fw' ),
					'blur'      => __( 'Soft Blur', 'fw' ),
					'duotone'   => __( 'Duotone (tint)', 'fw' ),
				),
			),
			'duo_color' => $imgs_color(
				__( 'Duotone Color', 'fw' ),
				__( 'The tint colour for the Duotone filter (a grayscale image tinted with this colour).', 'fw' )
			),
			'scrim' => array(
				'label'   => __( 'Scrim Overlay', 'fw' ),
				'type'    => 'select',
				'value'   => 'none',
				'choices' => array(
					'none'   => __( 'None', 'fw' ),
					'bottom' => __( 'Bottom (for captions over image)', 'fw' ),
					'top'    => __( 'Top', 'fw' ),
					'radial' => __( 'Radial (corner)', 'fw' ),
				),
				'desc'    => __( 'A gradient overlay that darkens part of the image so overlaid text stays legible.', 'fw' ),
			),
			'scrim_color' => $imgs_color(
				__( 'Scrim Color', 'fw' ),
				__( 'The scrim gradient colour (fades from transparent to this).', 'fw' )
			),
			'custom_css'  => array(
				'label'       => __( 'Custom CSS (advanced)', 'fw' ),
				'type'        => 'code-editor',
				'mode'        => 'css',
				'height'      => 150,
				'placeholder' => "{{SELECTOR}} img {\n  /* your styles */\n}\n{{SELECTOR}}:hover img {\n  transform: scale(1.05);\n}",
				'desc'        => __( 'Optional raw CSS for anything the fields above don\'t cover. Use <code>{{SELECTOR}}</code> for this style\'s wrapper class (e.g. <code>{{SELECTOR}} img { … }</code>, <code>{{SELECTOR}}::after { … }</code>). Applies wherever the style is used.', 'fw' ),
			),
		),
		'template'        => $preview_template,
	),
);
