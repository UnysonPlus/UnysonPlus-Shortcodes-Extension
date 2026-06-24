<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array();

$cfg['page_builder'] = array(
	'title'       => __( 'Flip Box', 'fw' ),
	'description' => __( 'A two-sided card that flips on hover or click — icon/title on the front, text + button on the back. Four directions, several designs.', 'fw' ),
	'tab'         => __( 'Interactive Elements', 'fw' ),
	'popup_size'  => 'large',

	'title_template' => '
		{{ if ( o ) {
			var f = o["front_title"] || "Front";
			var b = o["back_title"] || "Back";
		}}
			<div style="margin-top:.4rem;display:flex;align-items:center;gap:8px;">
				<span style="border:1px solid #e2e6ea;border-radius:5px;padding:4px 10px;font-weight:600;">{{- f }}</span>
				<span style="color:#bbb;">⤿</span>
				<span style="border:1px dashed #c9d2da;border-radius:5px;padding:4px 10px;color:#555;">{{- b }}</span>
			</div>
		{{ } }}
	',
);
