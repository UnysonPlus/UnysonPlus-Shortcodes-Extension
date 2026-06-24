<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array();

$cfg['page_builder'] = array(
	'title'       => __( 'Logo Grid', 'fw' ),
	'description' => __( 'A grid, boxed grid, carousel or marquee of client / partner logos, with an optional grayscale-to-color hover.', 'fw' ),
	'tab'         => __( 'Media Elements', 'fw' ),
	'popup_size'  => 'large',

	'title_template' => '
		{{ if ( o && o["logos"] && o["logos"].length ) { }}
			<div style="display:flex;flex-wrap:wrap;gap:6px;align-items:center;margin-top:.5rem;">
				{{ for ( var i = 0; i < Math.min(o["logos"].length,8); i++ ) {
					var l = o["logos"][i];
					var u = ( l.image && l.image.url ) ? l.image.url : "";
				}}
					{{ if ( u ) { }}<img src="{{- u }}" style="height:30px;max-width:80px;object-fit:contain;" />{{ } }}
				{{ } }}
			</div>
		{{ } else { }}
			<em>No logos added</em>
		{{ } }}
	',
);
