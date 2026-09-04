<?php if (!defined('FW')) die('Forbidden');

/**
 * Decodes a shortcode's encoded attributes using the first matching registered attribute coder.
 *
 * @param array $attributes Encoded attributes
 * @param $shortcode_tag 'button', 'section', etc.
 * @param $post_id
 * @return array|WP_Error
 * @since 1.3.0
 */
/** Decodes a shortcode's encoded attributes using the first matching registered attribute coder. */
function fw_ext_shortcodes_decode_attr(array $attributes, $shortcode_tag, $post_id) {
	/**
	 * @var FW_Extension_Shortcodes $shortcodes_ext
	 */
	$shortcodes_ext = fw_ext('shortcodes');

	foreach ($shortcodes_ext->get_attr_coder() as $coder) {
		if ($coder->can_decode($attributes, $shortcode_tag, $post_id)) {
			return $coder->decode($attributes, $shortcode_tag, $post_id);
		}
	}

	return $attributes;
}

/**
 * Parse string, extract shortcodes and enqueue their static files
 * @param string $content 'Hello [shortcode1 attr1="..."] World'
 * @since 1.3.17
 */
function fw_ext_shortcodes_enqueue_shortcodes_static($content) {
	/**
	 * @var FW_Extension_Shortcodes $shortcodes_ext
	 */
	$shortcodes_ext = fw_ext('shortcodes');

	$shortcodes_ext->enqueue_shortcodes_static($content);
}

/**
 * Enqueue admin scripts for each shortcode
 * @since 1.3.18
 */
function fw_ext_shortcodes_enqueue_shortcodes_admin_scripts() {
	static $has_run = false;

	if ($has_run) {
		return;
	}

	$has_run = true;

	/**
	 * @var FW_Extension_Shortcodes $shortcodes_ext
	 */
	$shortcodes_ext = fw_ext('shortcodes');

	foreach ($shortcodes_ext->get_shortcodes() as $shortcode) {
		fw()->backend->enqueue_options_static($shortcode->get_options());
	}

	/** Fires after every shortcode's option statics are enqueued in admin, so extensions can enqueue their own admin scripts. */
	do_action('fw:ext:shortcodes:enqueue-shortcodes-admin-scripts');
}

/**
 * Returns the pool of distinct shortcode-tag aliases used for nested flexbox containers.
 *
 * Pool of distinct shortcode-tag aliases for NESTED flexbox containers.
 *
 * WordPress' shortcode parser is non-recursive PER TAG: a [flexbox] inside a
 * [flexbox] (or the same alias inside itself) mis-pairs — the outer open binds
 * to the first inner close — self-closing the inner box and leaking the trailing
 * close tag as literal text. A single alias only fixes ONE nested level; deeper
 * trees re-collide. Cycling through this pool by nesting depth guarantees no
 * ancestor chain ever repeats a tag (good for trees up to count(pool)+1 levels;
 * the cycle then repeats, but only between NON-adjacent, non-self-nesting levels,
 * which the parser tolerates). All aliases render through the one flexbox
 * instance (FW_Shortcode::render keys off $this, not the passed $tag).
 *
 * @return string[]
 * @since 2.10.x
 */
function fw_flexbox_inner_alias_pool() {
	// One DISTINCT alias per nesting level. WP's shortcode parser is non-recursive
	// per tag, so within a single ancestor chain EVERY level must use a different
	// tag or the repeated open/close pairs mis-match and the trailing close tags
	// leak as literal text. fw_flexbox_alias_for_depth() cycles this pool by depth,
	// so the pool size is the true max safe nesting depth — beyond it the aliases
	// wrap and re-collide with an ancestor. Keep it comfortably above any realistic
	// design (16 = ~2x the deepest stress test); the editor caps authoring to match.
	// The builder cap FLEXBOX_NEST_MAX in the flexbox item's scripts.js MUST equal
	// count() of this pool — bump both together.
	return array(
		'fw_inner_flexbox',   // kept first for back-compat with existing content
		'fw_inner_flexbox2',
		'fw_inner_flexbox3',
		'fw_inner_flexbox4',
		'fw_inner_flexbox5',
		'fw_inner_flexbox6',
		'fw_inner_flexbox7',
		'fw_inner_flexbox8',
		'fw_inner_flexbox9',
		'fw_inner_flexbox10',
		'fw_inner_flexbox11',
		'fw_inner_flexbox12',
		'fw_inner_flexbox13',
		'fw_inner_flexbox14',
		'fw_inner_flexbox15',
		'fw_inner_flexbox16',
	);
}

/**
 * Pick the inner-flexbox alias for a given nesting depth (depth 1 = first flexbox
 * nested inside another flexbox). Cycles through fw_flexbox_inner_alias_pool().
 *
 * @param int $depth 1-based nesting depth (0 = top-level, never aliased).
 * @return string
 * @since 2.10.x
 */
function fw_flexbox_alias_for_depth( $depth ) {
	$pool = fw_flexbox_inner_alias_pool();
	$i    = ( (int) $depth - 1 ) % count( $pool );
	if ( $i < 0 ) {
		$i = 0;
	}
	return $pool[ $i ];
}

/**
 * The responsive breakpoint → class-infix map every structural shortcode iterates
 * to emit per-device utility classes: base = no infix, md = "-md", lg = "-lg".
 * Centralised so section / flexbox / column share ONE definition instead of each
 * re-declaring the literal array( 'base' => '', 'md' => '-md', 'lg' => '-lg' ).
 *
 * @return array{base:string,md:string,lg:string}
 * @since 3.0.x
 */
function fw_sc_bp_layers() {
	return array( 'base' => '', 'md' => '-md', 'lg' => '-lg' );
}

/**
 * Read a responsive { base, md, lg } option value from a shortcode's $atts,
 * tolerating a legacy flat scalar (which folds into `base`; md/lg blank). The
 * structural shortcodes (section / flexbox / column) all store their per-device
 * controls this way, so this is the single reader they share — replacing the
 * per-view `$fx_resp` closure and the repeated
 * `if ( ! is_array( $v ) ) { $v = array( 'base' => (string) $v ); }` blocks.
 *
 * Each layer is returned cast to string. A missing md/lg is '' (blank = inherit
 * the smaller device, which each caller resolves in its own cascade).
 *
 * @param array  $atts    Shortcode atts.
 * @param string $key     Option key to read.
 * @param string $default Value for `base` when nothing is set (md/lg stay '').
 * @return array{base:string,md:string,lg:string}
 * @since 3.0.x
 */
function fw_sc_resp_value( $atts, $key, $default = '' ) {
	$v = fw_akg( $key, $atts, null );
	if ( is_array( $v ) ) {
		return array(
			'base' => isset( $v['base'] ) ? (string) $v['base'] : $default,
			'md'   => isset( $v['md'] )   ? (string) $v['md']   : '',
			'lg'   => isset( $v['lg'] )   ? (string) $v['lg']   : '',
		);
	}
	return array(
		'base' => ( $v === null || $v === '' ) ? $default : (string) $v,
		'md'   => '',
		'lg'   => '',
	);
}
