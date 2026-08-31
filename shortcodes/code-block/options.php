<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$options = [
	'tab_code' => [
		'title'   => __( 'Content', 'fw' ),
		'type'    => 'tab',
		'options' => [
			'group_content' => [
                'type'    => 'group',
                'options' => [
					'code' => [
						'type'   => 'code-editor',
						'label'  => __( 'Code', 'fw' ),
						'desc'   => __( 'Enter some HTML/CSS/JavaScript here. Syntax highlighting enabled.', 'fw' ),
						'help'   => __( 'Paste raw markup exactly as you want it output - it is not escaped, so use this only with code you trust. Best for embeds, custom widgets, or snippets the visual editor would strip.', 'fw' ),
						'mode'   => 'htmlmixed', // covers HTML + inline CSS/JS - the common code-block case
						'height' => 500,
					],
					'render_as_code' => [
						'type'         => 'switch',
						'label'        => __( 'Render as Code', 'fw' ),
						'desc'         => __( 'Display the code above as a syntax-highlighted code block instead of executing/rendering it on the page.', 'fw' ),
						'help'         => __( 'When ON, the markup is HTML-escaped and wrapped in a Prism-ready &lt;pre&gt;&lt;code&gt; block, so visitors SEE the code instead of the rendered result. Perfect for documentation and "here is the markup" examples. When OFF (default), the code is output verbatim and runs as normal.', 'fw' ),
						'value'        => false,
						'right-choice' => [ 'value' => true,  'label' => __( 'Yes', 'fw' ) ],
						'left-choice'  => [ 'value' => false, 'label' => __( 'No', 'fw' ) ],
					],
					'render_mode' => [
						// INLINE multi-picker (a `select` picker, NO popover) = Shape A. Per
						// framework/includes/option-types/multi-picker/AGENTS.md the label/desc MUST live on the
						// PICKER sub-option, and the TOP level is label:false, desc:false. Putting them on the top
						// level is the #1 multi-picker mistake (misaligned label + desc floats to the bottom). The
						// Sandbox Source URL field only appears when "Sandbox" is chosen.
						'type'         => 'multi-picker',
						'label'        => false,
						'desc'         => false,
						'show_borders' => false,
						'value'        => [ 'mode' => 'inline' ],
						'picker' => [
							'mode' => [
								'type'    => 'select',
								'label'   => __( 'Render Mode', 'fw' ),
								'desc'    => __( 'How the code runs on the page. <b>Inline</b> (default) outputs it directly into the page. <b>Sandbox (isolated iframe)</b> runs it in its own document - use it for a whole self-contained page (a WebGL / three.js landing, an art-directed layout with body-level CSS or scroll-hijack) that would break when nested in the page.', 'fw' ),
								'value'   => 'inline',
								'choices' => [
									'inline'  => __( 'Inline (run in the page)', 'fw' ),
									'sandbox' => __( 'Sandbox (isolated iframe)', 'fw' ),
								],
							],
						],
						'choices' => [
							'inline'  => [],
							'sandbox' => [
								'sandbox_src' => [
									'type'  => 'text',
									'label' => __( 'Sandbox Source URL', 'fw' ),
									'desc'  => __( 'The same-origin URL of the file to run in the iframe (e.g. a mirror index.html under uploads). The Site Converter sets this automatically.', 'fw' ),
									'value' => '',
								],
							],
						],
					],
					'beautify' => [
						'type'         => 'switch',
						'label'        => __( 'Auto-format (Beautify)', 'fw' ),
						'desc'         => __( 'Automatically re-indent the markup with clean tab spacing before displaying it (only applies when "Render as Code" is ON, for HTML/Markup).', 'fw' ),
						'help'         => __( 'Normalizes messy or minified HTML into tidy, nested, tab-indented code so you do not have to hand-format it. Turn OFF only if you have intentionally formatted the code yourself (e.g. ASCII art) and want it left exactly as typed.', 'fw' ),
						'value'        => true,
						'right-choice' => [ 'value' => true,  'label' => __( 'Yes', 'fw' ) ],
						'left-choice'  => [ 'value' => false, 'label' => __( 'No', 'fw' ) ],
					],
					'code_language' => [
						'type'    => 'select',
						'label'   => __( 'Code Language', 'fw' ),
						'desc'    => __( 'Syntax-highlighting language (only applies when "Render as Code" is ON). Leave on Auto-detect - it picks the language from the code. Maps to the Prism "language-*" class.', 'fw' ),
						'value'   => 'auto',
						'choices' => [
							'auto'       => __( 'Auto-detect', 'fw' ),
							'markup'     => __( 'HTML / Markup', 'fw' ),
							'css'        => __( 'CSS', 'fw' ),
							'javascript' => __( 'JavaScript', 'fw' ),
							'php'        => __( 'PHP', 'fw' ),
							'json'       => __( 'JSON', 'fw' ),
							'bash'       => __( 'Shell / Bash', 'fw' ),
						],
					],
				],
			],
		],
	],
	'tab_styling' => [
		'title'   => __( 'Styling', 'fw' ),
		'type'    => 'tab',
		'options' => [
			'group_colors' => [
				'type'    => 'group',
				'options' => [
					'text_color'       => sc_color_field_compact( array( 'label' => __( 'Text Color', 'fw' ),       'kind' => 'text' ) ),
					'bg_color'         => sc_color_field_compact( array( 'label' => __( 'Background Color', 'fw' ), 'kind' => 'bg' ) ),
					'font_size_preset' => sc_font_size_field( array(
						'desc' => __( 'A named size from the framework presets. Customizable in Theme Settings on the official Unyson+ theme.', 'fw' ),
					) ),
				],
			],
			'group_spacings' => [
				'type'    => 'group',
				'options' => [
					'spacing' => array(
						'type'  => 'spacing',
						'label' => __( 'Margin & Padding', 'fw' ),
						'desc'  => __( 'All Sides applies to every side at once; any per-side value (Top, Right, Bottom, Left) overrides it for that direction.', 'fw' ),
						'help'  => sc_styling_help_text( 'spacing' ),
					),
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
		'title'   => __('Advanced', 'fw'),
		'type'    => 'tab',
		'options' => [
			'advanced_settings' => [
				'type'    => 'group',
				'options' => array_merge(
					sc_get_advanced_tab(),
				),
			],
		],
	],
];
