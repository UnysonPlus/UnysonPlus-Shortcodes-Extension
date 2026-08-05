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
		{{ if ( o && o["plans"] && o["plans"].length ) {
			var billing = o["billing_toggle"] === "yes";
		}}
			{{ if ( billing ) { }}
				<div style="margin-top:.4rem;font-size:11px;color:#2271b1;font-weight:600;">{{- (o["billing_monthly_label"] || "Bill Monthly") }} / {{- (o["billing_yearly_label"] || "Bill Yearly") }}{{ if ( o["billing_note"] ) { }}<span style="color:#135e29;"> &middot; {{- o["billing_note"] }}</span>{{ } }}</div>
			{{ } }}
			<div style="display:flex;gap:6px;flex-wrap:wrap;margin-top:.4rem;">
				{{ for ( var i = 0; i < o["plans"].length; i++ ) {
					var p = o["plans"][i] || {};
					var cur = p.currency || "";
					// Price / Period / Original Price are multi-inline { monthly, yearly } (back-compat: a
					// pre-merge plan stored a plain string = the monthly value).
					var mi = function ( f, w ) { return ( p[f] && typeof p[f] === "object" ) ? ( p[f][w] || "" ) : ( w === "monthly" ? ( p[f] || "" ) : "" ); };
					var pm = mi( "price", "monthly" ), py = mi( "price", "yearly" );
					var perm = mi( "period", "monthly" ), pery = mi( "period", "yearly" );
					var om = mi( "original_price", "monthly" );
					var hasYear = billing && py && ("" + py).length > 0;
				}}
					<div style="border:1px solid #e2e6ea;border-radius:6px;padding:6px 10px;min-width:78px;text-align:center;{{ if ( p.featured === "yes" ) { }}border-color:#4a90d9;{{ } }}">
						<div style="font-weight:600;font-size:12px;">{{- p.plan_title || "Plan" }}</div>
						{{ if ( om ) { }}<div style="font-size:10px;color:#999;text-decoration:line-through;">{{- om }}</div>{{ } }}
						<div style="font-size:13px;color:#333;">{{- cur }}{{- (pm || "0") }}<span style="font-size:10px;color:#999;">{{- perm }}</span></div>
						{{ if ( hasYear ) { }}<div style="font-size:11px;color:#135e29;margin-top:2px;">{{- cur }}{{- py }}<span style="font-size:9px;color:#8a9aa0;">{{- (pery || "/yr") }}</span></div>{{ } }}
					</div>
				{{ } }}
			</div>
		{{ } else { }}
			<em>No plans added</em>
		{{ } }}
	',
);
