<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/* Build the `design` image-picker choices from the single-source-of-truth
   registry, so adding a design there automatically lists it here. SVG
   thumbnails live under static/img/designs/. */
$g_uri           = fw_ext( 'shortcodes' )->get_declared_URI( '/shortcodes/gallery' );
$g_designs       = require dirname( __FILE__ ) . '/views/designs/registry.php';
$g_design_choices = array();
foreach ( $g_designs as $g_key => $g_def ) {
	$g_design_choices[ $g_key ] = array(
		'small' => array(
			'src'   => $g_uri . '/static/img/designs/' . $g_def['thumb'],
			'alt'   => $g_def['label'],
			'title' => $g_def['label'], // native hover tooltip = the design name
		),
		'label' => $g_def['label'],
	);
}

/* ---------------------------------------------------------------------------
 * Reusable sub-option builders shared by several designs (so the user only
 * ever sees the controls the chosen design actually uses).
 * ------------------------------------------------------------------------- */
$g_columns_choices = array( '1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6' );

$g_opt_columns = function ( $label, $default, $desc ) use ( $g_columns_choices ) {
	return array(
		'label'   => $label,
		'type'    => 'select',
		'value'   => (string) $default,
		'choices' => $g_columns_choices,
		'desc'    => $desc,
	);
};

$g_opt_gap = array(
	'label'   => __( 'Gap', 'fw' ),
	'type'    => 'select',
	'value'   => '3', // scale slug "3" = 1rem (the previous 16px default)
	'choices' => function_exists( 'sc_get_gap_select_choices' )
		? sc_get_gap_select_choices( __( 'None', 'fw' ) )
		: array( '3' => '3' ),
	'desc'    => __( 'Spacing between images, from your Spacing → Gap Scale presets.', 'fw' ),
	'help'    => function_exists( 'sc_styling_help_text' ) ? sc_styling_help_text( 'spacing' ) : '',
);

$g_opt_ratio = array(
	'label'   => __( 'Image Ratio', 'fw' ),
	'type'    => 'select',
	'value'   => '4-3',
	'choices' => array(
		'1-1'      => __( 'Square (1:1)', 'fw' ),
		'4-3'      => __( 'Landscape (4:3)', 'fw' ),
		'3-2'      => __( 'Photo (3:2)', 'fw' ),
		'16-9'     => __( 'Wide (16:9)', 'fw' ),
		'3-4'      => __( 'Portrait (3:4)', 'fw' ),
		'original' => __( 'Original (uncropped)', 'fw' ),
	),
	'desc' => __( 'Crop each thumbnail to this aspect ratio. "Original" keeps the uploaded proportions.', 'fw' ),
);

/* Carousel sub-options (reused by the Carousel design). */
$g_opt_autoplay = array(
	'label' => __( 'Autoplay', 'fw' ),
	'type'  => 'switch',
	'right-choice' => array( 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ),
	'left-choice'  => array( 'value' => 'no',  'label' => __( 'No', 'fw' ) ),
	'value' => 'no',
	'desc'  => __( 'Auto-cycle slides.', 'fw' ),
);
$g_opt_interval = array(
	'label' => __( 'Autoplay Interval (ms)', 'fw' ),
	'type'  => 'text',
	'value' => '4000',
	'desc'  => __( 'Delay between auto slides, in milliseconds.', 'fw' ),
);
$g_opt_loop = array(
	'label' => __( 'Loop', 'fw' ),
	'type'  => 'switch',
	'right-choice' => array( 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ),
	'left-choice'  => array( 'value' => 'no',  'label' => __( 'No', 'fw' ) ),
	'value' => 'yes',
	'desc'  => __( 'Wrap from the last slide back to the first.', 'fw' ),
);
$g_opt_arrows = array(
	'label' => __( 'Show Arrows', 'fw' ),
	'type'  => 'switch',
	'right-choice' => array( 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ),
	'left-choice'  => array( 'value' => 'no',  'label' => __( 'No', 'fw' ) ),
	'value' => 'yes',
	'desc'  => __( 'Display prev/next navigation arrows.', 'fw' ),
);
$g_opt_dots = array(
	'label' => __( 'Show Dots', 'fw' ),
	'type'  => 'switch',
	'right-choice' => array( 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ),
	'left-choice'  => array( 'value' => 'no',  'label' => __( 'No', 'fw' ) ),
	'value' => 'yes',
	'desc'  => __( 'Display pagination dots.', 'fw' ),
);
$g_opt_pause_hover = array(
	'label' => __( 'Pause on Hover', 'fw' ),
	'type'  => 'switch',
	'right-choice' => array( 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ),
	'left-choice'  => array( 'value' => 'no',  'label' => __( 'No', 'fw' ) ),
	'value' => 'yes',
	'desc'  => __( 'Stop autoplay while hovered.', 'fw' ),
);

$options = array(

	/* ----------------------------------------------------------------------
	 * CONTENT
	 * -------------------------------------------------------------------- */
	'tab_content' => array(
		'title'   => __( 'Content', 'fw' ),
		'type'    => 'tab',
		'options' => array(
			'group' => array(
				'type'    => 'group',
				'options' => array(
					'title' => array(
						'label' => __( 'Title', 'fw' ),
						'desc'  => __( 'Optional heading displayed above the gallery.', 'fw' ),
						'help'  => __( 'Plain heading text shown before the images. Leave blank for no heading.', 'fw' ),
						'type'  => 'text',
					),
					'images' => array(
						'label'       => __( 'Images', 'fw' ),
						'desc'        => __( 'Add or arrange the gallery images.', 'fw' ),
						'help'        => __( 'Upload new images or pick existing ones from the Media Library, then drag to reorder. Captions, alt text and titles are read from each image\'s Media Library fields (see the Caption Source option on the Style tab).', 'fw' ),
						'type'        => 'multi-upload',
						'images_only' => true,
					),
				),
			),
		),
	),

	/* ----------------------------------------------------------------------
	 * DESIGN — the image-picker drives a multi-picker that reveals ONLY the
	 * chosen design's options. `design_settings` is a dedicated option id so a
	 * legacy scalar never feeds a multi-picker (no blank-modal on old saves).
	 * -------------------------------------------------------------------- */
	'tab_design' => array(
		'title'   => __( 'Design', 'fw' ),
		'type'    => 'tab',
		'options' => array(
			'group' => array(
				'type'    => 'group',
				'options' => array(
					'design_settings' => array(
						'type'         => 'multi-picker',
						'label'        => __( 'Design', 'fw' ),
						'desc'         => __( 'Pick the gallery layout/design — its options appear in the panel.', 'fw' ),
						'popover'      => true,
						'show_borders' => false,
						'picker'       => array(
							'design' => array(
								'label'   => false,
								'type'    => 'image-picker',
								'choices' => $g_design_choices,
								'desc'    => __( 'Hover a tile to preview it.', 'fw' ),
								'help'    => __( 'Each design is a self-contained layout with its own arrangement. Only the chosen design\'s options appear below. Cross-design appearance (lightbox, captions, colors, spacing) lives on the Style tab.', 'fw' ),
							),
						),
						'value'        => array( 'design' => 'grid' ),
						'choices'      => array(

							/* Grid — uniform responsive tiles. */
							'grid' => array(
								'columns'        => $g_opt_columns( __( 'Columns (Desktop)', 'fw' ), 3, __( 'Columns per row on desktop.', 'fw' ) ),
								'columns_tablet' => $g_opt_columns( __( 'Columns (Tablet)', 'fw' ), 2, __( 'Columns per row on tablets (≤ 992px).', 'fw' ) ),
								'columns_mobile' => $g_opt_columns( __( 'Columns (Mobile)', 'fw' ), 1, __( 'Columns per row on phones (≤ 576px).', 'fw' ) ),
								'gap'            => $g_opt_gap,
								'ratio'          => $g_opt_ratio,
							),

							/* Masonry — CSS-column staggered tiling (natural heights). */
							'masonry' => array(
								'columns'        => $g_opt_columns( __( 'Columns (Desktop)', 'fw' ), 3, __( 'Masonry column count on desktop.', 'fw' ) ),
								'columns_tablet' => $g_opt_columns( __( 'Columns (Tablet)', 'fw' ), 2, __( 'Masonry column count on tablets (≤ 992px).', 'fw' ) ),
								'gap'            => $g_opt_gap,
							),

							/* Justified — Flickr-style equal-height rows. */
							'justified' => array(
								'row_height' => array(
									'label' => __( 'Target Row Height (px)', 'fw' ),
									'type'  => 'slider',
									'value' => 220,
									'properties' => array( 'min' => 120, 'max' => 420, 'step' => 10 ),
									'desc'  => __( 'Approximate height of each justified row.', 'fw' ),
								),
								'gap' => $g_opt_gap,
							),

							/* Metro / Bento — featured grid with spanning cells. */
							'metro' => array(
								'columns' => $g_opt_columns( __( 'Columns (Desktop)', 'fw' ), 4, __( 'Base grid columns on desktop. Some cells span 2× for the bento effect.', 'fw' ) ),
								'gap'     => $g_opt_gap,
							),

							/* Carousel — Splide slider. */
							'carousel' => array(
								'per_view' => $g_opt_columns( __( 'Slides per View', 'fw' ), 3, __( 'How many images are visible at once.', 'fw' ) ),
								'ratio'    => $g_opt_ratio,
								'gap'      => $g_opt_gap,
								'carousel_autoplay'    => $g_opt_autoplay,
								'carousel_interval'    => $g_opt_interval,
								'carousel_pause_hover' => $g_opt_pause_hover,
								'carousel_loop'        => $g_opt_loop,
								'carousel_arrows'      => $g_opt_arrows,
								'carousel_dots'        => $g_opt_dots,
							),

							/* Polaroid — scattered tilted photo cards. */
							'polaroid' => array(
								'columns'        => $g_opt_columns( __( 'Columns (Desktop)', 'fw' ), 4, __( 'Columns per row on desktop.', 'fw' ) ),
								'columns_tablet' => $g_opt_columns( __( 'Columns (Tablet)', 'fw' ), 2, __( 'Columns per row on tablets (≤ 992px).', 'fw' ) ),
								'gap'            => $g_opt_gap,
								'tilt' => array(
									'label' => __( 'Random Tilt', 'fw' ),
									'type'  => 'switch',
									'right-choice' => array( 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ),
									'left-choice'  => array( 'value' => 'no',  'label' => __( 'No', 'fw' ) ),
									'value' => 'yes',
									'desc'  => __( 'Give each card a slight, scattered rotation (straightens on hover).', 'fw' ),
								),
							),

							/* Showcase — large featured image + thumbnail strip. */
							'showcase' => array(
								'ratio'         => $g_opt_ratio,
								'thumb_position' => array(
									'label'   => __( 'Thumbnail Position', 'fw' ),
									'type'    => 'select',
									'value'   => 'bottom',
									'choices' => array(
										'bottom' => __( 'Below the main image', 'fw' ),
										'left'   => __( 'Left of the main image', 'fw' ),
										'right'  => __( 'Right of the main image', 'fw' ),
									),
									'desc' => __( 'Where the thumbnail strip sits relative to the featured image.', 'fw' ),
								),
								'gap' => $g_opt_gap,
							),

							/* Cards — image + caption panel in a shadowed card. */
							'cards' => array(
								'columns'        => $g_opt_columns( __( 'Columns (Desktop)', 'fw' ), 3, __( 'Columns per row on desktop.', 'fw' ) ),
								'columns_tablet' => $g_opt_columns( __( 'Columns (Tablet)', 'fw' ), 2, __( 'Columns per row on tablets (≤ 992px).', 'fw' ) ),
								'columns_mobile' => $g_opt_columns( __( 'Columns (Mobile)', 'fw' ), 1, __( 'Columns per row on phones (≤ 576px).', 'fw' ) ),
								'gap'            => $g_opt_gap,
								'ratio'          => $g_opt_ratio,
							),

							/* Slideshow / Fade — one full-width image, crossfading. */
							'slideshow' => array(
								'ratio'     => $g_opt_ratio,
								'ken_burns' => array(
									'label' => __( 'Ken Burns Zoom', 'fw' ),
									'type'  => 'switch',
									'right-choice' => array( 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ),
									'left-choice'  => array( 'value' => 'no',  'label' => __( 'No', 'fw' ) ),
									'value' => 'yes',
									'desc'  => __( 'Slowly zoom each slide while it is shown.', 'fw' ),
								),
								'carousel_autoplay'    => array_merge( $g_opt_autoplay, array( 'value' => 'yes' ) ),
								'carousel_interval'    => array_merge( $g_opt_interval, array( 'value' => '5000' ) ),
								'carousel_pause_hover' => $g_opt_pause_hover,
								'carousel_loop'        => $g_opt_loop,
								'carousel_arrows'      => $g_opt_arrows,
								'carousel_dots'        => $g_opt_dots,
							),

							/* Thumbnail Slider — main slider synced to a thumb nav. */
							'thumbslider' => array(
								'ratio'                => $g_opt_ratio,
								'carousel_autoplay'    => $g_opt_autoplay,
								'carousel_interval'    => $g_opt_interval,
								'carousel_pause_hover' => $g_opt_pause_hover,
								'carousel_loop'        => $g_opt_loop,
								'carousel_arrows'      => $g_opt_arrows,
							),

							/* Coverflow — centred slide large, neighbours scaled back. */
							'coverflow' => array(
								'per_view' => $g_opt_columns( __( 'Visible Slides', 'fw' ), 3, __( 'How many slides are visible (the centre one is focused).', 'fw' ) ),
								'ratio'    => $g_opt_ratio,
								'gap'      => $g_opt_gap,
								'carousel_autoplay'    => $g_opt_autoplay,
								'carousel_interval'    => $g_opt_interval,
								'carousel_pause_hover' => $g_opt_pause_hover,
								'carousel_loop'        => array_merge( $g_opt_loop, array( 'value' => 'yes' ) ),
								'carousel_arrows'      => $g_opt_arrows,
								'carousel_dots'        => $g_opt_dots,
							),

							/* Marquee / Ticker — continuously scrolling single row. */
							'marquee' => array(
								'row_height' => array(
									'label' => __( 'Row Height (px)', 'fw' ),
									'type'  => 'slider',
									'value' => 180,
									'properties' => array( 'min' => 100, 'max' => 360, 'step' => 10 ),
									'desc'  => __( 'Height of the scrolling strip.', 'fw' ),
								),
								'marquee_speed' => array(
									'label' => __( 'Scroll Speed', 'fw' ),
									'type'  => 'select',
									'value' => 'normal',
									'choices' => array(
										'slow'   => __( 'Slow', 'fw' ),
										'normal' => __( 'Normal', 'fw' ),
										'fast'   => __( 'Fast', 'fw' ),
									),
									'desc' => __( 'How fast the row scrolls.', 'fw' ),
								),
								'marquee_direction' => array(
									'label' => __( 'Direction', 'fw' ),
									'type'  => 'select',
									'value' => 'left',
									'choices' => array(
										'left'  => __( 'Right → Left', 'fw' ),
										'right' => __( 'Left → Right', 'fw' ),
									),
									'desc' => __( 'Scroll direction.', 'fw' ),
								),
								'gap' => $g_opt_gap,
							),

							/* Filmstrip — native horizontal scroll-snap strip. */
							'filmstrip' => array(
								'per_view' => $g_opt_columns( __( 'Visible Slides', 'fw' ), 3, __( 'Roughly how many images are visible at once.', 'fw' ) ),
								'ratio'    => $g_opt_ratio,
								'gap'      => $g_opt_gap,
							),

							/* Spotlight — first image large + the rest in a grid. */
							'spotlight' => array(
								'feature_side' => array(
									'label'   => __( 'Featured Side', 'fw' ),
									'type'    => 'select',
									'value'   => 'left',
									'choices' => array(
										'left'  => __( 'Left', 'fw' ),
										'right' => __( 'Right', 'fw' ),
									),
									'desc' => __( 'Which side the large featured image sits on.', 'fw' ),
								),
								'columns' => $g_opt_columns( __( 'Thumbnail Columns', 'fw' ), 3, __( 'Columns in the smaller image grid.', 'fw' ) ),
								'gap'     => $g_opt_gap,
							),

							/* Honeycomb — hexagon-tiled mosaic. */
							'honeycomb' => array(
								'columns' => $g_opt_columns( __( 'Columns (Desktop)', 'fw' ), 4, __( 'Hexagons per row on desktop.', 'fw' ) ),
								'gap'     => $g_opt_gap,
							),

							/* Image Accordion — panels that expand on hover. */
							'accordion' => array(
								'row_height' => array(
									'label' => __( 'Height (px)', 'fw' ),
									'type'  => 'slider',
									'value' => 340,
									'properties' => array( 'min' => 180, 'max' => 560, 'step' => 10 ),
									'desc'  => __( 'Height of the accordion panels.', 'fw' ),
								),
								'gap' => $g_opt_gap,
							),

							/* Flip Cards — hover-flip to reveal the caption. */
							'flipcards' => array(
								'columns'        => $g_opt_columns( __( 'Columns (Desktop)', 'fw' ), 3, __( 'Columns per row on desktop.', 'fw' ) ),
								'columns_tablet' => $g_opt_columns( __( 'Columns (Tablet)', 'fw' ), 2, __( 'Columns per row on tablets (≤ 992px).', 'fw' ) ),
								'columns_mobile' => $g_opt_columns( __( 'Columns (Mobile)', 'fw' ), 1, __( 'Columns per row on phones (≤ 576px).', 'fw' ) ),
								'gap'            => $g_opt_gap,
								'ratio'          => $g_opt_ratio,
							),

							/* Stack / Banners — full-width stacked strips. */
							'stack' => array(
								'banner_ratio' => array(
									'label'   => __( 'Banner Ratio', 'fw' ),
									'type'    => 'select',
									'value'   => '21-9',
									'choices' => array(
										'16-9'     => __( 'Wide (16:9)', 'fw' ),
										'2-1'      => __( 'Panorama (2:1)', 'fw' ),
										'21-9'     => __( 'Cinematic (21:9)', 'fw' ),
										'3-1'      => __( 'Banner (3:1)', 'fw' ),
										'4-1'      => __( 'Strip (4:1)', 'fw' ),
										'original' => __( 'Original (uncropped)', 'fw' ),
									),
									'desc' => __( 'Crop each full-width strip to this aspect ratio.', 'fw' ),
								),
								'gap' => $g_opt_gap,
							),
						),
					),
				),
			),
		),
	),

	/* ----------------------------------------------------------------------
	 * STYLE — cross-design appearance, lightbox, captions, colors + spacing.
	 * -------------------------------------------------------------------- */
	'tab_style' => array(
		'title'   => __( 'Style', 'fw' ),
		'type'    => 'tab',
		'options' => array(
			'group_behavior' => array(
				'type'    => 'group',
				'options' => array(
					'container_type' => array(
						'label' => __( 'Container', 'fw' ),
						'type'  => 'select',
						'value' => '',
						'choices' => array(
							''                => __( 'None (full width)', 'fw' ),
							'container'       => __( 'Container', 'fw' ),
							'container-fluid' => __( 'Fluid', 'fw' ),
						),
						'desc' => __( 'Outer width wrapper around the gallery.', 'fw' ),
					),
					'click_action' => array(
						'label' => __( 'On Image Click', 'fw' ),
						'type'  => 'select',
						'value' => 'lightbox',
						'choices' => array(
							'lightbox'   => __( 'Open Lightbox', 'fw' ),
							'file'       => __( 'Open Full Image (new tab)', 'fw' ),
							'attachment' => __( 'Go to Attachment Page', 'fw' ),
							'none'       => __( 'Do Nothing', 'fw' ),
						),
						'desc' => __( 'What happens when a visitor clicks an image.', 'fw' ),
					),
					'captions' => array(
						'label' => __( 'Captions', 'fw' ),
						'type'  => 'select',
						'value' => 'none',
						'choices' => array(
							'none'  => __( 'None', 'fw' ),
							'hover' => __( 'Overlay on Hover', 'fw' ),
							'below' => __( 'Below the Image', 'fw' ),
						),
						'desc' => __( 'Show a caption for each image, sourced from the Media Library field below.', 'fw' ),
					),
					'caption_source' => array(
						'label' => __( 'Caption Source', 'fw' ),
						'type'  => 'select',
						'value' => 'caption',
						'choices' => array(
							'caption'     => __( 'Image Caption', 'fw' ),
							'title'       => __( 'Image Title', 'fw' ),
							'alt'         => __( 'Alt Text', 'fw' ),
							'description' => __( 'Description', 'fw' ),
						),
						'desc' => __( 'Which Media Library field to use for the caption (and the lightbox caption).', 'fw' ),
					),
					'rounded' => array(
						'label' => __( 'Corner Radius', 'fw' ),
						'type'  => 'select',
						'value' => 'rounded',
						'choices' => array(
							'rounded-0'    => __( 'Square', 'fw' ),
							'rounded'      => __( 'Rounded', 'fw' ),
							'rounded-lg'   => __( 'Large', 'fw' ),
							'rounded-circle' => __( 'Circle', 'fw' ),
						),
						'desc' => __( 'Rounding applied to each image.', 'fw' ),
					),
					'hover_zoom' => array(
						'label' => __( 'Zoom on Hover', 'fw' ),
						'type'  => 'switch',
						'right-choice' => array( 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ),
						'left-choice'  => array( 'value' => 'no',  'label' => __( 'No', 'fw' ) ),
						'value' => 'yes',
						'desc'  => __( 'Gently scale each image when hovered.', 'fw' ),
					),
				),
			),
			'group_colors' => array(
				'type'    => 'group',
				'options' => array(
					'text_color'       => sc_color_field_compact( array( 'label' => __( 'Text Color', 'fw' ),       'kind' => 'text' ) ),
					'bg_color'         => sc_color_field_compact( array( 'label' => __( 'Background Color', 'fw' ), 'kind' => 'bg' ) ),
					'font_size_preset' => sc_font_size_field( array(
						'desc' => __( 'A named size from the framework presets. Customizable in Theme Settings on the official Unyson+ theme.', 'fw' ),
					) ),
					'title_color' => sc_color_field_compact( array(
						'label' => __( 'Title Color', 'fw' ),
						'desc'  => __( 'Overrides the general Text Color for the gallery heading.', 'fw' ),
					) ),
					'caption_color' => sc_color_field_compact( array(
						'label' => __( 'Caption Color', 'fw' ),
						'desc'  => __( 'Color of the per-image caption text.', 'fw' ),
					) ),
				),
			),
			'group_spacings' => array(
				'type'    => 'group',
				'options' => array(
					'spacing' => array(
						'type'  => 'spacing',
						'label' => __( 'Margin & Padding', 'fw' ),
						'desc'  => __( 'All Sides applies to every side at once; any per-side value (Top, Right, Bottom, Left) overrides it for that direction.', 'fw' ),
						'help'  => sc_styling_help_text( 'spacing' ),
					),
				),
			),
		),
	),

	'tab_animation' => array(
		'title'   => __( 'Animations', 'fw' ),
		'type'    => 'tab',
		'options' => sc_get_animation_fields(),
	),
	'tab_advanced' => array(
		'title'   => __( 'Advanced', 'fw' ),
		'type'    => 'tab',
		'options' => array(
			'advanced_settings' => array(
				'type'    => 'group',
				'options' => sc_get_advanced_tab(),
			),
		),
	),
);
