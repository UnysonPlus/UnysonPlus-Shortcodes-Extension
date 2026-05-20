<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array();

$cfg['page_builder'] = array(
	'title'       => __( 'Call To Action', 'fw' ),
	'description' => __( 'Add a Call to Action', 'fw' ),
	'tab'         => __( 'Content Elements', 'fw' ),

	'title_template' => '
		{{ if ( o ) { }}
			<div style="margin-top:.5rem; display:flex; align-items:center; gap:12px;">

				<div style="flex:1; min-width:0;">
					{{ if ( o["title"] ) { }}
						<strong>{{= o["title"] }}</strong>
					{{ } }}

					{{ if ( o["message"] ) { }}
						<div style="opacity:.7; font-size:12px; margin-top:4px;">
							{{= o["message"] }}
						</div>
					{{ } }}
				</div>

				{{ if ( o["button_label"] ) { }}
					<span style="flex-shrink:0; padding:4px 12px; background:#0d6efd; color:#fff; border-radius:4px; font-size:12px; white-space:nowrap;">
						{{= o["button_label"] }}
					</span>
				{{ } }}

				{{ if ( !o["title"] && !o["message"] && !o["button_label"] ) { }}
					<em>No content set</em>
				{{ } }}
			</div>
		{{ } }}
	',
);