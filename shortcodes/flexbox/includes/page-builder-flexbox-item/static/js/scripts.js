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
					? Object.assign(eventData, data)
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
		var FlexboxWidthChanger = fw.View.extend({
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
			template: fw.template(
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
				return this.widths.filter(function (o) { return o['id'] === p; })[0] ? p : 'none';
			},
			setId: function (id) {
				// Stepping always sets a non-custom preset on the BASE layer (so it
				// overrides a Custom %); keeps any md/lg overrides intact.
				var a = Object.assign({}, this.model.get('atts') || {});
				var w = (a.width && typeof a.width === 'object') ? a.width : {};
				var isResp = typeof w.base !== 'undefined';
				var base = Object.assign({}, isResp
					? ((w.base && typeof w.base === 'object') ? w.base : {})
					: ((typeof w.preset !== 'undefined') ? w : {}));
				if (String(base.preset) !== String(id)) {
					base.preset = id;
					var nw = isResp ? Object.assign({}, w) : {};
					nw.base = base;
					if (typeof nw.md === 'undefined') { nw.md = { preset: 'none' }; }
					if (typeof nw.lg === 'undefined') { nw.lg = { preset: 'none' }; }
					a.width = nw;
					this.model.set('atts', a);
				}
			},
			render: function () {
				var title, p = this.preset();
				// Fifths + content-sizing widths aren't in the twelfths stepper list, so show their
				// own label instead of falling back to "Auto".
				var extra = { '1_5': '1/5', '2_5': '2/5', '3_5': '3/5', '4_5': '4/5', 'fit': 'Fit', 'max': 'Max', 'min': 'Min' };
				if (p === 'custom') {
					var wc = (this.widthObj().custom || {}).width_custom || {};
					title = (String(wc.value || '').trim() !== '') ? (String(wc.value).trim() + (wc.unit || '%')) : 'Custom';
				} else if (extra[p]) {
					title = extra[p];
				} else {
					// NB: hoist the value — inside the callback `this` is not the view.
					var curId = this.currentId();
					title = (this.widths.filter(function (o) { return o['id'] === curId; })[0] || this.widths[0]).title;
				}
				this.$el.html(this.template({ title: title }));
				return this;
			},
			step: function (delta) {
				var ids = this.widths.map(function (o) { return o['id']; });
				var i = ids.indexOf(this.currentId());
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
			template: fw.template(
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
							title = fw.template(
								jQuery.trim(titleTemplate),
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

							title = fw.template('<%= title %>')({title: title});
						}
					} else {
						title = fw.template('<%= title %>')({title: title});
					}
				}

				this.defaultRender(
					jQuery.extend({}, this.initOptions.templateData, {title: title})
				);

				this.$el[this.model.get('fw-collapse') ? 'addClass' : 'removeClass']('pb-item-section-collapsed');

				// Mount the width stepper in the panel (defaultRender rebuilt the slot) — but NOT
				// for a Section-tag Div: a section is always a full-width band, so it has no Width
				// (use Content Width to constrain its content instead).
				if ((this.model.get('atts') || {}).html_tag !== 'section') {
					this.$el.find('.fx-width-slot:first').append(this.widthChangerView.$el);
					this.widthChangerView.delegateEvents();
				}

				// Canvas preview (device-aware): direction, width sizing, justify/align.
				// Registered on the device-preview bus so a device toggle re-runs it.
				if (fwDeviceFlexViews.indexOf(this) === -1) { fwDeviceFlexViews.push(this); }
				this.applyFlexPreview();
				// A Grid's equal cells carry no width and are sized by this parent re-running their
				// preview (below in applyFlexPreview). On first insert the child views don't exist yet
				// when the parent renders, so re-run once after the DOM settles — otherwise the cells
				// stack until the grid is clicked.
				if ((this.model.get('atts') || {}).display === 'grid') {
					var fxSelf = this;
					setTimeout(function () { if (document.body.contains(fxSelf.el)) { fxSelf.applyFlexPreview(); } }, 0);
				}

				fwEvents.trigger('fw:page-builder:shortcode:flexbox:controls', {
					$controls: this.$('.controls:first'),
					model: this.model,
					builder: builder
				});
			},

			applyFlexPreview: function () {
				var fxAtts = this.model.get('atts') || {};
				var device = window.fwPbDevice || 'lg';
				// A Section-tag Div is always a full-width band (Width is hidden for it), so it
				// never sizes itself to a fraction on the canvas.
				var fxIsSection = ( fxAtts.html_tag === 'section' );

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
				// Mark a Grid box so its child cells can detect "my parent is a grid" from the DOM
				// (reliable on first insert, unlike the model back-reference which isn't wired yet).
				this.$el.toggleClass('fx-disp-grid', fxAtts.display === 'grid');

				// Canvas COLUMN SIZING: size the box to its width so the editor mirrors the
				// real layout. Parent is the owner of THIS model's sibling collection.
				var fxParent     = this.model.collection && this.model.collection._item;
				var fxParentType = (fxParent && fxParent.get) ? fxParent.get('type') : null;
				var fxParentCol  = ( fxParentType === 'flexbox' && fxResolve( ( fxParent.get('atts') || {} ).direction, device ) === 'column' );
				// A grid parent sizes its cells via tracks, so an equal-grid cell carries NO width; on
				// the canvas (which previews with flex) distribute those width-less cells equally. Detect
				// the grid parent by the model OR — on first insert, before the model is wired — by the
				// parent element's fx-disp-grid class in the DOM.
				var fxParentGrid = ( fxParentType === 'flexbox' && ( fxParent.get('atts') || {} ).display === 'grid' )
					|| this.$el.parent().closest('.builder-item').hasClass('fx-disp-grid');

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
				} else if ({ '1_5':'20%', '2_5':'40%', '3_5':'60%', '4_5':'80%' }[fxPreset]) {
					fxWidthCss = { '1_5':'20%', '2_5':'40%', '3_5':'60%', '4_5':'80%' }[fxPreset];
				} else if (/^([1-9]|1[0-2])$/.test(fxPreset)) {
					fxWidthCss = (parseInt(fxPreset, 10) / 12 * 100) + '%';
				}

				if (fxIsSection) {
					this.$el.css({ 'flex': '', 'max-width': '', 'width': '' });
				} else if (fxResolve(fxAtts.flex_grow, device) === 'yes' && !fxParentCol) {
					this.$el.css({ 'flex': '1 1 0', 'max-width': '' });
				} else if (fxWidthCss && fxParentCol) {
					this.$el.css({ 'flex': '', 'max-width': fxWidthCss });
				} else if (fxWidthCss) {
					this.$el.css({ 'flex': '0 0 ' + fxWidthCss, 'max-width': fxWidthCss });
				} else if (fxParentGrid) {
					this.$el.css({ 'flex': '1 1 0', 'max-width': '', 'width': '' });
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

				if (fw.isEmpty(this.initOptions.modalOptions)) {
					return;
				}

				// Migrate legacy flat responsive atts to the new { base, md, lg } shape
				// BEFORE the modal reads them, so a pre-existing flexbox keeps its
				// per-device Direction / Justify when re-saved (mirrors views/view.php).
				// Idempotent: skips values already in the new shape; collapses layers
				// that equal the one below them so the DOM stays clean.
				(function (self) {
					var a = Object.assign({}, self.model.get('atts') || {});
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

					// Content Width: became a preset multi-picker ({ preset, custom:{ custom_width } }).
					// A legacy flat unit-input { value, unit } → Custom; anything else → Inherit. Guards
					// against the modal (which expects a `preset` key) silently resetting an existing
					// custom width to Inherit on the next save.
					if (isObj(a.content_width) && typeof a.content_width.preset === 'undefined') {
						if (typeof a.content_width.value !== 'undefined' && String(a.content_width.value).trim() !== '') {
							a.content_width = { preset: 'custom', custom: { custom_width: { value: String(a.content_width.value).replace(/[^0-9.\-]/g, ''), unit: a.content_width.unit || 'px' } } };
						} else {
							a.content_width = { preset: 'inherit' };
						}
						changed = true;
					} else if (!isObj(a.content_width)) {
						a.content_width = { preset: 'inherit' };
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
					// Re-run conditional option visibility when Display / HTML Tag change.
					if (typeof window.fxApplyModalVisibility === 'function') {
						window.fxApplyModalVisibility(values);
					}
				});

				this.listenTo(this.modal, {
					'open': function(){
						// Apply visibility once the options DOM has settled after opening.
						var self = this;
						setTimeout(function () {
							if (typeof window.fxApplyModalVisibility === 'function') {
								window.fxApplyModalVisibility(self.modal.get('values') || self.model.get('atts'));
							}
						}, 0);
						fwEvents.trigger(getEventName(this.model, 'options-modal:open'), {
							modal: this.modal, item: this.model, itemView: this
						});
					},
					'render': function(){
						// The framework rebuilt the options DOM — re-apply visibility.
						if (typeof window.fxApplyModalVisibility === 'function') {
							window.fxApplyModalVisibility(this.modal.get('values') || this.model.get('atts'));
						}
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
				// Palette-tile presets: a freshly-dropped flexbox reads the tile's data-* and presets
				// its atts — data-fxtag → html_tag (Main/Aside/… tiles), data-fxdisplay → display (the
				// Flexbox / Grid tiles drop a flex / grid container right away).
				if (opts && opts.$thumb) {
					var $data = opts.$thumb.find('.item-data');
					var fxTag = $data.attr('data-fxtag');
					var fxDisplay = $data.attr('data-fxdisplay');
					var patch = {};
					if (fxTag) { patch.html_tag = fxTag; }
					if (fxDisplay) { patch.display = fxDisplay; }
					// A freshly-dropped Section defaults to a FULL-WIDTH BAND (background edge-to-edge,
					// content contained) — the page-band convention (Divi / Bricks / Elementor Sections)
					// and what "Section" implies. Only new sections get this explicit flag; the option
					// default stays 'no', so existing sections keep their contained-band rendering.
					if (fxTag === 'section') { patch.full_width = 'yes'; }
					if (Object.keys(patch).length) {
						this.set('atts', Object.assign({}, this.get('atts') || {}, patch));
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

			// Enforce "sections are root-only" in the LIVE canvas: a section-tagged Div that
			// lands nested (has a parent item) is demoted to a plain <div>, mirroring the
			// items-corrector's server-side rule so there is never a <section> inside a
			// <section>. Deferred so the item's collection/parent is resolved after placement;
			// the corrector is the backstop for moves/imports this best-effort check misses.
			var self = this;
			setTimeout(function () {
				var atts = self.get('atts') || {};
				if (atts.html_tag !== 'section') { return; }
				var parent = self.collection && self.collection._item;
				if (parent && parent.get) {
					self.set('atts', Object.assign({}, atts, { html_tag: 'div' }));
				}
			}, 0);
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
					// Flexbox-in-flexbox (Div-in-Div) nests up to the inner-flexbox alias-pool
					// depth. The notation generator cycles fw_inner_flexbox…fw_inner_flexbox16 by
					// depth (modulo the pool); beyond the pool the aliases wrap and the repeated
					// shortcode tags collide, leaking the trailing close tags as text. So cap at
					// the pool size. MUST equal count(fw_flexbox_inner_alias_pool()) in the
					// shortcodes extension helpers.php — bump both together. NOTE: this only
					// measures the DESTINATION depth; dropping a pre-built deep subtree (or an
					// import/paste) can still exceed it, which is why the pool is sized generously
					// (16) well beyond any realistic design rather than relied on as a hard guard.
					var FLEXBOX_NEST_MAX = 16;
					var depth = 0, node = this.collection && this.collection._item;
					while (node && node.get && node.get('type') === 'flexbox') {
						depth++;
						node = (node.collection && node.collection._item) || null;
					}
					if (depth >= FLEXBOX_NEST_MAX) {
						allow = false; reason = 'max-nest-depth-' + FLEXBOX_NEST_MAX;
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

	// ── Conditional option visibility (the flexbox options modal) ───────────────
	// The framework has no declarative show_if, so we reactively show/hide option GROUPS
	// (rendered as #fw-backend-options-group-<id>) and a couple of lone options from the modal's
	// live values, so each KIND of flexbox (dropped from its own palette tile — Section / Flex /
	// Grid / Block) shows ONLY the options that apply to it. Two independent axes drive it:
	//
	//   Display (how it lays out its children):
	//     • Grid-only tracks  (group_grid)     → Display = Grid.
	//     • Flex-only flow    (group_flex)     → Display = Flex  (Direction / Wrap / Reverse).
	//     • Shared arrange    (group_arrange)  → Display = Flex OR Grid  (Gap + Justify / Align).
	//     • Responsive Collapse (lone option)  → Display = Flex OR Grid  (Block has no columns).
	//
	//   HTML Tag (band vs. nestable box):
	//     • Section Style (pattern + variant), Shape Dividers, Full-Width Band → Tag = section
	//       (band-only decoration; parity with the classic Section).
	//     • Placement (group_placement: Width Override / Grow / Shrink / Align Self / Order) →
	//       Tag ≠ section. These describe how a box behaves as a CHILD of a flex/grid parent; a
	//       root <section> is never such a child, so they are all inert for it.
	//
	// Toggling is a `.fx-cond-hidden` class (display:none) in the item's backend CSS, so a hidden
	// option keeps its value — changing Display / Tag reveals it again unchanged.
	function fxApplyModalVisibility (values, $scope) {
		var $ = jQuery;
		values = values || {};
		var display    = values.display || 'flex';
		var isGrid     = ( display === 'grid' );
		var isFlex     = ( display === 'flex' );
		var isFlexGrid = ( isFlex || isGrid );
		var isSection  = ( values.html_tag === 'section' );
		// Scope the lookups to the open modal when we have it (defensive against a second
		// modal), else fall back to the document — only one options modal is open at a time.
		var find = function (sel) {
			var $s = ( $scope && $scope.length ) ? $scope.find( sel ) : $();
			return $s.length ? $s : $( sel );
		};
		var toggle = function ($el, show) {
			if ($el && $el.length) { $el[ show ? 'removeClass' : 'addClass' ]( 'fx-cond-hidden' ); }
		};
		// Display-driven groups.
		toggle( find( '#fw-backend-options-group-group_grid' ),    isGrid );
		toggle( find( '#fw-backend-options-group-group_flex' ),    isFlex );
		toggle( find( '#fw-backend-options-group-group_arrange' ), isFlexGrid );
		// HTML-Tag-driven groups.
		toggle( find( '#fw-backend-options-group-group_section_style' ), isSection );
		toggle( find( '#fw-backend-options-group-group_dividers' ),      isSection );
		toggle( find( '#fw-backend-options-group-group_placement' ),     !isSection );
		// Lone options that sit among always-shown ones → target by id suffix (the modal prefixes
		// option ids, so match the tail).
		find( '[id^="fw-backend-option-"][id$="full_width"]' ).each( function () {
			toggle( $( this ), isSection );
		} );
		find( '[id^="fw-backend-option-"][id$="responsive_collapse"]' ).each( function () {
			toggle( $( this ), isFlexGrid );
		} );
	}
	// Expose so the item view (defined in the register-items closure above) can call it.
	window.fxApplyModalVisibility = fxApplyModalVisibility;
})(fwEvents);
