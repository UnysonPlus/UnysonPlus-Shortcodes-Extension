(function (fwe) {
	'use strict';

	// Register the section-like editor view/model for this type so the Bleed
	// Section behaves like a section in the canvas (can hold rows/columns, sorts,
	// etc.). The split image/content layout is a frontend-only concern — in the
	// editor the inner items render normally.
	fwe.on('fw-builder:' + 'page-builder' + ':register-items', function (builder) {
		var ItemClass = window.createSectionLikeItem(builder, {
			type: 'bleed_section',
			dataGlobalName: 'page_builder_item_type_bleed_section_data'
		});

		builder.registerItemClass(ItemClass);
	});

})(fwEvents);
