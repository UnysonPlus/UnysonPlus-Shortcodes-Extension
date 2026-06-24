<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/** @var array $atts */

if ( ! function_exists( 'sc_get' ) ) {
	function sc_get( $path, $atts, $default = '' ) {
		if ( function_exists( 'fw_akg' ) ) {
			$v = fw_akg( $path, $atts, null );
			if ( $v !== null ) { return $v; }
		}
		return $default;
	}
}

if ( ! function_exists( 'sc_ct_cell' ) ) {
	/** Render one cell from its raw token. */
	function sc_ct_cell( $raw ) {
		$check = '<svg class="fw-ct__check" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 13l4 4L19 7"/></svg>';
		$cross = '<svg class="fw-ct__cross" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>';
		$t = trim( (string) $raw );
		$l = strtolower( $t );
		if ( $t === '' ) { return '<span class="fw-ct__dash">&ndash;</span>'; }
		if ( in_array( $l, array( 'yes', 'y', 'true', 'check', 'tick', '✓', '✔' ), true ) ) {
			return $check . '<span class="screen-reader-text">' . esc_html__( 'Yes', 'fw' ) . '</span>';
		}
		if ( in_array( $l, array( 'no', 'n', 'false', 'cross', 'x', '✗', '✘' ), true ) ) {
			return $cross . '<span class="screen-reader-text">' . esc_html__( 'No', 'fw' ) . '</span>';
		}
		if ( in_array( $l, array( '-', '–', '—', 'dash', 'n/a', 'na' ), true ) ) {
			return '<span class="fw-ct__dash">&ndash;</span>';
		}
		return '<span class="fw-ct__val">' . esc_html( $t ) . '</span>';
	}
}

if ( ! function_exists( 'sc_ct_render' ) ) {
	function sc_ct_render( $atts ) {
		$columns = sc_get( 'columns', $atts, array() );
		$rows    = sc_get( 'rows', $atts, array() );
		if ( ! is_array( $columns ) ) { $columns = array(); }
		if ( ! is_array( $rows ) ) { $rows = array(); }

		if ( empty( $columns ) ) {
			if ( is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
				return '<div class="fw-ct__empty">' . esc_html__( 'Add at least one column and one row.', 'fw' ) . '</div>';
			}
			return '';
		}

		$first_label = sc_get( 'first_col_label', $atts, __( 'Features', 'fw' ) );
		$style       = sc_get( 'style', $atts, 'bordered' );
		$hl          = sc_get( 'highlight_featured', $atts, 'yes' ) === 'yes';
		$sticky      = sc_get( 'sticky_header', $atts, 'no' ) === 'yes';
		$center      = sc_get( 'center_cells', $atts, 'yes' ) === 'yes';
		$col_count   = count( $columns );

		$var = function ( $key, $name ) use ( $atts ) {
			$raw = sc_get( $key, $atts, '' );
			if ( is_array( $raw ) && ! empty( $raw['custom'] ) ) {
				$hex = preg_replace( '/[^#0-9a-zA-Z(),.%\s-]/', '', (string) $raw['custom'] );
				if ( $hex !== '' ) { return $name . ':' . $hex . ';'; }
			}
			return '';
		};
		$style_var  = $var( 'accent_color', '--ct-accent' );
		$style_var .= $var( 'header_bg', '--ct-head-bg' );
		$style_var .= $var( 'header_text', '--ct-head-text' );
		$style_var .= $var( 'text_color', '--ct-text' );
		$style_var .= $var( 'border_color', '--ct-border' );

		$classes = array(
			'fw-ct',
			'fw-ct--style-' . sanitize_html_class( $style ),
		);
		if ( $hl )     { $classes[] = 'fw-ct--highlight'; }
		if ( $sticky ) { $classes[] = 'fw-ct--sticky'; }
		if ( $center ) { $classes[] = 'fw-ct--center'; }

		$atts['base_class']       = 'comparison-table';
		$atts['unique_id_prefix'] = 'ct-';
		$atts['css_class']        = trim( implode( ' ', $classes ) . ' ' . ( isset( $atts['css_class'] ) ? $atts['css_class'] : '' ) );
		$attr = sc_build_wrapper_attr( $atts );
		if ( $style_var !== '' ) {
			$attr['style'] = ( isset( $attr['style'] ) && $attr['style'] !== '' ? rtrim( $attr['style'], ';' ) . ';' : '' ) . $style_var;
		}

		$is_featured = array();
		foreach ( $columns as $ci => $c ) {
			$is_featured[ $ci ] = ( isset( $c['featured'] ) && $c['featured'] === 'yes' );
		}

		ob_start();
		echo '<div ' . fw_attr_to_html( $attr ) . '>';
		echo '<div class="fw-ct__scroll"><table class="fw-ct__table">';

		// Head
		echo '<thead><tr>';
		echo '<th class="fw-ct__corner" scope="col">' . esc_html( $first_label ) . '</th>';
		foreach ( $columns as $ci => $c ) {
			$fcls = $is_featured[ $ci ] ? ' is-featured' : '';
			echo '<th class="fw-ct__col' . $fcls . '" scope="col">';
			if ( ! empty( $c['badge'] ) ) { echo '<span class="fw-ct__badge">' . esc_html( $c['badge'] ) . '</span>'; }
			echo '<span class="fw-ct__name">' . esc_html( isset( $c['name'] ) ? $c['name'] : '' ) . '</span>';
			if ( ! empty( $c['price'] ) ) { echo '<span class="fw-ct__price">' . esc_html( $c['price'] ) . '</span>'; }
			if ( ! empty( $c['button_text'] ) && ! empty( $c['button_url'] ) ) {
				$target = ( isset( $c['button_target'] ) && $c['button_target'] === '_blank' );
				echo '<a class="fw-ct__btn" href="' . esc_url( $c['button_url'] ) . '"' . ( $target ? ' target="_blank" rel="noopener noreferrer"' : '' ) . '>' . esc_html( $c['button_text'] ) . '</a>';
			}
			echo '</th>';
		}
		echo '</tr></thead>';

		// Body
		echo '<tbody>';
		foreach ( $rows as $r ) {
			$is_heading = isset( $r['is_heading'] ) && $r['is_heading'] === 'yes';
			$label      = isset( $r['label'] ) ? (string) $r['label'] : '';
			if ( $is_heading ) {
				echo '<tr class="fw-ct__heading"><th scope="colgroup" colspan="' . ( $col_count + 1 ) . '">' . esc_html( $label ) . '</th></tr>';
				continue;
			}
			$cells = isset( $r['values'] ) ? preg_split( '/\r\n|\r|\n/', (string) $r['values'] ) : array();
			echo '<tr>';
			echo '<th class="fw-ct__feature" scope="row"><span class="fw-ct__feature-label">' . esc_html( $label ) . '</span>';
			if ( ! empty( $r['tooltip'] ) ) { echo '<span class="fw-ct__hint">' . esc_html( $r['tooltip'] ) . '</span>'; }
			echo '</th>';
			for ( $ci = 0; $ci < $col_count; $ci++ ) {
				$fcls = $is_featured[ $ci ] ? ' is-featured' : '';
				$raw  = isset( $cells[ $ci ] ) ? $cells[ $ci ] : '';
				echo '<td class="fw-ct__cell' . $fcls . '">' . sc_ct_cell( $raw ) . '</td>';
			}
			echo '</tr>';
		}
		echo '</tbody>';

		echo '</table></div>';
		echo '</div>';
		return ob_get_clean();
	}
}

echo sc_ct_render( $atts );
