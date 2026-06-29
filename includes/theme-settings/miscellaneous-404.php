<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Theme Settings → Miscellaneous → 404 Page.
 *
 * Stored theme-scoped under the `misc_404` multi container — the SAME keys the theme
 * used, so existing values carry over with no migration. The page-selector
 * (404_page_id) is rendered on ANY theme by the template_include handler in
 * miscellaneous-handlers.php. The two "default template only" switches are still read
 * by the UnysonPlus theme's 404.php for its fallback markup (no effect on other themes).
 *
 * @var array $options Filled with the option schema (loaded via upw_ts_get_options()).
 */

// Build the page dropdown from published pages.
$pages_choices = array( '' => __( '— Use default 404 template —', 'fw' ) );
$pages = get_pages( array( 'sort_column' => 'post_title', 'sort_order' => 'ASC' ) );
if ( is_array( $pages ) ) {
	foreach ( $pages as $page ) {
		$pages_choices[ $page->ID ] = $page->post_title;
	}
}

$options = array(
	'misc_404' => array(
		'type'          => 'multi',
		'label'         => false,
		'inner-options' => array(
			'404_page_id' => array(
				'label'   => __( 'Use this page as the 404', 'fw' ),
				'desc'    => __( 'Pick a regular WordPress page to render in place of the default 404 template (works on any active theme).', 'fw' ),
				'type'    => 'select',
				'value'   => '',
				'choices' => $pages_choices,
			),
			'404_show_search' => array(
				'label' => __( 'Show search form (default template only)', 'fw' ),
				'desc'  => __( 'Applies only when no page is selected above and the active theme uses its built-in 404 design.', 'fw' ),
				'type'  => 'switch',
				'value' => 'yes',
			),
			'404_show_recent_posts' => array(
				'label' => __( 'Show recent posts (default template only)', 'fw' ),
				'desc'  => __( 'Applies only when no page is selected above and the active theme uses its built-in 404 design.', 'fw' ),
				'type'  => 'switch',
				'value' => 'no',
			),
		),
	),
);
