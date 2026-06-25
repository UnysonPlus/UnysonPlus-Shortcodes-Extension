(function (fwe) {
	fwe.on('fw-builder:' + 'page-builder' + ':register-items', function (builder) {
		var PageBuilderFlexboxItem,
			PageBuilderFlexboxItemView,
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

		// Canvas width stepper — the same UX as the Column's width changer, but
		// snapped to Bootstrap 1/12 columns and bound to the flexbox's atts.width
		// (none = Auto/full). A custom percentage is set via the modal (Styling),
		// not here. < Auto · 1/12 … 12/12 >.
		var FlexboxWidthChanger = Backbone.View.extend({
			tagName: 'div',
			className: 'fx-width-changer fw-builder-item-width-changer',
			// Ordered NARROW -> WIDE so the stepper reads left-to-right and Auto (full
			// width) sits at the wide end: < narrows, > widens. Auto LAST means the
			// decrease arrow is live when a freshly-dropped (Auto) flexbox is selected.
			// 12/12 == full == Auto, so it is omitted here; a modal-set 12 maps to Auto.
			// Titles are reduced to lowest terms (6/12 -> 1/2, 8/12 -> 2/3, …); the ids
			// stay 1..12 (the col-md-* the frontend uses).
			widths: [
				{ id: '1', title: '1/12' },  { id: '2', title: '1/6' },   { id: '3', title: '1/4' },
				{ id: '4', title: '1/3' },   { id: '5', title: '5/12' },  { id: '6', title: '1/2' },
				{ id: '7', title: '7/12' },  { id: '8', title: '2/3' },   { id: '9', title: '3/4' },
				{ id: '10', title: '5/6' },  { id: '11', title: '11/12' },
				{ id: 'none', title: 'Auto' }
			],
			template: _.template(
				'<a href="#" class="decrease-width dashicons dashicons-arrow-left-alt2" onclick="return false;" data-hover-tip="Narrower"></a>' +
				' <span class="current-width fw-wp-link-color"><%- title %></span> ' +
				'<a href="#" class="increase-width dashicons dashicons-arrow-right-alt2" onclick="return false;" data-hover-tip="Wider"></a>'
			),
			events: {
				'click .decrease-width': 'decrease',
				'click .increase-width': 'increase'
			},
			initialize: function (options) {
				this.listenTo(this.model, 'change:atts', this.render);
				this.render();
			},
			// The width is a multi-picker: atts.width = { preset, custom:{ width_custom } }.
			widthObj: function () {
				var w = (this.model.get('atts') || {}).width;
				return (w && typeof w === 'object') ? w : {};
			},
			preset: function () {
				var p = this.widthObj().preset;
				p = (p === null || typeof p === 'undefined' || p === '') ? 'none' : String(p);
				if (p === '12') { p = 'none'; } // 12/12 == full == Auto in the stepper
				return p;
			},
			currentId: function () {
				var p = this.preset();
				if (p === 'custom') { return 'none'; } // no custom slot in the stepper; step from the wide end
				return _.findWhere(this.widths, { id: p }) ? p : 'none';
			},
			setId: function (id) {
				// Stepping always sets a non-custom preset (so it overrides a Custom %).
				var a = _.extend({}, this.model.get('atts') || {});
				var w = _.extend({}, this.widthObj());
				if (String(w.preset) !== String(id)) {
					w.preset = id;
					a.width = w;
					this.model.set('atts', a);
				}
			},
			render: function () {
				var title;
				if (this.preset() === 'custom') {
					var wc = (this.widthObj().custom || {}).width_custom || {};
					title = (String(wc.value || '').trim() !== '') ? (String(wc.value).trim() + (wc.unit || '%')) : 'Custom';
				} else {
					title = (_.findWhere(this.widths, { id: this.currentId() }) || this.widths[0]).title;
				}
				this.$el.html(this.template({ title: title }));
				return this;
			},
			step: function (delta) {
				var ids = _.pluck(this.widths, 'id');
				var i = _.indexOf(ids, this.currentId());
				if (i === -1) { i = 0; }
				i = Math.max(0, Math.min(ids.length - 1, i + delta));
				this.setId(ids[i]);
			},
			decrease: function (e) { e.stopPropagation(); this.step(-1); },
			increase: function (e) { e.stopPropagation(); this.step(1); }
		});

		PageBuilderFlexboxItemView = builder.classes.ItemView.extend({
			initialize: function (options) {
				this.defaultInitialize();

				this.initOptions = options;
				this.initOptions.templateData = this.initOptions.templateData || {};

				// Bootstrap-snapped width stepper for this flexbox (atts.width).
				this.widthChangerView = new FlexboxWidthChanger({ model: this.model });
			},
			template: _.template(
				'<div class="pb-item-type-column pb-item custom-section custom-flexbox">' +
				/**/'<div class="panel fw-row">' +
				/**//**/'<div class="panel-left fw-col-xs-6">' +
				/**//**//**/'<div class="column-title"><%= title %></div>' +
				/**//**//**/'<div class="fx-width-slot"></div>' +
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

				// Canvas preview: reflect Direction (row = side-by-side, column = stacked)
				// in the editor. CSS keys off fx-dir-row / fx-dir-col on the wrapper.
				var fxAtts = this.model.get('atts') || {};
				// Row is the DEFAULT — only an explicit 'column' stacks (a freshly dropped
				// flexbox may have no `direction` saved yet; treat missing as row).
				var fxIsRow = (fxAtts.direction !== 'column');
				this.$el.toggleClass('fx-dir-row', fxIsRow);
				this.$el.toggleClass('fx-dir-col', !fxIsRow);

				// Mount the width stepper in the panel (defaultRender rebuilt the slot).
				this.$el.find('.fx-width-slot:first').append(this.widthChangerView.$el);
				this.widthChangerView.delegateEvents();

				// Canvas COLUMN SIZING: size the box to its width so the editor mirrors the
				// real layout — a 1/2 + 1/2 pair sits SIDE-BY-SIDE (the builder container is
				// a flex row), Auto = full width. This is the proper column behavior.
				//   • Root / Row parent      -> flex 0 0 N% + max-width N%  (flows side-by-side)
				//   • Column parent          -> max-width N% only           (stacked, capped)
				//   • Grow-to-Fill (not col) -> flex 1 1 0                  (absorbs free space)
				// Parent is the owner of THIS model's sibling collection (Backbone `_item`);
				// read it off this.model.collection (a View has no `.collection`).
				var fxParent     = this.model.collection && this.model.collection._item;
				var fxParentType = (fxParent && fxParent.get) ? fxParent.get('type') : null;
				var fxParentCol  = ( fxParentType === 'flexbox' && ( fxParent.get('atts') || {} ).direction === 'column' );

				var fxWidthObj = (fxAtts.width && typeof fxAtts.width === 'object') ? fxAtts.width : {};
				var fxPreset   = fxWidthObj.preset ? String(fxWidthObj.preset) : 'none';
				var fxWidthCss = '';
				if (fxPreset === 'custom') {
					var fxWC = (fxWidthObj.custom || {}).width_custom || {};
					if (typeof fxWC.value !== 'undefined' && String(fxWC.value).trim() !== '') {
						fxWidthCss = String(fxWC.value).replace(/[^0-9.\-]/g, '') + (fxWC.unit || '%');
					}
				} else if (/^([1-9]|1[0-2])$/.test(fxPreset)) {
					fxWidthCss = (parseInt(fxPreset, 10) / 12 * 100) + '%';
				}

				if (fxAtts.flex_grow === 'yes' && !fxParentCol) {
					this.$el.css({ 'flex': '1 1 0', 'max-width': '' });
				} else if (fxWidthCss && fxParentCol) {
					this.$el.css({ 'flex': '', 'max-width': fxWidthCss });
				} else if (fxWidthCss) {
					this.$el.css({ 'flex': '0 0 ' + fxWidthCss, 'max-width': fxWidthCss });
				} else {
					// Auto width. A nested flexbox CONTAINER spans the FULL row (Auto =
					// full width) — force 100% so the canvas content-sizing default (which
					// shrinks plain ELEMENTS to their content) doesn't also shrink this
					// container. In a Column parent it is already full-width, so leave it.
					this.$el.css({ 'flex': fxParentCol ? '' : '0 0 100%', 'max-width': '', 'width': '' });
				}

				// Canvas preview: Justify (main axis) + Align (cross axis) applied to the
				// flexbox's child container, plus a little height when a cross-axis align
				// is set so the effect is visible (the real Min Height isn't used on the
				// canvas — it would make the editor item huge).
				var $fxBox = this.$el.children('.custom-flexbox').children('.builder-items').first();
				if ($fxBox.length) {
					var jcMap = { start: 'flex-start', center: 'center', end: 'flex-end', between: 'space-between', around: 'space-around', evenly: 'space-evenly' };
					var aiMap = { start: 'flex-start', center: 'center', end: 'flex-end', stretch: 'stretch', baseline: 'baseline' };
					$fxBox.css({
						'justify-content': jcMap[ fxAtts.justify_content ] || '',
						'align-items':     aiMap[ fxAtts.align_items ] || ''
					});
					this.$el.toggleClass('fx-has-align', !!( fxAtts.align_items && aiMap[ fxAtts.align_items ] && fxAtts.align_items !== 'stretch' ));
					this.$el.toggleClass('fx-justify', fxIsRow && !!( fxAtts.justify_content && jcMap[ fxAtts.justify_content ] ));
				}

				// A direct child flexbox's width preview depends on THIS flexbox's
				// direction (side-by-side only inside a Row). Re-render child flexboxes so
				// their preview updates immediately when this direction changes.
				var fxKids = this.model.get('_items');
				if (fxKids && typeof fxKids.each === 'function') {
					fxKids.each(function (kid) {
						if (kid && kid.view && kid.get && kid.get('type') === 'flexbox' && typeof kid.view.render === 'function') {
							kid.view.render();
						}
					});
				}

				fwEvents.trigger('fw:page-builder:shortcode:flexbox:controls', {
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

				var eventData = {modalSettings: {buttons: []}};

				triggerEvent(this.model, 'options-modal:settings', eventData);

				this.modal = new fw.OptionsModal({
					title: 'Flexbox',
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
							modal: this.modal, item: this.model, itemView: this
						});
					},
					'render': function(){
						fwEvents.trigger(getEventName(this.model, 'options-modal:render'), {
							modal: this.modal, item: this.model, itemView: this
						});
					},
					'close': function(){
						fwEvents.trigger(getEventName(this.model, 'options-modal:close'), {
							modal: this.modal, item: this.model, itemView: this
						});
					},
					'change:values': function(){
						fwEvents.trigger(getEventName(this.model, 'options-modal:change:values'), {
							modal: this.modal, item: this.model, itemView: this
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

				fwEvents.trigger('fw:page-builder:shortcode:flexbox:modal:before-open', {
					modal: this.modal,
					model: this.model,
					builder: builder,
					flow: flow
				});

				if (! flow.cancelModalOpening) {
					// Pass the model's CURRENT atts so the modal re-renders with the
					// latest values — keeps it in sync with the canvas width stepper
					// (and any other model edits) made since the modal was first built.
					this.modal.open(this.model.get('atts'));
				}
			},
			cloneItem: function (e) {
				e.stopPropagation();

				var index = this.model.collection.indexOf(this.model),
					attributes = this.model.toJSON(),
					_items = attributes['_items'],
					clonedItem;

				delete attributes['_items'];

				clonedItem = new PageBuilderFlexboxItem(attributes);

				triggerEvent(clonedItem, 'clone-item:before');

				this.model.collection.add(clonedItem, {at: index + 1});
				clonedItem.get('_items').reset(_items);
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

		PageBuilderFlexboxItem = builder.classes.Item.extend({
			defaults: {
				type: 'flexbox'
			},
			initialize: function (atts, opts) {
				// Per-tag palette tiles: a freshly-dropped flexbox reads the tile's
				// data-fxtag (set in get_thumbnails_data) and presets its html_tag, so
				// dragging the "Main"/"Aside"/… tile gives a <main>/<aside> right away.
				if (opts && opts.$thumb) {
					var fxTag = opts.$thumb.find('.item-data').attr('data-fxtag');
					if (fxTag) {
						this.set('atts', _.extend({}, this.get('atts') || {}, { html_tag: fxTag }));
					}
				}

				this.view = new PageBuilderFlexboxItemView({
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
				var allow = true;
				var reason = 'ok';

				// Block the Section/Row/Column/Container structural types — a flexbox
				// is its own layout primitive and never mixes with the grid system.
				if (type === 'container' || type === 'column' || type === 'row') {
					allow = false; reason = 'structural-grid-type';
				} else if (
					window.fwSectionLikeTypes
					&& typeof window.fwSectionLikeTypes.isSectionLike === 'function'
					&& window.fwSectionLikeTypes.isSectionLike(type)
				) {
					allow = false; reason = 'section-like';
				} else if (type === 'flexbox') {
					// One level of flexbox-in-flexbox: a flexbox that is ITSELF already
					// inside a flexbox may not accept further flexboxes (WordPress can't
					// nest the same shortcode tag deeper than the fw_inner_flexbox alias).
					var parent = this.collection && this.collection._item;
					if (parent && parent.get && parent.get('type') === 'flexbox') {
						allow = false; reason = 'one-level-nest-cap';
					}
				}

				if (window.fwFlexboxDebug !== false) {
					var pt = (this.collection && this.collection._item && this.collection._item.get)
						? this.collection._item.get('type') : 'root';
					console.debug('[flexbox] allowIncomingType("' + type + '") parent=' + pt + ' -> ' + allow + ' (' + reason + ')');
				}
				return allow;
			},
			allowDestinationType: function (type) {
				// ! No "this" here — called on the prototype without an instance.
				var allow;
				if (type === null) {
					// null = ROOT: a flexbox is a top-level primitive, so it drops
					// straight onto the canvas with NO surrounding <section>.
					allow = true;
				} else if (
					window.fwSectionLikeTypes
					&& typeof window.fwSectionLikeTypes.isSectionLike === 'function'
					&& window.fwSectionLikeTypes.isSectionLike(type)
				) {
					// Also valid inside a Section band.
					allow = true;
				} else {
					// And inside another flexbox (one nested level). Never a Column.
					allow = (type === 'flexbox');
				}

				if (window.fwFlexboxDebug !== false) {
					console.debug('[flexbox] allowDestinationType("' + type + '") -> ' + allow);
				}
				return allow;
			}
		});

		builder.registerItemClass(PageBuilderFlexboxItem);

		// ── Debug tool ──────────────────────────────────────────────────────────
		// Run  fwFlexboxDebugInfo()  in the browser console to print the current
		// builder tree (nested item types + each flexbox's html_tag), so we can see
		// whether a dropped flexbox landed at ROOT or got wrapped in a Section/Column.
		// Per-decision logging is on by default; silence it with  fwFlexboxDebug=false.
		window.fwFlexboxDebugInfo = function () {
			var lines = [];
			(function dump(collection, depth) {
				if (!collection || typeof collection.each !== 'function') { return; }
				collection.each(function (item) {
					var atts = item.get('atts') || {};
					var tag  = atts.html_tag ? (' <' + atts.html_tag + '>') : '';
					var dir  = atts.direction ? (' dir=' + atts.direction) : '';
					var w    = (atts.width && atts.width !== 'none') ? (' w=' + atts.width) : '';
					lines.push(new Array(depth + 1).join('   ') + '- ' + item.get('type') + tag + dir + w);
					dump(item.get('_items'), depth + 1);
				});
			})(builder.rootItems, 0);
			console.log('=== Flexbox builder tree (root → leaves) ===\n' + lines.join('\n'));
			return lines.join('\n');
		};
	});

	function itemData () {
		return page_builder_item_type_flexbox_data;
	}
})(fwEvents);
