<?php if ( ! defined( 'ABSPATH' ) ) { die( 'Direct access forbidden.' ); }

//if(!function_exists('c_get_option')) :
/**
* Get options value
* if framework is missing , load defaults
* @return option value
*/
function c_get_option($id, $default = NULL){
    if (function_exists('fw_get_db_settings_option')) {
        return fw_get_db_settings_option($id);
    }else if(!empty( $default )){
        return $default;
    }
}
//endif;

// I'll be removing the c_get_option function
if(!function_exists('sc_get_option')) :
  /**
  * Get options value
  * if framework is missing , load defaults
  * @return option value
  */
  function sc_get_option($option_id, $default = NULL){
    if (function_exists('fw_get_db_settings_option')) {
        return fw_get_db_settings_option($option_id);
    }else{
        return $default;
    }
  }
endif;


if(!function_exists('sc_get_post_option')) :
  /**
   * Converts an attribute array to an HTML attribute string via fw_attr_to_html(), or returns the default.
   *
   * Adds 'unyson page-builder' body classes when the current post uses the page builder.
   *
  * Get post options value
  * if framework is missing , load defaults
  * @return option value
  */
  function sc_get_post_option($post_id, $id, $default = NULL){
    if (function_exists('fw_get_db_post_option')) {
        return fw_get_db_post_option($post_id, $id);
    }else{
        return $default;
    }
  }
endif;


if(!function_exists('sc_html_tag')) :
  /** Wraps fw_html_tag with guards, returning a default when the framework, tag, or content is missing. */
  function sc_html_tag($tag, array $attr, $content = NULL, $default = NULL){
			if(! function_exists('fw_html_tag')) return $default;
      if ( empty($tag) ) 			return $content;
			if ( empty($attr) ) 		$attr = array();
			if ( empty($content) ) 	$content = true;
			return fw_html_tag($tag, $attr, $content);
  }
endif;


if(!function_exists('sc_attr_to_html')) :
  /** Converts an attribute array to an HTML attribute string via fw_attr_to_html(), or returns the default. */
  function sc_attr_to_html(array $attr, $default = NULL){
      if (function_exists('fw_attr_to_html') && (!empty($attr))) {
          return fw_attr_to_html($attr);
      }else{
          return $default;
      }
  }
endif;


if( ! function_exists('sc_ext_page_builder_is_builder_post') ) :
  /** Adds 'unyson page-builder' body classes when the current post uses the page builder. */
  function sc_ext_page_builder_is_builder_post( $classes ) {
      global $post;
      if ( isset ( $post->ID ) && function_exists('fw_ext_page_builder_is_builder_post') && fw_ext_page_builder_is_builder_post($post->ID) ) {
          $classes[] = 'unyson page-builder';
      }
      return $classes;
  }
  add_filter( 'body_class', 'sc_ext_page_builder_is_builder_post' );
endif;