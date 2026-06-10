<?php if ( ! defined( 'FW' ) ) { die( 'Forbidden' ); }

/**
 * Page-builder item for the Bleed Section.
 *
 * Extends Page_Builder_Section_Like_Item, which gives it section behaviour for
 * free: registry registration, no auto-wrap into [section], inner row/column
 * correction, and auto-enqueue of this folder's static/{css,js} in the editor.
 */
class Page_Builder_Bleed_Section_Item extends Page_Builder_Section_Like_Item {

	public function get_type() {
		return 'bleed_section';
	}
}

FW_Option_Type_Builder::register_item_type( 'Page_Builder_Bleed_Section_Item' );
