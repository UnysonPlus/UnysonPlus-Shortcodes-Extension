<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array();

$cfg['page_builder'] = array(
	'title'       => __( 'Pricing Table', 'fw' ),
	'description' => __( 'Comparable pricing plans with price, feature list, a "featured" highlight and a call-to-action — in several card designs.', 'fw' ),
	'tab'         => __( 'Components', 'fw' ),
	'popup_size'  => 'large',

	'title_template' => '
		{{ if ( o && o["plans"] && o["plans"].length ) { }}
			<div style="display:flex;gap:6px;flex-wrap:wrap;margin-top:.4rem;">
				{{ for ( var i = 0; i < o["plans"].length; i++ ) {
					var p = o["plans"][i];
				}}
					<div style="border:1px solid #e2e6ea;border-radius:6px;padding:6px 10px;min-width:70px;text-align:center;{{ if (p.featured===\"yes\") { }}border-color:#4a90d9;{{ } }}">
						<div style="font-weight:600;font-size:12px;">{{- p.plan_title || \"Plan\" }}</div>
						<div style="font-size:13px;color:#333;">{{- (p.currency||\"\") }}{{- (p.price||\"\") }}<span style="font-size:10px;color:#999;">{{- (p.period||\"\") }}</span></div>
					</div>
				{{ } }}
			</div>
		{{ } else { }}
			<em>No plans added</em>
		{{ } }}
	',
);
