<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

class FW_Shortcode_Map extends FW_Shortcode {

	/**
	 *  @var $data = array(
	 *          'unique_id' => array(                               // some unique id (string)  required
	 *              'callback' => array($this, 'callable_method')  // array(stdClass, 'some_public_method') required
	 *              'label' => 'label',                            // data provider label (string) required
	 *              'options' => array()                           // extra options (array of options) optional
	 *          )
	 *       )
	 */
	private $data = array();

	private function load_data()
	{
		if (empty($this->data)) {
			$this->data = apply_filters('fw_shortcode_map_provider', array(
				'custom' => array(
					'callback'   => array($this, '_callback_get_custom_locations'),
					'label'      => __('Custom','fw'),
					'options'    => array(
						'locations' => array(
							'label' => __('Locations', 'fw'),
							'popup-title' => __('Add/Edit Location', 'fw'),
							'type' => 'addable-popup',
							'desc' => false,
							'template' => '{{  if (location.location !== "") {  print(location.location)} else { print("' . __('Note: Please set location', 'fw') . '")} }}',
							'popup-options' => array(
								'location' => array(
									'type' => 'map',
									'label' =>__('Location','fw'),
								),
								'title' => array(
									'type' => 'text',
									'label' => __('Location Title', 'fw'),
									'desc' => __('Set location title', 'fw'),
									'dynamic_content' => false,
								),
								'description' => array(
									'type'  => 'textarea',
									'label' => __('Location Description', 'fw'),
									'desc'  => __('Set location description', 'fw'),
									'dynamic_content' => false,
								),
								'url' => array(
									'type'  => 'text',
									'label' => __('Location Url', 'fw'),
									'desc'  => __('Set page url (Ex: http://example.com)', 'fw'),
									'dynamic_content' => false,
								),
								'thumb' => array(
									'label'       => __('Location Image', 'fw'),
									'desc'        => __('Add location image', 'fw'),
									'type'        => 'upload',
								)
							)
						)
					)
				)
			));
		}
	}

	public function _callback_get_custom_locations($atts) {
		$rows = fw_akg('data_provider/custom/locations', $atts, array());

		$result = array();
		if (!empty($rows)) {
			foreach($rows as $key => $row) {
				$result[$key]['title']       = fw_akg('title', $row);
				$result[$key]['url']         = fw_akg('url', $row);
				$result[$key]['thumb']       = fw_resize(wp_get_attachment_url(fw_akg('thumb/attachment_id', $row)), 100, 60, true);
				$result[$key]['coordinates'] = fw_akg('location/coordinates', $row);
				$result[$key]['description'] = fw_akg('description', $row);
			}
		}

		return $result;
	}

	/**
	 * Get the list of providers
	 * @internal
	 */
	public function _get_picker_dropdown_choices() {
		$this->load_data();
		$result = array();
		foreach($this->data as $unique_key => $item ) {
			$result[$unique_key] = $item['label'];
		}
		return $result;
	}

	/**
	 * Get the providers' options
	 * @internal
	 */
	public function _get_picker_choices() {
		$this->load_data();
		$result = array();
		foreach($this->data as $unique_key => $item ) {
			$result[$unique_key] = (isset($item['options']) && is_array($item['options'])) ? $item['options'] : array();
		}

		return $result;
	}

	protected function _render($atts, $content = null, $tag = '')
	{
		if (!isset($atts['data_provider']['population_method'])) {
			trigger_error(
				__('No location provider specified for map shortcode', 'fw')
			);
			return '<b>' . __( 'Map Placeholder', 'fw' ) . '</b>';
		}

		$this->load_data();
		$provider = $atts['data_provider']['population_method'];
		if (!isset($this->data[$provider])) {
			return '<!-- WARNING: '
			       . sprintf(__('Unknown location provider "%s" specified for map shortcode', 'fw'), $provider)
			       . ' -->';
		}

		/**
		 * @var $locations array structure:
		 * array(
		 *      array(
		 *          'title' => 'some_string',              //some text  (string) optional
		 *          'url'   => 'http://example.com'        //some uri   (string) optional
		 *          'description' => 'some string'         //some text  (string) optional
		 *          'thumb' => array(
		 *              'attachment_id' => '1'             //Existing atachment id (int)  optional
		 *          )
		 *          'coordinates' => array(                //key 'coordinates'   required
		 *              'lat' => 150                       //latitude   (float)  required
		 *              'lng' => -33.5                     //longitude  (float)  required
		 *          )
		 *      )
		 * )
		 */
		$locations = call_user_func( $this->data[$provider]['callback'], $atts );
		if ( !empty($locations) && is_array($locations) ) {
			foreach( $locations as $key => $location ) {
				if (
					!isset($location['coordinates'])        ||
					!is_array($location['coordinates'])     ||
					!isset($location['coordinates']['lat']) ||
					!isset($location['coordinates']['lng']) ||
					empty($location['coordinates']['lat'])  ||
					empty($location['coordinates']['lng'])
				) {
					//remove locations which has wrong coordinates/empty
					unset($locations[$key]);
				}
			}
		}

		// Map engine is a multi-picker: an engine select + per-engine sub-options.
		$engine = fw_akg('map_engine/engine', $atts, 'osm');
		$engine = in_array($engine, array('osm', 'google'), true) ? $engine : 'osm';

		// Only emit the attributes the front-end script actually consumes.
		// Wrapper styling (bg color, spacing, animation, custom CSS) flows
		// through sc_build_wrapper_attr() in the view, so it must NOT be
		// dumped here as data-* blobs.
		// Map height is a unit-input (e.g. array('value'=>'400','unit'=>'px'))
		// compiled to a CSS length string ("400px", "50vh"). Legacy saves stored
		// a bare pixel number as a string — migrate those to "<n>px".
		$map_height = fw_akg('map_height', $atts);
		if ( is_array($map_height) ) {
			$map_height = class_exists('FW_Option_Type_Unit_Input')
				? FW_Option_Type_Unit_Input::to_string($map_height)
				: ( ( isset($map_height['value']) && trim((string) $map_height['value']) !== '' )
					? trim((string) $map_height['value']) . ( isset($map_height['unit']) ? $map_height['unit'] : 'px' )
					: '' );
		} else {
			$map_height = trim((string) $map_height);
			if ( $map_height !== '' && is_numeric($map_height) ) {
				$map_height .= 'px';
			}
		}

		$map_data_attr = array(
			'data-locations'         => json_encode(array_values($locations)),
			'data-map-engine'        => $engine,
			'data-map-height'        => ( '' !== $map_height ) ? $map_height : false,
			'data-disable-scrolling' => fw_akg('disable_scrolling', $atts, false) ? 'true' : 'false',
		);

		if ($engine === 'google') {
			$map_data_attr['data-map-type'] = strtoupper( fw_akg('map_engine/google/map_type', $atts, 'roadmap') );
		} else {
			// OpenStreetMap (Leaflet) tile style + the site-wide provider keys
			// (only the key matching the chosen style is actually used by the JS).
			$map_data_attr['data-osm-style']         = $this->resolve_osm_style($atts);
			$map_data_attr['data-stadia-key']        = (string) get_option('unysonplus:stadia-key');
			$map_data_attr['data-thunderforest-key'] = (string) get_option('unysonplus:thunderforest-key');
			$map_data_attr['data-maptiler-key']      = (string) get_option('unysonplus:maptiler-key');
		}

		// Keep these out of the wrapper attributes (they are map config, not markup).
		unset($atts['data_provider']);
		unset($atts['map_engine']);
		unset($atts['map_height']);

		// Shared CSS + front-end script (engine-agnostic).
		$this->enqueue_static();
		// The mapping library itself depends on the chosen engine.
		$this->enqueue_map_engine($engine);
		return fw_render_view( $this->locate_path('/views/view.php'), compact('atts', 'content', 'tag', 'map_data_attr') );
	}

	/**
	 * Resolve the OpenStreetMap "Map Style" multi-picker (provider + variant)
	 * into a single OSM_TILES style id understood by scripts.js.
	 *
	 * @param array $atts
	 * @return string e.g. 'standard', 'carto_dark', 'stamen_toner', 'esri_satellite'
	 */
	protected function resolve_osm_style($atts) {
		$mp = fw_akg('map_engine/osm/osm_style', $atts, array());

		// Back-compat: a value saved under the old flat select (a plain string id).
		if (is_string($mp) && $mp !== '') {
			return $mp;
		}

		$provider = is_array($mp) ? fw_akg('provider', $mp, 'osm') : 'osm';

		switch ($provider) {
			case 'carto':
				return fw_akg('carto/carto_variant', $mp, 'carto_light');
			case 'stadia':
				return fw_akg('stadia/stadia_variant', $mp, 'stadia_alidade_smooth');
			case 'thunderforest':
				return fw_akg('thunderforest/tf_variant', $mp, 'tf_cycle');
			case 'maptiler':
				return fw_akg('maptiler/maptiler_variant', $mp, 'maptiler_streets');
			case 'opentopomap':
			case 'cyclosm':
			case 'hot':
				return $provider;
			case 'esri':
				return 'esri_satellite';
			case 'osm':
			default:
				return 'standard';
		}
	}

	/**
	 * Pinned Leaflet release used for the OpenStreetMap engine (CDN).
	 */
	const LEAFLET_VERSION = '1.9.4';

	/**
	 * Enqueue the mapping library for the chosen engine. The shared front-end
	 * script (scripts.js) detects which library a given map wants via its
	 * data-map-engine attribute and waits for the matching global.
	 *
	 * @param string $engine 'osm' | 'google'
	 */
	protected function enqueue_map_engine($engine) {
		if ($engine === 'google') {
			$query_params = array(
				'v'         => 'quarterly',
				'language'  => substr( get_locale(), 0, 2 ),
				'libraries' => 'places',
				'loading'   => 'async',
			);

			if (method_exists('FW_Option_Type_Map', 'api_key')) {
				$query_params['key'] = FW_Option_Type_Map::api_key();
			}

			wp_enqueue_script(
				'google-maps-api-v3',
				'https://maps.googleapis.com/maps/api/js?' . http_build_query($query_params),
				array(),
				$query_params['v'],
				true
			);
			return;
		}

		// Default: OpenStreetMap via Leaflet (free, no API key) from CDN.
		wp_enqueue_style(
			'leaflet',
			'https://unpkg.com/leaflet@' . self::LEAFLET_VERSION . '/dist/leaflet.css',
			array(),
			self::LEAFLET_VERSION
		);
		wp_enqueue_script(
			'leaflet',
			'https://unpkg.com/leaflet@' . self::LEAFLET_VERSION . '/dist/leaflet.js',
			array(),
			self::LEAFLET_VERSION,
			true
		);
	}

	/**
	 * Just a wrapper for the method render
	 * @param $extra array
	 * @param $data array
	 * @return string Generated shortcode html
	 *
	 * @var $extra = arrray(
	 *          'map_type'   => 'roadmap' // string any of (roadmap | terrain | satellite | hybrid )
	 *          'map_height' => '300'     // int height for map canvas block
	 * )
	 *
	 * @var $data = array(
	 *                  array(
	 *                      'description' => 'some desc'   //string
	 *                      'thumb' => array(
	 *                             'attachment_id' => '1'  //int any existing attachment id
	 *                       )
	 *                      'title' =>  'some title',      //string
	 *                      'url'   =>  'http://link.com', //string
	 *                      'location' => array(
	 *                            'coordinates' => array(
	 *                                  'lat' =>  -12,     //int
	 *                                  'lng' => 10        //int
	 *                                  )
	 *                             )
	 *                       )
	 *                   )
	 */
	public function render_custom($data, $extra = array()) {
		$atts = array(
			'map_height'    => fw_akg('map_height', $extra, false),
			'map_type'      => fw_akg('map_type', $extra, 'roadmap'),
			'data_provider' => array(
				'population_method' => 'custom',
				'custom' => array(
					'locations' => $data
				)
			)
		);
		return $this->_render($atts);
	}
}