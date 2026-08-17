<?php
/**
 * PHP Version: 7.4 or higher
 */
if (!defined('FW')) die('Forbidden');


if(! function_exists( 'sc_option_color_palette_defaults' )) :
	/**
	 * Color palette default values
	 */
	function sc_option_color_palette_defaults() {
		return array(
			array(
				'name'  =>'Black',
				'color' =>'#000'),
			array(
				'name'  =>'White',
				'color' =>'#fff'),
			array(
				'name'  =>'Gray',
				'color' =>'#636c72'),
			array(
				'name'  =>'Light Gray',
				'color' =>'#bdbdbd'),
			array(
				'name'  =>'Red',
				'color' =>'#d9534f'),
			array(
				'name'  =>'Pink',
				'color' =>'#e91e63'),
			array(
				'name'  =>'Purple',
				'color' =>'#9c27b0'),
			array(
				'name'  =>'Deep Purple',
				'color' =>'#673ab7'),
			array(
				'name'  =>'Indigo',
				'color' =>'#3f51b5'),
			array(
				'name'  =>'Blue',
				'color' =>'#286090'),
			array(
				'name'  =>'Light Blue',
				'color' =>'#03a9f4'),
			array(
				'name'  =>'Cyan',
				'color' =>'#00bcd4'),
			array(
				'name'  =>'Teal',
				'color' =>'#009688'),
			array(
				'name'  =>'Green',
				'color' =>'#5cb85c'),
			array(
				'name'  =>'Light Green',
				'color' =>'#8bc34a'),
			array(
				'name'  =>'Lime',
				'color' =>'#cddc39'),
			array(
				'name'  =>'Yellow',
				'color' =>'#ffeb3b'),
			array(
				'name'  =>'Amber',
				'color' =>'#ffc107'),
			array(
				'name'  =>'Orange',
				'color' =>'#ff9800'),
			array(
				'name'  =>'Deep Orange',
				'color' =>'#ff5722'),
			array(
				'name'  =>'Brown',
				'color' =>'#795548'),
			array(
				'name'  =>'Blue Gray',
				'color' =>'#607d8b'),
		);
	}
endif;


if(! function_exists( 'sc_option_color_palette' )) :
	/**
	 * Get predefined colors
	 */
	function sc_option_color_palette() {
		$theme_colors = sc_get_option('theme_colors');
		if(!isset($theme_colors)) {
			$theme_colors = sc_option_color_palette_defaults();
		}
		$predefined_colors = array();
		foreach($theme_colors as $theme_color) {
			$predefined_colors[$theme_color['name']] = $theme_color['color'];
		}
		return $predefined_colors;
	}
endif;


if(! function_exists( 'sc_option_color_select' )) :
	/**
	*  Color Swatch Options
	*/
	function sc_option_color_select( $label, $color = 'text' ) {
		$color_palette = array(
			''  =>'Default');
		$theme_colors = sc_option_color_palette();
		//$color_palette[''] = __( 'Default' , 'fw' );
		foreach($theme_colors as $key => $value) {
			$color_palette[sanitize_title_with_dashes( $color ) . '-'.sanitize_title_with_dashes( $key )] = __( $key , 'fw' );
		}
		return array(
			'label'   => __( '', 'fw' ),
			'type'    => 'select',
			'value'   => '',
			'desc'		=> sprintf( __( '%s color. Add or modify the color palettes by clicking <a href="%s" target="_blank">here</a>.', 'fw' ), $label,
					admin_url( 'themes.php?page=fw-settings#fw-options-tab-tab_colors')
			),
			'choices' => $color_palette,
		);
	}
endif;


if(! function_exists('sc_option_color_picker')) :
	/**
	 * Color Picker
	 */
	function sc_option_color_picker($label = NULL, $default = '#ffffff', $desc = NULL) {
		$option = array(
			'type' => 'predefined-colors-color-picker',
			'label' => __($label, 'fw' ),
			'desc'	=> __($desc, 'fw' ),
			'value' => array(
				'predefined' => '', // you can set default value
				'custom' => $default // or default value for picker
			),
			'colors' => array(
				'predefined' => array(
					'type' =>'predefined',
					'choices' => sc_option_color_palette(),
				),
				'custom' => array(
					'type' =>'custom',
					'picker' => 'color-picker', // color-picker|rgba-color-picker
				),
			),
			'help'  => __('Set your predefined color swatches in <a href="'.admin_url().'themes.php?page=fw-settings#fw-options-tab-tab_colors" target="_blank">here</a>', 'fw' )
		);
		return $option;
	}
endif;


if(! function_exists( 'sc_option_button_color_defaults' )) :
	/**
	 * Button color default values
	 */
	function sc_option_button_color_defaults() {
		return array(
			array(
				'id'  							=>'0000000001',
				'color_name'  			=>'Default',
				'normal_text_color' =>'#76838f',
				'normal_bg_color' 	=>'#e4eaec',
				'hover_text_color'	=>'#76838f',
				'hover_bg_color' 		=>'#ccd5db'),
			array(
				'id'  							=>'0000000002',
				'color_name'  			=>'Primary',
				'normal_text_color' =>'#fff',
				'normal_bg_color' 	=>'#3e8ef7',
				'hover_text_color'	=>'#fff',
				'hover_bg_color' 		=>'#589ffc'),
			array(
				'id'  							=>'0000000003',
				'color_name'  			=>'Success',
				'normal_text_color' =>'#fff',
				'normal_bg_color' 	=>'#11c26d',
				'hover_text_color'	=>'#fff',
				'hover_bg_color' 		=>'#28d17c'),
			array(
				'id'  							=>'0000000004',
				'color_name'  			=>'Info',
				'normal_text_color' =>'#fff',
				'normal_bg_color' 	=>'#0bb2d4',
				'hover_text_color'	=>'#fff',
				'hover_bg_color' 		=>'#28c0de'),
			array(
				'id'  							=>'0000000005',
				'color_name'  			=>'Warning',
				'normal_text_color' =>'#fff',
				'normal_bg_color' 	=>'#eb6709',
				'hover_text_color'	=>'#fff',
				'hover_bg_color' 		=>'#f57d1b'),
			array(
				'id'  							=>'0000000006',
				'color_name'  			=>'Danger',
				'normal_text_color' =>'#fff',
				'normal_bg_color' 	=>'#ff4c52',
				'hover_text_color'	=>'#fff',
				'hover_bg_color' 		=>'#ff666b'),
		);
	}
endif;


if(! function_exists( 'sc_option_button_colors' )) :
	/**
	 * Color palette default values
	 */
	function sc_option_button_colors() {
		$button_colors = sc_get_option('button_colors');
		if(!isset($button_colors)) {
			$button_colors = sc_option_button_color_defaults();
		}
		$predefined_colors = array();
		foreach($button_colors as $button_color) {
			$predefined_colors['btn-'.sanitize_title_with_dashes($button_color['color_name'])] = $button_color['color_name'];
		}
		return $predefined_colors;
	}
endif;


if(! function_exists( 'sc_option_button_size_defaults' )) :
	/**
	 * Button size default values
	 */
	function sc_option_button_size_defaults() {
		return array(
			array(
				'id'  					=> '0000010005',
				'size_name'			=> 'Extra Large',
				'slug'					=> 'xl',
				'font_size'  		=> '22px',
				'line_height'  	=> '24px',
				'padding' 			=> array( 'top' => '14px', 'right' => '24px', 'bottom' => '14px', 'left' => '24px' ),
				'border_width' 	=> '2px',
				'border_radius'	=> '10px',
			),
			array(
				'id'  					=> '0000010004',
				'size_name'			=> 'Large',
				'slug'					=> 'lg',
				'font_size'  		=> '20px',
				'line_height'  	=> '22px',
				'padding' 			=> array( 'top' => '12px', 'right' => '20px', 'bottom' => '12px', 'left' => '20px' ),
				'border_width' 	=> '2px',
				'border_radius'	=> '8px',
			),
			array(
				'id'  					=> '0000010003',
				'size_name'			=> 'Medium',
				'slug'					=> 'md',
				'font_size'  		=> '16px',
				'line_height'  	=> '18px',
				'padding' 			=> array( 'top' => '8px', 'right' => '16px', 'bottom' => '8px', 'left' => '16px' ),
				'border_width' 	=> '1px',
				'border_radius'	=> '6px',
			),
			array(
				'id'  					=> '0000010002',
				'size_name'			=> 'Small',
				'slug'					=> 'sm',
				'font_size'  		=> '13px',
				'line_height'  	=> '15px',
				'padding' 			=> array( 'top' => '6px', 'right' => '12px', 'bottom' => '6px', 'left' => '12px' ),
				'border_width' 	=> '1px',
				'border_radius'	=> '5px',
			),
			array(
				'id'  					=> '0000010001',
				'size_name'			=> 'Extra Small',
				'slug'					=> 'xs',
				'font_size'  		=> '12px',
				'line_height'  	=> '14px',
				'padding' 			=> array( 'top' => '2px', 'right' => '6px', 'bottom' => '2px', 'left' => '6px' ),
				'border_width' 	=> '1px',
				'border_radius'	=> '3px',
			),
		);
	}
endif;


if( ! function_exists( 'sc_option_button_sizes' )) :
	/**
	 * Color palette default values
	 */
	function sc_option_button_sizes() {
		$button_colors = sc_get_option('button_sizes');
		if(!isset($button_colors)) {
			$button_colors = sc_option_button_size_defaults();
		}
		$predefined_colors = array();
		foreach($button_colors as $button_color) {
			$predefined_colors['btn-'.sanitize_title_with_dashes($button_color['slug'])] = $button_color['size_name'];
		}
		return $predefined_colors;
	}
endif;


if( ! function_exists( 'sc_option_font_sizes' )) :
	/**
	 * Color palette default values
	 */
	function sc_option_font_sizes() {
		$typography = sc_get_option('typography');
		$font_sizes = $typography['font_sizes'];
		if( !isset($font_sizes) ) {
			return;
		}
		$font_sizes_choices = array();
		$font_sizes_choices[''] = 'Default';
		foreach($font_sizes as $font_size) {
			$font_sizes_choices[sanitize_title_with_dashes($font_size['name'])] = $font_size['size'] . 'px - ' . $font_size['name'];
		}
		if( !empty($typography['h1']['size']) ) 		$font_sizes_choices['h1'] = $typography['h1']['size'] . 'px - Same size with h1 heading';
		if( !empty($typography['h2']['size']) ) 		$font_sizes_choices['h2'] = $typography['h2']['size'] . 'px - Same size with h2 heading';
		if( !empty($typography['h3']['size']) ) 		$font_sizes_choices['h3'] = $typography['h3']['size'] . 'px - Same size with h3 heading';
		if( !empty($typography['h4']['size']) ) 		$font_sizes_choices['h4'] = $typography['h4']['size'] . 'px - Same size with h4 heading';
		if( !empty($typography['h5']['size']) ) 		$font_sizes_choices['h5'] = $typography['h5']['size'] . 'px - Same size with h5 heading';
		if( !empty($typography['h6']['size']) ) 		$font_sizes_choices['h6'] = $typography['h6']['size'] . 'px - Same size with h6 heading';
		if( !empty($typography['body']['size']) ) 	$font_sizes_choices['p'] = $typography['body']['size'] . 'px - Same size with paragraph text';
		return $font_sizes_choices;
	}
endif;


if( ! function_exists('sc_option_text_transform')) :
	/**
	* Text Transformation
	*/
	function sc_option_text_transform($label=NULL,$desc=NULL) {
		return array(
			'type'    => 'select',
			'label'   => __($label, 'fw' ),
			'desc'		=> __($desc, 'fw' ),
			'value'   => '',
			'choices' => array(
				''  => 'none',
				'text-lowercase' => 'lowercased text',
				'text-uppercase' => 'UPPERCASED TEXT',
				'text-capitalize' => 'Capitalized Text',
			)
		); 
	}
endif;


if( ! function_exists('sc_option_css_tag')) :
	/**
	* CSS Tag
	*/
	function sc_option_css_tag( $label=NULL, $desc=NULL, $default='h2' ) {
		return array(
			'type'    => 'select',
			'label'   => __( $label, 'fw' ),
			'desc'		=> __( $desc, 'fw' ),
			'value'   => $default,
			'choices' => array(
				'h1' => 'H1',
				'h2' => 'H2',
				'h3' => 'H3',
				'h4' => 'H4',
				'h5' => 'H5',
				'h6' => 'H6',
				'p' => 'p',
			)
		); 
	}
endif;


if( ! function_exists('sc_option_bg_atts')):
	/**
 * Option attributes for background
 */
	function sc_option_bg_atts($name) {
		$uri = get_template_directory_uri();
		return array(
			'label'         => false,
			'type'          => 'multi',
			'value'         => array(),
			'desc'          => false,
			'inner-options' => array(
				'image'    => array(
					'label'   => __( $name.' Background', 'fw' ),
					'type'    => 'background-image',
					'value'   => 'none',
					'choices' => array(
						'none' => array(
							'icon' => $uri . '/images/patterns/no_pattern.jpg',
							'css'  => array(
								'background-image' => 'none',
							)
						),
						'bg-1' => array(
							'icon' => $uri . '/images/patterns/diagonal_bottom_to_top_pattern_preview.jpg',
							'css'  => array(
								'background-image'  => 'url("' . $uri . '/images/patterns/diagonal_bottom_to_top_pattern.png' . '")',
							)
						),
						'bg-2' => array(
							'icon' => $uri . '/images/patterns/diagonal_top_to_bottom_pattern_preview.jpg',
							'css'  => array(
								'background-image'  => 'url("' . $uri . '/images/patterns/diagonal_top_to_bottom_pattern.png' . '")',
							)
						),
						'bg-3' => array(
							'icon' => $uri . '/images/patterns/dots_pattern_preview.jpg',
							'css'  => array(
								'background-image'  => 'url("' . $uri . '/images/patterns/dots_pattern.png' . '")',
							)
						),
						'bg-4' => array(
							'icon' => $uri . '/images/patterns/romb_pattern_preview.jpg',
							'css'  => array(
								'background-image'  => 'url("' . $uri . '/images/patterns/romb_pattern.png' . '")',
							)
						),
						'bg-5' => array(
							'icon' => $uri . '/images/patterns/square_pattern_preview.jpg',
							'css'  => array(
								'background-image'  => 'url("' . $uri . '/images/patterns/square_pattern.png' . '")',
							)
						),
						'bg-6' => array(
							'icon' => $uri . '/images/patterns/noise_pattern_preview.jpg',
							'css'  => array(
								'background-image'  => 'url("' . $uri . '/images/patterns/noise_pattern.png' . '")',
							)
						),
						'bg-7' => array(
							'icon' => $uri . '/images/patterns/vertical_lines_pattern_preview.jpg',
							'css'  => array(
								'background-image'  => 'url("' . $uri . '/images/patterns/vertical_lines_pattern.png' . '")',
							)
						),
						'bg-8' => array(
							'icon' => $uri . '/images/patterns/waves_pattern_preview.jpg',
							'css'  => array(
								'background-image'  => 'url("' . $uri . '/images/patterns/waves_pattern.png' . '")',
							)
						),
					),
				),
				'color' => sc_option_color_picker('','', 'Background color'),
				'position' => array(
					'label' => __( '', 'fw' ),
					'desc'  => __( 'Image position', 'fw' ),
					'type'  => 'select',
					'value' => 'top center',
					'choices' => array(
						'top left' 			=> __( 'Top Left', 'fw' ),
						'top center' 		=> __( 'Top Center', 'fw' ),
						'top right' 		=> __( 'Top Right', 'fw' ),
						'center left' 	=> __( 'Center Left', 'fw' ),
						'center center' => __( 'Center Center', 'fw' ),
						'center right' 	=> __( 'Center Right', 'fw' ),
						'bottom left' 	=> __( 'Bottom Left', 'fw' ),
						'bottom center' => __( 'Bottom Center', 'fw' ),
						'bottom right' 	=> __( 'Bottom Right', 'fw' ),
					)
				),
				'repeat' => array(
					'label' => __( '', 'fw' ),
					'desc'  => __( 'Image repeat', 'fw' ),
					'type'  => 'select',
					/*'attr'  => array( 'class' => '' ),*/
					'value' => 'repeat',
					'choices' => array(
						'no-repeat' => __( 'Display Once (No-Repeat)', 'fw' ),
						'repeat' 		=> __( 'Full Tile (Repeat XY Axis)', 'fw' ),
						'repeat-x' 	=> __( 'Horizontal Tile (Repeat X Axis)', 'fw' ),
						'repeat-y' 	=> __( 'Vertical Tile (Repeat Y Axis)', 'fw' ),
					)
				),
				'attachment' => array(
					'label' => __( '', 'fw' ),
					'desc'  => __( 'Image attachment', 'fw' ),
					'type'  => 'select',
					'value' => 'scroll',
					'choices' => array(
						'scroll' => __( 'Scroll', 'fw' ),
						'fixed' => __( 'Fixed', 'fw' ),
					),
					'help'	=> __( '<p><strong>scroll</strong> - The background scrolls along with the page. This is default</p>
									<p><strong>fixed</strong> - The background is fixed with regard to the viewport.</p>
									', 'fw' ),
				),
				'size' => array(
					'type' 	=> 'multi-inline',
					'label' => __('', 'fw' ),
					'desc'  => __( 'Image size', 'fw' ),
					'value' => array(
						'selected' 	 	=> 'auto',	
						'custom'		=> '',
					),
					'help'  => __( '<p><strong>auto</strong> -	Default value. The background image contains its width and height.</p>
								<p><strong>cover</strong> - Scale the background image to be as large as possible so that the background area is completely covered by the background image. Some parts of the background image may not be in view within the background positioning area.</p>
								<p><strong>contain</strong> - Scale the image to the largest size such that both its width and its height can fit inside the content area.</p>
								<p><strong>custom</strong> - Counts for the width and height of the background image. i.e.:<br />
								400px - it counts for the width, and the height is set to auto.<br />
								300px 100px - the first sets the background image\'s width and the second sets the height. </p>', 'fw' ),
					'fw_multi_options' => array(
						'selected' => array(
							'label' => __( '', 'fw' ),
							'desc'  => __( '', 'fw' ),
							'title' => false,
							'type'  => 'select',
							'choices' => array(
								'auto' => __( 'Auto', 'fw' ),
								'cover' => __( 'Cover', 'fw' ),
								'contain' => __( 'Contain', 'fw' ),
								'custom' => __( 'Custom Value', 'fw' ),
							)
						),
						'custom' => array(
							'type' 	=>'short-text',
							'title' => false,
						),
					)
				),
				'overlay' => array(
					'type'  => 'multi-picker',
					'label' => false,
					'desc'  => false,
					'picker' => array(
						'selected' => array(
							'type'  => 'switch',
							'label' => __( 'Overlay', 'fw' ),
							'desc'  => __( 'Enable background overlay?', 'fw' ),
							'value' => 'no',
							'right-choice' => array(
								'value' => 'yes',
								'label' => __('Yes', 'fw' ),
							),
							'left-choice' => array(
								'value' => 'no',
								'label' => __('No', 'fw' ),
							),
						),
					),
					'choices' => array(
						'yes' => array(
							'color' => sc_option_color_picker('','', 'Color 1'),
							'gradient' => sc_option_color_picker('','', 'Color 2. Select second color to enable gradient.'),
							'direction' => array(
								'label' => __( '', 'fw' ),
								'desc'  => __( 'Gradient direction.', 'fw' ),
								'type'  => 'select',
								'value' => 'bottom',
								'choices' => array(
									'bottom' 		=> __( 'Top to bottom', 'fw' ),
									'top' 			=> __( 'Bottom to top', 'fw' ),
									'right' 		=> __( 'Left to right', 'fw' ),
									'left' 			=> __( 'Right to left', 'fw' ),
									'top left' 		=> __( 'Top to left', 'fw' ),
									'top right' 	=> __( 'Top to right', 'fw' ),
									'bottom left' 	=> __( 'Bottom to left', 'fw' ),
									'bottom right' 	=> __( 'Bottom to right', 'fw' ),
								),
							),
							'opacity' => array(
								'type'  => 'slider',
								'value' => 100,
								'properties' => array(
									'min' => 0,
									'max' => 1,
									'step' => .1,
								),
								'label' => __( '', 'fw' ),
								'desc'  => __( 'Select the overlay color opacity in %', 'fw' ),
							)
						),
					),
				),
			),	
		);
	}
endif;


if(! function_exists('sc_option_link')) :
	/**
	 * Link Options
	 */
	function sc_option_link() {
		return array(
			'type'         => 'multi-picker',
			'label'        => false,
			'desc'         => false,
			'picker'       => array(
				'selected' => array(
					'label'   => __( 'Link', 'fw' ),
					'desc'  => __( 'Select your link source.', 'fw' ),
					'type'    => 'select',
					'choices' => array(
						'manual'=> __( 'Manual', 'fw' ),
						'page' 	=> __( 'Page', 'fw' ),
						'post' 	=> __( 'Blog Post', 'fw' ),
						'media' => __( 'Media', 'fw' ),
					),
				)
			),
			'choices'      => array(
				'manual'  => array(
					'href'   => array(
						'label' => __( '', 'fw' ),
						'type'  => 'text',
						'value' => '',
						'desc'  => __( 'Enter the URL. Leave Manual Link empty to disable.', 'fw' )
					),
					'target'      => array(
						'label'   => __( '', 'fw' ),
						'type'    => 'select',
						'value'   => '_self',
						'desc'    => __( 'Target attribute. How the link will be opened.','fw' ),
						'choices' => array(
							'_self'  	=> __( 'Open link in same window', 'fw' ),
							'_blank'  	=> __( 'Open link in new window', 'fw' ),
							//'lightbox' 	=> __( 'Open link inside a lightbox', 'fw' ),
							//'modal' 	=> __( 'Open link inside bootstrap modal', 'fw' ),
						),
					),
				),
				'page' => array(
					'href'      => array(
						'type'  => 'multi-select',
						'label' => __( '', 'fw' ),
						'desc'  => __( 'Enter the title of the page.', 'fw' ),
						'population' => 'posts',
						'source'=> 'page',
						'limit' => 1,
					),
					'target'      => array(
						'label'   => __( '', 'fw' ),
						'type'    => 'select',
						'value'   => '_self',
						'desc'    => __( 'Target attribute. How the link will be opened.','fw' ),
						'choices' => array(
							'_self'  	=> __( 'Open link in same window', 'fw' ),
							'_blank'  	=> __( 'Open link in new window', 'fw' ),
							//'lightbox' 	=> __( 'Open link inside a lightbox', 'fw' ),
							//'modal' 	=> __( 'Open link inside bootstrap modal', 'fw' ),
						),
					),
				),
				'post' => array(
					'href'      => array(
						'type'       => 'multi-select',
						'label'      => __( '', 'fw' ),
						'desc'  => __( 'Enter the title of the post.', 'fw' ),
						'population' => 'posts',
						'source'     => 'post',
						'limit' => 1,
					),
					'target'      => array(
						'label'   => __( '', 'fw' ),
						'type'    => 'select',
						'value'   => '_self',
						'desc'    => __( 'Target attribute. How the link will be opened.','fw' ),
						'choices' => array(
							'_self'  	=> __( 'Open link in same window', 'fw' ),
							'_blank'  	=> __( 'Open link in new window', 'fw' ),
							//'lightbox' 	=> __( 'Open link inside a lightbox', 'fw' ),
							//'modal' 	=> __( 'Open link inside bootstrap modal', 'fw' ),
						),
					),
				),
				'media' => array(
					'href'                    => array(
						'label'       => __( '', 'fw' ),
						'desc'        => __( 'Upload your media file or select from Media Library.', 'fw' ),
						'type'        => 'upload',
						'images_only' => false,
					),
					'target'      => array(
						'label'   => __( '', 'fw' ),
						'type'    => 'select',
						'value'   => '_self',
						'desc'    => __( 'Target attribute. How the link will be opened.','fw' ),
						'choices' => array(
							'_self'  	=> __( 'Open link in same window', 'fw' ),
							'_blank'  	=> __( 'Open link in new window', 'fw' ),
							//'lightbox' 	=> __( 'Open link inside a lightbox', 'fw' ),
							//'modal' 	=> __( 'Open link inside bootstrap modal', 'fw' ),
						),
					),
				),
			),
			'show_borders' => false,
		); 
	}
endif;


if(! function_exists('sc_option_float')) :
	/**
	 * Link Options
	 */
	function sc_option_float( $label = 'Alignment', $desc = 'Floats an element to the left or right, or disable floating, based on the current viewport size.' ) {
		return array(
			'type'    => 'multiple',
			'label'   => __( $label, 'fw' ),
			'desc'		=> __( $desc, 'fw' ),
			'value' => '',
			'choices' => array(
				'' 						=> __('None', 'fw' ),
				array(
					'attr'    	=> array(
						'label'         => __( 'For All Devices ( Default )', 'fw' ),
						//'data-whatever' => 'some data',
					),
					'choices' => array(
						'float-left' 		=> __( 'Float left', 'fw' ),
						'float-right' 	=> __( 'Float right', 'fw' ),
						'mx-auto d-block'	=> __( 'Centered', 'fw' ),
						'float-none' 		=> __( 'Don\'t float', 'fw' ),
					),
				),
				array(
					'attr'    	=> array(
						'label'         => __( 'Small devices (landscape phones, 576px and up)', 'fw' ),
					),
					'choices' => array(
						'float-sm-left' 	=> __( 'Float left', 'fw' ),
						'float-sm-right' 	=> __( 'Float right', 'fw' ),
						'mx-sm-auto d-block' 			=> __( 'Centered', 'fw' ),
						'float-sm-none' 	=> __( 'Don\'t float', 'fw' ),
					),
				),
				array(
					'attr'    	=> array(
						'label'         => __( 'Medium devices (tablets, 768px and up)', 'fw' ),
					),
					'choices' => array(
						'float-md-left' 	=> __( 'Float left', 'fw' ),
						'float-md-right' 	=> __( 'Float right', 'fw' ),
						'mx-md-auto d-block' => __( 'Centered', 'fw' ),
						'float-md-none' 	=> __( 'Don\'t float', 'fw' ),
					),
				),
				array(
					'attr'    	=> array(
						'label'         => __( 'Large devices (desktops, 992px and up)', 'fw' ),
					),
					'choices' => array(
						'float-lg-left' 	=> __( 'Float left', 'fw' ),
						'float-lg-right' 	=> __( 'Float right', 'fw' ),
						'mx-lg-auto d-block' 			=> __( 'Centered', 'fw' ),
						'float-lg-none' 	=> __( 'Don\'t float', 'fw' ),
					),
				),
				array(
					'attr'    	=> array(
						'label'         => __( 'Extra large devices (large desktops, 1200px and up)', 'fw' ),
					),
					'choices' => array(
						'float-xl-left' 	=> __( 'Float left', 'fw' ),
						'float-xl-right' 	=> __( 'Float right', 'fw' ),
						'mx-xl-auto d-block' 			=> __( 'Centered', 'fw' ),
						'float-xl-none' 	=> __( 'Don\'t float', 'fw' ),
					),
				),
			),
		);
	}
endif;


if(! function_exists('sc_option_hover_2d')) :
	/**
	 * 2D Hover Option
	 */
	function sc_option_hover_2d() {
		return array(
			'type'    => 'select',
			'label'   => __( '2d Transition', 'fw' ),
			'desc'		=> __( '', 'fw' ),
			'value' => '',
			'choices' => array(
				'' 				=> __( 'None', 'fw' ),
				'hvr-grow' 				=> __( 'Grow', 'fw' ),
				'hvr-shrink' 			=> __( 'Shrink', 'fw' ),
				'hvr-pulse' 			=> __( 'Pulse', 'fw' ),
				'hvr-pulse-grow' 	=> __( 'Pulse Grow', 'fw' ),
				'hvr-pulse-shrink'=> __( 'Pulse Shrink', 'fw' ),
				'hvr-push' 				=> __( 'Push', 'fw' ),
				'hvr-pop' 				=> __( 'Pop', 'fw' ),
				'hvr-bounce-in' 	=> __( 'Bounce In', 'fw' ),
				'hvr-bounce-out' 	=> __( 'Bounce Out', 'fw' ),
				'hvr-rotate' 			=> __( 'Rotate', 'fw' ),
				'hvr-grow-rotate' => __( 'Grow Rotate', 'fw' ),
				'hvr-float' 			=> __( 'Float', 'fw' ),
				'hvr-sink' 				=> __( 'Sink', 'fw' ),
				'hvr-bob' 				=> __( 'Bob', 'fw' ),
				'hvr-hang' 				=> __( 'Hang', 'fw' ),
				'hvr-skew' 				=> __( 'Skew', 'fw' ),
				'hvr-skew-forward' 	=> __( 'Skew Forward', 'fw' ),
				'hvr-skew-backward' => __( 'Skew Backward', 'fw' ),
				'hvr-wobble-horizontal' => __( 'Wobble Horizontal', 'fw' ),
				'hvr-wobble-vertical' 	=> __( 'Wobble Vertical', 'fw' ),
				'hvr-wobble-to-bottom-right'=> __( 'Wobble To Bottom Right', 'fw' ),
				'hvr-wobble-to-top-right' 	=> __( 'Wobble To Top Right', 'fw' ),
				'hvr-wobble-top' 	=> __( 'Wobble Top', 'fw' ),
				'hvr-wobble-bottom' => __( 'Wobble Bottom', 'fw' ),
				'hvr-wobble-skew' => __( 'Wobble Skew', 'fw' ),
				'hvr-buzz' 				=> __( 'Buzz', 'fw' ),
				'hvr-buzz-out' 		=> __( 'Buzz Out', 'fw' ),
				'hvr-forward' 		=> __( 'Forward', 'fw' ),
				'hvr-backward' 		=> __( 'Backward', 'fw' ),
			),
		);
	}
endif;


if(! function_exists('sc_option_hover_background')) :
	/**
	 * Background Hover Option
	 */
	function sc_option_hover_background() {
		return array(
			'type'    => 'select',
			'label'   => __( 'Background Transition', 'fw' ),
			'desc'		=> __( '', 'fw' ),
			'value' => '',
			'choices' => array(
				'' 				=> __( 'None', 'fw' ),
				'hvr-fade' => __( 'Fade', 'fw' ),
				'hvr-back-pulse' => __( 'Back Pulse', 'fw' ),
				'hvr-sweep-to-right' => __( 'Sweep To Right', 'fw' ),
				'hvr-sweep-to-left' => __( 'Sweep To Left', 'fw' ),
				'hvr-sweep-to-bottom' => __( 'Sweep To Bottom', 'fw' ),
				'hvr-sweep-to-top' => __( 'Sweep To Top', 'fw' ),
				'hvr-bounce-to-right' => __( 'Bounce To Right', 'fw' ),
				'hvr-bounce-to-left' => __( 'Bounce To Left', 'fw' ),
				'hvr-bounce-to-bottom' => __( 'Bounce To Bottom', 'fw' ),
				'hvr-bounce-to-top' => __( 'Bounce To Top', 'fw' ),
				'hvr-radial-out' => __( 'Radial Out', 'fw' ),
				'hvr-radial-in' => __( 'Radial In', 'fw' ),
				'hvr-rectangle-in' => __( 'Rectangle In', 'fw' ),
				'hvr-rectangle-out' => __( 'Rectangle Out', 'fw' ),
				'hvr-shutter-in-horizontal' => __( 'Shutter In Horizontal', 'fw' ),
				'hvr-shutter-out-horizontal' => __( 'Shutter Out Horizontal', 'fw' ),
				'hvr-shutter-in-vertical' => __( 'Shutter In Vertical', 'fw' ),
				'hvr-shutter-out-vertical' => __( 'Shutter Out Vertical', 'fw' ),
			),
		);
	}
endif;


if(! function_exists('sc_option_hover_border')) :
	/**
	 * Border Hover Option
	 */
	function sc_option_hover_border() {
		return array(
			'type'    => 'select',
			'label'   => __( 'Border Transition', 'fw' ),
			'desc'		=> __( '', 'fw' ),
			'value' => '',
			'choices' => array(
				'' 				=> __( 'None', 'fw' ),
				'hvr-border-fade' => __( 'Border Fade', 'fw' ),
				'hvr-hollow' => __( 'Hollow', 'fw' ),
				'hvr-trim' => __( 'Trim', 'fw' ),
				'hvr-ripple-out' => __( 'Ripple Out', 'fw' ),
				'hvr-ripple-in' => __( 'Ripple In', 'fw' ),
				'hvr-outline-out' => __( 'Outline Out', 'fw' ),
				'hvr-outline-in' => __( 'Outline In', 'fw' ),
				'hvr-round-corners' => __( 'Round Corners', 'fw' ),
				'hvr-underline-from-left' => __( 'Underline From Left', 'fw' ),
				'hvr-underline-from-center' => __( 'Underline From Center', 'fw' ),
				'hvr-underline-from-right' => __( 'Underline From Right', 'fw' ),
				'hvr-reveal' => __( 'Reveal', 'fw' ),
				'hvr-underline-reveal' => __( 'Underline Reveal', 'fw' ),
				'hvr-overline-reveal' => __( 'Overline Reveal', 'fw' ),
				'hvr-overline-from-left' => __( 'Overline From Left', 'fw' ),
				'hvr-overline-from-center' => __( 'Overline From Center', 'fw' ),
				'hvr-overline-from-right' => __( 'Overline From Right', 'fw' ),
			),
		);
	}
endif;


if(! function_exists('sc_option_hover_shadow')) :
	/**
	 * Shadow and Glow Hover Option
	 */
	function sc_option_hover_shadow() {
		return array(
			'type'    => 'select',
			'label'   => __( 'Shadow and Glow Transition', 'fw' ),
			'desc'		=> __( '', 'fw' ),
			'value' => '',
			'choices' => array(
				'' 				=> __( 'None', 'fw' ),
				'hvr-shadow' => __( 'Shadow', 'fw' ),
				'hvr-grow-shadow' => __( 'Grow Shadow', 'fw' ),
				'hvr-float-shadow' => __( 'Float Shadow', 'fw' ),
				'hvr-glow' => __( 'Glow', 'fw' ),
				'hvr-shadow-radial' => __( 'Shadow Radial', 'fw' ),
				'hvr-box-shadow-outset' => __( 'Box Shadow Outset', 'fw' ),
				'hvr-box-shadow-inset' => __( 'Box Shadow Inset', 'fw' ),
			),
		);
	}
endif;


if(! function_exists('sc_option_hover_speech_bubbles')) :
	/**
	 * Speech Bubbles Hover Option
	 */
	function sc_option_hover_speech_bubbles() {
		return array(
			'type'    => 'select',
			'label'   => __( 'Speech Bubbles', 'fw' ),
			'desc'		=> __( '', 'fw' ),
			'value' => '',
			'choices' => array(
				'' 				=> __( 'None', 'fw' ),
				'hvr-bubble-top' => __( 'Bubble Top', 'fw' ),
				'hvr-bubble-right' => __( 'Bubble Right', 'fw' ),
				'hvr-bubble-bottom' => __( 'Bubble Bottom', 'fw' ),
				'hvr-bubble-left' => __( 'Bubble Left', 'fw' ),
				'hvr-bubble-float-top' => __( 'Bubble Float Top', 'fw' ),
				'hvr-bubble-float-right' => __( 'Bubble Float Right', 'fw' ),
				'hvr-bubble-float-bottom' => __( 'Bubble Float Bottom', 'fw' ),
				'hvr-bubble-float-left' => __( 'Bubble Float Left', 'fw' ),
			),
		);
	}
endif;


if(! function_exists('sc_option_hover_curls')) :
	/**
	 * Curls Hover Option
	 */
	function sc_option_hover_curls() {
		return array(
			'type'    => 'select',
			'label'   => __( 'Curls', 'fw' ),
			'desc'		=> __( '', 'fw' ),
			'value' => '',
			'choices' => array(
				'' 				=> __( 'None', 'fw' ),
				'hvr-curl-top-left' => __( 'Curl Top Left', 'fw' ),
				'hvr-curl-top-right' => __( 'Curl Top Right', 'fw' ),
				'hvr-curl-bottom-right' => __( 'Curl Bottom Right', 'fw' ),
				'hvr-curl-bottom-left' => __( 'Curl Bottom Left', 'fw' ),
			),
		);
	}
endif;


if( ! function_exists('sc_option_alignment') ) :
	function sc_option_alignment() {
		$uri = get_template_directory_uri();
		return array(
			'type'    => 'group',
			'options' => array(
				'alignment' =>	array(
					'label' => __( 'Alignment', 'fw' ),
					'desc'  => __( 'Image alignment', 'fw' ),
					'type'  => 'image-picker',
					'value' => '',
					'choices' => array(
						'' => array(
							'small' => array(
								'height' => 50,
								'src' => $uri .'/images/image-picker/align-none.png',
								'title' => __( 'None','fw' )
							),
						),
						'float-left' => array(
							'small' => array(
								'height' => 50,
								'src' => $uri .'/images/image-picker/align-left.png',
								'title' => __( 'Left','fw' )
							),
						),
						'mx-auto d-block' => array(
							'small' => array(
								'height' => 50,
								'src' => $uri .'/images/image-picker/align-center.png',
								'title' => __( 'Center','fw' )
							),
						),
						'float-right' => array(
							'small' => array(
								'height' => 50,
								'src' => $uri .'/images/image-picker/align-right.png',
								'title' => __( 'Right','fw' )
							),
						),
					),
				),
				'alignment_responsive' => array(
					'type' => 'popup',
					'value' => array(
					),
					'label' 		=> __('', 'fw' ),
					'desc'  		=> __( '', 'fw' ),
					'popup-title' => __('Responsive Breakpoints', 'fw' ),
					'button' 		=> __('Responsive Breakpoints', 'fw' ),
					'popup-title' => __('Responsive Breakpoints', 'fw' ),
					'size' 			=> 'small', // small, medium, large
					'popup-options' => array(
						'sm' =>	array(
							'label' => __( 'Small', 'fw' ),
							'desc'  => __( 'Small devices (landscape phones, 576px and up)', 'fw' ),
							'type'  => 'image-picker',
							'value' => '',
							'choices' => array(
								'' => array(
									'small' => array(
										'height' => 50,
										'src' => $uri .'/images/image-picker/align-default.png',
										'title' => __( 'Default','fw' )
									),
								),
								'float-sm-none' => array(
									'small' => array(
										'height' => 50,
										'src' => $uri .'/images/image-picker/align-none.png',
										'title' => __( 'None','fw' )
									),
								),
								'float-sm-left' => array(
									'small' => array(
										'height' => 50,
										'src' => $uri .'/images/image-picker/align-left.png',
										'title' => __( 'Left','fw' )
									),
								),
								'mx-sm-auto d-block' => array(
									'small' => array(
										'height' => 50,
										'src' => $uri .'/images/image-picker/align-center.png',
										'title' => __( 'Center','fw' )
									),
								),
								'float-sm-right' => array(
									'small' => array(
										'height' => 50,
										'src' => $uri .'/images/image-picker/align-right.png',
										'title' => __( 'Right','fw' )
									),
								),
							),
						),
						'md' =>	array(
							'label' => __( 'Medium', 'fw' ),
							'desc'  => __( 'Medium devices (tablets, 768px and up)', 'fw' ),
							'type'  => 'image-picker',
							'value' => '',
							'choices' => array(
								'' => array(
									'small' => array(
										'height' => 50,
										'src' => $uri .'/images/image-picker/align-default.png',
										'title' => __( 'Default','fw' )
									),
								),
								'float-md-none' => array(
									'small' => array(
										'height' => 50,
										'src' => $uri .'/images/image-picker/align-none.png',
										'title' => __( 'None','fw' )
									),
								),
								'float-md-left' => array(
									'small' => array(
										'height' => 50,
										'src' => $uri .'/images/image-picker/align-left.png',
										'title' => __( 'Left','fw' )
									),
								),
								'mx-md-auto d-block' => array(
									'small' => array(
										'height' => 50,
										'src' => $uri .'/images/image-picker/align-center.png',
										'title' => __( 'Center','fw' )
									),
								),
								'float-md-right' => array(
									'small' => array(
										'height' => 50,
										'src' => $uri .'/images/image-picker/align-right.png',
										'title' => __( 'Right','fw' )
									),
								),
							),
						),
						'lg' =>	array(
							'label' => __( 'Large', 'fw' ),
							'desc'  => __( 'Large devices (desktops, 992px and up)', 'fw' ),
							'type'  => 'image-picker',
							'value' => '',
							'choices' => array(
								'' => array(
									'small' => array(
										'height' => 50,
										'src' => $uri .'/images/image-picker/align-default.png',
										'title' => __( 'Default','fw' )
									),
								),
								'float-lg-none' => array(
									'small' => array(
										'height' => 50,
										'src' => $uri .'/images/image-picker/align-none.png',
										'title' => __( 'None','fw' )
									),
								),
								'float-lg-left' => array(
									'small' => array(
										'height' => 50,
										'src' => $uri .'/images/image-picker/align-left.png',
										'title' => __( 'Left','fw' )
									),
								),
								'mx-lg-auto d-block' => array(
									'small' => array(
										'height' => 50,
										'src' => $uri .'/images/image-picker/align-center.png',
										'title' => __( 'Center','fw' )
									),
								),
								'float-lg-right' => array(
									'small' => array(
										'height' => 50,
										'src' => $uri .'/images/image-picker/align-right.png',
										'title' => __( 'Right','fw' )
									),
								),
							),
						),
						'xl' =>	array(
							'label' => __( 'Extra Large', 'fw' ),
							'desc'  => __( 'Extra large devices (large desktops, 1200px and up)', 'fw' ),
							'type'  => 'image-picker',
							'value' => '',
							'choices' => array(
								'' => array(
									'small' => array(
										'height' => 50,
										'src' => $uri .'/images/image-picker/align-default.png',
										'title' => __( 'Default','fw' )
									),
								),
								'float-xl-none' => array(
									'small' => array(
										'height' => 50,
										'src' => $uri .'/images/image-picker/align-none.png',
										'title' => __( 'None','fw' )
									),
								),
								'float-xl-left' => array(
									'small' => array(
										'height' => 50,
										'src' => $uri .'/images/image-picker/align-left.png',
										'title' => __( 'Left','fw' )
									),
								),
								'mx-xl-auto d-block' => array(
									'small' => array(
										'height' => 50,
										'src' => $uri .'/images/image-picker/align-center.png',
										'title' => __( 'Center','fw' )
									),
								),
								'float-xl-right' => array(
									'small' => array(
										'height' => 50,
										'src' => $uri .'/images/image-picker/align-right.png',
										'title' => __( 'Right','fw' )
									),
								),
							),
						),
					),
				),
			),
		);
	}
endif;


if(! function_exists('sc_option_text_alignment')) :
	/**
	 *  Options for Text Alignment
	 */
	function sc_option_text_alignment() {
		return array(
			'type'    => 'select',
			'label'   => __('Text Alignment', 'fw' ),
			'desc'		=> __('', 'fw' ),
			'choices' => array(
				'' 				=> 'Default',
				'text-left' 	=> 'Left aligned text',
				'text-center' 	=> 'Center aligned text',
				'text-right' 	=> 'Right aligned text',
				'text-justify' 	=> 'Justified text',
				'text-nowrap' 	=> 'No wrap text',
			)
		);
	}
endif;


if(! function_exists('sc_options_vertical_center_container')) :
	/**
	 *  Get the image from options
	 */
	function sc_options_vertical_center_container($atts,$tag) {
		if ( isset( $atts['is_vertical_center'] ) && $atts['is_vertical_center'] ) {
			if($tag == 'start') {
				return '<div '. sc_attr_to_html(array('class' => 'vc-container')) .'>';;
			}elseif($tag == 'end'){
				return '</div>';
			}else{
				return;
			}
		}else{
			return;
		}
	}
endif;


if(! function_exists('sc_option_animate')) :
	/**
	 *  Animate Options
	 */
	function sc_option_animate() {
		return array(
			'animation'   => array(
				'label'   => __( 'Animation', 'fw' ),
				'type'    => 'select',
				'value'   => '',
				'desc'    => __( 'Select animation.','fw' ),
				'choices' => array(
					'' => __( 'None', 'fw' ),				
					array(
						'attr'    => array(
							'label'         => __( 'Attention Seekers', 'fw' ),
						),
						'choices' => array(
							'bounce' => __( 'bounce', 'fw' ),
							'flash' => __( 'flash', 'fw' ),
							'pulse' => __( 'pulse', 'fw' ),
							'rubberBand' => __( 'rubberBand', 'fw' ),
							'shake' => __( 'shake', 'fw' ),
							'swing' => __( 'swing', 'fw' ),
							'tada' => __( 'tada', 'fw' ),
							'wobble' => __( 'wobble', 'fw' ),
							'jello' => __( 'jello', 'fw' ),
						),
					),	
					array(
						'attr'    => array(
							'label'         => __( 'Bouncing Entrances', 'fw' ),
						),
						'choices' => array(
							'bounceIn' => __( 'bounceIn', 'fw' ),
							'bounceInDown' => __( 'bounceInDown', 'fw' ),
							'bounceInLeft' => __( 'bounceInLeft', 'fw' ),
							'bounceInRight' => __( 'bounceInRight', 'fw' ),
							'bounceInUp' => __( 'bounceInUp', 'fw' ),
						),
					),	
				/*	array(
						'attr'    => array(
							'label'         => __( 'Bouncing Exits', 'fw' ),
						),
						'choices' => array(
							'bounceOut' => __( 'bounceOut', 'fw' ),
							'bounceOutDown' => __( 'bounceOutDown', 'fw' ),
							'bounceOutLeft' => __( 'bounceOutLeft', 'fw' ),
							'bounceOutRight' => __( 'bounceOutRight', 'fw' ),
							'bounceOutUp' => __( 'bounceOutUp', 'fw' ),
						),
					),	*/
					array(
						'attr'    => array(
							'label'         => __( 'Fading Entrances', 'fw' ),
						),
						'choices' => array(
							'fadeIn' => __( 'fadeIn', 'fw' ),
							'fadeInDown' => __( 'fadeInDown', 'fw' ),
							'fadeInDownBig' => __( 'fadeInDownBig', 'fw' ),
							'fadeInLeft' => __( 'fadeInLeft', 'fw' ),
							'fadeInLeftBig' => __( 'fadeInLeftBig', 'fw' ),
							'fadeInRight' => __( 'fadeInRight', 'fw' ),
							'fadeInRightBig' => __( 'fadeInRightBig', 'fw' ),
							'fadeInUp' => __( 'fadeInUp', 'fw' ),
							'fadeInUpBig' => __( 'fadeInUpBig', 'fw' ),
						),
					),	
				/*	array(
						'attr'    => array(
							'label'         => __( 'Fading Exits', 'fw' ),
						),
						'choices' => array(
							'fadeOut' => __( 'fadeOut', 'fw' ),
							'fadeOutDown' => __( 'fadeOutDown', 'fw' ),
							'fadeOutDownBig' => __( 'fadeOutDownBig', 'fw' ),
							'fadeOutLeft' => __( 'fadeOutLeft', 'fw' ),
							'fadeOutLeftBig' => __( 'fadeOutLeftBig', 'fw' ),
							'fadeOutRight' => __( 'fadeOutRight', 'fw' ),
							'fadeOutRightBig' => __( 'fadeOutRightBig', 'fw' ),
							'fadeOutUp' => __( 'fadeOutUp', 'fw' ),
							'fadeOutUpBig' => __( 'fadeOutUpBig', 'fw' ),
						),
					),	*/
					array(
						'attr'    => array(
							'label'         => __( 'Flippers', 'fw' ),
						),
						'choices' => array(
							'flip' => __( 'flip', 'fw' ),
							'flipInX' => __( 'flipInX', 'fw' ),
							'flipInY' => __( 'flipInY', 'fw' ),
							'flipOutX' => __( 'flipOutX', 'fw' ),
							'flipOutY' => __( 'flipOutY', 'fw' ),
						),
					),	
					array(
						'attr'    => array(
							'label'         => __( 'Lightspeed', 'fw' ),
						),
						'choices' => array(
							'lightSpeedIn' => __( 'lightSpeedIn', 'fw' ),
							'lightSpeedOut' => __( 'lightSpeedOut', 'fw' ),
						),
					),	
					array(
						'attr'    => array(
							'label'         => __( 'Rotating Entrances', 'fw' ),
						),
						'choices' => array(
							'rotateIn' => __( 'rotateIn', 'fw' ),
							'rotateInDownLeft' => __( 'rotateInDownLeft', 'fw' ),
							'rotateInDownRight' => __( 'rotateInDownRight', 'fw' ),
							'rotateInUpLeft' => __( 'rotateInUpLeft', 'fw' ),
							'rotateInUpRight' => __( 'rotateInUpRight', 'fw' ),
						),
					),	
			/*		array(
						'attr'    => array(
							'label'         => __( 'Rotating Exits', 'fw' ),
						),
						'choices' => array(
							'rotateOut' => __( 'rotateOut', 'fw' ),
							'rotateOutDownLeft' => __( 'rotateOutDownLeft', 'fw' ),
							'rotateOutDownRight' => __( 'rotateOutDownRight', 'fw' ),
							'rotateOutUpLeft' => __( 'rotateOutUpLeft', 'fw' ),
							'rotateOutUpRight' => __( 'rotateOutUpRight', 'fw' ),
						),
					),	*/
					array(
						'attr'    => array(
							'label'         => __( 'Sliding Entrances', 'fw' ),
						),
						'choices' => array(
							'slideInUp' => __( 'slideInUp', 'fw' ),
							'slideInDown' => __( 'slideInDown', 'fw' ),
							'slideInLeft' => __( 'slideInLeft', 'fw' ),
							'slideInRight' => __( 'slideInRight', 'fw' ),
						),
					),	
			/*		array(
						'attr'    => array(
							'label'         => __( 'Sliding Exits', 'fw' ),
						),
						'choices' => array(
							'slideOutUp' => __( 'slideOutUp', 'fw' ),
							'slideOutDown' => __( 'slideOutDown', 'fw' ),
							'slideOutLeft' => __( 'slideOutLeft', 'fw' ),
							'slideOutRight' => __( 'slideOutRight', 'fw' ),
						),
					),	*/
					array(
						'attr'    => array(
							'label'         => __( 'Zoom Entrances', 'fw' ),
						),
						'choices' => array(
							'zoomIn' => __( 'zoomIn', 'fw' ),
							'zoomInDown' => __( 'zoomInDown', 'fw' ),
							'zoomInLeft' => __( 'zoomInLeft', 'fw' ),
							'zoomInRight' => __( 'zoomInRight', 'fw' ),
							'zoomInUp' => __( 'zoomInUp', 'fw' ),
						),
					),	
			/*		array(
						'attr'    => array(
							'label'         => __( 'Zoom Exits', 'fw' ),
						),
						'choices' => array(
							'zoomOut' => __( 'zoomOut', 'fw' ),
							'zoomOutDown' => __( 'zoomOutDown', 'fw' ),
							'zoomOutLeft' => __( 'zoomOutLeft', 'fw' ),
							'zoomOutRight' => __( 'zoomOutRight', 'fw' ),
							'zoomOutUp' => __( 'zoomOutUp', 'fw' ),
						),
					),	*/
					array(
						'attr'    => array(
							'label'         => __( 'Specials', 'fw' ),
						),
						'choices' => array(
							'hinge' => __( 'hinge', 'fw' ),
							'rollIn' => __( 'rollIn', 'fw' ),
							'rollOut' => __( 'rollOut', 'fw' ),
						),
					),						
				),
			),
			'duration'                => array(
				'label' => __( 'Duration', 'fw' ),
				'type'  => 'short-text',
				'value' => NULL,
				'desc'  => __( 'Change the animation duration. ',	'fw' ),
				'help'  => sprintf( "%s<br />%s",
					__( 'E.g.: <b>2s</b> for 2 seconds.', 'fw' ),
					__( 'Leave blank to disable.', 'fw' )
				),
			),
			'delay'                => array(
				'label' => __( 'Delay', 'fw' ),
				'type'  => 'short-text',
				'value' => NULL,
				'desc'  => __( 'The delay before the animation starts. ',	'fw' ),
				'help'  => sprintf( "%s<br />%s",
					__( 'E.g.: <b>5s</b> for 5 seconds.', 'fw' ),
					__( 'Leave blank to disable.', 'fw' )
				),
			),
			'offset'                => array(
				'label' => __( 'Offset', 'fw' ),
				'type'  => 'short-text',
				'value' => '',
				'desc'  => __( 'The distance to start the animation (related to the browser bottom).',	'fw' ),
				'help'  => sprintf( "%s<br />%s",
					__( 'E.g.: <b>10</b> for 10px.', 'fw' ),
					__( 'Leave blank to disable.', 'fw' )
				),
			),
			'iteration' => array(
				'label' => __( 'Iteration', 'fw' ),
				'type'  => 'short-text',
				'value' => NULL,
				'desc'  => __( 'Number of times the animation is repeated.','fw' ),
				'help'  => sprintf( "%s<br />%s<br />%s",
					__( 'E.g.: <b>10</b> for 10 times.', 'fw' ),
					__( 'Type <b>infinite</b> for infinite loop.', 'fw' ),
					__( 'Leave blank to disable.', 'fw' )
				),
			),
		);
	}
endif;


if(! function_exists('sc_option_visibility')) :
	/**
	 *  Visibility Options
	 */
	function sc_option_visibility() {
		$user_choices = array(
			'' => __( 'Visible for all', 'fw' ),
			'logged-in' => __( 'Visible for Logged in user', 'fw' ),
			'logged-out' => __( 'Visible for Logged out user', 'fw' ),
		);

		$wp_roles = wp_roles();
		$roles = $wp_roles->get_names();
		foreach($roles as $key => $role) {
			$user_choices['visible-'.$key] = __( 'Visible for '.$role.' user', 'fw' );
		}
		$user_choices['hidden'] = __( 'Hidden', 'fw' );

		return array(
			'label'         => false,
			'type'          => 'multi',
			'value'         => array(),
			'desc'          => false,
			'inner-options' => array(
				'responsive' => array(
					'label'   => __( 'Visibility', 'fw' ),
					'type'    => 'select-multiple',
					'value'   => '',
					'desc'    => __( 'Device\'s Responsiveness Visibility.','fw' ),
					'choices' => array(
						'd-none' 								=> __( 'Hidden on all devices', 'fw' ),
						'd-none d-sm-block' 		=> __( 'Hidden only on Extra Small devices. (x < 577px)', 'fw' ),
						'd-sm-none d-md-block' 	=> __( 'Hidden only on Small devices. (576px > x < 768px)', 'fw' ),
						'd-md-none d-lg-block' 	=> __( 'Hidden only on Medium devices. (767px > x < 993px)', 'fw' ),
						'd-lg-none d-xl-block' 	=> __( 'Hidden only on Large devices. (992px > x < 1201px)', 'fw' ),
						'd-xl-none' 						=> __( 'Hidden only on Extra Large devices. (x > 1200px)', 'fw' ),
						''														=> __( 'Visible on all devices', 'fw' ),
						'd-block d-sm-none' 					=> __( 'Visible only on Extra Small devices. (x < 577px)', 'fw' ),
						'd-none d-sm-block d-md-none'	=> __( 'Visible only on Small devices. (576px > x < 768px)', 'fw' ),
						'd-none d-md-block d-lg-none'	=> __( 'Visible only on Medium devices. (767px > x < 993px)', 'fw' ),
						'd-none d-lg-block d-xl-none'	=> __( 'Visible only on Large devices. (992px > x < 1201px)', 'fw' ),
						'd-none d-xl-block' 					=> __( 'Visible only on Extra Large devices. (x > 1200px)', 'fw' ),
					),
					'help' 	=> sprintf( "%s",
						__( 'Ctrl + Click to select multiple choices.','fw' )
					),
				),
				'user' => array(
					'label'   => __( '', 'fw' ),
					'type'    => 'select-multiple',
					'value'   => '',
					'desc'    => __( 'User Visibility','fw' ),
					'choices' => $user_choices,
					'help' 	=> sprintf( "%s",
						__( 'Ctrl + Click to select multiple choices.','fw' )
					),
				),
			),
		);
	}
endif;


if(! function_exists('sc_options_get_user_visibility')) :
/**
 *  Get Visibility Options
 */
function sc_options_get_user_visibility($atts) {
	
	if(!empty($atts['visibility']['user'])) {
		if(!empty($atts['visibility']['user'][0])) {

			$wp_roles = wp_roles();
			$roles = $wp_roles->get_names();
			$current_user_roles = wp_get_current_user()->roles;
			if(
				( in_array( 'logged-in', $atts['visibility']['user']) && is_user_logged_in() ) || 
				( in_array( 'logged-out', $atts['visibility']['user']) && !is_user_logged_in() ) ||
				( in_array( 'hidden', $atts['visibility']['user']) && !is_user_logged_in() )
			){
			}else{
				if(!empty($current_user_roles)) {
					foreach($roles as $key => $role) {
						foreach($current_user_roles as $current_user_role) {
							$check = 'visible-'.$current_user_role;
							if(!in_array( $check, $atts['visibility']['user'])) {
								$set_visible = true;
							}
						}
					}
					if(isset($set_visible)) return true;
				}else{
					return true;
				}
				
				
			}
		}		
	}
}
endif;


if(! function_exists('sc_get_shortcode_attr')) :
/**
 *  Get Shortcode Attributes
 */
function sc_get_shortcode_attr($atts) {
	//The classes for the block
	$class = array();
	$class[] = $atts['shortcode'];
	if(!empty($atts['animate']['animation'])) {
		$class[] = 'wow';
		$class[] = $atts['animate']['animation'];
	}
	if(!empty($atts['visibility']['responsive'])) {
		$class[] = $atts['visibility']['responsive'];
	}
	if(!empty($atts['visibility']['user'])) {
		if(( $atts['visibility']['user'] == 'logged-in') && !is_user_logged_in() ||
			($atts['visibility']['user'] == 'logged-out') && is_user_logged_in() ||
			($atts['visibility']['user'] == 'hidden')){
			$class[] = 'hidden';
		}
	}
	if(!empty($atts['class'])) {
		$class[] = $atts['class'];
	}
	$class = join( ' ', $class );
	
	//The attributes for the block
	$attr['class'] = $class;
	if(!empty($atts['custom_id'])){
		$attr['id'] = $atts['custom_id'];
	}
	if(!empty($atts['animate']['duration'])){
		$attr['data-wow-duration'] = $atts['animate']['duration'];
	}
	if(!empty($atts['animate']['delay'])){
		$attr['data-wow-delay'] = $atts['animate']['delay'];
	}
	if(!empty($atts['animate']['offset'])){
		$attr['data-wow-offset'] = $atts['animate']['offset'];
	}
	if(!empty($atts['animate']['iteration'])){
		$attr['data-wow-iteration'] = $atts['animate']['iteration'];
	}
	return $attr;
}
endif;


if(!function_exists('sc_option_spacing')) :
	/**
	 * Spacing Options
	 */
	function sc_option_spacing( $default = NULL ) {
		return array(
			'type'         => 'multi-picker',
			'label'        => false,
			'desc'         => false,
			'value'        => array(
				'selected' => 'bootstrap',
				'bootstrap' => $default,
			),
			'picker'       => array(
				'selected' => array(
					'label'   => __( 'Spacing', 'fw' ),
					'type'    => 'select',
					'choices' => array(
						'bootstrap' => __( 'Bootstrap margins and paddings (Recommended)', 'fw' ),
						'custom' 		=> __( 'Custom margins and paddings', 'fw' )
					),
					'desc'    => __( 'Select spacing method.', 'fw' ),
					'help'    => __( 'Using custom method will add new CSS classes for each element.', 'fw' ),
				)
			),
			'choices'      => array(
				'bootstrap'  => array(
					'all'   => sc_option_bs_spacing( '' ),
					'responsive' => array(
						'type' => 'popup',
						'value' => array(
						),
						'label' 		=> __('', 'fw' ),
						'desc'  		=> __( '', 'fw' ),
						'popup-title' => __('Responsive Breakpoints', 'fw' ),
						'button' 		=> __('Responsive Breakpoints', 'fw' ),
						'popup-title' => __('Responsive Breakpoints', 'fw' ),
						'size' 			=> 'medium', // small, medium, large
						'popup-options' => array(
							'sm'		=> sc_option_bs_spacing( 'sm' ),
							'md'  	=> sc_option_bs_spacing( 'md' ),
							'lg'  	=> sc_option_bs_spacing( 'lg' ),
							'xl'  	=> sc_option_bs_spacing( 'xl' ),
						),
					),
				),
				'custom' => array(
					'mall'	=> sc_option_box( '', 'Margin for all devices' ),
					'pall' 	=> sc_option_box( '', 'Padding for all devices' ),
					'responsive' => array(
						'type' => 'popup',
						'value' => array(
						),
						'label' 		=> __('', 'fw' ),
						'desc'  		=> __( '', 'fw' ),
						'popup-title' => __('Responsive Breakpoints', 'fw' ),
						'button' 		=> __('Responsive Breakpoints', 'fw' ),
						'popup-title' => __('Responsive Breakpoints', 'fw' ),
						'size' 			=> 'medium', // small, medium, large
						'popup-options' => array(
							'msm'		=> sc_option_box( 'Phones', 'Margin for small devices (landscape phones, <strong>576px</strong> and up)' ),
							'psm' 	=> sc_option_box( '', 'Padding for small devices (landscape phones, <strong>576px</strong> and up)' ),
							'mmd'		=> sc_option_box( 'Tablets', 'Margin for medium devices (tablets  phones, <strong>768px</strong> and up)' ),
							'pmd' 	=> sc_option_box( '', 'Padding for medium devices (tablets  phones, <strong>768px</strong> and up)' ),
							'mlg'		=> sc_option_box( 'Desktops', 'Margin for large devices (desktops, <strong>992px</strong> and up)' ),
							'plg' 	=> sc_option_box( '', 'Padding for large devices (desktops, <strong>992px</strong> and up)' ),
							'mxl'		=> sc_option_box( 'Large Desktops', 'Margin for extra large devices (large desktops, <strong>1200px</strong> and up)' ),
							'pxl' 	=> sc_option_box( '', 'Padding for extra large devices (large desktops, <strong>1200px</strong> and up)' ),
						),
					),
				),
			),
			'show_borders' => false,
		);
	}
endif;


if(!function_exists('sc_option_bs_spacing')) :
	/**
	 * Margin & Padding Options
	 */
	function sc_option_bs_spacing( $breakpoint ) {
		if( $breakpoint == 'sm' ) {
			$breakpointlabel = 'Phones';
			$breakpointdesc = 'Margin and Padding for small devices (landscape phones, <strong>576px</strong> and up)';
		}elseif( $breakpoint == 'md' ) {
			$breakpointlabel = 'Tablets';
			$breakpointdesc = 'Margin and Padding for medium devices (tablets  phones, <strong>768px</strong> and up)';
		}elseif( $breakpoint == 'lg' ) {
			$breakpointlabel = 'Desktops';
			$breakpointdesc = 'Margin and Padding for large devices (desktops, <strong>992px</strong> and up)';
		}elseif( $breakpoint == 'xl' ) {
			$breakpointlabel = 'Large Desktops';
			$breakpointdesc = 'Margin and Padding for extra large devices (large desktops, <strong>1200px</strong> and up)';
		}else{
			$breakpointlabel = '';
			$breakpointdesc = 'Margin and Padding for all devices';
		}
		return array(
			'type'      => 'multi-select',
			'label'     => __( $breakpointlabel, 'fw' ),
			'desc'      => __( $breakpointdesc,	'fw' ),
			//'value'			=> array( 'py-4' ),
			'population'=> 'array',
			'choices'   => sc_option_bs_spacing_choices( $breakpoint ),
		);
	}
endif;


if(!function_exists('sc_option_bs_spacing_choices')) :
	/**
	 * Margin & Padding Options
	 */
	function sc_option_bs_spacing_choices( $breakpoint ) {
		return array_merge(
			sc_option_bs_spacing_size_choices( 'm', '', $breakpoint ),
			sc_option_bs_spacing_size_choices( 'm', 't', $breakpoint ),
			sc_option_bs_spacing_size_choices( 'm', 'r', $breakpoint ),
			sc_option_bs_spacing_size_choices( 'm', 'b', $breakpoint ),
			sc_option_bs_spacing_size_choices( 'm', 'l', $breakpoint ),
			sc_option_bs_spacing_size_choices( 'm', 'x', $breakpoint ),
			sc_option_bs_spacing_size_choices( 'm', 'y', $breakpoint ),
			sc_option_bs_spacing_size_choices( 'p', '', $breakpoint ),
			sc_option_bs_spacing_size_choices( 'p', 't', $breakpoint ),
			sc_option_bs_spacing_size_choices( 'p', 'r', $breakpoint ),
			sc_option_bs_spacing_size_choices( 'p', 'b', $breakpoint ),
			sc_option_bs_spacing_size_choices( 'p', 'l', $breakpoint ),
			sc_option_bs_spacing_size_choices( 'p', 'x', $breakpoint ),
			sc_option_bs_spacing_size_choices( 'p', 'y', $breakpoint )
		);
	}
endif;


if(!function_exists('sc_option_bs_margin')) :
	/**
	 * Margin & Padding Options
	 */
	function sc_option_bs_margin( $breakpoint ) {
		if( $breakpoint == 'sm' ) {
			$breakpointlabel = 'Phones';
			$breakpointdesc = 'Margin for small devices (landscape phones, <strong>576px</strong> and up)';
		}elseif( $breakpoint == 'md' ) {
			$breakpointlabel = 'Tablets';
			$breakpointdesc = 'Margin for medium devices (tablets  phones, <strong>768px</strong> and up)';
		}elseif( $breakpoint == 'lg' ) {
			$breakpointlabel = 'Desktops';
			$breakpointdesc = 'Margin for large devices (desktops, <strong>992px</strong> and up)';
		}elseif( $breakpoint == 'xl' ) {
			$breakpointlabel = 'Large Desktops';
			$breakpointdesc = 'Margin for extra large devices (large desktops, <strong>1200px</strong> and up)';
		}else{
			$breakpointlabel = '';
			$breakpointdesc = 'Margin for all devices';
		}
		return array(
			'type'      => 'multi-select',
			'label'     => __( $breakpointlabel, 'fw' ),
			'desc'      => __( $breakpointdesc,	'fw' ),
			//'value'			=> array( 'py-4' ),
			'population'=> 'array',
			'choices'   => sc_option_bs_margin_choices( $breakpoint ),
		);
	}
endif;


if(!function_exists('sc_option_bs_margin_choices')) :
	/**
	 * Margin & Padding Options
	 */
	function sc_option_bs_margin_choices( $breakpoint ) {
		return array_merge(
			sc_option_bs_spacing_size_choices( 'm', '', $breakpoint ),
			sc_option_bs_spacing_size_choices( 'm', 't', $breakpoint ),
			sc_option_bs_spacing_size_choices( 'm', 'r', $breakpoint ),
			sc_option_bs_spacing_size_choices( 'm', 'b', $breakpoint ),
			sc_option_bs_spacing_size_choices( 'm', 'l', $breakpoint ),
			sc_option_bs_spacing_size_choices( 'm', 'x', $breakpoint ),
			sc_option_bs_spacing_size_choices( 'm', 'y', $breakpoint )
		);
	}
endif;


if(!function_exists('sc_option_bs_spacing_size_choices')) :
	/**
	 * Margin & Padding Options
	 */
	function sc_option_bs_spacing_size_choices( $property, $sides, $breakpoint ) {
		$spacer = 16;
		if( $property == 'm' ) {
			$propertytext = 'margin';
		}
		if( $property == 'p' ) {
			$propertytext = 'padding';
		}
		if( $sides == 't' ) {
			$sidestext = ' top';
		}elseif( $sides == 'r' ) {
			$sidestext = ' right';
		}elseif( $sides == 'b' ) {
			$sidestext = ' bottom';
		}elseif( $sides == 'l' ) {
			$sidestext = ' left';
		}elseif( $sides == 'x' ) {
			$sidestext = ' left and right';
		}elseif( $sides == 'y' ) {
			$sidestext = ' top and bottom';
		}else{
			$sidestext = '';
		}
		if( !empty($breakpoint) )		$breakpoint = '-' . $breakpoint;
		return array(
			$property . $sides . $breakpoint . '-0' 	=> __( $propertytext . $sidestext . ' - none ' . ' (' . ($spacer * 0) . 'px)', 'fw' ),
			$property . $sides . $breakpoint . '-1' 	=> __( $propertytext . $sidestext . ' - extra small ' . ' (' . ($spacer * .25) . 'px)', 'fw' ),
			$property . $sides . $breakpoint . '-2' 	=> __( $propertytext . $sidestext . ' - small ' . ' (' . ($spacer * .5) . 'px)', 'fw' ),
			$property . $sides . $breakpoint . '-3' 	=> __( $propertytext . $sidestext . ' - medium ' . ' (' . $spacer . 'px)', 'fw' ),
			$property . $sides . $breakpoint . '-4' 	=> __( $propertytext . $sidestext . ' - large ' . ' (' . ($spacer * 1.5) . 'px)', 'fw' ),
			$property . $sides . $breakpoint . '-5' 	=> __( $propertytext . $sidestext . ' - extra large ' . ' (' . ($spacer * 3) . 'px)', 'fw' ),
			$property . $sides . $breakpoint . '-auto' 	=> __( $propertytext . $sidestext . ' - auto ', 'fw' ),
		);
	}
endif;


if(!function_exists('sc_option_margin')) :
	/**
	 * Margin & Padding Options
	 */
	function sc_option_margin() {
		return array(
			'type'         => 'multi-picker',
			'label'        => false,
			'desc'         => false,
			'value'        => array(
				'selected' => 'bootstrap',
				'bootstrap' => null,
			),
			'picker'       => array(
				'selected' => array(
					'label'   => __( 'Spacing', 'fw' ),
					'type'    => 'select',
					'choices' => array(
						'bootstrap' => __( 'Bootstrap margins (Recommended)', 'fw' ),
						'custom' 		=> __( 'Custom margins', 'fw' )
					),
					'desc'    => __( 'Select spacing method.', 'fw' ),
					'help'    => __( 'Using custom method will add new CSS classes for each element.', 'fw' ),
				)
			),
			'choices'      => array(
				'bootstrap'  => array(
					'all'   => sc_option_bs_margin( '' ),
					'responsive' => array(
						'type' => 'popup',
						'value' => array(
						),
						'label' 		=> __('', 'fw' ),
						'desc'  		=> __( '', 'fw' ),
						'popup-title' => __('Responsive Breakpoints', 'fw' ),
						'button' 		=> __('Responsive Breakpoints', 'fw' ),
						'popup-title' => __('Responsive Breakpoints', 'fw' ),
						'size' 			=> 'medium', // small, medium, large
						'popup-options' => array(
							'sm'		=> sc_option_bs_margin( 'sm' ),
							'md'  	=> sc_option_bs_margin( 'md' ),
							'lg'  	=> sc_option_bs_margin( 'lg' ),
							'xl'  	=> sc_option_bs_margin( 'xl' ),
						),
					),
				),
				'custom' => array(
					'mall'	=> sc_option_box( '', 'Margin for all devices' ),
					'responsive' => array(
						'type' => 'popup',
						'value' => array(
						),
						'label' 		=> __('', 'fw' ),
						'desc'  		=> __( '', 'fw' ),
						'popup-title' => __('Responsive Breakpoints', 'fw' ),
						'button' 		=> __('Responsive Breakpoints', 'fw' ),
						'popup-title' => __('Responsive Breakpoints', 'fw' ),
						'size' 			=> 'medium', // small, medium, large
						'popup-options' => array(
							'msm'		=> sc_option_box( 'Phones', 'Margin for small devices (landscape phones, <strong>576px</strong> and up)' ),
							'mmd'		=> sc_option_box( 'Tablets', 'Margin for medium devices (tablets  phones, <strong>768px</strong> and up)' ),
							'mlg'		=> sc_option_box( 'Desktops', 'Margin for large devices (desktops, <strong>992px</strong> and up)' ),
							'mxl'		=> sc_option_box( 'Large Desktops', 'Margin for extra large devices (large desktops, <strong>1200px</strong> and up)' ),
						),
					),
				),
			),
			'show_borders' => false,
		);
	}
endif;



if(!function_exists('sc_option_box')) :
	/**
	 * Margin & Padding Options
	 */
	function sc_option_box($label, $desc=NULL, $top=NULL, $right=NULL, $bottom=NULL, $left=NULL) {
		return array(
			'type' 	=> 'multi-inline',
			'label' => __($label, 'fw' ),
			'desc' 	=> __($desc, 'fw' ),
			'value' => array(
				'top' 	 	=> $top,
				'right'  	=> $right,
				'bottom' 	=> $bottom,
				'left' 	 	=> $left,	
			),
			'help'      => __( 'Input values in pixels. i.e.: 60',	'fw' ),
			'fw_multi_options' => array(
				'top' => array(
					'type' 	=>'short-text',
					'title' => __('Top', 'fw' ),
				),
				'right' => array(
					'type' 	=>'short-text',
					'title' => __('Right', 'fw' ),
				),
				'bottom' => array(
					'type' 	=>'short-text',
					'title' => __('Bottom', 'fw' ),
				),
				'left' => array(
					'type' 	=>'short-text',
					'title' => __('Left', 'fw' ),
				),
			)
		);
	}
endif;


if(!function_exists('sc_option_box_border')) :
	/**
	 * Border Options
	 */
	function sc_option_box_border($label,$top='',$right='',$bottom='',$left='') {
		return array(
			'type' => 'checkboxes',
			'label' => __($label, 'fw' ),
			//'desc' => __('', 'fw' ),
			'value' => array(
				'top' 	=>$top,
				'right' =>$right,
				'bottom'=>$bottom,
				'left' 	=>$left,	
			),
			'choices' => array(
				'top' 	=> __('Top', 'fw' ),
				'right' => __('Right', 'fw' ),
				'bottom'=> __('Bottom', 'fw' ),
				'left' 	=> __('Left', 'fw' ),
			),
			'inline' => true,
			'attr'  => array( 'class' => 'border-options'),
		);
	}
endif; 


if(!function_exists('sc_get_options_box_border')) :
	/**
	 * Get Border Options
	 */
	function sc_get_options_box_border($atts) {
		$border = array(
			'border'				=> array('top' => true, 'right' => true, 'bottom' => true, 'left' => true),
			'border-top'		=> array('top' => true),
			'border-right'	=> array('right' => true),
			'border-bottom'	=> array('bottom' => true),
			'border-left'		=> array('left' => true),
			//'border-0'		=> array('top' => true, 'right' => true, 'bottom' => true, 'left' => true),
			'border-top-0'	=> array('right' => true, 'bottom' => true, 'left' => true),
			'border-right-0'=> array('top' => true, 'bottom' => true, 'left' => true),
			'border-bottom-0'=> array('top' => true, 'right' => true, 'left' => true),
			'border-left-0'	=> array('top' => true, 'right' => true, 'bottom' => true),
		);

		while ($bordervalue = current($border)) {
			if ($bordervalue == $atts['side']) {
					$border_value =  key($border);
			}
			next($border);
		}
		
		if(empty($border_value) && array_filter($atts['side'])){
			foreach($atts['side'] as $key => $value)
			{
				$atts['side'][$key] = 'border-'.$key;
			} 
			return join(' ', $atts['side']);
		}else{
			return $border_value;
		}
	}
endif;


if(!function_exists('sc_option_box_border_radius')) :
	/**
	 * Border Radius Options
	 */
	function sc_option_box_border_radius($label) {
		return array(
			'type'    => 'select',
			'label'   => __('', 'fw' ),
			'desc'   	=> $label,
			'value'   => '',
			'choices' => array(
				''  						=> 'none',
				'rounded' 			=> 'Rounded',
				'rounded-top'		=> 'Rounded Top',
				'rounded-right' => 'Rounded Right',
				'rounded-bottom'=> 'Rounded Bottom',
				'rounded-left' 	=> 'Rounded Left',
				'rounded-circle'=> 'Circle',
			)
		);
	}
endif; 

// This function is deprecated
if(! function_exists('get_css_box_measurements')){
	function get_css_box_measurements($side_size) {
		if($side_size['select'] == 'custom'):
			return 'unquote("'.$side_size['custom']['size'].'")';
		else:
			return $side_size['select'];
		endif;
	}
}


if(! function_exists('sc_option_custom_id')) :
	/**
	 * Custom ID
	 */
	function sc_option_custom_id($label='CSS ID',$desc=NULL) {
		return array(
			'label'   => $label,
			'desc'    => $desc,
			'type'    => 'text'
		);
	}
endif;


if(! function_exists('sc_option_class')) :
/**
* Class
*/
function sc_option_class() {
	return array(
		'label'   => __('CSS Class', 'fw' ),
		'desc'    => false,
		'type'    => 'text'
	);
}
endif;

if(! function_exists('sc_options_get_id')) :
/**
 * Get the ID
 */
function sc_options_get_id($shortcode,$id,$custom_id) {
	if (!empty($custom_id)) : 
		return $custom_id;
	else :
		return substr($shortcode, 0, 3).'-'.substr($id, 0, 10);
	endif;
}
endif;


if(! function_exists('sc_options_add_scss')) :
/**
 * Get the ID
 */
function sc_options_add_scss($atts,$scss) {
	$shortcode_style = '';
	foreach ($scss as $key => $value){
		$shortcode_style .= $value;
	}
			
	if( !get_option( $atts['shortcode'].'_style' ) ) {
		$shortcode_styles = array();
		$shortcode_styles[get_the_ID()][$atts['id']] = $shortcode_style;		
		add_option( $atts['shortcode'].'_style', $shortcode_styles, '', 'yes' );
	} else {
		$shortcode_styles = get_option( $atts['shortcode'].'_style' );		
		$shortcode_styles[get_the_ID()][$atts['id']] = $shortcode_style;
		update_option( $atts['shortcode'].'_style', $shortcode_styles, 'yes' );
	}	
	
	if( !get_option( $atts['shortcode'].'_style_temp' ) ) {
		$shortcode_styles_temp = array();
		$shortcode_styles_temp[$atts['id']] = $shortcode_style;		
		add_option( $atts['shortcode'].'_style_temp', $shortcode_styles_temp, '', 'yes' );
	} else {	
		$shortcode_styles_temp = get_option( $atts['shortcode'].'_style_temp');	
		$shortcode_styles_temp[$atts['id']] = $shortcode_style;
		update_option( $atts['shortcode'].'_style_temp', $shortcode_styles_temp, 'yes' );
	}	
	$shortcode_styles[get_the_ID()] = array_intersect_key($shortcode_styles[get_the_ID()], $shortcode_styles_temp);
	update_option( $atts['shortcode'].'_style', $shortcode_styles, 'yes' );
}
endif;