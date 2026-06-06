<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

class Page_Builder_Hero_Section_Item extends Page_Builder_Section_Like_Item {

	public function get_type() {
		return 'hero_section';
	}
}

FW_Option_Type_Builder::register_item_type( 'Page_Builder_Hero_Section_Item' );
