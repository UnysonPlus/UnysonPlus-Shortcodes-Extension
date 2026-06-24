<?php if ( ! defined( 'FW' ) ) {
    die( 'Forbidden' );
}

/**
 * Table of Contents — frontend view.
 *
 * @var array $atts
 *
 * Renders a configuration-only shell: a <nav> wrapper plus an empty list the
 * frontend script (static/js/scripts.js) fills in after scanning the page.
 * All behaviour travels as data-* attributes so the script needs no inline
 * config. Colors resolve to CSS custom properties on the wrapper so the
 * stylesheet can drive hover / active states (which inline styles can't).
 */

/* ----- helpers ---------------------------------------------------------- */

// Resolve a unit-input value (array {value,unit}) or legacy string → CSS length.
$css_len = function ( $v ) {
    if ( is_array( $v ) ) {
        $num  = isset( $v['value'] ) ? trim( (string) $v['value'] ) : '';
        $unit = isset( $v['unit'] )  ? trim( (string) $v['unit'] )  : '';
        return $num === '' ? '' : $num . $unit;
    }
    return trim( (string) $v );
};

// Numeric (px) value of a unit-input — for the JS scroll offset.
$unit_number = function ( $v ) {
    if ( is_array( $v ) ) {
        return isset( $v['value'] ) ? (int) $v['value'] : 0;
    }
    return (int) $v;
};

// Resolve a compact-color att ({predefined,custom} | legacy string) to a CSS
// color token: a preset slug is looked up against the live palette so it
// becomes a real hex we can drop into a CSS variable; a custom hex is
// sanitised and passed through. Returns '' when nothing is set.
$color_value = function ( $key ) use ( $atts ) {
    $v = $atts[ $key ] ?? '';
    $predefined = '';
    $custom     = '';
    if ( is_array( $v ) ) {
        $predefined = isset( $v['predefined'] ) ? trim( (string) $v['predefined'] ) : '';
        $custom     = isset( $v['custom'] )     ? trim( (string) $v['custom'] )     : '';
    } elseif ( is_string( $v ) ) {
        $predefined = trim( $v );
    }
    if ( $predefined !== '' ) {
        $slug = preg_replace( '/^(text|bg)-/', '', $predefined );
        if ( function_exists( 'unysonplus_color_preset_slug_map' ) ) {
            $map = unysonplus_color_preset_slug_map();
            if ( isset( $map[ $slug ] ) ) {
                return $map[ $slug ];
            }
        }
        return '';
    }
    if ( $custom !== '' && $custom !== 'transparent' && $custom !== 'rgba(0,0,0,0)' ) {
        $custom = preg_replace( '/[^A-Za-z0-9#(),.%\s]/', '', $custom );
        return $custom;
    }
    return '';
};

/* ----- read atts -------------------------------------------------------- */

// Heading levels (checkboxes) → "2,3"
$levels_in = isset( $atts['levels'] ) && is_array( $atts['levels'] ) ? $atts['levels'] : array();
$selected_levels = array();
foreach ( array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ) as $h ) {
    if ( ! empty( $levels_in[ $h ] ) ) {
        $selected_levels[] = substr( $h, 1 );
    }
}
if ( empty( $selected_levels ) ) {
    $selected_levels = array( '2', '3' );
}
$levels_attr = implode( ',', $selected_levels );

$title             = $atts['title'] ?? '';
$hierarchical      = ( ( $atts['hierarchical'] ?? 'yes' ) === 'yes' );
$min_headings      = max( 1, (int) ( $atts['min_headings'] ?? 2 ) );
$numeration        = $atts['numeration'] ?? 'decimal_nested';
$numeration_suffix = $atts['numeration_suffix'] ?? '.';

$collapsible       = ( ( $atts['collapsible'] ?? 'no' ) === 'yes' );
$collapsed_default = ( ( $atts['collapsed_default'] ?? 'no' ) === 'yes' );
$label_show        = $atts['label_show'] ?? __( 'show', 'fw' );
$label_hide        = $atts['label_hide'] ?? __( 'hide', 'fw' );

$scope          = $atts['scope'] ?? 'content';
$scope_selector = trim( (string) ( $atts['scope_selector'] ?? '' ) );
$skip_text      = trim( (string) ( $atts['skip_text'] ?? '' ) );

$smooth_scroll = ( ( $atts['smooth_scroll'] ?? 'yes' ) === 'yes' );
$scroll_offset = $unit_number( $atts['scroll_offset'] ?? 0 );
$scrollspy     = ( ( $atts['scrollspy'] ?? 'yes' ) === 'yes' );
$nofollow      = ( ( $atts['nofollow'] ?? 'no' ) === 'yes' );
$noindex       = ( ( $atts['noindex'] ?? 'no' ) === 'yes' );

$width        = $atts['width'] ?? 'full';
$custom_width = $css_len( $atts['custom_width'] ?? '' );
$float        = $atts['float'] ?? '';
$sticky       = ( ( $atts['sticky'] ?? 'no' ) === 'yes' );
$sticky_off   = $css_len( $atts['sticky_offset'] ?? '' );

$title_size = sc_sanitize_class( $atts['title_size'] ?? '' );
$items_size = sc_sanitize_class( $atts['items_size'] ?? '' );

/* ----- wrapper attributes ---------------------------------------------- */

$atts['base_class']       = 'sc-toc';
$atts['unique_id_prefix'] = 'toc-';

$attr = sc_build_wrapper_attr( $atts );

// Modifier classes
$classes = isset( $attr['class'] ) ? array( $attr['class'] ) : array();
if ( $hierarchical )          { $classes[] = 'sc-toc--nested'; }
if ( $numeration === 'bullets' ) { $classes[] = 'sc-toc--bullets'; }
if ( $numeration === 'none' )    { $classes[] = 'sc-toc--plain'; }
if ( $float === 'left' )       { $classes[] = 'sc-toc--float-left'; }
if ( $float === 'right' )      { $classes[] = 'sc-toc--float-right'; }
if ( $width === 'auto' )        { $classes[] = 'sc-toc--auto'; }
if ( $sticky )                  { $classes[] = 'sc-toc--sticky'; }
if ( $collapsible )             { $classes[] = 'sc-toc--collapsible'; }
if ( $collapsible && $collapsed_default ) { $classes[] = 'is-collapsed'; }
$attr['class'] = implode( ' ', array_filter( $classes ) );

// CSS variables + box geometry → inline style
$style_bits = array();
$cv = array(
    '--sc-toc-bg'          => $color_value( 'bg_color' ),
    '--sc-toc-border'      => $color_value( 'border_color' ),
    '--sc-toc-title-color' => $color_value( 'title_color' ),
    '--sc-toc-link'        => $color_value( 'link_color' ),
    '--sc-toc-link-hover'  => $color_value( 'link_hover_color' ),
    '--sc-toc-link-active' => $color_value( 'link_active_color' ),
);
foreach ( $cv as $var => $val ) {
    if ( $val !== '' ) {
        $style_bits[] = $var . ':' . $val;
    }
}
if ( $width === 'custom' && $custom_width !== '' ) {
    $style_bits[] = 'width:' . $custom_width;
}
if ( $sticky && $sticky_off !== '' ) {
    $style_bits[] = '--sc-toc-sticky-top:' . $sticky_off;
}
if ( ! empty( $attr['style'] ) ) {
    array_unshift( $style_bits, rtrim( $attr['style'], '; ' ) );
}
if ( ! empty( $style_bits ) ) {
    $attr['style'] = implode( '; ', $style_bits );
}

// data-* config consumed by scripts.js
$attr['data-scope']       = $scope;
$attr['data-selector']    = $scope_selector;
$attr['data-levels']      = $levels_attr;
$attr['data-hierarchical']= $hierarchical ? '1' : '0';
$attr['data-min']         = (string) $min_headings;
$attr['data-numeration']  = $numeration;
$attr['data-suffix']      = $numeration_suffix;
$attr['data-smooth']      = $smooth_scroll ? '1' : '0';
$attr['data-offset']      = (string) $scroll_offset;
$attr['data-scrollspy']   = $scrollspy ? '1' : '0';
$attr['data-nofollow']    = $nofollow ? '1' : '0';
$attr['data-skip']        = $skip_text;

$title_classes = array( 'sc-toc__title' );
if ( $title_size !== '' ) { $title_classes[] = $title_size; }

$list_classes = array( 'sc-toc__list' );
if ( $items_size !== '' ) { $list_classes[] = $items_size; }
?>
<?php if ( $noindex ) { echo '<!--noindex-->'; } ?>
<nav <?php echo fw_attr_to_html( $attr ); ?> role="navigation" aria-label="<?php echo esc_attr( $title !== '' ? wp_strip_all_tags( $title ) : __( 'Table of Contents', 'fw' ) ); ?>">
    <?php if ( $title !== '' || $collapsible ) : ?>
        <div class="sc-toc__header">
            <?php if ( $title !== '' ) : ?>
                <span class="<?php echo esc_attr( implode( ' ', $title_classes ) ); ?>"><?php echo wp_kses_post( $title ); ?></span>
            <?php endif; ?>
            <?php if ( $collapsible ) : ?>
                <button type="button" class="sc-toc__toggle"
                        aria-expanded="<?php echo $collapsed_default ? 'false' : 'true'; ?>"
                        data-label-show="<?php echo esc_attr( $label_show ); ?>"
                        data-label-hide="<?php echo esc_attr( $label_hide ); ?>"><?php
                    echo esc_html( $collapsed_default ? $label_show : $label_hide );
                ?></button>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <ul class="<?php echo esc_attr( implode( ' ', $list_classes ) ); ?>">
        <li class="sc-toc__empty" hidden><?php esc_html_e( 'No headings found.', 'fw' ); ?></li>
    </ul>
</nav>
<?php if ( $noindex ) { echo '<!--/noindex-->'; } ?>
