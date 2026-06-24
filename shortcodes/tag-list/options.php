<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

// Inline data-URI SVG thumbnails for the Design image-picker — three mini tags drawn in each
// design's style (no asset files). Brand green = #1a8f74; neutrals match the admin palette.
$tl_thumb = function ( $design ) {
	$W = 120; $H = 46; $g = '#1a8f74';
	$svg = '<rect width="' . $W . '" height="' . $H . '" fill="#ffffff"/>';
	$xs = array( 13, 51, 85 ); $ws = array( 32, 28, 22 );
	for ( $i = 0; $i < 3; $i++ ) {
		$x = $xs[ $i ]; $w = $ws[ $i ]; $y = 15; $h = 16; $r = 8;
		if ( $design === 'line' ) {
			$svg .= '<rect x="' . $x . '" y="20" width="' . $w . '" height="6" rx="3" fill="#8c8c8c"/>';
			if ( $i < 2 ) { $svg .= '<circle cx="' . ( $x + $w + 5 ) . '" cy="23" r="1.7" fill="#c4c8cc"/>'; }
			continue;
		}
		if ( $design === 'soft' )    { $fill = 'rgba(26,143,116,.14)'; $stroke = ''; $tcol = $g; }
		elseif ( $design === 'outline' ) { $fill = '#ffffff'; $stroke = '#c9cdd2'; $tcol = '#8c8c8c'; }
		elseif ( $design === 'solid' )   { $fill = $g; $stroke = ''; $tcol = '#ffffff'; }
		else /* subtle */                { $fill = '#eef0f1'; $stroke = ''; $tcol = '#9aa1a8'; }
		$svg .= '<rect x="' . $x . '" y="' . $y . '" width="' . $w . '" height="' . $h . '" rx="' . $r . '" fill="' . $fill . '"' . ( $stroke ? ' stroke="' . $stroke . '"' : '' ) . '/>';
		$svg .= '<rect x="' . ( $x + 6 ) . '" y="' . ( $y + 6 ) . '" width="' . ( $w - 12 ) . '" height="4" rx="2" fill="' . $tcol . '"/>';
	}
	$svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' . $W . ' ' . $H . '" width="' . $W . '" height="' . $H . '">' . $svg . '</svg>';
	return 'data:image/svg+xml,' . rawurlencode( $svg );
};
$tl_pick = function ( $design, $label ) use ( $tl_thumb ) {
	return array( 'small' => array( 'src' => $tl_thumb( $design ), 'height' => 46 ), 'label' => $label );
};

$options = array(

	/* ========================== CONTENT ========================== */
	'tab_content' => array(
		'title'   => __( 'Content', 'fw' ),
		'type'    => 'tab',
		'options' => array(
			'group_content' => array(
				'type'    => 'group',
				'options' => array(
					'items' => array(
						'type'  => 'textarea',
						'label' => __( 'Items', 'fw' ),
						'desc'  => __( 'One item per line. Add a link with "Label | https://example.com" (or a relative "/path/").', 'fw' ),
						'help'  => __( 'Each line becomes one tag. A line without a "|" is plain text; "Label | URL" turns that tag into a link. Blank lines are ignored.', 'fw' ),
						'value' => "Layout\nContent\nMedia\nInteractive\nComponents",
					),
				),
			),
		),
	),

	/* ========================== DESIGN ========================== */
	'tab_design' => array(
		'title'   => __( 'Design', 'fw' ),
		'type'    => 'tab',
		'options' => array(
			'group_look' => array(
				'type'    => 'group',
				'options' => array(
					'design' => array(
						'type'    => 'image-picker',
						'label'   => __( 'Design', 'fw' ),
						'help'    => __( 'Soft = tinted fill; Outline = bordered; Solid = filled; Subtle = light grey; Inline = no pill, items separated by a dot. The colour comes from the Styling tab.', 'fw' ),
						'value'   => 'soft',
						'choices' => array(
							'soft'    => $tl_pick( 'soft',    __( 'Soft', 'fw' ) ),
							'outline' => $tl_pick( 'outline', __( 'Outline', 'fw' ) ),
							'solid'   => $tl_pick( 'solid',   __( 'Solid', 'fw' ) ),
							'subtle'  => $tl_pick( 'subtle',  __( 'Subtle', 'fw' ) ),
							'line'    => $tl_pick( 'line',    __( 'Inline', 'fw' ) ),
						),
					),
					'shape' => array(
						'type'    => 'select',
						'label'   => __( 'Shape', 'fw' ),
						'value'   => 'pill',
						'choices' => array(
							'pill'    => __( 'Pill (fully rounded)', 'fw' ),
							'rounded' => __( 'Rounded', 'fw' ),
							'square'  => __( 'Square', 'fw' ),
						),
						'desc'    => __( 'Corner rounding (ignored by the Inline design).', 'fw' ),
					),
					'size' => array(
						'type'    => 'select',
						'label'   => __( 'Size', 'fw' ),
						'value'   => 'md',
						'choices' => array( 'sm' => __( 'Small', 'fw' ), 'md' => __( 'Medium', 'fw' ), 'lg' => __( 'Large', 'fw' ) ),
					),
				),
			),
			'group_layout' => array(
				'type'    => 'group',
				'options' => array(
					'align' => array(
						'type'    => 'select',
						'label'   => __( 'Alignment', 'fw' ),
						'value'   => 'start',
						'choices' => array( 'start' => __( 'Left', 'fw' ), 'center' => __( 'Center', 'fw' ), 'end' => __( 'Right', 'fw' ) ),
						'desc'    => __( 'Horizontal alignment of the row of tags.', 'fw' ),
					),
					'gap' => array(
						'type'    => 'select',
						'label'   => __( 'Gap', 'fw' ),
						'value'   => 'sm',
						'choices' => array( 'sm' => __( 'Tight', 'fw' ), 'md' => __( 'Normal', 'fw' ), 'lg' => __( 'Roomy', 'fw' ) ),
						'desc'    => __( 'Space between tags.', 'fw' ),
					),
					'marker' => array(
						'type'    => 'select',
						'label'   => __( 'Leading Marker', 'fw' ),
						'value'   => 'none',
						'choices' => array( 'none' => __( 'None', 'fw' ), 'dot' => __( 'Dot', 'fw' ) ),
						'desc'    => __( 'Optional small dot before each item.', 'fw' ),
					),
					'hover' => array(
						'type'         => 'switch',
						'label'        => __( 'Hover Lift', 'fw' ),
						'desc'         => __( 'Subtle lift + accent on hover — most noticeable on linked tags.', 'fw' ),
						'right-choice' => array( 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ),
						'left-choice'  => array( 'value' => 'no',  'label' => __( 'No', 'fw' ) ),
						'value'        => 'no',
					),
				),
			),
		),
	),

	/* ========================== STYLING ========================== */
	'tab_styling' => array(
		'title'   => __( 'Styling', 'fw' ),
		'type'    => 'tab',
		'options' => array(
			'group_colors' => array(
				'type'    => 'group',
				'options' => array(
					'tag_color' => sc_color_field_compact( array(
						'label' => __( 'Tag Color', 'fw' ),
						'desc'  => __( 'Drives the fill / border / text of every tag. Pick a Color Preset (recommended — it follows your brand) or a custom colour. Leave empty for a neutral grey.', 'fw' ),
					) ),
				),
			),
			'group_spacings' => array(
				'type'    => 'group',
				'options' => array(
					'spacing' => array(
						'type'  => 'spacing',
						'label' => __( 'Margin & Padding', 'fw' ),
						'desc'  => __( 'All Sides applies to every side at once; any per-side value overrides it for that direction.', 'fw' ),
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
