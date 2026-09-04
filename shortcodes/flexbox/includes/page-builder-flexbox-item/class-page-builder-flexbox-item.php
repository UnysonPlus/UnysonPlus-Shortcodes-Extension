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
			// 'fw' added: FlexboxWidthChanger extends fw.View as of shortcodes
			// 1.13.41 (was Backbone.View, which it never declared).
			array('fw', 'fw-events'),
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
		$shortcode = fw_ext( 'shortcodes' )->get_shortcode( 'flexbox' );
		$base      = '/includes/page-builder-flexbox-item/static/img/tiles/';

		$in_tb = function_exists( 'up_theme_builder_current_admin_post_type' )
			&& in_array( up_theme_builder_current_admin_post_type(), array( 'up_header', 'up_body', 'up_footer' ), true );

		// Theme Builder (Header / Body / Footer): the FULL set of semantic-tag tiles under "Structure"
		// — what site chrome is built from. Each drops the flexbox preset to that html_tag.
		if ( $in_tb ) {
			$tags   = array(
				'div' => __( 'Div', 'fw' ),
				'main' => __( 'Main', 'fw' ),
				'article' => __( 'Article', 'fw' ),
				'header' => __( 'Header', 'fw' ),
				'footer' => __( 'Footer', 'fw' ),
				'aside' => __( 'Aside', 'fw' ),
				'nav' => __( 'Nav', 'fw' ),
			);
			$thumbs = array();
			foreach ( $tags as $tag => $label ) {
				$thumbs[ 'flexbox_' . $tag ] = array(
					'tab'         => __( 'Structure', 'fw' ),
					'title'       => $label,
					'description' => sprintf( __( 'A flexbox container that outputs a %s element.', 'fw' ), strtoupper( $tag ) ),
					'icon'        => $shortcode->locate_URI( $base . $tag . '.svg' ),
					'data'        => array( 'fxtag' => $tag ),
				);
			}
			return $thumbs;
		}

		// Normal pages / posts: the MODERN layout primitives. Both drop the flexbox (Div) preset to
		// the right CSS display — Flexbox = one-dimensional rows/stacks, Grid = two-dimensional columns.
		$tab = __( 'Layout Elements', 'fw' );
		return array(
			'flexbox_section' => array(
				'tab'         => $tab,
				'title'       => __( 'Section', 'fw' ),
				'description' => __( 'A full-width content band — where you start a page. Outputs a section element and keeps its content contained to the site width.', 'fw' ),
				'icon'        => $shortcode->locate_URI( $base . 'section.svg' ),
				'data'        => array( 'fxtag' => 'section', 'fxdisplay' => 'block' ),
			),
			'flexbox_flex' => array(
				'tab'         => $tab,
				'title'       => __( 'Flexbox (div)', 'fw' ),
				'description' => __( 'A one-dimensional layout — a row or a stack of items (CSS flexbox). Outputs a div.', 'fw' ),
				'icon'        => $shortcode->locate_URI( $base . 'flexbox.svg' ),
				'data'        => array( 'fxtag' => 'div', 'fxdisplay' => 'flex' ),
			),
			'flexbox_grid' => array(
				'tab'         => $tab,
				'title'       => __( 'Grid (div)', 'fw' ),
				'description' => __( 'A two-dimensional column layout (CSS grid). Set the number of columns in the options. Outputs a div.', 'fw' ),
				'icon'        => $shortcode->locate_URI( $base . 'grid.svg' ),
				'data'        => array( 'fxtag' => 'div', 'fxdisplay' => 'grid' ),
			),
			'flexbox_block' => array(
				'tab'         => $tab,
				'title'       => __( 'Block (div)', 'fw' ),
				'description' => __( 'A plain container that stacks its children (CSS block) — the simplest wrapper, for grouping, spacing or a background. Outputs a div.', 'fw' ),
				'icon'        => $shortcode->locate_URI( $base . 'div.svg' ),
				'data'        => array( 'fxtag' => 'div', 'fxdisplay' => 'block' ),
			),
		);
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
