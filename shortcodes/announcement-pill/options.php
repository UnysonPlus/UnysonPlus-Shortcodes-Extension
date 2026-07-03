<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

// Inline data-URI SVG thumbnails for the Design image-picker — one mini pill drawn in each
// style (no asset files). Brand green = #1a8f74; neutrals match the admin palette.
$ap_thumb = function ( $style ) {
	$W = 132; $H = 48; $g = '#1a8f74';
	$x = 14; $y = 14; $w = 104; $h = 20; $r = 10; // the pill
	$defs = ''; $fill = '#ffffff'; $stroke = ''; $tagfill = $g; $tagtext = '#ffffff'; $msg = '#5b636b';
	switch ( $style ) {
		case 'soft':     $fill = 'rgba(26,143,116,.13)'; $msg = $g; break;
		case 'outline':  $fill = '#ffffff'; $stroke = '#c9cdd2'; $msg = '#6b7178'; break;
		case 'solid':    $fill = $g; $msg = '#ffffff'; $tagfill = '#ffffff'; $tagtext = $g; break;
		case 'subtle':   $fill = '#eef0f1'; $msg = '#8a9096'; $tagfill = '#9aa1a8'; break;
		case 'ghost':    $fill = 'none'; $msg = $g; break;
		case 'gradient':
			$defs = '<linearGradient id="apg" x1="0" y1="0" x2="1" y2="0"><stop offset="0" stop-color="#1a8f74"/><stop offset="1" stop-color="#2f74e6"/></linearGradient>';
			$fill = 'url(#apg)'; $msg = '#ffffff'; $tagfill = 'rgba(255,255,255,.85)'; $tagtext = '#1a8f74'; break;
		case 'glass':    $fill = 'rgba(120,130,140,.18)'; $stroke = 'rgba(255,255,255,.7)'; $msg = '#45505a'; break;
	}
	$svg  = '<rect width="' . $W . '" height="' . $H . '" fill="#fafbfc"/>';
	$svg .= $defs ? '<defs>' . $defs . '</defs>' : '';
	$svg .= '<rect x="' . $x . '" y="' . $y . '" width="' . $w . '" height="' . $h . '" rx="' . $r . '" fill="' . $fill . '"' . ( $stroke ? ' stroke="' . $stroke . '"' : '' ) . '/>';
	// sub-tag
	$svg .= '<rect x="' . ( $x + 8 ) . '" y="' . ( $y + 6 ) . '" width="22" height="8" rx="4" fill="' . $tagfill . '"/>';
	$svg .= '<rect x="' . ( $x + 11 ) . '" y="' . ( $y + 8.5 ) . '" width="16" height="3" rx="1.5" fill="' . $tagtext . '"/>';
	// message line
	$svg .= '<rect x="' . ( $x + 38 ) . '" y="' . ( $y + 8 ) . '" width="46" height="4" rx="2" fill="' . $msg . '"/>';
	// trailing arrow dot
	$svg .= '<circle cx="' . ( $x + $w - 12 ) . '" cy="' . ( $y + 10 ) . '" r="2.4" fill="' . $msg . '"/>';
	$svg  = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' . $W . ' ' . $H . '" width="' . $W . '" height="' . $H . '">' . $svg . '</svg>';
	return 'data:image/svg+xml,' . rawurlencode( $svg );
};
$ap_pick = function ( $style, $label ) use ( $ap_thumb ) {
	return array( 'small' => array( 'src' => $ap_thumb( $style ), 'height' => 48 ), 'label' => $label );
};

$options = array(

	/* ========================== CONTENT ========================== */
	'tab_content' => array(
		'title'   => __( 'Content', 'fw' ),
		'type'    => 'tab',
		'options' => array(
			'group_text' => array(
				'type'    => 'group',
				'options' => array(
					'tag_text' => array(
						'type'  => 'text',
						'label' => __( 'Sub-tag', 'fw' ),
						'value' => 'New',
						'desc'  => __( 'The small leading badge inside the pill (e.g. "New", "Beta", "Pro"). Leave empty to omit it.', 'fw' ),
					),
					'message' => array(
						'type'  => 'text',
						'label' => __( 'Message', 'fw' ),
						'value' => 'We just shipped v2.0',
						'desc'  => __( 'The main pill text.', 'fw' ),
					),
					'link' => array(
						'type'  => 'text',
						'label' => __( 'Link (optional)', 'fw' ),
						'value' => '',
						'desc'  => __( 'Make the whole pill a link (e.g. to release notes). A full "https://" URL on another domain opens in a new tab automatically. Leave empty for a non-clickable pill.', 'fw' ),
					),
				),
			),
			'group_icons' => array(
				'type'    => 'group',
				'options' => array(
					'leading' => array(
						'type'    => 'select',
						'label'   => __( 'Leading Marker', 'fw' ),
						'value'   => 'none',
						'choices' => array(
							'none'  => __( 'None', 'fw' ),
							'dot'   => __( 'Dot', 'fw' ),
							'pulse' => __( 'Pulse Dot (live)', 'fw' ),
							'icon'  => __( 'Icon', 'fw' ),
						),
						'desc'    => __( 'A small marker before the sub-tag. "Pulse Dot" animates — ideal for a "live / now available" pill. "Icon" uses the Leading Icon below.', 'fw' ),
					),
					'leading_icon' => array(
						'type'         => 'icon-v2',
						'label'        => __( 'Leading Icon', 'fw' ),
						'preview_size' => 'small',
						'modal_size'   => 'medium',
						'desc'         => __( 'Used only when Leading Marker = Icon.', 'fw' ),
					),
					'trailing_icon' => array(
						'type'         => 'icon-v2',
						'label'        => __( 'Trailing Icon', 'fw' ),
						'preview_size' => 'small',
						'modal_size'   => 'medium',
						'desc'         => __( 'Optional icon after the message — e.g. an arrow on a linked pill.', 'fw' ),
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
					'style' => array(
						'type'    => 'image-picker',
						'label'   => __( 'Style', 'fw' ),
						'help'    => __( 'Soft = tinted fill; Outline = bordered; Solid = filled; Subtle = light grey; Ghost = text only; Gradient = two-colour fade; Glass = frosted blur. Colours come from the Styling tab.', 'fw' ),
						'value'   => 'soft',
						'choices' => array(
							'soft'     => $ap_pick( 'soft',     __( 'Soft', 'fw' ) ),
							'outline'  => $ap_pick( 'outline',  __( 'Outline', 'fw' ) ),
							'solid'    => $ap_pick( 'solid',    __( 'Solid', 'fw' ) ),
							'subtle'   => $ap_pick( 'subtle',   __( 'Subtle', 'fw' ) ),
							'ghost'    => $ap_pick( 'ghost',    __( 'Ghost', 'fw' ) ),
							'gradient' => $ap_pick( 'gradient', __( 'Gradient', 'fw' ) ),
							'glass'    => $ap_pick( 'glass',    __( 'Glass', 'fw' ) ),
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
					),
					'tag_style' => array(
						'type'    => 'select',
						'label'   => __( 'Sub-tag Style', 'fw' ),
						'value'   => 'filled',
						'choices' => array(
							'filled'  => __( 'Filled', 'fw' ),
							'soft'    => __( 'Soft', 'fw' ),
							'outline' => __( 'Outline', 'fw' ),
							'none'    => __( 'No box (plain text)', 'fw' ),
						),
						'desc'    => __( 'How the leading sub-tag is drawn.', 'fw' ),
					),
					'hover' => array(
						'type'    => 'select',
						'label'   => __( 'Hover Effect', 'fw' ),
						'value'   => 'lift',
						'choices' => array(
							'none'  => __( 'None', 'fw' ),
							'lift'  => __( 'Lift', 'fw' ),
							'glow'  => __( 'Glow', 'fw' ),
							'slide' => __( 'Arrow slide', 'fw' ),
						),
						'desc'    => __( 'Hover feedback (most noticeable on a linked pill). "Arrow slide" nudges the trailing icon.', 'fw' ),
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
					'pill_color' => sc_color_field_compact( array(
						'label' => __( 'Pill Color', 'fw' ),
						'desc'  => __( 'Drives the fill / border / text of the pill. A Color Preset (recommended) follows your brand; or a custom colour. Empty = neutral grey.', 'fw' ),
					) ),
					'text_color' => sc_color_field_compact( array(
						'label' => __( 'Message Color', 'fw' ),
						'desc'  => __( 'Override the message text colour. Empty = derived from the Pill Color / style.', 'fw' ),
					) ),
					'tag_color' => sc_color_field_compact( array(
						'label' => __( 'Sub-tag Color', 'fw' ),
						'desc'  => __( 'Colour of the leading sub-tag. Empty = the Pill Color.', 'fw' ),
					) ),
				),
			),
			'group_gradient' => array(
				'type'    => 'group',
				'options' => array(
					'gradient_from' => sc_color_field_compact( array(
						'label' => __( 'Gradient Start', 'fw' ),
						'desc'  => __( 'Used only by the Gradient style. Empty = the Pill Color.', 'fw' ),
					) ),
					'gradient_to' => sc_color_field_compact( array(
						'label' => __( 'Gradient End', 'fw' ),
						'desc'  => __( 'Used only by the Gradient style.', 'fw' ),
					) ),
				),
			),
			'group_spacings' => array(
				'type'    => 'group',
				'options' => array(
					'spacing' => array(
						'type'  => 'spacing',
						'label' => __( 'Margin & Padding', 'fw' ),
						'desc'  => __( 'Margin positions the pill; padding is usually best left to the chosen Size.', 'fw' ),
						'help'  => sc_styling_help_text( 'spacing' ),
					),
				),
			),
		),
	),

	/* ========================== LINK & SEO ========================== */
	'tab_seo' => array(
		'title'   => __( 'Link & SEO', 'fw' ),
		'type'    => 'tab',
		'options' => array(
			'group_link' => array(
				'type'    => 'group',
				'options' => array(
					'link_target' => array(
						'type'    => 'select',
						'label'   => __( 'Open Link In', 'fw' ),
						'value'   => 'auto',
						'choices' => array(
							'auto'   => __( 'Auto (new tab for external links)', 'fw' ),
							'_self'  => __( 'Same tab', 'fw' ),
							'_blank' => __( 'New tab', 'fw' ),
						),
						'desc'    => __( 'External links always get rel="noopener noreferrer" for security.', 'fw' ),
					),
					'rel_nofollow' => array(
						'type'         => 'switch',
						'label'        => __( 'rel="nofollow"', 'fw' ),
						'desc'         => __( 'Tell search engines not to pass ranking credit through this link.', 'fw' ),
						'right-choice' => array( 'value' => 'yes', 'label' => __( 'On', 'fw' ) ),
						'left-choice'  => array( 'value' => 'no',  'label' => __( 'Off', 'fw' ) ),
						'value'        => 'no',
					),
					'rel_sponsored' => array(
						'type'         => 'switch',
						'label'        => __( 'rel="sponsored"', 'fw' ),
						'desc'         => __( 'Mark paid / affiliate / advertisement links (Google requires labelling these).', 'fw' ),
						'right-choice' => array( 'value' => 'yes', 'label' => __( 'On', 'fw' ) ),
						'left-choice'  => array( 'value' => 'no',  'label' => __( 'Off', 'fw' ) ),
						'value'        => 'no',
					),
					'rel_ugc' => array(
						'type'         => 'switch',
						'label'        => __( 'rel="ugc"', 'fw' ),
						'desc'         => __( 'Mark a user-generated-content link.', 'fw' ),
						'right-choice' => array( 'value' => 'yes', 'label' => __( 'On', 'fw' ) ),
						'left-choice'  => array( 'value' => 'no',  'label' => __( 'Off', 'fw' ) ),
						'value'        => 'no',
					),
				),
			),
			'group_a11y' => array(
				'type'    => 'group',
				'options' => array(
					'aria_label' => array(
						'type'  => 'text',
						'label' => __( 'Accessible Label (aria-label)', 'fw' ),
						'value' => '',
						'desc'  => __( 'A fuller description for screen readers when the visible text is terse (e.g. "Read the v2.0 release notes"). Leave empty to use the visible message.', 'fw' ),
					),
					'title_attr' => array(
						'type'  => 'text',
						'label' => __( 'Tooltip (title)', 'fw' ),
						'value' => '',
						'desc'  => __( 'Optional native hover tooltip.', 'fw' ),
					),
				),
			),
			'group_dismiss' => array(
				'type'    => 'group',
				'options' => array(
					'dismissible' => array(
						'type'         => 'switch',
						'label'        => __( 'Dismissible', 'fw' ),
						'desc'         => __( 'Add a × button that hides the pill and remembers the choice in this browser.', 'fw' ),
						'right-choice' => array( 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ),
						'left-choice'  => array( 'value' => 'no',  'label' => __( 'No', 'fw' ) ),
						'value'        => 'no',
					),
					'dismiss_id' => array(
						'type'  => 'text',
						'label' => __( 'Dismiss Key', 'fw' ),
						'value' => '',
						'desc'  => __( 'A unique id for this pill so its dismissal is remembered independently (e.g. "v2-launch"). Required for Dismissible.', 'fw' ),
					),
				),
			),
			'group_schema' => array(
				'type'    => 'group',
				'options' => array(
					'schema_enable' => array(
						'type'         => 'switch',
						'label'        => __( 'Announcement structured data', 'fw' ),
						'desc'         => __( 'Emit schema.org SpecialAnnouncement JSON-LD. Use ONLY for a genuine announcement — misused structured data can hurt SEO. Default off.', 'fw' ),
						'right-choice' => array( 'value' => 'yes', 'label' => __( 'On', 'fw' ) ),
						'left-choice'  => array( 'value' => 'no',  'label' => __( 'Off', 'fw' ) ),
						'value'        => 'no',
					),
					'schema_name' => array(
						'type'  => 'text',
						'label' => __( 'Announcement Name', 'fw' ),
						'value' => '',
						'desc'  => __( 'Used when structured data is on. Empty = the message text.', 'fw' ),
					),
					'schema_date' => array(
						'type'  => 'text',
						'label' => __( 'Date Posted', 'fw' ),
						'value' => '',
						'desc'  => __( 'ISO date, e.g. 2026-06-28. Used when structured data is on.', 'fw' ),
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
