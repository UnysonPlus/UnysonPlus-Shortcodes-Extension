<?php if (!defined('FW')) die('Forbidden');

/**
 * @param array $attributes Encoded attributes
 * @param $shortcode_tag 'button', 'section', etc.
 * @param $post_id
 * @return array|WP_Error
 * @since 1.3.0
 */
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

	do_action('fw:ext:shortcodes:enqueue-shortcodes-admin-scripts');
}

/**
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
	return array(
		'fw_inner_flexbox',   // kept first for back-compat with existing content
		'fw_inner_flexbox2',
		'fw_inner_flexbox3',
		'fw_inner_flexbox4',
		'fw_inner_flexbox5',
		'fw_inner_flexbox6',
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
