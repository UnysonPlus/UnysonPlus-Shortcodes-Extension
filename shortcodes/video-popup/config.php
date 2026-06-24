<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array();

$cfg['page_builder'] = array(
	'title'       => __( 'Video Popup', 'fw' ),
	'description' => __( 'A poster image with a play button that opens a YouTube / Vimeo / self-hosted video in a lightbox.', 'fw' ),
	'tab'         => __( 'Media Elements', 'fw' ),
	'popup_size'  => 'large',

	'title_template' => '
		{{ if ( o ) { }}
			{{ if ( o.poster && o.poster.url ) { }}
				<div style="position:relative;margin-top:.5rem;display:inline-block;">
					<img src="{{- o.poster.url }}" style="max-width:100%;max-height:120px;display:block;border-radius:4px;" />
					<span style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;"><span style="width:34px;height:34px;border-radius:50%;background:rgba(0,0,0,.55);display:flex;align-items:center;justify-content:center;color:#fff;">▶</span></span>
				</div>
			{{ } else { }}
				<em>Video Popup — add a poster + video URL</em>
			{{ } }}
		{{ } }}
	',
);
