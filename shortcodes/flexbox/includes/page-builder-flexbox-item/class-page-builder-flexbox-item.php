<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * The 'flexbox' page-builder item type — a self-contained, nestable flex
 * container. Modeled on Page_Builder_Container_Item but with its own type so it
 * never collides with (or modifies) Section / Row / Column / Container.
 */
class Page_Builder_Flexbox_Item extends Page_Builder_Item
{
	public function get_type()
	{
		return 'flexbox';
	}

	private function get_shortcode_options()
	{
		return fw_ext('shortcodes')->get_shortcode('flexbox')->get_options();
	}

	private function get_shortcode_config()
	{
		return fw_ext('shortcodes')->get_shortcode('flexbox')->get_shortcode_config();
	}

	/**
	 * Called when the builder is rendered.
	 */
	public function enqueue_static()
	{
		$shortcode_instance = fw_ext('shortcodes')->get_shortcode('flexbox');

		// Reuse the SECTION builder-item CSS for the canvas item look (same
		// `.custom-section` markup), like the Container item does.
		$section_instance = fw_ext('shortcodes')->get_shortcode('section');
		wp_enqueue_style(
			$this->get_builder_type() . '_item_type_' . $this->get_type() . '_base',
			$section_instance->locate_URI('/includes/page-builder-section-item/static/css/styles.css'),
			array(),
			fw()->theme->manifest->get_version()
		);

		// Flexbox-specific canvas CSS: a real, targetable child drop zone (so an
		// empty flexbox can receive elements / a nested flexbox) + the row preview.
		wp_enqueue_style(
			$this->get_builder_type() . '_item_type_' . $this->get_type(),
			$shortcode_instance->locate_URI('/includes/page-builder-flexbox-item/static/css/styles.css'),
			array( $this->get_builder_type() . '_item_type_' . $this->get_type() . '_base' ),
			fw_ext('shortcodes')->manifest->get_version()
		);

		wp_enqueue_script(
			$this->get_builder_type() . '_item_type_' . $this->get_type(),
			$shortcode_instance->locate_URI('/includes/page-builder-flexbox-item/static/js/scripts.js'),
			array('fw-events', 'underscore'),
			fw_ext('shortcodes')->manifest->get_version(),
			true
		);

		wp_localize_script(
			$this->get_builder_type() . '_item_type_' . $this->get_type(),
			str_replace('-', '_', $this->get_builder_type() . '_item_type_' . $this->get_type() . '_data'),
			$shortcode_instance->get_item_data()
		);
	}

	/**
	 * One palette tile PER semantic HTML tag (Div / Main / Article / Header / Footer
	 * / Aside / Nav) — all dropping the SAME 'flexbox' item type, but each tile
	 * carries a data-fxtag which the JS reads on drop to preset html_tag. Mirrors how
	 * Page_Builder_Simple_Item emits one thumbnail per shortcode via the `data` key.
	 */
	protected function get_thumbnails_data()
	{
		$config = $this->get_shortcode_config();
		$tab    = isset( $config['tab'] ) ? $config['tab'] : __( 'Structure', 'fw' );

		$tags = array(
			'div'     => __( 'Div', 'fw' ),
			'main'    => __( 'Main', 'fw' ),
			'article' => __( 'Article', 'fw' ),
			'header'  => __( 'Header', 'fw' ),
			'footer'  => __( 'Footer', 'fw' ),
			'aside'   => __( 'Aside', 'fw' ),
			'nav'     => __( 'Nav', 'fw' ),
		);

		// A distinct inline-SVG icon per tag that hints at the semantic role
		// (header = top bar, footer = bottom bar, aside = side panel, nav = menu
		// lines, article = text lines, main = content block, div = plain box).
		$tile_icon = function ( $tag ) {
			$accent = '#2271b1';
			$box    = '<rect x="3" y="3" width="42" height="28" rx="3" fill="#eef3f8" stroke="' . $accent . '" stroke-width="1.4"/>';
			$hint   = '';
			switch ( $tag ) {
				case 'header':  $hint = '<rect x="3" y="3" width="42" height="9" rx="3" fill="' . $accent . '" opacity="0.85"/>'; break;
				case 'footer':  $hint = '<rect x="3" y="22" width="42" height="9" rx="3" fill="' . $accent . '" opacity="0.85"/>'; break;
				case 'aside':   $hint = '<rect x="31" y="3" width="14" height="28" rx="3" fill="' . $accent . '" opacity="0.85"/>'; break;
				case 'nav':     $hint = '<rect x="9" y="8" width="30" height="2.5" rx="1.25" fill="' . $accent . '"/><rect x="9" y="13" width="30" height="2.5" rx="1.25" fill="' . $accent . '"/><rect x="9" y="18" width="30" height="2.5" rx="1.25" fill="' . $accent . '"/>'; break;
				case 'article': $hint = '<rect x="9" y="9" width="22" height="2.4" rx="1.2" fill="#8aa6c4"/><rect x="9" y="14" width="30" height="2.4" rx="1.2" fill="#8aa6c4"/><rect x="9" y="19" width="26" height="2.4" rx="1.2" fill="#8aa6c4"/><rect x="9" y="24" width="18" height="2.4" rx="1.2" fill="#8aa6c4"/>'; break;
				case 'main':    $hint = '<rect x="11" y="10" width="26" height="14" rx="2" fill="' . $accent . '" opacity="0.85"/>'; break;
				default:        $hint = ''; // div = plain box
			}
			$svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 34" width="48" height="34">' . $box . $hint . '</svg>';
			return 'data:image/svg+xml,' . rawurlencode( $svg );
		};

		$thumbs = array();
		foreach ( $tags as $tag => $label ) {
			$thumbs[ 'flexbox_' . $tag ] = array(
				'tab'         => $tab,
				'title'       => $label,
				'description' => sprintf( __( 'A flexbox container that outputs a %s element.', 'fw' ), strtoupper( $tag ) ),
				'icon'        => $tile_icon( $tag ),
				'data'        => array( 'fxtag' => $tag ),
			);
		}

		return $thumbs;
	}

	public function get_value_from_attributes($attributes)
	{
		$attributes['type'] = $this->get_type();

		$options = $this->get_shortcode_options();
		if (!empty($options)) {
			if (empty($attributes['atts'])) {
				$attributes['atts'] = fw_get_options_values_from_input(
					$options, array()
				);
			} else {
				$options = fw_extract_only_options($options);

				foreach ($attributes['atts'] as $option_id => $option_value) {
					if (isset($options[$option_id])) {
						$options[$option_id]['value'] = $option_value;
					}
				}

				$attributes['atts'] = fw_get_options_values_from_input(
					$options, array()
				);
			}
		}

		return $attributes;
	}

	public function get_shortcode_data($atts = array())
	{
		$return = array(
			'tag' => $this->get_type()
		);
		if (isset($atts['atts'])) {
			$return['atts'] = $atts['atts'];
		}
		return $return;
	}
}
FW_Option_Type_Builder::register_item_type('Page_Builder_Flexbox_Item');
