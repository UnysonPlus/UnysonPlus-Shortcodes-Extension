<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Social Share — network catalog (single source of truth).
 *
 *   key => array(
 *     'label' : human name (also the multi-select choice + optional button label)
 *     'color' : brand color (used by the "brand" / "soft" / "outline" designs)
 *     'icon'  : inline SVG glyph (24×24, currentColor)
 *     'url'   : sprintf template with %1$s = url, %2$s = title (both URL-encoded);
 *               '' for the special Copy-link action (handled by JS)
 *     'window': bool — open in a small popup window (vs default navigation)
 *   )
 *
 * options.php builds the network multi-select choices from this; view.php renders
 * the selected ones (in the saved order) into share links.
 */
return array(
	'facebook' => array(
		'label'  => __( 'Facebook', 'fw' ),
		'color'  => '#1877f2',
		'icon'   => '<svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><path fill="currentColor" d="M22 12.06C22 6.5 17.52 2 12 2S2 6.5 2 12.06c0 5 3.66 9.15 8.44 9.94v-7.03H7.9v-2.9h2.54V9.85c0-2.51 1.49-3.9 3.78-3.9 1.1 0 2.24.2 2.24.2v2.46h-1.26c-1.24 0-1.63.78-1.63 1.57v1.88h2.78l-.44 2.9h-2.34V22c4.78-.79 8.44-4.94 8.44-9.94Z"/></svg>',
		'url'    => 'https://www.facebook.com/sharer/sharer.php?u=%1$s',
		'window' => true,
	),
	'twitter' => array(
		'label'  => __( 'X / Twitter', 'fw' ),
		'color'  => '#000000',
		'icon'   => '<svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><path fill="currentColor" d="M18.24 2.25h3.31l-7.23 8.26 8.5 11.24h-6.66l-5.21-6.82-5.96 6.82H1.68l7.73-8.84L1.25 2.25h6.83l4.71 6.23 5.45-6.23Zm-1.16 17.52h1.83L7.01 4.13H5.04l12.04 15.64Z"/></svg>',
		'url'    => 'https://twitter.com/intent/tweet?url=%1$s&text=%2$s',
		'window' => true,
	),
	'linkedin' => array(
		'label'  => __( 'LinkedIn', 'fw' ),
		'color'  => '#0a66c2',
		'icon'   => '<svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><path fill="currentColor" d="M20.45 20.45h-3.56v-5.57c0-1.33-.02-3.04-1.85-3.04-1.85 0-2.14 1.45-2.14 2.94v5.67H9.35V9h3.41v1.56h.05c.48-.9 1.64-1.85 3.37-1.85 3.6 0 4.27 2.37 4.27 5.45v6.29ZM5.34 7.43a2.07 2.07 0 1 1 0-4.13 2.07 2.07 0 0 1 0 4.13ZM7.12 20.45H3.56V9h3.56v11.45ZM22.22 0H1.77C.8 0 0 .78 0 1.74v20.51C0 23.22.8 24 1.77 24h20.45c.98 0 1.78-.78 1.78-1.75V1.74C24 .78 23.2 0 22.22 0Z"/></svg>',
		'url'    => 'https://www.linkedin.com/sharing/share-offsite/?url=%1$s',
		'window' => true,
	),
	'pinterest' => array(
		'label'  => __( 'Pinterest', 'fw' ),
		'color'  => '#e60023',
		'icon'   => '<svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><path fill="currentColor" d="M12 2C6.48 2 2 6.48 2 12c0 4.24 2.64 7.86 6.36 9.32-.09-.79-.17-2 .03-2.86.18-.78 1.18-4.99 1.18-4.99s-.3-.6-.3-1.49c0-1.4.81-2.44 1.82-2.44.86 0 1.27.64 1.27 1.41 0 .86-.55 2.15-.83 3.34-.24 1 .5 1.81 1.48 1.81 1.78 0 3.14-1.87 3.14-4.58 0-2.39-1.72-4.07-4.18-4.07-2.85 0-4.52 2.13-4.52 4.34 0 .86.33 1.78.74 2.28.08.1.09.19.07.29-.08.33-.25 1-.28 1.14-.05.18-.15.22-.34.13-1.26-.59-2.05-2.43-2.05-3.91 0-3.18 2.31-6.1 6.66-6.1 3.5 0 6.22 2.49 6.22 5.82 0 3.47-2.19 6.27-5.23 6.27-1.02 0-1.98-.53-2.31-1.16l-.63 2.4c-.23.87-.84 1.96-1.25 2.62.94.29 1.93.45 2.97.45 5.52 0 10-4.48 10-10S17.52 2 12 2Z"/></svg>',
		'url'    => 'https://pinterest.com/pin/create/button/?url=%1$s&description=%2$s',
		'window' => true,
	),
	'whatsapp' => array(
		'label'  => __( 'WhatsApp', 'fw' ),
		'color'  => '#25d366',
		'icon'   => '<svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><path fill="currentColor" d="M.06 24l1.69-6.16a11.86 11.86 0 0 1-1.59-5.95C.16 5.34 5.5 0 12.06 0a11.82 11.82 0 0 1 8.41 3.49 11.82 11.82 0 0 1 3.48 8.41c0 6.56-5.34 11.9-11.9 11.9a11.9 11.9 0 0 1-5.69-1.45L.06 24Zm6.6-3.8c1.68.99 3.28 1.59 5.39 1.59 5.45 0 9.89-4.43 9.89-9.88a9.82 9.82 0 0 0-2.9-6.99 9.82 9.82 0 0 0-6.98-2.9c-5.46 0-9.89 4.43-9.89 9.88 0 2.22.65 3.88 1.74 5.62l-.99 3.6 3.74-.92Zm11.36-5.55c-.07-.12-.27-.2-.56-.34-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.94 1.16-.17.2-.35.22-.64.07-.3-.15-1.25-.46-2.39-1.47-.88-.79-1.48-1.76-1.65-2.06-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.07-.15-.67-1.61-.92-2.21-.24-.58-.49-.5-.67-.51l-.57-.01c-.2 0-.52.07-.79.37-.27.3-1.04 1.01-1.04 2.47 0 1.46 1.06 2.87 1.21 3.07.15.2 2.09 3.2 5.07 4.48.71.31 1.26.49 1.69.63.71.23 1.36.19 1.87.12.57-.09 1.76-.72 2.01-1.41.25-.7.25-1.29.17-1.42Z"/></svg>',
		'url'    => 'https://api.whatsapp.com/send?text=%2$s%%20%1$s',
		'window' => true,
	),
	'telegram' => array(
		'label'  => __( 'Telegram', 'fw' ),
		'color'  => '#229ed9',
		'icon'   => '<svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><path fill="currentColor" d="M11.94 2.5C6.74 2.5 2.5 6.74 2.5 11.94c0 5.2 4.24 9.44 9.44 9.44 5.2 0 9.44-4.24 9.44-9.44 0-5.2-4.24-9.44-9.44-9.44Zm4.39 6.42-1.47 6.95c-.11.49-.4.61-.81.38l-2.24-1.65-1.08 1.04c-.12.12-.22.22-.45.22l.16-2.28 4.15-3.75c.18-.16-.04-.25-.28-.09l-5.13 3.23-2.21-.69c-.48-.15-.49-.48.1-.71l8.63-3.33c.4-.15.75.09.62.7Z"/></svg>',
		'url'    => 'https://t.me/share/url?url=%1$s&text=%2$s',
		'window' => true,
	),
	'reddit' => array(
		'label'  => __( 'Reddit', 'fw' ),
		'color'  => '#ff4500',
		'icon'   => '<svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><path fill="currentColor" d="M22 11.82a2.1 2.1 0 0 0-3.55-1.5 10.3 10.3 0 0 0-5.42-1.7l.92-4.34 3.02.64a1.5 1.5 0 1 0 .16-.98l-3.38-.72a.4.4 0 0 0-.48.31l-1.02 4.8a10.36 10.36 0 0 0-5.5 1.7 2.1 2.1 0 1 0-2.32 3.45 4.13 4.13 0 0 0-.05.63c0 3.2 3.73 5.8 8.32 5.8 4.6 0 8.32-2.6 8.32-5.8 0-.21-.02-.42-.05-.63A2.1 2.1 0 0 0 22 11.82ZM7.34 13.3a1.5 1.5 0 1 1 3 0 1.5 1.5 0 0 1-3 0Zm8.38 3.96c-1.02 1.02-3.98 1.02-5 0a.36.36 0 1 1 .5-.5c.72.72 3.28.72 4 0a.36.36 0 1 1 .5.5Zm-.23-2.46a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3Z"/></svg>',
		'url'    => 'https://www.reddit.com/submit?url=%1$s&title=%2$s',
		'window' => true,
	),
	'email' => array(
		'label'  => __( 'Email', 'fw' ),
		'color'  => '#6b7280',
		'icon'   => '<svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><path fill="currentColor" d="M3 5h18a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Zm9 7.42 8-5.42H4l8 5.42ZM4 8.2V17h16V8.2l-7.44 5.05a1 1 0 0 1-1.12 0L4 8.2Z"/></svg>',
		'url'    => 'mailto:?subject=%2$s&body=%1$s',
		'window' => false,
	),
	'copy' => array(
		'label'  => __( 'Copy Link', 'fw' ),
		'color'  => '#475569',
		'icon'   => '<svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><path fill="currentColor" d="M9 7h9a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H9a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2Zm0 2v10h9V9H9ZM5 3h9a2 2 0 0 1 2 2v1h-2V5H5v10h1v2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z"/></svg>',
		'url'    => '',
		'window' => false,
	),
);
