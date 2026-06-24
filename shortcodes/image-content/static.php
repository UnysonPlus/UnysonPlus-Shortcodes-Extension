<?php if ( ! defined( 'FW' ) ) { die( 'Forbidden' ); }

// Image+Content has no stylesheet of its own — its layout/utility classes
// (the .fw- grid plus flex / alignment / sizing / img-fluid / rounded / shadow
// utilities) all live in the builder's frontend-grid sheet, which the plugin
// ships in place of Bootstrap. Enqueue it so the shortcode renders correctly
// even when used outside a Section/Column that would already pull it in.
if ( ! is_admin() ) {
	wp_enqueue_style( 'fw-ext-builder-frontend-grid' );
}
