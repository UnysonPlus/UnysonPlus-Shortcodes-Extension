<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/* Public post types for the Source dropdown (built in admin context). */
$pc_post_types = array();
if ( function_exists( 'get_post_types' ) ) {
	foreach ( get_post_types( array( 'public' => true ), 'objects' ) as $pt ) {
		if ( in_array( $pt->name, array( 'attachment' ), true ) ) { continue; }
		$pc_post_types[ $pt->name ] = $pt->labels->name . ' (' . $pt->name . ')';
	}
}
if ( empty( $pc_post_types ) ) { $pc_post_types = array( 'post' => 'Posts' ); }

$options = array(

	/* ========================== QUERY ========================== */
	'tab_query' => array(
		'title'   => __( 'Query', 'fw' ),
		'type'    => 'tab',
		'options' => array(
			'group' => array(
				'type'    => 'group',
				'options' => array(
					'post_type' => array(
						'type'    => 'select',
						'label'   => __( 'Source', 'fw' ),
						'value'   => 'post',
						'choices' => $pc_post_types,
					),
					'taxonomy' => array(
						'type'  => 'text',
						'label' => __( 'Taxonomy', 'fw' ),
						'value' => 'category',
						'desc'  => __( 'Taxonomy to filter by, e.g. category, post_tag, or a custom taxonomy.', 'fw' ),
					),
					'terms' => array(
						'type'  => 'text',
						'label' => __( 'Terms', 'fw' ),
						'desc'  => __( 'Comma-separated term slugs to include. Blank = all.', 'fw' ),
					),
					'number' => array(
						'type'  => 'text',
						'label' => __( 'Number of Posts', 'fw' ),
						'value' => '9',
					),
					'orderby' => array(
						'type'    => 'select',
						'label'   => __( 'Order By', 'fw' ),
						'value'   => 'date',
						'choices' => array(
							'date'          => __( 'Date', 'fw' ),
							'title'         => __( 'Title', 'fw' ),
							'menu_order'    => __( 'Menu order', 'fw' ),
							'rand'          => __( 'Random', 'fw' ),
							'comment_count' => __( 'Comment count', 'fw' ),
						),
					),
					'order' => array(
						'type'    => 'select',
						'label'   => __( 'Order', 'fw' ),
						'value'   => 'DESC',
						'choices' => array( 'DESC' => __( 'Descending', 'fw' ), 'ASC' => __( 'Ascending', 'fw' ) ),
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
						$base     = fw_ext( 'shortcodes' )->get_declared_URI( '/shortcodes/post-carousel/static/img/design' );
						$choices  = array();
						foreach ( (array) $registry as $key => $meta ) {
							$choices[ $key ] = array( 'small' => array(
								'src'    => $base . '/' . ( isset( $meta['thumb'] ) ? $meta['thumb'] : $key . '.svg' ),
								'height' => 64,
								'title'  => isset( $meta['label'] ) ? $meta['label'] : $key,
							) );
						}
						return array(
							'type'    => 'image-picker',
							'label'   => __( 'Card Design', 'fw' ),
							'value'   => 'standard',
							'choices' => $choices,
						);
					} ),
				),
			),
			'group_card' => array(
				'type'    => 'group',
				'options' => array(
					'image_ratio' => array(
						'type'    => 'select',
						'label'   => __( 'Image Ratio', 'fw' ),
						'value'   => 'ratio-16-9',
						'choices' => array(
							'original'   => __( 'Original', 'fw' ),
							'ratio-16-9' => __( '16:9', 'fw' ),
							'ratio-4-3'  => __( '4:3', 'fw' ),
							'ratio-1-1'  => __( '1:1', 'fw' ),
							'ratio-3-4'  => __( '3:4 (portrait)', 'fw' ),
						),
					),
					'show_excerpt' => array(
						'type'  => 'switch',
						'label' => __( 'Show Excerpt', 'fw' ),
						'right-choice' => array( 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ),
						'left-choice'  => array( 'value' => 'no',  'label' => __( 'No', 'fw' ) ),
						'value' => 'yes',
					),
					'excerpt_length' => array(
						'type'  => 'text',
						'label' => __( 'Excerpt Words', 'fw' ),
						'value' => '18',
					),
					'show_date' => array(
						'type'  => 'switch',
						'label' => __( 'Show Date', 'fw' ),
						'right-choice' => array( 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ),
						'left-choice'  => array( 'value' => 'no',  'label' => __( 'No', 'fw' ) ),
						'value' => 'yes',
					),
					'show_meta' => array(
						'type'  => 'switch',
						'label' => __( 'Show Author / Category', 'fw' ),
						'right-choice' => array( 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ),
						'left-choice'  => array( 'value' => 'no',  'label' => __( 'No', 'fw' ) ),
						'value' => 'no',
					),
					'readmore' => array(
						'type'  => 'text',
						'label' => __( 'Read More Text', 'fw' ),
						'value' => __( 'Read More', 'fw' ),
						'desc'  => __( 'Blank to hide the read-more link.', 'fw' ),
					),
				),
			),
			'group_carousel' => array(
				'type'    => 'group',
				'options' => array(
					'per_view' => array(
						'type'    => 'select',
						'label'   => __( 'Slides per View', 'fw' ),
						'value'   => '3',
						'choices' => array( '1' => '1', '2' => '2', '3' => '3', '4' => '4' ),
					),
					'gap' => array(
						'type'    => 'select',
						'label'   => __( 'Gap', 'fw' ),
						'value'   => '4',
						'choices' => function_exists( 'sc_get_gap_select_choices' ) ? sc_get_gap_select_choices( __( 'None', 'fw' ) ) : array( '4' => '4' ),
						'help'    => function_exists( 'sc_styling_help_text' ) ? sc_styling_help_text( 'spacing' ) : '',
					),
					'autoplay' => array(
						'type'  => 'switch',
						'label' => __( 'Autoplay', 'fw' ),
						'right-choice' => array( 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ),
						'left-choice'  => array( 'value' => 'no',  'label' => __( 'No', 'fw' ) ),
						'value' => 'no',
					),
					'loop' => array(
						'type'  => 'switch',
						'label' => __( 'Loop', 'fw' ),
						'right-choice' => array( 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ),
						'left-choice'  => array( 'value' => 'no',  'label' => __( 'No', 'fw' ) ),
						'value' => 'yes',
					),
					'arrows' => array(
						'type'  => 'switch',
						'label' => __( 'Arrows', 'fw' ),
						'right-choice' => array( 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ),
						'left-choice'  => array( 'value' => 'no',  'label' => __( 'No', 'fw' ) ),
						'value' => 'yes',
					),
					'dots' => array(
						'type'  => 'switch',
						'label' => __( 'Dots', 'fw' ),
						'right-choice' => array( 'value' => 'yes', 'label' => __( 'Yes', 'fw' ) ),
						'left-choice'  => array( 'value' => 'no',  'label' => __( 'No', 'fw' ) ),
						'value' => 'yes',
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
					'image_style'  => function_exists( 'sc_image_style_field' )
						? sc_image_style_field()
						: array( 'type' => 'select', 'label' => __( 'Image Style', 'fw' ), 'value' => '', 'choices' => array( '' => __( 'None', 'fw' ) ) ),
					'accent_color' => sc_color_field_compact( array( 'label' => __( 'Accent (links / meta)', 'fw' ), 'kind' => 'bg' ) ),
					'card_bg'      => sc_color_field_compact( array( 'label' => __( 'Card Background', 'fw' ), 'kind' => 'bg' ) ),
					'title_color'  => sc_color_field_compact( array( 'label' => __( 'Title Color', 'fw' ) ) ),
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
