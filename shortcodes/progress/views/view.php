<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * @var array $atts
 */

if ( ! function_exists( 'fw_progress_color' ) ) {
	/** A preset/custom colour value → [ class, style-decls ] for a CSS property ('text' or 'bg'). */
	function fw_progress_color( $value, $kind = 'bg' ) {
		if ( is_string( $value ) && $value !== '' && preg_match( '/^(#|rgb)/i', trim( $value ) ) ) {
			$value = array( 'predefined' => '', 'custom' => trim( $value ) );
		}
		if ( function_exists( 'sc_normalize_color_value' ) ) {
			$c = sc_normalize_color_value( $value, $kind );
			return array( ! empty( $c['class'] ) ? $c['class'] : '', ! empty( $c['style'] ) ? $c['style'] . ';' : '' );
		}
		return array( '', '' );
	}
}
if ( ! function_exists( 'fw_progress_raw_color' ) ) {
	/**
	 * Resolve a compact/legacy colour value to a RAW css colour string
	 * (e.g. "#2563eb"). Needed for SVG strokes & gradients where a preset
	 * CSS class can't be used. Presets are mapped back to their hex via the
	 * live palette, so circular / gauge styles support presets too.
	 */
	function fw_progress_raw_color( $value ) {
		if ( is_string( $value ) ) {
			$value = ( $value !== '' && preg_match( '/^(#|rgb)/i', trim( $value ) ) )
				? array( 'predefined' => '', 'custom' => trim( $value ) )
				: array( 'predefined' => $value, 'custom' => '' );
		}
		if ( ! is_array( $value ) ) {
			return '';
		}
		$predefined = isset( $value['predefined'] ) ? trim( (string) $value['predefined'] ) : '';
		$custom     = isset( $value['custom'] )     ? trim( (string) $value['custom'] )     : '';
		if ( $custom !== '' ) {
			return preg_replace( '/[^A-Za-z0-9#(),.%\s]/', '', $custom );
		}
		if ( $predefined !== '' && function_exists( 'unysonplus_color_preset_slug_map' ) ) {
			$slug = preg_replace( '/^(bg|text)-/', '', $predefined );
			$map  = unysonplus_color_preset_slug_map();
			if ( isset( $map[ $slug ] ) ) {
				return $map[ $slug ];
			}
		}
		return '';
	}
}
if ( ! function_exists( 'fw_progress_icon_html' ) ) {
	/** Render an icon-v2 value to an <i>/<img>, enqueuing the icon CSS once. */
	function fw_progress_icon_html( $icon ) {
		if ( empty( $icon ) || ! is_array( $icon ) ) {
			return '';
		}
		if ( isset( fw()->backend->option_type( 'icon-v2' )->packs_loader ) ) {
			fw()->backend->option_type( 'icon-v2' )->packs_loader->enqueue_pack_for_icon( $icon );
		}
		$type = $icon['type'] ?? '';
		if ( $type === 'icon-font' && ! empty( $icon['icon-class'] ) ) {
			return '<i class="fw-progress__icon ' . esc_attr( trim( (string) $icon['icon-class'] ) ) . '" aria-hidden="true"></i>';
		}
		if ( $type === 'custom-upload' && ! empty( $icon['url'] ) ) {
			return '<img class="fw-progress__icon" src="' . esc_url( $icon['url'] ) . '" alt="' . esc_attr( $icon['alt'] ?? '' ) . '" loading="lazy">';
		}
		return '';
	}
}

$bars = isset( $atts['bars'] ) && is_array( $atts['bars'] ) ? $atts['bars'] : array();
if ( empty( $bars ) ) {
	return;
}

// ---- Layout (multi-picker: bar | circle | gauge) -------------------------
$layout = isset( $atts['layout'] ) && is_array( $atts['layout'] ) ? $atts['layout'] : array();
$type   = isset( $layout['type'] ) && in_array( $layout['type'], array( 'bar', 'circle', 'gauge', 'pie', 'vertical', 'segmented' ), true ) ? $layout['type'] : 'bar';

$circle_cfg = isset( $layout['circle'] ) && is_array( $layout['circle'] ) ? $layout['circle'] : array();
$gauge_cfg  = isset( $layout['gauge'] )  && is_array( $layout['gauge'] )  ? $layout['gauge']  : array();
$pie_cfg       = isset( $layout['pie'] )       && is_array( $layout['pie'] )       ? $layout['pie']       : array();
$vertical_cfg  = isset( $layout['vertical'] )  && is_array( $layout['vertical'] )  ? $layout['vertical']  : array();
$segmented_cfg = isset( $layout['segmented'] ) && is_array( $layout['segmented'] ) ? $layout['segmented'] : array();

// ---- Shared style --------------------------------------------------------
$height     = isset( $atts['height'] ) && trim( (string) $atts['height'] ) !== '' ? trim( (string) $atts['height'] ) : '10px';
$gap        = isset( $atts['gap'] ) && trim( (string) $atts['gap'] ) !== '' ? trim( (string) $atts['gap'] ) : '1.1rem';
$rounded    = ! isset( $atts['rounded'] ) || $atts['rounded'] !== 'no';
$striped    = isset( $atts['striped'] ) && $atts['striped'] === 'yes';
$show_value = ! isset( $atts['show_value'] ) || $atts['show_value'] !== 'no';
$animate    = ! isset( $atts['animate'] ) || $atts['animate'] !== 'no';
$count_up   = $animate && ( ! isset( $atts['count_up'] ) || $atts['count_up'] !== 'no' );
$val_inside = ( $atts['value_position'] ?? 'head' ) === 'inside';

// ---- Colours -------------------------------------------------------------
list( $fill_class, $fill_style )   = fw_progress_color( $atts['fill_color'] ?? '', 'bg' );
list( $track_class, $track_style ) = fw_progress_color( $atts['track_color'] ?? '', 'bg' );
list( $label_class, $label_style ) = fw_progress_color( $atts['label_color'] ?? '', 'text' );

$fill_raw  = fw_progress_raw_color( $atts['fill_color'] ?? '' );
$fill2_raw = fw_progress_raw_color( $atts['fill_color_2'] ?? '' );
$track_raw = fw_progress_raw_color( $atts['track_color'] ?? '' );
$gradient  = ( $fill_raw !== '' && $fill2_raw !== '' ) ? 'linear-gradient(90deg,' . $fill_raw . ',' . $fill2_raw . ')' : '';

// ---- Advanced (css id / class / responsive hide) -------------------------
$css_id    = ! empty( $atts['css_id'] ) ? $atts['css_id'] : '';
$css_class = ! empty( $atts['css_class'] ) ? $atts['css_class'] : '';
$hide_keys = array_keys( array_filter( (array) ( $atts['responsive_hide'] ?? array() ) ) );

$classes = array( 'fw-progress', 'fw-progress--' . $type );
if ( $rounded ) { $classes[] = 'fw-progress--rounded'; }
if ( $striped && $type === 'bar' ) { $classes[] = 'fw-progress--striped'; }
if ( $count_up ) { $classes[] = 'fw-progress--countup'; }
if ( $css_class !== '' ) { $classes[] = $css_class; }
$classes = array_unique( array_merge( $classes, $hide_keys ) );

// Per-instance wrapper style.
$wrap_style = '--fwp-h:' . $height . ';--fwp-gap:' . $gap;
if ( in_array( $type, array( 'circle', 'gauge', 'pie', 'vertical' ), true ) ) {
	$cols_map   = array(
		'circle'   => $circle_cfg['circle_columns']     ?? 3,
		'gauge'    => $gauge_cfg['gauge_columns']       ?? 3,
		'pie'      => $pie_cfg['pie_columns']           ?? 3,
		'vertical' => $vertical_cfg['vertical_columns'] ?? 3,
	);
	$cols       = max( 1, (int) $cols_map[ $type ] );
	$wrap_style .= ';--fwp-cols:' . $cols;
}

/** Resolve a single bar's fill: [ css-class, inline-style, raw-color ]. Per-bar colour overrides the section fill / gradient. */
$resolve_bar_fill = function ( $bar ) use ( $fill_class, $fill_style, $fill_raw, $gradient ) {
	list( $bc, $bs ) = fw_progress_color( $bar['color'] ?? '', 'bg' );
	$braw = fw_progress_raw_color( $bar['color'] ?? '' );
	if ( $bc !== '' || $bs !== '' || $braw !== '' ) {
		return array( $bc, $bs, $braw !== '' ? $braw : '' ); // per-bar solid override
	}
	// Section default: gradient if set, else the fill class/style.
	if ( $gradient !== '' ) {
		return array( '', 'background:' . $gradient . ';', $fill_raw );
	}
	return array( $fill_class, $fill_style, $fill_raw );
};
?>
<div<?php echo $css_id !== '' ? ' id="' . esc_attr( $css_id ) . '"' : ''; ?> class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" style="<?php echo esc_attr( $wrap_style ); ?>" data-animate="<?php echo $animate ? '1' : '0'; ?>">
<?php
foreach ( $bars as $bar ) :
	$label   = isset( $bar['label'] ) ? trim( (string) $bar['label'] ) : '';
	$percent = isset( $bar['percent'] ) ? max( 0, min( 100, (int) $bar['percent'] ) ) : 0;
	$icon    = fw_progress_icon_html( $bar['icon'] ?? array() );
	list( $f_class, $f_style, $f_raw ) = $resolve_bar_fill( $bar );

	// The percentage span (count-up aware). $tag/$cls vary per layout below.
	$value_span = '';
	if ( $show_value ) {
		$value_span = '<span class="fw-progress__value"' . ( $count_up ? ' data-count="' . esc_attr( $percent ) . '"' : '' ) . '>'
			. ( $count_up ? '0' : esc_html( $percent ) ) . '%</span>';
	}

	// =====================================================================
	if ( $type === 'circle' || $type === 'gauge' || $type === 'pie' ) :
		$is_gauge = ( $type === 'gauge' );
		$is_pie   = ( $type === 'pie' );
		$size     = $is_gauge
			? max( 40, (int) ( $gauge_cfg['gauge_size'] ?? 160 ) )
			: max( 40, (int) ( $circle_cfg['circle_size'] ?? 120 ) );
		$th       = $is_gauge
			? max( 2, min( 40, (int) ( $gauge_cfg['gauge_thickness'] ?? 12 ) ) )
			: max( 2, min( 40, (int) ( $circle_cfg['circle_thickness'] ?? 10 ) ) );
		if ( $is_pie ) {
			$size = max( 40, (int) ( $pie_cfg['pie_size'] ?? 140 ) );
			$th   = (int) round( $size / 2 );
		}
		$pad = $th / 2;
		$r   = ( $size - $th ) / 2;
		$cap = ( $is_pie || ! $rounded ) ? 'butt' : 'round';

		if ( $is_gauge ) {
			$cx     = $size / 2;
			$cy     = $r + $pad;
			$len    = M_PI * $r; // half circumference
			$startx = round( $pad, 2 );
			$endx   = round( $size - $pad, 2 );
			$d      = 'M ' . $startx . ' ' . round( $cy, 2 ) . ' A ' . round( $r, 2 ) . ' ' . round( $r, 2 ) . ' 0 0 1 ' . $endx . ' ' . round( $cy, 2 );
			$svg_h  = round( $cy + $pad, 2 );
		} else {
			$cx    = $cy = $size / 2;
			$len   = 2 * M_PI * $r; // full circumference
			$svg_h = $size;
		}
		$final_offset = round( $len * ( 1 - $percent / 100 ), 2 );
		$start_offset = $animate ? round( $len, 2 ) : $final_offset;
		$len_r        = round( $len, 2 );

		// SVG stroke colour: per-bar/section raw colour, gradient via <defs>.
		$use_grad   = ( $f_raw === $fill_raw && $gradient !== '' ); // section default + gradient set
		$grad_id    = $use_grad ? 'fwpg-' . substr( md5( uniqid( '', true ) ), 0, 8 ) : '';
		$fill_paint = $use_grad ? 'url(#' . $grad_id . ')' : ( $f_raw !== '' ? $f_raw : '' );
		$fill_attr  = $fill_paint !== '' ? ' stroke="' . esc_attr( $fill_paint ) . '"' : '';
		$track_attr = $track_raw !== '' ? ' stroke="' . esc_attr( $track_raw ) . '"' : '';
		?>
		<div class="fw-progress__item">
			<div class="fw-progress__svgwrap" style="width:<?php echo esc_attr( $size ); ?>px;height:<?php echo esc_attr( $svg_h ); ?>px">
				<svg class="fw-progress__svg" viewBox="0 0 <?php echo esc_attr( $size . ' ' . $svg_h ); ?>" width="<?php echo esc_attr( $size ); ?>" height="<?php echo esc_attr( $svg_h ); ?>" role="img" aria-label="<?php echo esc_attr( ( $label !== '' ? $label . ': ' : '' ) . $percent . '%' ); ?>">
					<?php if ( $use_grad ) : ?>
						<defs><linearGradient id="<?php echo esc_attr( $grad_id ); ?>" x1="0" y1="0" x2="1" y2="0">
							<stop offset="0" stop-color="<?php echo esc_attr( $fill_raw ); ?>"/><stop offset="1" stop-color="<?php echo esc_attr( $fill2_raw ); ?>"/>
						</linearGradient></defs>
					<?php endif; ?>
					<?php if ( $is_gauge ) : ?>
						<path class="fw-progress__svg-track" d="<?php echo esc_attr( $d ); ?>" fill="none" stroke-width="<?php echo esc_attr( $th ); ?>"<?php echo $track_attr; ?>/>
						<path class="fw-progress__svg-fill" d="<?php echo esc_attr( $d ); ?>" fill="none" stroke-width="<?php echo esc_attr( $th ); ?>" stroke-linecap="<?php echo esc_attr( $cap ); ?>"<?php echo $fill_attr; ?> stroke-dasharray="<?php echo esc_attr( $len_r ); ?>" stroke-dashoffset="<?php echo esc_attr( $start_offset ); ?>" data-offset="<?php echo esc_attr( $final_offset ); ?>"/>
					<?php else : ?>
						<circle class="fw-progress__svg-track" cx="<?php echo esc_attr( $cx ); ?>" cy="<?php echo esc_attr( $cy ); ?>" r="<?php echo esc_attr( round( $r, 2 ) ); ?>" fill="none" stroke-width="<?php echo esc_attr( $th ); ?>"<?php echo $track_attr; ?>/>
						<circle class="fw-progress__svg-fill" cx="<?php echo esc_attr( $cx ); ?>" cy="<?php echo esc_attr( $cy ); ?>" r="<?php echo esc_attr( round( $r, 2 ) ); ?>" fill="none" stroke-width="<?php echo esc_attr( $th ); ?>" stroke-linecap="<?php echo esc_attr( $cap ); ?>"<?php echo $fill_attr; ?> stroke-dasharray="<?php echo esc_attr( $len_r ); ?>" stroke-dashoffset="<?php echo esc_attr( $start_offset ); ?>" data-offset="<?php echo esc_attr( $final_offset ); ?>" transform="rotate(-90 <?php echo esc_attr( $cx . ' ' . $cy ); ?>)"/>
					<?php endif; ?>
				</svg>
				<div class="fw-progress__center<?php echo $is_gauge ? ' is-gauge' : ''; ?><?php echo $label_class ? ' ' . esc_attr( $label_class ) : ''; ?>"<?php echo $label_style ? ' style="' . esc_attr( $label_style ) . '"' : ''; ?>>
					<?php echo $icon; // already escaped ?>
					<?php echo $value_span; // already escaped ?>
				</div>
			</div>
			<?php if ( $label !== '' ) : ?>
				<div class="fw-progress__caption<?php echo $label_class ? ' ' . esc_attr( $label_class ) : ''; ?>"<?php echo $label_style ? ' style="' . esc_attr( $label_style ) . '"' : ''; ?>><?php echo esc_html( $label ); ?></div>
			<?php endif; ?>
		</div>
		<?php
	// =====================================================================
	elseif ( $type === 'vertical' ) :
		$fill_h = $animate ? '0' : ( $percent . '%' );
		$vh     = max( 40, (int) ( $vertical_cfg['vertical_height'] ?? 180 ) );
		?>
		<div class="fw-progress__item">
			<div class="fw-progress__vtrack<?php echo $track_class ? ' ' . esc_attr( $track_class ) : ''; ?>" style="height:<?php echo esc_attr( $vh ); ?>px;<?php echo esc_attr( $track_style ); ?>" role="progressbar" aria-valuenow="<?php echo esc_attr( $percent ); ?>" aria-valuemin="0" aria-valuemax="100"<?php echo $label !== '' ? ' aria-label="' . esc_attr( $label ) . '"' : ''; ?>>
				<span class="fw-progress__vfill<?php echo $f_class ? ' ' . esc_attr( $f_class ) : ''; ?>" data-height="<?php echo esc_attr( $percent ); ?>%" style="height:<?php echo esc_attr( $fill_h ); ?>;<?php echo esc_attr( $f_style ); ?>"></span>
			</div>
			<?php if ( $show_value ) : ?>
				<div class="fw-progress__caption<?php echo $label_class ? ' ' . esc_attr( $label_class ) : ''; ?>"<?php echo $label_style ? ' style="' . esc_attr( $label_style ) . '"' : ''; ?>><?php echo $value_span; ?></div>
			<?php endif; ?>
			<?php if ( $label !== '' ) : ?>
				<div class="fw-progress__caption<?php echo $label_class ? ' ' . esc_attr( $label_class ) : ''; ?>"<?php echo $label_style ? ' style="' . esc_attr( $label_style ) . '"' : ''; ?>><?php echo $icon; ?><?php echo esc_html( $label ); ?></div>
			<?php endif; ?>
		</div>
		<?php
	elseif ( $type === 'segmented' ) :
		$seg_count = max( 2, (int) ( $segmented_cfg['segment_count'] ?? 10 ) );
		$seg_on    = (int) round( $percent / 100 * $seg_count );
		?>
		<div class="fw-progress__item">
			<?php if ( $label !== '' || ( $show_value && ! $val_inside ) || $icon ) : ?>
				<div class="fw-progress__head<?php echo $label_class ? ' ' . esc_attr( $label_class ) : ''; ?>"<?php echo $label_style ? ' style="' . esc_attr( $label_style ) . '"' : ''; ?>>
					<span class="fw-progress__label"><?php echo $icon; ?><?php echo $label !== '' ? esc_html( $label ) : ''; ?></span>
					<?php echo ( $show_value && ! $val_inside ) ? $value_span : ''; ?>
				</div>
			<?php endif; ?>
			<div class="fw-progress__segments" role="progressbar" aria-valuenow="<?php echo esc_attr( $percent ); ?>" aria-valuemin="0" aria-valuemax="100"<?php echo $label !== '' ? ' aria-label="' . esc_attr( $label ) . '"' : ''; ?>>
				<?php for ( $si = 0; $si < $seg_count; $si++ ) :
					$on = ( $si < $seg_on );
				?>
					<span class="fw-progress__seg<?php echo $on ? ' is-on' : ''; ?><?php echo ( $on && $f_class ) ? ' ' . esc_attr( $f_class ) : ''; ?>"<?php echo ( $on && $f_style ) ? ' style="' . esc_attr( $f_style ) . '"' : ''; ?>></span>
				<?php endfor; ?>
			</div>
		</div>
		<?php
	else : // horizontal bar
		$fill_w = $animate ? '0' : ( $percent . '%' );
		?>
		<div class="fw-progress__item">
			<?php if ( $label !== '' || ( $show_value && ! $val_inside ) || $icon ) : ?>
				<div class="fw-progress__head<?php echo $label_class ? ' ' . esc_attr( $label_class ) : ''; ?>"<?php echo $label_style ? ' style="' . esc_attr( $label_style ) . '"' : ''; ?>>
					<span class="fw-progress__label"><?php echo $icon; ?><?php echo $label !== '' ? esc_html( $label ) : ''; ?></span>
					<?php echo ( $show_value && ! $val_inside ) ? $value_span : ''; ?>
				</div>
			<?php endif; ?>
			<div class="fw-progress__track<?php echo $track_class ? ' ' . esc_attr( $track_class ) : ''; ?>"<?php echo $track_style ? ' style="' . esc_attr( $track_style ) . '"' : ''; ?> role="progressbar" aria-valuenow="<?php echo esc_attr( $percent ); ?>" aria-valuemin="0" aria-valuemax="100"<?php echo $label !== '' ? ' aria-label="' . esc_attr( $label ) . '"' : ''; ?>>
				<span class="fw-progress__fill<?php echo $f_class ? ' ' . esc_attr( $f_class ) : ''; ?>" data-width="<?php echo esc_attr( $percent ); ?>%" style="width:<?php echo esc_attr( $fill_w ); ?>;<?php echo esc_attr( $f_style ); ?>">
					<?php echo ( $show_value && $val_inside ) ? $value_span : ''; ?>
				</span>
			</div>
		</div>
		<?php
	endif;
endforeach;
?>
</div>
