<?php if ( ! defined( 'FW' ) ) { die( 'Forbidden' ); }

// Image+Content lays out with its OWN CSS grid (static/css/styles.css) — no
// framework grid classes (.fw-row / .fw-col-* / .fw-order-*), so its structure
// is self-contained and renders correctly anywhere. The frontend-grid sheet is
// still enqueued for the generic text-alignment (.text-*) and spacing (.p-*)
// utilities the content column may use.
if ( ! is_admin() ) {
	$ext = fw_ext( 'shortcodes' );
	if ( $ext ) {
		wp_enqueue_style(
			'fw-shortcode-image-content',
			$ext->get_declared_URI( '/shortcodes/image-content/static/css/styles.css' ),
			array(),
			$ext->manifest->get_version()
		);
	}
	wp_enqueue_style( 'fw-ext-builder-frontend-grid' );
}
