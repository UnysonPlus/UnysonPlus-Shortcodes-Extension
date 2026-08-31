<?php if ( ! defined( 'FW' ) ) { die( 'Forbidden' ); }

$cfg = array();

$cfg['page_builder'] = array(
	'title'       => __( 'Code Block', 'fw' ),
	'description' => __( 'Add a HTML/CSS/Javascript Block', 'fw' ),
	'tab'         => __( 'Content Elements', 'fw' ),
	'popup_size'    => 'large', // can be large, medium or small
	// Builder preview: NEVER inject the raw code into the editor DOM — a block holding a full page
	// (a Sandbox mirror's <style>html,body{…}</style> + scripts) would restyle/hijack the whole builder.
	// Show a safe, ESCAPED, truncated summary instead (a label for Sandbox mode).
	'title_template' => '<div class="sc-code-block-title">{{ var _m = o.render_mode && o.render_mode.mode ? o.render_mode.mode : o.render_mode; if ( _m === "sandbox" ) { }}&#9974; Sandboxed page (isolated iframe){{ } else { }}{{- ( o.code || "" ).replace( /\s+/g, " " ).substring( 0, 160 ) }}{{ } }}</div>',
);
