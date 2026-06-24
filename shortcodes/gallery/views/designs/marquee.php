<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Design: Marquee / Ticker — a single fixed-height row of images that scrolls
 * continuously (CSS animation, pauses on hover). The item list is rendered
 * twice so the loop is seamless; the second copy is non-interactive (click
 * action "none") so it never duplicates the lightbox set.
 */

$row   = max( 80, (int) $g_dp( 'row_height', 180 ) );
$gap   = sc_gallery_gap_css( $g_dp( 'gap', '3' ) );
$speed = $g_dp( 'marquee_speed', 'normal' );
$dir   = $g_dp( 'marquee_direction', 'left' );

$per_item = array( 'slow' => 5.0, 'normal' => 3.2, 'fast' => 2.0 );
$dur      = max( 8, count( $items ) * ( isset( $per_item[ $speed ] ) ? $per_item[ $speed ] : 3.2 ) );

$wrap_style = sprintf( '--mq-h:%dpx;--mq-gap:%s;--mq-dur:%ss;', $row, $gap, rtrim( rtrim( number_format( $dur, 2, '.', '' ), '0' ), '.' ) );
$wrap_class = 'fw-gallery__marquee fw-gallery__marquee--' . ( $dir === 'right' ? 'right' : 'left' );

$tile_real  = array_merge( $tile_args, array( 'media_class' => 'fw-gallery__media--auto' ) );
$tile_clone = array_merge( $tile_real, array( 'click_action' => 'none', 'captions' => 'none' ) );
?>
<div <?php echo fw_attr_to_html( $attr ); ?>>
	<?php if ( $container_cls ) : ?><div class="<?php echo esc_attr( $container_cls ); ?>"><?php endif; ?>
		<?php if ( $title !== '' ) : ?>
			<h3 class="fw-gallery__title <?php echo esc_attr( $title_class_extra ); ?>"<?php echo $title_style_extra !== '' ? ' style="' . esc_attr( $title_style_extra ) . '"' : ''; ?>><?php echo esc_html( $title ); ?></h3>
		<?php endif; ?>

		<div class="<?php echo esc_attr( $wrap_class ); ?>" style="<?php echo esc_attr( $wrap_style ); ?>">
			<div class="fw-gallery__marquee-track">
				<?php foreach ( $items as $item ) {
					echo sc_gallery_render_tile( $item, $tile_real );
				} ?>
				<?php foreach ( $items as $item ) {
					echo sc_gallery_render_tile( $item, $tile_clone );
				} ?>
			</div>
		</div>
	<?php if ( $container_cls ) : ?></div><?php endif; ?>
</div>
