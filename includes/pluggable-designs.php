<?php if ( ! defined( 'FW' ) ) { die( 'Forbidden' ); }

/**
 * Pluggable Designs — framework layer.
 *
 * Generalizes the per-shortcode "thin dispatcher" design pattern (reference:
 * testimonials — views/designs/registry.php + view.php `include designs/<key>.php`
 * + static.php per-design enqueue) so that a shortcode's design list is the UNION
 * of its built-in designs AND user-installed **design packs** (PHP view partial +
 * options + assets + icon) living under the uploads catalog.
 *
 * A design-capable shortcode calls fw_sc_designs($tag) in its three seams instead
 * of `require .../views/designs/registry.php`:
 *   - options.php → fw_sc_design_picker_choices($tag)   (image-picker choices)
 *   - view.php    → fw_sc_design_partial($tag,$key)     (path to include)
 *   - static.php  → fw_sc_design_enqueue($tag,$key)     (origin-aware CSS/JS)
 *
 * Each design's meta carries `origin` (plugin|uploads) + resolved absolute URLs so
 * the picker/enqueue/dispatch treat built-in and installed designs identically.
 *
 * Design packs are admin-installed code (same trust tier as the shortcode ZIP
 * installer); they live at fw_design_lib_dir($tag)/<key>/ (a folder with
 * manifest.json {type:'design'} + view.php [+ options.php] [+ static/] [+ img/icon.svg]).
 * That same `designs/` dir also holds Tier-A JSON *presets* (folders with
 * design.json) — the two scanners are content-gated and ignore each other.
 */

if ( ! function_exists( 'fw_sc_designs' ) ) :
	/**
	 * The merged design registry for a shortcode: built-in + installed packs.
	 *
	 * @param string $tag shortcode tag (e.g. 'testimonials').
	 * @param bool   $include_disabled When true, also return designs an admin has
	 *               disabled (each carries an `enabled` flag) — used by the manager
	 *               UI. Default false hides disabled designs (picker/dispatch/enqueue).
	 * @return array key => meta{ key, label, origin, enabled, deletable, lockable,
	 *               version, author, thumb_uri, css_uri, js_uri, partial(path),
	 *               options(path|null), vendor_deps[], base_uri, base_path }.
	 */
	function fw_sc_designs( $tag, $include_disabled = false ) {
		$tag = sanitize_key( $tag );
		$out = array();
		$ext = function_exists( 'fw_ext' ) ? fw_ext( 'shortcodes' ) : null;
		if ( ! $ext ) { return $out; }

		// 1) Built-in registry shipped with the shortcode. Two shapes are supported:
		//    - LAYOUT dispatcher  → views/designs/registry.php  (each design is a
		//      swappable render partial views/designs/<key>.php; render='partial').
		//    - SKIN registry      → views/parts/registry.php    (each design is a CSS
		//      skin applied via a class; no partial; render='skin'). Built-in skin CSS
		//      is enqueued by the shortcode's own static.php, so css_uri stays ''.
		// The shortcode TAG uses underscores; its FOLDER may use hyphens (tag
		// 'pricing_table' → folder 'pricing-table'). Resolve the real folder.
		$folder = $tag;
		if ( ! is_dir( $ext->get_declared_path( '/shortcodes/' . $folder ) ) ) {
			$alt = str_replace( '_', '-', $tag );
			if ( is_dir( $ext->get_declared_path( '/shortcodes/' . $alt ) ) ) { $folder = $alt; }
		}
		$base_uri  = $ext->get_declared_URI( '/shortcodes/' . $folder );
		$base_path = $ext->get_declared_path( '/shortcodes/' . $folder );

		$layout_path = $base_path . '/views/designs/registry.php';
		$skin_path   = $base_path . '/views/parts/registry.php';
		$reg_path    = is_readable( $layout_path ) ? $layout_path : ( is_readable( $skin_path ) ? $skin_path : '' );
		$shape       = ( $reg_path === $layout_path ) ? 'layout' : 'skin';

		if ( $reg_path !== '' ) {
			$reg = require $reg_path;
			if ( is_array( $reg ) ) {
				foreach ( $reg as $key => $meta ) {
					if ( ! is_string( $key ) || $key === '' ) { continue; }
					$meta    = is_array( $meta ) ? $meta : array();
					// 'default' can never be disabled — it is the render fallback.
					$enabled = ( $key === 'default' ) ? true : fw_sc_design_pack_enabled( $tag, $key );
					if ( ! $enabled && ! $include_disabled ) { continue; }
					$thumb = isset( $meta['thumb'] ) ? (string) $meta['thumb'] : '';
					$css   = ! empty( $meta['css'] ) ? (string) $meta['css'] : '';
					$js    = ! empty( $meta['js'] )  ? (string) $meta['js']  : '';
					// Thumbnail dir varies by shortcode (designs/ vs design/) — probe both.
					$thumb_uri = '';
					if ( $thumb !== '' ) {
						foreach ( array( '/static/img/designs/', '/static/img/design/' ) as $td ) {
							if ( is_readable( $base_path . $td . $thumb ) ) { $thumb_uri = $base_uri . $td . $thumb; break; }
						}
						if ( $thumb_uri === '' ) { $thumb_uri = $base_uri . '/static/img/designs/' . $thumb; }
					}
					$out[ $key ] = array(
						'key'         => $key,
						'label'       => isset( $meta['label'] ) ? (string) $meta['label'] : ucwords( str_replace( array( '-', '_' ), ' ', $key ) ),
						'origin'      => 'plugin',
						'render'      => $shape, // 'layout' | 'skin'
						'enabled'     => (bool) $enabled,
						'deletable'   => false,
						'lockable'    => ( $key !== 'default' ),
						'version'     => '',
						'author'      => '',
						'thumb_uri'   => $thumb_uri,
						'css_uri'     => ( $shape === 'layout' && $css !== '' ) ? $base_uri . '/static/css/designs/' . $css : '',
						'js_uri'      => ( $shape === 'layout' && $js !== '' )  ? $base_uri . '/static/js/designs/' . $js  : '',
						'partial'     => $shape === 'layout' ? $base_path . '/views/designs/' . $key . '.php' : '',
						'options'     => null,
						'vendor_deps' => isset( $meta['splide'] ) && $meta['splide'] ? array( 'splide' ) : array(),
						'base_uri'    => $base_uri,
						'base_path'   => $base_path,
					);
				}
			}
		}

		// 2) Installed design PACKS under uploads. A pack has a manifest.json
		//    {type:'design'} and is either a LAYOUT pack (ships view.php → render a
		//    partial) or a SKIN pack (CSS only, applied via the design-<key> class →
		//    render='skin'). The manifest may declare `render`; otherwise it's inferred
		//    from the presence of view.php.
		if ( function_exists( 'fw_design_lib_dir' ) ) {
			$root     = fw_design_lib_dir( $tag );
			$url_root = function_exists( 'fw_design_lib_url' ) ? fw_design_lib_url( $tag ) : '';
			if ( is_dir( $root ) ) {
				foreach ( (array) glob( trailingslashit( $root ) . '*', GLOB_ONLYDIR ) as $dir ) {
					$manifest = @json_decode( @file_get_contents( $dir . '/manifest.json' ), true );
					if ( ! is_array( $manifest ) ) { continue; }                       // not a pack (e.g. a JSON preset folder)
					if ( ( isset( $manifest['type'] ) ? $manifest['type'] : '' ) !== 'design' ) { continue; }
					$has_view  = is_readable( $dir . '/view.php' );
					$has_css   = is_readable( $dir . '/static/css/styles.css' );
					$render    = isset( $manifest['render'] ) && in_array( $manifest['render'], array( 'layout', 'skin' ), true )
						? $manifest['render']
						: ( $has_view ? 'layout' : 'skin' );
					// A layout pack must render (view.php); a skin pack must style (styles.css).
					if ( $render === 'layout' && ! $has_view ) { continue; }
					if ( $render === 'skin' && ! $has_css ) { continue; }
					$key = sanitize_key( isset( $manifest['design_key'] ) ? $manifest['design_key'] : basename( $dir ) );
					if ( $key === '' ) { continue; }
					$enabled = fw_sc_design_pack_enabled( $tag, $key );
						if ( ! $enabled && ! $include_disabled ) { continue; }

					$b = trailingslashit( $url_root ) . basename( $dir );             // pack's public base URL
					$out[ $key ] = array(
						'key'         => $key,
						'label'       => isset( $manifest['name'] ) && $manifest['name'] !== '' ? (string) $manifest['name'] : ucwords( str_replace( array( '-', '_' ), ' ', $key ) ),
						'origin'      => 'uploads',
						'render'      => $render, // 'layout' | 'skin'
						'enabled'     => (bool) $enabled,
						'deletable'   => true,
						'lockable'    => true,
						'version'     => isset( $manifest['version'] ) ? (string) $manifest['version'] : '',
						'author'      => isset( $manifest['author'] ) ? (string) $manifest['author'] : '',
						'thumb_uri'   => is_readable( $dir . '/img/icon.svg' ) ? $b . '/img/icon.svg' : '',
						'css_uri'     => $has_css ? $b . '/static/css/styles.css' : '',
						'js_uri'      => is_readable( $dir . '/static/js/script.js' )   ? $b . '/static/js/script.js'   : '',
						'partial'     => $has_view ? $dir . '/view.php' : '',
						'options'     => is_readable( $dir . '/options.php' ) ? $dir . '/options.php' : null,
						'vendor_deps' => ( isset( $manifest['vendor_deps'] ) && is_array( $manifest['vendor_deps'] ) ) ? array_map( 'sanitize_key', $manifest['vendor_deps'] ) : array(),
						'base_uri'    => $b,
						'base_path'   => $dir,
						'manifest'    => $manifest,
					);
				}
			}
		}

		return apply_filters( 'fw_sc_designs', $out, $tag );
	}
endif;

if ( ! function_exists( 'fw_sc_design_pack_enabled' ) ) :
	/**
	 * Whether an installed design pack is enabled. Default true; a disabled map is
	 * stored per shortcode (mirrors icon-v3's per-pack toggle). Filterable.
	 */
	function fw_sc_design_pack_enabled( $tag, $key ) {
		$map = get_option( 'fw_sc_design_packs_disabled', array() );
		$disabled = ( is_array( $map ) && isset( $map[ $tag ] ) && is_array( $map[ $tag ] ) ) ? $map[ $tag ] : array();
		$enabled  = ! in_array( $key, $disabled, true );
		return (bool) apply_filters( 'fw_sc_design_pack_enabled', $enabled, $tag, $key );
	}
endif;

if ( ! function_exists( 'fw_sc_design_resolve' ) ) :
	/**
	 * Resolve the selected design key from an element's atts, whitelisted to the
	 * merged registry, falling back to 'default'. Mirrors the testimonials view:
	 * new multi-picker path `design_settings/design` → legacy scalar `design` → 'default'.
	 */
	function fw_sc_design_resolve( $tag, $atts, $default = 'default' ) {
		$designs = fw_sc_designs( $tag );
		$key = function_exists( 'fw_akg' ) ? fw_akg( 'design_settings/design', $atts, null ) : null;
		if ( ! is_string( $key ) || $key === '' ) {
			$key = ( isset( $atts['design'] ) && is_string( $atts['design'] ) ) ? $atts['design'] : $default;
		}
		if ( ! isset( $designs[ $key ] ) ) { $key = isset( $designs[ $default ] ) ? $default : ( $designs ? key( $designs ) : $default ); }
		return $key;
	}
endif;

if ( ! function_exists( 'fw_sc_design_picker_choices' ) ) :
	/**
	 * image-picker `choices` for a shortcode's Design option (built-in + packs).
	 * Each: key => [ 'small' => ['src'=>thumb, 'alt'=>label, 'height'=>60], 'label'=>label ].
	 */
	function fw_sc_design_picker_choices( $tag ) {
		$choices = array();
		foreach ( fw_sc_designs( $tag ) as $key => $d ) {
			$choices[ $key ] = array(
				// 'title' drives the image-picker's native hover tooltip (= design name).
				'small' => array( 'src' => $d['thumb_uri'], 'alt' => $d['label'], 'title' => $d['label'], 'height' => 60 ),
				'label' => $d['label'],
			);
		}
		return $choices;
	}
endif;

if ( ! function_exists( 'fw_sc_design_partial' ) ) :
	/**
	 * Absolute path of the design's render partial (built-in or pack), or '' if the
	 * key is unknown / has no partial. Callers keep their own file_exists guard.
	 */
	function fw_sc_design_partial( $tag, $key ) {
		$designs = fw_sc_designs( $tag );
		if ( isset( $designs[ $key ]['partial'] ) && is_readable( $designs[ $key ]['partial'] ) ) {
			return $designs[ $key ]['partial'];
		}
		return '';
	}
endif;

if ( ! function_exists( 'fw_sc_design_enqueue' ) ) :
	/**
	 * Enqueue a design's CSS/JS (origin-agnostic), with the shortcode's base handle
	 * as a dependency + any declared vendor deps (e.g. 'splide'). Safe to call for
	 * built-in designs too. Call from the `fw_ext_shortcodes_enqueue_static:<tag>`
	 * handler after resolving the active design.
	 */
	function fw_sc_design_enqueue( $tag, $key ) {
		$designs = fw_sc_designs( $tag );
		if ( ! isset( $designs[ $key ] ) ) { return; }
		$d       = $designs[ $key ];
		$ext     = fw_ext( 'shortcodes' );
		$version = $ext ? $ext->manifest->get_version() : null;
		$base    = 'fw-shortcode-' . str_replace( '_', '-', $tag ); // matches static.php base handle
		$deps    = array_merge( array_values( (array) $d['vendor_deps'] ), array( $base ) );
		$handle  = $base . '-' . $key;

		if ( ! empty( $d['css_uri'] ) ) {
			wp_enqueue_style( $handle, $d['css_uri'], array( $base ), $version );
		}
		if ( ! empty( $d['js_uri'] ) ) {
			wp_enqueue_script( $handle, $d['js_uri'], $deps, $version, true );
		}
	}
endif;

if ( ! function_exists( 'fw_sc_design_pack_option_fragments' ) ) :
	/**
	 * Collect installed design PACKS' option fragments for a shortcode:
	 *   key => options-array (the pack's options.php returns $options).
	 * The host shortcode merges these into its `design_settings` multi-picker
	 * `choices[<key>]` so a pack's controls appear (design-scoped) when selected.
	 */
	function fw_sc_design_pack_option_fragments( $tag ) {
		$frags = array();
		foreach ( fw_sc_designs( $tag ) as $key => $d ) {
			if ( ( isset( $d['origin'] ) ? $d['origin'] : '' ) !== 'uploads' ) { continue; }
			if ( empty( $d['options'] ) || ! is_readable( $d['options'] ) ) { continue; }
			$options = null;
			// The fragment file returns (or sets) $options — support both styles.
			$ret = include $d['options'];
			if ( is_array( $ret ) ) { $options = $ret; }
			elseif ( isset( $options ) && is_array( $options ) ) { /* set by the file */ }
			if ( is_array( $options ) && $options ) { $frags[ $key ] = $options; }
		}
		return $frags;
	}
endif;

/* ---------------------------------------------------------------------------
 * Manager helpers — power the "Design packs" tab on the Shortcodes page.
 * ------------------------------------------------------------------------- */

if ( ! function_exists( 'fw_sc_designs_manage' ) ) :
	/**
	 * Every design for a shortcode INCLUDING disabled ones (each with an `enabled`
	 * flag), for the management UI. Thin wrapper over fw_sc_designs( $tag, true ).
	 */
	function fw_sc_designs_manage( $tag ) {
		return fw_sc_designs( $tag, true );
	}
endif;

if ( ! function_exists( 'fw_sc_design_pack_count' ) ) :
	/** Number of INSTALLED design packs (origin=uploads) for a shortcode. */
	function fw_sc_design_pack_count( $tag ) {
		$n = 0;
		foreach ( fw_sc_designs( $tag, true ) as $d ) {
			if ( ( isset( $d['origin'] ) ? $d['origin'] : '' ) === 'uploads' ) { $n++; }
		}
		return $n;
	}
endif;

if ( ! function_exists( 'fw_sc_design_capable_tags' ) ) :
	/**
	 * Discovered shortcodes that have designs to manage — a built-in
	 * views/designs/registry.php OR one or more installed design packs.
	 *
	 * @return array tag => title (sorted by title).
	 */
	function fw_sc_design_capable_tags() {
		$ext = function_exists( 'fw_ext' ) ? fw_ext( 'shortcodes' ) : null;
		if ( ! $ext ) { return array(); }
		$out = array();
		foreach ( $ext->discover_all_shortcodes() as $tag => $meta ) {
			// Use the real folder (may be hyphenated) from discover meta.
			$folder       = ( isset( $meta['dir'] ) && $meta['dir'] !== '' ) ? $meta['dir'] : $tag;
			$base         = $ext->get_declared_path( '/shortcodes/' . $folder );
			$has_registry = is_readable( $base . '/views/designs/registry.php' )  // layout dispatcher
				|| is_readable( $base . '/views/parts/registry.php' );            // CSS-skin registry
			$has_packs    = false;
			if ( ! $has_registry && function_exists( 'fw_design_lib_dir' ) ) {
				$root      = fw_design_lib_dir( $tag );
				$has_packs = is_dir( $root ) && (bool) glob( trailingslashit( $root ) . '*', GLOB_ONLYDIR );
			}
			if ( $has_registry || $has_packs ) {
				$out[ $tag ] = isset( $meta['title'] ) && $meta['title'] !== '' ? $meta['title'] : ucwords( str_replace( array( '-', '_' ), ' ', $tag ) );
			}
		}
		asort( $out, SORT_NATURAL | SORT_FLAG_CASE );
		return $out;
	}
endif;

if ( ! function_exists( 'fw_sc_design_set_enabled' ) ) :
	/**
	 * Enable/disable a design (built-in or pack) for a shortcode by updating the
	 * shared `fw_sc_design_packs_disabled` option map (tag => [disabled keys]).
	 * 'default' can never be disabled. Returns true on success.
	 */
	function fw_sc_design_set_enabled( $tag, $key, $enabled ) {
		$tag = sanitize_key( $tag );
		$key = sanitize_key( $key );
		if ( $tag === '' || $key === '' ) { return false; }
		if ( $key === 'default' && ! $enabled ) { return false; } // never disable the fallback

		$map = get_option( 'fw_sc_design_packs_disabled', array() );
		if ( ! is_array( $map ) ) { $map = array(); }
		$disabled = ( isset( $map[ $tag ] ) && is_array( $map[ $tag ] ) ) ? $map[ $tag ] : array();

		if ( $enabled ) {
			$disabled = array_values( array_diff( $disabled, array( $key ) ) );
		} elseif ( ! in_array( $key, $disabled, true ) ) {
			$disabled[] = $key;
		}

		if ( $disabled ) { $map[ $tag ] = array_values( $disabled ); } else { unset( $map[ $tag ] ); }
		update_option( 'fw_sc_design_packs_disabled', $map, false );
		return true;
	}
endif;
