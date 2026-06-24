<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array();

$cfg['page_builder'] = array(
	'title'       => __( 'Tag List', 'fw' ),
	'description' => __( 'A list of short items rendered as pills, chips or an inline separated list. One item per line, with optional links (Label | URL).', 'fw' ),
	'tab'         => __( 'Content Elements', 'fw' ),
	'popup_size'  => 'small',

	// Canvas preview: the item labels (text before any "| url"), joined with bullets.
	'title_template' =>
		'{{ var raw = ( o && o["items"] ) ? String( o["items"] ) : ""; }}' .
		'{{ var lines = raw.split( /\r\n|\r|\n/ ).map( function ( s ) { return ( s.split( "|" )[0] || "" ).trim(); } ).filter( Boolean ); }}' .
		'{{ if ( lines.length ) { }}<span class="sc-tl-preview">{{- lines.slice( 0, 12 ).join( "   •   " ) }}</span>' .
		'{{ } else { }}<em>{{- "No items yet" }}</em>{{ } }}',
);
