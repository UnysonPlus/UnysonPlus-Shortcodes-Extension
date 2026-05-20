<?php if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}

/**
 * @var array $atts
 */

$icon_style    = ! empty( $atts['icon_style'] )    ? $atts['icon_style']    : 'plus-minus';
$icon_position = ! empty( $atts['icon_position'] )  ? $atts['icon_position']  : 'left';
$initially_open = ! empty( $atts['initially_open'] ) ? $atts['initially_open'] : 'first';
$collapsible   = ( ! empty( $atts['collapsible'] )   && $atts['collapsible'] === 'yes' );
$multiple_open = ( ! empty( $atts['multiple_open'] ) && $atts['multiple_open'] === 'yes' );

$numbering_style    = fw_akg( 'numbering/style',           $atts, 'none' );
$numbering_template = fw_akg( 'numbering/custom/template', $atts, 'Q{n}' );
$numbering_start    = (int) fw_akg( 'numbering_start',     $atts, 1 );

// Custom-icon inputs (only consulted when $icon_style === 'custom').
// Defensive fallbacks to '+' / '−' so cleared text fields + no image won't render blank.
$icon_closed_image = ! empty( $atts['icon_closed_image']['url'] ) ? $atts['icon_closed_image']['url'] : '';
$icon_open_image   = ! empty( $atts['icon_open_image']['url'] )   ? $atts['icon_open_image']['url']   : '';
$icon_closed_text  = ( isset( $atts['icon_closed_text'] ) && $atts['icon_closed_text'] !== '' ) ? $atts['icon_closed_text'] : '+';
$icon_open_text    = ( isset( $atts['icon_open_text'] )   && $atts['icon_open_text']   !== '' ) ? $atts['icon_open_text']   : '−';

if ( ! function_exists( 'fw_sc_accordion_int_to_alpha' ) ) {
    /**
     * Excel-style alpha index: 1=a/A, 26=z/Z, 27=aa/AA, 28=ab/AB, ...
     * Clamps n<1 to 1 so non-positive inputs still produce a letter.
     */
    function fw_sc_accordion_int_to_alpha( $n, $upper = false ) {
        if ( $n < 1 ) { $n = 1; }
        $result = '';
        while ( $n > 0 ) {
            $r      = ( $n - 1 ) % 26;
            $result = chr( 65 + $r ) . $result;
            $n      = intdiv( $n - 1, 26 );
        }
        return $upper ? $result : strtolower( $result );
    }
}

if ( ! function_exists( 'fw_sc_accordion_int_to_roman' ) ) {
    /**
     * Returns the Roman numeral form of a positive integer. Clamps n<1 to 1.
     */
    function fw_sc_accordion_int_to_roman( $n ) {
        if ( $n < 1 ) { $n = 1; }
        $map = array(
            1000 => 'M', 900 => 'CM', 500 => 'D', 400 => 'CD',
            100  => 'C', 90  => 'XC', 50  => 'L', 40  => 'XL',
            10   => 'X', 9   => 'IX', 5   => 'V', 4   => 'IV',
            1    => 'I',
        );
        $result = '';
        foreach ( $map as $v => $sym ) {
            while ( $n >= $v ) {
                $result .= $sym;
                $n      -= $v;
            }
        }
        return $result;
    }
}

if ( ! function_exists( 'fw_sc_accordion_format_number' ) ) {
    /**
     * Render the numbering label for one accordion item.
     *
     * @param string $style    Numbering style key from options.
     * @param string $template Custom template string (only used when $style is 'custom').
     * @param int    $index    Zero-based item index.
     * @param int    $start    The number assigned to the first item (default 1).
     * @return string          Empty string when $style is 'none'.
     */
    function fw_sc_accordion_format_number( $style, $template, $index, $start ) {
        $n = (int) $start + (int) $index;
        switch ( $style ) {
            case 'none':
                return '';
            case 'decimal':
                return (string) $n;
            case 'decimal-leading-zero':
                return ( $n >= 0 && $n < 10 ) ? '0' . $n : (string) $n;
            case 'lower-alpha':
                return fw_sc_accordion_int_to_alpha( $n, false );
            case 'upper-alpha':
                return fw_sc_accordion_int_to_alpha( $n, true );
            case 'lower-roman':
                return strtolower( fw_sc_accordion_int_to_roman( $n ) );
            case 'upper-roman':
                return fw_sc_accordion_int_to_roman( $n );
            case 'q-prefix':
                return 'Q' . $n;
            case 'custom':
                if ( $template === '' || $template === null ) {
                    return (string) $n;
                }
                return strtr( $template, array(
                    '{n}'  => (string) $n,
                    '{0n}' => ( $n >= 0 && $n < 10 ) ? '0' . $n : (string) $n,
                    '{a}'  => fw_sc_accordion_int_to_alpha( $n, false ),
                    '{A}'  => fw_sc_accordion_int_to_alpha( $n, true ),
                    '{i}'  => strtolower( fw_sc_accordion_int_to_roman( $n ) ),
                    '{I}'  => fw_sc_accordion_int_to_roman( $n ),
                ) );
        }
        return '';
    }
}

$atts['base_class']       = 'accordion';
$atts['unique_id_prefix'] = 'ac-';

$attr = sc_build_wrapper_attr( $atts );

$wrapper_classes = [
    'accordion-icon-' . esc_attr( $icon_style ),
    'accordion-icon-' . esc_attr( $icon_position ),
];

if ( $numbering_style !== 'none' ) {
    $wrapper_classes[] = 'accordion-has-numbering';
}

if ( ! empty( $attr['class'] ) ) {
    $attr['class'] .= ' ' . implode( ' ', $wrapper_classes );
} else {
    $attr['class'] = implode( ' ', $wrapper_classes );
}

$attr['data-icon-style']    = esc_attr( $icon_style );
$attr['data-icon-position'] = esc_attr( $icon_position );
$attr['data-initially-open'] = esc_attr( $initially_open );
$attr['data-collapsible']   = $collapsible ? 'true' : 'false';
$attr['data-multiple-open'] = $multiple_open ? 'true' : 'false';

$tabs = fw_akg( 'tabs', $atts, array() );

if ( empty( $attr['id'] ) ) {
    $attr['id'] = 'accordion-' . uniqid();
}
$accordion_id = $attr['id'];
?>

<?php if ( ! empty( $tabs ) ) : ?>
        <div <?php echo fw_attr_to_html( $attr ); ?> role="tablist" aria-multiselectable="<?php echo $multiple_open ? 'true' : 'false'; ?>">
                <?php foreach ( $tabs as $index => $tab ) :
                    $panel_id  = $accordion_id . '-panel-' . $index;
                    $header_id = $accordion_id . '-header-' . $index;

                    $is_open = false;
                    if ( $initially_open === 'all' ) {
                        $is_open = true;
                    } elseif ( $initially_open === 'first' && $index === 0 ) {
                        $is_open = true;
                    }

                    $number_label = fw_sc_accordion_format_number( $numbering_style, $numbering_template, $index, $numbering_start );
                ?>
                        <h3 class="accordion-title<?php echo $is_open ? ' ui-state-active' : ''; ?>"
                            id="<?php echo esc_attr( $header_id ); ?>"
                            role="tab"
                            aria-controls="<?php echo esc_attr( $panel_id ); ?>"
                            aria-expanded="<?php echo $is_open ? 'true' : 'false'; ?>"
                            tabindex="0">
                                <span class="accordion-icon" aria-hidden="true">
                                        <?php if ( $icon_style === 'custom' ) : ?>
                                                <span class="accordion-icon-state-closed">
                                                        <?php if ( $icon_closed_image !== '' ) : ?>
                                                                <img src="<?php echo esc_url( $icon_closed_image ); ?>" alt="">
                                                        <?php else : ?>
                                                                <?php echo esc_html( $icon_closed_text ); ?>
                                                        <?php endif; ?>
                                                </span>
                                                <span class="accordion-icon-state-open">
                                                        <?php if ( $icon_open_image !== '' ) : ?>
                                                                <img src="<?php echo esc_url( $icon_open_image ); ?>" alt="">
                                                        <?php else : ?>
                                                                <?php echo esc_html( $icon_open_text ); ?>
                                                        <?php endif; ?>
                                                </span>
                                        <?php endif; ?>
                                </span>
                                <?php if ( $number_label !== '' ) : ?>
                                        <span class="accordion-number" aria-hidden="true"><?php echo esc_html( $number_label ); ?></span>
                                <?php endif; ?>
                                <span class="accordion-title-text"><?php echo esc_html( $tab['tab_title'] ); ?></span>
                        </h3>
                        <div class="accordion-content"
                             id="<?php echo esc_attr( $panel_id ); ?>"
                             role="tabpanel"
                             aria-labelledby="<?php echo esc_attr( $header_id ); ?>"
                             aria-hidden="<?php echo $is_open ? 'false' : 'true'; ?>"
                             style="display:<?php echo $is_open ? 'block' : 'none'; ?>;">
                                <?php echo do_shortcode( $tab['tab_content'] ); ?>
                        </div>
                <?php endforeach; ?>
        </div>
<?php endif; ?>
