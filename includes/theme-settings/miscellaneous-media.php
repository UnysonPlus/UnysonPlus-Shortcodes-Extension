<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Theme Settings → Miscellaneous → Media (Custom Image Sizes).
 *
 * A framework-level feature (provided to any Unyson+ theme), moved here from the
 * theme so the option schema AND its behaviour (add_image_size registration +
 * the image_size_names_choose picker filter, in miscellaneous-handlers.php) live
 * together in the plugin. Stored under the SAME `theme_image_sizes` key the theme
 * used, read via fw_get_db_settings_option(), so existing values carry over with
 * NO migration.
 *
 * @var array $options Filled with the option schema (loaded via upw_ts_get_options()).
 */

$note_html = '<div class="unysonplus-note" style="padding:12px 14px;background:#f0f6fc;border:1px solid #c5d9ed;border-radius:6px;font-size:13px;line-height:1.5;">'
	. '<strong>' . esc_html__( 'How custom image sizes work', 'fw' ) . '</strong><br>'
	. esc_html__( 'Each size below is registered with WordPress. Turn on "Show in editor" to make a size selectable in the media library and block editor image-size dropdowns.', 'fw' ) . '<br>'
	. '<em>' . esc_html__( 'New sizes only apply to images uploaded afterwards. To apply a size to existing images, regenerate thumbnails — e.g. the free "Regenerate Thumbnails" plugin, or WP-CLI: wp media regenerate.', 'fw' ) . '</em>'
	. '</div>';

$options = array(
	'image_sizes_note' => array(
		'type'  => 'html-full',
		'label' => false,
		'html'  => $note_html,
	),
	'theme_image_sizes' => array(
		'label'       => false,
		'type'        => 'addable-box',
		'value'       => array(
			array(
				'name'           => 'Custom Size 1',
				'width'          => 450,
				'height'         => 250,
				'crop'           => false,
				'show_in_editor' => 'yes',
			),
		),
		'box-options' => array(
			'name'   => array(
				'label'           => __( 'Name', 'fw' ),
				'desc'            => __( 'Shown in the size dropdown; the CSS-safe slug is derived from it. Avoid the reserved names Thumbnail / Medium / Large / Medium Large.', 'fw' ),
				'type'            => 'text',
				'value'           => '',
				'dynamic_content' => false,
			),
			'width'  => array(
				'label'           => __( 'Width (px)', 'fw' ),
				'type'            => 'text',
				'value'           => '',
				'dynamic_content' => false,
			),
			'height' => array(
				'label'           => __( 'Height (px)', 'fw' ),
				'type'            => 'text',
				'value'           => '',
				'dynamic_content' => false,
			),
			'show_in_editor' => array(
				'label'        => __( 'Show in editor', 'fw' ),
				'desc'         => __( 'Make this size selectable in the media library / block editor image-size dropdown. Turn off to keep it for template code only.', 'fw' ),
				'type'         => 'switch',
				'value'        => 'yes',
				'right-choice' => array( 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ),
				'left-choice'  => array( 'value' => 'no',  'label' => __( 'No', 'fw' ) ),
			),
			'crop'   => array(
				'label'   => __( 'Crop', 'fw' ),
				'type'    => 'select',
				'value'   => 'false',
				'choices' => array(
					'false'         => __( 'No Crop', 'fw' ),
					'true'          => __( 'Cropped', 'fw' ),
					'top-left'      => __( 'Top Left', 'fw' ),
					'top-center'    => __( 'Top Center', 'fw' ),
					'top-right'     => __( 'Top Right', 'fw' ),
					'center-left'   => __( 'Center Left', 'fw' ),
					'center'        => __( 'Center', 'fw' ),
					'center-right'  => __( 'Center Right', 'fw' ),
					'bottom-left'   => __( 'Bottom Left', 'fw' ),
					'bottom-center' => __( 'Bottom Center', 'fw' ),
					'bottom-right'  => __( 'Bottom Right', 'fw' ),
				),
			),
		),
		'template' => '{{- name }}: {{- width }} x {{- height }}',
	),
);
