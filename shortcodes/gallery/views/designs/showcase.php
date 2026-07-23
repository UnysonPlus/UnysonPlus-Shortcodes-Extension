<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Design: Showcase — a large featured image plus a thumbnail strip. Clicking a
 * thumbnail swaps the featured image (showcase.js); clicking the featured image
 * opens the lightbox at the active index (via hidden lightbox source anchors,
 * so lightbox.js handles it with zero coupling). The strip can sit below, left
 * or right of the stage.
 */

$ratio    = $g_dp( 'ratio', '4-3' );
$ratio_css = sc_gallery_ratio_css( $ratio );
$gap      = sc_gallery_gap_css( $g_dp( 'gap', '3' ) );
$position = $g_dp( 'thumb_position', 'bottom' );
if ( ! in_array( $position, array( 'bottom', 'left', 'right' ), true ) ) {
	$position = 'bottom';
}

$wrap_style = sprintf( '--gal-gap:%s;', $gap );
if ( $ratio_css !== '' ) {
	$wrap_style .= '--gal-ratio:' . $ratio_css . ';';
}
$wrap_class = 'fw-gallery__showcase fw-gallery__showcase--' . $position
	. ( $ratio === 'original' ? ' fw-gallery__showcase--natural' : '' );

$first   = $items[0];
$first_caption = sc_gallery_caption_text( $first, $caption_source );

/* Featured <img> (JS swaps src/srcset/alt on thumb click). */
$stage_alt = $first['alt'] !== '' ? $first['alt'] : ( $first['title'] !== '' ? $first['title'] : $first_caption );
$stage_img = '<img class="fw-gallery__stage-img" src="' . esc_url( $first['url'] ) . '"'
	. ( $first['srcset'] !== '' ? ' srcset="' . esc_attr( $first['srcset'] ) . '"' : '' )
	. ( $first['sizes'] !== '' ? ' sizes="' . esc_attr( $first['sizes'] ) . '"' : '' )
	. ' alt="' . esc_attr( $stage_alt ) . '" decoding="async" />';
?>
<div <?php echo fw_attr_to_html( $attr ); ?>>
	<?php if ( $container_cls ) : ?><div class="<?php echo esc_attr( $container_cls ); ?>"><?php endif; ?>

		<div class="<?php echo esc_attr( $wrap_class ); ?>" style="<?php echo esc_attr( $wrap_style ); ?>" data-gallery-showcase data-click="<?php echo esc_attr( $click_action ); ?>">

			<?php
			// --- Stage (featured image) ---
			$stage_media = '<div class="fw-gallery__stage-media ' . esc_attr( $rounded ) . ( $hover_zoom ? ' fw-gallery--zoom' : '' ) . '">' . $stage_img;
			if ( $captions === 'hover' && $first_caption !== '' ) {
				$stage_media .= '<span class="fw-gallery__stage-caption' . ( $caption_class_extra !== '' ? ' ' . esc_attr( $caption_class_extra ) : '' ) . '"'
					. ( $caption_style_extra !== '' ? ' style="' . esc_attr( $caption_style_extra ) . '"' : '' ) . '>' . esc_html( $first_caption ) . '</span>';
			}
			$stage_media .= '</div>';

			if ( $click_action === 'lightbox' ) {
				echo '<button type="button" class="fw-gallery__stage" data-index="0" aria-label="' . esc_attr__( 'Open image in lightbox', 'fw' ) . '">' . $stage_media . '</button>';

				// Hidden source anchors — the lightbox group lightbox.js reads.
				echo '<div class="fw-gallery__lb-sources" hidden aria-hidden="true">';
				foreach ( $items as $item ) {
					$cap = sc_gallery_caption_text( $item, $caption_source );
					echo '<a data-fw-lightbox="' . esc_attr( $lightbox_group ) . '" href="' . esc_url( $item['full'] ) . '"'
						. ( $cap !== '' ? ' data-fw-caption="' . esc_attr( $cap ) . '"' : '' ) . '></a>';
				}
				echo '</div>';
			} elseif ( $click_action === 'file' || $click_action === 'attachment' ) {
				$href = ( $click_action === 'attachment' && $first['id'] ) ? get_attachment_link( $first['id'] ) : $first['full'];
				echo '<a class="fw-gallery__stage" data-index="0" href="' . esc_url( $href ) . '"'
					. ( $click_action === 'file' ? ' target="_blank" rel="noopener noreferrer"' : '' ) . '>' . $stage_media . '</a>';
			} else {
				echo '<div class="fw-gallery__stage" data-index="0">' . $stage_media . '</div>';
			}

			// --- Thumbnail strip (a group of buttons that swap the stage image, not a
			// tab widget — the stage is not a tabpanel, so role="group" is the honest role). ---
			echo '<div class="fw-gallery__thumbs" role="group" aria-label="' . esc_attr__( 'Gallery thumbnails', 'fw' ) . '">';
			foreach ( $items as $idx => $item ) {
				$thumb_src = $item['url'];
				if ( $item['id'] ) {
					$tsrc = wp_get_attachment_image_src( $item['id'], 'thumbnail' );
					if ( $tsrc ) {
						$thumb_src = $tsrc[0];
					}
				}
				$cap = sc_gallery_caption_text( $item, $caption_source );
				echo '<button type="button" class="fw-gallery__thumb' . ( $idx === 0 ? ' is-active' : '' ) . '"'
					. ' data-index="' . esc_attr( $idx ) . '"'
					. ' data-full="' . esc_url( $item['full'] ) . '"'
					. ' data-src="' . esc_url( $item['url'] ) . '"'
					. ( $item['srcset'] !== '' ? ' data-srcset="' . esc_attr( $item['srcset'] ) . '"' : '' )
					. ( $cap !== '' ? ' data-caption="' . esc_attr( $cap ) . '"' : '' )
					. ' aria-label="' . esc_attr( sprintf( __( 'View image %d', 'fw' ), $idx + 1 ) ) . '">'
					. '<img src="' . esc_url( $thumb_src ) . '" alt="" loading="lazy" decoding="async" /></button>';
			}
			echo '</div>';
			?>

		</div>
	<?php if ( $container_cls ) : ?></div><?php endif; ?>
</div>
