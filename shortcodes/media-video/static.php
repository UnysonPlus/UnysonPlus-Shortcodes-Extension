<?php if ( ! defined( 'FW' ) ) { die( 'Forbidden' ); }

// Media Video has no stylesheet of its own — it only needs the `.ratio*`
// aspect-ratio container and `.mx-auto` helper, which live in the builder's
// frontend-grid sheet the plugin ships in place of Bootstrap.
if ( ! is_admin() ) {
	wp_enqueue_style( 'fw-ext-builder-frontend-grid' );
}
