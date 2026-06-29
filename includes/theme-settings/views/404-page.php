<?php if ( ! defined( 'ABSPATH' ) ) {
	die( 'Forbidden' );
}
/**
 * Plugin-provided 404 template: renders the page chosen in
 * Theme Settings → Miscellaneous → 404 Page, using the active theme's header /
 * footer so it works under any theme. Selected via the template_include filter in
 * miscellaneous-handlers.php; $GLOBALS['upw_ts_404_page_id'] carries the page id.
 *
 * Status stays 404 (WordPress already set it) — a not-found URL returns 404 while
 * showing a friendly page.
 */

get_header();

$upw_ts_404_id   = isset( $GLOBALS['upw_ts_404_page_id'] ) ? (int) $GLOBALS['upw_ts_404_page_id'] : 0;
$upw_ts_404_post = $upw_ts_404_id ? get_post( $upw_ts_404_id ) : null;
?>
<div class="fw-container">
	<div class="fw-row">
		<main id="main" class="site-main content-area fw-col-md" role="main">
			<?php
			if ( $upw_ts_404_post ) :
				global $post;
				$post = $upw_ts_404_post;
				setup_postdata( $post );
				?>
				<article id="post-<?php the_ID(); ?>" <?php post_class( 'error-404 error-404--page' ); ?>>
					<header class="entry-header">
						<h1 class="entry-title"><?php the_title(); ?></h1>
					</header>
					<div class="entry-content">
						<?php the_content(); ?>
					</div>
				</article>
				<?php
				wp_reset_postdata();
			endif;
			?>
		</main>
		<?php get_sidebar(); ?>
	</div>
</div>
<?php
get_footer();
