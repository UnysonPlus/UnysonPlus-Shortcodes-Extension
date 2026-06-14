(function(fwe) {

	// Live column item views, re-previewed when the device toggle changes.
	// IMPORTANT: we bind ONE handler to the custom fwEvents bus here, instead of
	// per-view via Backbone `this.listenTo(fwEvents, …)`. fwEvents is NOT a
	// Backbone.Events object, so listenTo would make Backbone's stopListening()
	// call fwEvents.off() when a column is removed — that throws
	// (Object.keys(undefined)) and aborts the delete (deleted columns reappear,
	// qtips stick, controls die). A plain registry never touches fwEvents.off.
	// Dead views (detached from the DOM) are pruned lazily.
	var fwDeviceColumnViews = [];
	fwe.on('fw:builder:device-preview', function () {
		for (var i = fwDeviceColumnViews.length - 1; i >= 0; i--) {
			var v = fwDeviceColumnViews[i];
			if (!v || !v.model || !v.el || !document.body.contains(v.el)) {
				fwDeviceColumnViews.splice(i, 1);
				continue;
			}
			try { v.applyLayoutPreview(); } catch (e) {}
		}
	});

	fwe.on('fw-builder:' + 'page-builder' + ':register-items', function(builder) {
		var PageBuilderColumnItem,
			PageBuilderColumnItemView,
			PageBuilderColumnItemViewWidthChanger,
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

				fwEvents.trigger(event, eventData ? _.extend(eventData, data) : data);
			},
			getEventName = function(itemModel, event) {
				return 'fw:builder-type:{builder-type}:item-type:{item-type}:'
					.replace('{builder-type}', builder.get('type'))
					.replace('{item-type}', itemModel.get('type'))
					+ event;
			};

		PageBuilderColumnItemViewWidthChanger = FwBuilderComponents.ItemView.WidthChanger.extend({
			widths: itemData().item_widths
		});

		PageBuilderColumnItemView = builder.classes.ItemView.extend({
			initialize: function(options) {
				this.defaultInitialize();

				// Live-preview the responsive layout (width / offset / alignment)
				// on the canvas whenever the column's options change.
				this.listenTo(this.model, 'change:atts', this.applyLayoutPreview);

				// Re-preview on device-toggle changes via the module-level registry
				// above (NOT this.listenTo(fwEvents, …) — see the note there).
				if (fwDeviceColumnViews.indexOf(this) === -1) {
					fwDeviceColumnViews.push(this);
				}

				this.initOptions = options;
				this.initOptions.templateData = this.initOptions.templateData || {};
				this.initOptions.modalOptions = this.initOptions.modalOptions || {};

				this.widthChangerView = new PageBuilderColumnItemViewWidthChanger({
					model: this.model,
					view: this,
					modelAttribute: 'width'
				});
			},
			template: _.template(
				'<div class="pb-item-type-column pb-item <% if (hasOptions) { print(' + '"has-options"' + ')} %>">' +
				/**/'<div class="panel fw-row">' +
				/**//**/'<div class="panel-left fw-col-xs-6">' +
				/**//**//**/'<div class="width-changer"></div>' +
				/**//**/'</div>' +
				/**//**/'<div class="panel-right fw-col-xs-6">' +
				/**//**//**/'<div class="controls">' +

				/**//**//**//**/'<% if (hasOptions) { %>' +
				/**//**//**//**/'<i class="dashicons dashicons-admin-generic edit-options" data-hover-tip="<%- edit %>"></i>' +
				/**//**//**//**/'<% } %>' +

				/**//**//**//**/'<i class="dashicons dashicons-admin-page column-item-clone" data-hover-tip="<%- duplicate %>"></i>' +
				/**//**//**//**/'<i class="dashicons dashicons-no column-item-delete" data-hover-tip="<%- remove %>"></i>' +
				/**//**//**//**/'<i class="dashicons dashicons-arrow-down column-item-collapse" data-hover-tip="<%- collapse %>"></i>' +
				/**//**//**/'</div>' +
				/**//**/'</div>' +
				/**/'</div>' +
				/**/'<div class="builder-items"></div>' +
				'</div>'
			),
			render: function() {
				this.defaultRender(this.initOptions.templateData);

				// Scope to THIS column's OWN width-changer slot. `this.$('.width-changer')`
				// = this.$el.find('.width-changer'), which — now that a column can host
				// other columns — also matches every NESTED column's width-changer. The
				// jQuery .append() then clones the parent's width-changer into each child
				// (the "extra width-changer appears in the child" bug). The panel is a
				// direct child of .pb-item-type-column and never contains nested columns
				// (those live in the sibling .builder-items), so finding within the panel
				// targets only this column's own slot.
				this.$el
					.children('.pb-item-type-column')
					.children('.panel')
					.find('.width-changer')
					.first()
					.append(this.widthChangerView.$el);
				this.widthChangerView.delegateEvents();

				this.$el[this.model.get('fw-collapse') ? 'addClass' : 'removeClass']('pb-item-column-collapsed');

				/**
				 * Other scripts can append/prepend other control $elements.
				 * Scope to this column's own panel controls (direct children) so a
				 * nested column's controls aren't matched by `.controls:first`.
				 */
				fwEvents.trigger('fw:page-builder:shortcode:column:controls', {
					$controls: this.$el
						.children('.pb-item-type-column')
						.children('.panel')
						.find('.controls')
						.first(),
					model: this.model,
					builder: builder
				});

				this.applyLayoutPreview();
			},
			/**
			 * Reflect the responsive Width / Offset / Alignment options on the
			 * canvas for the active device (window.fwPbDevice, default 'lg'). Uses
			 * inline styles so it doesn't depend on Bootstrap / frontend-grid
			 * utilities (absent in the admin). Resolves width/offset by the same
			 * mobile-first cascade as the frontend; only sets a property when
			 * applicable, resetting to '' otherwise.
			 */
			applyLayoutPreview: function () {
				if (!this.model) { return; } // guard: skip views whose model is gone
				var atts   = this.model.get('atts') || {};
				var device = window.fwPbDevice || 'lg';

				// Effective width for the active device (mirrors the frontend cascade):
				//   sm (phone, xs): w_phone, else full-width (the xs base is fw-col-12)
				//   md (tablet):    w_tablet, else the base picker width (leave empty
				//                   so the fw-col-sm-* backend class drives the canvas)
				//   lg (desktop):   w_desktop → w_tablet → base picker width
				var w = '';
				if (device === 'sm') {
					w = (atts.w_phone && atts.w_phone !== 'default') ? String(atts.w_phone) : '12';
				} else if (device === 'md') {
					if (atts.w_tablet && atts.w_tablet !== 'default') { w = String(atts.w_tablet); }
				} else {
					_.each(['w_desktop', 'w_tablet'], function (k) {
						if (w === '' && atts[k] && atts[k] !== 'default') { w = String(atts[k]); }
					});
				}
				var widthCss = { 'flex': '', 'max-width': '', 'width': '' };
				if (w === 'auto') {
					widthCss = { 'flex': '1 0 0%', 'max-width': 'none', 'width': 'auto' };
				} else if (/^([1-9]|1[0-2])$/.test(w)) {
					var pct = (parseInt(w, 10) / 12 * 100) + '%';
					widthCss = { 'flex': '0 0 ' + pct, 'max-width': pct, 'width': pct };
				}
				this.$el.css(widthCss);

				// Offset → margin-left, for the active device:
				//   sm: offset_phone | md: offset_tablet | lg: offset_desktop → offset_tablet
				var off = '';
				if (device === 'sm') {
					off = (atts.offset_phone && atts.offset_phone !== 'none') ? String(atts.offset_phone) : '';
				} else if (device === 'md') {
					off = (atts.offset_tablet && atts.offset_tablet !== 'none') ? String(atts.offset_tablet) : '';
				} else {
					_.each(['offset_desktop', 'offset_tablet'], function (k) {
						if (off === '' && atts[k] && atts[k] !== 'none') { off = String(atts[k]); }
					});
				}
				this.$el.css('margin-left', /^([1-9]|1[01])$/.test(off) ? (parseInt(off, 10) / 12 * 100) + '%' : '');

				// Column self vertical alignment (within the row).
				var selfMap = { start: 'flex-start', center: 'center', end: 'flex-end', stretch: 'stretch' };
				this.$el.css('align-self', selfMap[atts.align_self] || '');

				// Content alignment in the canvas — vertical + horizontal, like the live
				// page. The content container is grown to fill the column (flex-canvas.css),
				// so Middle / Bottom / Space Between have room to show when the column is
				// taller than its content (equal-height row). "Top / Default" applies no
				// flex, so elements stay at the top and the drop area fills the column.
				var $items     = this.$el
					.children('.pb-item-type-column')
					.children('.builder-items')
					.first();
				var justifyMap = { start: 'flex-start', center: 'center', end: 'flex-end', between: 'space-between' };
				var alignMap   = { start: 'flex-start', center: 'center', end: 'flex-end' };
				var cv = justifyMap[ atts.content_v ];
				var ch = alignMap[ atts.content_h ];
				if ( cv || ch ) {
					$items.css({
						'display': 'flex',
						'flex-direction': 'column',
						'justify-content': cv || '',
						'align-items': ch || ''
					});
				} else {
					$items.css({ 'display': '', 'flex-direction': '', 'justify-content': '', 'align-items': '' });
				}
			},
			events: {
				'click': 'editOptions',
				'click .edit-options': 'editOptions',
				'click .column-item-clone': 'cloneItem',
				'click .column-item-delete': 'removeItem',
				'click .column-item-collapse': 'collapseItem'
			},
			lazyInitModal: function () {
				this.lazyInitModal = function (){};

				if (_.isEmpty(this.initOptions.modalOptions)) {
					return;
				}

				var eventData = {modalSettings: {buttons: []}};

				/**
				 * eventData.modalSettings can be changed by reference
				 */
				triggerEvent(this.model, 'options-modal:settings', eventData);

				this.modal = new fw.OptionsModal({
					title: itemData().l10n.title,
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
				fwEvents.trigger('fw:page-builder:shortcode:column:modal:before-open', {
					modal: this.modal,
					model: this.model,
					builder: builder,
					flow: flow
				});

				if (! flow.cancelModalOpening) {
					this.modal.open();
				}
			},
			cloneItem: function(e) {
				e.stopPropagation();

				var index = this.model.collection.indexOf(this.model),
					attributes = this.model.toJSON(),
					_items = attributes['_items'],
					clonedColumn;

				delete attributes['_items'];

				clonedColumn = new PageBuilderColumnItem(attributes);

				triggerEvent(clonedColumn, 'clone-item:before');

				this.model.collection.add(clonedColumn, {at: index + 1});
				clonedColumn.get('_items').reset(_items);
			},
			removeItem: function(e) {
				e.stopPropagation();

				this.remove();
				this.model.collection.remove(this.model);
			},
			collapseItem: function(e) {
				e.stopPropagation();
				this.model.set('fw-collapse', !this.model.get('fw-collapse'));
			}
		});

		PageBuilderColumnItem = builder.classes.Item.extend({
			defaults: {
				type: 'column'
			},
			restrictedTypes: itemData().restrictedTypes,
			initialize: function(atts, opts) {
				if (
					!this.get('width')
					&&
					(typeof opts != 'undefined' && typeof opts.$thumb != 'undefined')
				) {
					this.set('width', opts.$thumb.find('.item-data').attr('data-width'));
				}

				this.view = new PageBuilderColumnItemView({
					id: 'page-builder-item-'+ this.cid,
					model: this,
					modalOptions: itemData().options,
					modalSize: itemData().popup_size,
					templateData: {
						hasOptions: !! itemData().options,
						edit : itemData().l10n.edit,
						duplicate : itemData().l10n.duplicate,
						remove : itemData().l10n.remove,
						collapse: itemData().l10n.collapse,
					}
				});

				this.defaultInitialize();
			},
			allowIncomingType: function(type) {
				var allow = _.indexOf(this.restrictedTypes, type) === -1;

				// Never let a section-like item land inside a column. Each section's
				// own allowDestinationType already blocks this, but mirroring it here
				// keeps the drag-highlight correct even for a section-like type with a
				// permissive allowDestinationType.
				if (
					allow
					&& window.fwSectionLikeTypes
					&& typeof window.fwSectionLikeTypes.isSectionLike === 'function'
					&& window.fwSectionLikeTypes.isSectionLike(type)
				) {
					allow = false;
				}

				// One-level nesting cap. A column MAY host columns, but a column that
				// is ITSELF already inside a column may not accept further columns.
				// The owning item of this column's sibling collection is its parent
				// (Backbone relational `collectionKey: '_item'`).
				if (allow && type === 'column') {
					var parent = this.collection && this.collection._item;
					if (parent && parent.get && parent.get('type') === 'column') {
						allow = false;
					}
				}

				var data = {
					allow: allow,
					type: type,
					model: this
				};

				// in this event you can change data.allow by reference
				fwEvents.trigger('fw:builder:page-builder:column:filter:allow-incomming-type', data);

				if (window.fwNestedColDebug !== false) {
					try {
						var pt = (this.collection && this.collection._item && this.collection._item.get)
							? this.collection._item.get('type') : 'root';
						console.debug('[nested-col][backend] column.allowIncomingType("' + type + '") parent=' + pt + ' -> ' + data.allow);
					} catch (e) {}
				}

				return data.allow;
			}
		});

		builder.registerItemClass(PageBuilderColumnItem);
	});

	function itemData () {
		// return fw.unysonShortcodesData()['column'];
		return page_builder_item_type_column_data;
	}
})(fwEvents);
