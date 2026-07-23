<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Posts shortcode class. Its job is to register the front-end admin-ajax
 * endpoints for the pagination/filter features — `_init()` runs every request
 * (shortcodes are instantiated on init), so the endpoints exist even though the
 * shortcode's view.php only loads when a Posts block is on a page.
 *
 * How an instance is recovered on AJAX: the render (view.php) stores this
 * instance's RESOLVED atts in a transient keyed by the wrapper's DOM id, and the
 * JS posts back only that id (+ page / term). The client therefore never sends
 * atts — nothing to tamper with (no arbitrary post_type / posts_per_page / query
 * injection). Endpoints are read-only over published posts, so nopriv is intended.
 *
 * SECURITY: nonce-verified (`fw_sc_posts`); `page` is an int, `term` a sanitized
 * slug, `wrap` a sanitized class used only as a transient key. All queries force
 * post_status=publish via the stored atts / main query.
 */
class FW_Shortcode_Posts extends FW_Shortcode {

	/** @internal */
	public function _init() {
		add_action( 'wp_ajax_fw_sc_posts_loadmore',        array( $this, '_ajax_loadmore' ) );
		add_action( 'wp_ajax_nopriv_fw_sc_posts_loadmore', array( $this, '_ajax_loadmore' ) );
		add_action( 'wp_ajax_fw_sc_posts_filter',          array( $this, '_ajax_filter' ) );
		add_action( 'wp_ajax_nopriv_fw_sc_posts_filter',   array( $this, '_ajax_filter' ) );
	}

	/**
	 * Ensure the render helpers (sc_posts_render_cards + card-part dependencies)
	 * are loaded. view.php's executable tail is guarded by `isset( $atts )`, so
	 * requiring it here only pulls in its function definitions.
	 *
	 * @internal
	 */
	private function _ensure_render_loaded() {
		if ( ! function_exists( 'sc_posts_render_cards' ) ) {
			require_once dirname( __FILE__ ) . '/views/view.php';
		}
		if ( ! function_exists( 'sc_build_wrapper_attr' ) ) {
			$bh = dirname( __FILE__, 3 ) . '/includes/shortcode-build-helper.php';
			if ( file_exists( $bh ) ) {
				require_once $bh;
			}
		}
	}

	/**
	 * Load a stored instance by its wrapper id (the transient key). Returns the
	 * payload array, or null when the id is missing/expired.
	 *
	 * @internal
	 */
	private function _load_instance() {
		$wrap = isset( $_POST['wrap'] ) ? sanitize_html_class( wp_unslash( $_POST['wrap'] ) ) : '';
		if ( $wrap === '' ) {
			return null;
		}
		$data = get_transient( 'sc_posts_ax_' . $wrap );
		if ( ! is_array( $data ) || empty( $data['atts'] ) || ! is_array( $data['atts'] ) ) {
			return null;
		}
		$this->_ensure_render_loaded();
		return $data;
	}

	/**
	 * Rebuild the instance's WP_Query for a given page, optionally filtered to one
	 * taxonomy term. Reuses sc_posts_build_query_args (custom query) or the stored
	 * main-query vars (the "Posts for current page" archive pattern).
	 *
	 * @internal
	 * @return WP_Query
	 */
	private function _build_query( $data, $page, $term = '' ) {
		$atts = $data['atts'];
		$tax  = sc_get( 'cat_taxonomy', $atts, 'category' );
		$page = max( 1, (int) $page );

		if ( ! empty( $data['use_current'] ) && ! empty( $data['main_qv'] ) && is_array( $data['main_qv'] ) ) {
			$qv          = $data['main_qv'];
			$qv['paged'] = $page;
			if ( $term !== '' && $tax !== '' ) {
				$tq   = ( isset( $qv['tax_query'] ) && is_array( $qv['tax_query'] ) ) ? $qv['tax_query'] : array();
				$tq[] = array( 'taxonomy' => $tax, 'field' => 'slug', 'terms' => array( $term ) );
				$qv['tax_query'] = $tq;
			}
			return new WP_Query( $qv );
		}

		$args = sc_posts_build_query_args( $atts, $page );

		// Exclude Current — is_singular() is false during AJAX, so honour it via the
		// current post id captured at render time.
		if ( ! empty( $data['current_id'] ) && sc_get( 'exclude_current', $atts, 'yes' ) === 'yes' ) {
			$ex   = isset( $args['post__not_in'] ) ? (array) $args['post__not_in'] : array();
			$ex[] = (int) $data['current_id'];
			$args['post__not_in'] = array_values( array_unique( $ex ) );
		}

		if ( $term !== '' && $tax !== '' ) {
			$tq   = ( isset( $args['tax_query'] ) && is_array( $args['tax_query'] ) ) ? $args['tax_query'] : array();
			$tq[] = array( 'taxonomy' => $tax, 'field' => 'slug', 'terms' => array( $term ) );
			if ( count( array_filter( $tq, 'is_array' ) ) > 1 ) {
				$tq['relation'] = 'AND';
			}
			$args['tax_query'] = $tq;
		}

		return new WP_Query( $args );
	}

	/**
	 * Load More / Infinite Scroll — append the next page's cards.
	 *
	 * @internal
	 */
	public function _ajax_loadmore() {
		check_ajax_referer( 'fw_sc_posts', 'nonce' );

		$data = $this->_load_instance();
		if ( ! $data ) {
			wp_die(); // empty response → JS stops appending gracefully
		}

		$page  = max( 2, (int) ( isset( $_POST['page'] ) ? $_POST['page'] : 2 ) );
		$query = $this->_build_query( $data, $page );

		if ( ! empty( $query->posts ) ) {
			$ppp   = max( 1, (int) ( isset( $data['ppp'] ) ? $data['ppp'] : 6 ) );
			$start = ( $page - 1 ) * $ppp; // keep first-post treatments on the true first post
			echo sc_posts_render_cards( $data['atts'], $query->posts, $start ); // phpcs:ignore WordPress.Security.EscapeOutput — card markup is escaped at source
		}

		wp_reset_postdata();
		wp_die();
	}

	/**
	 * Live Filter — replace the grid with the chosen term's first page.
	 *
	 * @internal
	 */
	public function _ajax_filter() {
		check_ajax_referer( 'fw_sc_posts', 'nonce' );

		$data = $this->_load_instance();
		if ( ! $data ) {
			wp_die();
		}

		$term  = isset( $_POST['term'] ) ? sanitize_title( wp_unslash( $_POST['term'] ) ) : '';
		$query = $this->_build_query( $data, 1, $term );

		if ( empty( $query->posts ) ) {
			$no = sc_get( 'no_results_text', $data['atts'], __( 'Sorry, no posts matched your criteria.', 'fw' ) );
			echo '<div class="posts__empty">' . esc_html( $no ) . '</div>';
		} else {
			echo sc_posts_render_cards( $data['atts'], $query->posts, 0 ); // phpcs:ignore WordPress.Security.EscapeOutput — card markup is escaped at source
		}

		wp_reset_postdata();
		wp_die();
	}
}

FW_Shortcode_Posts::class; // referenced by the loader via the class name
