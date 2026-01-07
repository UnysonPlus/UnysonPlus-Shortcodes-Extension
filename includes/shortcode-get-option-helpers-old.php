<?php if ( ! defined( 'ABSPATH' ) ) die( 'Direct access forbidden.' );

if( !function_exists( 'sc_get_option_typography_css' ) ) :
	/**
	 * Color Picker
	 */
	function sc_get_option_typography_css( $field ) {
		$output = '';

		if ( isset($field['size']) ) {
			$output .= 'font-size: rem(' . $field['size'] . 'px);';
			$output .= "\r\n";
		} 
		if ( isset($field['family']) ) {
			$output .= 'font-family: \'' . $field['family'] . '\';';
			$output .= "\r\n";
		}
		if ( isset($field['color']) ) {
			$output .= 'color: ' . $field['color'] . ';';
			$output .= "\r\n";
		}
		if ( isset($field['style']) ) {
			$pattern = '/(\d+)|(regular|italic)/i';
			preg_match_all($pattern, $field['style'], $matches);
			foreach ($matches[0] as $value) {
				if ( $value == 'italic' ) {
					$output .= 'font-style: ' . $value . ';';
					$output .= "\r\n";
				} else if ( $value == 'regular' ) {
					$output .= 'font-style: normal;';
					$output .= "\r\n";
				} else {
					$output .= 'font-weight: ' . $value . ';';
					$output .= "\r\n";
				}
			}
		}
		if ( isset($field['variation']) ) {
			$pattern = '/(\d+)|(regular|italic)/i';
			preg_match_all($pattern, $field['variation'], $matches);
			foreach ($matches[0] as $value) {
				if ( $value == 'italic' ) {
					$output .= 'font-style: ' . $value . ';';
					$output .= "\r\n";
				} else if ( $value == 'regular' ) {
					$output .= 'font-style: normal;';
					$output .= "\r\n";
				} else {
					$output .= 'font-weight: ' . $value . ';';
					$output .= "\r\n";
				}
			}
		}
		if ( isset($field['line-height']) ) {
			$output .= 'line-height: ' . $field['line-height'] . ';';
			$output .= "\r\n";
		}
		if ( isset($field['letter-spacing']) && !empty($field['letter-spacing']) ) {
			$output .= 'letter-spacing: rem(' . $field['letter-spacing'] . 'px);';
			$output .= "\r\n";
		}
		return $output;
	}
endif;


if ( ! function_exists( 'sc_get_option_star_rating' )) :
/**
 *  Star Rating
 */
	function sc_get_option_star_rating( $rating ) {
		$star[] = '<span class="rating">';
 		for( $i = 0; $i < 5; $i++ ) {
			if( ($rating - $i) == 0.5 ){
				$star[] = '<span class="fa fa-star"><span class="fa fa-star half"></span></span>';
			}elseif( $rating > $i ) {
				$star[] = '<span class="fa fa-star checked"></span>';
			}else{
				$star[] = '<span class="fa fa-star"></span>';
			}
		}
		$star[] = '</span>';
		return join( '', $star );
	}
endif;


if(! function_exists('sc_get_options_background_css')) :
	function sc_get_options_background_css($atts) {
		if(isset($atts['background'])) {
			$bg = $atts['background'];
			// Check if there is a custom background.
			if (is_array($bg['image'])) { // Check if a custom background has been set
				$image	= $bg['image']['data']['css']['background-image'];
				$color	= sc_get_option_color_picker( $bg['color'] );
				
				if ( ($image != 'none') || (!empty($color)) ) {
					$position 	= $bg['position'].' ';
					$repeat		= $bg['repeat'];
					if( $image == 'none' && !empty($color) ) {
						$background = 'background:' . $color . ';';	
					}elseif( $image != 'none' && empty($color) ) {
						$background = 'background: ' . $image . ' ' . $position . $repeat .';';	
					}else{
						$background = 'background: ' . $image . ' ' . $color . ' ' .  $position . $repeat .';';
					}
					if ( ($image != 'none') && $bg['size']['selected'] != 'auto' ) {
						$custom = explode( ' ', ltrim($bg['size']['custom']) );
						  foreach($custom as $key => $value)
							{
								$custom[$key] = 'rem(' . $value . ')';
							}
						if( $bg['size']['selected'] != 'custom' ) {
							$size 		= 'background-size: ' . $bg['size']['selected'] . ';';
						}else{
							$size 		= 'background-size: ' . join( ' ', $custom ) . ';';
						}
					}else{
						$size 		= '';
					}
				}else{
					$background = '';
					$size 		= '';
				}
			}else{
				$background = '';
				$size 		= '';
			}
			$shortcode_atts[] = $background;
			$shortcode_atts[] = $size;
			
			if( isset( $bg['overlay'] ) && ( $bg['overlay']['selected'] == 'yes' ) ) {
				if( !empty( sc_get_option_color_picker( $bg['overlay']['yes']['color'] ) ) ) {
					$overlay = array();
					$direction = $bg['overlay']['yes']['direction'];
					$color = sc_get_option_color_picker( $bg['overlay']['yes']['color'] );
					if( !empty( sc_get_option_color_picker( $bg['overlay']['yes']['gradient']) ) ) {
						$gradient = sc_get_option_color_picker( $bg['overlay']['yes']['gradient'] );
					}else{
						$gradient = $color;
					}
					$overlay[] = '&:before { ';
					$overlay[] = 'content: \'\';
						position: absolute;
						top: 0;
						bottom: 0;
						left: 0;
						right: 0;
						background-image: linear-gradient(to ' . $direction . ', ' . $color . ', ' . $gradient .');
						opacity: '. $bg['overlay']['yes']['opacity'] . ';';
					$overlay[] = '}';
					$shortcode_atts[] = join( "\r\n", $overlay );
				}
			}
			return $shortcode_atts;
		}else{
			return array();
		}
	}
endif;


if( ! function_exists('sc_get_option_image') ) :
/** NOT USED... To be deleted.
 *  Get the image from options
 */
	function sc_get_option_image( $atts ) {
	
		$width  = ( is_numeric( $atts['width'] ) && ( $atts['width'] > 0 ) ) ? $atts['width'] : '';
		$height = ( is_numeric( $atts['height'] ) && ( $atts['height'] > 0 ) ) ? $atts['height'] : '';

		if ( ! empty( $width ) && ! empty( $height ) ) {
			$src = fw_resize( $atts['image']['attachment_id'], $width, $height, true );
		} else {
			$src = $atts['image']['url'];
		}

		$meta = wp_prepare_attachment_for_js( $atts['image']['attachment_id'] ); 

		$attr = array(
			'src' => $src,
			'alt' => $meta['alt'] ? $meta['alt'] : $meta['title']
		);

		if(!empty($width)){
			$attr['width'] = $width;
		}else{
			$attr['width'] = $meta['width'];
		}

		if(!empty($height)){
			$attr['height'] = $height;
		}else{
			$attr['height'] = $meta['height'];
		}
		
		$class = array();
		$class[] = 'img-fluid';
		if( !empty($atts['alignment']) ) {
			$class[] = $atts['alignment'];
			if( !empty($atts['alignment_responsive']['sm']) ) $class[] = $atts['alignment_responsive']['sm'];
			if( !empty($atts['alignment_responsive']['md']) ) $class[] = $atts['alignment_responsive']['md'];
			if( !empty($atts['alignment_responsive']['lg']) ) $class[] = $atts['alignment_responsive']['lg'];
			if( !empty($atts['alignment_responsive']['xl']) ) $class[] = $atts['alignment_responsive']['xl'];
		}
		$class = array_merge( $class, sc_get_option_advanced_class( $atts ) );
		$attr['class'] = join(' ', $class);
		$attr = array_merge( $attr, sc_get_option_animate_attr( $atts ) );

		$attr['src'] = $atts['image']['url'];
		
		return sc_html_tag( 'img', $attr, true );
	}
endif;


if( !function_exists('sc_get_option_color_picker') ):
	/**
	 * Get Color Picker
	 */
	function sc_get_option_color_picker( $atts ) {
		if(!empty($atts['predefined'])) {
			return $atts['predefined'];
		}else{
			return $atts['custom'];
		}
	}
endif;


if( !function_exists('sc_get_option_link') ):
	/**
	 * Get Link
	 */
	function sc_get_option_link( array $link, $content = NULL ) {
		if( !empty($link[$link['selected']]['href']) ) {
			if($link['selected'] == 'page' || $link['selected'] == 'post') {
				$link[$link['selected']]['href'] = get_permalink($link[$link['selected']]['href'][0]);
			}elseif($link['selected'] == 'media') {
				$link[$link['selected']]['href'] = $link[$link['selected']]['href']['url'];
			}
			return sc_html_tag('a', $link[$link['selected']], $content);
		}else{
			return $content;
		}
	}
endif;


if( !function_exists('sc_get_option_advanced_class') ) :
	/**
	 * Get Advanced Classes
	 */
	function sc_get_option_advanced_class( $atts ) {
		$advanced_class = array();
		
		// Get Hover Class
		if( isset( $atts['hover'] ) ) {
			if( !empty( $atts['hover']['2d'] ) )																								$advanced_class[] = $atts['hover']['2d'];
			if( !empty( $atts['hover']['background'] ) )																				$advanced_class[] = $atts['hover']['background'];
			if( !empty( $atts['hover']['border'] ) )																						$advanced_class[] = $atts['hover']['border'];
			if( !empty( $atts['hover']['shadow'] ) )																						$advanced_class[] = $atts['hover']['shadow'];
			if( !empty( $atts['hover']['speech_bubbles'] ) )																		$advanced_class[] = $atts['hover']['speech_bubbles'];
			if( !empty( $atts['hover']['curls'] ) )																							$advanced_class[] = $atts['hover']['curls'];
		}
		
		// Get border class
		if( isset($atts['border']) ) {
			if ( array_filter($atts['border']['side'])) 																				$advanced_class[] = sc_get_options_box_border($atts['border']);
			if ( !empty($atts['border']['color']) && array_filter($atts['border']['side'])) 		$advanced_class[] = $atts['border']['color'];
			if ( !empty($atts['border']['radius']) && array_filter($atts['border']['side']))		$advanced_class[] = $atts['border']['radius'];
		}
		// Get animate class
		if( isset($atts['animate']) && !empty($atts['animate']['animation']))	{
			$advanced_class[] = 'wow';
			$advanced_class[] = $atts['animate']['animation'];
		}
		// Get spacing class
		if( isset($atts['spacing']) && ($atts['spacing']['selected'] == 'bootstrap') ) {
			if( !empty($atts['spacing']['bootstrap']['all']) ) {
				$advanced_class[] = join( ' ', $atts['spacing']['bootstrap']['all'] );
				if( !empty($atts['spacing']['bootstrap']['responsive']['sm']) )										$advanced_class[] = join( ' ', $atts['spacing']['bootstrap']['responsive']['sm'] );
				if( !empty($atts['spacing']['bootstrap']['responsive']['md']) )										$advanced_class[] = join( ' ', $atts['spacing']['bootstrap']['responsive']['md'] );
				if( !empty($atts['spacing']['bootstrap']['responsive']['lg']) )										$advanced_class[] = join( ' ', $atts['spacing']['bootstrap']['responsive']['lg'] );
				if( !empty($atts['spacing']['bootstrap']['responsive']['xl']) )										$advanced_class[] = join( ' ', $atts['spacing']['bootstrap']['responsive']['xl'] );
			}
		}
		// Get visibility class
		if( isset($atts['visibility']) && !empty($atts['visibility']['responsive']) && !in_array('', $atts['visibility']['responsive']) ) 	$advanced_class[] = join( ' ', $atts['visibility']['responsive']);
		// Get class
		if( isset($atts['class']) && !empty($atts['class'])) 		$advanced_class[] = $atts['class'];	
		return $advanced_class;		
	}
endif;


if( !function_exists('sc_get_option_spacing_css_property') ) :
	/**
	 * Get Advanced Classes
	 */
	function sc_get_option_spacing_css_property( $atts, $spacing, $property ) { 
		$output = NULL;
		if( $spacing == 'mall' || $spacing == 'pall' ){
			$custom = $atts['spacing']['custom'];
		}else{
			$custom = $atts['spacing']['custom']['responsive'];
		}
		if( isset($atts['spacing']) && ($atts['spacing']['selected'] == 'custom') )		{
			$array_check = false;
			foreach($custom[$spacing] as $key => $value) {
				if( $value != '' )		$array_check = true;
			}
			if( $array_check ) {
			//if( !in_array( '', $atts['spacing']['custom'][$spacing]) ) {
				foreach($custom[$spacing] as $key => $value) {
					$value = preg_replace('/[^0-9]/', '', $value);
					if( $value == '' ) { 
						$custom[$spacing][$key] = '0'; 
					}else{
						$custom[$spacing][$key] = 'rem(' . $value . ')';
					}
				}
				$output = $property . ': ' . join( ' ', $custom[$spacing] ) . ';';
			}
		}
		return $output;
	}
endif;


if( !function_exists('sc_get_option_spacing_css') ) :
	/**
	 * Spacing Breakpoints CSS
	 */
	function sc_get_option_spacing_css( $atts ) {
		$shortcode_atts = array();
		$wrapper = '';
		if( isset( $atts['shortcode'] ) && in_array( $atts['shortcode'], array( 'section', 'column') ) ) $wrapper = ' .wrap';
		global $post;
		
		if( (null !== sc_get_option_spacing_css_property( $atts, 'mall', 'margin') ) || ( null !== sc_get_option_spacing_css_property( $atts, 'pall', 'padding' )) ) {
			$shortcode_atts[] = '.' . $post->post_type . '-' . $post->post_name . ' .' . $atts['id'] . $wrapper . ' { ';
				if( null !== sc_get_option_spacing_css_property( $atts, 'mall', 'margin' ) )			$shortcode_atts[]	= sc_get_option_spacing_css_property( $atts, 'mall', 'margin' );
				if( null !== sc_get_option_spacing_css_property( $atts, 'pall', 'padding' ) )		$shortcode_atts[]	= sc_get_option_spacing_css_property( $atts, 'pall', 'padding' );
			$shortcode_atts[] = '}';
		}
		
		if( (null !== sc_get_option_spacing_css_property( $atts, 'msm', 'margin') ) || ( null !== sc_get_option_spacing_css_property( $atts, 'psm', 'padding' )) ) {
			$shortcode_atts[] = '@media (min-width: 576px) {';
				$shortcode_atts[] = '.' . $post->post_type . '-' . $post->post_name . ' .' . $atts['id'] . $wrapper . ' { ';
					if( null !== sc_get_option_spacing_css_property( $atts, 'msm', 'margin' ) )		$shortcode_atts[]	= sc_get_option_spacing_css_property( $atts, 'msm', 'margin' );
					if( null !== sc_get_option_spacing_css_property( $atts, 'psm', 'padding' ) )		$shortcode_atts[]	= sc_get_option_spacing_css_property( $atts, 'psm', 'padding' );
				$shortcode_atts[] = '}';
			$shortcode_atts[] = '}';
		}
		
		if( (null !== sc_get_option_spacing_css_property( $atts, 'mmd', 'margin') ) || ( null !== sc_get_option_spacing_css_property( $atts, 'pmd', 'padding' )) ) {
			$shortcode_atts[] = '@media (min-width: 768px) {';
				$shortcode_atts[] = '.' . $post->post_type . '-' . $post->post_name . ' .' . $atts['id'] . $wrapper . ' { ';
					if( null !== sc_get_option_spacing_css_property( $atts, 'mmd', 'margin' ) )		$shortcode_atts[]	= sc_get_option_spacing_css_property( $atts, 'mmd', 'margin' );
					if( null !== sc_get_option_spacing_css_property( $atts, 'pmd', 'padding' ) )		$shortcode_atts[]	= sc_get_option_spacing_css_property( $atts, 'pmd', 'padding' );
				$shortcode_atts[] = '}';
			$shortcode_atts[] = '}';
		}
		
		if( (null !== sc_get_option_spacing_css_property( $atts, 'mlg', 'margin') ) || ( null !== sc_get_option_spacing_css_property( $atts, 'plg', 'padding' )) ) {
			$shortcode_atts[] = '@media (min-width: 992px) {';
				$shortcode_atts[] = '.' . $post->post_type . '-' . $post->post_name . ' .' . $atts['id'] . $wrapper . ' { ';
					if( null !== sc_get_option_spacing_css_property( $atts, 'mlg', 'margin' ) )		$shortcode_atts[]	= sc_get_option_spacing_css_property( $atts, 'mlg', 'margin' );
					if( null !== sc_get_option_spacing_css_property( $atts, 'plg', 'padding' ) )		$shortcode_atts[]	= sc_get_option_spacing_css_property( $atts, 'plg', 'padding' );
				$shortcode_atts[] = '}';
			$shortcode_atts[] = '}';
		}
		
		if( (null !== sc_get_option_spacing_css_property( $atts, 'mxl', 'margin') ) || ( null !== sc_get_option_spacing_css_property( $atts, 'pxl', 'padding' )) ) {
			$shortcode_atts[] = '@media (min-width: 1200px) {';
				$shortcode_atts[] = '.' . $post->post_type . '-' . $post->post_name . ' .' . '-'.$atts['id'] . $wrapper . ' { ';
					if( null !== sc_get_option_spacing_css_property( $atts, 'mxl', 'margin' ) )		$shortcode_atts[]	= sc_get_option_spacing_css_property( $atts, 'mxl', 'margin' );
					if( null !== sc_get_option_spacing_css_property( $atts, 'pxl', 'padding' ) )		$shortcode_atts[]	= sc_get_option_spacing_css_property( $atts, 'pxl', 'padding' );
				$shortcode_atts[] = '}';
			$shortcode_atts[] = '}';
		}
		return $shortcode_atts;
	}
endif;


if( !function_exists('sc_get_option_enqueue_wow') ) :
	/**
	 * Enqueue WOW Animate Script
	 */
	function sc_get_option_enqueue_wow( $atts ) {
		// The inline script gets duplicated if not enqueued check
		if( ! wp_script_is( 'wow', 'enqueued' ) && !is_admin() && !empty( $atts['animate']['animation'] ) ) {
			wp_enqueue_script(
				'wow',
				get_template_directory_uri() . '/js/wow.min.js',
				array(),
				'1.1.3',
				true
			);
			wp_add_inline_script( 'wow', '
				new WOW().init();
			');
		}
	}
endif;


if( !function_exists('sc_get_option_animate_attr') ) :
	/**
	 * Add WOW Animate Attributes
	 */
	function sc_get_option_animate_attr( $atts ) {
		$animate_attr = array();
		if( isset($atts['animate']) && !empty($atts['animate']['animation']))	{
			if( !empty($atts['animate']['duration']) )		$animate_attr['data-wow-duration'] 	= $atts['animate']['duration'];
			if( !empty($atts['animate']['delay']) )				$animate_attr['data-wow-delay'] 		= $atts['animate']['delay'];
			if( !empty($atts['animate']['offset']) )			$animate_attr['data-wow-offset']	 	= $atts['animate']['offset'];
			if( !empty($atts['animate']['iteration']) )		$animate_attr['data-wow-iteration'] = $atts['animate']['iteration'];
		}
		return $animate_attr;
	}
endif;


if( !function_exists('sc_get_option_border_width') ) :
	/**
	 * Get Border Width Values
	 */
	function sc_get_option_border_width( $atts ) {
		$css = array();
		if( ! empty( $atts['border']['width'] ) ) {
			$css[] = 'border-width: rem(' . preg_replace( '/[^0-9]/', '', $atts['border']['width'] ) . 'px) !important;';
		}
		return $css;
	}
endif;


if( !function_exists('sc_options_get_shortcode_css') ) :
	/**
	 * Prints the CSS in the sass.php file
	 */
	function sc_options_get_shortcode_css( $atts, $shortcode_atts ) {	
		$shortcode_style = '';
		foreach ($shortcode_atts as $key => $value){
			$shortcode_style .= $value;
		}
				
		if( !get_option( $atts['shortcode'].'-style' ) ) {
			$shortcode_styles = array();
			$shortcode_styles[get_the_ID()][$atts['id']] = $shortcode_style;		
			add_option( $atts['shortcode'].'-style', $shortcode_styles, '', 'yes' );
		} else {
			$shortcode_styles = get_option( $atts['shortcode'] . '-style' );		
			$shortcode_styles[get_the_ID()][$atts['id']] = $shortcode_style;
			update_option( $atts['shortcode'].'-style', $shortcode_styles, 'yes' );
		}	
		
		if( !get_option( $atts['shortcode'].'-style-temp' ) ) {
			$shortcode_styles_temp = array();
			$shortcode_styles_temp[$atts['id']] = $shortcode_style;		
			add_option( $atts['shortcode'].'-style-temp', $shortcode_styles_temp, '', 'yes' );
		} else {	
			$shortcode_styles_temp = get_option( $atts['shortcode'] . '-style-temp');	
			$shortcode_styles_temp[$atts['id']] = $shortcode_style;
			update_option( $atts['shortcode'].'-style-temp', $shortcode_styles_temp, 'yes' );
		}	
		//$difference = array_diff($shortcode_styles[get_the_ID()],$shortcode_styles_temp);	
		$shortcode_styles[get_the_ID()] = array_intersect_key($shortcode_styles[get_the_ID()], $shortcode_styles_temp);
		update_option( $atts['shortcode'].'-style', $shortcode_styles, 'yes' );
		//delete_option($atts['shortcode'].'-style-temp');
	}
endif;