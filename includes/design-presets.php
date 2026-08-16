<?php if ( ! defined( 'FW' ) ) { die( 'Forbidden' ); }

/**
 * Shortcode Design Presets — uploadable, per-element "designs".
 *
 * A design is a small JSON envelope:
 *   { "unysonplus_design": 1, "shortcode": "image_box", "name": "…", "atts": { …all option values… } }
 *
 * It carries the element's OPTION VALUES (content + design + styling, and the
 * per-element `custom_css` field — which already emits scoped to the element's
 * `.u{hash}` class via dynamic-css.php). Applying a design in the builder is the
 * same, proven path the right-click "Paste Settings" uses:
 *     item.set('atts', merged)  +  modal.set('values', merged, {silent:true})
 * so every option lands in its tab and stays fully editable afterwards. Because a
 * design is just validated VALUES (no markup/JS), it is safe to share/upload; new
 * markup remains a child-theme/dev concern.
 *
 * The UI is a native **"Presets" tab** injected into the edit modal of every
 * enabled shortcode (Upload design / Export current / Browse Library). MVP: Image
 * Box. "Browse Library" will later reuse the Template-Library importer.
 */

// The uploads-based design catalog (import / store / list / remote browse + AJAX).
require_once dirname( __FILE__ ) . '/design-presets/design-library.php';

if ( ! function_exists( 'sc_design_enabled_shortcodes' ) ) :
	/**
	 * Which shortcodes get the Presets tab. Filterable so more can opt in (and so
	 * a Design-Pack plugin could enable its own) without touching this file.
	 */
	function sc_design_enabled_shortcodes() {
		return apply_filters( 'unysonplus_design_enabled_shortcodes', array( 'image_box' ) );
	}
endif;

if ( ! function_exists( 'sc_design_presets_panel_html' ) ) :
	/** The Presets-tab panel markup (server-rendered, safe). JS wires the buttons. */
	function sc_design_presets_panel_html() {
		ob_start();
		?>
		<div class="sc-design-panel" data-sc-design>
			<div class="sc-design-panel__head">
				<h3 class="sc-design-panel__title"><?php echo esc_html__( 'Element Designs', 'fw' ); ?></h3>
				<button type="button" class="button sc-design-btn" data-act="export"><?php echo esc_html__( 'Export current design', 'fw' ); ?></button>
			</div>
			<div class="sc-design-grid" data-sc-design-grid aria-label="<?php echo esc_attr__( 'Designs', 'fw' ); ?>"><!-- tiles injected by design-manager.js --></div>
			<p class="sc-design-panel__empty" data-sc-design-empty hidden><?php echo esc_html__( 'No designs yet for this element. Import or browse designs in Theme Settings → Components → Element Designs.', 'fw' ); ?></p>
			<p class="sc-design-panel__note"><?php echo esc_html__( 'Click a design to apply it — it fills this element\'s options, and every option stays editable in the other tabs. Manage, import and browse designs in Theme Settings → Components → Element Designs.', 'fw' ); ?></p>
		</div>
		<?php
		return ob_get_clean();
	}
endif;

if ( ! function_exists( 'sc_design_presets_tab' ) ) :
	/** The "Presets" tab (a single full-width html panel). */
	function sc_design_presets_tab() {
		return array(
			'title'   => __( 'Presets', 'fw' ),
			'type'    => 'tab',
			'options' => array(
				'sc_design_panel' => array(
					'type'  => 'html-full',
					'label' => false,
					'html'  => sc_design_presets_panel_html(),
				),
			),
		);
	}
endif;

// Inject the Presets tab (after Content) into every enabled shortcode's options.
if ( ! function_exists( '_sc_design_inject_tab' ) ) :
	function _sc_design_inject_tab( $options, $tag ) {
		if ( ! is_array( $options ) || isset( $options['tab_presets'] ) ) { return $options; }
		if ( ! in_array( $tag, (array) sc_design_enabled_shortcodes(), true ) ) { return $options; }

		$tab = sc_design_presets_tab();
		$out = array();
		$done = false;
		foreach ( $options as $k => $v ) {
			$out[ $k ] = $v;
			if ( $k === 'tab_content' ) { $out['tab_presets'] = $tab; $done = true; }
		}
		if ( ! $done ) { $out = array( 'tab_presets' => $tab ) + $out; } // no Content tab → put first
		return $out;
	}
	add_filter( 'fw_shortcode_get_options', '_sc_design_inject_tab', 10, 2 );
endif;

if ( ! function_exists( '_sc_design_enqueue_builder' ) ) :
	/**
	 * Load the builder-side Design manager on the post edit screens, only when the
	 * page builder is available. It wires the Presets-tab panel buttons on modal
	 * open/render, capturing the modal + item so Apply/Export can use them.
	 */
	function _sc_design_enqueue_builder( $hook ) {
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) { return; }
		if ( ! function_exists( 'fw_ext' ) || ! fw_ext( 'page-builder' ) ) { return; }

		$ext = fw_ext( 'shortcodes' );
		if ( ! $ext ) { return; }
		$base = $ext->get_declared_URI( '/includes/design-presets' );
		$ver  = $ext->manifest->get_version();

		wp_enqueue_style( 'sc-design-manager', $base . '/design-manager.css', array(), $ver );
		wp_enqueue_script(
			'sc-design-manager',
			$base . '/design-manager.js',
			array( 'jquery', 'fw-events' ),
			$ver,
			true
		);
		// Installed designs grouped by shortcode → the element picker reads
		// scDesignManager.designs[shortcode]. atts included so Apply works offline;
		// thumbnails are small URLs (decoded to files on import), never base64.
		$designs = array();
		foreach ( fw_design_lib_installed_items() as $it ) {
			$designs[ $it['shortcode'] ][] = array(
				'id'    => $it['id'],
				'name'  => $it['name'],
				'thumb' => $it['thumb'],
				'atts'  => $it['atts'],
			);
		}

		wp_localize_script( 'sc-design-manager', 'scDesignManager', array(
			'schema'   => 1,
			'enabled'  => array_values( (array) sc_design_enabled_shortcodes() ),
			'designs'  => $designs,
			'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'fw_design_lib_manage' ),
			'l10n'    => array(
				'browseSoon' => __( 'The Design Library is coming soon. For now, use “Upload design…”.', 'fw' ),
				'bad'        => __( 'That file is not a valid UnysonPlus design (.json).', 'fw' ),
				'wrongType'  => __( 'This design is made for a different element (%s), not this one.', 'fw' ),
				'applied'    => __( 'Design applied — fine-tune it in the tabs.', 'fw' ),
				'exported'   => __( 'Design exported.', 'fw' ),
				'namePrompt' => __( 'Name this design:', 'fw' ),
			),
		) );
	}
	add_action( 'admin_enqueue_scripts', '_sc_design_enqueue_builder' );
endif;
