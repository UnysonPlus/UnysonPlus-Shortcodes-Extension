<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array();

$cfg['page_builder'] = array(
	'title'       => __( 'Table', 'fw' ),
	'description' => __( 'Add a Table', 'fw' ),
	'tab'         => __( 'Content Elements', 'fw' ),
	'popup_size'  => 'large',
	'title_template' => '
		{{ var t = o["table"] || {}; }}
		{{ var rows = t["content"] || []; }}
		{{ var ho = t["header_options"] || {}; }}
		{{ var hr = ho["header_rows"] ? parseInt(ho["header_rows"], 10) : 0; }}
		{{ if ( rows.length && rows[0] && rows[0].length ) { }}
			<table class="fw-table-preview" style="border-collapse:collapse;width:100%;table-layout:fixed">
				{{ for ( var r = 0; r < rows.length; r++ ) { }}
					{{ var cells = rows[r] || []; }}
					<tr>
						{{ for ( var c = 0; c < cells.length; c++ ) { }}
							{{ var cell = cells[c] || {}; }}
							{{ if ( cell["merged"] ) { continue; } }}
							{{ var val = cell["textarea"] || cell["amount"] || cell["description"] || "&nbsp;"; }}
							{{ var cs = cell["colspan"] && cell["colspan"] > 1 ? " colspan=\\"" + cell["colspan"] + "\\"" : ""; }}
							{{ var rs = cell["rowspan"] && cell["rowspan"] > 1 ? " rowspan=\\"" + cell["rowspan"] + "\\"" : ""; }}
							{{ var st = "border:1px solid #d8dbde;padding:5px 9px;text-align:left;vertical-align:top;overflow:hidden;text-overflow:ellipsis" + (r < hr ? ";background:#f1f4f9;font-weight:600" : ""); }}
							{{ if ( r < hr ) { }}
								<th style="{{= st }}"{{= cs }}{{= rs }}>{{= val }}</th>
							{{ } else { }}
								<td style="{{= st }}"{{= cs }}{{= rs }}>{{= val }}</td>
							{{ } }}
						{{ } }}
					</tr>
				{{ } }}
			</table>
		{{ } else { }}
			<em>{{= "' . esc_js( __( 'Empty table', 'fw' ) ) . '" }}</em>
		{{ } }}
	',
);