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

if ( ! function_exists( 'sc_bi_mins' ) ) {
	/** "HH:MM" -> minutes since midnight, or null if invalid. */
	function sc_bi_mins( $t ) {
		$t = trim( (string) $t );
		if ( ! preg_match( '/^(\d{1,2}):(\d{2})$/', $t, $m ) ) { return null; }
		$h = (int) $m[1]; $i = (int) $m[2];
		if ( $h > 23 || $i > 59 ) { return null; }
		return $h * 60 + $i;
	}
}

if ( ! function_exists( 'sc_bi_fmt' ) ) {
	/** Format "HH:MM" per 12/24-hour. */
	function sc_bi_fmt( $t, $fmt ) {
		$mins = sc_bi_mins( $t );
		if ( $mins === null ) { return esc_html( $t ); }
		$h = intdiv( $mins, 60 ); $i = $mins % 60;
		if ( $fmt === '24' ) { return sprintf( '%02d:%02d', $h, $i ); }
		$ap = $h >= 12 ? 'PM' : 'AM';
		$h12 = $h % 12; if ( $h12 === 0 ) { $h12 = 12; }
		return $i === 0 ? ( $h12 . ' ' . $ap ) : sprintf( '%d:%02d %s', $h12, $i, $ap );
	}
}

if ( ! function_exists( 'sc_bi_render' ) ) {
	function sc_bi_render( $atts ) {
		$registry = require __DIR__ . '/parts/registry.php';
		$design   = sc_get( 'design', $atts, 'card' );
		if ( ! isset( $registry[ $design ] ) ) { $design = 'card'; }

		$day_labels = array(
			'mon' => __( 'Monday', 'fw' ), 'tue' => __( 'Tuesday', 'fw' ), 'wed' => __( 'Wednesday', 'fw' ),
			'thu' => __( 'Thursday', 'fw' ), 'fri' => __( 'Friday', 'fw' ), 'sat' => __( 'Saturday', 'fw' ), 'sun' => __( 'Sunday', 'fw' ),
		);

		$name  = trim( (string) sc_get( 'biz_name', $atts, '' ) );
		$hours = sc_get( 'hours', $atts, array() );
		if ( ! is_array( $hours ) ) { $hours = array(); }
		$address = trim( (string) sc_get( 'address', $atts, '' ) );
		$phone   = trim( (string) sc_get( 'phone', $atts, '' ) );
		$email   = trim( (string) sc_get( 'email', $atts, '' ) );
		$website = trim( (string) sc_get( 'website', $atts, '' ) );
		$map     = trim( (string) sc_get( 'map_link', $atts, '' ) );

		$has_contact = ( $address . $phone . $email . $website . $map ) !== '';
		if ( empty( $hours ) && ! $has_contact && $name === '' ) {
			if ( is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
				return '<div class="fw-bi__empty">' . esc_html__( 'Add opening hours or contact details.', 'fw' ) . '</div>';
			}
			return '';
		}

		$show_status = sc_get( 'show_status', $atts, 'yes' ) === 'yes';
		$fmt         = sc_get( 'time_format', $atts, '12' ) === '24' ? '24' : '12';
		$hl_today    = sc_get( 'highlight_today', $atts, 'yes' ) === 'yes';

		/* Current day + time in the site timezone. */
		$today_key = ''; $now_mins = 0;
		if ( function_exists( 'current_datetime' ) ) {
			$now = current_datetime();
			$today_key = strtolower( $now->format( 'D' ) );
			$now_mins  = (int) $now->format( 'G' ) * 60 + (int) $now->format( 'i' );
		}

		/* Open/closed status from today's row. */
		$is_open = null; // null = unknown
		if ( $show_status && $today_key !== '' ) {
			foreach ( $hours as $row ) {
				if ( ! is_array( $row ) || ( isset( $row['day'] ) ? $row['day'] : '' ) !== $today_key ) { continue; }
				if ( isset( $row['closed'] ) && $row['closed'] === 'yes' ) { $is_open = false; break; }
				$o = sc_bi_mins( isset( $row['open'] ) ? $row['open'] : '' );
				$c = sc_bi_mins( isset( $row['close'] ) ? $row['close'] : '' );
				if ( $o === null || $c === null ) { $is_open = false; break; }
				$is_open = ( $c > $o ) ? ( $now_mins >= $o && $now_mins < $c ) : ( $now_mins >= $o || $now_mins < $c );
				break;
			}
			if ( $is_open === null ) { $is_open = false; }
		}

		$var = function ( $key, $name ) use ( $atts ) {
			$raw = sc_get( $key, $atts, '' );
			if ( is_array( $raw ) && ! empty( $raw['custom'] ) ) {
				$hex = preg_replace( '/[^#0-9a-zA-Z(),.%\s-]/', '', (string) $raw['custom'] );
				if ( $hex !== '' ) { return $name . ':' . $hex . ';'; }
			}
			return '';
		};
		$style_var  = $var( 'accent_color', '--bi-accent' );
		$style_var .= $var( 'card_bg', '--bi-card-bg' );
		$style_var .= $var( 'text_color', '--bi-text' );

		$classes = array( 'fw-bi', 'fw-bi--design-' . sanitize_html_class( $design ) );

		$atts['base_class']       = 'business-info';
		$atts['unique_id_prefix'] = 'bi-';
		$atts['css_class']        = trim( implode( ' ', $classes ) . ' ' . ( isset( $atts['css_class'] ) ? $atts['css_class'] : '' ) );
		$attr = sc_build_wrapper_attr( $atts );
		if ( $style_var !== '' ) {
			$attr['style'] = ( isset( $attr['style'] ) && $attr['style'] !== '' ? rtrim( $attr['style'], ';' ) . ';' : '' ) . $style_var;
		}

		ob_start();
		echo '<div ' . fw_attr_to_html( $attr ) . '>';

		if ( $name !== '' || ( $show_status && $is_open !== null ) ) {
			echo '<div class="fw-bi__head">';
			if ( $name !== '' ) { echo '<h3 class="fw-bi__name">' . esc_html( $name ) . '</h3>'; }
			if ( $show_status && $is_open !== null ) {
				echo '<span class="fw-bi__status ' . ( $is_open ? 'is-open' : 'is-closed' ) . '">'
					. '<span class="fw-bi__dot" aria-hidden="true"></span>'
					. esc_html( $is_open ? __( 'Open now', 'fw' ) : __( 'Closed', 'fw' ) ) . '</span>';
			}
			echo '</div>';
		}

		echo '<div class="fw-bi__cols">';

		/* Contact block (not for the table-only design). */
		if ( $design !== 'table' && $has_contact ) {
			echo '<div class="fw-bi__contact">';
			if ( $address !== '' ) {
				echo '<div class="fw-bi__row"><span class="fw-bi__ico" aria-hidden="true">&#128205;</span><span>' . nl2br( esc_html( $address ) );
				if ( $map !== '' ) { echo ' <a class="fw-bi__maplink" href="' . esc_url( $map ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Directions', 'fw' ) . '</a>'; }
				echo '</span></div>';
			}
			if ( $phone !== '' ) {
				$tel = preg_replace( '/[^0-9+]/', '', $phone );
				echo '<div class="fw-bi__row"><span class="fw-bi__ico" aria-hidden="true">&#128222;</span><a href="tel:' . esc_attr( $tel ) . '">' . esc_html( $phone ) . '</a></div>';
			}
			if ( $email !== '' && is_email( $email ) ) {
				echo '<div class="fw-bi__row"><span class="fw-bi__ico" aria-hidden="true">&#9993;</span><a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a></div>';
			}
			if ( $website !== '' ) {
				echo '<div class="fw-bi__row"><span class="fw-bi__ico" aria-hidden="true">&#127760;</span><a href="' . esc_url( $website ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( preg_replace( '~^https?://~', '', $website ) ) . '</a></div>';
			}
			echo '</div>';
		}

		/* Hours table. */
		if ( ! empty( $hours ) ) {
			echo '<table class="fw-bi__hours"><tbody>';
			foreach ( $hours as $row ) {
				if ( ! is_array( $row ) ) { continue; }
				$dk    = isset( $row['day'] ) ? $row['day'] : '';
				$label = isset( $day_labels[ $dk ] ) ? $day_labels[ $dk ] : ucfirst( (string) $dk );
				$closed= isset( $row['closed'] ) && $row['closed'] === 'yes';
				$note  = isset( $row['note'] ) ? trim( (string) $row['note'] ) : '';
				$is_today = ( $hl_today && $dk === $today_key );

				echo '<tr class="fw-bi__hrow' . ( $is_today ? ' is-today' : '' ) . ( $closed ? ' is-closed' : '' ) . '">';
				echo '<th scope="row" class="fw-bi__day">' . esc_html( $label ) . '</th>';
				echo '<td class="fw-bi__time">';
				if ( $closed ) {
					echo '<span class="fw-bi__closed-label">' . esc_html__( 'Closed', 'fw' ) . '</span>';
				} else {
					echo esc_html( sc_bi_fmt( isset( $row['open'] ) ? $row['open'] : '', $fmt ) ) . ' &ndash; ' . esc_html( sc_bi_fmt( isset( $row['close'] ) ? $row['close'] : '', $fmt ) );
				}
				if ( $note !== '' ) { echo ' <span class="fw-bi__note">' . esc_html( $note ) . '</span>'; }
				echo '</td></tr>';
			}
			echo '</tbody></table>';
		}

		echo '</div></div>';
		return ob_get_clean();
	}
}

echo sc_bi_render( $atts );
