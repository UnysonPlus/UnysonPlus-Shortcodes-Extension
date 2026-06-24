<?php if ( ! defined( 'FW' ) ) { die( 'Forbidden' ); }

/**
 * WYSIWYG editor enhancements for the Shortcodes extension.
 *
 * Currently a "List style" TinyMCE menu button (editor.js) that applies a single
 * fw-list-* class to the selected <ul>/<ol> — Pros / Cons (check / cross, each in
 * plain, solid and outline), numbered Steps, and Arrow — plus a Clear entry. The
 * markup stays clean (one class, no stacking), and the styles (static/css/editor-content.css)
 * load on the front end AND into the editor iframe for a live preview. Colors read
 * the Color Presets (--color-green / --color-red) so they match the brand and stay
 * overridable in a child theme.
 *
 * This is the home for future editor toolbars too. The list set is filterable via
 * `unysonplus_editor_list_formats`.
 */
class FW_Ext_Shortcodes_Editor {

	/** @var FW_Extension_Shortcodes */
	private $extension;

	public function __construct( $extension ) {
		$this->extension = $extension;

		// Front end: the styles that render the list classes.
		add_action( 'wp_enqueue_scripts', array( $this, '_enqueue_frontend' ), 30 );

		// Editor: add the button to row 1 (before Blockquote, next to the list buttons),
		// load the plugin JS, and feed it the iframe CSS. The menu items are passed as a
		// JS global (see _print_list_styles_js) because the wp-editor option type re-inits
		// TinyMCE in its modal and drops custom init settings like fwlists_styles.
		add_filter( 'mce_buttons', array( $this, '_mce_buttons' ) );
		add_filter( 'mce_external_plugins', array( $this, '_mce_external_plugins' ) );
		add_filter( 'tiny_mce_before_init', array( $this, '_mce_before_init' ), 9 );
		add_action( 'admin_print_footer_scripts', array( $this, '_print_list_styles_js' ) );
	}

	/** The "List style" menu: { title, class } leaves; a nested `items` array = submenu. Filterable. */
	public function list_styles() {
		$styles = array(
			array( 'title' => __( 'Plain', 'fw' ), 'class' => 'fw-list-plain' ),
			array( 'title' => __( 'Pros', 'fw' ), 'items' => array(
				array( 'title' => __( 'Check', 'fw' ),           'class' => 'fw-list-pros' ),
				array( 'title' => __( 'Check — solid', 'fw' ),   'class' => 'fw-list-pros-solid' ),
				array( 'title' => __( 'Check — outline', 'fw' ), 'class' => 'fw-list-pros-outline' ),
			) ),
			array( 'title' => __( 'Cons', 'fw' ), 'items' => array(
				array( 'title' => __( 'Cross', 'fw' ),           'class' => 'fw-list-cons' ),
				array( 'title' => __( 'Cross — solid', 'fw' ),   'class' => 'fw-list-cons-solid' ),
				array( 'title' => __( 'Cross — outline', 'fw' ), 'class' => 'fw-list-cons-outline' ),
			) ),
			array( 'title' => __( 'Steps (numbered)', 'fw' ), 'class' => 'fw-list-steps' ),
			array( 'title' => __( 'Arrow', 'fw' ),            'class' => 'fw-list-arrow' ),
		);
		return apply_filters( 'unysonplus_editor_list_formats', $styles );
	}

	public function _mce_buttons( $buttons ) {
		$buttons = (array) $buttons;
		if ( in_array( 'fwlists', $buttons, true ) ) {
			return $buttons;
		}
		$pos = array_search( 'blockquote', $buttons, true ); // sit right before Blockquote
		if ( false === $pos ) {
			$buttons[] = 'fwlists';
		} else {
			array_splice( $buttons, $pos, 0, 'fwlists' );
		}
		return $buttons;
	}

	/** Expose the list-style menu to editor.js as a JS global (survives the modal re-init). */
	public function _print_list_styles_js() {
		echo '<script>window.fwListStyles=' . wp_json_encode( $this->list_styles() ) . ';</script>' . "\n";
	}

	public function _mce_external_plugins( $plugins ) {
		$plugins['fwlists'] = fw_min_uri( $this->extension->get_uri( '/static/js/editor.js' ) );
		return $plugins;
	}

	public function _mce_before_init( $init ) {
		$init['fwlists_styles'] = wp_json_encode( $this->list_styles() );
		$css = fw_min_uri( $this->extension->get_uri( '/static/css/editor-content.css' ) );
		$init['content_css'] = ( empty( $init['content_css'] ) ? '' : $init['content_css'] . ',' ) . $css;
		return $init;
	}

	public function _enqueue_frontend() {
		// This styles the fw-list-* list classes the "List style" button applies,
		// so it's a genuine FRONT-END stylesheet (not editor chrome). Load it only
		// where a styled list will actually render — most pages have none.
		if ( ! $this->_content_has_list_styles() ) {
			return;
		}

		wp_enqueue_style(
			'fw-shortcodes-editor-content',
			fw_min_uri( $this->extension->get_uri( '/static/css/editor-content.css' ) ),
			array(),
			$this->extension->manifest->get( 'version' )
		);
	}

	/**
	 * Whether the current request will output an fw-list-* styled list. The class
	 * string is stored verbatim inside the page-builder shortcodes in post_content
	 * (e.g. <ul class="fw-list-pros">), so a substring scan is reliable for singular
	 * views. Header/footer-builder or other dynamic content that injects a styled
	 * list outside the main post can force the CSS on via the filter.
	 */
	private function _content_has_list_styles() {
		if ( apply_filters( 'unysonplus_force_list_styles_css', false ) ) {
			return true;
		}

		if ( is_singular() ) {
			$post = get_post();
			if ( $post instanceof WP_Post && false !== strpos( (string) $post->post_content, 'fw-list-' ) ) {
				return true;
			}
		}

		return false;
	}
}
