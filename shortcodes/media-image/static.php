<?php if ( ! defined( 'FW' ) ) { die( 'Forbidden' ); }

// Media Image has no stylesheet of its own — it only needs the `.img-fluid`
// responsive-image helper, which lives in the builder's frontend-grid sheet
// the plugin ships in place of Bootstrap.
if ( ! is_admin() ) {
	wp_enqueue_style( 'fw-ext-builder-frontend-grid' );
}
