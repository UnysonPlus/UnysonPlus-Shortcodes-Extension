<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/*
 * Row-preview template for each Item in the addable-popup list. Underscore
 * template (evaluated client-side against the item's saved values), so it can
 * render the chosen icon-v2 value inline before the text — mirroring the four
 * icon types the frontend supports (font glyph, uploaded image, emoji, inline
 * SVG). Font glyphs rely on the pack CSS already loaded by the icon picker in
 * the builder; if a glyph can't resolve, the text still shows.
 */
$fl_item_template = <<<'TPL'
{{ var _ic = (typeof icon !== "undefined" && icon) ? icon : null; var _im = "";
   if (_ic && _ic.type) {
     if (_ic.type === "icon-font" && _ic["icon-class"]) {
       _im = '<i class="' + _ic["icon-class"] + '" style="margin-right:8px;opacity:.75;" aria-hidden="true"></i>';
     } else if (_ic.type === "custom-upload" && _ic.url) {
       _im = '<img src="' + _ic.url + '" alt="" style="width:16px;height:16px;object-fit:contain;margin-right:8px;vertical-align:middle;">';
     } else if (_ic.type === "emoji" && _ic.char) {
       _im = '<span style="margin-right:8px;" aria-hidden="true">' + _ic.char + '</span>';
     } else if (_ic.type === "svg" && _ic.markup) {
       _im = '<span class="fw-fl-item-svg" style="display:inline-block;width:16px;height:16px;margin-right:8px;vertical-align:middle;line-height:0;">' + _ic.markup + '</span>';
     } else if (_ic.type === "svg" && _ic.url) {
       _im = '<img src="' + _ic.url + '" alt="" style="width:16px;height:16px;object-fit:contain;margin-right:8px;vertical-align:middle;">';
     }
   }
}}{{= _im }}{{- text }}
TPL;

$options = array(

	/* ========================== CONTENT ========================== */
	'tab_content' => array(
		'title'   => __( 'Content', 'fw' ),
		'type'    => 'tab',
		'options' => array(
			'group' => array(
				'type'    => 'group',
				'options' => array(
					'items' => array(
						'type'          => 'addable-popup',
						'label'         => __( 'Items', 'fw' ),
						'popup-title'   => __( 'Add / Edit Item', 'fw' ),
						'template'      => $fl_item_template,
						'popup-options' => array(
							'text' => array(
								'type'  => 'text',
								'label' => __( 'Text', 'fw' ),
								'value' => __( 'Feature item', 'fw' ),
							),
							'subtext' => array(
								'type'  => 'text',
								'label' => __( 'Sub-text', 'fw' ),
								'desc'  => __( 'Optional smaller line beneath the text.', 'fw' ),
							),
							'value_text' => array(
								'type'  => 'text',
								'label' => __( 'Value', 'fw' ),
								'desc'  => __( 'Optional right-aligned value on the same row (e.g. "96%", "24h"). Great for spec / stat rows.', 'fw' ),
							),
							'icon' => array(
								'type'         => 'icon',
								'label'        => __( 'Icon', 'fw' ),
								'preview_size' => 'small',
								'desc'         => __( 'Used by the "Per-item icons" / "Badge" designs.', 'fw' ),
							),
							'marker_color' => sc_color_field_compact( array(
								'label' => __( 'Marker Color', 'fw' ),
								'kind'  => 'bg',
								'desc'  => __( 'Override the list marker color for this item only (e.g. a red cross). Blank inherits the list Marker Color.', 'fw' ),
							) ),
							'state' => array(
								'type'    => 'select',
								'label'   => __( 'Check State', 'fw' ),
								'value'   => 'on',
								'choices' => array(
									'on'  => __( 'Available (check)', 'fw' ),
									'off' => __( 'Unavailable (cross)', 'fw' ),
								),
								'desc' => __( 'Used by the Checklist design.', 'fw' ),
							),
							'link_url' => array(
								'type'  => 'text',
								'label' => __( 'Link URL', 'fw' ),
								'desc'  => __( 'Optional — makes the item a link.', 'fw' ),
							),
							'link_target' => array(
								'type'  => 'switch',
								'label' => __( 'Open in New Tab', 'fw' ),
								'right-choice' => array( 'value' => '_blank', 'label' => __( 'Yes', 'fw' ) ),
								'left-choice'  => array( 'value' => '_self', 'label' => __( 'No', 'fw' ) ),
								'value' => '_self',
							),
						),
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
			'group_design' => array(
				'type'    => 'group',
				'options' => array(
					'design' => call_user_func( function () {
						if ( function_exists( 'fw_sc_design_picker_choices' ) ) {
							$choices = fw_sc_design_picker_choices( 'feature_list' );
						} else {
							$registry = require dirname( __FILE__ ) . '/views/parts/registry.php';
							$base     = fw_ext( 'shortcodes' )->get_declared_URI( '/shortcodes/feature-list/static/img/design' );
							$choices  = array();
							foreach ( (array) $registry as $key => $meta ) {
								$choices[ $key ] = array( 'small' => array(
									'src'    => $base . '/' . ( isset( $meta['thumb'] ) ? $meta['thumb'] : $key . '.svg' ),
									'height' => 60,
									'title'  => isset( $meta['label'] ) ? $meta['label'] : $key,
								) );
							}
						}
						return array(
							'type'    => 'image-picker',
							'label'   => __( 'Default Marker', 'fw' ),
							'desc'    => __( 'The fallback marker for items with no icon. A per-item Icon (Content tab) always overrides this — so add an icon to an item and it shows, no matter the marker. "Icons only" uses each item\'s icon with no fallback; "Checkmark + icon" shows both.', 'fw' ),
							'value'   => 'check',
							'choices' => $choices,
						);
					} ),
				),
			),
			'group_layout' => array(
				'type'    => 'group',
				'options' => array(
					'orientation' => call_user_func( function () {
						$base = fw_ext( 'shortcodes' )->get_declared_URI( '/shortcodes/feature-list/static/img/design' );
						$tile = function ( $file, $title ) use ( $base ) {
							return array( 'small' => array( 'src' => $base . '/' . $file, 'height' => 60, 'title' => $title ) );
						};
						return array(
							'type'    => 'image-picker',
							'label'   => __( 'Orientation', 'fw' ),
							'value'   => 'vertical',
							'choices' => array(
								'vertical'   => $tile( 'orient-vertical.svg',   __( 'Vertical list', 'fw' ) ),
								'horizontal' => $tile( 'orient-horizontal.svg', __( 'Horizontal strip', 'fw' ) ),
							),
							'desc'    => __( 'Vertical stacks the items in rows (using the Columns grid). Horizontal flows them in a wrapping inline row — a feature / trust strip.', 'fw' ),
						);
					} ),
					'icon_position' => array(
						'type'    => 'select',
						'label'   => __( 'Icon Position', 'fw' ),
						'value'   => 'left',
						'choices' => array(
							'left' => __( 'Left of text', 'fw' ),
							'top'  => __( 'Above text (centered)', 'fw' ),
						),
					),
					'icon_style' => array(
						'type'    => 'select',
						'label'   => __( 'Icon Style', 'fw' ),
						'value'   => 'plain',
						'choices' => array(
							'plain'   => __( 'Plain', 'fw' ),
							'tint'    => __( 'Soft tint', 'fw' ),
							'circle'  => __( 'Solid circle', 'fw' ),
							'outline' => __( 'Outline', 'fw' ),
							'square'  => __( 'Square badge', 'fw' ),
						),
						'desc'    => __( 'Chip drawn around the Checklist / Per-item icon markers.', 'fw' ),
					),
					'columns' => array(
						'type'    => 'select',
						'label'   => __( 'Columns', 'fw' ),
						'value'   => '1',
						'choices' => array( '1' => '1', '2' => '2', '3' => '3' ),
						'desc'    => __( 'Applies to the Vertical orientation only.', 'fw' ),
					),
					'dividers' => array(
						'type'  => 'switch',
						'label' => __( 'Row Dividers', 'fw' ),
						'right-choice' => array( 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ),
						'left-choice'  => array( 'value' => 'no',  'label' => __( 'No', 'fw' ) ),
						'value' => 'no',
					),
					'zebra' => array(
						'type'  => 'switch',
						'label' => __( 'Alternating Rows', 'fw' ),
						'right-choice' => array( 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ),
						'left-choice'  => array( 'value' => 'no',  'label' => __( 'No', 'fw' ) ),
						'value' => 'no',
						'desc'  => __( 'Zebra-stripe the rows (Vertical orientation).', 'fw' ),
					),
					'spacing_size' => array(
						'type'    => 'select',
						'label'   => __( 'Row Spacing', 'fw' ),
						'value'   => 'md',
						'choices' => array( 'sm' => __( 'Tight', 'fw' ), 'md' => __( 'Normal', 'fw' ), 'lg' => __( 'Roomy', 'fw' ) ),
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
					'box_style'    => sc_card_box_style_field( array( 'desc' => __( 'Apply a Box Preset to each feature item. Manage presets in Theme Settings → Components → Box Presets.', 'fw' ) ) ),
					'icon_badge_preset' => sc_icon_badge_preset_field( array( 'desc' => __( 'Apply a reusable Icon Badge — a shaped tile (fill, border, corners, shadow) with its own icon colour + size — to EVERY item\'s icon. Manage presets in Theme Settings → Components → Icon Badges.', 'fw' ) ) ),
					'marker_color' => sc_color_field_compact( array( 'label' => __( 'Marker Color', 'fw' ), 'kind' => 'bg' ) ),
					'marker_size'  => array(
						'type'  => 'unit-input',
						'label' => __( 'Icon Size', 'fw' ),
						'desc'  => __( 'Marker / icon size (Ex: 20px, 1.5rem). Leave empty for the default.', 'fw' ),
						'value' => array( 'value' => '', 'unit' => 'px' ),
						'units' => array( 'px', 'rem', 'em' ),
					),
					'text_color'   => sc_color_field_compact( array( 'label' => __( 'Text Color', 'fw' ) ) ),
					'sub_color'    => sc_color_field_compact( array( 'label' => __( 'Sub-text Color', 'fw' ) ) ),
					'font_size_preset' => sc_font_size_field(),
				),
			),
			'group_spacings' => array(
				'type'    => 'group',
				'options' => array(
					'spacing' => array(
						'type'  => 'spacing',
						'label' => __( 'Margin & Padding', 'fw' ),
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
