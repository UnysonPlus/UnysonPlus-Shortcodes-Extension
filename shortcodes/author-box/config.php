<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array();

$cfg['page_builder'] = array(
	'title'       => __( 'Author Box', 'fw' ),
	'description' => __( 'An author / profile box — avatar, name, bio, social links and post count — for the current post author, a chosen user, or fully custom content.', 'fw' ),
	'tab'         => __( 'Components', 'fw' ),
	'popup_size'  => 'large',

	'title_template' => <<<'TPL'
		{{ if ( o ) {
			var src   = o["source"] || "current";
			var srcLabel = ( src === "custom" ) ? "Custom" : ( src === "user" ? "Specific user" : "Current post author" );
			var name  = ( o["name"] || "" ).toString().trim();
			var role  = ( o["role"] || "" ).toString().trim();
			var bio   = ( o["bio"]  || "" ).toString().replace( /<[^>]*>/g, " " ).replace( /&nbsp;/g, " " ).replace( /\s+/g, " " ).trim();
			if ( bio.length > 110 ) { bio = bio.slice( 0, 110 ) + "…"; }
			var av    = ( o["avatar"] && o["avatar"].url ) ? o["avatar"].url : "";
			var shape = o["avatar_shape"] || "circle";
			var radius = ( shape === "circle" ) ? "50%" : ( shape === "rounded" ? "8px" : "0" );
			var heading = name || ( src === "custom" ? "Author Box" : srcLabel );
		}}
		<div style="display:flex; align-items:center; gap:10px;">
			{{ if ( av ) { }}
				<img src="{{- av }}" style="width:44px; height:44px; object-fit:cover; border-radius:{{- radius }}; flex:0 0 auto;" />
			{{ } else { }}
				<span style="width:44px; height:44px; border-radius:{{- radius }}; flex:0 0 auto; background:rgba(0,0,0,.08); display:flex; align-items:center; justify-content:center; font-size:20px; opacity:.5;">&#128100;</span>
			{{ } }}
			<div style="min-width:0;">
				<div style="display:flex; align-items:center; gap:6px; flex-wrap:wrap;">
					<strong>{{- heading }}</strong>
					<span style="display:inline-block; padding:0 7px; border:1px solid rgba(0,0,0,.18); border-radius:999px; font-size:.78em; opacity:.7;">{{- srcLabel }}</span>
				</div>
				{{ if ( role ) { }}<div style="margin-top:1px; opacity:.75; font-size:.9em;">{{- role }}</div>{{ } }}
				{{ if ( bio ) { }}<div style="margin-top:2px; opacity:.6; font-size:.86em; line-height:1.35;">{{- bio }}</div>{{ } }}
			</div>
		</div>
		{{ } }}
TPL
,
);
