(function (fwe) {
	var fwDeviceFlexViews = [];
	fwe.on('fw:builder:device-preview', function () {
		for (var i = fwDeviceFlexViews.length - 1; i >= 0; i--) {
			var v = fwDeviceFlexViews[i];
			if (!v || !v.model || !v.el || !document.body.contains(v.el)) { fwDeviceFlexViews.splice(i, 1); continue; }
			try { v.applyFlexPreview(); } catch (e) {}
		}
	});

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
			// Width is now responsive: atts.width = { base, md, lg }, each layer a
			// multi-picker value { preset, custom:{ width_custom } }. The stepper drives
			// the BASE (phone / small) layer — the per-device overrides live in the modal.
			// A legacy flat { preset, custom } (pre-responsive) is treated as the base.
			widthObj: function () {
				var w = (this.model.get('atts') || {}).width;
				if (!w || typeof w !== 'object') { return {}; }
				if (typeof w.base !== 'undefined') { return (w.base && typeof w.base === 'object') ? w.base : {}; }
				return w; // legacy flat { preset, custom }
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
				// Stepping always sets a non-custom preset on the BASE layer (so it
				// overrides a Custom %); keeps any md/lg overrides intact.
				var a = _.extend({}, this.model.get('atts') || {});
				var w = (a.width && typeof a.width === 'object') ? a.width : {};
				var isResp = typeof w.base !== 'undefined';
				var base = _.extend({}, isResp
					? ((w.base && typeof w.base === 'object') ? w.base : {})
					: ((typeof w.preset !== 'undefined') ? w : {}));
				if (String(base.preset) !== String(id)) {
					base.preset = id;
					var nw = isResp ? _.extend({}, w) : {};
					nw.base = base;
					if (typeof nw.md === 'undefined') { nw.md = { preset: 'none' }; }
					if (typeof nw.lg === 'undefined') { nw.lg = { preset: 'none' }; }
					a.width = nw;
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

				// Mount the width stepper in the panel (defaultRender rebuilt the slot).
				this.$el.find('.fx-width-slot:first').append(this.widthChangerView.$el);
				this.widthChangerView.delegateEvents();

				// Canvas preview (device-aware): direction, width sizing, justify/align.
				// Registered on the device-preview bus so a device toggle re-runs it.
				if (fwDeviceFlexViews.indexOf(this) === -1) { fwDeviceFlexViews.push(this); }
				this.applyFlexPreview();

				fwEvents.trigger('fw:page-builder:shortcode:flexbox:controls', {
					$controls: this.$('.controls:first'),
					model: this.model,
					builder: builder
				});
			},

			applyFlexPreview: function () {
				var fxAtts = this.model.get('atts') || {};
				var device = window.fwPbDevice || 'lg';

				// Resolve a per-device value { base, md, lg } with the mobile-first cascade
				// (sm->base, md->md||base, lg->lg||md||base); tolerates a legacy scalar.
				var fxResolve = function (v, d) {
					if (v == null) { return ''; }
					if (typeof v === 'string') { return v; }
					var b = v.base || '', m = v.md || '', l = v.lg || '';
					if (d === 'sm') { return b; }
					if (d === 'md') { return m || b; }
					return l || m || b;
				};

				// Direction (row = side-by-side, column = stacked). CSS keys off fx-dir-row/col.
				var fxIsRow = ( fxResolve(fxAtts.direction, device) !== 'column' );
				this.$el.toggleClass('fx-dir-row', fxIsRow);
				this.$el.toggleClass('fx-dir-col', !fxIsRow);

				// Canvas COLUMN SIZING: size the box to its width so the editor mirrors the
				// real layout. Parent is the owner of THIS model's sibling collection.
				var fxParent     = this.model.collection && this.model.collection._item;
				var fxParentType = (fxParent && fxParent.get) ? fxParent.get('type') : null;
				var fxParentCol  = ( fxParentType === 'flexbox' && fxResolve( ( fxParent.get('atts') || {} ).direction, device ) === 'column' );

				// Width: pick the active device's layer from the responsive { base, md, lg }
				// (each layer = { preset, custom }), mobile-first; tolerate a legacy flat shape.
				var fxWidthObj;
				(function () {
					var wr = fxAtts.width;
					if (wr && typeof wr === 'object' && typeof wr.base !== 'undefined') {
						var lay  = function (x) { return (x && typeof x === 'object') ? x : {}; };
						var setP = function (x) { x = lay(x); return x.preset && x.preset !== 'none' && x.preset !== ''; };
						var b = lay(wr.base);
						if (device === 'sm')      { fxWidthObj = b; }
						else if (device === 'md') { fxWidthObj = setP(wr.md) ? lay(wr.md) : b; }
						else                      { fxWidthObj = setP(wr.lg) ? lay(wr.lg) : (setP(wr.md) ? lay(wr.md) : b); }
					} else {
						fxWidthObj = (wr && typeof wr === 'object') ? wr : {};
					}
				})();
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

				if (fxResolve(fxAtts.flex_grow, device) === 'yes' && !fxParentCol) {
					this.$el.css({ 'flex': '1 1 0', 'max-width': '' });
				} else if (fxWidthCss && fxParentCol) {
					this.$el.css({ 'flex': '', 'max-width': fxWidthCss });
				} else if (fxWidthCss) {
					this.$el.css({ 'flex': '0 0 ' + fxWidthCss, 'max-width': fxWidthCss });
				} else {
					this.$el.css({ 'flex': fxParentCol ? '' : '0 0 100%', 'max-width': '', 'width': '' });
				}

				// Justify (main axis) + Align (cross axis) on the flexbox's child container.
				var $fxBox = this.$el.children('.custom-flexbox').children('.builder-items').first();
				if ($fxBox.length) {
					var jcMap = { start: 'flex-start', center: 'center', end: 'flex-end', between: 'space-between', around: 'space-around', evenly: 'space-evenly' };
					var aiMap = { start: 'flex-start', center: 'center', end: 'flex-end', stretch: 'stretch', baseline: 'baseline' };
					var jcv = fxResolve(fxAtts.justify_content, device);
					var aiv = fxResolve(fxAtts.align_items, device);
					$fxBox.css({ 'justify-content': jcMap[ jcv ] || '', 'align-items': aiMap[ aiv ] || '' });
					this.$el.toggleClass('fx-has-align', !!( aiv && aiMap[ aiv ] && aiv !== 'stretch' ));
					this.$el.toggleClass('fx-justify', fxIsRow && !!( jcv && jcMap[ jcv ] ));
				}

				// A direct child flexbox's width preview depends on THIS direction; re-run theirs.
				var fxKids = this.model.get('_items');
				if (fxKids && typeof fxKids.each === 'function') {
					fxKids.each(function (kid) {
						if (kid && kid.view && kid.get && kid.get('type') === 'flexbox' && typeof kid.view.applyFlexPreview === 'function') {
							kid.view.applyFlexPreview();
						}
					});
				}
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

				// Migrate legacy flat responsive atts to the new { base, md, lg } shape
				// BEFORE the modal reads them, so a pre-existing flexbox keeps its
				// per-device Direction / Justify when re-saved (mirrors views/view.php).
				// Idempotent: skips values already in the new shape; collapses layers
				// that equal the one below them so the DOM stays clean.
				(function (self) {
					var a = _.extend({}, self.model.get('atts') || {});
					var changed = false;
					var isObj = function (v) { return v && typeof v === 'object'; };
					var collapse = function (base, mid, top) {
						var md = (mid !== base) ? mid : '';
						var lg = (top !== (md || base)) ? top : '';
						return { base: base, md: md, lg: lg };
					};

					// Direction: mobile-first — base = mobile||desktop, tablet, desktop.
					if (!isObj(a.direction)) {
						var dBase = (a.direction === 'column') ? 'column' : 'row';
						var dMob = (a.direction_mobile === 'row' || a.direction_mobile === 'column') ? a.direction_mobile : dBase;
						var dTab = (a.direction_tablet === 'row' || a.direction_tablet === 'column') ? a.direction_tablet : dBase;
						a.direction = collapse(dMob, dTab, dBase);
						changed = true;
					}

					// Justify: synthesize md/lg only if a flat override exists.
					if (!isObj(a.justify_content)) {
						var jcValid = ['start', 'center', 'end', 'between', 'around', 'evenly'];
						var jBase = String(a.justify_content || '');
						var jMob = jcValid.indexOf(a.justify_content_mobile) !== -1 ? a.justify_content_mobile : '';
						var jTab = jcValid.indexOf(a.justify_content_tablet) !== -1 ? a.justify_content_tablet : '';
						if (jMob || jTab) {
							var norm = function (v) { return jcValid.indexOf(v) !== -1 ? v : 'start'; };
							a.justify_content = collapse(norm(jMob || jBase), norm(jTab || jBase), norm(jBase));
						} else {
							a.justify_content = { base: jBase, md: '', lg: '' };
						}
						changed = true;
					}

					// gap / align_items / align_self / order / reverse / wrap / align_content /
					// flex_grow had no flat per-device companions — fold a legacy scalar into base.
					['gap', 'align_items', 'align_self', 'order', 'reverse', 'wrap', 'align_content', 'flex_grow'].forEach(function (k) {
						if (!isObj(a[k])) { a[k] = { base: String(a[k] || ''), md: '', lg: '' }; changed = true; }
					});

					// Min Height: legacy flat { value, unit } → base; else default responsive.
					if (a.min_height && isObj(a.min_height) && typeof a.min_height.base === 'undefined') {
						a.min_height = { base: a.min_height, md: { value: '', unit: 'vh' }, lg: { value: '', unit: 'vh' } };
						changed = true;
					} else if (!isObj(a.min_height)) {
						a.min_height = { base: { value: '', unit: 'vh' }, md: { value: '', unit: 'vh' }, lg: { value: '', unit: 'vh' } };
						changed = true;
					}

					// Width: legacy flat multi-picker { preset, custom } applied tablet-up
					// (md); the retired Phone Width (width_phone) → base. New shape is
					// { base, md, lg }, each a { preset, custom } layer.
					if (a.width && isObj(a.width) && typeof a.width.base === 'undefined') {
						var frac = ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12'];
						var wp = String(a.width_phone || '');
						a.width = {
							base: (frac.indexOf(wp) !== -1) ? { preset: wp } : { preset: 'none' },
							md:   a.width,
							lg:   { preset: 'none' }
						};
						changed = true;
					} else if (!isObj(a.width)) {
						a.width = { base: { preset: 'none' }, md: { preset: 'none' }, lg: { preset: 'none' } };
						changed = true;
					}

					// Drop the retired flat overrides so they don't linger in saved atts.
					['direction_mobile', 'direction_tablet', 'justify_content_mobile', 'justify_content_tablet', 'responsive_note', 'width_phone'].forEach(function (k) {
						if (typeof a[k] !== 'undefined') { delete a[k]; changed = true; }
					});

					if (changed) { self.model.set('atts', a); }
				})(this);

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
