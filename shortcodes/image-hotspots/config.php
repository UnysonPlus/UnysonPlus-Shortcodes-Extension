<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array();

$cfg['page_builder'] = array(
	'title'       => __( 'Image Hotspots', 'fw' ),
	'description' => __( 'An image with interactive pins; each pin reveals a tooltip card with a title, text and link.', 'fw' ),
	'tab'         => __( 'Media Elements', 'fw' ),
	'popup_size'  => 'large',

	'title_template' => '
		{{ if ( o && o.image && o.image.url ) { }}
			<div style="margin-top:.5rem;"><img src="{{- o.image.url }}" style="max-width:100%;max-height:120px;display:block;border-radius:4px;" /></div>
			<div style="margin-top:.3rem;color:#777;"><em>{{= ( o["hotspots"] ? o["hotspots"].length : 0 ) }} hotspot(s)</em></div>
		{{ } else { }}
			<em>Image Hotspots — add an image</em>
		{{ } }}
	',
);
