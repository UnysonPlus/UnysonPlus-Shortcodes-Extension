<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * @var array $atts
 */

/** @var FW_Extension_Shortcodes $shortcodes */
$shortcodes = fw_ext( 'shortcodes' );
/** @var FW_Shortcode_Table $table */
$table = $shortcodes->get_shortcode( 'table' );

// Build wrapper attributes
$atts['base_class']       = 'pricing';
$atts['unique_id_prefix'] = 'pri-';
$attr = sc_build_wrapper_attr( $atts );

// CSS-grid columns (replaces the Bootstrap-style fw-col-sm-N on each package).
// One track per column: a description/label column stays narrow; plan columns
// share the remaining space equally. Emitted as a custom property the pricing
// CSS reads, so the whole layout is self-contained (no .fw- grid classes) and
// collapses to a single column on mobile.
$pt_tracks = array();
foreach ( $atts['table']['cols'] as $col ) {
	$pt_tracks[] = ( isset( $col['name'] ) && $col['name'] === 'desc-col' )
		? 'minmax(0, 225px)'
		: 'minmax(0, 1fr)';
}
$pt_decl       = '--pt-tracks:' . implode( ' ', $pt_tracks ) . ';';
$attr['style'] = ( isset( $attr['style'] ) && $attr['style'] !== '' ) ? rtrim( $attr['style'], '; ' ) . ';' . $pt_decl : $pt_decl;
?>

<div <?php echo fw_attr_to_html( $attr ); ?>>
	<?php foreach ( $atts['table']['cols'] as $col_key => $col ): ?>
        <div class="package-wrap <?php echo esc_attr( $col['name'] ); ?>">
            <div class="package">
				<?php foreach ( $atts['table']['rows'] as $row_key => $row ): ?>
					<?php if ( $col['name'] == 'desc-col' ) : ?>
                        <div class="default-row">
							<?php echo wp_kses_post( fw_akg( 'textarea', $atts['table']['content'][ $row_key ][ $col_key ], '' ) ); ?>
                        </div>
						<?php continue; endif; ?>
					<?php if ( $row['name'] === 'heading-row' ): ?>
                        <div class="heading-row">
                            <span>
								<?php echo wp_kses_post( fw_akg(
									'textarea',
									$atts['table']['content'][ $row_key ][ $col_key ],
									$col['name'] === 'desc-col' ? '&nbps;' : ''
								) ); ?>
							</span>
                        </div>
					<?php elseif ( $row['name'] === 'pricing-row' ): ?>
                        <div class="pricing-row">
                            <span><?php echo wp_kses_post( fw_akg(
		                            'amount',
		                            $atts['table']['content'][ $row_key ][ $col_key ],
		                            $col['name'] === 'desc-col' ? '&nbps;' : ''
	                            ) ) ?></span>
                            <small><?php echo wp_kses_post( fw_akg(
									'description',
									$atts['table']['content'][ $row_key ][ $col_key ],
									$col['name'] === 'desc-col' ? '&nbps;' : ''
								) ) ?></small>
                        </div>
					<?php elseif ( $row['name'] == 'button-row' ) : ?>
						<?php if ( $button = $table->get_button_shortcode() ): ?>
                            <div class="button-row">
								<?php if ( false === empty( $atts['table']['content'][ $row_key ][ $col_key ]['button'] ) and false === empty( $button ) ) : ?>
									<?php echo $button->render( $atts['table']['content'][ $row_key ][ $col_key ]['button'] ); // shortcode-rendered HTML, safe ?>
								<?php else : ?>
                                    <span>&nbsp;</span>
								<?php endif; ?>
                            </div>
						<?php endif; ?>
					<?php elseif ( $row['name'] === 'switch-row' ) : ?>
                        <div class="switch-row">
							<?php $value = $atts['table']['content'][ $row_key ][ $col_key ]['switch']; ?>
                            <span>
								<i class="fa price-icon-<?php echo esc_attr( $value ) ?>"></i>
							</span>
                        </div>
					<?php elseif ( $row['name'] === 'default-row' ) : ?>
                        <div class="default-row"><?php
							echo wp_kses_post( fw_akg( "textarea", $atts['table']['content'][ $row_key ][ $col_key ] ) )
							?></div>
					<?php endif; ?>
				<?php endforeach; ?>
            </div>
        </div>
	<?php endforeach; ?>
</div>