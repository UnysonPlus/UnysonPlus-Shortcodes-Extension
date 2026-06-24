<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array();

$cfg['page_builder'] = array(
	'title'       => __( 'Before / After', 'fw' ),
	'description' => __( 'An interactive before/after image comparison slider — drag, hover or click to reveal, in many handle styles, horizontal or vertical.', 'fw' ),
	'tab'         => __( 'Media Elements', 'fw' ),
	'popup_size'  => 'large',

	'title_template' => '
		{{ if ( o ) {
			var b = ( o["before_image"] && o["before_image"].url ) ? o["before_image"].url : "";
			var a = ( o["after_image"]  && o["after_image"].url )  ? o["after_image"].url  : "";
			var d = o["design"] || "classic";
		}}
			{{ if ( b || a ) { }}
				<div style="display:flex;gap:6px;align-items:flex-end;margin-top:.5rem;">
					{{ if ( b ) { }}<figure style="margin:0;text-align:center;"><img src="{{- b }}" style="width:90px;height:64px;object-fit:cover;border-radius:4px;display:block;" /><figcaption style="font-size:10px;color:#888;">Before</figcaption></figure>{{ } }}
					<span style="font-size:18px;color:#bbb;padding-bottom:14px;">⇄</span>
					{{ if ( a ) { }}<figure style="margin:0;text-align:center;"><img src="{{- a }}" style="width:90px;height:64px;object-fit:cover;border-radius:4px;display:block;" /><figcaption style="font-size:10px;color:#888;">After</figcaption></figure>{{ } }}
				</div>
			{{ } else { }}
				<em>No images added</em>
			{{ } }}
			<div style="margin-top:.4rem;"><em style="opacity:.7;">{{- d }}</em></div>
		{{ } }}
	',
);
