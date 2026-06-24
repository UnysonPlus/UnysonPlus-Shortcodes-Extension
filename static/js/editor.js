/* Shortcodes — WYSIWYG editor enhancements (TinyMCE 4).
   "List style" menu button: applies a SINGLE fw-list-* class to the selected
   <ul>/<ol> (stripping any previous fw-list-* first) so the markup stays clean —
   no stacked classes. The menu is read from window.fwListStyles (printed in the
   admin footer) with a getParam fallback — the wp-editor option type re-inits
   TinyMCE in its modal and drops custom init settings, so the global is the
   reliable source. Styles: editor-content.css. Filter (PHP): unysonplus_editor_list_formats. */
( function () {
	if ( typeof tinymce === 'undefined' ) { return; }

	tinymce.PluginManager.add( 'fwlists', function ( editor ) {
		function resolveStyles() {
			if ( typeof window !== 'undefined' && Array.isArray( window.fwListStyles ) ) { return window.fwListStyles; }
			var raw = editor.getParam( 'fwlists_styles' );
			if ( Array.isArray( raw ) ) { return raw; }
			if ( typeof raw === 'string' && raw ) { try { return JSON.parse( raw ); } catch ( e ) {} }
			return [];
		}
		var styles = resolveStyles();

		function listNode() {
			return editor.dom.getParent( editor.selection.getNode(), 'ul,ol' );
		}

		// Keep exactly one fw-list-* class on the list (or none for Clear/Plain).
		function apply( cls ) {
			var node = listNode();
			if ( ! node ) {
				editor.windowManager.alert( 'Place the cursor inside a bullet or numbered list first.' );
				return;
			}
			node.className = ( node.className || '' ).split( /\s+/ ).filter( function ( c ) {
				return c && c.indexOf( 'fw-list-' ) !== 0;
			} ).join( ' ' );
			if ( cls ) { editor.dom.addClass( node, cls ); }
			editor.nodeChanged();
			editor.fire( 'change' );
		}

		function toItems( list ) {
			return list.map( function ( s ) {
				if ( s.items ) { return { text: s.title, menu: toItems( s.items ) }; }
				return { text: s.title, onclick: function () { apply( s[ 'class' ] ); } };
			} );
		}

		var menu = [ { text: 'Clear list style', onclick: function () { apply( '' ); } } ].concat( toItems( styles ) );

		editor.addButton( 'fwlists', {
			type: 'menubutton',
			icon: 'bullist',
			tooltip: 'List style',
			menu: menu
		} );
	} );
}() );
