(function (fwe) {
	fwe.on('fw-builder:' + 'page-builder' + ':register-items', function (builder) {
		var PageBuilderSectionItem,
			PageBuilderSectionItemView,
			triggerEvent = function(itemModel, event, eventData) {
				event = 'fw:builder-type:{builder-type}:item-type:{item-type}:'
					.replace('{builder-type}', builder.get('type'))
					.replace('{item-type}', itemModel.get('type'))
					+ event;

				var data = {
					modal: itemModel.view ? itemModel.view.modal : null,
					item: itemModel,
					itemView: itemModel.view,
					shortcode: itemModel.get('shortcode'),
					builder: builder
				};

				fwEvents.trigger(event, eventData
					? _.extend(eventData, data)
					: data
				);
			},
			getEventName = function(itemModel, event) {
				return 'fw:builder-type:{builder-type}:item-type:{item-type}:'
					.replace('{builder-type}', builder.get('type'))
					.replace('{item-type}', itemModel.get('type'))
					+ event;
			};

		PageBuilderSectionItemView = builder.classes.ItemView.extend({
			initialize: function (options) {
				this.defaultInitialize();

				this.initOptions = options;
				this.initOptions.templateData = this.initOptions.templateData || {};
			},
			template: _.template(
				'<div class="pb-item-type-column pb-item custom-section">' +
				/**/'<div class="panel fw-row">' +
				/**//**/'<div class="panel-left fw-col-xs-6">' +
				/**//**//**//**/'<div class="column-title"><%= title %></div>' +
				/**//**/'</div>' +
				/**//**/'<div class="panel-right fw-col-xs-6">' +
				/**//**//**/'<div class="controls">' +

				/**//**//**//**/'<% if (hasOptions) { %>' +
				/**//**//**//**/'<i class="dashicons dashicons-admin-generic edit-options" data-hover-tip="<%- edit %>"></i>' +
				/**//**//**//**/'<%  } %>' +

				/**//**//**//**/'<i class="dashicons dashicons-admin-page custom-section-clone" data-hover-tip="<%- duplicate %>"></i>' +
				/**//**//**//**/'<i class="dashicons dashicons-no custom-section-delete" data-hover-tip="<%- remove %>"></i>' +
				/**//**//**//**/'<i class="dashicons dashicons-arrow-down custom-section-collapse" data-hover-tip="<%- collapse %>"></i>' +
				/**//**//**/'</div>' +
				/**//**/'</div>' +
				/**/'</div>' +
				/**/'<div class="builder-items"></div>' +
				'</div>'
			),
			render: function () {
				{
					var title = this.initOptions.templateData.title,
						titleTemplate = itemData().title_template;

					if (titleTemplate && this.model.get('atts')) {
						try {
							title = _.template(
								jQuery.trim(titleTemplate),
								undefined,
								{
									evaluate: /\{\{([\s\S]+?)\}\}/g,
									interpolate: /\{\{=([\s\S]+?)\}\}/g,
									escape: /\{\{-([\s\S]+?)\}\}/g
								}
							)({
								o: this.model.get('atts'),
								title: title
							});
						} catch (e) {
							console.error('$cfg["page_builder"]["title_template"]', e.message);

							title = _.template('<%= title %>')({title: title});
						}
					} else {
						title = _.template('<%= title %>')({title: title});
					}
				}

				this.defaultRender(
					jQuery.extend({}, this.initOptions.templateData, {title: title})
				);

				this.$el[this.model.get('fw-collapse') ? 'addClass' : 'removeClass']('pb-item-section-collapsed');

				// Reflect "Columns Horizontal Alignment" (atts.column_halign) on the
				// canvas so the builder preview matches the frontend, which routes it
				// through .section--cols-{center,right} on the <section>. The matching
				// rule in this item's styles.css justifies the section's own flex
				// column row. Default (left) needs no class. render() re-runs on every
				// atts change (ItemView listens to model 'change'), so this stays live.
				{
					var atts    = this.model.get('atts') || {},
						halign  = atts.column_halign ? String(atts.column_halign) : '',
						reverse = atts.reverse_columns ? String(atts.reverse_columns) : '';

					this.$el.removeClass('section--cols-center section--cols-right section--cols-between section--cols-around section--cols-evenly section--rev section--rev-tablet section--rev-mobile');

					if (['center', 'right', 'between', 'around', 'evenly'].indexOf(halign) !== -1) {
						this.$el.addClass('section--cols-' + halign);
					}

					// Reflect Column Order on the canvas. device-preview.css keys off the
					// builder root's .fw-device-{lg|md|sm} class to apply the actual reverse per
					// preview mode (the CSS @media rules can't fire — the canvas isn't resized to
					// the real viewport), matching the frontend's per-breakpoint behaviour.
					if (['all', 'tablet', 'mobile'].indexOf(reverse) !== -1) {
						this.$el.addClass(reverse === 'all' ? 'section--rev' : 'section--rev-' + reverse);
					}
				}

				// Reflect Min Height (atts.min_height) live on the canvas — WYSIWYG, so a
				// Full-Viewport (100vh) or Custom section shows its real height while editing
				// (mirrors the frontend view's min-height resolution). Removed while collapsed
				// so a collapsed section doesn't stay tall.
				{
					var mhAtts = this.model.get('atts') || {},
						mh     = mhAtts.min_height || '',
						mhCss  = '';

					if (mh && typeof mh === 'object') {
						var mhPreset = mh.preset ? String(mh.preset) : '';
						if (mhPreset === 'custom') {
							var uv   = (mh.custom && mh.custom.custom_height) ? mh.custom.custom_height : {},
								num  = (uv.value != null) ? String(uv.value).trim() : '',
								unit = uv.unit ? String(uv.unit) : 'px';
							if (/^-?\d*\.?\d+$/.test(num)) { mhCss = num + unit; }
						} else if (mhPreset && mhPreset !== 'auto') {
							mhCss = mhPreset; // e.g. "100vh"
						}
					} else if (typeof mh === 'string' && mh !== 'auto' && mh !== '') {
						mhCss = mh.trim(); // legacy plain-string value
					}

					// Only allow safe CSS length values through to the inline style.
					if (mhCss && !/^-?\d*\.?\d+(px|%|vh|vw|rem|em)$/.test(mhCss)) { mhCss = ''; }

					// Apply to the section BODY box (the bordered .pb-item-type-column), NOT the
					// outer .builder-item — putting it on the outer wrapper only adds empty space
					// (a "margin") below the content-height body. The body growing pulls the
					// wrapper with it. Clear any legacy value an older build left on the wrapper.
					this.$el.css('min-height', '');
					var $body = this.$el.children('.pb-item-type-column');
					if (!$body.length) { $body = this.$el; }
					$body.css('min-height', (this.model.get('fw-collapse') || !mhCss) ? '' : mhCss);

					// Reflect Columns Vertical Alignment on the canvas too — only meaningful with a
					// Min Height. Mirrors the frontend: Stretch = columns fill the tall section;
					// Top / Center / Bottom position the columns block. The body becomes a flex
					// column and the columns row fills + aligns via this item's styles.css.
					var va = mhAtts.column_valign ? String(mhAtts.column_valign) : 'top'; // empty = top (frontend fallback)
					if (['stretch', 'top', 'center', 'bottom'].indexOf(va) === -1) { va = 'top'; }
					$body.removeClass('section--canvas-mh section--canvas-va-stretch section--canvas-va-top section--canvas-va-center section--canvas-va-bottom');
					if (mhCss && !this.model.get('fw-collapse')) {
						$body.addClass('section--canvas-mh section--canvas-va-' + va);
					}
				}

				/**
				 * Other scripts can append/prepend other control $elements
				 */
				fwEvents.trigger('fw:page-builder:shortcode:section:controls', {
					$controls: this.$('.controls:first'),
					model: this.model,
					builder: builder
				});
			},
			events: {
				'click': 'editOptions',
				'click .edit-options': 'editOptions',
				'click .custom-section-clone': 'cloneItem',
				'click .custom-section-delete': 'removeItem',
				'click .custom-section-collapse': 'collapseItem'
			},
			lazyInitModal: function () {
				this.lazyInitModal = function (){};

				if (_.isEmpty(this.initOptions.modalOptions)) {
					return;
				}

				// Migrate legacy saved atts to current value-shapes BEFORE the modal
				// renders them. get_value_from_attributes (PHP) does NOT run on normal
				// builder load — the modal opens with the raw saved atts — so any
				// option whose value shape changed (e.g. min_height: select-string →
				// multi-picker-array) must be migrated here, or the multi-picker's PHP
				// render throws and the modal shows a blank "error:". Set it back on
				// the model so a save persists the upgraded shape.
				this.model.set('atts', migrateSectionAtts(this.model.get('atts')));

				var eventData = {modalSettings: {buttons: []}};

				/**
				 * eventData.modalSettings can be changed by reference
				 */
				triggerEvent(this.model, 'options-modal:settings', eventData);

				this.modal = new fw.OptionsModal({
					title: 'Section',
					options: this.initOptions.modalOptions,
					values: this.model.get('atts'),
					size: this.initOptions.modalSize,
					headerElements: itemData().header_elements
				}, eventData.modalSettings);

				this.listenTo(this.modal, 'change:values', function (modal, values) {
					this.model.set('atts', values);
				});

				this.listenTo(this.modal, {
					'open': function(){
						fwEvents.trigger(getEventName(this.model, 'options-modal:open'), {
							modal: this.modal,
							item: this.model,
							itemView: this
						});
					},
					'render': function(){
						fwEvents.trigger(getEventName(this.model, 'options-modal:render'), {
							modal: this.modal,
							item: this.model,
							itemView: this
						});
					},
					'close': function(){
						fwEvents.trigger(getEventName(this.model, 'options-modal:close'), {
							modal: this.modal,
							item: this.model,
							itemView: this
						});
					},
					'change:values': function(){
						fwEvents.trigger(getEventName(this.model, 'options-modal:change:values'), {
							modal: this.modal,
							item: this.model,
							itemView: this
						});
					}
				});
			},
			editOptions: function (e) {
				e.stopPropagation();

				this.lazyInitModal();

				if (!this.modal) {
					return;
				}

				var flow = {cancelModalOpening: false};

				/**
				 * Trigger before-open model just like we do this for
				 * item-simple shortcodes.
				 *
				 * http://bit.ly/1KY6tpP
				 */
				fwEvents.trigger('fw:page-builder:shortcode:section:modal:before-open', {
					modal: this.modal,
					model: this.model,
					builder: builder,
					flow: flow
				});

				if (! flow.cancelModalOpening) {
					this.modal.open();
				}
			},
			cloneItem: function (e) {
				e.stopPropagation();

				var index = this.model.collection.indexOf(this.model),
					attributes = this.model.toJSON(),
					_items = attributes['_items'],
					clonedColumn;

				delete attributes['_items'];

				clonedColumn = new PageBuilderSectionItem(attributes);

				triggerEvent(clonedColumn, 'clone-item:before');

				this.model.collection.add(clonedColumn, {at: index + 1});
				clonedColumn.get('_items').reset(_items);
			},
			removeItem: function (e) {
				e.stopPropagation();

				this.remove();
				this.model.collection.remove(this.model);
			},
			collapseItem: function (e) {
				e.stopPropagation();

				this.model.set('fw-collapse', !this.model.get('fw-collapse'));
			}
		});

		PageBuilderSectionItem = builder.classes.Item.extend({
			defaults: {
				type: 'section'
			},
			initialize: function() {
				this.view = new PageBuilderSectionItemView({
					id: 'page-builder-item-' + this.cid,
					model: this,
					modalOptions: itemData().options,
					modalSize: itemData().popup_size,
					templateData: {
						hasOptions: !!itemData().options,
						edit : itemData().l10n.edit,
						duplicate : itemData().l10n.duplicate,
						remove : itemData().l10n.remove,
						collapse : itemData().l10n.collapse,
						title: itemData().title
					}
				});

				this.defaultInitialize();
			},
			allowIncomingType: function (type) {
				// Reject ALL section-like types (not just the literal 'section'),
				// so custom variants like hero_section/parallax_section can't be
				// nested inside this section either. Falls back to the original
				// strict equality if the registry hasn't loaded yet.
				if (window.fwSectionLikeTypes && typeof window.fwSectionLikeTypes.isSectionLike === 'function') {
					return !window.fwSectionLikeTypes.isSectionLike(type);
				}
				return 'section' !== type;
			},
			allowDestinationType: function (type) {
				return 'column' !== type;
			}
		});

		builder.registerItemClass(PageBuilderSectionItem);
	});

	function itemData () {
		// return fw.unysonShortcodesData()['section'];
		return page_builder_item_type_section_data;
	}

	/**
	 * Migrate legacy saved section atts to the current value-shapes. Mirrors the
	 * PHP migrators in section/includes/migration.php. Must run in the editor
	 * because get_value_from_attributes (PHP) is NOT invoked on normal builder
	 * load — the options modal renders the raw saved atts.
	 */
	function migrateSectionAtts (atts) {
		if (!_.isObject(atts)) {
			return atts;
		}

		// min_height: legacy scalar ('', '40vh', '600px', …) → multi-picker shape
		// { preset, custom:{ custom_height:{value,unit} } }. Arrays pass through.
		if (_.has(atts, 'min_height') && !_.isObject(atts.min_height)) {
			atts = _.clone(atts);
			atts.min_height = migrateMinHeight(atts.min_height);
		}

		return atts;
	}

	function migrateMinHeight (v) {
		v = (v === null || typeof v === 'undefined') ? '' : String(v);
		v = v.replace(/^\s+|\s+$/g, '');

		if (v === '' || v === 'auto') {
			return {preset: 'auto'};
		}
		if (['40vh', '60vh', '80vh', '100vh'].indexOf(v) !== -1) {
			return {preset: v};
		}

		var m    = v.match(/^([0-9.]+)\s*([a-z%]+)$/i),
			num  = m ? m[1] : v.replace(/[^0-9.]/g, ''),
			unit = m ? m[2].toLowerCase() : 'px';

		return {preset: 'custom', custom: {custom_height: {value: num, unit: unit}}};
	}
})(fwEvents);

