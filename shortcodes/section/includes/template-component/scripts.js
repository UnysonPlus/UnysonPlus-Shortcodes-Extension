(function($, localized){
	var eventsNamespace = '.templates-section',
		loadingId = 'fw-builder-templates-type-section',
		genUid = function () {
			var s = '', i;
			if (window.crypto && window.crypto.getRandomValues) {
				var a = new Uint8Array(16);
				window.crypto.getRandomValues(a);
				for (i = 0; i < a.length; i++) { s += ('0' + a[i].toString(16)).slice(-2); }
				return s;
			}
			for (i = 0; i < 32; i++) { s += Math.floor(Math.random() * 16).toString(16); }
			return s;
		},
		modal,
		lazyInitModal = function () {
			lazyInitModal = function (){};

			var options = [
				{
					'template_name': {
						'type': 'text',
						'label': localized.l10n.template_name
					}
				}
			];

			// Only offer the Global Template switch when the snippets extension is
			// active (it's the store for global templates). Default OFF.
			if (localized.globalTemplatesEnabled) {
				options.push({
					'save_as_global': {
						'type': 'switch',
						'label': localized.l10n.save_as_global_label,
						'desc': localized.l10n.save_as_global_desc,
						// switch defaults: left-choice false (off) / right-choice true (on)
						'value': false
					}
				});
			}

			modal = new fw.OptionsModal({
				title: localized.l10n.save_template,
				options: options,
				values: ''
			});
		};

	fwEvents.on('fw:option-type:builder:templates:init', function(data){
		var loading = data.tooltipLoading,
			builder = data.builder,
			tooltipHideCallback = data.tooltipHideCallback,
			tooltipRefreshCallback = data.tooltipRefreshCallback;

		data.$elements.find('.fw-builder-templates-type-section')
			.off(eventsNamespace)
			.on('click'+ eventsNamespace, 'a[data-load-global-section]', function(){
				// Global Template: insert a [global_section] REFERENCE (synced) at
				// root — NOT a content copy. Editing the snippet updates every page.
				var snippetId = $(this).attr('data-load-global-section');

				builder.rootItems.add({
					type: 'global_section',
					atts: {
						snippet_id: String(snippetId),
						unique_id: genUid()
					}
				});

				tooltipHideCallback();
			})
			.on('click'+ eventsNamespace, 'a[data-delete-global]', function(){
				// Delete a Global Template (→ Trash, reversible). Confirm because it
				// affects every page that embeds it.
				var snippetId = $(this).attr('data-delete-global');

				fw.confirm(localized.l10n.global_delete_confirm, function () {

				loading.show();

				$.ajax({
					type: 'post',
					dataType: 'json',
					url: ajaxurl,
					data: {
						'action': 'fw_global_template_delete',
						'_nonce': localized.globalTemplatesDeleteNonce,
						'snippet_id': snippetId
					}
				})
					.done(function(json){
						loading.hide();
						if (!json.success) {
							fw.notify(localized.l10n.global_delete_failed, 'error', {id: 'fw-global-template'});
							return;
						}
						tooltipRefreshCallback();
					})
					.fail(function(xhr, status, error){
						loading.hide();
						console.error('Ajax global delete error', error);
						fw.notify(localized.l10n.global_delete_failed, 'error', {id: 'fw-global-template'});
					});
				});
			})
			.on('click'+ eventsNamespace, 'a[data-load-template]', function(){
				var templateId = $(this).attr('data-load-template');

				loading.show();

				$.ajax({
					type: 'post',
					dataType: 'json',
					url: ajaxurl,
					data: {
						'action': 'fw_builder_templates_section_load',
						'builder_type': builder.get('type'),
						'template_id': templateId
					}
				})
					.done(function(json){
						loading.hide();

						if (!json.success) {
							console.error('Failed to load builder template', json);
							return;
						}

						if (JSON.stringify(builder.rootItems) === json.data.json) {
							console.log('Loaded value is the same as current');
						} else {
							builder.rootItems.add(JSON.parse(json.data.json));
						}

						tooltipHideCallback();

						// scroll to the bottom of the builder
						setTimeout(function(){
							var $builderOption = builder.$input.closest('.fw-option-type-builder'),
								$scrollParent = $builderOption.scrollParent();

							if ($scrollParent.get(0) === document || $scrollParent.get(0) === document.body) {
								$scrollParent = $(window);
							}

							$scrollParent.scrollTop(
								$builderOption.offset().top
								+
								$builderOption.outerHeight()
								-
								$scrollParent.height()
							);
						}, 100);
					})
					.fail(function(xhr, status, error){
						loading.hide();

						console.error('Ajax error', error);
					});
			})
			.on('click'+ eventsNamespace, 'a[data-delete-template]', function(){
				var templateId = $(this).attr('data-delete-template');

				loading.show();

				$.ajax({
					type: 'post',
					dataType: 'json',
					url: ajaxurl,
					data: {
						'action': 'fw_builder_templates_section_delete',
						'builder_type': builder.get('type'),
						'template_id': templateId
					}
				})
					.done(function(json){
						loading.hide();

						if (!json.success) {
							console.error('Failed to delete builder template', json);
							return;
						}

						tooltipRefreshCallback();
					})
					.fail(function(xhr, status, error){
						loading.hide();

						console.error('Ajax error', error);
					});
			})
			.on('click'+ eventsNamespace, 'a[data-export-template]', function(){
				var templateId = $(this).attr('data-export-template');

				loading.show();

				$.ajax({
					type: 'post',
					dataType: 'json',
					url: ajaxurl,
					data: {
						'action': 'fw_builder_templates_section_export',
						'builder_type': builder.get('type'),
						'template_id': templateId
					}
				})
					.done(function(json){
						loading.hide();

						if (!json.success) {
							console.error('Failed to export builder template', json);
							return;
						}

						var payload = JSON.stringify(json.data.content, null, 2);
						var blob = new Blob([payload], { type: 'application/json' });
						var url = URL.createObjectURL(blob);
						var a = document.createElement('a');
						a.href = url;
						a.download = json.data.filename;
						document.body.appendChild(a);
						a.click();
						document.body.removeChild(a);
						setTimeout(function () { URL.revokeObjectURL(url); }, 1000);
					})
					.fail(function(xhr, status, error){
						loading.hide();

						console.error('Ajax export error', error);
					});
			})
			.on('click'+ eventsNamespace, 'a.import-template', function () {
				var $input = $(this).closest('.save-template-wrapper').find('input.template-import-file');
				$input.val('');
				$input.trigger('click');
			})
			.on('change'+ eventsNamespace, 'input.template-import-file', function () {
				var file = this.files && this.files[0];
				if (!file) {
					return;
				}

				var formData = new FormData();
				formData.append('action', 'fw_builder_templates_section_import');
				formData.append('builder_type', builder.get('type'));
				formData.append('template_file', file);

				loading.show();

				$.ajax({
					type: 'post',
					dataType: 'json',
					url: ajaxurl,
					data: formData,
					processData: false,
					contentType: false
				})
					.done(function (json) {
						loading.hide();

						if (!json.success) {
							var msg = (json.data && json.data.message)
								? json.data.message
								: (localized.l10n.import_failed || 'Failed to import template');
							fw.notify(msg, 'error');
							return;
						}

						tooltipRefreshCallback();
					})
					.fail(function (xhr, status, error) {
						loading.hide();
						console.error('Ajax import error', error);
						fw.notify(localized.l10n.import_failed || 'Failed to import template', 'error');
					});
			});
	});

	fwEvents.on('fw:page-builder:shortcode:section:controls', function(data){
		// A Global Section is a reference to a snippet, not real content — saving it
		// as a template would nest a reference inside a template. Skip the icon.
		if (data && data.model && data.model.get('type') === 'global_section') {
			return;
		}

		data.$controls.prepend(
			$('<i class="fw-shortcode-section-save dashicons dashicons-download"></i>')
				.attr('data-hover-tip', localized.l10n.save_template_tooltip)
				.on('click', function(e){
					e.stopPropagation();
					e.preventDefault();

					lazyInitModal();

					// reset previous values
					modal.set('values', {}, {silent: true});

					// remove previous listener
					modal.off('change:values');

					modal.on('change:values', function (modal, values) {
						fw.loading.show(loadingId);

						// Global Template: store the section as a reusable `snippet`
						// post instead of a local (copy) template. Reused by reference
						// via the Snippet element, so edits sync to every page.
						if (values.save_as_global) {
							$.ajax({
								type: 'post',
								dataType: 'json',
								url: ajaxurl,
								data: {
									'action': 'fw_builder_global_template_save',
									'_nonce': localized.globalTemplatesNonce,
									'kind': 'section',
									'template_name': values.template_name,
									'model_json': JSON.stringify(data.model),
									'builder_type': data.builder.get('type')
								}
							})
								.done(function (json) {
									fw.loading.hide(loadingId);

									if (!json.success) {
										console.error('Failed to save global template', json);
										fw.notify(localized.l10n.global_save_failed, 'error', {id: 'fw-global-template'});
										return;
									}

									// Make the new snippet immediately pickable in the
									// Snippet element without a page reload.
									if (window.fwSnippetTitles && json.data && json.data.id) {
										window.fwSnippetTitles[String(json.data.id)] = json.data.title;
									}

									fw.notify(localized.l10n.global_saved, 'success', {id: 'fw-global-template'});
								})
								.fail(function (xhr, status, error) {
									fw.loading.hide(loadingId);
									console.error('Ajax global save error', error);
									fw.notify(localized.l10n.global_save_failed, 'error', {id: 'fw-global-template'});
								});

							return;
						}

						$.ajax({
							type: 'post',
							dataType: 'json',
							url: ajaxurl,
							data: {
								'action': 'fw_builder_templates_section_save',
								'template_name': values.template_name,
								'section_json': JSON.stringify(data.model),
								'builder_type': data.builder.get('type')
							}
						})
							.done(function (json) {
								fw.loading.hide(loadingId);

								if (!json.success) {
									console.error('Failed to save builder template', json);
									return;
								}
							})
							.fail(function (xhr, status, error) {
								fw.loading.hide(loadingId);

								console.error('Ajax save error', error);
							});
					});

					modal.open();
				})
		);
	});
})(jQuery, _fw_option_type_builder_templates_section);