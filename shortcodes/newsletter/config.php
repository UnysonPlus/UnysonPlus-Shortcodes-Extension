<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array();

$cfg['page_builder'] = array(
	'title'       => __( 'Newsletter', 'fw' ),
	'description' => __( 'An email signup form (AJAX) — wired to your site mail (Mailer extension) and to a hook for Mailchimp / list integrations.', 'fw' ),
	'tab'         => __( 'Interactive Elements', 'fw' ),
	'popup_size'  => 'large',

	// A mini render of the form itself — fields + button, laid out per the chosen
	// Design — so the canvas shows what the element IS rather than the word
	// "Newsletter". Same approach as the Button element's chip. Colours are the
	// builder's neutral blue on purpose: the theme's Button Preset CSS is not
	// loaded in the admin, so a faithful colour here would be a guess, and a
	// wrong guess reads as a bug.
	'title_template' => '
		{{
			var d      = { inline: 1, stacked: 1, boxed: 1 }[ o && o["design"] ] ? o["design"] : "inline";
			var r      = { "rounded-0": "0", "rounded": "6px", "pill": "999px" }[ o && o["rounded"] ] || "6px";
			var name   = o && o["show_name"] === "yes";
			var title  = o && o["title"] ? ( "" + o["title"] ).replace( /<[^>]+>/g, " " ).replace( /\s+/g, " " ).trim() : "";
			var label  = o && o["button_label"] ? ( "" + o["button_label"] ).trim() : "Subscribe";
			var emailP = o && o["email_placeholder"] ? ( "" + o["email_placeholder"] ).trim() : "Your email address";
			var nameP  = o && o["name_placeholder"] ? ( "" + o["name_placeholder"] ).trim() : "Your name";
			var col    = d !== "inline";
			var field  = "flex:1 1 auto; min-width:0; padding:5px 10px; border:1px solid #c3c4c7; border-radius:" + r + "; background:#fff; color:#8c8f94; font-size:12px; line-height:1.5; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;";
			var btn    = "flex:0 0 auto; padding:5px 14px; border-radius:" + r + "; background:#0d6efd; color:#fff; font-size:12px; font-weight:600; line-height:1.5; white-space:nowrap;" + ( col ? " text-align:center;" : "" );
		}}
		<span style="display:block; margin-top:.5rem; max-width:{{= col ? "320px" : "100%" }};{{= d === "boxed" ? " padding:10px; border:1px solid #dcdcde; border-radius:8px; background:#f6f7f7;" : "" }}">
			{{ if ( title ) { }}<strong style="display:block; margin-bottom:6px; font-size:13px; line-height:1.3;">{{- title }}</strong>{{ } }}
			<span style="display:flex; gap:6px; align-items:stretch; flex-direction:{{= col ? "column" : "row" }};">
				{{ if ( name ) { }}<span style="{{= field }}">{{- nameP }}</span>{{ } }}
				<span style="{{= field }}">{{- emailP }}</span>
				<span style="{{= btn }}">{{- label }}</span>
			</span>
		</span>
	',
);
