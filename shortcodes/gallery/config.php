<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array();

$cfg['page_builder'] = array(
	'title'          => __( 'Gallery', 'fw' ),
	'description'    => __( 'A flexible image gallery — grid, masonry, justified, metro, carousel, polaroid or showcase, with a built-in lightbox & captions', 'fw' ),
	'tab'            => __( 'Media Elements', 'fw' ),
	'popup_size'     => 'large', // can be large, medium or small
	'title_template' => '
		{{ if ( o["images"] && o["images"].length > 0 ) { }}
			<div style="display:flex;flex-wrap:wrap;gap:3px;align-items:center;">
				{{ for ( var i = 0; i < Math.min( o["images"].length, 9 ); i++ ) { }}
					{{ var gimg = o["images"][i]; var gurl = ( gimg && typeof gimg === "object" ) ? gimg.url : ""; }}
					{{ if ( gurl ) { }}
						<img src="{{- gurl }}" style="width:46px;height:46px;object-fit:cover;border-radius:4px;" />
					{{ } }}
				{{ } }}
				{{ if ( o["images"].length > 9 ) { }}
					<span style="font-size:12px;color:#777;padding:0 4px;">+{{= o["images"].length - 9 }}</span>
				{{ } }}
			</div>
		{{ } else { }}
			<em>No images added</em>
		{{ } }}
	',
);
