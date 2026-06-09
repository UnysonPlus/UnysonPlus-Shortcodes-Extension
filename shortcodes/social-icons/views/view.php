<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * @var array $atts
 *
 * Renders social profile links. "theme_settings" delegates to the theme's own
 * renderer when present (so it matches the rest of the site); "manual" renders
 * the list defined in the shortcode.
 */

$source = ! empty( $atts['source'] ) ? $atts['source'] : 'theme_settings';

if ( $source === 'theme_settings' ) {
	if ( function_exists( 'unysonplus_render_social_icons' ) ) {
		unysonplus_render_social_icons();
	}
	return;
}

$profiles = ( ! empty( $atts['profiles'] ) && is_array( $atts['profiles'] ) ) ? $atts['profiles'] : array();
if ( ! $profiles ) {
	return;
}

$size = ! empty( $atts['size'] ) ? $atts['size'] : 'md';

// Enqueue icon-v2 pack CSS so non-global packs (Linecons, Entypo, …) render.
if ( isset( fw()->backend->option_type( 'icon-v2' )->packs_loader ) ) {
	fw()->backend->option_type( 'icon-v2' )->packs_loader->enqueue_frontend_css();
}

$atts['base_class']       = 'sc-social';
$atts['unique_id_prefix'] = 'social-';
$atts['extra_attrs']      = array();
$attr = sc_build_wrapper_attr( $atts );

$classes = ! empty( $attr['class'] ) ? explode( ' ', $attr['class'] ) : array();
unset( $attr['class'] );
$classes[] = 'sc-social--' . sanitize_html_class( $size );
?>
<ul class="<?php echo esc_attr( implode( ' ', array_filter( $classes ) ) ); ?>" <?php echo fw_attr_to_html( $attr ); ?>>
	<?php foreach ( $profiles as $p ) : ?>
		<?php
		$link = ! empty( $p['link'] ) ? $p['link'] : '';
		if ( $link === '' ) {
			continue;
		}
		$icon_class = ! empty( $p['icon']['icon-class'] ) ? $p['icon']['icon-class'] : '';
		$sr         = ! empty( $p['label'] ) ? $p['label'] : $link;
		?>
		<li class="sc-social__item">
			<a class="sc-social__link" href="<?php echo esc_url( $link ); ?>" target="_blank" rel="noopener noreferrer">
				<?php if ( $icon_class ) : ?>
					<i class="<?php echo esc_attr( $icon_class ); ?>" aria-hidden="true"></i>
				<?php endif; ?>
				<span class="screen-reader-text"><?php echo esc_html( $sr ); ?></span>
			</a>
		</li>
	<?php endforeach; ?>
</ul>
