(function (fwe) {
	fwe.on('fw-builder:' + 'page-builder' + ':register-items', function (builder) {
		var ItemClass = window.createSectionLikeItem(builder, {
			type: 'hero_section',
			dataGlobalName: 'page_builder_item_type_hero_section_data'
		});

		builder.registerItemClass(ItemClass);
	});
})(fwEvents);
