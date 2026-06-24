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

if ( ! function_exists( 'sc_cal_to_ymd' ) ) {
	/** Normalise a date-picker / timestamp value to Y-m-d (or '' if unparseable). */
	function sc_cal_to_ymd( $v ) {
		$v = trim( (string) $v );
		if ( $v === '' ) { return ''; }
		if ( ctype_digit( $v ) && strlen( $v ) >= 9 ) { // unix timestamp
			return gmdate( 'Y-m-d', (int) $v );
		}
		$ts = strtotime( $v );
		return $ts ? gmdate( 'Y-m-d', $ts ) : '';
	}
}

if ( ! function_exists( 'sc_cal_color' ) ) {
	function sc_cal_color( $c ) {
		$ok = array( 'blue', 'green', 'amber', 'red', 'purple', 'teal' );
		return in_array( $c, $ok, true ) ? $c : 'blue';
	}
}

if ( ! function_exists( 'sc_cal_events' ) ) {
	/** Read + normalise events. Falls back to the legacy data_provider shape. */
	function sc_cal_events( $atts ) {
		$raw = sc_get( 'events', $atts, array() );
		$out = array();

		if ( is_array( $raw ) && ! empty( $raw ) ) {
			foreach ( $raw as $e ) {
				if ( ! is_array( $e ) ) { continue; }
				$start = sc_cal_to_ymd( isset( $e['date'] ) ? $e['date'] : '' );
				if ( $start === '' ) { continue; }
				$end = sc_cal_to_ymd( isset( $e['end_date'] ) ? $e['end_date'] : '' );
				if ( $end === '' || $end < $start ) { $end = $start; }
				$out[] = array(
					'title'  => isset( $e['title'] ) ? trim( (string) $e['title'] ) : '',
					'start'  => $start,
					'end'    => $end,
					'time'   => ( isset( $e['all_day'] ) && $e['all_day'] === 'yes' ) ? '' : ( isset( $e['time'] ) ? trim( (string) $e['time'] ) : '' ),
					'allDay' => ( isset( $e['all_day'] ) && $e['all_day'] === 'yes' ),
					'url'    => isset( $e['url'] ) ? trim( (string) $e['url'] ) : '',
					'color'  => sc_cal_color( isset( $e['color'] ) ? $e['color'] : 'blue' ),
				);
			}
			return $out;
		}

		// Legacy: data_provider/custom/custom_events with calendar_date_range (timestamps).
		$legacy = sc_get( 'data_provider/custom/custom_events', $atts, array() );
		if ( is_array( $legacy ) ) {
			foreach ( $legacy as $e ) {
				if ( ! is_array( $e ) ) { continue; }
				$from  = isset( $e['calendar_date_range']['from'] ) ? $e['calendar_date_range']['from'] : '';
				$to    = isset( $e['calendar_date_range']['to'] ) ? $e['calendar_date_range']['to'] : '';
				$start = sc_cal_to_ymd( $from );
				if ( $start === '' ) { continue; }
				$end = sc_cal_to_ymd( $to );
				if ( $end === '' || $end < $start ) { $end = $start; }
				$out[] = array(
					'title'  => isset( $e['title'] ) ? trim( (string) $e['title'] ) : '',
					'start'  => $start, 'end' => $end, 'time' => '', 'allDay' => false,
					'url'    => isset( $e['url'] ) ? trim( (string) $e['url'] ) : '',
					'color'  => 'blue',
				);
			}
		}
		return $out;
	}
}

if ( ! function_exists( 'sc_cal_render_grid' ) ) {
	/** Render one month grid (server side). Mirrors the JS renderer in scripts.js. */
	function sc_cal_render_grid( $year, $month, $start_mon, $by_day, $today ) {
		global $wp_locale;
		$first_dow = (int) gmdate( 'w', gmmktime( 0, 0, 0, $month, 1, $year ) ); // 0=Sun..6=Sat
		$offset    = $start_mon ? ( ( $first_dow + 6 ) % 7 ) : $first_dow;       // blanks before the 1st
		$start_ts  = gmmktime( 0, 0, 0, $month, 1 - $offset, $year );

		$out = '<div class="fw-cal__weekdays">';
		for ( $i = 0; $i < 7; $i++ ) {
			$dow  = $start_mon ? ( ( $i + 1 ) % 7 ) : $i; // 0=Sun
			$name = $wp_locale ? $wp_locale->get_weekday_abbrev( $wp_locale->get_weekday( $dow ) ) : gmdate( 'D', gmmktime( 0, 0, 0, 1, 4 + $dow, 2021 ) );
			$out .= '<div class="fw-cal__wd">' . esc_html( $name ) . '</div>';
		}
		$out .= '</div><div class="fw-cal__grid">';

		for ( $cell = 0; $cell < 42; $cell++ ) {
			$ts   = $start_ts + $cell * DAY_IN_SECONDS;
			$ymd  = gmdate( 'Y-m-d', $ts );
			$dnum = (int) gmdate( 'j', $ts );
			$dow  = (int) gmdate( 'w', $ts );
			$cls  = array( 'fw-cal__cell' );
			if ( gmdate( 'n', $ts ) != $month ) { $cls[] = 'is-out'; }
			if ( $ymd === $today ) { $cls[] = 'is-today'; }
			if ( $dow === 0 || $dow === 6 ) { $cls[] = 'is-weekend'; }
			$evs = isset( $by_day[ $ymd ] ) ? $by_day[ $ymd ] : array();
			if ( ! empty( $evs ) ) { $cls[] = 'has-events'; }

			$out .= '<div class="' . esc_attr( implode( ' ', $cls ) ) . '" data-date="' . esc_attr( $ymd ) . '">';
			$out .= '<span class="fw-cal__num">' . $dnum . '</span>';
			if ( ! empty( $evs ) ) {
				$out  .= '<div class="fw-cal__events">';
				$shown = array_slice( $evs, 0, 3 );
				foreach ( $shown as $ev ) {
					$tag  = $ev['url'] !== '' ? 'a' : 'span';
					$href = $ev['url'] !== '' ? ' href="' . esc_url( $ev['url'] ) . '"' : '';
					$tip  = trim( ( $ev['time'] !== '' ? $ev['time'] . ' · ' : '' ) . $ev['title'] );
					$out .= '<' . $tag . $href . ' class="fw-cal__ev fw-cal__ev--' . esc_attr( $ev['color'] ) . '" title="' . esc_attr( $tip ) . '">'
						. ( $ev['time'] !== '' ? '<span class="fw-cal__ev-time">' . esc_html( $ev['time'] ) . '</span>' : '' )
						. '<span class="fw-cal__ev-title">' . esc_html( $ev['title'] !== '' ? $ev['title'] : __( 'Event', 'fw' ) ) . '</span>'
						. '</' . $tag . '>';
				}
				$more = count( $evs ) - count( $shown );
				if ( $more > 0 ) { $out .= '<span class="fw-cal__more">+' . $more . ' ' . esc_html__( 'more', 'fw' ) . '</span>'; }
				$out .= '</div>';
			}
			$out .= '</div>';
		}
		return $out . '</div>';
	}
}

if ( ! function_exists( 'sc_cal_render' ) ) {
	function sc_cal_render( $atts ) {
		global $wp_locale;
		$registry = require __DIR__ . '/parts/registry.php';
		$design   = sc_get( 'design', $atts, 'classic' );
		if ( ! isset( $registry[ $design ] ) ) { $design = 'classic'; }

		$events    = sc_cal_events( $atts );
		$start_mon = sc_get( 'start_week', $atts, 'mon' ) !== 'sun';
		$show_list = sc_get( 'show_list', $atts, 'yes' ) === 'yes';
		$list_lim  = max( 1, (int) sc_get( 'list_limit', $atts, 5 ) );

		// Per-day index (multi-day events span every day in their range).
		$by_day = array();
		foreach ( $events as $ev ) {
			$cur = strtotime( $ev['start'] . ' UTC' );
			$end = strtotime( $ev['end'] . ' UTC' );
			$guard = 0;
			while ( $cur <= $end && $guard < 400 ) {
				$by_day[ gmdate( 'Y-m-d', $cur ) ][] = $ev;
				$cur += DAY_IN_SECONDS;
				$guard++;
			}
		}

		$tz    = function_exists( 'wp_timezone' ) ? wp_timezone() : new DateTimeZone( 'UTC' );
		$now   = new DateTime( 'now', $tz );
		$today = $now->format( 'Y-m-d' );
		$year  = (int) $now->format( 'Y' );
		$month = (int) $now->format( 'n' );

		$var = function ( $key, $name ) use ( $atts ) {
			$raw = sc_get( $key, $atts, '' );
			if ( is_array( $raw ) && ! empty( $raw['custom'] ) ) {
				$hex = preg_replace( '/[^#0-9a-zA-Z(),.%\s-]/', '', (string) $raw['custom'] );
				if ( $hex !== '' ) { return $name . ':' . $hex . ';'; }
			}
			return '';
		};
		$style_var  = $var( 'accent_color', '--cal-accent' );
		$style_var .= $var( 'text_color', '--cal-text' );

		$classes = array( 'fw-cal', 'fw-cal--design-' . sanitize_html_class( $design ) );

		$atts['base_class']       = 'calendar';
		$atts['unique_id_prefix'] = 'cal-';
		$atts['css_class']        = trim( implode( ' ', $classes ) . ' ' . ( isset( $atts['css_class'] ) ? $atts['css_class'] : '' ) );
		$attr = sc_build_wrapper_attr( $atts );
		if ( $style_var !== '' ) {
			$attr['style'] = ( isset( $attr['style'] ) && $attr['style'] !== '' ? rtrim( $attr['style'], ';' ) . ';' : '' ) . $style_var;
		}
		// Localized names passed to JS so navigation stays in the site language.
		$wd = array();
		for ( $i = 0; $i < 7; $i++ ) { $wd[] = $wp_locale ? $wp_locale->get_weekday_abbrev( $wp_locale->get_weekday( $i ) ) : gmdate( 'D', gmmktime( 0, 0, 0, 1, 3 + $i, 2021 ) ); }
		$mo = array();
		for ( $m = 1; $m <= 12; $m++ ) { $mo[] = date_i18n( 'F', gmmktime( 12, 0, 0, $m, 1, 2021 ) ); }

		$attr['data-events']     = wp_json_encode( $events );
		$attr['data-first-week'] = $start_mon ? 'mon' : 'sun';
		$attr['data-year']       = $year;
		$attr['data-month']      = $month;
		$attr['data-today']      = $today;
		$attr['data-wd']         = wp_json_encode( $wd );
		$attr['data-mo']         = wp_json_encode( $mo );
		$attr['data-more']       = esc_attr__( 'more', 'fw' );
		$attr['data-event-label'] = esc_attr__( 'Event', 'fw' );

		$ico_prev = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 6l-6 6 6 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
		$ico_next = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 6l6 6-6 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';

		ob_start();
		echo '<div ' . fw_attr_to_html( $attr ) . '>';

		// Header.
		echo '<div class="fw-cal__head">';
		echo '<h3 class="fw-cal__title" data-cal-title>' . esc_html( date_i18n( 'F Y', $now->getTimestamp() + $now->getOffset() ) ) . '</h3>';
		echo '<div class="fw-cal__nav">';
		echo '<button type="button" class="fw-cal__btn" data-cal-nav="prev" aria-label="' . esc_attr__( 'Previous month', 'fw' ) . '">' . $ico_prev . '</button>';
		echo '<button type="button" class="fw-cal__btn fw-cal__btn--today" data-cal-nav="today">' . esc_html__( 'Today', 'fw' ) . '</button>';
		echo '<button type="button" class="fw-cal__btn" data-cal-nav="next" aria-label="' . esc_attr__( 'Next month', 'fw' ) . '">' . $ico_next . '</button>';
		echo '</div></div>';

		// Month grid (server-rendered; JS re-renders only on navigation).
		echo '<div class="fw-cal__month" data-cal-month>';
		echo sc_cal_render_grid( $year, $month, $start_mon, $by_day, $today ); // phpcs:ignore
		echo '</div>';

		// Upcoming list.
		if ( $show_list ) {
			$upcoming = array_filter( $events, function ( $e ) use ( $today ) { return $e['end'] >= $today; } );
			usort( $upcoming, function ( $a, $b ) { return strcmp( $a['start'], $b['start'] ); } );
			$upcoming = array_slice( $upcoming, 0, $list_lim );
			if ( ! empty( $upcoming ) ) {
				echo '<div class="fw-cal__list"><h4 class="fw-cal__list-title">' . esc_html__( 'Upcoming', 'fw' ) . '</h4><ul>';
				foreach ( $upcoming as $ev ) {
					$d   = strtotime( $ev['start'] . ' UTC' );
					$tag = $ev['url'] !== '' ? 'a' : 'span';
					$hrf = $ev['url'] !== '' ? ' href="' . esc_url( $ev['url'] ) . '"' : '';
					echo '<li class="fw-cal__li fw-cal__li--' . esc_attr( $ev['color'] ) . '">';
					echo '<span class="fw-cal__li-date"><span class="fw-cal__li-d">' . esc_html( gmdate( 'j', $d ) ) . '</span><span class="fw-cal__li-m">' . esc_html( date_i18n( 'M', $d ) ) . '</span></span>';
					echo '<span class="fw-cal__li-body"><' . $tag . $hrf . ' class="fw-cal__li-title">' . esc_html( $ev['title'] !== '' ? $ev['title'] : __( 'Event', 'fw' ) ) . '</' . $tag . '>';
					if ( $ev['time'] !== '' ) { echo '<span class="fw-cal__li-time">' . esc_html( $ev['time'] ) . '</span>'; }
					echo '</span></li>';
				}
				echo '</ul></div>';
			}
		}

		echo '</div>';
		return ob_get_clean();
	}
}

echo sc_cal_render( $atts );
