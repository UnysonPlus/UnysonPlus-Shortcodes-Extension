<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array();

$cfg['page_builder'] = array(
	'title'       => __( 'Button', 'fw' ),
	'description' => __( 'Add a Button', 'fw' ),
	'tab'         => __( 'Components', 'fw' ),
	'popup_size' => 'large',
	// Canvas preview: render the label as a filled button-chip (not plain text) so a
	// Button reads as a button in the builder. Purely a builder affordance — it does
	// NOT mirror the frontend skin; the chip radius just echoes the chosen Shape.
	'title_template' => '
		{{
			var label = o && o["label"] ? ( "" + o["label"] ).replace( /<[^>]+>/g, " " ).replace( /\s+/g, " " ).trim() : "";
			var shape = o && o["shape"] ? o["shape"] : "default";
			var radius = shape === "pill" ? "999px" : ( shape === "square" ? "0" : "6px" );
			// Draw the icon in the chip. svg markup is resized to 14px; a font icon /
			// emoji / uploaded image render inline. A library icon with only an svg-id
			// (set programmatically) has no markup here — the importer resolves it.
			var ic = ( o && o["icon"] ) ? o["icon"] : {};
			var iconHtml = "";
			if ( ic.type === "svg" && ic.markup ) {
				iconHtml = ( "" + ic.markup ).replace( /width=\"[^\"]*\"/i, "width=\"14\"" ).replace( /height=\"[^\"]*\"/i, "height=\"14\"" );
			} else if ( ic.type === "icon-font" && ic["icon-class"] ) {
				iconHtml = "<i class=\"" + ic["icon-class"] + "\" style=\"font-size:13px;\"></i>";
			} else if ( ic.type === "emoji" && ic.char ) {
				iconHtml = "<span style=\"font-size:13px;\">" + ic.char + "</span>";
			} else if ( ic.type === "custom-upload" && ic.url ) {
				iconHtml = "<img src=\"" + ic.url + "\" style=\"height:14px;width:auto;\">";
			}
			var pos = ( o && o["icon_position"] ) ? o["icon_position"] : "after";
		}}
		<span style="display:inline-flex; align-items:center; gap:6px; margin-top:.5rem; max-width:100%; padding:5px 14px; background:#0d6efd; color:#fff; border-radius:{{= radius }}; font-size:12px; font-weight:600; line-height:1.5; white-space:nowrap; overflow:hidden; vertical-align:middle;">
			{{ if ( iconHtml && pos === "before" ) { }}{{= iconHtml }}{{ } }}
			<span style="overflow:hidden; text-overflow:ellipsis;">{{- label ? label : "Button" }}</span>
			{{ if ( iconHtml && pos === "after" ) { }}{{= iconHtml }}{{ } }}
		</span>
	',
);
