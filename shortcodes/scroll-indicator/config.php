<?php if ( ! defined( 'FW' ) ) { die( 'Forbidden' ); }

$cfg = array();

$cfg['page_builder'] = array(
	'title'       => __( 'Scroll Indicator', 'fw' ),
	'description' => __( 'A hero "scroll to descend" cue — a label + animated chevron that smooth-scrolls to the next section.', 'fw' ),
	'tab'         => __( 'Content Elements', 'fw' ),
	'popup_size'  => 'small',

	// Canvas preview: the label above/below the chevron, matching the layout.
	'title_template' => '
		{{
			var txt    = ( o && o["text"] ) ? ( "" + o["text"] ).replace(/<[^>]+>/g," ").replace(/\\s+/g," ").trim() : "";
			var layout = ( o && o["layout"] ) || "stacked";
			var ic     = ( o && o["icon"] ) || {};
			var glyph  = "&#8964;"; // default chevron-down look
			if ( ic["type"] === "svg" && ic["markup"] ) {
				glyph = ( "" + ic["markup"] ).replace(/width="[^"]*"/i, \'width="18"\').replace(/height="[^"]*"/i, \'height="18"\');
			} else if ( ic["type"] === "icon-font" && ic["icon-class"] ) {
				glyph = \'<i class="\' + ic["icon-class"] + \'"></i>\';
			}
			var label = ( txt && layout !== "icon-only" ) ? \'<span style="font-size:10px;letter-spacing:.12em;text-transform:uppercase;opacity:.85;">\' + txt + \'</span>\' : "";
			var icon  = \'<span style="display:inline-flex;color:#4a90d9;">\' + glyph + \'</span>\';
			var order = ( layout === "stacked-reverse" ) ? ( icon + label ) : ( label + icon );
		}}
		<div style="margin-top:.4rem;display:inline-flex;flex-direction:column;align-items:center;gap:4px;">{{= order }}</div>
	',
);
