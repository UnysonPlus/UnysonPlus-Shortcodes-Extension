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
			var strip = function ( s ) { return ( "" + ( s || "" ) ).replace( /<[^>]+>/g, " " ).replace( /&nbsp;/g, " " ).replace( /\s+/g, " " ).trim(); };
			var clip  = function ( s, n ) { s = strip( s ); return s.length > n ? s.slice( 0, n ) + "…" : s; };

			var hasIconFont = o["front_icon"] && o["front_icon"]["type"] === "icon-font" && o["front_icon"]["icon-class"];
			var hasIconImg  = o["front_icon"] && o["front_icon"]["type"] === "custom-upload" && o["front_icon"]["url"];
			var hasBIconFont = o["back_icon"] && o["back_icon"]["type"] === "icon-font" && o["back_icon"]["icon-class"];
			var hasBIconImg  = o["back_icon"] && o["back_icon"]["type"] === "custom-upload" && o["back_icon"]["url"];

			var fTitle = clip( o["front_title"], 40 );
			var fText  = clip( o["front_text"], 70 );
			var bTitle = clip( o["back_title"], 40 );
			var bText  = clip( o["back_text"], 70 );
			var btn    = strip( o["button_label"] );
		}}
			<div style="margin-top:.5rem;display:flex;align-items:stretch;gap:8px;">

				<div style="flex:1;border:1px solid #e2e6ea;border-radius:6px;padding:8px 10px;min-width:0;">
					<div style="display:flex;align-items:center;gap:6px;">
						{{ if ( hasIconFont ) { }}<i class="{{= o["front_icon"]["icon-class"] }}" style="font-size:16px;"></i>{{ } else if ( hasIconImg ) { }}<img src="{{= o["front_icon"]["url"] }}" style="width:16px;height:16px;object-fit:contain;">{{ } }}
						{{ if ( fTitle ) { }}<strong style="font-size:12px;">{{- fTitle }}</strong>{{ } else { }}<em style="font-size:12px;color:#999;">Front</em>{{ } }}
					</div>
					{{ if ( fText ) { }}<div style="font-size:11px;color:#777;margin-top:3px;">{{- fText }}</div>{{ } }}
				</div>

				<div style="display:flex;align-items:center;color:#bbb;">⤿</div>

				<div style="flex:1;border:1px dashed #c9d2da;border-radius:6px;padding:8px 10px;min-width:0;">
					<div style="display:flex;align-items:center;gap:6px;">
						{{ if ( hasBIconFont ) { }}<i class="{{= o["back_icon"]["icon-class"] }}" style="font-size:16px;"></i>{{ } else if ( hasBIconImg ) { }}<img src="{{= o["back_icon"]["url"] }}" style="width:16px;height:16px;object-fit:contain;">{{ } }}
						{{ if ( bTitle ) { }}<strong style="font-size:12px;">{{- bTitle }}</strong>{{ } else { }}<em style="font-size:12px;color:#999;">Back</em>{{ } }}
					</div>
					{{ if ( bText ) { }}<div style="font-size:11px;color:#777;margin-top:3px;">{{- bText }}</div>{{ } }}
					{{ if ( btn ) { }}<span style="display:inline-block;margin-top:5px;font-size:11px;background:#eef1f4;border-radius:4px;padding:2px 8px;color:#333;">{{- btn }}</span>{{ } }}
				</div>
			</div>
		{{ } }}
	',
);
