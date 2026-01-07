<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * @var $map_data_attr
 * @var $atts
 * @var $content
 * @var $tag
 */

// Always set these before building attributes
$atts['base_class']       = 'map';
$atts['unique_id_prefix'] = 'mp-';

$attr = sc_build_wrapper_attr( $atts );
$attr = array_merge( $attr, $map_data_attr );

?>
<div <?php echo fw_attr_to_html($attr); ?>>
	<div class="map-canvas"></div>
</div>