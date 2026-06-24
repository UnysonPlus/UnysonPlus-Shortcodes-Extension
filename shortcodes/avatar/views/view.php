<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * @var array $atts
 *
 * Avatar — single avatar or an overlapping group with a "+N" counter.
 * Image (server-side cropped, retina-ready) OR initials fallback, status dot,
 * shapes / sizes, and five CSS design treatments.
 */

if ( ! function_exists( 'sc_get' ) ) {
	function sc_get( $path, $atts, $default = '' ) {
		if ( function_exists( 'fw_akg' ) ) {
			$v = fw_akg( $path, $atts, null );
			if ( $v !== null ) { return $v; }
		}
		return $default;
	}
}

/* Derive 1–2 uppercase initials from a name (or an explicit override). */
if ( ! function_exists( 'sc_avatar_initials' ) ) {
	function sc_avatar_initials( $name, $override = '' ) {
		$override = trim( (string) $override );
		if ( $override !== '' ) {
			return mb_strtoupper( mb_substr( $override, 0, 2 ) );
		}
		$name = trim( (string) $name );
		if ( $name === '' ) { return '?'; }
		$parts = preg_split( '/\s+/', $name );
		if ( count( $parts ) >= 2 ) {
			$last = $parts[ count( $parts ) - 1 ];
			return mb_strtoupper( mb_substr( $parts[0], 0, 1 ) . mb_substr( $last, 0, 1 ) );
		}
		return mb_strtoupper( mb_substr( $name, 0, 2 ) );
	}
}

/* Stable per-name color pair for the Auto initials background. Same name →
   same color on every render (crc32 of the name indexes a fixed palette). */
if ( ! function_exists( 'sc_avatar_auto_color' ) ) {
	function sc_avatar_auto_color( $seed ) {
		$palette = array(
			array( '#4a90d9', '#fff' ), array( '#7c4dff', '#fff' ), array( '#00b295', '#fff' ),
			array( '#e8590c', '#fff' ), array( '#d6336c', '#fff' ), array( '#1098ad', '#fff' ),
			array( '#5c7cfa', '#fff' ), array( '#f08c00', '#fff' ), array( '#37b24d', '#fff' ),
			array( '#c2255c', '#fff' ), array( '#9c36b5', '#fff' ), array( '#2b8a3e', '#fff' ),
		);
		$seed = trim( (string) $seed );
		$idx  = $seed === '' ? 0 : ( crc32( mb_strtolower( $seed ) ) % count( $palette ) );
		return $palette[ $idx ];
	}
}

/* Resolve a compact color-picker value to a concrete CSS color string. Custom
   hex wins; otherwise a known preset slug (bg-primary, text-danger, …) maps to
   its hex so the Style tab works whether the user picks a preset or a hex. */
if ( ! function_exists( 'sc_avatar_css_color' ) ) {
	function sc_avatar_css_color( $raw ) {
		static $preset = array(
			'primary' => '#0d6efd', 'secondary' => '#6c757d', 'success' => '#198754',
			'danger'  => '#dc3545', 'warning'   => '#ffc107', 'info'    => '#0dcaf0',
			'light'   => '#f8f9fa', 'dark'      => '#212529', 'white'   => '#ffffff',
			'black'   => '#000000', 'muted'     => '#6c757d', 'body'    => '#212529',
		);
		if ( ! is_array( $raw ) ) { return ''; }
		$custom = isset( $raw['custom'] ) ? trim( (string) $raw['custom'] ) : '';
		if ( $custom !== '' ) {
			return preg_replace( '/[^#0-9a-zA-Z(),.%\s-]/', '', $custom );
		}
		$pre = isset( $raw['predefined'] ) ? trim( (string) $raw['predefined'] ) : '';
		if ( $pre !== '' ) {
			$slug = preg_replace( '/^(bg|text)-/', '', $pre );
			if ( isset( $preset[ $slug ] ) ) { return $preset[ $slug ]; }
		}
		return '';
	}
}

/* Build one .fw-avatar face (image or initials, + optional status dot, + link). */
if ( ! function_exists( 'sc_avatar_face' ) ) {
	function sc_avatar_face( $person, $args ) {
		$size        = (int) $args['size'];
		$show_status = ! empty( $args['show_status'] );
		$init_mode   = $args['initials_color_mode']; // auto|theme
		$z           = isset( $args['z'] ) ? (int) $args['z'] : 0;

		$name     = isset( $person['name'] ) ? trim( (string) $person['name'] ) : '';
		$status   = isset( $person['status'] ) ? (string) $person['status'] : '';
		$link     = isset( $person['link'] ) ? trim( (string) $person['link'] ) : '';
		$target   = ( isset( $person['target'] ) && $person['target'] === '_blank' ) ? '_blank' : '_self';

		// Image source: library attachments are cropped to a sharp 2× square
		// (retina) via WP's image editor + on-disk cache; an external/hotlinked
		// URL is used as-is (fw_resize can only resize media-library files).
		$img_url  = '';
		$image    = isset( $person['image'] ) ? $person['image'] : '';
		$orig_url = ( is_array( $image ) && ! empty( $image['url'] ) ) ? (string) $image['url'] : '';
		if ( is_array( $image ) ) {
			if ( ! empty( $image['attachment_id'] ) && function_exists( 'fw_resize' ) ) {
				$target_px = min( $size * 2, 512 );
				$resized   = fw_resize( (int) $image['attachment_id'], $target_px, $target_px, true );
				// fw_resize returns its first argument (the attachment id) unchanged
				// when the crop fails — guard against that yielding a non-URL src.
				$img_url = ( is_string( $resized ) && strpos( $resized, '/' ) !== false ) ? $resized : $orig_url;
			} else {
				$img_url = $orig_url;
			}
		}

		// Inner: <img> or initials.
		if ( $img_url !== '' ) {
			$inner = '<img class="fw-avatar__img" src="' . esc_url( $img_url ) . '" alt="' . esc_attr( $name ) . '"'
				. ' width="' . $size . '" height="' . $size . '" loading="lazy" decoding="async" />';
			$face_extra_style = '';
		} else {
			$initials = sc_avatar_initials( $name, isset( $person['initials'] ) ? $person['initials'] : '' );
			$face_extra_style = '';
			if ( $init_mode === 'auto' ) {
				list( $bg, $fg ) = sc_avatar_auto_color( $name !== '' ? $name : $initials );
				$face_extra_style = '--av-initials-bg:' . $bg . ';--av-initials-fg:' . $fg . ';';
			}
			$inner = '<span class="fw-avatar__initials" aria-hidden="true">' . esc_html( $initials ) . '</span>';
		}

		// Status dot.
		$dot = '';
		if ( $show_status && $status !== '' && $status !== 'none' ) {
			$dot = '<span class="fw-avatar__status fw-avatar__status--' . esc_attr( $status ) . '"></span>';
		}

		$style_attr = $face_extra_style !== '' || $z ? ' style="' . esc_attr( $face_extra_style . ( $z ? 'z-index:' . $z . ';' : '' ) ) . '"' : '';
		$title_attr = $name !== '' ? ' title="' . esc_attr( $name ) . '"' : '';
		$aria       = $img_url === '' && $name !== '' ? ' role="img" aria-label="' . esc_attr( $name ) . '"' : '';

		$face = '<span class="fw-avatar"' . $style_attr . $title_attr . $aria . '>' . $inner . $dot . '</span>';

		if ( $link !== '' ) {
			$rel  = $target === '_blank' ? ' rel="noopener noreferrer"' : '';
			$face = '<a class="fw-avatar-link" href="' . esc_url( $link ) . '" target="' . esc_attr( $target ) . '"' . $rel . $title_attr . '>' . $face . '</a>';
		}
		return $face;
	}
}

/* ---------------------------------------------------------------------------
 * Resolve options.
 * ------------------------------------------------------------------------- */
$av_registry = require __DIR__ . '/parts/registry.php';

$mode   = sc_get( 'mode_settings/mode', $atts, 'single' );
$mode   = ( $mode === 'group' ) ? 'group' : 'single';

$design = sc_get( 'design', $atts, 'plain' );
if ( ! is_string( $design ) || ! isset( $av_registry[ $design ] ) ) { $design = 'plain'; }

$shape = sc_get( 'shape', $atts, 'circle' );
if ( ! in_array( $shape, array( 'circle', 'rounded', 'square' ), true ) ) { $shape = 'circle'; }

$size = (int) sc_get( 'size', $atts, 56 );
if ( $size < 16 ) { $size = 16; }
if ( $size > 400 ) { $size = 400; }

$show_status = sc_get( 'show_status', $atts, 'yes' ) === 'yes';
$show_label  = sc_get( 'show_label', $atts, 'no' ) === 'yes';
$init_mode   = sc_get( 'initials_color_mode', $atts, 'auto' ) === 'theme' ? 'theme' : 'auto';

/* Style-tab colors → CSS custom properties on the root (so border/ring/dot/
   counter all read them). Empty values simply fall back to the CSS defaults. */
$root_vars  = '--av-size:' . $size . 'px;';
$ring_c     = sc_avatar_css_color( sc_get( 'ring_color', $atts, '' ) );
$init_bg    = sc_avatar_css_color( sc_get( 'initials_bg', $atts, '' ) );
$init_fg    = sc_avatar_css_color( sc_get( 'initials_color', $atts, '' ) );
$label_c    = sc_avatar_css_color( sc_get( 'label_color', $atts, '' ) );
$counter_bg = sc_avatar_css_color( sc_get( 'counter_bg', $atts, '' ) );
$counter_fg = sc_avatar_css_color( sc_get( 'counter_color', $atts, '' ) );
if ( $ring_c )     { $root_vars .= '--av-ring:' . $ring_c . ';'; }
if ( $init_mode === 'theme' && $init_bg ) { $root_vars .= '--av-initials-bg:' . $init_bg . ';'; }
if ( $init_fg )    { $root_vars .= '--av-initials-fg:' . $init_fg . ';'; }
if ( $label_c )    { $root_vars .= '--av-label:' . $label_c . ';'; }
if ( $counter_bg ) { $root_vars .= '--av-counter-bg:' . $counter_bg . ';'; }
if ( $counter_fg ) { $root_vars .= '--av-counter-fg:' . $counter_fg . ';'; }

/* Font-size preset (label / initials / counter scale with text). */
$font_size_styling = sc_extract_styling_atts( $atts, array( 'font_size_preset' ) );
$font_class_extra  = implode( ' ', $font_size_styling['classes'] );

/* ---------------------------------------------------------------------------
 * Wrapper. base_class 'fw-avatar-sc' (avoids theme `.avatar` collisions); the
 * Styling tab (bg/spacing), Animations and Advanced fold into $attr here.
 * ------------------------------------------------------------------------- */
$atts['base_class']       = 'fw-avatar-sc';
$atts['unique_id_prefix'] = 'av-';
$attr = sc_build_wrapper_attr( $atts );

$attr['class'] = trim(
	( isset( $attr['class'] ) ? $attr['class'] : '' )
	. ' fw-avatar--' . $design
	. ' fw-avatar--shape-' . $shape
	. ' fw-avatar--mode-' . $mode
	. ( $font_class_extra ? ' ' . $font_class_extra : '' )
);
$attr['style'] = ( isset( $attr['style'] ) ? rtrim( $attr['style'], ';' ) . ';' : '' ) . $root_vars;

$base_args = array(
	'size'                => $size,
	'show_status'         => $show_status,
	'initials_color_mode' => $init_mode,
);

ob_start();

if ( $mode === 'group' ) {
	$people = sc_get( 'mode_settings/group/people', $atts, array() );
	if ( ! is_array( $people ) ) { $people = array(); }
	// Drop fully-empty rows.
	$people = array_values( array_filter( $people, function ( $p ) {
		return is_array( $p ) && ( ! empty( $p['name'] ) || ! empty( $p['image'] ) || ! empty( $p['initials'] ) );
	} ) );

	$max         = (int) sc_get( 'mode_settings/group/max_visible', $atts, 5 );
	$extra_count = trim( (string) sc_get( 'mode_settings/group/extra_count', $atts, '' ) );
	$overlap     = (int) sc_get( 'mode_settings/group/overlap', $atts, 35 );
	$overlap     = max( 0, min( 80, $overlap ) ) / 100;
	$order       = sc_get( 'mode_settings/group/stack_order', $atts, 'first-on-top' ) === 'last-on-top' ? 'last-on-top' : 'first-on-top';

	$visible = ( $max > 0 && $max < count( $people ) ) ? array_slice( $people, 0, $max ) : $people;
	$hidden  = count( $people ) - count( $visible );

	$counter_text = '';
	if ( $extra_count !== '' ) {
		$counter_text = is_numeric( $extra_count ) ? '+' . $extra_count : $extra_count;
	} elseif ( $hidden > 0 ) {
		$counter_text = '+' . $hidden;
	}

	$total = count( $visible ) + ( $counter_text !== '' ? 1 : 0 );

	$group_style = '--av-overlap:' . $overlap . ';';
	if ( empty( $people ) ) {
		if ( is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			echo '<div class="fw-avatar-empty">' . esc_html__( 'Add at least one person to the avatar group.', 'fw' ) . '</div>';
		}
	} else {
		echo '<div class="fw-avatar-group fw-avatar-group--' . esc_attr( $order ) . '" style="' . esc_attr( $group_style ) . '">';
		foreach ( $visible as $i => $person ) {
			$z = ( $order === 'last-on-top' ) ? ( $i + 1 ) : ( $total - $i );
			echo sc_avatar_face( $person, array_merge( $base_args, array( 'z' => $z ) ) );
		}
		if ( $counter_text !== '' ) {
			$cz = ( $order === 'last-on-top' ) ? ( $total + 1 ) : 0;
			echo '<span class="fw-avatar fw-avatar__more" style="z-index:' . (int) $cz . ';">'
				. '<span class="fw-avatar__more-text">' . esc_html( $counter_text ) . '</span></span>';
		}
		echo '</div>';
	}
} else {
	// SINGLE
	$single = sc_get( 'mode_settings/single', $atts, array() );
	if ( ! is_array( $single ) ) { $single = array(); }

	$face = sc_avatar_face( $single, $base_args );

	$name     = isset( $single['name'] ) ? trim( (string) $single['name'] ) : '';
	$subtitle = isset( $single['subtitle'] ) ? trim( (string) $single['subtitle'] ) : '';
	$link     = isset( $single['link'] ) ? trim( (string) $single['link'] ) : '';
	$target   = ( isset( $single['target'] ) && $single['target'] === '_blank' ) ? '_blank' : '_self';

	if ( $show_label && ( $name !== '' || $subtitle !== '' ) ) {
		// The face already carries its own link; for the chip we want the whole
		// thing clickable, so rebuild the face WITHOUT its link and wrap the chip.
		$face_nolink = sc_avatar_face( array_merge( $single, array( 'link' => '' ) ), $base_args );
		$text  = '<span class="fw-avatar-chip__text">';
		if ( $name !== '' )     { $text .= '<span class="fw-avatar-chip__name">' . esc_html( $name ) . '</span>'; }
		if ( $subtitle !== '' ) { $text .= '<span class="fw-avatar-chip__sub">' . esc_html( $subtitle ) . '</span>'; }
		$text .= '</span>';
		$chip_inner = $face_nolink . $text;

		if ( $link !== '' ) {
			$rel = $target === '_blank' ? ' rel="noopener noreferrer"' : '';
			echo '<a class="fw-avatar-chip" href="' . esc_url( $link ) . '" target="' . esc_attr( $target ) . '"' . $rel . '>' . $chip_inner . '</a>';
		} else {
			echo '<span class="fw-avatar-chip">' . $chip_inner . '</span>';
		}
	} else {
		echo $face;
	}
}

$inner_html = ob_get_clean();

if ( trim( $inner_html ) === '' ) {
	return;
}

echo '<div ' . fw_attr_to_html( $attr ) . '>' . $inner_html . '</div>';
