<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array();

$cfg['page_builder'] = array(
	'title'       => __( 'Feature List', 'fw' ),
	'description' => __( 'An icon-led list (checklist, cross/mixed, numbered or per-item icons) with optional sub-text and links — one or more columns.', 'fw' ),
	'tab'         => __( 'Components', 'fw' ),
	'popup_size'  => 'large',

	'title_template' => '
		{{ if ( o && o["items"] && o["items"].length ) {
			var design = o["design"] || "check";
			// Build the marker for one item, matching the chosen Marker Style: a per-item icon
			// (icon / badge designs — inline SVG markup or a font <i>), a check/cross (check design,
			// per the item state), a number, or a bullet.
			var mk = function ( it, i ) {
				if ( design === "numbered" ) { return \'\' + ( i + 1 ) + \'.\'; }
				if ( design === "bullet" )   { return \'&bull;\'; }
				if ( design === "check" )    { return ( it && it["state"] === "off" ) ? \'&#10005;\' : \'&#10003;\'; }
				var ic = ( it && it["icon"] ) || {};
				if ( ic["type"] === "svg" && ic["markup"] ) {
					return ( \'\' + ic["markup"] ).replace(/width="[^"]*"/i, \'width="16"\').replace(/height="[^"]*"/i, \'height="16"\');
				}
				if ( ic["type"] === "icon-font" && ic["icon-class"] ) { return \'<i class="\' + ic["icon-class"] + \'"></i>\'; }
				if ( ic["type"] === "emoji" && ic["char"] )           { return ic["char"]; }
				if ( ic["type"] === "custom-upload" && ic["url"] )    { return \'<img src="\' + ic["url"] + \'" style="width:16px;height:16px;vertical-align:middle;">\'; }
				return \'&bull;\';
			};
		}}
			<ul style="list-style:none;margin:.4rem 0 0;padding:0;">
				{{ for ( var i = 0; i < Math.min(o["items"].length,6); i++ ) { var it = o["items"][i]; }}
					<li style="display:flex;align-items:flex-start;gap:8px;margin:.3rem 0;">
						<span style="flex:0 0 auto;display:inline-flex;align-items:center;color:#4a90d9;line-height:1.5;">{{= mk( it, i ) }}</span>
						<span>{{- ( it && it["text"] ) || "" }}</span>
					</li>
				{{ } }}
			</ul>
		{{ } else { }}
			<em>No items added</em>
		{{ } }}
	',
);
