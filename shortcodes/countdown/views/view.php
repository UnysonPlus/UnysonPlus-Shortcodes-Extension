<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * @var array $atts
 */

if ( ! function_exists( 'fw_countdown_typography_css' ) ) {
	/** A typography-v2 value → inline CSS declarations (only the keys that are set). */
	function fw_countdown_typography_css( $t ) {
		if ( ! is_array( $t ) ) {
			return '';
		}
		$css = '';
		if ( ! empty( $t['family'] ) ) {
			$css .= 'font-family:' . $t['family'] . ';';
		}
		// '400' is the default weight (styles.css) — never write it inline, so a
		// default countdown keeps a clean DOM. Only a non-default weight is emitted.
		if ( ! empty( $t['weight'] ) && (string) $t['weight'] !== '400' ) {
			$css .= 'font-weight:' . $t['weight'] . ';';
		}
		if ( isset( $t['size'] ) && $t['size'] !== '' ) {
			$css .= 'font-size:' . (int) $t['size'] . 'px;';
		}
		if ( isset( $t['line-height'] ) && $t['line-height'] !== '' && (int) $t['line-height'] > 0 ) {
			$css .= 'line-height:' . (int) $t['line-height'] . 'px;';
		}
		if ( isset( $t['letter-spacing'] ) && $t['letter-spacing'] !== '' && (int) $t['letter-spacing'] !== 0 ) {
			$css .= 'letter-spacing:' . (int) $t['letter-spacing'] . 'px;';
		}
		if ( ! empty( $t['style'] ) && $t['style'] !== 'normal' ) {
			$css .= 'font-style:' . $t['style'] . ';';
		}
		return $css;
	}
}

if ( ! function_exists( 'fw_countdown_enqueue_font' ) ) {
	/** Enqueue a typography-v2 value's Google font, if one was chosen. */
	function fw_countdown_enqueue_font( $t ) {
		if ( ! is_array( $t ) || empty( $t['google_font'] ) || empty( $t['family'] ) ) {
			return;
		}
		$fam = str_replace( ' ', '+', $t['family'] );
		$wt  = ! empty( $t['weight'] ) ? ':' . $t['weight'] : '';
		wp_enqueue_style(
			'fw-countdown-font-' . sanitize_title( $t['family'] . $t['weight'] ),
			'https://fonts.googleapis.com/css?family=' . $fam . $wt . '&display=swap',
			array(),
			null
		);
	}
}

if ( ! function_exists( 'fw_countdown_color' ) ) {
	/** A preset/custom colour value → [ class, style-decls ] for a given CSS property. */
	function fw_countdown_color( $value, $kind = 'text' ) {
		// Back-compat: a flat hex/rgb string → treat as a custom colour.
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

$target_raw = isset( $atts['target'] ) ? trim( (string) $atts['target'] ) : '';
$target_ms  = 0;
if ( $target_raw !== '' ) {
	try {
		$tz = function_exists( 'wp_timezone' ) ? wp_timezone() : new DateTimeZone( date_default_timezone_get() );
		$dt = date_create( $target_raw, $tz );
		if ( $dt ) {
			$target_ms = $dt->getTimestamp() * 1000;
		}
	} catch ( Exception $e ) {
		$target_ms = 0;
	}
}

// Alignment: '' (Inherit) forces nothing — the timer follows the theme / parent
// alignment. Only `center` / `right` emit a modifier class (there is no `--left`
// rule: left is the natural default), so an empty / left value adds no class.
$alignment   = isset( $atts['alignment'] ) && in_array( $atts['alignment'], array( 'left', 'center', 'right' ), true ) ? $atts['alignment'] : '';
// Box Preset (Theme Settings → Components → Box Presets) → a `.boxp-{name}` class on each
// unit card. None = plain numbers. Option key stays `box_preset`.
$box_preset  = isset( $atts['box_preset'] ) ? (string) $atts['box_preset'] : '';
$box_class   = ( $box_preset !== '' && preg_match( '/^boxp-[a-z0-9_-]+$/i', $box_preset ) ) ? $box_preset : '';
$on_complete = isset( $atts['on_complete'] ) && in_array( $atts['on_complete'], array( 'message', 'zeros', 'hide' ), true ) ? $atts['on_complete'] : 'message';
$complete    = isset( $atts['complete_text'] ) ? (string) $atts['complete_text'] : '';

// Which units to render, in order.
$units = array(
	'days'    => array( 'show' => ( ! isset( $atts['show_days'] ) || $atts['show_days'] !== 'no' ),    'label' => isset( $atts['label_days'] ) ? $atts['label_days'] : __( 'Days', 'fw' ) ),
	'hours'   => array( 'show' => ( ! isset( $atts['show_hours'] ) || $atts['show_hours'] !== 'no' ),   'label' => isset( $atts['label_hours'] ) ? $atts['label_hours'] : __( 'Hours', 'fw' ) ),
	'minutes' => array( 'show' => ( ! isset( $atts['show_minutes'] ) || $atts['show_minutes'] !== 'no' ), 'label' => isset( $atts['label_minutes'] ) ? $atts['label_minutes'] : __( 'Minutes', 'fw' ) ),
	'seconds' => array( 'show' => ( ! isset( $atts['show_seconds'] ) || $atts['show_seconds'] !== 'no' ), 'label' => isset( $atts['label_seconds'] ) ? $atts['label_seconds'] : __( 'Seconds', 'fw' ) ),
);

// Typography + colour for the numbers and labels.
$number_font = isset( $atts['number_font'] ) && is_array( $atts['number_font'] ) ? $atts['number_font'] : array();
$label_font  = isset( $atts['label_font'] ) && is_array( $atts['label_font'] ) ? $atts['label_font'] : array();
fw_countdown_enqueue_font( $number_font );
fw_countdown_enqueue_font( $label_font );

list( $num_class, $num_color_css )   = fw_countdown_color( isset( $atts['number_color'] ) ? $atts['number_color'] : '', 'text' );
list( $lab_class, $lab_color_css )   = fw_countdown_color( isset( $atts['label_color'] ) ? $atts['label_color'] : '', 'text' );

$num_style = trim( fw_countdown_typography_css( $number_font ) . $num_color_css );
$lab_style = trim( fw_countdown_typography_css( $label_font ) . $lab_color_css );

// Advanced (the 'advanced_settings' group flattens to top-level $atts keys).
$css_id    = ! empty( $atts['css_id'] ) ? $atts['css_id'] : '';
$css_class = ! empty( $atts['css_class'] ) ? $atts['css_class'] : '';
$hide_keys = array_keys( array_filter( (array) ( $atts['responsive_hide'] ?? array() ) ) );

$classes = array( 'fw-countdown' );
if ( $alignment === 'center' || $alignment === 'right' ) {
	$classes[] = 'fw-countdown--' . $alignment;
}
if ( $css_class !== '' ) {
	$classes[] = $css_class;
}
$classes = array_merge( $classes, $hide_keys );
?>
<div<?php echo $css_id !== '' ? ' id="' . esc_attr( $css_id ) . '"' : ''; ?> class="<?php echo esc_attr( implode( ' ', array_unique( $classes ) ) ); ?>" data-target="<?php echo esc_attr( $target_ms ); ?>" data-oncomplete="<?php echo esc_attr( $on_complete ); ?>">
	<div class="fw-countdown__units">
		<?php foreach ( $units as $unit => $info ) : ?>
			<?php if ( ! $info['show'] ) { continue; } ?>
			<div class="fw-countdown__unit<?php echo $box_class ? ' ' . esc_attr( $box_class ) : ''; ?>" data-unit="<?php echo esc_attr( $unit ); ?>">
				<span class="fw-countdown__num<?php echo $num_class ? ' ' . esc_attr( $num_class ) : ''; ?>"<?php echo $num_style ? ' style="' . esc_attr( $num_style ) . '"' : ''; ?>>--</span>
				<?php if ( $info['label'] !== '' ) : ?>
					<span class="fw-countdown__label<?php echo $lab_class ? ' ' . esc_attr( $lab_class ) : ''; ?>"<?php echo $lab_style ? ' style="' . esc_attr( $lab_style ) . '"' : ''; ?>><?php echo esc_html( $info['label'] ); ?></span>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	</div>
	<?php if ( $on_complete === 'message' && $complete !== '' ) : ?>
		<div class="fw-countdown__done" style="display:none"><?php echo esc_html( $complete ); ?></div>
	<?php endif; ?>
</div>
