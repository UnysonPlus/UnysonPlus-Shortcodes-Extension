<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * @var array $atts
 */

/*
|--------------------------------------------------------------------------
| Enqueue icon-v2 frontend CSS (only when a picked icon will actually render)
|--------------------------------------------------------------------------
*/
if (
	empty( $atts['custom_icon'] ) &&
	! empty( $atts['icon'] ) &&
	isset( fw()->backend->option_type( 'icon-v2' )->packs_loader )
) {
	fw()->backend->option_type( 'icon-v2' )->packs_loader->enqueue_frontend_css();
}

/*
|--------------------------------------------------------------------------
| Normalize incoming attributes
|--------------------------------------------------------------------------
*/
$type         = ! empty( $atts['type'] ) ? $atts['type'] : 'info';
$layout       = ( isset( $atts['layout'] ) && $atts['layout'] === 'stacked' ) ? 'stacked' : 'inline';
$border_style = ! empty( $atts['border_style'] ) ? $atts['border_style'] : 'filled';
$message      = isset( $atts['message'] ) ? (string) $atts['message'] : '';
$label_text   = isset( $atts['label_text'] ) ? trim( (string) $atts['label_text'] ) : '';
$custom_icon  = isset( $atts['custom_icon'] ) ? trim( (string) $atts['custom_icon'] ) : '';
$picked_icon  = ! empty( $atts['icon'] ) ? $atts['icon'] : null;
$dismissible  = ! empty( $atts['dismissible'] );
$auto_dismiss = isset( $atts['auto_dismiss'] ) ? max( 0, (int) $atts['auto_dismiss'] ) : 0;

$default_labels = apply_filters( 'sc_notification_default_labels', [
	'primary'   => __( 'Note!',        'fw' ),
	'secondary' => __( 'Note!',        'fw' ),
	'success'   => __( 'Success!',     'fw' ),
	'info'      => __( 'Information!', 'fw' ),
	'warning'   => __( 'Warning!',     'fw' ),
	'danger'    => __( 'Error!',       'fw' ),
	'light'     => __( 'Note!',        'fw' ),
	'dark'      => __( 'Note!',        'fw' ),
] );

$default_icons = apply_filters( 'sc_notification_default_icons', [
	'primary'   => 'fa-solid fa-bolt',
	'secondary' => 'fa-solid fa-circle-dot',
	'success'   => 'fa-solid fa-circle-check',
	'info'      => 'fa-solid fa-circle-info',
	'warning'   => 'fa-solid fa-triangle-exclamation',
	'danger'    => 'fa-solid fa-circle-xmark',
	'light'     => 'fa-solid fa-lightbulb',
	'dark'      => 'fa-solid fa-moon',
] );

$label = $label_text !== '' ? $label_text : ( $default_labels[ $type ] ?? '' );

// Use the new wrapped-icon DOM only when one of the NEW icon fields is in play.
// Otherwise emit the exact same bare <i class="… alert-icon"></i> as the old view,
// so existing saved shortcodes render with the identical DOM.
$use_new_icon_markup = ( $custom_icon !== '' || ! empty( $picked_icon ) );

/*
|--------------------------------------------------------------------------
| Icon rendering helper (used only for the new icon markup path)
|--------------------------------------------------------------------------
| Priority: custom_icon (emoji / inline SVG) > icon-v2 picked icon > default FA icon for type.
*/
if ( ! function_exists( 'sc_notification_render_icon' ) ) {
	function sc_notification_render_icon( $custom_icon, $picked_icon, $type, $default_icons ) {

		// 1. Custom icon override (emoji or inline SVG)
		if ( is_string( $custom_icon ) && $custom_icon !== '' ) {

			$svg_allowed = [
				'svg'      => [ 'xmlns' => true, 'viewbox' => true, 'width' => true, 'height' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true, 'class' => true, 'role' => true, 'aria-hidden' => true, 'focusable' => true ],
				'g'        => [ 'fill' => true, 'stroke' => true, 'transform' => true, 'class' => true ],
				'path'     => [ 'd' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true, 'stroke-linecap' => true, 'stroke-linejoin' => true, 'class' => true ],
				'circle'   => [ 'cx' => true, 'cy' => true, 'r' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true, 'class' => true ],
				'rect'     => [ 'x' => true, 'y' => true, 'width' => true, 'height' => true, 'rx' => true, 'ry' => true, 'fill' => true, 'stroke' => true, 'class' => true ],
				'line'     => [ 'x1' => true, 'y1' => true, 'x2' => true, 'y2' => true, 'stroke' => true, 'stroke-width' => true, 'class' => true ],
				'polyline' => [ 'points' => true, 'fill' => true, 'stroke' => true, 'class' => true ],
				'polygon'  => [ 'points' => true, 'fill' => true, 'stroke' => true, 'class' => true ],
				'title'    => [],
				'desc'     => [],
			];

			if ( stripos( $custom_icon, '<svg' ) !== false ) {
				return wp_kses( $custom_icon, $svg_allowed );
			}

			return esc_html( $custom_icon );
		}

		// 2. icon-v2 image upload
		if (
			is_array( $picked_icon ) &&
			isset( $picked_icon['type'] ) &&
			$picked_icon['type'] === 'custom-upload' &&
			! empty( $picked_icon['url'] )
		) {
			return sprintf(
				'<img src="%s" alt="" class="alert__icon-image" loading="lazy">',
				esc_url( $picked_icon['url'] )
			);
		}

		// 3. icon-v2 font icon
		if (
			is_array( $picked_icon ) &&
			isset( $picked_icon['type'] ) &&
			$picked_icon['type'] === 'icon-font' &&
			! empty( $picked_icon['icon-class'] )
		) {
			return '<i class="' . esc_attr( $picked_icon['icon-class'] ) . '"></i>';
		}

		// 4. Default per-type Font Awesome icon
		if ( ! empty( $default_icons[ $type ] ) ) {
			return '<i class="' . esc_attr( $default_icons[ $type ] ) . '"></i>';
		}

		return '';
	}
}

/*
|--------------------------------------------------------------------------
| Wrapper attributes
|--------------------------------------------------------------------------
| Note: only emit layout / border modifier classes when they differ from the
| defaults of the old shortcode. This keeps the wrapper's class attribute
| identical for existing saved notifications (inline + filled).
*/
$wrapper_classes = [ 'alert-' . sanitize_html_class( $type ) ];

if ( $layout !== 'inline' ) {
	$wrapper_classes[] = 'alert--' . sanitize_html_class( $layout );
}
if ( $border_style !== 'filled' ) {
	$wrapper_classes[] = 'alert--border-' . sanitize_html_class( $border_style );
}
if ( $dismissible ) {
	$wrapper_classes[] = 'alert-dismissible';
	$wrapper_classes[] = 'fade';
	$wrapper_classes[] = 'show';
}

// Per-element color picks (kept off the wrapper). sc_extract_styling_atts
// gives us both preset classes AND compact-picker custom-hex inline styles.
$label_styling   = sc_extract_styling_atts( $atts, array( 'label_color' ) );
$message_styling = sc_extract_styling_atts( $atts, array( 'message_color' ) );
$icon_styling    = sc_extract_styling_atts( $atts, array( 'icon_color' ) );
$label_extras    = $label_styling['classes'];
$message_extras  = $message_styling['classes'];
$icon_extras     = $icon_styling['classes'];
$label_style     = $label_styling['styles']   ? implode( '; ', $label_styling['styles'] )   : '';
$message_style   = $message_styling['styles'] ? implode( '; ', $message_styling['styles'] ) : '';
$icon_style      = $icon_styling['styles']    ? implode( '; ', $icon_styling['styles'] )    : '';

$label_style_attr   = $label_style   !== '' ? ' style="' . esc_attr( $label_style ) . '"'   : '';
$message_style_attr = $message_style !== '' ? ' style="' . esc_attr( $message_style ) . '"' : '';
$icon_style_attr    = $icon_style    !== '' ? ' style="' . esc_attr( $icon_style ) . '"'    : '';

$label_class_attr   = ! empty( $label_extras )   ? ' class="' . esc_attr( trim( 'alert__label ' . implode( ' ', $label_extras ) ) ) . '"' : ' class="alert__label"';
$label_bare_attr    = ! empty( $label_extras )   ? ' class="' . esc_attr( implode( ' ', $label_extras ) ) . '"' : '';
$message_inline_attr = ! empty( $message_extras ) ? ' class="' . esc_attr( implode( ' ', $message_extras ) ) . '"' : '';
$icon_wrap_class    = trim( 'alert__icon ' . implode( ' ', $icon_extras ) );

// Force a wrapping <span> on the message when only a custom hex (no class) is
// picked, so the inline style has something to land on.
$message_has_style_only = ( $message_inline_attr === '' && $message_style_attr !== '' );

$atts['base_class']       = 'alert';
$atts['unique_id_prefix'] = 'al-';
$atts['css_class']        = trim( implode( ' ', $wrapper_classes ) . ' ' . ( $atts['css_class'] ?? '' ) );

if ( $dismissible && $auto_dismiss > 0 ) {
	$atts['extra_attrs'] = array_merge(
		isset( $atts['extra_attrs'] ) && is_array( $atts['extra_attrs'] ) ? $atts['extra_attrs'] : [],
		[ 'data-auto-dismiss' => (string) $auto_dismiss ]
	);
}

$attr = sc_build_wrapper_attr( $atts );
?>

<div <?php echo fw_attr_to_html( $attr ); ?> role="alert">
	<?php if ( $layout === 'stacked' ) : ?>
		<?php
		// New stacked layout: icon column + body column (label above message).
		$icon_inner = sc_notification_render_icon( $custom_icon, $picked_icon, $type, $default_icons );
		if ( $icon_inner !== '' ) :
		?>
			<span class="<?php echo esc_attr( $icon_wrap_class ); ?>"<?php echo $icon_style_attr; ?> aria-hidden="true"><?php echo $icon_inner; ?></span>
		<?php endif; ?>
		<div class="alert__body">
			<?php if ( $label !== '' ) : ?>
				<strong<?php echo $label_class_attr; ?><?php echo $label_style_attr; ?>><?php echo esc_html( $label ); ?></strong>
			<?php endif; ?>
			<div class="alert__message<?php echo ! empty( $message_extras ) ? ' ' . esc_attr( implode( ' ', $message_extras ) ) : ''; ?>"<?php echo $message_style_attr; ?>><?php echo wp_kses_post( $message ); ?></div>
		</div>
	<?php elseif ( $use_new_icon_markup ) : ?>
		<?php
		// Inline layout but using the NEW icon path (custom_icon or icon-v2 picked).
		// Wrap the icon for consistent sizing of emoji / SVG / <img>.
		$icon_inner = sc_notification_render_icon( $custom_icon, $picked_icon, $type, $default_icons );
		if ( $icon_inner !== '' ) :
		?>
			<span class="<?php echo esc_attr( $icon_wrap_class ); ?>"<?php echo $icon_style_attr; ?> aria-hidden="true"><?php echo $icon_inner; ?></span><?php echo ' '; ?>
		<?php endif; ?>
		<?php if ( $label !== '' ) : ?>
			<strong<?php echo $label_bare_attr; ?><?php echo $label_style_attr; ?>><?php echo esc_html( $label ); ?></strong><?php echo ' '; ?>
		<?php endif; ?>
		<?php if ( ! empty( $message_extras ) || $message_has_style_only ) : ?>
			<span<?php echo $message_inline_attr; ?><?php echo $message_style_attr; ?>><?php echo wp_kses_post( $message ); ?></span>
		<?php else : ?>
			<?php echo wp_kses_post( $message ); ?>
		<?php endif; ?>
	<?php else : ?>
		<?php
		// Legacy inline path — emit the exact DOM of the old view, extended with
		// optional Styling-tab color classes when picked.
		if ( ! empty( $default_icons[ $type ] ) ) {
			$legacy_icon_class = trim( $default_icons[ $type ] . ' alert-icon ' . implode( ' ', $icon_extras ) );
			echo '<i class="' . esc_attr( $legacy_icon_class ) . '"' . $icon_style_attr . '></i> ';
		}
		if ( $label !== '' ) {
			echo '<strong' . $label_bare_attr . $label_style_attr . '>' . esc_html( $label ) . '</strong> ';
		}
		if ( ! empty( $message_extras ) || $message_has_style_only ) {
			echo '<span' . $message_inline_attr . $message_style_attr . '>' . wp_kses_post( $message ) . '</span>';
		} else {
			echo wp_kses_post( $message );
		}
		?>
	<?php endif; ?>

	<?php if ( $dismissible ) : ?>
		<button type="button" class="alert__close" aria-label="<?php echo esc_attr__( 'Close', 'fw' ); ?>">&times;</button>
	<?php endif; ?>
</div>
