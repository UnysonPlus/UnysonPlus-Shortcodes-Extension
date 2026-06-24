<?php
if ( ! defined( 'FW' ) ) { die( 'Forbidden' ); }

$pt_tax_choices = array();
foreach ( get_taxonomies( array( 'public' => true ), 'objects' ) as $pt_tax_slug => $pt_tax_obj ) {
	$pt_tax_choices[ $pt_tax_slug ] = $pt_tax_obj->labels->name;
}
if ( empty( $pt_tax_choices ) ) {
	$pt_tax_choices = array( 'category' => __( 'Categories', 'fw' ), 'post_tag' => __( 'Tags', 'fw' ) );
}

$options = [
	'tab_content' => [
		'title'   => __( 'Content', 'fw' ),
		'type'    => 'tab',
		'options' => [
			'group_content' => [
				'type'    => 'group',
				'options' => [
					'taxonomy' => [
						'type'    => 'select',
						'label'   => __( 'Taxonomy', 'fw' ),
						'desc'    => __( 'Which terms to show (Categories, Tags, or a custom taxonomy).', 'fw' ),
						'value'   => isset( $pt_tax_choices['category'] ) ? 'category' : key( $pt_tax_choices ),
						'choices' => $pt_tax_choices,
					],
					'term_prefix' => [
						'type'  => 'text',
						'label' => __( 'Prefix', 'fw' ),
						'desc'  => __( 'Text before the terms, e.g. "Filed under:".', 'fw' ),
						'value' => '',
					],
					'term_separator' => [
						'type'  => 'text',
						'label' => __( 'Separator', 'fw' ),
						'value' => ', ',
					],
					'link_terms' => [
						'type'         => 'switch',
						'label'        => __( 'Link Terms', 'fw' ),
						'value'        => 'yes',
						'right-choice' => [ 'value' => 'yes', 'label' => __( 'On', 'fw' ) ],
						'left-choice'  => [ 'value' => 'no',  'label' => __( 'Off', 'fw' ) ],
					],
					'text_align' => sc_alignment_field( [
						'label'   => __( 'Alignment', 'fw' ),
						'inherit' => true,
					] ),
				],
			],
		],
	],
	'tab_styling' => [
		'title'   => __( 'Styling', 'fw' ),
		'type'    => 'tab',
		'options' => [
			'group_styling' => [
				'type'    => 'group',
				'options' => [
					'text_color'       => sc_color_field_compact( [ 'label' => __( 'Text Color', 'fw' ), 'kind' => 'text' ] ),
					'font_size_preset' => sc_font_size_field(),
					'spacing'          => [
						'type'  => 'spacing',
						'label' => __( 'Margin & Padding', 'fw' ),
					],
				],
			],
		],
	],
	'tab_animation' => [
		'title'   => __( 'Animations', 'fw' ),
		'type'    => 'tab',
		'options' => sc_get_animation_fields(),
	],
	'tab_advanced' => [
		'title'   => __( 'Advanced', 'fw' ),
		'type'    => 'tab',
		'options' => [
			'advanced_settings' => [
				'type'    => 'group',
				'options' => sc_get_advanced_tab(),
			],
		],
	],
];
