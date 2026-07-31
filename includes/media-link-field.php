<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

if ( defined( 'UPW_MEDIA_LINK_FIELD' ) ) { return; }
define( 'UPW_MEDIA_LINK_FIELD', true );

/**
 * Adds a "Link URL" field + an "Open link in a new tab" checkbox to every image in the Media
 * Library (the attachment edit screen AND the media modal), stored as `_upw_link_url` /
 * `_upw_link_new_tab` attachment meta.
 *
 * Gallery elements read them when their card-click action is "Open Link": the URL — and its
 * new-tab preference — live WITH the image, right where Alt/Caption are edited, inside the
 * builder's normal "Edit image" flow. Per-image new-tab means one gallery can freely mix
 * internal (same tab) and external links; external hosts always open a new tab regardless
 * (the site-wide convention).
 */

add_filter( 'attachment_fields_to_edit', function ( $fields, $post ) {
	if ( strpos( (string) $post->post_mime_type, 'image/' ) !== 0 ) {
		return $fields;
	}
	$fields['upw_link_url'] = array(
		'label' => __( 'Link URL', 'fw' ),
		'input' => 'text',
		'value' => (string) get_post_meta( $post->ID, '_upw_link_url', true ),
		'helps' => __( 'Where this image links when a gallery\'s Card Click is set to "Open Link".', 'fw' ),
	);
	/* Checkbox via a custom html input. The hidden input in front guarantees the key is ALWAYS
	 * submitted (an unchecked checkbox alone would vanish from the form post, making "unchecked"
	 * indistinguishable from "field not rendered" in the save handler). */
	$checked = get_post_meta( $post->ID, '_upw_link_new_tab', true ) === '1';
	$name    = 'attachments[' . (int) $post->ID . '][upw_link_new_tab]';
	$fields['upw_link_new_tab'] = array(
		'label' => __( 'Open link in a new tab', 'fw' ),
		'input' => 'html',
		'html'  => '<input type="hidden" name="' . esc_attr( $name ) . '" value="0" />'
			. '<label><input type="checkbox" name="' . esc_attr( $name ) . '" value="1" ' . checked( $checked, true, false ) . ' /> '
			. esc_html__( 'Open this image\'s Link URL in a new tab', 'fw' ) . '</label>',
		'helps' => __( 'External links already open a new tab automatically — tick this to force it for internal links too.', 'fw' ),
	);
	return $fields;
}, 10, 2 );

add_filter( 'attachment_fields_to_save', function ( $post, $attachment ) {
	if ( isset( $attachment['upw_link_url'] ) ) {
		$url = esc_url_raw( trim( (string) $attachment['upw_link_url'] ) );
		if ( $url === '' ) {
			delete_post_meta( $post['ID'], '_upw_link_url' );
		} else {
			update_post_meta( $post['ID'], '_upw_link_url', $url );
		}
	}
	if ( isset( $attachment['upw_link_new_tab'] ) ) {
		if ( (string) $attachment['upw_link_new_tab'] === '1' ) {
			update_post_meta( $post['ID'], '_upw_link_new_tab', '1' );
		} else {
			delete_post_meta( $post['ID'], '_upw_link_new_tab' );
		}
	}
	return $post;
}, 10, 2 );
