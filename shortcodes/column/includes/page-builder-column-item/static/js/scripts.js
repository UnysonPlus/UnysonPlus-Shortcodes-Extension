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

				// Resolve a per-device value { base, md, lg } for the active canvas device
				// with the frontend mobile-first cascade (sm->base, md->md||base, lg->lg||md||base).
				// Tolerates a legacy scalar.
				var resolveResponsive = function (v, d) {
					if (v == null) { return ''; }
					if (typeof v === 'string') { return v; }
					var b = v.base || '', m = v.md || '', l = v.lg || '';
					if (d === 'sm') { return b; }
					if (d === 'md') { return m || b; }
					return l || m || b;
				};

				// Per-device width/offset: prefer the merged responsive controls (col_width /
				// col_offset), fall back to the legacy per-device atts, so the canvas preview is
				// unchanged after the merge.
				var cw = atts.col_width, co = atts.col_offset;
				var w_phone   = ( cw && typeof cw === 'object' ) ? ( cw.base || '' ) : ( atts.w_phone || '' );
				var w_tablet  = ( cw && typeof cw === 'object' ) ? ( cw.md   || '' ) : ( atts.w_tablet || '' );
				var w_desktop = ( cw && typeof cw === 'object' ) ? ( cw.lg   || '' ) : ( atts.w_desktop || '' );
				var o_phone   = ( co && typeof co === 'object' ) ? ( co.base || '' ) : ( atts.offset_phone || '' );
				var o_tablet  = ( co && typeof co === 'object' ) ? ( co.md   || '' ) : ( atts.offset_tablet || '' );
				var o_desktop = ( co && typeof co === 'object' ) ? ( co.lg   || '' ) : ( atts.offset_desktop || '' );

				// Effective width for the active device (mirrors the frontend cascade):
				//   sm (phone, xs): w_phone, else full-width (the xs base is fw-col-12)
				//   md (tablet):    w_tablet, else the base picker width (leave empty
				//                   so the fw-col-sm-* backend class drives the canvas)
				//   lg (desktop):   w_desktop → w_tablet → base picker width
				var w = '';
				if (device === 'sm') {
					w = (w_phone && w_phone !== 'default') ? String(w_phone) : '12';
				} else if (device === 'md') {
					if (w_tablet && w_tablet !== 'default') { w = String(w_tablet); }
				} else {
					if (w_desktop && w_desktop !== 'default') { w = String(w_desktop); }
					else if (w_tablet && w_tablet !== 'default') { w = String(w_tablet); }
				}
				var widthCss = { 'flex': '', 'max-width': '', 'width': '' };
				if (w === 'auto') {
					widthCss = { 'flex': '1 0 0%', 'max-width': 'none', 'width': 'auto' };
				} else if (/^([1-9]|1[0-2])$/.test(w)) {
					var pct = (parseInt(w, 10) / 12 * 100) + '%';
					widthCss = { 'flex': '0 0 ' + pct, 'max-width': pct, 'width': pct };
				} else if (/^[1-4]5$/.test(w)) {
					// Fifths (15/25/35/45 = 1/5..4/5). {numerator}*20% — mirrors the
					// fw-col-*-{15,25,35,45} frontend/backend classes (20/40/60/80%).
					var pct5 = (parseInt(w.charAt(0), 10) * 20) + '%';
					widthCss = { 'flex': '0 0 ' + pct5, 'max-width': pct5, 'width': pct5 };
				}
				this.$el.css(widthCss);

				// Reflect the EFFECTIVE per-device width on the ◄ N ► width label too, so it
				// matches the canvas + frontend for the previewed device. `w` is the override
				// for this device ('' = none → restore the native base-width title). On phone
				// `w` is '12' (columns are full-width there), so the label reads 1/1 — accurate.
				if (this.widthChangerView && this.widthChangerView.$el) {
					var $cur = this.widthChangerView.$el.find('.current-width');
					if ($cur.length) {
						if (w !== '') {
							var fracMap = { '1':'1/12','2':'1/6','3':'1/4','4':'1/3','5':'5/12','6':'1/2','7':'7/12','8':'2/3','9':'3/4','10':'5/6','11':'11/12','12':'1/1','15':'1/5 (20%)','25':'2/5 (40%)','35':'3/5 (60%)','45':'4/5 (80%)' };
							$cur.text(w === 'auto' ? 'Auto' : (fracMap[w] || (w + '/12')));
						} else {
							var nw = _.findWhere(this.widthChangerView.widths, { id: this.model.get('width') });
							if (nw) { $cur.text(nw.title); }
						}
					}
				}

				// Offset → margin-left, for the active device:
				//   sm: offset_phone | md: offset_tablet | lg: offset_desktop → offset_tablet
				var off = '';
				if (device === 'sm') {
					off = (o_phone && o_phone !== 'none') ? String(o_phone) : '';
				} else if (device === 'md') {
					off = (o_tablet && o_tablet !== 'none') ? String(o_tablet) : '';
				} else {
					if (o_desktop && o_desktop !== 'none') { off = String(o_desktop); }
					else if (o_tablet && o_tablet !== 'none') { off = String(o_tablet); }
				}
				this.$el.css('margin-left', /^([1-9]|1[01])$/.test(off) ? (parseInt(off, 10) / 12 * 100) + '%' : '');

				// Column self vertical alignment (within the row).
				var selfMap = { start: 'flex-start', center: 'center', end: 'flex-end', stretch: 'stretch' };
				this.$el.css('align-self', selfMap[ resolveResponsive( atts.align_self, device ) ] || '');

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

				// If this column hosts NESTED COLUMNS, its `.builder-items` is a grid
				// ROW (flex-canvas tags it `.fw-pb-flex-row`: display:flex; flex-wrap),
				// NOT a vertical content stack. Forcing `flex-direction:column` +
				// justify-content here would fight that row layout — the nested
				// columns, sized with `flex:0 0 width%`, then collapse on the main
				// axis into a thin sliver. Content alignment is a LEAF-content concept,
				// so skip the override (and clear any stale inline flex) when child
				// columns are present and let flex-canvas govern the row.
				var hasChildColumn = false;
				var childItems = this.model.get('_items');
				if ( childItems && childItems.each ) {
					childItems.each(function ( child ) {
						if ( child.get('type') === 'column' ) { hasChildColumn = true; }
					});
				}

				// Content Direction = Inline (row): the `sc-col-inline` class (styles.css) lays
				// the leaf elements out side-by-side (content-sized), copying the Flexbox item's
				// approach. Skipped when the column hosts nested columns (flex-canvas governs those).
				var isRow = ( atts.content_direction === 'row' ) && ! hasChildColumn;
				this.$el[ isRow ? 'addClass' : 'removeClass' ]( 'sc-col-inline' );

				var contentH = resolveResponsive( atts.content_h, device );
				var contentV = resolveResponsive( atts.content_v, device );

				// Axis-aware (mirrors view.php + the flexbox item): a row swaps the flex axes,
				// so Content Alignment drives justify-content in a row but align-items in a
				// column — and vice-versa for Content Vertical Alignment.
				var jc = isRow ? justifyMap[ contentH ] : justifyMap[ contentV ];
				var ai = isRow ? alignMap[ contentV ]   : alignMap[ contentH ];

				// Content Order = per-device Reverse switch { base, md, lg } of yes/no. Resolve
				// the effective reverse for the active device (mobile-first). Tolerates a legacy
				// scalar (all / tablet / mobile). Reverse is now LITERAL — no justify flip.
				var dev = window.fwPbDevice;
				var reverse;
				var co  = atts.content_order;
				if ( co && typeof co === 'object' ) {
					reverse = resolveResponsive( co, dev ) === 'yes';
				} else {
					var order = co || '';
					reverse = ( order === 'all' )
						|| ( order === 'tablet' && dev !== 'lg' )
						|| ( order === 'mobile' && dev === 'sm' );
				}

				if ( isRow ) {
					// Clear any stale column inline-flex (from a previous Stacked state) so the
					// sc-col-inline row CSS wins; override only the direction for reverse.
					$items.css({ 'display': '', 'flex-direction': reverse ? 'row-reverse' : '', 'justify-content': jc || '', 'align-items': ai || '' });
				} else if ( ( jc || ai || reverse ) && ! hasChildColumn ) {
					$items.css({
						'display': 'flex',
						'flex-direction': reverse ? 'column-reverse' : 'column',
						'justify-content': jc || '',
						'align-items': ai || ''
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

					// Migrate legacy per-device Width/Offset atts into the merged responsive controls
					// (col_width / col_offset) BEFORE the modal opens, so the switcher shows the saved
					// values and a save persists the new { base, md, lg } shape. Mirrors view.php.
					(function (model) {
						var a = model.get('atts') || {}, changed = false;
						if (a.col_width == null && (a.w_phone != null || a.w_tablet != null || a.w_desktop != null)) {
							a.col_width = { base: a.w_phone || 'default', md: a.w_tablet || '', lg: a.w_desktop || '' };
							changed = true;
						}
						if (a.col_offset == null && (a.offset_phone != null || a.offset_tablet != null || a.offset_desktop != null)) {
							a.col_offset = { base: a.offset_phone || 'none', md: a.offset_tablet || '', lg: a.offset_desktop || '' };
							changed = true;
						}
						if (changed) { model.set('atts', a); }
					})(this.model);

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
