<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Design: Flip Cards — each tile flips on hover to reveal the caption (and a
 * "view" cue) on the back. The whole card is the click target, so the front/back
 * faces are built by hand (the shared tile renders a single face); lightbox.js
 * still neutralises + handles the anchor exactly like every other design.
 */

$g_c    = $g_cols( 3 );
$cols   = $g_c['desktop'];
$cols_t = $g_c['tablet'];
$cols_m = $g_c['mobile'];
$gap    = sc_gallery_gap_css( $g_dp( 'gap', '3' ) );
$ratio  = $g_dp( 'ratio', '4-3' );
$ratio_css = sc_gallery_ratio_css( $ratio );

$grid_style = sprintf( '--gal-cols:%d;--gal-cols-t:%d;--gal-cols-m:%d;--gal-gap:%s;', $cols, $cols_t, $cols_m, $gap );
if ( $ratio_css !== '' ) {
	$grid_style .= '--gal-ratio:' . $ratio_css . ';';
}

$rounded   = isset( $tile_args['rounded'] ) ? $tile_args['rounded'] : 'rounded';
$cap_class = $caption_class_extra !== '' ? ' ' . $caption_class_extra : '';
$cap_style = $caption_style_extra !== '' ? ' style="' . esc_attr( $caption_style_extra ) . '"' : '';
?>
<div <?php echo fw_attr_to_html( $attr ); ?>>
	<?php if ( $container_cls ) : ?><div class="<?php echo esc_attr( $container_cls ); ?>"><?php endif; ?>
		<?php if ( $title !== '' ) : ?>
			<h3 class="fw-gallery__title <?php echo esc_attr( $title_class_extra ); ?>"<?php echo $title_style_extra !== '' ? ' style="' . esc_attr( $title_style_extra ) . '"' : ''; ?>><?php echo esc_html( $title ); ?></h3>
		<?php endif; ?>

		<div class="fw-gallery__flip-grid" style="<?php echo esc_attr( $grid_style ); ?>">
			<?php foreach ( $items as $item ) :
				$cap       = sc_gallery_caption_text( $item, $caption_source );
				$back_text = $cap !== '' ? $cap : $item['title'];
				$img       = sc_gallery_img_html( $item, $tile_args );

				switch ( $click_action ) {
					case 'file':
						$open  = '<a class="fw-gallery__flip" href="' . esc_url( $item['full'] ) . '" target="_blank" rel="noopener noreferrer">';
						$close = '</a>';
						break;
					case 'attachment':
						$href  = $item['id'] ? get_attachment_link( $item['id'] ) : $item['full'];
						$open  = '<a class="fw-gallery__flip" href="' . esc_url( $href ) . '">';
						$close = '</a>';
						break;
					case 'none':
						$open  = '<div class="fw-gallery__flip">';
						$close = '</div>';
						break;
					case 'lightbox':
					default:
						$open  = '<a class="fw-gallery__flip" href="' . esc_url( $item['full'] ) . '" data-fw-lightbox="' . esc_attr( $lightbox_group ) . '"'
							. ( $cap !== '' ? ' data-fw-caption="' . esc_attr( $cap ) . '"' : '' ) . '>';
						$close = '</a>';
						break;
				}

				echo $open;
				echo '<span class="fw-gallery__flip-inner">';
				echo '<span class="fw-gallery__flip-face fw-gallery__flip-front ' . esc_attr( $rounded ) . '">' . $img . '</span>';
				echo '<span class="fw-gallery__flip-face fw-gallery__flip-back ' . esc_attr( $rounded ) . '">';
				if ( $back_text !== '' ) {
					echo '<span class="fw-gallery__flip-caption' . esc_attr( $cap_class ) . '"' . $cap_style . '>' . esc_html( $back_text ) . '</span>';
				}
				if ( $click_action !== 'none' ) {
					echo '<span class="fw-gallery__flip-cue" aria-hidden="true">' . esc_html__( 'View', 'fw' ) . '</span>';
				}
				echo '</span>';
				echo '</span>';
				echo $close;
			endforeach; ?>
		</div>
	<?php if ( $container_cls ) : ?></div><?php endif; ?>
</div>
