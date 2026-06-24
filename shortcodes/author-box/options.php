<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/* User dropdown (built in admin context). */
$ab_users = array( '' => __( '— select a user —', 'fw' ) );
if ( function_exists( 'get_users' ) ) {
	foreach ( get_users( array( 'fields' => array( 'ID', 'display_name' ), 'number' => 200, 'orderby' => 'display_name' ) ) as $u ) {
		$ab_users[ (string) $u->ID ] = $u->display_name . ' (#' . $u->ID . ')';
	}
}

/* Social network choices from the catalog. */
$ab_socials = require dirname( __FILE__ ) . '/views/parts/socials.php';
$ab_net_choices = array();
foreach ( (array) $ab_socials as $k => $n ) { $ab_net_choices[ $k ] = $n['label']; }

$options = array(

	/* ========================== CONTENT ========================== */
	'tab_content' => array(
		'title'   => __( 'Content', 'fw' ),
		'type'    => 'tab',
		'options' => array(
			'group_source' => array(
				'type'    => 'group',
				'options' => array(
					'source' => array(
						'type'    => 'select',
						'label'   => __( 'Author Source', 'fw' ),
						'value'   => 'current',
						'choices' => array(
							'current' => __( 'Current post author', 'fw' ),
							'user'    => __( 'Specific user', 'fw' ),
							'custom'  => __( 'Custom (manual)', 'fw' ),
						),
					),
					'user_id' => array(
						'type'    => 'select',
						'label'   => __( 'User', 'fw' ),
						'value'   => '',
						'choices' => $ab_users,
						'desc'    => __( 'Used when Author Source is "Specific user".', 'fw' ),
					),
				),
			),
			'group_custom' => array(
				'type'    => 'group',
				'title'   => __( 'Custom / overrides', 'fw' ),
				'options' => array(
					'name' => array(
						'type'  => 'text',
						'label' => __( 'Name', 'fw' ),
						'desc'  => __( 'Custom name, or overrides the user\'s name.', 'fw' ),
					),
					'role' => array(
						'type'  => 'text',
						'label' => __( 'Role / Tagline', 'fw' ),
					),
					'bio' => array(
						'type'  => 'textarea',
						'label' => __( 'Bio', 'fw' ),
						'desc'  => __( 'Custom bio, or overrides the user\'s biographical info.', 'fw' ),
					),
					'avatar' => array(
						'type'  => 'upload',
						'label' => __( 'Avatar', 'fw' ),
						'desc'  => __( 'Custom avatar, or overrides the user\'s Gravatar.', 'fw' ),
					),
				),
			),
			'group_socials' => array(
				'type'    => 'group',
				'options' => array(
					'socials' => array(
						'type'          => 'addable-popup',
						'label'         => __( 'Social / Profile Links', 'fw' ),
						'popup-title'   => __( 'Add / Edit Link', 'fw' ),
						'template'      => '{{= network }}',
						'popup-options' => array(
							'network' => array(
								'type'    => 'select',
								'label'   => __( 'Network', 'fw' ),
								'value'   => 'website',
								'choices' => $ab_net_choices,
							),
							'url' => array(
								'type'  => 'text',
								'label' => __( 'URL', 'fw' ),
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
						$registry = require dirname( __FILE__ ) . '/views/parts/registry.php';
						$base     = fw_ext( 'shortcodes' )->get_declared_URI( '/shortcodes/author-box/static/img/design' );
						$choices  = array();
						foreach ( (array) $registry as $key => $meta ) {
							$choices[ $key ] = array( 'small' => array(
								'src'    => $base . '/' . ( isset( $meta['thumb'] ) ? $meta['thumb'] : $key . '.svg' ),
								'height' => 60,
								'title'  => isset( $meta['label'] ) ? $meta['label'] : $key,
							) );
						}
						return array(
							'type'    => 'image-picker',
							'label'   => __( 'Design', 'fw' ),
							'value'   => 'card',
							'choices' => $choices,
						);
					} ),
				),
			),
			'group_options' => array(
				'type'    => 'group',
				'options' => array(
					'avatar_shape' => array(
						'type'    => 'select',
						'label'   => __( 'Avatar Shape', 'fw' ),
						'value'   => 'circle',
						'choices' => array( 'circle' => __( 'Circle', 'fw' ), 'rounded' => __( 'Rounded', 'fw' ), 'square' => __( 'Square', 'fw' ) ),
					),
					'avatar_size' => array(
						'type'  => 'slider',
						'label' => __( 'Avatar Size (px)', 'fw' ),
						'value' => 84,
						'properties' => array( 'min' => 48, 'max' => 160, 'step' => 4 ),
					),
					'show_posts' => array(
						'type'  => 'switch',
						'label' => __( 'Show "View all posts" Link', 'fw' ),
						'right-choice' => array( 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ),
						'left-choice'  => array( 'value' => 'no',  'label' => __( 'No', 'fw' ) ),
						'value' => 'yes',
						'desc'  => __( 'Links to the author archive (skipped for custom authors).', 'fw' ),
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
					'accent_color' => sc_color_field_compact( array( 'label' => __( 'Accent (links / socials)', 'fw' ), 'kind' => 'bg' ) ),
					'card_bg'      => sc_color_field_compact( array( 'label' => __( 'Card Background', 'fw' ), 'kind' => 'bg' ) ),
					'name_color'   => sc_color_field_compact( array( 'label' => __( 'Name Color', 'fw' ) ) ),
					'text_color'   => sc_color_field_compact( array( 'label' => __( 'Text Color', 'fw' ) ) ),
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
