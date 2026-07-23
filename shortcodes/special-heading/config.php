<?php if (!defined('FW')) die('Forbidden');

$cfg = array();

$cfg['page_builder'] = array(
	'title'         => __('Special Heading', 'fw'),
	'description'   => __('Add a Special Heading', 'fw'),
	'tab'           => __('Content Elements', 'fw'),
	'popup_size'    => 'medium', // can be large, medium or small
	// Canvas preview: overline, then the heading with the Title Icon rendered
	// before the title (svg / pack icons are force-sized to 1em so they show even
	// when a pack's markup carries no width — matching the frontend), then subtitle.
	'title_template' => <<<'TPL'
{{
var ic = ( o && o.icon ) ? o.icon : {};
var iconHtml = "";
if ( ic.type === "svg" && ic.markup ) {
	iconHtml = ( "" + ic.markup ).replace( /\s(width|height)="[^"]*"/gi, "" ).replace( /<svg/i, '<svg width="1em" height="1em" style="vertical-align:-.12em;margin-right:.4em"' );
} else if ( ic.type === "icon-font" && ic["icon-class"] ) {
	iconHtml = '<i class="' + ic["icon-class"] + '" style="margin-right:.4em"></i>';
} else if ( ic.type === "emoji" && ic.char ) {
	iconHtml = '<span style="margin-right:.4em">' + ic.char + '</span>';
} else if ( ic.type === "custom-upload" && ic.url ) {
	iconHtml = '<img src="' + ic.url + '" style="height:1em;width:auto;vertical-align:-.12em;margin-right:.4em">';
}
}}<div>{{= o.overline }}</div>
<{{= o.heading }}>{{= iconHtml }}{{= o.title }}</{{= o.heading }}>
<div>{{= o.subtitle }}</div>
TPL,
);