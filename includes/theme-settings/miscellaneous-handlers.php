<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Front-end handlers for the built-in Miscellaneous features (Custom CSS, Custom
 * Scripts, Analytics & Tracking). Plugin-owned, so they run under ANY active theme.
 *
 * Values are read from the SAME theme-scoped multi buckets the theme used
 * (misc_custom_css / misc_custom_scripts / misc_analytics) via upw_ts_setting(), so
 * existing saved values keep working with no migration. Required once by loader.php.
 */

/* ===================== Custom CSS ===================== */

if ( ! function_exists( 'upw_ts_custom_css' ) ) :
	function upw_ts_custom_css() {
		$css = trim( (string) upw_ts_setting( 'misc_custom_css', 'custom_css', '' ) );
		return $css === '' ? '' : wp_strip_all_tags( $css );
	}
endif;

// Fold the site-wide Custom CSS into the plugin's combined presets stylesheet
// (css-tokens.php applies this filter), so it is combiner-absorbed rather than its
// own inline <style> block.
add_filter( 'unysonplus_global_css', function ( $css ) {
	$own = upw_ts_custom_css();
	if ( $own === '' ) {
		return $css;
	}
	return ( '' === (string) $css ) ? $own : $css . "\n" . $own;
} );

/* ============== Custom Head / Body-Open / Footer Scripts ============== */

add_action( 'wp_head', function () {
	$html = (string) upw_ts_setting( 'misc_custom_scripts', 'custom_head_scripts', '' );
	if ( trim( $html ) !== '' ) {
		echo "\n" . $html . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- verbatim user scripts by design
	}
}, 999 );

add_action( 'wp_body_open', function () {
	$html = (string) upw_ts_setting( 'misc_custom_scripts', 'custom_body_open_scripts', '' );
	if ( trim( $html ) !== '' ) {
		echo "\n" . $html . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- verbatim user scripts by design
	}
}, 5 );

add_action( 'wp_footer', function () {
	$html = (string) upw_ts_setting( 'misc_custom_scripts', 'custom_footer_scripts', '' );
	if ( trim( $html ) !== '' ) {
		echo "\n" . $html . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- verbatim user scripts by design
	}
}, 999 );

/* ===================== Analytics & Tracking ===================== */

add_action( 'wp_head', function () {
	$ga4     = trim( (string) upw_ts_setting( 'misc_analytics', 'analytics_ga4_id', '' ) );
	$gtm     = trim( (string) upw_ts_setting( 'misc_analytics', 'analytics_gtm_id', '' ) );
	$pixel   = trim( (string) upw_ts_setting( 'misc_analytics', 'analytics_meta_pixel_id', '' ) );
	$clarity = trim( (string) upw_ts_setting( 'misc_analytics', 'analytics_clarity_id', '' ) );

	if ( $ga4 !== '' ) {
		$id = preg_replace( '/[^A-Za-z0-9_-]/', '', $ga4 );
		?>
<!-- Google Analytics 4 -->
<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_attr( $id ); ?>"></script>
<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','<?php echo esc_js( $id ); ?>');</script>
		<?php
	}

	if ( $gtm !== '' ) {
		$id = preg_replace( '/[^A-Za-z0-9_-]/', '', $gtm );
		?>
<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','<?php echo esc_js( $id ); ?>');</script>
		<?php
	}

	if ( $pixel !== '' ) {
		$id = preg_replace( '/[^0-9]/', '', $pixel );
		?>
<!-- Meta Pixel -->
<script>!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');fbq('init','<?php echo esc_js( $id ); ?>');fbq('track','PageView');</script>
		<?php
	}

	if ( $clarity !== '' ) {
		$id = preg_replace( '/[^A-Za-z0-9]/', '', $clarity );
		?>
<!-- Microsoft Clarity -->
<script>(function(c,l,a,r,i,t,y){c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y)})(window,document,"clarity","script","<?php echo esc_js( $id ); ?>");</script>
		<?php
	}
}, 25 );

add_action( 'wp_body_open', function () {
	$gtm   = trim( (string) upw_ts_setting( 'misc_analytics', 'analytics_gtm_id', '' ) );
	$pixel = trim( (string) upw_ts_setting( 'misc_analytics', 'analytics_meta_pixel_id', '' ) );

	if ( $gtm !== '' ) {
		$id = preg_replace( '/[^A-Za-z0-9_-]/', '', $gtm );
		echo '<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=' . esc_attr( $id ) . '" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>' . "\n";
	}
	if ( $pixel !== '' ) {
		$id = preg_replace( '/[^0-9]/', '', $pixel );
		echo '<noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=' . esc_attr( $id ) . '&ev=PageView&noscript=1" alt=""></noscript>' . "\n";
	}
}, 10 );

/* ===================== Performance Tweaks ===================== */

add_action( 'init', function () {
	if ( upw_ts_setting( 'misc_performance', 'perf_disable_emojis' ) === 'yes' ) {
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		remove_action( 'admin_print_styles', 'print_emoji_styles' );
		remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
		remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
		remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
	}
	if ( upw_ts_setting( 'misc_performance', 'perf_disable_embeds' ) === 'yes' ) {
		remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
		remove_action( 'wp_head', 'wp_oembed_add_host_js' );
		add_action( 'wp_enqueue_scripts', function () { wp_dequeue_script( 'wp-embed' ); }, 100 );
	}
	if ( upw_ts_setting( 'misc_performance', 'perf_remove_rsd_wlw' ) === 'yes' ) {
		remove_action( 'wp_head', 'rsd_link' );
		remove_action( 'wp_head', 'wlwmanifest_link' );
	}
	if ( upw_ts_setting( 'misc_performance', 'perf_remove_version_meta' ) === 'yes' ) {
		remove_action( 'wp_head', 'wp_generator' );
	}
	if ( upw_ts_setting( 'misc_performance', 'perf_disable_xmlrpc' ) === 'yes' ) {
		add_filter( 'xmlrpc_enabled', '__return_false' );
	}
}, 15 );

add_action( 'wp_default_scripts', function ( $scripts ) {
	if ( is_admin() ) {
		return;
	}
	if ( upw_ts_setting( 'misc_performance', 'perf_disable_jquery_migrate' ) !== 'yes' ) {
		return;
	}
	if ( ! empty( $scripts->registered['jquery'] ) && ! empty( $scripts->registered['jquery']->deps ) ) {
		$scripts->registered['jquery']->deps = array_diff( $scripts->registered['jquery']->deps, array( 'jquery-migrate' ) );
	}
} );

/* ===================== Maintenance Mode ===================== */

if ( ! function_exists( 'upw_ts_maintenance_user_is_allowed' ) ) :
	function upw_ts_maintenance_user_is_allowed() {
		if ( ! is_user_logged_in() ) {
			return false;
		}
		$allowed = upw_ts_setting( 'misc_maintenance', 'maintenance_allowed_roles', array( 'administrator' ) );
		if ( ! is_array( $allowed ) ) {
			$allowed = array( 'administrator' );
		}
		$user = wp_get_current_user();
		if ( ! $user || empty( $user->roles ) ) {
			return false;
		}
		return (bool) array_intersect( $allowed, (array) $user->roles );
	}
endif;

add_action( 'template_redirect', function () {
	if ( upw_ts_setting( 'misc_maintenance', 'maintenance_enabled' ) !== 'yes' ) {
		return;
	}
	if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
		return;
	}
	if ( wp_doing_ajax() || wp_doing_cron() ) {
		return;
	}
	if ( isset( $GLOBALS['pagenow'] ) && $GLOBALS['pagenow'] === 'wp-login.php' ) {
		return;
	}
	if ( upw_ts_maintenance_user_is_allowed() ) {
		return;
	}

	$title    = (string) upw_ts_setting( 'misc_maintenance', 'maintenance_title', __( "We'll be right back", 'fw' ) );
	$message  = (string) upw_ts_setting( 'misc_maintenance', 'maintenance_message', '' );
	$logo     = upw_ts_setting( 'misc_maintenance', 'maintenance_logo', '' );
	$logo_url = ( is_array( $logo ) && ! empty( $logo['url'] ) ) ? $logo['url'] : '';

	nocache_headers();
	status_header( 503 );
	header( 'Retry-After: 3600' );
	header( 'Content-Type: text/html; charset=' . get_bloginfo( 'charset' ) );
	?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<meta name="robots" content="noindex,nofollow">
	<title><?php echo esc_html( $title ); ?> &mdash; <?php bloginfo( 'name' ); ?></title>
	<style>
		html,body{margin:0;padding:0;height:100%;font-family:system-ui,-apple-system,"Segoe UI",Roboto,sans-serif;background:#f5f5f5;color:#212529;}
		body{display:flex;align-items:center;justify-content:center;padding:2rem;}
		.maintenance-page{max-width:600px;width:100%;background:#fff;border-radius:8px;padding:2.5rem;text-align:center;box-shadow:0 4px 24px rgba(0,0,0,0.08);}
		.maintenance-page__logo{max-width:200px;height:auto;margin:0 auto 1.5rem;display:block;}
		.maintenance-page__title{margin:0 0 1rem;font-size:1.75rem;}
		.maintenance-page__body{font-size:1rem;line-height:1.6;color:#555;}
		.maintenance-page__body :last-child{margin-bottom:0;}
	</style>
</head>
<body>
	<main class="maintenance-page" role="main">
		<?php if ( $logo_url ) : ?>
			<img class="maintenance-page__logo" src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
		<?php endif; ?>
		<h1 class="maintenance-page__title"><?php echo esc_html( $title ); ?></h1>
		<div class="maintenance-page__body"><?php echo wp_kses_post( wpautop( $message ) ); ?></div>
	</main>
</body>
</html>
	<?php
	exit;
}, 0 );

/* ===================== 404 Page (selector) ===================== */

// When a 404 page is selected, render it on ANY theme via a plugin template that
// uses the active theme's header/footer. The two "default template only" switches
// are left to the active theme's own 404 template (read from the same misc_404 keys).
add_filter( 'template_include', function ( $template ) {
	if ( ! is_404() ) {
		return $template;
	}
	$id = (int) upw_ts_setting( 'misc_404', '404_page_id', 0 );
	if ( $id <= 0 ) {
		return $template;
	}
	$post = get_post( $id );
	if ( ! $post || $post->post_status !== 'publish' || $post->post_type !== 'page' ) {
		return $template;
	}
	$GLOBALS['upw_ts_404_page_id'] = $id;
	$view = __DIR__ . '/views/404-page.php';
	return is_file( $view ) ? $view : $template;
}, 99 );

/* ===================== Media — Custom Image Sizes ===================== */
/*
 * Schema in miscellaneous-media.php (Miscellaneous → Media). Registers the sizes
 * defined under the `theme_image_sizes` key and — for sizes with "Show in editor"
 * on — exposes them in the media library / block editor size dropdown. Read via
 * fw_get_db_settings_option( 'theme_image_sizes' ), the same key the theme used, so
 * existing saves carry over with no migration. Registered on `init` (fires reliably
 * after the extension loads) rather than after_setup_theme.
 */

if ( ! function_exists( 'upw_ts_image_size_crop_map' ) ) :
	/** Saved crop value → add_image_size() $crop argument. */
	function upw_ts_image_size_crop_map() {
		return array(
			'false'         => false,
			'true'          => true,
			'top-left'      => array( 'left', 'top' ),
			'top-center'    => array( 'center', 'top' ),
			'top-right'     => array( 'right', 'top' ),
			'center-left'   => array( 'left', 'center' ),
			'center'        => array( 'center', 'center' ),
			'center-right'  => array( 'right', 'center' ),
			'bottom-left'   => array( 'left', 'bottom' ),
			'bottom-center' => array( 'center', 'bottom' ),
			'bottom-right'  => array( 'right', 'bottom' ),
		);
	}
endif;

if ( ! function_exists( 'upw_ts_register_image_sizes' ) ) :
	function upw_ts_register_image_sizes() {
		if ( ! function_exists( 'fw_get_db_settings_option' ) ) { return; }
		$sizes = fw_get_db_settings_option( 'theme_image_sizes' );
		if ( empty( $sizes ) || ! is_array( $sizes ) ) { return; }

		$crop_map = upw_ts_image_size_crop_map();
		$reserved = array( 'thumbnail', 'medium', 'medium_large', 'large', 'full', 'post-thumbnail', '1536x1536', '2048x2048' );

		foreach ( $sizes as $size ) {
			if ( empty( $size['name'] ) ) { continue; }
			$name   = sanitize_title_with_dashes( $size['name'] );
			$width  = (int) preg_replace( '/[^0-9]/', '', isset( $size['width'] ) ? $size['width'] : '' );
			$height = (int) preg_replace( '/[^0-9]/', '', isset( $size['height'] ) ? $size['height'] : '' );
			if ( $name === '' || in_array( $name, $reserved, true ) || ( $width === 0 && $height === 0 ) ) { continue; }
			$crop_key = isset( $size['crop'] ) ? $size['crop'] : 'false';
			$crop     = array_key_exists( $crop_key, $crop_map ) ? $crop_map[ $crop_key ] : false;
			add_image_size( $name, $width, $height, $crop );
		}
	}
endif;
add_action( 'init', 'upw_ts_register_image_sizes', 20 );

if ( ! function_exists( 'upw_ts_selectable_image_sizes' ) ) :
	/**
	 * Expose custom sizes (with "Show in editor" on) in the media / block-editor
	 * size dropdown. Without this a registered size is usable only from template code.
	 *
	 * @param array $sizes slug => label map WordPress offers in the picker.
	 * @return array
	 */
	function upw_ts_selectable_image_sizes( $sizes ) {
		if ( ! function_exists( 'fw_get_db_settings_option' ) ) { return $sizes; }
		$defined = fw_get_db_settings_option( 'theme_image_sizes' );
		if ( empty( $defined ) || ! is_array( $defined ) ) { return $sizes; }

		$reserved = array( 'thumbnail', 'medium', 'medium_large', 'large', 'full', 'post-thumbnail' );
		foreach ( $defined as $size ) {
			if ( empty( $size['name'] ) ) { continue; }
			if ( isset( $size['show_in_editor'] ) && $size['show_in_editor'] === 'no' ) { continue; }
			$slug = sanitize_title_with_dashes( $size['name'] );
			if ( $slug === '' || in_array( $slug, $reserved, true ) ) { continue; }
			$sizes[ $slug ] = $size['name']; // human label as typed
		}
		return $sizes;
	}
endif;
add_filter( 'image_size_names_choose', 'upw_ts_selectable_image_sizes' );
