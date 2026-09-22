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
			// The options below apply ONLY to the built-in 404 design (no page selected
			// above) — the UnysonPlus theme's 404.php reads them for its fallback markup;
			// they have no effect on other themes or on a selected replacement page.
			'404_heading' => array(
				'label' => __( 'Heading (default template only)', 'fw' ),
				'desc'  => __( 'Heading shown on the built-in 404 design. Pre-filled with the default; edit it, or clear it to fall back to the default.', 'fw' ),
				'type'  => 'text',
				// Pre-filled with the theme's default copy so it's visible and editable.
				'value' => __( 'This page wandered off.', 'fw' ),
			),
			'404_text' => array(
				'label'           => __( 'Message (default template only)', 'fw' ),
				'desc'            => __( 'Message shown under the heading. Add links, light formatting, or an image (Add Media). Clear it to fall back to the default.', 'fw' ),
				'type'            => 'wp-editor',
				'size'            => 'small',
				'editor_height'   => 160,
				'media_buttons'   => true,   // "Add Media" so an image can be inserted
				'dynamic_content' => false,  // no dynamic-content picker needed on a 404 message
				'shortcodes'      => false,
				'value'           => __( 'The page you were looking for isn\'t here. It may have moved, or it never existed. Let\'s get you back on track.', 'fw' ),
			),
			'404_show_button' => array(
				'label' => __( 'Show "Back to home" button (default template only)', 'fw' ),
				'desc'  => __( 'Show a button that links to the homepage.', 'fw' ),
				'type'  => 'switch',
				'value' => 'yes',
			),
			'404_button_label' => array(
				'label' => __( 'Button label (default template only)', 'fw' ),
				'desc'  => __( 'Text for the homepage button. Pre-filled with the default; edit it, or clear it to fall back to the default.', 'fw' ),
				'type'  => 'text',
				'value' => __( 'Back to home', 'fw' ),
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
