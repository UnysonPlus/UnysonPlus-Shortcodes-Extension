<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array();

$cfg['page_builder'] = array(
	'title'       => __( 'Avatar', 'fw' ),
	'description' => __( 'A user avatar — single, or an overlapping group with a "+N" counter. Image or initials fallback, status dot, shapes and sizes.', 'fw' ),
	'tab'         => __( 'Components', 'fw' ),
	'popup_size'  => 'large',

	'title_template' => '
		{{ var m = ( o && o["mode_settings"] && o["mode_settings"]["mode"] ) ? o["mode_settings"]["mode"] : "single"; }}
		{{ if ( m === "group" ) { }}
			{{ var ppl = ( o["mode_settings"]["group"] && o["mode_settings"]["group"]["people"] ) ? o["mode_settings"]["group"]["people"] : []; }}
			<div style="margin-top:.4rem;color:#555;"><span style="font-size:15px;">&#128101;</span> {{= ppl.length }} {{= ppl.length === 1 ? "person" : "people" }} (group)</div>
		{{ } else { }}
			{{ var nm = ( o["mode_settings"]["single"] && o["mode_settings"]["single"]["name"] ) ? o["mode_settings"]["single"]["name"] : ""; }}
			<div style="margin-top:.4rem;color:#555;"><span style="font-size:15px;">&#128100;</span> {{- nm ? nm : "Avatar" }}</div>
		{{ } }}
	',
);
